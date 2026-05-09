<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/helpers.php';
$lang = get_current_lang();
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurador Visual – <?= h(tr('app_title', $lang)) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/designer.css">
</head>
<body>
<div class="background-shape shape-a"></div>
<div class="background-shape shape-b"></div>

<header class="topbar">
    <h1><?= h(tr('app_title', $lang)) ?></h1>
    <div class="topbar-tools">
        <nav>
            <a href="<?= h(url_with_lang('index.php', [], $lang)) ?>"><?= h(tr('new', $lang)) ?></a>
            <a href="designer.php" class="active">Configurador</a>
            <a href="descompuesto.php">Descompuesto S28</a>
            <a href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>"><?= h(tr('history', $lang)) ?></a>
        </nav>
    </div>
</header>

<main class="designer-layout">

    <!-- ── PANEL IZQUIERDO ── -->
    <aside class="designer-panel">
        <h2>Configurador visual</h2>
        <p class="field-hint">Ventanas · Balconeras · Puertas · Fachadas</p>

        <div class="form-section">
            <h3>Dimensiones del hueco</h3>
            <div class="grid two">
                <label>Ancho L (mm)
                    <input type="number" id="facadeW" value="2400" min="200" max="15000" step="1">
                </label>
                <label>Alto H (mm)
                    <input type="number" id="facadeH" value="1200" min="200" max="6000" step="1">
                </label>
            </div>
        </div>

        <div class="form-section">
            <h3>Dividir panel seleccionado</h3>
            <div class="btn-group">
                <button id="btnSplitV" class="secondary-button split-btn" title="Añadir montante vertical">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="1" y="1" width="16" height="16" rx="2"/><line x1="9" y1="1" x2="9" y2="17"/>
                    </svg>
                    Montante
                </button>
                <button id="btnSplitH" class="secondary-button split-btn" title="Añadir travesaño horizontal">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="1" y="1" width="16" height="16" rx="2"/><line x1="1" y1="9" x2="17" y2="9"/>
                    </svg>
                    Travesaño
                </button>
            </div>
            <button id="btnUnsplit" class="secondary-button" style="width:100%;margin-top:0.4rem" disabled>
                ✕ Eliminar división
            </button>
        </div>

        <div class="form-section">
            <h3>Panel seleccionado</h3>
            <p class="field-hint" id="panelInfo">Haz clic en un panel</p>

            <div id="leafControls">
                <label>Sistema
                    <select id="panelSystem">
                        <option value="fijo">Fijo (vidrio fijo)</option>
                        <option value="practicable">Practicable</option>
                        <option value="oscilobatiente">Oscilobatiente</option>
                        <option value="abatible">Abatible / Proyectante</option>
                        <option value="corredera">Corredera</option>
                        <option value="puerta">Puerta</option>
                    </select>
                </label>
                <label id="openingRow">Apertura
                    <select id="panelOpening">
                        <option value="izq">Izquierda / Arriba</option>
                        <option value="der">Derecha / Abajo</option>
                    </select>
                </label>
                <label>Etiqueta (opcional)
                    <input type="text" id="panelLabel" placeholder="V1, P1, F1…">
                </label>
            </div>
        </div>

        <div class="form-section" id="splitControls" style="display:none">
            <h3>Posición del corte</h3>
            <label>Desde el inicio (%)
                <input type="range" id="splitRatio" min="10" max="90" value="50" step="1" style="width:100%">
            </label>
            <p class="field-hint" id="splitInfo" style="text-align:center">50% · 50%</p>
        </div>

        <div class="form-section">
            <h3>Paneles</h3>
            <div id="panelList" class="panel-list"></div>
        </div>

        <div class="form-section">
            <h3>Leyenda</h3>
            <div class="sys-legend" id="sysLegend"></div>
        </div>

        <button class="print-btn" onclick="window.print()">Imprimir / PDF</button>
    </aside>

    <!-- ── CANVAS DERECHO ── -->
    <section class="designer-canvas-area">
        <div class="canvas-hint">
            Clic en un panel para seleccionarlo · Usa los botones para dividirlo · Las dimensiones son en mm
        </div>
        <div class="canvas-wrap" id="canvasWrap"></div>
    </section>

