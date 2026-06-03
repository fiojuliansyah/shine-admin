class FabricLetterEditor {
    constructor() {
        this.pages = [];
        this.currentPage = 0;
        this.CANVAS_W = 794;
        this.CANVAS_H = 1123;
        this.MAX_HISTORY = 30;
        this._autoPageLock = false;
    }

    init(savedData) {
        if (savedData && savedData.trim() !== '') {
            try {
                const parsed = JSON.parse(savedData);
                if (parsed.pages && Array.isArray(parsed.pages)) {
                    parsed.pages.forEach((_, i) => this._buildPageContainer(i));
                    this.loadFromJSON(parsed);
                } else {
                    this._buildPageContainer(0);
                    this.convertFromHTML(savedData);
                }
            } catch (e) {
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
    }

    _buildPageContainer(index) {
        const container = document.getElementById('canvasContainer');
        const wrapper = document.createElement('div');
        wrapper.id = 'page-wrapper-' + index;
        wrapper.className = 'canvas-page-wrapper';
        wrapper.style.cssText = 'position:relative;display:inline-block;';

        const canvasEl = document.createElement('canvas');
        canvasEl.id = 'fabricCanvas-' + index;
        wrapper.appendChild(canvasEl);

        const rulerOverlay = document.createElement('div');
        rulerOverlay.id = 'ruler-overlay-' + index;
        rulerOverlay.style.cssText = 'position:absolute;top:0;left:0;width:' + this.CANVAS_W + 'px;height:' + this.CANVAS_H + 'px;pointer-events:none;overflow:hidden;';
        wrapper.appendChild(rulerOverlay);

        container.appendChild(wrapper);

        const fc = new fabric.Canvas('fabricCanvas-' + index, {
            width: this.CANVAS_W,
            height: this.CANVAS_H,
            backgroundColor: '#fff',
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
            self.renderLayers();
        });
        fc.on('object:removed', () => {
            saveHistory();
            self.renderLayers();
        });
        fc.on('object:modified', (e) => {
            saveHistory();
            self._constrainObject(e.target, margins);
            if (!self._autoPageLock) self._checkAutoPage(index, e.target);
            self.renderLayers();
        });
        fc.on('object:moving', (e) => {
            self._constrainObject(e.target, margins);
        });
        fc.on('text:changed', (e) => {
            if (!self._autoPageLock) self._checkAutoPage(index, e.target);
            self.renderLayers();
        });
        fc.on('selection:created', () => { self._syncToolbarFromSelection(); self.renderLayers(); });
        fc.on('selection:updated', () => { self._syncToolbarFromSelection(); self.renderLayers(); });
        fc.on('selection:cleared', () => { self._syncToolbarFromSelection(); self.renderLayers(); });
        wrapper.addEventListener('click', () => self.switchPage(self.pages.findIndex(p => p.id === index)));

        return fc;
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
        const MARGIN = 40;
        const THRESHOLD = this.CANVAS_H - MARGIN;

        const objBottom = changedObj.top + changedObj.getScaledHeight();
        if (objBottom <= THRESHOLD) return;

        this._autoPageLock = true;

        const overflowObjs = fc.getObjects().filter(obj => {
            return (obj.top + obj.getScaledHeight()) > THRESHOLD;
        });

        if (overflowObjs.length === 0) { this._autoPageLock = false; return; }

        const nextPageIndex = pageIndex + 1;
        let nextPage = this.pages[nextPageIndex];

        if (!nextPage) {
            this._buildPageContainer(nextPageIndex);
            nextPage = this.pages[nextPageIndex];
            this.renderThumbs();
        }

        const nextFc = nextPage.canvas;
        const existingTops = nextFc.getObjects().map(o => o.top);
        const lowestExisting = existingTops.length > 0 ? Math.max(...existingTops) : 0;
        const lastObj = nextFc.getObjects().slice(-1)[0];
        let insertTop = lastObj ? lastObj.top + lastObj.getScaledHeight() + 10 : 40;

        overflowObjs.forEach(obj => {
            fc.remove(obj);
            const cloned = fabric.util.object.clone(obj);
            cloned._layerId = obj._layerId;
            cloned._layerName = obj._layerName;
            const overflowAmount = obj.top - THRESHOLD;
            cloned.set({ top: Math.max(40, insertTop), left: obj.left });
            insertTop += cloned.getScaledHeight() + 10;
            nextFc.add(cloned);
        });

        fc.discardActiveObject();
        fc.renderAll();
        nextFc.discardActiveObject();
        nextFc.renderAll();

        this._autoPageLock = false;

        this.switchPage(nextPageIndex);
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
        const wrapper = document.getElementById('page-wrapper-' + page.id);
        if (wrapper) wrapper.remove();
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
        const wrapper = document.getElementById('page-wrapper-' + this.pages[index].id);
        if (wrapper) wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
        data.pages.forEach((pageData, i) => {
            const fc = self.pages[i].canvas;
            fc.loadFromJSON(pageData.canvasJSON, () => {
                fc.getObjects().forEach(o => self._ensureLayerMeta(o));
                self._drawRuler(fc);
                fc.renderAll();
                if (i === self.currentPage) self.renderLayers();
            });
        });
    }

    serializeAll() {
        const data = {
            pages: this.pages.map(p => {
                const json = p.canvas.toJSON(['_isRuler', 'excludeFromExport', '_layerId', '_layerName']);
                json.objects = (json.objects || []).filter(o => !o._isRuler);

                const overlay = document.getElementById('ruler-overlay-' + p.id);
                if (overlay) overlay.style.display = 'none';
                p.canvas.discardActiveObject();
                p.canvas.renderAll();
                const pageImage = p.canvas.toDataURL({ format: 'png', multiplier: 1 });
                if (overlay) overlay.style.display = '';
                p.canvas.renderAll();

                return { id: p.id, canvasJSON: json, pageImage };
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

    applyFormat(prop, value) {
        const fc = this.pages[this.currentPage].canvas;
        const obj = fc.getActiveObject();
        if (!obj) return;
        if (obj.type === 'textbox' || obj.type === 'i-text') {
            if (prop === 'fontWeight') {
                obj.set('fontWeight', obj.fontWeight === 'bold' ? 'normal' : 'bold');
            } else if (prop === 'fontStyle') {
                obj.set('fontStyle', obj.fontStyle === 'italic' ? 'normal' : 'italic');
            } else if (prop === 'underline') {
                obj.set('underline', !obj.underline);
            } else {
                obj.set(prop, value);
            }
        }
        fc.renderAll();
        this._syncToolbarFromSelection();
    }

    _syncToolbarFromSelection() {
        const fc = this.pages[this.currentPage].canvas;
        const obj = fc.getActiveObject();
        if (!obj || (obj.type !== 'textbox' && obj.type !== 'i-text')) return;
        const ff = document.getElementById('tbFontFamily');
        const fs = document.getElementById('tbFontSize');
        const tc = document.getElementById('tbColor');
        if (ff) ff.value = obj.fontFamily || 'Arial';
        if (fs) fs.value = obj.fontSize || 14;
        if (tc) tc.value = obj.fill || '#000000';
        const bold = document.getElementById('tbBold');
        const italic = document.getElementById('tbItalic');
        const underline = document.getElementById('tbUnderline');
        const alignLeft = document.getElementById('tbAlignLeft');
        const alignCenter = document.getElementById('tbAlignCenter');
        const alignRight = document.getElementById('tbAlignRight');
        const alignJustify = document.getElementById('tbAlignJustify');
        if (bold) bold.classList.toggle('active', obj.fontWeight === 'bold');
        if (italic) italic.classList.toggle('active', obj.fontStyle === 'italic');
        if (underline) underline.classList.toggle('active', !!obj.underline);
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

    _bindToolbar() {
        const self = this;
        const get = id => document.getElementById(id);

        get('tbAddText') && get('tbAddText').addEventListener('click', () => self.addText());
        get('tbDelete') && get('tbDelete').addEventListener('click', () => self.deleteSelected());
        get('tbUndo') && get('tbUndo').addEventListener('click', () => self.undo());
        get('tbRedo') && get('tbRedo').addEventListener('click', () => self.redo());
        get('tbAddPage') && get('tbAddPage').addEventListener('click', () => self.addPage());
        get('tbDeletePage') && get('tbDeletePage').addEventListener('click', () => self.deletePage());

        get('tbBold') && get('tbBold').addEventListener('click', () => self.applyFormat('fontWeight'));
        get('tbItalic') && get('tbItalic').addEventListener('click', () => self.applyFormat('fontStyle'));
        get('tbUnderline') && get('tbUnderline').addEventListener('click', () => self.applyFormat('underline'));

        get('tbFontFamily') && get('tbFontFamily').addEventListener('change', function() {
            self.applyFormat('fontFamily', this.value);
        });
        get('tbFontSize') && get('tbFontSize').addEventListener('change', function() {
            self.applyFormat('fontSize', parseInt(this.value));
        });
        get('tbColor') && get('tbColor').addEventListener('input', function() {
            self.applyFormat('fill', this.value);
        });

        get('tbAlignLeft') && get('tbAlignLeft').addEventListener('click', () => self.applyFormat('textAlign', 'left'));
        get('tbAlignCenter') && get('tbAlignCenter').addEventListener('click', () => self.applyFormat('textAlign', 'center'));
        get('tbAlignRight') && get('tbAlignRight').addEventListener('click', () => self.applyFormat('textAlign', 'right'));
        get('tbAlignJustify') && get('tbAlignJustify').addEventListener('click', () => self.applyFormat('textAlign', 'justify'));

        get('tbAddImage') && get('tbAddImage').addEventListener('click', () => get('tbImageInput').click());
        get('tbImageInput') && get('tbImageInput').addEventListener('change', function() {
            if (this.files[0]) self.addImage(this.files[0]);
            this.value = '';
        });

        get('layerMoveUp') && get('layerMoveUp').addEventListener('click', () => self.bringForward());
        get('layerMoveDown') && get('layerMoveDown').addEventListener('click', () => self.sendBackwards());
        get('layerToFront') && get('layerToFront').addEventListener('click', () => self.bringToFront());
        get('layerToBack') && get('layerToBack').addEventListener('click', () => self.sendToBack());
    }
}
