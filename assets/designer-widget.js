window.DesignerWidget = (function () {
    // ── CONSTANTES ────────────────────────────────────────
    const RAL_COLORS = [
        { code: '9010', name: 'Blanco puro',      hex: '#f4f4f0' },
        { code: '9016', name: 'Blanco tráfico',   hex: '#f6f6f6' },
        { code: '9003', name: 'Blanco señal',      hex: '#f0f0ec' },
        { code: '9002', name: 'Gris blanco',       hex: '#e8e4d9' },
        { code: '7035', name: 'Gris claro',        hex: '#d7d7d0' },
        { code: '7016', name: 'Gris antracita',    hex: '#3e4349' },
        { code: '7021', name: 'Gris negruzco',     hex: '#2e3234' },
        { code: '9005', name: 'Negro intenso',     hex: '#0a0a0a' },
        { code: '1013', name: 'Blanco perla',      hex: '#f0e8d0' },
        { code: '8017', name: 'Marrón chocolate',  hex: '#3b1f1c' },
        { code: '8019', name: 'Marrón grisáceo',   hex: '#3d3635' },
        { code: '6005', name: 'Verde musgo',       hex: '#1f3a2a' },
        { code: '5010', name: 'Azul genciana',     hex: '#1a3a5c' },
        { code: '3005', name: 'Rojo vino',         hex: '#4a1520' },
        { code: '1015', name: 'Marfil claro',      hex: '#f0e0b0' },
        { code: '6001', name: 'Verde esmeralda',   hex: '#29572a' },
        { code: '6009', name: 'Verde abeto',       hex: '#1c3626' },
        { code: '7015', name: 'Gris pizarra',      hex: '#5a5e62' },
        { code: '7022', name: 'Gris sombra',       hex: '#3a3d3e' },
        { code: '9006', name: 'Aluminio blanco',   hex: '#a8a8a8' },
        { code: 'custom', name: 'Personalizado',   hex: null },
    ];

    const SYS = {
        fijo:           { name: 'Fijo',           fill: '#d8e8f4', stroke: '#4a80b0' },
        practicable:    { name: 'Practicable',     fill: '#d8f0da', stroke: '#3a8a4a' },
        oscilobatiente: { name: 'Oscilobatiente',  fill: '#f4f0d8', stroke: '#a07828' },
        abatible:       { name: 'Abatible',        fill: '#d8f0f4', stroke: '#2a88a8' },
        corredera:      { name: 'Corredera',       fill: '#ead8f4', stroke: '#7838a8' },
        puerta:         { name: 'Puerta',          fill: '#f4e2d8', stroke: '#a85828' },
        tubo:           { name: 'Perfil/Tubo',     fill: '#c8cdd2', stroke: '#5a6370' },
    };
    const NEEDS_OPENING = ['practicable', 'oscilobatiente', 'corredera', 'puerta'];

    // ── ESTADO ────────────────────────────────────────────
    let state = { facadeW: 2400, facadeH: 1200, tree: null, sel: null, shape: 'rectangular', slopeDeg: 0 };
    let opts = {};
    let onSvgChange = null;

    // ── MODELO DE ÁRBOL ───────────────────────────────────
    function uid() { return Math.random().toString(36).slice(2, 8); }

    function mkLeaf(system, label, opening) {
        return { id: uid(), split: null, system: system || 'fijo', label: label || '', opening: opening || 'izq', heightPct: 100, topPct: 0 };
    }

    function find(node, id) {
        if (!node) return null;
        if (node.id === id) return node;
        if (node.split) return find(node.split.a, id) || find(node.split.b, id);
        return null;
    }

    function findParent(node, id) {
        if (!node.split) return null;
        if (node.split.a.id === id || node.split.b.id === id) return node;
        return findParent(node.split.a, id) || findParent(node.split.b, id);
    }

    // Devuelve hojas con bbox normalizada [0-1] + heightPct/topPct
    function leaves(node, x, y, w, h) {
        x = x || 0; y = y || 0;
        w = w === undefined ? 1 : w;
        h = h === undefined ? 1 : h;
        if (!node.split) return [{ node, x, y, w, h }];
        const sp = node.split;
        if (sp.dir === 'v') {
            return leaves(sp.a, x, y, w * sp.ratio, h)
                .concat(leaves(sp.b, x + w * sp.ratio, y, w * (1 - sp.ratio), h));
        }
        return leaves(sp.a, x, y, w, h * sp.ratio)
            .concat(leaves(sp.b, x, y + h * sp.ratio, w, h * (1 - sp.ratio)));
    }

    function splitNode(dir) {
        const node = find(state.tree, state.sel);
        if (!node || node.split) return;
        const a = mkLeaf(node.system, node.label, node.opening);
        const b = mkLeaf(node.system, '', node.opening);
        node.split = { dir, ratio: 0.5, a, b };
        node.system = null; node.label = null; node.opening = null;
        state.sel = a.id;
        render();
    }

    function unsplitParent() {
        const parent = findParent(state.tree, state.sel);
        if (!parent || parent.split.a.split || parent.split.b.split) return;
        const sys = parent.split.a.system || parent.split.b.system || 'fijo';
        parent.split = null;
        parent.system = sys; parent.label = ''; parent.opening = 'izq';
        parent.heightPct = 100; parent.topPct = 0;
        state.sel = parent.id;
        render();
    }

    function countLeaves(node) {
        if (!node) return 0;
        if (!node.split) return 1;
        return countLeaves(node.split.a) + countLeaves(node.split.b);
    }

    function addRight(systemType, label, opening) {
        var newLeaf = mkLeaf(systemType || 'fijo', label || (SYS[systemType] ? SYS[systemType].name : ''), opening || 'izq');
        var n = countLeaves(state.tree);
        var ratio = n / (n + 1);
        var oldTree = state.tree;
        state.tree = { id: uid(), split: { dir: 'v', ratio: ratio, a: oldTree, b: newLeaf },
                       system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
        state.sel = newLeaf.id;
        render();
    }

    function addTop(systemType, label, topRatio) {
        var newLeaf = mkLeaf(systemType || 'fijo', label || 'Tacha', null);
        var ratio = topRatio || 0.15;
        var oldTree = state.tree;
        state.tree = { id: uid(), split: { dir: 'h', ratio: ratio, a: newLeaf, b: oldTree },
                       system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
        state.sel = newLeaf.id;
        render();
    }

    // ── SVG ───────────────────────────────────────────────
    const SVG_W = 960, SVG_H = 560, MARGIN = 52;
    function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function buildSVG() {
        const fw = state.facadeW, fh = state.facadeH;
        const avW = SVG_W - MARGIN * 2, avH = SVG_H - MARGIN * 2;
        const sc  = Math.min(avW / fw, avH / fh);
        const W   = fw * sc, H = fh * sc;
        const ox  = (SVG_W - W) / 2, oy = (SVG_H - H) / 2;
        const FR  = Math.max(5, Math.min(12, sc * 22));

        let s = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${SVG_W} ${SVG_H}" width="${SVG_W}" height="${SVG_H}">`;

        s += `<defs>
          <pattern id="dwglass" patternUnits="userSpaceOnUse" width="14" height="14">
            <line x1="0" y1="14" x2="14" y2="0" stroke="#9ab8cc" stroke-width="0.65" opacity="0.55"/>
          </pattern>
          <pattern id="dwtube" patternUnits="userSpaceOnUse" width="1" height="8">
            <line x1="0" y1="2" x2="1" y2="2" stroke="#7a8590" stroke-width="0.8"/>
            <line x1="0" y1="5" x2="1" y2="5" stroke="#9aa5ad" stroke-width="0.6"/>
          </pattern>
        </defs>`;

        // Marco exterior (trapezoidal o rectangular)
        const slopePx = state.shape === 'trapezoidal' ? Math.tan((state.slopeDeg || 0) * Math.PI / 180) * H : 0;
        if (state.shape === 'trapezoidal' && slopePx !== 0) {
            const tlx = ox - FR + slopePx, tly = oy - FR;
            const trx = ox + W - FR + slopePx, try_ = oy - FR;
            const brx = ox + W + FR, bry = oy + H + FR;
            const blx = ox - FR, bly = oy + H + FR;
            s += `<polygon points="${tlx},${tly} ${trx},${try_} ${brx},${bry} ${blx},${bly}" fill="#3a4750"/>`;
        } else {
            s += `<rect x="${ox - FR}" y="${oy - FR}" width="${W + FR*2}" height="${H + FR*2}" fill="#3a4750" rx="4"/>`;
        }

        const leafs = leaves(state.tree);
        for (const { node, x, y, w, h } of leafs) {
            // Aplicar heightPct y topPct: el panel ocupa solo una fracción de su celda
            const hp = (node.heightPct ?? 100) / 100;
            const tp = (node.topPct    ?? 0)   / 100;
            const safeHp = Math.min(hp, 1 - tp);

            // Si hay espacio vacío arriba del panel, rellenar con marco
            const cellTopPx  = oy + y * H;
            const cellH      = h * H;
            const panelTopPx = cellTopPx + tp * cellH;
            const panelH     = safeHp * cellH;
            const panelLeft  = ox + x * W;
            const panelW     = w * W;

            // Espacio "vacío" (marco) encima del panel
            if (tp > 0.005) {
                s += `<rect x="${panelLeft}" y="${cellTopPx}" width="${panelW}" height="${tp * cellH}" fill="#3a4750"/>`;
            }
            // Espacio "vacío" (marco) debajo del panel
            const bottomGap = 1 - tp - safeHp;
            if (bottomGap > 0.005) {
                s += `<rect x="${panelLeft}" y="${panelTopPx + panelH}" width="${panelW}" height="${bottomGap * cellH}" fill="#3a4750"/>`;
            }

            const px = panelLeft, py = panelTopPx;
            const pw = panelW,    ph = panelH;
            const sel = node.id === state.sel;
            const col = SYS[node.system] || SYS.fijo;
            const panW = Math.round(fw * w);
            const panH = Math.round(fh * h * safeHp);

            s += `<rect x="${px}" y="${py}" width="${pw}" height="${ph}" fill="#3a4750"/>`;

            const gi = FR * 0.55;
            const gx = px + gi, gy = py + gi, gw = pw - gi*2, gh = ph - gi*2;
            if (gw > 2 && gh > 2) {
                if (node.system === 'tubo') {
                    // Render de tubo: sólido con líneas horizontales
                    s += `<rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="${col.fill}" class="panel-r" data-id="${node.id}" style="cursor:pointer"/>`;
                    s += `<rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="url(#dwtube)" pointer-events="none"/>`;
                    s += `<line x1="${gx+4}" y1="${gy + gh/2}" x2="${gx+gw-4}" y2="${gy + gh/2}" stroke="${col.stroke}" stroke-width="2" pointer-events="none"/>`;
                } else {
                    s += `<rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="${col.fill}" class="panel-r" data-id="${node.id}" style="cursor:pointer"/>`;
                    s += `<rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="url(#dwglass)" pointer-events="none"/>`;
                    s += sysIndicator(node.system, node.opening, gx, gy, gw, gh, col.stroke);
                }

                if (sel) {
                    s += `<rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="none" stroke="#1d4ed8" stroke-width="2.5" stroke-dasharray="7,3" pointer-events="none"/>`;
                }

                const cx = px + pw/2, cy = py + ph/2;
                const lbl = esc(node.label || (SYS[node.system] ? SYS[node.system].name : ''));
                const fs  = Math.min(12, pw * 0.09, ph * 0.14);
                if (fs > 5 && gw > 30 && gh > 20) {
                    s += `<text x="${cx}" y="${cy - (gh > 45 ? 8 : 0)}" text-anchor="middle" dominant-baseline="middle" font-size="${fs.toFixed(1)}" font-family="system-ui,sans-serif" fill="#1a2730" font-weight="600" pointer-events="none">${lbl}</text>`;
                    if (gh > 44) {
                        const fsm = Math.min(9.5, fs * 0.78);
                        s += `<text x="${cx}" y="${cy + 11}" text-anchor="middle" font-size="${fsm.toFixed(1)}" font-family="monospace,sans-serif" fill="#48606e" pointer-events="none">${panW}×${panH}</text>`;
                    }
                }
            }
        }

        // Cotas exteriores
        const dy = oy - 30;
        s += dimLine(ox, dy + 8, ox + W, dy + 8, true);
        s += `<text x="${ox + W/2}" y="${dy + 3}" text-anchor="middle" font-size="11" font-family="system-ui,sans-serif" fill="#2a3840">${fw} mm</text>`;
        const dx = ox - 30;
        s += dimLine(dx + 8, oy, dx + 8, oy + H, false);
        s += `<text x="${dx}" y="${oy + H/2}" text-anchor="middle" dominant-baseline="middle" font-size="11" font-family="system-ui,sans-serif" fill="#2a3840" transform="rotate(-90 ${dx} ${oy + H/2})">${fh} mm</text>`;

        s += `</svg>`;
        return s;
    }

    function dimLine(x1, y1, x2, y2, horiz) {
        const tick = horiz
            ? `<line x1="${x1}" y1="${y1-5}" x2="${x1}" y2="${y1+5}" stroke="#4a5a68" stroke-width="1"/>
               <line x1="${x2}" y1="${y1-5}" x2="${x2}" y2="${y1+5}" stroke="#4a5a68" stroke-width="1"/>`
            : `<line x1="${x1-5}" y1="${y1}" x2="${x1+5}" y2="${y1}" stroke="#4a5a68" stroke-width="1"/>
               <line x1="${x1-5}" y1="${y2}" x2="${x1+5}" y2="${y2}" stroke="#4a5a68" stroke-width="1"/>`;
        return `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#4a5a68" stroke-width="1"/>${tick}`;
    }

    function sysIndicator(sys, op, x, y, w, h, col) {
        if (w < 16 || h < 16) return '';
        const sw = 1.4;
        const der = op === 'der';
        switch (sys) {
            case 'practicable': {
                const hx = der ? x + w - 3 : x + 3;
                const tx = der ? x + 3     : x + w - 3;
                return `<path d="M${hx},${y+3} L${hx},${y+h-3} L${tx},${y+h/2} Z" fill="${col}22" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/>
                        <circle cx="${hx}" cy="${y+h/2}" r="2.8" fill="${col}" pointer-events="none"/>`;
            }
            case 'oscilobatiente': {
                const hx = der ? x + w - 3 : x + 3;
                const tx = der ? x + 3     : x + w - 3;
                return `<path d="M${hx},${y+3} L${hx},${y+h-3} L${tx},${y+h/2} Z" fill="${col}18" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/>
                        <path d="M${x+3},${y+h-3} L${x+w-3},${y+h-3} L${x+w/2},${y+h*0.45} Z" fill="${col}18" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/>
                        <circle cx="${hx}" cy="${y+h/2}" r="2.8" fill="${col}" pointer-events="none"/>`;
            }
            case 'abatible':
                return `<path d="M${x+3},${y+3} L${x+w-3},${y+3} L${x+w/2},${y+h*0.55} Z" fill="${col}18" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/>`;
            case 'corredera': {
                const dir = der ? 1 : -1;
                const cx = x + w / 2, cy = y + h / 2;
                const a = Math.min(22, w * 0.25);
                return `<line x1="${cx-a}" y1="${cy}" x2="${cx+a}" y2="${cy}" stroke="${col}" stroke-width="${sw+0.2}" pointer-events="none"/>
                        <polyline points="${cx+a*0.5*dir},${cy-6} ${cx+a*dir},${cy} ${cx+a*0.5*dir},${cy+6}" fill="none" stroke="${col}" stroke-width="${sw}" pointer-events="none"/>
                        <line x1="${cx}" y1="${y+5}" x2="${cx}" y2="${y+h-5}" stroke="${col}" stroke-width="0.9" stroke-dasharray="4,3" pointer-events="none"/>`;
            }
            case 'puerta': {
                const hx = der ? x + w - 3 : x + 3;
                const sw2 = Math.min(w * 0.65, h * 0.65);
                const ex = der ? hx - sw2 : hx + sw2;
                return `<rect x="${x+3}" y="${y+h-9}" width="${w-6}" height="6" fill="${col}28" stroke="${col}" stroke-width="1" rx="1" pointer-events="none"/>
                        <line x1="${hx}" y1="${y+3}" x2="${hx}" y2="${y+h-9}" stroke="${col}" stroke-width="2.2" pointer-events="none"/>
                        <path d="M${hx},${y+3} A${sw2},${sw2} 0 0 ${der?0:1} ${ex},${y+3}" fill="${col}14" stroke="${col}" stroke-width="${sw}" stroke-dasharray="5,2.5" pointer-events="none"/>`;
            }
            default: return '';
        }
    }

    // ── UI UPDATE ─────────────────────────────────────────
    function el(id) { return document.getElementById(id); }

    function updateForm() {
        const node   = find(state.tree, state.sel);
        const parent = node ? findParent(state.tree, state.sel) : null;
        const isLeaf = node && !node.split;

        if (node) {
            const leaf = leaves(state.tree).find(l => l.node.id === state.sel);
            if (leaf) {
                const pw = Math.round(state.facadeW * leaf.w);
                const hp = (leaf.node.heightPct ?? 100) / 100;
                const ph = Math.round(state.facadeH * leaf.h * hp);
                if (el(opts.panelInfo)) el(opts.panelInfo).textContent = `${pw} × ${ph} mm`;
            }
        }

        const lc = el(opts.leafControls);
        if (lc) { lc.style.opacity = isLeaf ? '1' : '0.35'; lc.style.pointerEvents = isLeaf ? '' : 'none'; }
        if (isLeaf) {
            if (el(opts.panelSystem))  el(opts.panelSystem).value  = node.system  || 'fijo';
            if (el(opts.panelLabel))   el(opts.panelLabel).value   = node.label   || '';
            if (el(opts.panelOpening)) el(opts.panelOpening).value = node.opening || 'izq';
            const needsOp = NEEDS_OPENING.includes(node.system);
            const or = el(opts.openingRow);
            if (or) or.style.display = needsOp ? '' : 'none';

            // Sliders de altura
            const hpVal = node.heightPct ?? 100;
            const tpVal = node.topPct    ?? 0;
            if (el(opts.panelHeightPct)) el(opts.panelHeightPct).value = hpVal;
            if (el(opts.panelTopPct))    el(opts.panelTopPct).value    = tpVal;
            if (el(opts.panelHeightPctLabel)) el(opts.panelHeightPctLabel).textContent = `${hpVal}%`;
            if (el(opts.panelTopPctLabel))    el(opts.panelTopPctLabel).textContent    = `${tpVal}%`;

            // Ocultar altura/posición para tubos (siempre span completo)
            const heightControls = el(opts.heightControls);
            if (heightControls) heightControls.style.display = node.system === 'tubo' ? 'none' : '';
        }

        if (parent && parent.split) {
            const pct = Math.round(parent.split.ratio * 100);
            const sc = el(opts.splitControls);
            if (sc) sc.style.display = '';
            if (el(opts.splitRatio)) el(opts.splitRatio).value = pct;
            if (el(opts.splitInfo)) el(opts.splitInfo).textContent = `${pct}% · ${100 - pct}%`;
        } else {
            const sc = el(opts.splitControls);
            if (sc) sc.style.display = 'none';
        }

        const canUnsplit = parent && parent.split && !parent.split.a.split && !parent.split.b.split;
        if (el(opts.btnUnsplit)) el(opts.btnUnsplit).disabled = !canUnsplit;
        if (el(opts.btnSplitV)) el(opts.btnSplitV).disabled = !isLeaf;
        if (el(opts.btnSplitH)) el(opts.btnSplitH).disabled = !isLeaf;
    }

    function updatePanelList() {
        const leafs = leaves(state.tree);
        let i = 1;
        const html = leafs.map(({ node, w, h }) => {
            const col = SYS[node.system] || SYS.fijo;
            const pw  = Math.round(state.facadeW * w);
            const hp  = (node.heightPct ?? 100) / 100;
            const ph  = Math.round(state.facadeH * h * hp);
            const lbl = node.label || `${col.name} ${i++}`;
            const sel = node.id === state.sel ? ' selected' : '';
            return `<div class="panel-list-item${sel}" data-id="${node.id}" style="border-left-color:${col.stroke}">
                <strong>${lbl}</strong><span>${pw}×${ph}</span></div>`;
        }).join('');
        const pl = el(opts.panelList);
        if (pl) {
            pl.innerHTML = html;
            pl.querySelectorAll('.panel-list-item').forEach(e =>
                e.addEventListener('click', () => { state.sel = e.dataset.id; render(); })
            );
        }
        // Badge contador
        const badge = el(opts.panelBadge);
        if (badge) badge.textContent = leafs.length > 1 ? `${leafs.length} paneles` : '';
    }

    function updateLegend() {
        const used = [...new Set(leaves(state.tree).map(l => l.node.system))];
        const html = used.map(s => {
            const c = SYS[s] || SYS.fijo;
            return `<div class="sys-legend-item"><span class="sys-swatch" style="background:${c.fill};border-color:${c.stroke}"></span>${c.name}</div>`;
        }).join('');
        const sl = el(opts.sysLegend);
        if (sl) sl.innerHTML = html;
    }

    // ── RENDER ────────────────────────────────────────────
    function render() {
        const svgStr = buildSVG();
        const cw = el(opts.canvasWrap);
        if (cw) {
            cw.innerHTML = svgStr;
            cw.querySelectorAll('.panel-r').forEach(e =>
                e.addEventListener('click', () => { state.sel = e.dataset.id; render(); })
            );
        }
        updateForm();
        updatePanelList();
        updateLegend();
        if (typeof onSvgChange === 'function') {
            onSvgChange(svgStr, state.tree);
        }
    }

    // ── BIND EVENTS ───────────────────────────────────────
    function bindEvents() {
        el(opts.btnSplitV)?.addEventListener('click', () => splitNode('v'));
        el(opts.btnSplitH)?.addEventListener('click', () => splitNode('h'));
        el(opts.btnUnsplit)?.addEventListener('click', unsplitParent);

        ['facadeW', 'facadeH'].forEach(k => {
            el(opts[k])?.addEventListener('input', e => {
                state[k] = parseInt(e.target.value) || (k === 'facadeW' ? 2400 : 1200);
                render();
            });
        });

        el(opts.panelSystem)?.addEventListener('change', e => {
            const n = find(state.tree, state.sel);
            if (n && !n.split) { n.system = e.target.value; render(); }
        });
        el(opts.panelOpening)?.addEventListener('change', e => {
            const n = find(state.tree, state.sel);
            if (n && !n.split) { n.opening = e.target.value; render(); }
        });
        el(opts.panelLabel)?.addEventListener('input', e => {
            const n = find(state.tree, state.sel);
            if (n && !n.split) { n.label = e.target.value; render(); }
        });
        el(opts.splitRatio)?.addEventListener('input', e => {
            const parent = findParent(state.tree, state.sel);
            if (parent && parent.split) { parent.split.ratio = parseInt(e.target.value) / 100; render(); }
        });
        el(opts.panelHeightPct)?.addEventListener('input', e => {
            const n = find(state.tree, state.sel);
            if (n && !n.split) {
                n.heightPct = parseInt(e.target.value);
                if (el(opts.panelHeightPctLabel)) el(opts.panelHeightPctLabel).textContent = `${n.heightPct}%`;
                render();
            }
        });
        el(opts.panelTopPct)?.addEventListener('input', e => {
            const n = find(state.tree, state.sel);
            if (n && !n.split) {
                n.topPct = parseInt(e.target.value);
                if (el(opts.panelTopPctLabel)) el(opts.panelTopPctLabel).textContent = `${n.topPct}%`;
                render();
            }
        });
        el(opts.shapeSelect)?.addEventListener('change', e => {
            state.shape = e.target.value;
            render();
        });
        el(opts.slopeRange)?.addEventListener('input', e => {
            state.slopeDeg = parseInt(e.target.value) || 0;
            if (el(opts.slopeLabel)) el(opts.slopeLabel).textContent = `${state.slopeDeg}°`;
            render();
        });
    }

    // ── PRESETS ────────────────────────────────────────────
    function applyPreset(preset, facadeW, facadeH) {
        state.facadeW = facadeW || state.facadeW;
        state.facadeH = facadeH || state.facadeH;
        if (el(opts.facadeW)) el(opts.facadeW).value = state.facadeW;
        if (el(opts.facadeH)) el(opts.facadeH).value = state.facadeH;

        if (preset === 'escaparate') {
            // Puerta (40%) + Fijo (60%) con división vertical
            var door = mkLeaf('puerta', 'Puerta', 'izq');
            var show = mkLeaf('fijo', 'Escaparate');
            var sp = { dir: 'v', ratio: 0.40, a: door, b: show };
            state.tree = { id: uid(), split: sp, system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
            state.sel = door.id;
        } else if (preset === 'fijo_puerta_fijo') {
            // Fijo (25%) | Puerta (50%) | Fijo (25%)
            var f1 = mkLeaf('fijo', 'Fijo', 'izq');
            var door2 = mkLeaf('puerta', 'Puerta', 'izq');
            var f2 = mkLeaf('fijo', 'Fijo', 'der');
            var spR = { dir: 'v', ratio: 0.50, a: door2, b: f2 };
            state.tree = { id: uid(), split: { dir: 'v', ratio: 0.25, a: f1, b: spR }, system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
            state.sel = door2.id;
        } else if (preset === 'puerta_tacha') {
            // [ TACHA ] / [ PUERTA ] — tacha superior (22%) + puerta (78%)
            var tachaPT   = mkLeaf('fijo',   'Tacha',   null);
            var doorPT    = mkLeaf('puerta', 'Puerta',  'izq');
            state.tree = { id: uid(), split: { dir: 'h', ratio: 0.22, a: tachaPT, b: doorPT },
                           system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
            state.sel = doorPT.id;
        } else if (preset === 'fijo_puerta_tacha') {
            // [ FIJO ] [ PUERTA + TACHA ] — fijo lateral (35%) + tacha arriba + puerta abajo
            var fijoFPT   = mkLeaf('fijo',   'Fijo lateral', null);
            var tachFPT   = mkLeaf('fijo',   'Tacha',        null);
            var doorFPT   = mkLeaf('puerta', 'Puerta',       'izq');
            var doorSect  = { id: uid(), split: { dir: 'h', ratio: 0.22, a: tachFPT, b: doorFPT },
                              system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
            state.tree = { id: uid(), split: { dir: 'v', ratio: 0.35, a: fijoFPT, b: doorSect },
                           system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
            state.sel = doorFPT.id;
        } else if (preset === 'fijo_corredera') {
            // [ FIJO ] [ CORREDERA ] — fijo lateral (35%) + corredera (65%)
            var fijoFC    = mkLeaf('fijo',      'Fijo lateral', null);
            var corrFC    = mkLeaf('corredera', 'Corredera',    'der');
            state.tree = { id: uid(), split: { dir: 'v', ratio: 0.35, a: fijoFC, b: corrFC },
                           system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
            state.sel = corrFC.id;
        } else if (preset === 'escaparate_vitrina') {
            // Puerta estrecha (~15%) + Gran vitrina fija (~85%) — ej. 1000 + 5500 = 6500 mm
            var doorEV = mkLeaf('puerta', 'Puerta', 'izq');
            var vitrEV = mkLeaf('fijo', 'Vitrina');
            var spEV = { dir: 'v', ratio: Math.round(1000 / 6500 * 1000) / 1000, a: doorEV, b: vitrEV };
            state.tree = { id: uid(), split: spEV, system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
            state.sel = vitrEV.id;
        } else if (preset === 'escaparate_completo') {
            // [ TACHA CORRIDA ] / [ FIJO | PUERTA | FIJO ] — escaparate 4 módulos
            var tachaEC   = mkLeaf('fijo',   'Tacha corrida',  null);
            var fijoECL   = mkLeaf('fijo',   'Fijo lateral',   null);
            var doorEC    = mkLeaf('puerta', 'Puerta',         'izq');
            var fijoECR   = mkLeaf('fijo',   'Fijo lateral',   null);
            var rowRight  = { id: uid(), split: { dir: 'v', ratio: 0.50, a: doorEC, b: fijoECR },
                              system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
            var rowBase   = { id: uid(), split: { dir: 'v', ratio: 0.25, a: fijoECL, b: rowRight },
                              system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
            state.tree = { id: uid(), split: { dir: 'h', ratio: 0.20, a: tachaEC, b: rowBase },
                           system: null, label: null, opening: null, heightPct: 100, topPct: 0 };
            state.sel = doorEC.id;
        } else {
            state.tree = mkLeaf('practicable');
            state.sel = state.tree.id;
        }
        render();
    }

    function deepCloneTree(node) {
        if (!node) return null;
        var clone = { id: uid(), system: node.system, label: node.label, opening: node.opening, heightPct: node.heightPct, topPct: node.topPct, split: null };
        if (node.split) {
            clone.split = { dir: node.split.dir, ratio: node.split.ratio, a: deepCloneTree(node.split.a), b: deepCloneTree(node.split.b) };
        }
        return clone;
    }

    // ── API PÚBLICA ───────────────────────────────────────
    function init(options) {
        opts = options || {};
        onSvgChange = opts.onSvgChange || null;
        state.facadeW = opts.facadeW_val || 2400;
        state.facadeH = opts.facadeH_val || 1200;
        if (opts.tree) {
            state.tree = deepCloneTree(opts.tree);
        } else {
            state.tree = mkLeaf('practicable');
        }
        state.sel = state.tree ? state.tree.id : null;
        if (el(opts.facadeW)) el(opts.facadeW).value = state.facadeW;
        if (el(opts.facadeH)) el(opts.facadeH).value = state.facadeH;
        bindEvents();
        render();
    }

    function loadState(treeData) {
        state.tree = deepCloneTree(treeData);
        state.sel = state.tree ? state.tree.id : null;
        if (state.tree) {
            if (el(opts.facadeW)) el(opts.facadeW).value = state.facadeW;
            if (el(opts.facadeH)) el(opts.facadeH).value = state.facadeH;
        }
        render();
    }

    function setDimensions(w, h) {
        state.facadeW = w || state.facadeW;
        state.facadeH = h || state.facadeH;
        if (el(opts.facadeW)) el(opts.facadeW).value = state.facadeW;
        if (el(opts.facadeH)) el(opts.facadeH).value = state.facadeH;
        render();
    }

    function getState() { return { facadeW: state.facadeW, facadeH: state.facadeH, tree: state.tree }; }

    return { init, loadState, setDimensions, getState, applyPreset, deepCloneTree, addRight, addTop, RAL_COLORS, SYS };
})();
