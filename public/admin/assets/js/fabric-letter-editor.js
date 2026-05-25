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

        fc.on('object:added', saveHistory);
        fc.on('object:removed', saveHistory);
        fc.on('object:modified', (e) => {
            saveHistory();
            self._constrainObject(e.target, margins);
            if (!self._autoPageLock) self._checkAutoPage(index, e.target);
        });
        fc.on('object:moving', (e) => {
            self._constrainObject(e.target, margins);
        });
        fc.on('text:changed', (e) => {
            if (!self._autoPageLock) self._checkAutoPage(index, e.target);
        });
        fc.on('selection:created', () => self._syncToolbarFromSelection());
        fc.on('selection:updated', () => self._syncToolbarFromSelection());
        fc.on('selection:cleared', () => self._syncToolbarFromSelection());
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
                self._drawRuler(fc);
                fc.renderAll();
            });
        });
    }

    serializeAll() {
        const data = {
            pages: this.pages.map(p => {
                const json = p.canvas.toJSON(['_isRuler', 'excludeFromExport']);
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
        const reader = new FileReader();
        reader.onload = function(e) {
            fabric.Image.fromURL(e.target.result, function(img) {
                img.scaleToWidth(300);
                img.set({ left: 100, top: 100 });
                const fc = self.pages[self.currentPage].canvas;
                fc.add(img);
                fc.setActiveObject(img);
                fc.renderAll();
            });
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
            self._drawRuler(page.canvas);
            page.canvas.renderAll();
        });
    }

    redo() {
        const page = this.pages[this.currentPage];
        if (page.historyRedo.length === 0) return;
        const next = page.historyRedo.pop();
        page.historyUndo.push(next);
        const self = this;
        page.canvas.loadFromJSON(next, () => {
            self._drawRuler(page.canvas);
            page.canvas.renderAll();
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
    }
}