</main>

<script>
// ══════════════════════════════════════════════════════════
//  MODELO DE DATOS
// ══════════════════════════════════════════════════════════

function uid() { return Math.random().toString(36).slice(2, 8); }

function mkLeaf(system, label, opening) {
    return { id: uid(), split: null, system: system || 'fijo', label: label || '', opening: opening || 'izq' };
}

const SYS = {
    fijo:           { name: 'Fijo',           fill: '#d8e8f4', stroke: '#4a80b0' },
    practicable:    { name: 'Practicable',     fill: '#d8f0da', stroke: '#3a8a4a' },
    oscilobatiente: { name: 'Oscilobatiente',  fill: '#f4f0d8', stroke: '#a07828' },
    abatible:       { name: 'Abatible',        fill: '#d8f0f4', stroke: '#2a88a8' },
    corredera:      { name: 'Corredera',       fill: '#ead8f4', stroke: '#7838a8' },
    puerta:         { name: 'Puerta',          fill: '#f4e2d8', stroke: '#a85828' },
};

const NEEDS_OPENING = ['practicable', 'oscilobatiente', 'corredera', 'puerta'];

let state = {
    facadeW: 2400,
    facadeH: 1200,
    tree: null,
    sel: null,
};

state.tree = mkLeaf('practicable');
state.sel  = state.tree.id;

// ── Árbol ─────────────────────────────────────────────────

function find(node, id) {
    if (node.id === id) return node;
    if (node.split) return find(node.split.a, id) || find(node.split.b, id);
    return null;
}

function findParent(node, id) {
    if (!node.split) return null;
    if (node.split.a.id === id || node.split.b.id === id) return node;
    return findParent(node.split.a, id) || findParent(node.split.b, id);
}

// Devuelve todas las hojas con su bbox normalizada [0-1]
function leaves(node, x, y, w, h) {
    x = x || 0; y = y || 0; w = w === undefined ? 1 : w; h = h === undefined ? 1 : h;
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
    state.sel = parent.id;
    render();
}

// ══════════════════════════════════════════════════════════
//  RENDER SVG
// ══════════════════════════════════════════════════════════

