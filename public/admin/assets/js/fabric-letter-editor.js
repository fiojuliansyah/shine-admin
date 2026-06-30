if (typeof fabric !== 'undefined') {
    // Ketajaman teks ditangani lewat enableRetinaScaling per-canvas.
    // CATATAN: jangan matikan objectCaching pada teks — di fabric 5.3.1 hal itu
    // memicu bug "textBaseline 'alphabetical'" dan crash saat render.
    fabric.devicePixelRatio = window.devicePixelRatio || 1;

    if (fabric.Text && fabric.Text.prototype) {
        fabric.Text.prototype.enlargeSpaces = function () {
            for (var i, e, l, k, n, t, b, a = 0, c = this._textLines.length; a < c; a++) {
                if (a === c - 1 || this.isEndOfWrapping(a)) continue;
                k = 0;
                n = this._textLines[a];
                e = this.getLineWidth(a);
                if (e < this.width && (t = this.textLines[a].match(this._reSpacesAndTabs))) {
                    i = t.length;
                    l = (this.width - e) / i;
                    for (var h = 0, m = n.length; h <= m; h++) {
                        b = this.__charBounds[a][h];
                        if (this._reSpaceAndTab.test(n[h])) {
                            b.width += l;
                            b.kernedWidth += l;
                            b.left += k;
                            k += l;
                        } else {
                            b.left += k;
                        }
                    }
                }
            }
        };
    }
}

class FabricLetterEditor {
    constructor() {
        this.pages = [];
        this.currentPage = 0;
        this.CANVAS_W = 794;
        this.CANVAS_H = 1123;
        this.MAX_HISTORY = 30;
        this._autoPageLock = false;
        this._mode = 'select'; // 'select' | 'textbox'
        this.TAB_STR = '\u00a0\u00a0\u00a0\u00a0'; // 4 non-breaking spaces (tab seperti Word)
        this._dirty = false;
    }

    init(savedData) {
        console.info('[LetterEditor] init v4 — data length:', savedData ? savedData.length : 0);
        if (savedData && savedData.trim() !== '') {
            let parsed = this._parseSavedData(savedData);

            if (parsed && parsed.pages && Array.isArray(parsed.pages)) {
                console.info('[LetterEditor] loading fabric pages:', parsed.pages.length);
                parsed.pages.forEach((_, i) => this._buildPageContainer(i));
                this.loadFromJSON(parsed);
            } else {
                console.warn('[LetterEditor] data bukan format fabric pages, fallback ke teks.');
                this._buildPageContainer(0);
                this.convertFromHTML(savedData);
            }
        } else {
            this._buildPageContainer(0);
        }
        this.switchPage(0);
        this.renderThumbs();
        this.renderLayers();
        this._bindToolbar();
        this._bindDirtyGuard();
    }

    /**
     * Parse data tersimpan dengan toleransi: menangani JSON ganda-encode
     * (string di dalam string) yang bisa membuat editor salah menampilkan
     * teks JSON mentah alih-alih merender halaman.
     */
    _parseSavedData(savedData) {
        let value = savedData;
        for (let i = 0; i < 3; i++) {
            if (typeof value !== 'string') break;
            try {
                value = JSON.parse(value);
            } catch (e) {
                console.error('[LetterEditor] JSON.parse gagal:', e.message);
                return null;
            }
        }
        return (typeof value === 'object') ? value : null;
    }

