/**
 * EscaparateWidget — Configurador visual de escaparates comerciales
 * Sistema de paneles en banda lineal (izquierda → derecha)
 *
 * Estado: { totalWidth, totalHeight, tachaHeight, modules: [{id, type, width, opening, label}] }
 */
window.EscaparateWidget = (function () {

    // ── TIPOS ─────────────────────────────────────────────────────────────
    const TYPES = {
        puerta:         { name: 'Puerta',         fill: '#d8ecf8', stroke: '#2a6080', tag: 'P' },
        fijo:           { name: 'Fijo',           fill: '#d8ecf8', stroke: '#2a6080', tag: 'F' },
        corredera:      { name: 'Corredera',      fill: '#e4d8f4', stroke: '#5a38a0', tag: 'C' },
        oscilobatiente: { name: 'Oscilobatiente', fill: '#f4f0d8', stroke: '#a07828', tag: 'O' },
        abatible:       { name: 'Abatible',       fill: '#d8f4dc', stroke: '#2a8040', tag: 'A' },
    };
    const NEEDS_OPENING = ['puerta', 'corredera', 'oscilobatiente', 'abatible'];

    // ── ESTADO ─────────────────────────────────────────────────────────────
    let state = {
        totalWidth:  6500,
        totalHeight: 2200,
        tachaHeight: 0,
        modules: [
            { id: 'M1', type: 'puerta', width: 1000, opening: 'izquierda', label: '' },
            { id: 'M2', type: 'fijo',   width: 5500, opening: '',          label: '' },
        ],
    };
    let opts      = {};
    let moduleSeq = 3;
    let onChanged = null;

    function uid()  { return 'M' + (moduleSeq++); }
    function el(id) { return document.getElementById(id); }

    // ── SVG ────────────────────────────────────────────────────────────────
    const SVG_W = 900, SVG_H = 480;
    const ML = 72, MR = 44, MT = 32, MB = 70;   // márgenes para cotas

    function buildSVG() {
        const tw = state.totalWidth  || 1;
        const th = state.totalHeight || 1;
        const tachaH = Math.max(0, state.tachaHeight || 0);

        const avW = SVG_W - ML - MR;
        const avH = SVG_H - MT - MB;
        const sc  = Math.min(avW / tw, avH / th);

        const W = tw * sc, H = th * sc;
        const ox = ML + (avW - W) / 2;
        const oy = MT + (avH - H) / 2;

        const tachaHpx = Math.min(tachaH * sc, H * 0.35);
        const glassHpx = H - tachaHpx;

        // Módulos activos (sin tipo 'tacha' si alguien lo pusiera)
        const mods = state.modules.filter(m => TYPES[m.type]);
        const totalModW = mods.reduce((s, m) => s + (m.width || 0), 0) || 1;

        const FRAME = 6;  // grosor del perfil exterior (px)
        const BAR   = 4;  // grosor del montante entre paneles (px)

        let s = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${SVG_W} ${SVG_H}" width="${SVG_W}" height="${SVG_H}">`;

        // ── DEFS ──────────────────────────────────────────────────────────
        s += `<defs>
          <pattern id="esc-glass" patternUnits="userSpaceOnUse" width="18" height="18">
            <rect width="18" height="18" fill="#d4edf8"/>
            <line x1="0" y1="18" x2="18" y2="0" stroke="#5a9ec0" stroke-width="0.7" opacity="0.55"/>
            <line x1="0" y1="0"  x2="18" y2="18" stroke="#5a9ec0" stroke-width="0.7" opacity="0.55"/>
          </pattern>
          <pattern id="esc-tacha" patternUnits="userSpaceOnUse" width="8" height="8">
            <rect width="8" height="8" fill="#c8cdd2"/>
          </pattern>
          <marker id="esc-arrR" markerWidth="6" markerHeight="6" refX="6" refY="3" orient="auto">
            <polygon points="0,0 6,3 0,6" fill="#2a3840"/>
          </marker>
          <marker id="esc-arrL" markerWidth="6" markerHeight="6" refX="0" refY="3" orient="auto-start-reverse">
            <polygon points="0,0 6,3 0,6" fill="#2a3840"/>
          </marker>
        </defs>`;

        // ── MARCO EXTERIOR ────────────────────────────────────────────────
        s += `<rect x="${ox - FRAME}" y="${oy - FRAME}" width="${W + FRAME*2}" height="${H + FRAME*2}" fill="#2a3840" rx="3"/>`;

        // ── ÁREA DE TACHA (banda inferior) ────────────────────────────────
        if (tachaHpx > 0.5) {
            s += `<rect x="${ox}" y="${oy + glassHpx}" width="${W}" height="${tachaHpx}" fill="#c8cdd3"/>`;
            // Línea divisoria entre zona vidrio y tacha
            s += `<rect x="${ox}" y="${oy + glassHpx - BAR/2}" width="${W}" height="${BAR}" fill="#2a3840"/>`;
        }

        // ── PANELES (izquierda → derecha) ─────────────────────────────────
        let cx = ox;
        mods.forEach((mod, i) => {
            const mWpx = (mod.width / totalModW) * W;

            // Montante entre paneles
            if (i > 0) {
                s += `<rect x="${cx - BAR/2}" y="${oy}" width="${BAR}" height="${glassHpx}" fill="#2a3840"/>`;
            }

            // Área de vidrio del panel
            const px = cx, py = oy, pw = mWpx, ph = glassHpx;
            const gi = 3;  // inner gap (vidrio inset)
            const gx = px + gi, gy = py + gi, gw = pw - gi*2, gh = ph - gi*2;

            if (gw > 2 && gh > 2) {
                // Fondo vidrio
                s += `<rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="url(#esc-glass)"/>`;

                // Indicador según tipo
                s += renderTypeIndicator(mod, gx, gy, gw, gh);
            }

            cx += mWpx;
        });

        // ── COTAS ─────────────────────────────────────────────────────────
        const dimBaseY = oy + H + FRAME + 14;
        const dimLineY = dimBaseY + 12;

        // H1 total (debajo)
        s += dimLine(ox, dimLineY, ox + W, dimLineY, `H1=${tw}`, true, true);

        // Cotas por módulo (encima de H1 si hay más de 1)
        if (mods.length > 1) {
            const subDimY = dimLineY - 20;
            let mx = ox;
            mods.forEach((mod, i) => {
                const mWpx = (mod.width / totalModW) * W;
                const label = (mod.label || `H${i+2}`) + `=${mod.width}`;
                // solo línea de ticks y texto, sin línea continua (evita superposición)
                s += `<line x1="${mx}" y1="${subDimY-4}" x2="${mx}" y2="${subDimY+4}" stroke="#3a4a58" stroke-width="1"/>`;
                s += `<line x1="${mx+mWpx}" y1="${subDimY-4}" x2="${mx+mWpx}" y2="${subDimY+4}" stroke="#3a4a58" stroke-width="1"/>`;
                s += `<line x1="${mx+1}" y1="${subDimY}" x2="${mx+mWpx-1}" y2="${subDimY}" stroke="#3a4a58" stroke-width="0.8"/>`;
                s += `<text x="${mx + mWpx/2}" y="${subDimY - 7}" text-anchor="middle" font-size="9.5" font-family="'Arial Narrow',Arial,monospace" fill="#2a3840">${label}</text>`;
                mx += mWpx;
            });
        }

        // V1 altura (derecha)
        const vDimX = ox + W + FRAME + 16;
        s += dimLine(vDimX, oy, vDimX, oy + H, `V1=${th}`, false, true);

        // Cota tacha si existe
        if (tachaHpx > 0.5) {
            const tDimX = vDimX + 28;
            s += dimLine(tDimX, oy + glassHpx, tDimX, oy + H, `${tachaH}`, false, false);
            s += `<text x="${tDimX + 14}" y="${oy + glassHpx + tachaHpx/2}" text-anchor="start" dominant-baseline="middle" font-size="8.5" font-family="'Arial Narrow',Arial,monospace" fill="#5a6a78">tacha</text>`;
        }

        s += '</svg>';
        return s;
    }

    function renderTypeIndicator(mod, gx, gy, gw, gh) {
        const type = mod.type;
        const isLeft = mod.opening !== 'derecha' && mod.opening !== 'der';
        let out = '';

        if (type === 'puerta') {
            // Línea de bisagra (vertical, lado de apertura)
            const hingeX = isLeft ? gx : gx + gw;
            const openEndX = isLeft ? gx + gw : gx;
            const arcR = Math.min(gw * 0.9, gh * 0.7);

            // Arco de apertura (dashed)
            const sweep = isLeft ? 0 : 1;
            out += `<path d="M${hingeX},${gy+gh} A${arcR},${arcR} 0 0 ${sweep} ${openEndX},${gy+gh}" fill="#5a9ec018" stroke="#2a6080" stroke-width="1.2" stroke-dasharray="6,3"/>`;
            // Línea vertical de la hoja
            out += `<line x1="${hingeX}" y1="${gy}" x2="${hingeX}" y2="${gy+gh}" stroke="#2a6080" stroke-width="2.5"/>`;
            // Diagonal de la hoja
            out += `<line x1="${hingeX}" y1="${gy}" x2="${openEndX}" y2="${gy+gh}" stroke="#2a6080" stroke-width="1" opacity="0.5"/>`;
            // Maneta
            const hx = isLeft ? gx + gw - 9 : gx + 5;
            const hy = gy + gh * 0.45;
            out += `<rect x="${hx}" y="${hy}" width="4" height="22" rx="2" fill="#2a6080" opacity="0.85"/>`;

        } else if (type === 'corredera') {
            const dir = isLeft ? 1 : -1;
            const acx = gx + gw / 2, acy = gy + gh / 2;
            const aw = Math.min(32, gw * 0.28);
            out += `<line x1="${acx-aw}" y1="${acy}" x2="${acx+aw}" y2="${acy}" stroke="#5a38a0" stroke-width="1.6"/>`;
            out += `<polyline points="${acx+aw*0.5*dir},${acy-7} ${acx+aw*dir},${acy} ${acx+aw*0.5*dir},${acy+7}" fill="none" stroke="#5a38a0" stroke-width="1.6"/>`;
            out += `<line x1="${acx}" y1="${gy+5}" x2="${acx}" y2="${gy+gh-5}" stroke="#5a38a0" stroke-width="0.8" stroke-dasharray="4,3" opacity="0.6"/>`;

        } else if (type === 'oscilobatiente') {
            const hx = isLeft ? gx : gx + gw;
            const tx = isLeft ? gx + gw : gx;
            out += `<path d="M${hx},${gy+3} L${hx},${gy+gh-3} L${tx},${gy+gh/2} Z" fill="#a0782814" stroke="#a07828" stroke-width="1.2"/>`;
            out += `<path d="M${gx+3},${gy+gh-3} L${gx+gw-3},${gy+gh-3} L${gx+gw/2},${gy+gh*0.45} Z" fill="#a0782814" stroke="#a07828" stroke-width="1.2"/>`;

        } else if (type === 'abatible') {
            out += `<path d="M${gx+3},${gy+3} L${gx+gw-3},${gy+3} L${gx+gw/2},${gy+gh*0.55} Z" fill="#2a804014" stroke="#2a8040" stroke-width="1.2"/>`;
        }

        // Etiqueta (pequeña, esquina superior)
        const t  = TYPES[type] || TYPES.fijo;
        const fs = Math.min(11, Math.max(7, gw * 0.08, gh * 0.06));
        if (gw > 30 && gh > 20) {
            const lbl = mod.label || t.name;
            out += `<text x="${gx + gw/2}" y="${gy + 14}" text-anchor="middle" font-size="${fs.toFixed(1)}" font-family="system-ui,'Arial',sans-serif" fill="#1a2a38" opacity="0.75">${escSvg(lbl)}</text>`;
        }

        return out;
    }

    function dimLine(x1, y1, x2, y2, label, horiz, withArrows) {
        let out = '';
        out += `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#3a4a58" stroke-width="0.9"${withArrows ? ` marker-start="url(#esc-arrL)" marker-end="url(#esc-arrR)"` : ''}/>`;
        if (horiz) {
            out += `<line x1="${x1}" y1="${y1-5}" x2="${x1}" y2="${y1+5}" stroke="#3a4a58" stroke-width="1"/>`;
            out += `<line x1="${x2}" y1="${y1-5}" x2="${x2}" y2="${y1+5}" stroke="#3a4a58" stroke-width="1"/>`;
            out += `<text x="${(x1+x2)/2}" y="${y1+14}" text-anchor="middle" font-size="10.5" font-family="'Arial Narrow',Arial,monospace" fill="#1a2840">${escSvg(label)}</text>`;
        } else {
            out += `<line x1="${x1-5}" y1="${y1}" x2="${x1+5}" y2="${y1}" stroke="#3a4a58" stroke-width="1"/>`;
            out += `<line x1="${x1-5}" y1="${y2}" x2="${x1+5}" y2="${y2}" stroke="#3a4a58" stroke-width="1"/>`;
            const mx = x1, my = (y1 + y2) / 2;
            out += `<text x="${mx+14}" y="${my}" text-anchor="middle" dominant-baseline="middle" font-size="10.5" font-family="'Arial Narrow',Arial,monospace" fill="#1a2840" transform="rotate(-90 ${mx+14} ${my})">${escSvg(label)}</text>`;
        }
        return out;
    }

    function escSvg(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── RENDER UI ─────────────────────────────────────────────────────────
    function renderCanvas() {
        const svg = buildSVG();
        const cw  = el(opts.canvasWrap);
        if (cw) cw.innerHTML = svg;
        if (typeof onChanged === 'function') onChanged(svg, state);
    }

    function renderModuleList() {
        const list = el(opts.moduleList);
        if (!list) return;

        list.innerHTML = state.modules.map((mod, i) => {
            const t = TYPES[mod.type] || TYPES.fijo;
            const needsOp = NEEDS_OPENING.includes(mod.type);
            return `
            <div class="esc-module-row" data-id="${mod.id}" style="border-left:4px solid ${t.stroke}">
                <div class="esc-module-header">
                    <span class="esc-module-badge" style="background:${t.fill};border-color:${t.stroke}">${t.name}</span>
                    <div class="esc-module-controls">
                        <button type="button" class="esc-btn-icon" data-action="up"   data-idx="${i}" title="Subir"   ${i===0?'disabled':''}>↑</button>
                        <button type="button" class="esc-btn-icon" data-action="down" data-idx="${i}" title="Bajar"   ${i===state.modules.length-1?'disabled':''}>↓</button>
                        <button type="button" class="esc-btn-icon esc-btn-del" data-action="del" data-idx="${i}" title="Eliminar">✕</button>
                    </div>
                </div>
                <div class="esc-module-fields">
                    <label class="esc-field">Tipo
                        <select data-field="type" data-idx="${i}">
                            ${Object.entries(TYPES).map(([k,v]) => `<option value="${k}"${k===mod.type?' selected':''}>${v.name}</option>`).join('')}
                        </select>
                    </label>
                    <label class="esc-field">Ancho (mm)
                        <input type="number" data-field="width" data-idx="${i}" value="${mod.width}" min="50" max="12000" step="1">
                    </label>
                    ${needsOp ? `<label class="esc-field">Apertura
                        <select data-field="opening" data-idx="${i}">
                            <option value="izquierda"${mod.opening==='izquierda'?' selected':''}>Izquierda</option>
                            <option value="derecha"${mod.opening==='derecha'?' selected':''}>Derecha</option>
                        </select>
                    </label>` : ''}
                    <label class="esc-field">Etiqueta
                        <input type="text" data-field="label" data-idx="${i}" value="${escSvg(mod.label)}" placeholder="P1, F1…">
                    </label>
                </div>
            </div>`;
        }).join('');

        list.querySelectorAll('[data-field]').forEach(inp => {
            inp.addEventListener('input', e => {
                const idx   = parseInt(e.target.dataset.idx);
                const field = e.target.dataset.field;
                state.modules[idx][field] = e.target.type === 'number'
                    ? (parseInt(e.target.value) || 50)
                    : e.target.value;
                renderCanvas();
                syncHidden();
            });
            inp.addEventListener('change', e => {
                const idx   = parseInt(e.target.dataset.idx);
                const field = e.target.dataset.field;
                state.modules[idx][field] = e.target.type === 'number'
                    ? (parseInt(e.target.value) || 50)
                    : e.target.value;
                if (field === 'type') renderModuleList();  // rerender para mostrar/ocultar apertura
                renderCanvas();
                syncHidden();
            });
        });

        list.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', e => {
                const action = e.currentTarget.dataset.action;
                const idx    = parseInt(e.currentTarget.dataset.idx);
                if (action === 'del') {
                    state.modules.splice(idx, 1);
                } else if (action === 'up' && idx > 0) {
                    [state.modules[idx-1], state.modules[idx]] = [state.modules[idx], state.modules[idx-1]];
                } else if (action === 'down' && idx < state.modules.length - 1) {
                    [state.modules[idx], state.modules[idx+1]] = [state.modules[idx+1], state.modules[idx]];
                }
                renderModuleList();
                renderCanvas();
                syncHidden();
            });
        });
    }

    function syncHidden() {
        const h = el(opts.hiddenJson);
        if (h) h.value = JSON.stringify(state);
    }

    function syncDimInputs() {
        const fw = el(opts.facadeW), fh = el(opts.facadeH), ft = el(opts.tachaH);
        if (fw) fw.value = state.totalWidth;
        if (fh) fh.value = state.totalHeight;
        if (ft) ft.value = state.tachaHeight;
    }

    // ── EVENTS ────────────────────────────────────────────────────────────
    function bindEvents() {
        el(opts.facadeW)?.addEventListener('input', e => {
            state.totalWidth = parseInt(e.target.value) || 1000;
            renderCanvas(); syncHidden();
        });
        el(opts.facadeH)?.addEventListener('input', e => {
            state.totalHeight = parseInt(e.target.value) || 500;
            renderCanvas(); syncHidden();
        });
        el(opts.tachaH)?.addEventListener('input', e => {
            state.tachaHeight = Math.max(0, parseInt(e.target.value) || 0);
            renderCanvas(); syncHidden();
        });

        el(opts.btnAddModule)?.addEventListener('click', () => {
            state.modules.push({ id: uid(), type: 'fijo', width: 1000, opening: '', label: '' });
            renderModuleList();
            renderCanvas();
            syncHidden();
        });
    }

    // ── PRESETS ───────────────────────────────────────────────────────────
    function applyPreset(name) {
        const presets = {
            'puerta': {
                totalWidth: 1000, totalHeight: 2100, tachaHeight: 0,
                modules: [{ id: uid(), type: 'puerta', width: 1000, opening: 'izquierda', label: '' }],
            },
            'puerta_fijo': {
                totalWidth: 6500, totalHeight: 2200, tachaHeight: 0,
                modules: [
                    { id: uid(), type: 'puerta', width: 1000, opening: 'izquierda', label: '' },
                    { id: uid(), type: 'fijo',   width: 5500, opening: '',          label: '' },
                ],
            },
            'fijo_puerta': {
                totalWidth: 4000, totalHeight: 2200, tachaHeight: 0,
                modules: [
                    { id: uid(), type: 'fijo',   width: 2500, opening: '',          label: '' },
                    { id: uid(), type: 'puerta', width: 1500, opening: 'derecha',   label: '' },
                ],
            },
            'fijo_puerta_fijo': {
                totalWidth: 5000, totalHeight: 2200, totalHeight: 2200, tachaHeight: 0,
                modules: [
                    { id: uid(), type: 'fijo',   width: 1500, opening: '',          label: '' },
                    { id: uid(), type: 'puerta', width: 1000, opening: 'izquierda', label: '' },
                    { id: uid(), type: 'fijo',   width: 2500, opening: '',          label: '' },
                ],
            },
            'puerta_tacha': {
                totalWidth: 1000, totalHeight: 2400, tachaHeight: 300,
                modules: [{ id: uid(), type: 'puerta', width: 1000, opening: 'izquierda', label: '' }],
            },
            'escaparate_vitrina': {
                totalWidth: 6500, totalHeight: 2200, tachaHeight: 300,
                modules: [
                    { id: uid(), type: 'puerta', width: 1000, opening: 'izquierda', label: '' },
                    { id: uid(), type: 'fijo',   width: 5500, opening: '',          label: '' },
                ],
            },
            'escaparate_completo': {
                totalWidth: 8000, totalHeight: 2400, tachaHeight: 400,
                modules: [
                    { id: uid(), type: 'fijo',   width: 2000, opening: '',          label: '' },
                    { id: uid(), type: 'puerta', width: 1000, opening: 'izquierda', label: '' },
                    { id: uid(), type: 'fijo',   width: 5000, opening: '',          label: '' },
                ],
            },
            'corredera_fijo': {
                totalWidth: 3600, totalHeight: 2200, tachaHeight: 0,
                modules: [
                    { id: uid(), type: 'corredera', width: 1800, opening: 'derecha', label: '' },
                    { id: uid(), type: 'fijo',      width: 1800, opening: '',        label: '' },
                ],
            },
        };

        const p = presets[name];
        if (!p) return;

        state.totalWidth  = p.totalWidth;
        state.totalHeight = p.totalHeight;
        state.tachaHeight = p.tachaHeight;
        state.modules     = p.modules;

        syncDimInputs();
        renderModuleList();
        renderCanvas();
        syncHidden();
    }

    // ── API PÚBLICA ───────────────────────────────────────────────────────
    function init(options) {
        opts      = options || {};
        onChanged = opts.onChanged || null;

        if (opts.initialState) {
            try { Object.assign(state, JSON.parse(opts.initialState)); } catch (_) {}
        }
        if (opts.facadeW_val)      state.totalWidth  = opts.facadeW_val;
        if (opts.facadeH_val)      state.totalHeight = opts.facadeH_val;
        if (opts.tachaH_val !== undefined) state.tachaHeight = opts.tachaH_val;
        if (opts.preset)           applyPreset(opts.preset);

        syncDimInputs();
        bindEvents();
        renderModuleList();
        renderCanvas();
        syncHidden();
    }

    function getState()        { return JSON.parse(JSON.stringify(state)); }
    function getSVG()          { return buildSVG(); }
    function loadState(s)      { Object.assign(state, s); syncDimInputs(); renderModuleList(); renderCanvas(); syncHidden(); }

    return { init, getState, getSVG, loadState, applyPreset, TYPES };
})();