const SVG_W = 960, SVG_H = 560, MARGIN = 52;
function esc(s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function buildSVG() {
    const fw = state.facadeW, fh = state.facadeH;
    const avW = SVG_W - MARGIN * 2, avH = SVG_H - MARGIN * 2;
    const sc  = Math.min(avW / fw, avH / fh);
    const W   = fw * sc, H = fh * sc;
    const ox  = (SVG_W - W) / 2, oy = (SVG_H - H) / 2;
    const FR  = Math.max(5, Math.min(12, sc * 22));   // frame render px

    let s = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${SVG_W} ${SVG_H}" width="${SVG_W}" height="${SVG_H}">`;

    // Defs
    s += `<defs>
      <pattern id="glass" patternUnits="userSpaceOnUse" width="14" height="14">
        <line x1="0" y1="14" x2="14" y2="0" stroke="#9ab8cc" stroke-width="0.65" opacity="0.55"/>
      </pattern>
    </defs>`;

    // Marco exterior
    s += `<rect x="${ox - FR}" y="${oy - FR}" width="${W + FR*2}" height="${H + FR*2}" fill="#3a4750" rx="4"/>`;

    const leafs = leaves(state.tree);
    for (const { node, x, y, w, h } of leafs) {
        const px = ox + x * W, py = oy + y * H;
        const pw = w * W,      ph = h * H;
        const sel = node.id === state.sel;
        const col = SYS[node.system] || SYS.fijo;
        const panW = Math.round(fw * w), panH = Math.round(fh * h);

        // Perfil de marco alrededor del panel (gris oscuro)
        s += `<rect x="${px}" y="${py}" width="${pw}" height="${ph}" fill="#3a4750"/>`;

        // Superficie acristalada interior
        const gi = FR * 0.55;
        const gx = px + gi, gy = py + gi, gw = pw - gi*2, gh = ph - gi*2;
        if (gw > 2 && gh > 2) {
            s += `<rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="${col.fill}" class="panel-r" data-id="${node.id}" style="cursor:pointer"/>`;
            s += `<rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="url(#glass)" pointer-events="none"/>`;

            // Indicador de sistema
            s += sysIndicator(node.system, node.opening, gx, gy, gw, gh, col.stroke);

            // Borde de selección
            if (sel) {
                s += `<rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="none" stroke="#1d4ed8" stroke-width="2.5" stroke-dasharray="7,3" pointer-events="none"/>`;
            }

            // Etiqueta y dimensión
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

    // Cotas exteriores – anchura
    const dy = oy - 30;
    s += dimLine(ox, dy + 8, ox + W, dy + 8, true);
    s += `<text x="${ox + W/2}" y="${dy + 3}" text-anchor="middle" font-size="11" font-family="system-ui,sans-serif" fill="#2a3840">${fw} mm</text>`;

    // Cotas exteriores – altura
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
    const sw  = 1.4;
    const der = op === 'der';

    switch (sys) {
        case 'practicable': {
            // Triángulo desde el lado de la bisagra hacia el centro
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
        case 'abatible': {
            return `<path d="M${x+3},${y+3} L${x+w-3},${y+3} L${x+w/2},${y+h*0.55} Z" fill="${col}18" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/>`;
        }
        case 'corredera': {
            const dir = der ? 1 : -1;
            const cx  = x + w / 2, cy = y + h / 2;
            const a   = Math.min(22, w * 0.25);
            return `<line x1="${cx - a}" y1="${cy}" x2="${cx + a}" y2="${cy}" stroke="${col}" stroke-width="${sw+0.2}" pointer-events="none"/>
                    <polyline points="${cx+a*0.5*dir},${cy-6} ${cx+a*dir},${cy} ${cx+a*0.5*dir},${cy+6}" fill="none" stroke="${col}" stroke-width="${sw}" pointer-events="none"/>
                    <line x1="${cx}" y1="${y+5}" x2="${cx}" y2="${y+h-5}" stroke="${col}" stroke-width="0.9" stroke-dasharray="4,3" pointer-events="none"/>`;
        }
        case 'puerta': {
            const hx = der ? x + w - 3 : x + 3;
            const sw2 = Math.min(w * 0.65, h * 0.65);
            const ex  = der ? hx - sw2 : hx + sw2;
            return `<rect x="${x+3}" y="${y+h-9}" width="${w-6}" height="6" fill="${col}28" stroke="${col}" stroke-width="1" rx="1" pointer-events="none"/>
                    <line x1="${hx}" y1="${y+3}" x2="${hx}" y2="${y+h-9}" stroke="${col}" stroke-width="2.2" pointer-events="none"/>
                    <path d="M${hx},${y+3} A${sw2},${sw2} 0 0 ${der?0:1} ${ex},${y+3}" fill="${col}14" stroke="${col}" stroke-width="${sw}" stroke-dasharray="5,2.5" pointer-events="none"/>`;
        }
        default: return ''; // fijo – solo patrón de vidrio
    }
}

// ══════════════════════════════════════════════════════════
//  FORM UPDATE
// ══════════════════════════════════════════════════════════

function updateForm() {
    const node   = find(state.tree, state.sel);
    const parent = node ? findParent(state.tree, state.sel) : null;
    const isLeaf = node && !node.split;

    // Dimensiones del panel
    if (node) {
        const leaf = leaves(state.tree).find(l => l.node.id === state.sel);
        if (leaf) {
            const pw = Math.round(state.facadeW * leaf.w);
            const ph = Math.round(state.facadeH * leaf.h);
            document.getElementById('panelInfo').textContent = `${pw} × ${ph} mm`;
        }
    }

    // Controles de hoja (solo para paneles hoja)
    document.getElementById('leafControls').style.opacity = isLeaf ? '1' : '0.35';
    document.getElementById('leafControls').style.pointerEvents = isLeaf ? '' : 'none';
    if (isLeaf) {
        document.getElementById('panelSystem').value  = node.system || 'fijo';
        document.getElementById('panelLabel').value   = node.label  || '';
        document.getElementById('panelOpening').value = node.opening || 'izq';
        document.getElementById('openingRow').style.display = NEEDS_OPENING.includes(node.system) ? '' : 'none';
    }

    // Controles de split
    if (parent && parent.split) {
        const pct = Math.round(parent.split.ratio * 100);
        document.getElementById('splitControls').style.display = '';
        document.getElementById('splitRatio').value = pct;
        document.getElementById('splitInfo').textContent = `${pct}% · ${100 - pct}%`;
    } else {
        document.getElementById('splitControls').style.display = 'none';
    }

    // Botón unsplit – solo si ambos hijos son hojas
    const canUnsplit = parent && parent.split && !parent.split.a.split && !parent.split.b.split;
    document.getElementById('btnUnsplit').disabled = !canUnsplit;
    document.getElementById('btnSplitV').disabled = !isLeaf;
    document.getElementById('btnSplitH').disabled = !isLeaf;
}

function updatePanelList() {
    const leafs = leaves(state.tree);
    let i = 1;
    const html = leafs.map(({ node, w, h }) => {
        const col  = SYS[node.system] || SYS.fijo;
        const pw   = Math.round(state.facadeW * w);
        const ph   = Math.round(state.facadeH * h);
        const lbl  = node.label || `${col.name} ${i++}`;
        const sel  = node.id === state.sel ? ' selected' : '';
        return `<div class="panel-list-item${sel}" data-id="${node.id}" style="border-left-color:${col.stroke}">
            <strong>${lbl}</strong>
            <span>${pw}×${ph}</span>
        </div>`;
    }).join('');
    document.getElementById('panelList').innerHTML = html;
    document.querySelectorAll('.panel-list-item').forEach(el =>
        el.addEventListener('click', () => { state.sel = el.dataset.id; render(); })
    );
}

function updateLegend() {
    const used = [...new Set(leaves(state.tree).map(l => l.node.system))];
    const html = used.map(s => {
        const c = SYS[s] || SYS.fijo;
        return `<div class="sys-legend-item"><span class="sys-swatch" style="background:${c.fill};border-color:${c.stroke}"></span>${c.name}</div>`;
    }).join('');
    document.getElementById('sysLegend').innerHTML = html;
}

// ══════════════════════════════════════════════════════════
//  MAIN RENDER
// ══════════════════════════════════════════════════════════

function render() {
    document.getElementById('canvasWrap').innerHTML = buildSVG();
    document.querySelectorAll('.panel-r').forEach(el =>
        el.addEventListener('click', () => { state.sel = el.dataset.id; render(); })
    );
    updateForm();
    updatePanelList();
    updateLegend();
}

// ══════════════════════════════════════════════════════════
//  EVENTOS
// ══════════════════════════════════════════════════════════

document.getElementById('btnSplitV').addEventListener('click', () => splitNode('v'));
document.getElementById('btnSplitH').addEventListener('click', () => splitNode('h'));
document.getElementById('btnUnsplit').addEventListener('click', unsplitParent);

['facadeW', 'facadeH'].forEach(id =>
    document.getElementById(id).addEventListener('input', e => {
        state[id] = parseInt(e.target.value) || (id === 'facadeW' ? 2400 : 1200);
        render();
    })
);

document.getElementById('panelSystem').addEventListener('change', e => {
    const n = find(state.tree, state.sel);
    if (n && !n.split) { n.system = e.target.value; render(); }
});
document.getElementById('panelOpening').addEventListener('change', e => {
    const n = find(state.tree, state.sel);
    if (n && !n.split) { n.opening = e.target.value; render(); }
});
document.getElementById('panelLabel').addEventListener('input', e => {
    const n = find(state.tree, state.sel);
    if (n && !n.split) { n.label = e.target.value; render(); }
});
document.getElementById('splitRatio').addEventListener('input', e => {
    const parent = findParent(state.tree, state.sel);
    if (parent && parent.split) { parent.split.ratio = parseInt(e.target.value) / 100; render(); }
});

// Render inicial
render();
</script>
</body>
</html>