    _bindDirtyGuard() {
        const self = this;
        this._dirty = false;
        window.addEventListener('beforeunload', (e) => {
            if (!self._dirty || self._allowLeave) return;
            e.preventDefault();
            e.returnValue = '';
            return '';
        });
        document.querySelectorAll('a[data-confirm-leave]').forEach(a => {
            a.addEventListener('click', (e) => {
                if (self._dirty && !self._allowLeave) {
                    if (!confirm('Perubahan belum disimpan akan hilang. Tetap keluar?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    }

    markDirty() {
        if (this._loading) return;
        this._dirty = true;
    }

    markClean() {
        this._dirty = false;
        this._allowLeave = true;
    }

    _buildPageContainer(index) {
        const container = document.getElementById('canvasContainer');

        const grid = document.createElement('div');
        grid.id = 'page-grid-' + index;
        grid.className = 'canvas-with-ruler';

        const corner = document.createElement('div');
        corner.className = 'ruler-corner';

        const rulerH = document.createElement('div');
        rulerH.className = 'ruler-h';
        rulerH.id = 'ruler-h-' + index;
        rulerH.style.width = this.CANVAS_W + 'px';

        const rulerV = document.createElement('div');
        rulerV.className = 'ruler-v';
        rulerV.id = 'ruler-v-' + index;
        rulerV.style.height = this.CANVAS_H + 'px';

        const wrapper = document.createElement('div');
        wrapper.id = 'page-wrapper-' + index;
        wrapper.className = 'ruler-canvas-wrap';
        wrapper.style.cssText = 'position:relative;display:inline-block;';

        const canvasEl = document.createElement('canvas');
        canvasEl.id = 'fabricCanvas-' + index;
        wrapper.appendChild(canvasEl);

        const rulerOverlay = document.createElement('div');
        rulerOverlay.id = 'ruler-overlay-' + index;
        rulerOverlay.style.cssText = 'position:absolute;top:0;left:0;width:' + this.CANVAS_W + 'px;height:' + this.CANVAS_H + 'px;pointer-events:none;overflow:hidden;';
        wrapper.appendChild(rulerOverlay);

        grid.appendChild(corner);
        grid.appendChild(rulerH);
        grid.appendChild(rulerV);
        grid.appendChild(wrapper);
        container.appendChild(grid);

        this._drawRulerH(rulerH, this.CANVAS_W);
        this._drawRulerV(rulerV, this.CANVAS_H);

        const fc = new fabric.Canvas('fabricCanvas-' + index, {
            width: this.CANVAS_W,
            height: this.CANVAS_H,
            backgroundColor: '#fff',
            enableRetinaScaling: true,
            imageSmoothingEnabled: true,
        });

        const margins = { top: 60, bottom: 60, left: 60, right: 60 };
        this._initRulerOverlay(rulerOverlay, margins, index);

        const historyUndo = [];
        const historyRedo = [];
        this.pages.push({ id: index, canvas: fc, historyUndo, historyRedo, margins });

        const self = this;

        const saveHistory = () => {
            const snap = JSON.stringify(fc.toJSON());
            historyUndo.push(snap);
            if (historyUndo.length > self.MAX_HISTORY) historyUndo.shift();
            historyRedo.length = 0;
        };

        fc.on('object:added', (e) => {
            self._ensureLayerMeta(e.target);
            saveHistory();
            self.markDirty();
            self.renderLayers();
        });
        fc.on('object:removed', () => {
            saveHistory();
            self.markDirty();
            self.renderLayers();
        });
        fc.on('object:modified', (e) => {
            saveHistory();
            self.markDirty();
            self._constrainObject(e.target, margins);
            if (!self._autoPageLock) self._checkAutoPage(index, e.target);
            self.renderLayers();
        });
        fc.on('object:moving', (e) => {
            self._constrainObject(e.target, margins);
        });
        let _autoPageTimer = null;
        fc.on('text:changed', (e) => {
            self.markDirty();
            self.renderLayers();
            if (self._autoPageLock) return;
            if (_autoPageTimer) clearTimeout(_autoPageTimer);
            _autoPageTimer = setTimeout(() => {
                _autoPageTimer = null;
                if (!self._autoPageLock) self._checkAutoPage(index, e.target);
            }, 300);
        });
        fc.on('selection:created', (e) => { self._attachTextSelectionSync(e); self._syncToolbarFromSelection(); self.renderLayers(); });
        fc.on('selection:updated', (e) => { self._attachTextSelectionSync(e); self._syncToolbarFromSelection(); self.renderLayers(); });
        fc.on('selection:cleared', () => { self._syncToolbarFromSelection(); self.renderLayers(); });

        fc.on('text:editing:entered', (e) => {
            const textarea = fc.upperCanvasEl && fc.upperCanvasEl.parentNode
                ? fc.upperCanvasEl.parentNode.querySelector('textarea')
                : null;
            if (textarea && !textarea._kiloTabBound) {
                textarea._kiloTabBound = true;
                textarea.addEventListener('keydown', (ev) => {
                    if (ev.key !== 'Tab') return;
                    ev.preventDefault();
                    ev.stopPropagation();
                    const obj = fc.getActiveObject();
                    if (!obj || !obj.isEditing) return;
                    const tabStr = self.TAB_STR || '    ';
                    const start = Math.min(obj.selectionStart, obj.selectionEnd);
                    const end = Math.max(obj.selectionStart, obj.selectionEnd);
                    obj.insertChars(tabStr, null, start, end);
                    obj.selectionStart = start + tabStr.length;
                    obj.selectionEnd = start + tabStr.length;
                    obj.hiddenTextarea && (obj.hiddenTextarea.value = obj.text);
                    fc.renderAll();
                }, true);
            }
        });

        fc.on('mouse:down', (e) => {
            if (self._mode === 'textbox') {
                const pointer = fc.getPointer(e.e);
                self._addTextboxAt(index, pointer.x, pointer.y);
                self.setMode('select');
                return;
            }

            // Word-like: klik di area kosong langsung buat paragraph baru selebar halaman
            if (self._mode === 'select' && !e.target) {
                const pointer = fc.getPointer(e.e);
                const page = self.pages[index];
                if (!page) return;
                const left = page.margins.left;
                const width = self.CANVAS_W - page.margins.left - page.margins.right;
                const top = Math.max(page.margins.top, pointer.y);
                const tb = new fabric.Textbox('', {
                    left,
                    top,
                    width,
                    fontSize: 14,
                    fontFamily: 'Arial',
                    fill: '#000000',
                    padding: 0,
                    borderColor: 'transparent',
                    cornerColor: 'transparent',
                    hasControls: false,
                    hasBorders: false,
                });
                self._ensureLayerMeta(tb);
                fc.add(tb);
                fc.setActiveObject(tb);
                fc.renderAll();
                tb.enterEditing();
                tb.on('editing:exited', () => {
                    if (!tb.text || tb.text.trim() === '') {
                        fc.remove(tb);
                        fc.renderAll();
                    } else {
                        tb.set({ hasControls: true, hasBorders: true });
                        fc.renderAll();
                    }
                });
            }
        });

        wrapper.addEventListener('click', () => self.switchPage(self.pages.findIndex(p => p.id === index)));

        return fc;
    }

    _drawRulerH(ruler, width) {
        ruler.innerHTML = '';
        // 96 DPI: 1cm = 37.8px
        const PX_PER_CM = 37.8;
        const totalCm = Math.ceil(width / PX_PER_CM);
        for (let i = 0; i <= totalCm; i++) {
            const x = Math.round(i * PX_PER_CM);
            if (x > width) break;
            const line = document.createElement('div');
            line.className = 'ruler-tick-line';
            const isMajor = i % 1 === 0;
            line.style.cssText = `left:${x}px;top:${isMajor ? 10 : 14}px;width:1px;height:${isMajor ? 10 : 6}px;`;
            ruler.appendChild(line);
            if (i > 0 && i % 2 === 0) {
                const label = document.createElement('div');
                label.className = 'ruler-tick';
                label.style.cssText = `left:${x + 2}px;top:1px;`;
                label.textContent = i + 'cm';
                ruler.appendChild(label);
            }
            // Half-cm ticks
            const xHalf = Math.round((i + 0.5) * PX_PER_CM);
            if (xHalf < width) {
                const half = document.createElement('div');
                half.className = 'ruler-tick-line';
                half.style.cssText = `left:${xHalf}px;top:14px;width:1px;height:6px;`;
                ruler.appendChild(half);
            }
        }
    }

    _drawRulerV(ruler, height) {
        ruler.innerHTML = '';
        const PX_PER_CM = 37.8;
        const totalCm = Math.ceil(height / PX_PER_CM);
        for (let i = 0; i <= totalCm; i++) {
            const y = Math.round(i * PX_PER_CM);
            if (y > height) break;
            const line = document.createElement('div');
            line.className = 'ruler-tick-line';
            line.style.cssText = `top:${y}px;left:${10}px;height:1px;width:10px;`;
            ruler.appendChild(line);
            if (i > 0 && i % 2 === 0) {
                const label = document.createElement('div');
                label.className = 'ruler-tick';
                label.style.cssText = `top:${y + 2}px;left:1px;writing-mode:vertical-rl;transform:rotate(180deg);`;
                label.textContent = i + 'cm';
                ruler.appendChild(label);
            }
            const yHalf = Math.round((i + 0.5) * PX_PER_CM);
            if (yHalf < height) {
                const half = document.createElement('div');
                half.className = 'ruler-tick-line';
                half.style.cssText = `top:${yHalf}px;left:14px;height:1px;width:6px;`;
                ruler.appendChild(half);
            }
        }
    }

    setMode(mode) {
        this._mode = mode;
        const arrowBtn = document.getElementById('tbArrow');
        const textboxBtn = document.getElementById('tbAddTextbox');
        if (arrowBtn) arrowBtn.classList.toggle('active', mode === 'select');
        if (textboxBtn) textboxBtn.classList.toggle('active', mode === 'textbox');
        const page = this.pages[this.currentPage];
        if (page) {
            page.canvas.defaultCursor = mode === 'textbox' ? 'text' : 'default';
            page.canvas.hoverCursor = mode === 'textbox' ? 'text' : 'move';
        }
    }

    _addTextboxAt(pageIndex, x, y) {
        const page = this.pages[pageIndex];
        if (!page) return;
        const tb = new fabric.Textbox('Ketik teks di sini...', {
            left: Math.max(page.margins.left, x),
            top: Math.max(page.margins.top, y),
            width: 300,
            fontSize: 14,
            fontFamily: 'Arial',
            fill: '#000000',
        });
        page.canvas.add(tb);
        page.canvas.setActiveObject(tb);
        page.canvas.renderAll();
        tb.enterEditing();
        tb.selectAll();
    }

    _initRulerOverlay(overlay, margins, pageIndex) {
        const self = this;
        const W = this.CANVAS_W;
        const H = this.CANVAS_H;

        const makeRuler = (isHorizontal, position, key) => {
            const ruler = document.createElement('div');
            ruler.style.cssText = `
                position:absolute;
                background: ${isHorizontal ? 'rgba(220,80,80,0.55)' : 'rgba(80,100,220,0.55)'};
                cursor: ${isHorizontal ? 'ns-resize' : 'ew-resize'};
                pointer-events:all;
                z-index:10;
                ${isHorizontal
                    ? `left:0;width:${W}px;height:2px;top:${position}px;`
                    : `top:0;height:${H}px;width:2px;left:${position}px;`
                }
            `;
            ruler.title = 'Drag untuk geser margin';

            let dragging = false;
            let startPos = 0;
            let startMargin = 0;

            ruler.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();
                dragging = true;
                startPos = isHorizontal ? e.clientY : e.clientX;
                startMargin = margins[key];
                document.body.style.cursor = isHorizontal ? 'ns-resize' : 'ew-resize';
            });

            document.addEventListener('mousemove', (e) => {
                if (!dragging) return;
                const delta = isHorizontal ? e.clientY - startPos : e.clientX - startPos;
                let newMargin;
                if (key === 'top') newMargin = Math.max(0, Math.min(H / 2, startMargin + delta));
                else if (key === 'bottom') newMargin = Math.max(0, Math.min(H / 2, startMargin - delta));
                else if (key === 'left') newMargin = Math.max(0, Math.min(W / 2, startMargin + delta));
                else if (key === 'right') newMargin = Math.max(0, Math.min(W / 2, startMargin - delta));

                margins[key] = Math.round(newMargin);

                if (key === 'top') ruler.style.top = margins.top + 'px';
                else if (key === 'bottom') ruler.style.top = (H - margins.bottom) + 'px';
                else if (key === 'left') ruler.style.left = margins.left + 'px';
                else if (key === 'right') ruler.style.left = (W - margins.right) + 'px';
            });

            document.addEventListener('mouseup', () => {
                if (dragging) {
                    dragging = false;
                    document.body.style.cursor = '';
                }
            });

            overlay.appendChild(ruler);
        };

        makeRuler(true, margins.top, 'top');
        makeRuler(true, H - margins.bottom, 'bottom');
        makeRuler(false, margins.left, 'left');
        makeRuler(false, W - margins.right, 'right');
    }

    _constrainObject(obj, margins) {
        if (!obj) return;
        const W = this.CANVAS_W;
        const H = this.CANVAS_H;
        const objW = obj.getScaledWidth();
        const objH = obj.getScaledHeight();

        const minX = margins.left;
        const maxX = W - margins.right - objW;
        const minY = margins.top;
        const maxY = H - margins.bottom - objH;

        let changed = false;
        let newLeft = obj.left;
        let newTop = obj.top;

        if (newLeft < minX) { newLeft = minX; changed = true; }
        if (newLeft > maxX) { newLeft = Math.max(minX, maxX); changed = true; }
        if (newTop < minY) { newTop = minY; changed = true; }
        if (newTop > maxY) { newTop = Math.max(minY, maxY); changed = true; }

        if (changed) obj.set({ left: newLeft, top: newTop });
    }

    _drawRuler(fc) {}

    _checkAutoPage(pageIndex, changedObj) {
        if (!changedObj) return;
        const page = this.pages[pageIndex];
        if (!page) return;
        const fc = page.canvas;
        const THRESHOLD = this.CANVAS_H - page.margins.bottom;

        // Force re-render to get accurate dimensions
        changedObj._clearCache && changedObj._clearCache();
        changedObj.initDimensions && changedObj.initDimensions();

        const objBottom = changedObj.top + changedObj.height * (changedObj.scaleY || 1);
        if (objBottom <= THRESHOLD) return;

        const isText = changedObj.type === 'textbox' || changedObj.type === 'i-text';
        if (!isText) {
            // Non-text: just move to next page
            this._autoPageLock = true;
            const nextPageIndex = pageIndex + 1;
            if (!this.pages[nextPageIndex]) {
                this._buildPageContainer(nextPageIndex);
                this.renderThumbs();
            }
            const nextPage = this.pages[nextPageIndex];
            const nextFc = nextPage.canvas;
            const lastObj = nextFc.getObjects().filter(o => !o._isRuler).slice(-1)[0];
            const insertTop = lastObj ? lastObj.top + lastObj.height + 4 : nextPage.margins.top;
            fc.remove(changedObj);
            const cloned = fabric.util.object.clone(changedObj);
            cloned._layerId = changedObj._layerId;
            cloned._layerName = changedObj._layerName;
            cloned.set({ top: insertTop });
            nextFc.add(cloned);
            fc.renderAll();
            nextFc.renderAll();
            this._autoPageLock = false;
            this.switchPage(nextPageIndex);
            this.renderThumbs();
            return;
        }

        // Text: split by lines
        const textLines = changedObj._textLines;
        if (!textLines || textLines.length <= 1) return;

        // Calculate per-line height
        const totalHeight = changedObj.height * (changedObj.scaleY || 1);
        const lineHeight = totalHeight / textLines.length;

        let splitLineIndex = -1;
        for (let i = 0; i < textLines.length; i++) {
            const lineBottom = changedObj.top + (i + 1) * lineHeight;
            if (lineBottom > THRESHOLD) {
                splitLineIndex = i;
                break;
            }
        }

        if (splitLineIndex <= 0) return;

        this._autoPageLock = true;

        const nextPageIndex = pageIndex + 1;
        if (!this.pages[nextPageIndex]) {
            this._buildPageContainer(nextPageIndex);
            this.renderThumbs();
        }
        const nextPage = this.pages[nextPageIndex];
        const nextFc = nextPage.canvas;
        const lastObj = nextFc.getObjects().filter(o => !o._isRuler).slice(-1)[0];
        const insertTop = lastObj ? lastObj.top + lastObj.height * (lastObj.scaleY || 1) + 4 : nextPage.margins.top;

        const allLines = textLines.map(l => Array.isArray(l) ? l.join('') : String(l));
        const topText = allLines.slice(0, splitLineIndex).join('\n');
        const bottomText = allLines.slice(splitLineIndex).join('\n');

        const wasEditing = changedObj.isEditing;
        if (wasEditing) changedObj.exitEditing();

        changedObj.set({ text: topText });
        changedObj._clearCache && changedObj._clearCache();
        changedObj.initDimensions && changedObj.initDimensions();
        fc.renderAll();

        const overflowTb = new fabric.Textbox(bottomText, {
            left: changedObj.left,
            top: insertTop,
            width: changedObj.width,
            fontSize: changedObj.fontSize,
            fontFamily: changedObj.fontFamily,
            fill: changedObj.fill,
            fontWeight: changedObj.fontWeight,
            fontStyle: changedObj.fontStyle,
            underline: changedObj.underline,
            textAlign: changedObj.textAlign,
            lineHeight: changedObj.lineHeight,
        });
        this._ensureLayerMeta(overflowTb);
        nextFc.add(overflowTb);
        nextFc.renderAll();

        if (wasEditing) {
            fc.setActiveObject(changedObj);
            changedObj.enterEditing();
            changedObj.selectionStart = topText.length;
            changedObj.selectionEnd = topText.length;
            fc.renderAll();
        }

        this._autoPageLock = false;
        this.renderLayers();
        this.renderThumbs();
    }

    _addPage() {
        const index = this.pages.length;
        this._buildPageContainer(index);
    }

    addPage() {
        this._addPage();
        this.switchPage(this.pages.length - 1);
        this.renderThumbs();
    }

    deletePage() {
        if (this.pages.length <= 1) {
            alert('Minimal harus ada 1 halaman.');
            return;
        }
        const index = this.currentPage;
        const page = this.pages[index];
        page.canvas.dispose();
        const grid = document.getElementById('page-grid-' + page.id);
        if (grid) grid.remove();
        this.pages.splice(index, 1);
        const newIndex = Math.max(0, index - 1);
        this.currentPage = -1;
        this.switchPage(newIndex);
        this.renderThumbs();
    }

    switchPage(index) {
        if (index < 0 || index >= this.pages.length) return;
        this.currentPage = index;
        this.pages.forEach((p, i) => {
            const w = document.getElementById('page-wrapper-' + p.id);
            if (w) w.classList.toggle('active-page', i === index);
        });
        const grid = document.getElementById('page-grid-' + this.pages[index].id);
        if (grid) grid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        this.renderThumbs();
        this.renderLayers();
    }

    convertFromHTML(html) {
        const text = html.replace(/<[^>]+>/g, ' ').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();
        if (!text) return;
        const tb = new fabric.Textbox(text, {
            left: 50, top: 50,
            width: this.CANVAS_W - 100,
            fontSize: 14,
            fontFamily: 'Arial',
            fill: '#000000',
        });
        this.pages[0].canvas.add(tb);
        this.pages[0].canvas.renderAll();
    }

    loadFromJSON(data) {
        const self = this;
        self._loading = true;
        let pending = data.pages.length;
        data.pages.forEach((pageData, i) => {
            const fc = self.pages[i].canvas;
            fc.loadFromJSON(pageData.canvasJSON, () => {
                try {
                    fc.getObjects().forEach(o => self._ensureLayerMeta(o));
                    self._drawRuler(fc);
                } catch (err) {
                    console.error('loadFromJSON post-process error:', err);
                }
                fc.renderAll();
                if (i === self.currentPage) self.renderLayers();
                pending--;
                if (pending <= 0) {
                    self._loading = false;
                    self._dirty = false;
                }
            });
        });
    }

    serializeAll() {
        const data = {
            pages: this.pages.map(p => {
                const json = p.canvas.toJSON(['_isRuler', 'excludeFromExport', '_layerId', '_layerName']);
                json.objects = (json.objects || []).filter(o => !o._isRuler);

                return { id: p.id, canvasJSON: json };
            })
        };
        return JSON.stringify(data);
    }

    insertVariable(code) {
        const fc = this.pages[this.currentPage].canvas;
        const tb = new fabric.Textbox(code, {
            left: 100, top: 100,
            fontSize: 14,
            fontFamily: 'Arial',
            fill: '#0d6efd',
            fontStyle: 'italic',
            width: 200,
        });
        fc.add(tb);
        fc.setActiveObject(tb);
        fc.renderAll();
    }

    addText() {
        const fc = this.pages[this.currentPage].canvas;
        const tb = new fabric.Textbox('Ketik teks di sini...', {
            left: 80, top: 80,
            width: 400,
            fontSize: 14,
            fontFamily: 'Arial',
            fill: '#000000',
        });
        fc.add(tb);
        fc.setActiveObject(tb);
        fc.renderAll();
    }

    addImage(file) {
        const self = this;
        const isPng = file.type === 'image/png';
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const MAX_W = 800;
                const MAX_H = 800;
                let w = img.width;
                let h = img.height;
                if (w > MAX_W || h > MAX_H) {
                    const ratio = Math.min(MAX_W / w, MAX_H / h);
                    w = Math.round(w * ratio);
                    h = Math.round(h * ratio);
                }
                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                if (!isPng) {
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, w, h);
                }
                ctx.drawImage(img, 0, 0, w, h);
                const compressed = isPng
                    ? canvas.toDataURL('image/png')
                    : canvas.toDataURL('image/jpeg', 0.7);

                fabric.Image.fromURL(compressed, function(fImg) {
                    if (!fImg) return;
                    fImg.scaleToWidth(Math.min(300, self.CANVAS_W - 120));
                    fImg.set({ left: 100, top: 100 });
                    const fc = self.pages[self.currentPage].canvas;
                    fc.add(fImg);
                    fc.setActiveObject(fImg);
                    fc.renderAll();
                });
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    deleteSelected() {
        const fc = this.pages[this.currentPage].canvas;
        const active = fc.getActiveObjects();
        if (active.length === 0) return;
        active.forEach(obj => fc.remove(obj));
        fc.discardActiveObject();
        fc.renderAll();
    }

    _rememberSelection() {
        const page = this.pages[this.currentPage];
        if (!page) return;
        const obj = page.canvas.getActiveObject();
        if (!obj || (obj.type !== 'textbox' && obj.type !== 'i-text')) {
            this._savedSelection = null;
            return;
        }
        if (obj.isEditing
            && typeof obj.selectionStart === 'number'
            && typeof obj.selectionEnd === 'number'
            && obj.selectionEnd > obj.selectionStart) {
            this._savedSelection = {
                obj,
                start: obj.selectionStart,
                end: obj.selectionEnd,
            };
        } else {
            this._savedSelection = null;
        }
    }

    applyFormat(prop, value) {
        const fc = this.pages[this.currentPage].canvas;
        const obj = fc.getActiveObject();
        if (!obj) return;
        if (obj.type !== 'textbox' && obj.type !== 'i-text') return;

        const isTextProp = ['fontWeight', 'fontStyle', 'underline', 'linethrough', 'overline',
                            'fontFamily', 'fontSize', 'fill'].includes(prop);

        const isAlign = prop === 'textAlign';
        const isWholeObjectProp = prop === 'lineHeight';

        let inEditWithSelection = obj.isEditing
            && typeof obj.selectionStart === 'number'
            && typeof obj.selectionEnd === 'number'
            && obj.selectionEnd > obj.selectionStart;

        // Jika seleksi hilang karena fokus pindah ke kontrol toolbar, pulihkan
        // seleksi yang sempat disimpan agar format diterapkan ke teks terpilih.
        let selStart = obj.selectionStart;
        let selEnd = obj.selectionEnd;
        if (!inEditWithSelection && this._savedSelection && this._savedSelection.obj === obj) {
            selStart = this._savedSelection.start;
            selEnd = this._savedSelection.end;
            inEditWithSelection = true;
        }

        if (isWholeObjectProp) {
            obj.set(prop, value);
        } else if (isAlign) {
            if (inEditWithSelection) {
                this._applyAlignToSelection(obj, value, fc);
            } else {
                obj.set('textAlign', value);
            }
        } else if (isTextProp && inEditWithSelection) {
            const start = selStart;
            const end = selEnd;
            let style = {};
            if (prop === 'fontWeight') {
                const cur = obj.getSelectionStyles(start, end) || [];
                const allBold = cur.length > 0 && cur.every(s => s.fontWeight === 'bold');
                style.fontWeight = allBold ? 'normal' : 'bold';
            } else if (prop === 'fontStyle') {
                const cur = obj.getSelectionStyles(start, end) || [];
                const allItalic = cur.length > 0 && cur.every(s => s.fontStyle === 'italic');
                style.fontStyle = allItalic ? 'normal' : 'italic';
            } else if (prop === 'underline') {
                const cur = obj.getSelectionStyles(start, end) || [];
                const allUnder = cur.length > 0 && cur.every(s => s.underline === true);
                style.underline = !allUnder;
            } else if (prop === 'fontSize') {
                style.fontSize = value;
            } else if (prop === 'fontFamily') {
                style.fontFamily = value;
            } else if (prop === 'fill') {
                style.fill = value;
            }
            obj.setSelectionStyles(style, start, end);

            // Jika seleksi mencakup seluruh teks, terapkan juga pada level objek
            // dan bersihkan style per-karakter. Tanpa ini, fontFamily default objek
            // tetap (mis. Arial) sehingga saat draft dibuka ulang teks bisa kembali
            // ke font lama.
            const wholeText = start === 0 && end >= (obj.text ? obj.text.length : 0);
            if (wholeText) {
                Object.keys(style).forEach((p) => {
                    obj.set(p, style[p]);
                    this._stripCharStyleProp(obj, p);
                });
            }
        } else {
            if (prop === 'fontWeight') {
                obj.set('fontWeight', obj.fontWeight === 'bold' ? 'normal' : 'bold');
            } else if (prop === 'fontStyle') {
                obj.set('fontStyle', obj.fontStyle === 'italic' ? 'normal' : 'italic');
            } else if (prop === 'underline') {
                obj.set('underline', !obj.underline);
            } else {
                obj.set(prop, value);
            }
            if (isTextProp && obj.styles) {
                this._stripCharStyleProp(obj, prop);
            }
        }

        obj._clearCache && obj._clearCache();
        fc.renderAll();
        this._syncToolbarFromSelection();
    }

    _stripCharStyleProp(obj, prop) {
        if (!obj || !obj.styles) return;
        Object.keys(obj.styles).forEach((lineKey) => {
            const line = obj.styles[lineKey];
            if (!line) return;
            Object.keys(line).forEach((charKey) => {
                if (line[charKey] && Object.prototype.hasOwnProperty.call(line[charKey], prop)) {
                    delete line[charKey][prop];
                }
                if (line[charKey] && Object.keys(line[charKey]).length === 0) {
                    delete line[charKey];
                }
            });
            if (Object.keys(line).length === 0) {
                delete obj.styles[lineKey];
            }
        });
    }

    _applyAlignToSelection(obj, align, fc) {
        const fullText = obj.text || '';
        const start = obj.selectionStart;
        const end = obj.selectionEnd;

        // Find line boundaries for selection
        // We split by lines and figure out which lines are touched by [start, end)
        const lines = obj._textLines || obj.text.split('\n');
        let charIndex = 0;
        const lineMeta = [];
        for (let i = 0; i < lines.length; i++) {
            const lineLen = lines[i].length;
            lineMeta.push({ start: charIndex, end: charIndex + lineLen, text: lines[i] });
            charIndex += lineLen + 1; // +1 for newline
        }

        // Which lines are in selection?
        const selectedLines = lineMeta.filter(l => l.end > start && l.start < end);
        if (selectedLines.length === 0) {
            obj.set('textAlign', align);
            fc.renderAll();
            return;
        }

        const firstSelLine = selectedLines[0];
        const lastSelLine = selectedLines[selectedLines.length - 1];

        // Split full text into 3 segments by line boundaries
        const beforeText = fullText.substring(0, firstSelLine.start).replace(/\n$/, '');
        const selText = fullText.substring(firstSelLine.start, lastSelLine.end);
        const afterText = fullText.substring(lastSelLine.end).replace(/^(\n)/, '');

        // Helper to extract styles for a char range
        const extractStyles = (charStart, charEnd) => {
            const result = {};
            for (let i = charStart; i < charEnd; i++) {
                const s = obj.getStyleAtPosition ? obj.getStyleAtPosition(i) : {};
                if (s && Object.keys(s).length > 0) {
                    result[i - charStart] = s;
                }
            }
            return result;
        };

        const baseProps = {
            left: obj.left,
            top: obj.top,
            width: obj.width,
            fontSize: obj.fontSize,
            fontFamily: obj.fontFamily,
            fill: obj.fill,
            fontWeight: obj.fontWeight,
            fontStyle: obj.fontStyle,
            underline: obj.underline,
            lineHeight: obj.lineHeight,
        };

        const page = this.pages[this.currentPage];
        const objIndex = fc.getObjects().indexOf(obj);
        let currentTop = obj.top;

        fc.remove(obj);

        const addSegment = (text, textAlign, charOffset) => {
            if (!text) return null;
            const styles = extractStyles(charOffset, charOffset + text.length);
            const tb = new fabric.Textbox(text, {
                ...baseProps,
                top: currentTop,
                textAlign,
                styles,
            });
            this._ensureLayerMeta(tb);
            fc.add(tb);
            currentTop += tb.getScaledHeight();
            return tb;
        };

        let lastAdded = null;
        if (beforeText) lastAdded = addSegment(beforeText, obj.textAlign || 'left', 0);
        const selTb = addSegment(selText, align, firstSelLine.start);
        if (selTb) lastAdded = selTb;
        if (afterText) lastAdded = addSegment(afterText, obj.textAlign || 'left', lastSelLine.end);

        if (lastAdded) {
            fc.setActiveObject(lastAdded);
        }
        fc.renderAll();
        this.renderLayers();
    }

    _syncToolbarFromSelection() {
        const fc = this.pages[this.currentPage].canvas;
        const obj = fc.getActiveObject();
        if (!obj || (obj.type !== 'textbox' && obj.type !== 'i-text')) return;

        let fontFamily = obj.fontFamily || 'Arial';
        let fontSize = obj.fontSize || 14;
        let fill = obj.fill || '#000000';
        let isBold = obj.fontWeight === 'bold';
        let isItalic = obj.fontStyle === 'italic';
        let isUnderline = !!obj.underline;

        if (obj.isEditing
            && typeof obj.selectionStart === 'number'
            && typeof obj.selectionEnd === 'number'
            && obj.selectionEnd > obj.selectionStart) {
            const styles = obj.getSelectionStyles(obj.selectionStart, obj.selectionEnd) || [];
            if (styles.length > 0) {
                const first = styles[0] || {};
                if (first.fontFamily) fontFamily = first.fontFamily;
                if (first.fontSize) fontSize = first.fontSize;
                if (first.fill) fill = first.fill;
                isBold = styles.every(s => s.fontWeight === 'bold');
                isItalic = styles.every(s => s.fontStyle === 'italic');
                isUnderline = styles.every(s => s.underline === true);
            }
        }

        const ff = document.getElementById('tbFontFamily');
        const fs = document.getElementById('tbFontSize');
        const tc = document.getElementById('tbColor');
        const lh = document.getElementById('tbLineHeight');
        if (ff) ff.value = fontFamily;
        if (fs) fs.value = fontSize;
        if (tc) tc.value = (typeof fill === 'string' && fill.startsWith('#')) ? fill : '#000000';
        if (lh) lh.value = (obj.lineHeight != null ? obj.lineHeight : 1.16);
        const bold = document.getElementById('tbBold');
        const italic = document.getElementById('tbItalic');
        const underline = document.getElementById('tbUnderline');
        const alignLeft = document.getElementById('tbAlignLeft');
        const alignCenter = document.getElementById('tbAlignCenter');
        const alignRight = document.getElementById('tbAlignRight');
        const alignJustify = document.getElementById('tbAlignJustify');
        if (bold) bold.classList.toggle('active', isBold);
        if (italic) italic.classList.toggle('active', isItalic);
        if (underline) underline.classList.toggle('active', isUnderline);
        if (alignLeft) alignLeft.classList.toggle('active', obj.textAlign === 'left');
        if (alignCenter) alignCenter.classList.toggle('active', obj.textAlign === 'center');
        if (alignRight) alignRight.classList.toggle('active', obj.textAlign === 'right');
        if (alignJustify) alignJustify.classList.toggle('active', obj.textAlign === 'justify');
    }

    undo() {
        const page = this.pages[this.currentPage];
        if (page.historyUndo.length <= 1) return;
        const current = page.historyUndo.pop();
        page.historyRedo.push(current);
        const prev = page.historyUndo[page.historyUndo.length - 1];
        const self = this;
        page.canvas.loadFromJSON(prev, () => {
            page.canvas.getObjects().forEach(o => self._ensureLayerMeta(o));
            self._drawRuler(page.canvas);
            page.canvas.renderAll();
            self.renderLayers();
        });
    }

    redo() {
        const page = this.pages[this.currentPage];
        if (page.historyRedo.length === 0) return;
        const next = page.historyRedo.pop();
        page.historyUndo.push(next);
        const self = this;
        page.canvas.loadFromJSON(next, () => {
            page.canvas.getObjects().forEach(o => self._ensureLayerMeta(o));
            self._drawRuler(page.canvas);
            page.canvas.renderAll();
            self.renderLayers();
        });
    }

    renderThumbs() {
        const container = document.getElementById('pageThumbs');
        if (!container) return;
        container.innerHTML = '';
        this.pages.forEach((p, i) => {
            const thumb = document.createElement('div');
            thumb.className = 'page-thumb' + (i === this.currentPage ? ' active' : '');
            thumb.innerHTML = '<div class="page-thumb-preview">' + (i + 1) + '</div>Hal. ' + (i + 1);
            thumb.addEventListener('click', () => this.switchPage(i));
            container.appendChild(thumb);
        });
    }

    _ensureLayerMeta(obj) {
        if (!obj) return;
        // Textbox hasil impor bisa tidak punya properti `styles`. Tanpa ini,
        // Fabric 5.5.x crash di stylesToArray saat toJSON()/toObject().
        const isText = obj.type === 'textbox' || obj.type === 'i-text' || obj.type === 'text';
        if (isText && (obj.styles === undefined || obj.styles === null)) {
            obj.styles = {};
        }
        if (!obj._layerId) {
            obj._layerId = 'L' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
        }
        if (!obj._layerName) {
            obj._layerName = this._defaultLayerName(obj);
        }
    }

    _defaultLayerName(obj) {
        if (!obj) return 'Layer';
        if (obj.type === 'textbox' || obj.type === 'i-text' || obj.type === 'text') {
            const t = (obj.text || '').replace(/\s+/g, ' ').trim();
            return t ? (t.length > 22 ? t.slice(0, 22) + '…' : t) : 'Teks';
        }
        if (obj.type === 'image') return 'Gambar';
        if (obj.type === 'rect') return 'Persegi';
        if (obj.type === 'circle') return 'Lingkaran';
        if (obj.type === 'line') return 'Garis';
        if (obj.type === 'group') return 'Grup';
        return obj.type ? obj.type.charAt(0).toUpperCase() + obj.type.slice(1) : 'Layer';
    }

    _layerIcon(obj) {
        if (!obj) return 'ti-square';
        if (obj.type === 'textbox' || obj.type === 'i-text' || obj.type === 'text') return 'ti-text-size';
        if (obj.type === 'image') return 'ti-photo';
        if (obj.type === 'rect') return 'ti-square';
        if (obj.type === 'circle') return 'ti-circle';
        if (obj.type === 'line') return 'ti-line';
        if (obj.type === 'group') return 'ti-stack-2';
        return 'ti-shape';
    }

    renderLayers() {
        const container = document.getElementById('layersList');
        const empty = document.getElementById('layersEmpty');
        if (!container) return;

        const page = this.pages[this.currentPage];
        if (!page) {
            container.innerHTML = '';
            if (empty) empty.style.display = '';
            return;
        }

        const fc = page.canvas;
        const objects = fc.getObjects().filter(o => !o._isRuler && !o.excludeFromExport);
        const active = fc.getActiveObjects();
        const activeIds = new Set(active.map(o => o._layerId));

        container.innerHTML = '';
        if (objects.length === 0) {
            if (empty) empty.style.display = '';
            return;
        }
        if (empty) empty.style.display = 'none';

        // Render top z-order first (Photoshop convention: top of list = top layer)
        const ordered = [...objects].reverse();
        const self = this;

        ordered.forEach((obj) => {
            self._ensureLayerMeta(obj);

            const item = document.createElement('div');
            item.className = 'layer-item' + (activeIds.has(obj._layerId) ? ' active' : '');
            item.draggable = true;
            item.dataset.layerId = obj._layerId;

            const handle = document.createElement('span');
            handle.className = 'layer-handle';
            handle.innerHTML = '<i class="ti ti-grip-vertical"></i>';
            handle.title = 'Geser untuk ubah urutan';

            const visBtn = document.createElement('button');
            visBtn.type = 'button';
            visBtn.className = 'layer-btn' + (obj.visible === false ? ' muted' : '');
            visBtn.title = obj.visible === false ? 'Tampilkan' : 'Sembunyikan';
            visBtn.innerHTML = obj.visible === false ? '<i class="ti ti-eye-off"></i>' : '<i class="ti ti-eye"></i>';
            visBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                self.toggleLayerVisibility(obj._layerId);
            });

            const lockBtn = document.createElement('button');
            lockBtn.type = 'button';
            lockBtn.className = 'layer-btn' + (obj.lockMovementX ? '' : ' muted');
            lockBtn.title = obj.lockMovementX ? 'Buka kunci' : 'Kunci layer';
            lockBtn.innerHTML = obj.lockMovementX ? '<i class="ti ti-lock"></i>' : '<i class="ti ti-lock-open"></i>';
            lockBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                self.toggleLayerLock(obj._layerId);
            });

            const icon = document.createElement('span');
            icon.className = 'layer-icon';
            icon.innerHTML = '<i class="ti ' + self._layerIcon(obj) + '"></i>';

            const name = document.createElement('span');
            name.className = 'layer-name';
            name.textContent = obj._layerName || self._defaultLayerName(obj);
            name.title = name.textContent + ' (klik dua kali untuk rename)';
            name.addEventListener('dblclick', (e) => {
                e.stopPropagation();
                name.contentEditable = 'true';
                name.focus();
                document.execCommand('selectAll', false, null);
            });
            name.addEventListener('blur', () => {
                if (name.contentEditable === 'true') {
                    name.contentEditable = 'false';
                    const newName = (name.textContent || '').trim() || self._defaultLayerName(obj);
                    obj._layerName = newName;
                    name.textContent = newName;
                }
            });
            name.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); name.blur(); }
                if (e.key === 'Escape') {
                    name.textContent = obj._layerName || self._defaultLayerName(obj);
                    name.blur();
                }
            });

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'layer-btn';
            delBtn.title = 'Hapus layer';
            delBtn.innerHTML = '<i class="ti ti-trash"></i>';
            delBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                self.deleteLayer(obj._layerId);
            });

            item.appendChild(handle);
            item.appendChild(icon);
            item.appendChild(name);
            item.appendChild(visBtn);
            item.appendChild(lockBtn);
            item.appendChild(delBtn);

            item.addEventListener('click', () => {
                if (name.contentEditable === 'true') return;
                self.selectLayer(obj._layerId);
            });

            // Drag & drop reorder
            item.addEventListener('dragstart', (e) => {
                item.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', obj._layerId);
            });
            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
                container.querySelectorAll('.layer-item').forEach(el => el.classList.remove('drag-over'));
            });
            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                item.classList.add('drag-over');
            });
            item.addEventListener('dragleave', () => item.classList.remove('drag-over'));
            item.addEventListener('drop', (e) => {
                e.preventDefault();
                item.classList.remove('drag-over');
                const draggedId = e.dataTransfer.getData('text/plain');
                if (draggedId && draggedId !== obj._layerId) {
                    self.reorderLayer(draggedId, obj._layerId);
                }
            });

            container.appendChild(item);
        });
    }

    _findLayer(layerId) {
        const page = this.pages[this.currentPage];
        if (!page) return null;
        return page.canvas.getObjects().find(o => o._layerId === layerId) || null;
    }

    selectLayer(layerId) {
        const page = this.pages[this.currentPage];
        if (!page) return;
        const obj = this._findLayer(layerId);
        if (!obj || obj.visible === false) return;
        page.canvas.setActiveObject(obj);
        page.canvas.requestRenderAll();
        this.renderLayers();
        this._syncToolbarFromSelection();
    }

    toggleLayerVisibility(layerId) {
        const page = this.pages[this.currentPage];
        if (!page) return;
        const obj = this._findLayer(layerId);
        if (!obj) return;
        const becomingHidden = obj.visible !== false;
        obj.visible = !becomingHidden;
        if (becomingHidden && page.canvas.getActiveObject() === obj) {
            page.canvas.discardActiveObject();
        }
        page.canvas.requestRenderAll();
        this.renderLayers();
    }

    toggleLayerLock(layerId) {
        const page = this.pages[this.currentPage];
        if (!page) return;
        const obj = this._findLayer(layerId);
        if (!obj) return;
        const locked = !!obj.lockMovementX;
        const next = !locked;
        obj.set({
            lockMovementX: next,
            lockMovementY: next,
            lockScalingX: next,
            lockScalingY: next,
            lockRotation: next,
            selectable: !next || true,
            evented: true,
            hasControls: !next,
        });
        if (next && page.canvas.getActiveObject() === obj) {
            page.canvas.discardActiveObject();
        }
        page.canvas.requestRenderAll();
        this.renderLayers();
    }

    deleteLayer(layerId) {
        const page = this.pages[this.currentPage];
        if (!page) return;
        const obj = this._findLayer(layerId);
        if (!obj) return;
        if (!confirm('Hapus layer "' + (obj._layerName || 'ini') + '"?')) return;
        page.canvas.remove(obj);
        page.canvas.discardActiveObject();
        page.canvas.requestRenderAll();
        this.renderLayers();
    }

    reorderLayer(draggedId, targetId) {
        const page = this.pages[this.currentPage];
        if (!page) return;
        const fc = page.canvas;
        const dragged = this._findLayer(draggedId);
        const target = this._findLayer(targetId);
        if (!dragged || !target) return;

        const objects = fc.getObjects();
        const targetIndex = objects.indexOf(target);
        if (targetIndex < 0) return;

        // Top of UI list = top z-order. Dropping onto target means: place dragged
        // immediately above target in z-order.
        fc.moveTo(dragged, targetIndex + 1);
        fc.requestRenderAll();
        this.renderLayers();
    }

    bringForward() {
        const page = this.pages[this.currentPage];
        if (!page) return;
        const obj = page.canvas.getActiveObject();
        if (!obj) return;
        page.canvas.bringForward(obj);
        page.canvas.requestRenderAll();
        this.renderLayers();
    }

    sendBackwards() {
        const page = this.pages[this.currentPage];
        if (!page) return;
        const obj = page.canvas.getActiveObject();
        if (!obj) return;
        page.canvas.sendBackwards(obj);
        page.canvas.requestRenderAll();
        this.renderLayers();
    }

    bringToFront() {
        const page = this.pages[this.currentPage];
        if (!page) return;
        const obj = page.canvas.getActiveObject();
        if (!obj) return;
        page.canvas.bringToFront(obj);
        page.canvas.requestRenderAll();
        this.renderLayers();
    }

    sendToBack() {
        const page = this.pages[this.currentPage];
        if (!page) return;
        const obj = page.canvas.getActiveObject();
        if (!obj) return;
        page.canvas.sendToBack(obj);
        page.canvas.requestRenderAll();
        this.renderLayers();
    }

    _attachTextSelectionSync(e) {
        const target = e && (e.target || (e.selected && e.selected[0]));
        if (!target) return;
        if (target.type !== 'textbox' && target.type !== 'i-text') return;
        if (target._kiloSelSyncBound) return;
        target._kiloSelSyncBound = true;
        const self = this;
        target.on('selection:changed', () => self._syncToolbarFromSelection());
        target.on('editing:entered', () => self._syncToolbarFromSelection());
        target.on('editing:exited', () => self._syncToolbarFromSelection());
    }

    _bindKeyboardShortcuts() {
        const self = this;
        document.addEventListener('keydown', (e) => {
            const isMeta = e.ctrlKey || e.metaKey;
            const tag = (e.target && e.target.tagName) || '';
            const isFormField = ['INPUT', 'SELECT', 'TEXTAREA'].includes(tag)
                || (e.target && e.target.isContentEditable);

            const fc = self.pages[self.currentPage] && self.pages[self.currentPage].canvas;
            const active = fc ? fc.getActiveObject() : null;
            const isEditingText = active
                && (active.type === 'textbox' || active.type === 'i-text')
                && active.isEditing;

            const key = e.key;
            const keyLower = key.toLowerCase();

            // Delete / Backspace — delete selected object (not while editing text)
            if ((key === 'Delete' || key === 'Backspace') && !isEditingText && !isFormField) {
                e.preventDefault();
                self.deleteSelected();
                return;
            }

            // V — switch to select mode
            if (key === 'v' && !isMeta && !isEditingText && !isFormField) {
                self.setMode('select');
                return;
            }

            // T — switch to textbox mode
            if (key === 't' && !isMeta && !isEditingText && !isFormField) {
                self.setMode('textbox');
                return;
            }

            // Escape — cancel textbox mode
            if (key === 'Escape' && self._mode === 'textbox') {
                self.setMode('select');
                return;
            }

            if (!isMeta) return;

            if (keyLower === 'z' && !e.shiftKey) {
                if (isFormField && !isEditingText) return;
                e.preventDefault();
                self.undo();
                return;
            }
            if ((keyLower === 'y') || (keyLower === 'z' && e.shiftKey)) {
                if (isFormField && !isEditingText) return;
                e.preventDefault();
                self.redo();
                return;
            }
            if (keyLower === 'b') {
                if (!active || (active.type !== 'textbox' && active.type !== 'i-text')) return;
                e.preventDefault();
                self.applyFormat('fontWeight');
                return;
            }
            if (keyLower === 'i') {
                if (!active || (active.type !== 'textbox' && active.type !== 'i-text')) return;
                e.preventDefault();
                self.applyFormat('fontStyle');
                return;
            }
            if (keyLower === 'u') {
                if (!active || (active.type !== 'textbox' && active.type !== 'i-text')) return;
                e.preventDefault();
                self.applyFormat('underline');
                return;
            }
        });
    }

    _bindToolbar() {
        const self = this;
        const get = id => document.getElementById(id);

        get('tbArrow') && get('tbArrow').addEventListener('click', () => self.setMode('select'));
        get('tbDeleteObj') && get('tbDeleteObj').addEventListener('click', () => self.deleteSelected());

        get('tbAddText') && get('tbAddText').addEventListener('click', () => self.addText());
        get('tbDelete') && get('tbDelete').addEventListener('click', () => self.deleteSelected());
        get('tbUndo') && get('tbUndo').addEventListener('click', () => self.undo());
        get('tbRedo') && get('tbRedo').addEventListener('click', () => self.redo());
        get('tbAddPage') && get('tbAddPage').addEventListener('click', () => self.addPage());
        get('tbDeletePage') && get('tbDeletePage').addEventListener('click', () => self.deletePage());

        get('tbBold') && get('tbBold').addEventListener('mousedown', (e) => e.preventDefault());
        get('tbItalic') && get('tbItalic').addEventListener('mousedown', (e) => e.preventDefault());
        get('tbUnderline') && get('tbUnderline').addEventListener('mousedown', (e) => e.preventDefault());
        get('tbAlignLeft') && get('tbAlignLeft').addEventListener('mousedown', (e) => e.preventDefault());
        get('tbAlignCenter') && get('tbAlignCenter').addEventListener('mousedown', (e) => e.preventDefault());
        get('tbAlignRight') && get('tbAlignRight').addEventListener('mousedown', (e) => e.preventDefault());
        get('tbAlignJustify') && get('tbAlignJustify').addEventListener('mousedown', (e) => e.preventDefault());

        // Font family, font size, dan color adalah elemen form yang harus bisa
        // diklik/dibuka. Jangan preventDefault mousedown-nya. Sebagai gantinya,
        // simpan seleksi teks aktif sebelum fokus berpindah ke kontrol toolbar.
        ['tbFontFamily', 'tbFontSize', 'tbColor'].forEach(id => {
            const el = get(id);
            if (!el) return;
            el.addEventListener('pointerdown', () => self._rememberSelection());
            el.addEventListener('focus', () => self._rememberSelection());
        });

        get('tbBold') && get('tbBold').addEventListener('click', () => self.applyFormat('fontWeight'));
        get('tbItalic') && get('tbItalic').addEventListener('click', () => self.applyFormat('fontStyle'));
        get('tbUnderline') && get('tbUnderline').addEventListener('click', () => self.applyFormat('underline'));

        get('tbFontFamily') && get('tbFontFamily').addEventListener('change', function() {
            self.applyFormat('fontFamily', this.value);
        });
        get('tbFontSize') && get('tbFontSize').addEventListener('change', function() {
            const v = parseInt(this.value);
            if (!isNaN(v)) self.applyFormat('fontSize', v);
        });
        get('tbColor') && get('tbColor').addEventListener('input', function() {
            self.applyFormat('fill', this.value);
        });

        get('tbLineHeight') && get('tbLineHeight').addEventListener('pointerdown', () => self._rememberSelection());
        get('tbLineHeight') && get('tbLineHeight').addEventListener('change', function() {
            const v = parseFloat(this.value);
            if (!isNaN(v)) self.applyFormat('lineHeight', v);
        });

        get('tbAlignLeft') && get('tbAlignLeft').addEventListener('mousedown', (e) => { e.preventDefault(); self.applyFormat('textAlign', 'left'); });
        get('tbAlignCenter') && get('tbAlignCenter').addEventListener('mousedown', (e) => { e.preventDefault(); self.applyFormat('textAlign', 'center'); });
        get('tbAlignRight') && get('tbAlignRight').addEventListener('mousedown', (e) => { e.preventDefault(); self.applyFormat('textAlign', 'right'); });
        get('tbAlignJustify') && get('tbAlignJustify').addEventListener('mousedown', (e) => { e.preventDefault(); self.applyFormat('textAlign', 'justify'); });

        get('tbAddImage') && get('tbAddImage').addEventListener('click', () => get('tbImageInput').click());
        get('tbImageInput') && get('tbImageInput').addEventListener('change', function() {
            if (this.files[0]) self.addImage(this.files[0]);
            this.value = '';
        });

        get('layerMoveUp') && get('layerMoveUp').addEventListener('click', () => self.bringForward());
        get('layerMoveDown') && get('layerMoveDown').addEventListener('click', () => self.sendBackwards());
        get('layerToFront') && get('layerToFront').addEventListener('click', () => self.bringToFront());
        get('layerToBack') && get('layerToBack').addEventListener('click', () => self.sendToBack());

        self._bindKeyboardShortcuts();
    }
}
