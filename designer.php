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
    <title>Configurador – <?= h(tr('app_title', $lang)) ?></title>
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
            <h3>Serie</h3>
            <select id="serieType">
                <option value="s28">EXTRUAL Serie 28</option>
            </select>
        </div>

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
                <label id="extrualSysRow">Tipo EXTRUAL S28
                    <select id="panelExtrualSystem"></select>
                </label>
                <label>Vidrio (descompuesto)
                    <select id="panelGlassType">
                        <option value="4_12_4" selected>4+12+4 (C-20mm)</option>
                        <option value="4_6_4">4+6+4 (C-16mm)</option>
                        <option value="4_16_4">4+16+4 (C-24mm)</option>
                        <option value="4_8_4_8_4">4+8+4+8+4 doble</option>
                        <option value="laminar_6">Laminado 6mm</option>
                        <option value="laminar_88">Laminado 8.8mm</option>
                        <option value="simple_4">Simple 4mm</option>
                    </select>
                </label>
                <label>Junquillo
                    <select id="panelJunquillo">
                        <option value="curvo_clip" selected>Curvo clip</option>
                        <option value="curvo_grapa">Curvo grapa</option>
                        <option value="recto">Recto</option>
                        <option value="redondo_grapa">Redondo grapa</option>
                        <option value="redondo_clip">Redondo clip</option>
                    </select>
                </label>
                <div id="pasoLibreRow" style="display:none">
                    <label>Paso libre (mm)
                        <input type="number" id="pasoLibre" min="400" max="2500" step="1" placeholder="ej. 950">
                    </label>
                    <p class="field-hint" id="pasoLibreInfo"></p>
                </div>
                <label>Etiqueta (opcional)
                    <input type="text" id="panelLabel" placeholder="V1, P1, F1…">
                </label>
            </div>
        </div>

        <div class="form-section" id="splitControls" style="display:none">
            <h3>Posición del corte</h3>
            <label>Desde el inicio
                <div class="split-pos-row">
                    <input type="range" id="splitRatio" min="10" max="90" value="50" step="1">
                    <input type="number" id="splitMm" min="10" step="1" class="split-mm-input">
                    <span class="split-mm-unit">mm</span>
                </div>
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

    <!-- ── ÁREA DERECHA: CANVAS + BOM ── -->
    <section class="designer-canvas-area">
        <div class="canvas-hint">
            Clic en un panel para seleccionarlo · Arrastra montantes/travesaños para reposicionarlos · Dimensiones en mm
        </div>
        <div class="canvas-wrap" id="canvasWrap"></div>
        <div class="bom-section" id="bomSection">
            <div class="bom-header">
                <h3>Descompuesto EXTRUAL S28</h3>
                <span id="bomPanelCount" class="field-hint"></span>
            </div>
            <div id="bomResults"></div>
        </div>
    </section>

</main>

<script src="assets/extrual_s28.js"></script>
<script>
// ══════════════════════════════════════════════════════════
//  MODELO DE DATOS
// ══════════════════════════════════════════════════════════

function uid() { return Math.random().toString(36).slice(2, 8); }

const GLASS_THICK_MAP = {
    '4_6_4': 16, '4_12_4': 20, '4_16_4': 24,
    '4_8_4_8_4': 36, 'laminar_6': 6, 'laminar_88': 8.8, 'simple_4': 4,
};

function mkLeaf(system, label, opening) {
    return {
        id: uid(), split: null,
        system: system || 'fijo', label: label || '', opening: opening || 'izq',
        extrualSystem: null,
        pasoLibre: null,
        glassType: '4_12_4',
        glassThick: 20,
        junquilloType: 'curvo_clip',
    };
}

const SYS = {
    fijo:           { name: 'Fijo',           fill: '#d0e8f4', stroke: '#3a70a0' },
    practicable:    { name: 'Practicable',     fill: '#cceedd', stroke: '#2a7a3a' },
    oscilobatiente: { name: 'Oscilobatiente',  fill: '#f0eacc', stroke: '#907020' },
    abatible:       { name: 'Abatible',        fill: '#cceef4', stroke: '#1a7898' },
    corredera:      { name: 'Corredera',       fill: '#e4d0f4', stroke: '#6828a0' },
    puerta:         { name: 'Puerta',          fill: '#f4ddd0', stroke: '#a04820' },
};

const NEEDS_OPENING = ['practicable', 'oscilobatiente', 'corredera', 'puerta'];

const SYSTEM_TO_EXTRUAL = {
    fijo:           [['v_fijo',    'Ventana Fija']],
    practicable:    [['v1h_prac',  'Ventana 1H Practicable'],
                     ['v2h_prac',  'Ventana 2H Practicable'],
                     ['v3h_prac',  'Ventana 3H Practicable'],
                     ['b1h_prac',  'Balconera 1H Practicable'],
                     ['b2h_prac',  'Balconera 2H Practicable']],
    oscilobatiente: [['v1h_osci',  'Ventana 1H Oscilobatiente']],
    abatible:       [['v_abatible','Ventana Abatible'],
                     ['b1h_ext',   'Balconera Ap. Exterior']],
    corredera:      [],
    puerta:         [['p1h_int',   'Puerta 1H Interior'],
                     ['p1h_fijo',  'Puerta 1H + Fijo']],
};

function autoMapExtrual(system) {
    const opts = SYSTEM_TO_EXTRUAL[system];
    return opts && opts.length ? opts[0][0] : null;
}

let state = {
    facadeW: 2400,
    facadeH: 1200,
    tree: null,
    sel: null,
    svgScale: 1,
    svgOx: 0,
    svgOy: 0,
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

function leaves(node, x, y, w, h) {
    x = x || 0; y = y || 0; w = w === undefined ? 1 : w; h = h === undefined ? 1 : h;
    if (!node.split) return [{ node, x, y, w, h }];
    const sp = node.split;
    if (sp.dir === 'v')
        return leaves(sp.a, x, y, w * sp.ratio, h)
            .concat(leaves(sp.b, x + w * sp.ratio, y, w * (1 - sp.ratio), h));
    return leaves(sp.a, x, y, w, h * sp.ratio)
        .concat(leaves(sp.b, x, y + h * sp.ratio, w, h * (1 - sp.ratio)));
}

function allSplits(node, x, y, w, h) {
    x = x || 0; y = y || 0; w = w === undefined ? 1 : w; h = h === undefined ? 1 : h;
    if (!node.split) return [];
    const sp = node.split;
    const res = [{ node, x, y, w, h }];
    if (sp.dir === 'v')
        return res
            .concat(allSplits(sp.a, x, y, w * sp.ratio, h))
            .concat(allSplits(sp.b, x + w * sp.ratio, y, w * (1 - sp.ratio), h));
    return res
        .concat(allSplits(sp.a, x, y, w, h * sp.ratio))
        .concat(allSplits(sp.b, x, y + h * sp.ratio, w, h * (1 - sp.ratio)));
}

function getNodeBounds(nodeId) {
    function traverse(node, x, y, w, h) {
        if (node.id === nodeId) return { x, y, w, h };
        if (!node.split) return null;
        const sp = node.split;
        if (sp.dir === 'v')
            return traverse(sp.a, x, y, w * sp.ratio, h) ||
                   traverse(sp.b, x + w * sp.ratio, y, w * (1 - sp.ratio), h);
        return traverse(sp.a, x, y, w, h * sp.ratio) ||
               traverse(sp.b, x, y + h * sp.ratio, w, h * (1 - sp.ratio));
    }
    return traverse(state.tree, 0, 0, 1, 1);
}

function splitNode(dir) {
    const node = find(state.tree, state.sel);
    if (!node || node.split) return;
    const a = mkLeaf(node.system, node.label, node.opening);
    const b = mkLeaf(node.system, '', node.opening);
    // Inherit glass/junquillo from parent
    a.glassType = node.glassType; a.glassThick = node.glassThick; a.junquilloType = node.junquilloType;
    b.glassType = node.glassType; b.glassThick = node.glassThick; b.junquilloType = node.junquilloType;
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
    const FR  = Math.max(6, Math.min(14, sc * 24));

    // Store for drag handler
    state.svgScale = sc;
    state.svgOx = ox;
    state.svgOy = oy;
    state.svgW  = W;
    state.svgH  = H;
    state.svgFR = FR;

    let s = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${SVG_W} ${SVG_H}" width="${SVG_W}" height="${SVG_H}" id="mainSvg">`;

    s += `<defs>
      <pattern id="glass" patternUnits="userSpaceOnUse" width="14" height="14">
        <line x1="0" y1="14" x2="14" y2="0" stroke="#8ab4cc" stroke-width="0.6" opacity="0.5"/>
      </pattern>
      <linearGradient id="profileGradH" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#6a7880"/>
        <stop offset="45%" stop-color="#3e4c56"/>
        <stop offset="100%" stop-color="#252e35"/>
      </linearGradient>
      <linearGradient id="profileGradV" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%" stop-color="#4a5860"/>
        <stop offset="100%" stop-color="#2a3540"/>
      </linearGradient>
      <linearGradient id="glassGrad" x1="0" y1="0" x2="0.25" y2="1">
        <stop offset="0%" stop-color="#cce0f0" stop-opacity="0.95"/>
        <stop offset="100%" stop-color="#96c0dc" stop-opacity="0.85"/>
      </linearGradient>
      <filter id="frameShadow" x="-5%" y="-5%" width="115%" height="115%">
        <feDropShadow dx="2" dy="3" stdDeviation="3" flood-color="#1a2530" flood-opacity="0.35"/>
      </filter>
    </defs>`;

    // Sombra exterior del marco
    s += `<rect x="${ox - FR - 1}" y="${oy - FR - 1}" width="${W + FR*2 + 2}" height="${H + FR*2 + 2}" fill="#1a2530" opacity="0.25" rx="5" transform="translate(3,4)"/>`;

    // Marco exterior: cara frontal (fondo oscuro)
    s += `<rect x="${ox - FR}" y="${oy - FR}" width="${W + FR*2}" height="${H + FR*2}" fill="#3a4750" rx="3"/>`;
    // Cara superior (más clara)
    s += `<polygon points="${ox-FR},${oy-FR} ${ox+W+FR},${oy-FR} ${ox+W},${oy} ${ox},${oy}" fill="#5a6870" rx="2"/>`;
    // Cara inferior
    s += `<polygon points="${ox},${oy+H} ${ox+W},${oy+H} ${ox+W+FR},${oy+H+FR} ${ox-FR},${oy+H+FR}" fill="#252e35"/>`;
    // Cara izquierda
    s += `<polygon points="${ox-FR},${oy-FR} ${ox},${oy} ${ox},${oy+H} ${ox-FR},${oy+H+FR}" fill="#3e4c58"/>`;
    // Cara derecha
    s += `<polygon points="${ox+W},${oy} ${ox+W+FR},${oy-FR} ${ox+W+FR},${oy+H+FR} ${ox+W},${oy+H}" fill="#2a363f"/>`;

    // ── Paneles hoja ────────────────────────────────────────
    const leafs = leaves(state.tree);
    for (const { node, x, y, w, h } of leafs) {
        const px = ox + x * W, py = oy + y * H;
        const pw = w * W,      ph = h * H;
        const sel = node.id === state.sel;
        const col = SYS[node.system] || SYS.fijo;
        const panW = Math.round(fw * w), panH = Math.round(fh * h);

        // Perfil de contorno del panel
        s += `<rect x="${px}" y="${py}" width="${pw}" height="${ph}" fill="#3a4750"/>`;

        const gi = FR * 0.5;
        const gx = px + gi, gy = py + gi, gw = pw - gi*2, gh = ph - gi*2;

        if (gw > 4 && gh > 4) {
            const isFijo = node.system === 'fijo';
            const HJ = isFijo ? 0 : Math.max(3, FR * 0.6); // grosor perfil hoja

            // Zona vidrio exterior (detrás del perfil hoja si lo hay)
            s += `<rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="${col.fill}"/>`;

            if (!isFijo && HJ > 0 && gw > HJ*3 && gh > HJ*3) {
                // Perfil de hoja (4 caras)
                s += `<rect x="${gx}" y="${gy}" width="${gw}" height="${HJ}" fill="url(#profileGradH)"/>`;
                s += `<rect x="${gx}" y="${gy+gh-HJ}" width="${gw}" height="${HJ}" fill="#252e35"/>`;
                s += `<rect x="${gx}" y="${gy}" width="${HJ}" height="${gh}" fill="url(#profileGradV)"/>`;
                s += `<rect x="${gx+gw-HJ}" y="${gy}" width="${HJ}" height="${gh}" fill="#2a363f"/>`;
                // Reflejos en perfil
                s += `<rect x="${gx+HJ*0.15}" y="${gy+HJ*0.1}" width="${gw-HJ*0.3}" height="${HJ*0.3}" fill="white" opacity="0.12"/>`;
            }

            // Vidrio interior
            const vx = gx + HJ, vy = gy + HJ;
            const vw = gw - HJ*2, vh = gh - HJ*2;

            if (vw > 2 && vh > 2) {
                s += `<rect x="${vx}" y="${vy}" width="${vw}" height="${vh}" fill="url(#glassGrad)" class="panel-r" data-id="${esc(node.id)}" style="cursor:pointer"/>`;
                s += `<rect x="${vx}" y="${vy}" width="${vw}" height="${vh}" fill="url(#glass)" pointer-events="none" opacity="0.5"/>`;
                // Reflejo diagonal
                const rpw = vw * 0.3, rph = vh;
                s += `<polygon points="${vx},${vy} ${vx+rpw},${vy} ${vx+rpw*0.3},${vy+rph} ${vx},${vy+rph}" fill="white" opacity="0.13" pointer-events="none"/>`;

                // Borde selección
                if (sel) {
                    s += `<rect x="${vx}" y="${vy}" width="${vw}" height="${vh}" fill="none" stroke="#1d4ed8" stroke-width="2.5" stroke-dasharray="7,3" pointer-events="none"/>`;
                }

                // Indicador de sistema
                s += sysIndicator(node.system, node.opening, vx, vy, vw, vh, col.stroke);

                // Etiqueta
                const cx = gx + gw/2, cy = gy + gh/2;
                const extrualKey = node.extrualSystem || autoMapExtrual(node.system);
                const extrualName = extrualKey && SYSTEMS[extrualKey] ? SYSTEMS[extrualKey].name : '';
                const lbl = esc(node.label || col.name);
                const fs  = Math.min(11.5, gw * 0.085, gh * 0.13);
                if (fs > 4.5 && gw > 35 && gh > 22) {
                    s += `<rect x="${cx - lbl.length*fs*0.33 - 4}" y="${cy - fs - (gh > 50 ? 10 : 0) - 4}" width="${lbl.length*fs*0.66 + 8}" height="${fs + 6}" fill="white" opacity="0.55" rx="3" pointer-events="none"/>`;
                    s += `<text x="${cx}" y="${cy - (gh > 50 ? 8 : 0)}" text-anchor="middle" dominant-baseline="middle" font-size="${fs.toFixed(1)}" font-family="system-ui,sans-serif" fill="#1a2730" font-weight="700" pointer-events="none">${lbl}</text>`;
                    if (gh > 50) {
                        const fsm = Math.min(9, fs * 0.78);
                        s += `<text x="${cx}" y="${cy + 10}" text-anchor="middle" font-size="${fsm.toFixed(1)}" font-family="monospace,sans-serif" fill="#364a58" pointer-events="none">${panW}×${panH}</text>`;
                        if (extrualName && gh > 70) {
                            const fse = Math.min(8, fsm * 0.85);
                            s += `<text x="${cx}" y="${cy + 10 + fsm + 3}" text-anchor="middle" font-size="${fse.toFixed(1)}" font-family="system-ui,sans-serif" fill="#5070a0" pointer-events="none">${esc(extrualName)}</text>`;
                        }
                    }
                }
            }
        }
    }

    // ── Handles de arrastre para divisores ──────────────────
    const splits = allSplits(state.tree);
    for (const { node, x, y, w, h } of splits) {
        const sp = node.split;
        const nx = ox + x * W, ny = oy + y * H;
        const nw = w * W,      nh = h * H;
        if (sp.dir === 'v') {
            const divX = nx + nw * sp.ratio;
            // Línea divisora visible
            s += `<line x1="${divX}" y1="${ny}" x2="${divX}" y2="${ny+nh}" stroke="#2a3840" stroke-width="3" pointer-events="none"/>`;
            // Hit area arrastrable
            s += `<rect class="div-handle" data-splitid="${esc(node.id)}" data-dir="v"
                   x="${divX-6}" y="${ny}" width="12" height="${nh}"
                   fill="rgba(255,255,255,0.01)" style="cursor:col-resize"/>`;
            // Indicador visual en el centro del handle
            const midY = ny + nh/2;
            s += `<circle cx="${divX}" cy="${midY}" r="5" fill="#5a7080" opacity="0.7" pointer-events="none"/>`;
            s += `<line x1="${divX-9}" y1="${midY}" x2="${divX+9}" y2="${midY}" stroke="white" stroke-width="1.5" opacity="0.8" pointer-events="none"/>`;
        } else {
            const divY = ny + nh * sp.ratio;
            s += `<line x1="${nx}" y1="${divY}" x2="${nx+nw}" y2="${divY}" stroke="#2a3840" stroke-width="3" pointer-events="none"/>`;
            s += `<rect class="div-handle" data-splitid="${esc(node.id)}" data-dir="h"
                   x="${nx}" y="${divY-6}" width="${nw}" height="12"
                   fill="rgba(255,255,255,0.01)" style="cursor:row-resize"/>`;
            const midX = nx + nw/2;
            s += `<circle cx="${midX}" cy="${divY}" r="5" fill="#5a7080" opacity="0.7" pointer-events="none"/>`;
            s += `<line x1="${midX}" y1="${divY-9}" x2="${midX}" y2="${divY+9}" stroke="white" stroke-width="1.5" opacity="0.8" pointer-events="none"/>`;
        }
    }

    // ── Cotas exteriores ─────────────────────────────────────
    const dyLabel = oy - 34;
    s += dimLine(ox, dyLabel + 10, ox + W, dyLabel + 10, true);
    s += `<rect x="${ox + W/2 - 28}" y="${dyLabel - 1}" width="56" height="14" fill="white" opacity="0.7" rx="3"/>`;
    s += `<text x="${ox + W/2}" y="${dyLabel + 10}" text-anchor="middle" dominant-baseline="middle" font-size="11" font-family="monospace,sans-serif" fill="#1a2830" font-weight="600">${fw} mm</text>`;

    const dxLabel = ox - 34;
    s += dimLine(dxLabel + 10, oy, dxLabel + 10, oy + H, false);
    s += `<rect x="${dxLabel - 25}" y="${oy + H/2 - 7}" width="50" height="14" fill="white" opacity="0.7" rx="3" transform="rotate(-90 ${dxLabel - 25 + 25} ${oy + H/2})"/>`;
    s += `<text x="${dxLabel + 4}" y="${oy + H/2}" text-anchor="middle" dominant-baseline="middle" font-size="11" font-family="monospace,sans-serif" fill="#1a2830" font-weight="600" transform="rotate(-90 ${dxLabel + 4} ${oy + H/2})">${fh} mm</text>`;

    // ── Cotas individuales de paneles ─────────────────────────
    for (const { x, w } of leafs) {
        if (leafs.length > 1) {
            const px = ox + x * W, pxe = px + w * W;
            const panW = Math.round(fw * w);
            const dy2 = oy + H + 18;
            s += `<line x1="${px+2}" y1="${dy2-3}" x2="${px+2}" y2="${dy2+3}" stroke="#6a8090" stroke-width="1"/>`;
            s += `<line x1="${pxe-2}" y1="${dy2-3}" x2="${pxe-2}" y2="${dy2+3}" stroke="#6a8090" stroke-width="1"/>`;
            s += `<line x1="${px+2}" y1="${dy2}" x2="${pxe-2}" y2="${dy2}" stroke="#6a8090" stroke-width="0.8" stroke-dasharray="3,2"/>`;
            s += `<text x="${px + w*W/2}" y="${dy2 + 9}" text-anchor="middle" font-size="8.5" font-family="monospace" fill="#6a8090">${panW}</text>`;
        }
    }

    s += `</svg>`;
    return s;
}

function dimLine(x1, y1, x2, y2, horiz) {
    const tick = horiz
        ? `<line x1="${x1}" y1="${y1-5}" x2="${x1}" y2="${y1+5}" stroke="#5a6a78" stroke-width="1.2"/>
           <line x1="${x2}" y1="${y1-5}" x2="${x2}" y2="${y1+5}" stroke="#5a6a78" stroke-width="1.2"/>`
        : `<line x1="${x1-5}" y1="${y1}" x2="${x1+5}" y2="${y1}" stroke="#5a6a78" stroke-width="1.2"/>
           <line x1="${x1-5}" y1="${y2}" x2="${x1+5}" y2="${y2}" stroke="#5a6a78" stroke-width="1.2"/>`;
    return `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#5a6a78" stroke-width="1"/>${tick}`;
}

function sysIndicator(sys, op, x, y, w, h, col) {
    if (w < 18 || h < 18) return '';
    const sw  = 1.5;
    const der = op === 'der';

    switch (sys) {
        case 'practicable': {
            const hx = der ? x + w - 4 : x + 4;
            const tx = der ? x + 4     : x + w - 4;
            return `<path d="M${hx},${y+4} L${hx},${y+h-4} L${tx},${y+h/2} Z" fill="${col}28" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/>
                    <circle cx="${hx}" cy="${y+h/2}" r="3.2" fill="${col}" pointer-events="none"/>`;
        }
        case 'oscilobatiente': {
            const hx = der ? x + w - 4 : x + 4;
            const tx = der ? x + 4     : x + w - 4;
            return `<path d="M${hx},${y+4} L${hx},${y+h-4} L${tx},${y+h/2} Z" fill="${col}20" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/>
                    <path d="M${x+4},${y+h-4} L${x+w-4},${y+h-4} L${x+w/2},${y+h*0.44} Z" fill="${col}20" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/>
                    <circle cx="${hx}" cy="${y+h/2}" r="3.2" fill="${col}" pointer-events="none"/>`;
        }
        case 'abatible': {
            return `<path d="M${x+4},${y+4} L${x+w-4},${y+4} L${x+w/2},${y+h*0.54} Z" fill="${col}20" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/>`;
        }
        case 'corredera': {
            const dir = der ? 1 : -1;
            const cx  = x + w / 2, cy = y + h / 2;
            const a   = Math.min(24, w * 0.28);
            return `<line x1="${cx - a}" y1="${cy}" x2="${cx + a}" y2="${cy}" stroke="${col}" stroke-width="${sw+0.2}" pointer-events="none"/>
                    <polyline points="${cx+a*0.5*dir},${cy-7} ${cx+a*dir},${cy} ${cx+a*0.5*dir},${cy+7}" fill="none" stroke="${col}" stroke-width="${sw}" pointer-events="none"/>
                    <line x1="${cx}" y1="${y+6}" x2="${cx}" y2="${y+h-6}" stroke="${col}" stroke-width="0.9" stroke-dasharray="4,3" pointer-events="none"/>`;
        }
        case 'puerta': {
            const hx = der ? x + w - 4 : x + 4;
            const sw2 = Math.min(w * 0.62, h * 0.62);
            const ex  = der ? hx - sw2 : hx + sw2;
            return `<rect x="${x+4}" y="${y+h-10}" width="${w-8}" height="7" fill="${col}30" stroke="${col}" stroke-width="1" rx="1.5" pointer-events="none"/>
                    <line x1="${hx}" y1="${y+4}" x2="${hx}" y2="${y+h-10}" stroke="${col}" stroke-width="2.5" pointer-events="none"/>
                    <path d="M${hx},${y+4} A${sw2},${sw2} 0 0 ${der?0:1} ${ex},${y+4}" fill="${col}18" stroke="${col}" stroke-width="${sw}" stroke-dasharray="5,2.5" pointer-events="none"/>`;
        }
        default: return '';
    }
}

// ══════════════════════════════════════════════════════════
//  DRAG DE DIVISORES
// ══════════════════════════════════════════════════════════

let dragState = null;

const canvasWrap = document.getElementById('canvasWrap');

canvasWrap.addEventListener('mousedown', e => {
    const handle = e.target.closest('.div-handle');
    if (!handle) return;
    e.preventDefault();
    const splitNode = find(state.tree, handle.dataset.splitid);
    if (!splitNode || !splitNode.split) return;
    const dir = splitNode.split.dir;
    const bounds = getNodeBounds(splitNode.id);
    const parentWmm = Math.round(state.facadeW * bounds.w);
    const parentHmm = Math.round(state.facadeH * bounds.h);
    dragState = {
        splitNode,
        dir,
        startRatio: splitNode.split.ratio,
        startMouse: dir === 'v' ? e.clientX : e.clientY,
        parentWmm,
        parentHmm,
    };
});

document.addEventListener('mousemove', e => {
    if (!dragState) return;
    const svgEl = document.getElementById('mainSvg');
    if (!svgEl) return;
    const svgRect = svgEl.getBoundingClientRect();
    const pxPerUnit = (dragState.dir === 'v' ? svgRect.width : svgRect.height) / (dragState.dir === 'v' ? SVG_W : SVG_H);
    const pxPerMm = state.svgScale * pxPerUnit;
    const delta = (dragState.dir === 'v' ? e.clientX : e.clientY) - dragState.startMouse;
    const deltaMm = delta / pxPerMm;
    const dimMm = dragState.dir === 'v' ? dragState.parentWmm : dragState.parentHmm;
    const newRatio = Math.max(0.05, Math.min(0.95, dragState.startRatio + deltaMm / dimMm));
    dragState.splitNode.split.ratio = newRatio;
    render();
});

document.addEventListener('mouseup', () => { dragState = null; });

// ══════════════════════════════════════════════════════════
//  FORM UPDATE
// ══════════════════════════════════════════════════════════

function updateExtrualOptions(system, currentExtrual) {
    const opts = SYSTEM_TO_EXTRUAL[system] || [];
    const sel = document.getElementById('panelExtrualSystem');
    const row = document.getElementById('extrualSysRow');
    if (opts.length === 0) { row.style.display = 'none'; return; }
    row.style.display = '';
    sel.innerHTML = opts.map(([key, name]) =>
        `<option value="${esc(key)}" ${key === currentExtrual ? 'selected' : ''}>${esc(name)}</option>`
    ).join('');
}

function updateForm() {
    const node   = find(state.tree, state.sel);
    const parent = node ? findParent(state.tree, state.sel) : null;
    const isLeaf = node && !node.split;

    if (node) {
        const leaf = leaves(state.tree).find(l => l.node.id === state.sel);
        if (leaf) {
            const pw = Math.round(state.facadeW * leaf.w);
            const ph = Math.round(state.facadeH * leaf.h);
            document.getElementById('panelInfo').textContent = `${pw} × ${ph} mm`;
        }
    }

    document.getElementById('leafControls').style.opacity = isLeaf ? '1' : '0.35';
    document.getElementById('leafControls').style.pointerEvents = isLeaf ? '' : 'none';

    if (isLeaf) {
        document.getElementById('panelSystem').value    = node.system || 'fijo';
        document.getElementById('panelLabel').value     = node.label  || '';
        document.getElementById('panelOpening').value   = node.opening || 'izq';
        document.getElementById('panelGlassType').value = node.glassType || '4_12_4';
        document.getElementById('panelJunquillo').value = node.junquilloType || 'curvo_clip';

        const needsOpening = NEEDS_OPENING.includes(node.system);
        document.getElementById('openingRow').style.display = needsOpening ? '' : 'none';

        updateExtrualOptions(node.system, node.extrualSystem || autoMapExtrual(node.system));

        // Paso libre
        const isPuerta = node.system === 'puerta';
        document.getElementById('pasoLibreRow').style.display = isPuerta ? '' : 'none';
        if (isPuerta) {
            if (node.pasoLibre) document.getElementById('pasoLibre').value = node.pasoLibre;
            else document.getElementById('pasoLibre').value = '';

            const parentSplit = parent && parent.split;
            if (parentSplit && parentSplit.dir === 'v') {
                const bounds = getNodeBounds(parent.id);
                const parentMm = Math.round(state.facadeW * bounds.w);
                const pl = node.pasoLibre || 0;
                const panelW = pl > 0 ? Math.round(pl + K.PUERTA_H_OFFSET) : '—';
                document.getElementById('pasoLibreInfo').textContent =
                    pl > 0 ? `Panel = ${panelW} mm · Hueco total = ${parentMm} mm` : `Hueco total = ${parentMm} mm`;
            } else {
                document.getElementById('pasoLibreInfo').textContent =
                    'Divide con montante para fijar el paso libre';
            }
        }
    }

    // Split controls
    if (parent && parent.split) {
        const pct = Math.round(parent.split.ratio * 100);
        document.getElementById('splitControls').style.display = '';
        document.getElementById('splitRatio').value = pct;
        document.getElementById('splitInfo').textContent = `${pct}% · ${100 - pct}%`;

        const parentBounds = getNodeBounds(parent.id);
        const parentMm = parent.split.dir === 'v'
            ? Math.round(state.facadeW * parentBounds.w)
            : Math.round(state.facadeH * parentBounds.h);
        const posMm = Math.round(parent.split.ratio * parentMm);
        document.getElementById('splitMm').value = posMm;
        document.getElementById('splitMm').max   = parentMm - 10;
        document.getElementById('splitMm').min   = 10;
    } else {
        document.getElementById('splitControls').style.display = 'none';
    }

    const canUnsplit = parent && parent.split && !parent.split.a.split && !parent.split.b.split;
    document.getElementById('btnUnsplit').disabled  = !canUnsplit;
    document.getElementById('btnSplitV').disabled   = !isLeaf;
    document.getElementById('btnSplitH').disabled   = !isLeaf;
}

function updatePanelList() {
    const leafs = leaves(state.tree);
    let i = 1;
    const html = leafs.map(({ node, w, h }) => {
        const col = SYS[node.system] || SYS.fijo;
        const pw  = Math.round(state.facadeW * w);
        const ph  = Math.round(state.facadeH * h);
        const lbl = node.label || `${col.name} ${i++}`;
        const sel = node.id === state.sel ? ' selected' : '';
        return `<div class="panel-list-item${sel}" data-id="${esc(node.id)}" style="border-left-color:${col.stroke}">
            <strong>${esc(lbl)}</strong>
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
//  BOM AUTOMÁTICO
// ══════════════════════════════════════════════════════════

function buildAndRenderBOM() {
    const leafs = leaves(state.tree);
    const bomPanels = [];

    for (const { node, w, h } of leafs) {
        const sysKey = node.extrualSystem || autoMapExtrual(node.system);
        if (!sysKey || !SYSTEMS[sysKey]) continue;
        const L = Math.round(state.facadeW * w);
        const H = Math.round(state.facadeH * h);
        const opts = {
            glassThick: node.glassThick || 20,
            junquilloType: node.junquilloType || 'curvo_clip',
        };
        const result = SYSTEMS[sysKey].calc(L, H, 1, opts);
        const panelLabel = node.label || (SYS[node.system] ? SYS[node.system].name : sysKey);
        bomPanels.push({ label: panelLabel, sysKey, L, H, result });
    }

    const countEl = document.getElementById('bomPanelCount');
    const resultsEl = document.getElementById('bomResults');

    if (bomPanels.length === 0) {
        countEl.textContent = '';
        resultsEl.innerHTML = '<p class="field-hint bom-empty">Asigna un tipo EXTRUAL a cada panel para ver el descompuesto.</p>';
        return;
    }

    countEl.textContent = `${bomPanels.length} panel${bomPanels.length > 1 ? 'es' : ''}`;

    // Agregados
    const barTotals = {};
    const allGlass  = [];
    const accTotals = {};

    for (const { label, sysKey, L, H, result } of bomPanels) {
        for (const b of result.bars) {
            const key = b.ref + '|' + b.desc;
            if (!barTotals[key]) barTotals[key] = { ref: b.ref, desc: b.desc, cuts: [] };
            barTotals[key].cuts.push({ cut: b.cut, qty: b.qty, panel: label });
        }
        for (const g of result.glass) {
            allGlass.push({ ...g, panel: label, sysName: SYSTEMS[sysKey].name });
        }
        for (const a of result.accessories) {
            const key = a.ref + '|' + a.desc;
            if (!accTotals[key]) accTotals[key] = { ref: a.ref, desc: a.desc, qty: a.qty, note: a.note || '' };
        }
    }

    let html = '';

    // ── Tabla de barras ──
    html += `<h4 class="bom-table-title">Perfiles de aluminio</h4>
    <table class="bom-table">
        <thead><tr><th>Ref.</th><th>Descripción</th><th>Panel</th><th>Corte (mm)</th><th>Cant.</th><th>Total ml</th></tr></thead>
        <tbody>`;

    let totalAlMm = 0;
    for (const [, bar] of Object.entries(barTotals)) {
        for (const cut of bar.cuts) {
            const ml = (cut.cut * cut.qty / 1000).toFixed(3);
            totalAlMm += cut.cut * cut.qty;
            html += `<tr>
                <td class="ref-cell">${esc(bar.ref)}</td>
                <td>${esc(PROFILES[bar.ref] || bar.desc)}</td>
                <td class="panel-cell">${esc(cut.panel)}</td>
                <td>${Math.round(cut.cut)}</td>
                <td>${cut.qty}</td>
                <td>${ml}</td>
            </tr>`;
        }
    }
    html += `</tbody>
        <tfoot><tr><td colspan="5"><strong>Total aluminio</strong></td><td><strong>${(totalAlMm/1000).toFixed(3)} ml</strong></td></tr></tfoot>
    </table>`;

    // ── Tabla de vidrio ──
    html += `<h4 class="bom-table-title">Vidrio</h4>
    <table class="bom-table">
        <thead><tr><th>Panel</th><th>Descripción</th><th>Ancho (mm)</th><th>Alto (mm)</th><th>Cant.</th><th>m²</th></tr></thead>
        <tbody>`;
    let totalM2 = 0;
    for (const g of allGlass) {
        const m2 = (g.W / 1000) * (g.H / 1000) * g.qty;
        totalM2 += m2;
        const cls = g.W <= 0 || g.H <= 0 ? 'cell-error' : '';
        html += `<tr>
            <td class="panel-cell">${esc(g.panel)}</td>
            <td>${esc(g.desc)}</td>
            <td class="${cls}">${Math.round(g.W)}</td>
            <td class="${cls}">${Math.round(g.H)}</td>
            <td>${g.qty}</td>
            <td>${m2.toFixed(3)}</td>
        </tr>`;
    }
    html += `</tbody>
        <tfoot><tr><td colspan="5"><strong>Total m² vidrio</strong></td><td><strong>${totalM2.toFixed(3)} m²</strong></td></tr></tfoot>
    </table>`;

    // ── Accesorios ──
    html += `<h4 class="bom-table-title">Accesorios</h4>
    <table class="bom-table">
        <thead><tr><th>Ref.</th><th>Descripción</th><th>Cant.</th><th>Nota</th></tr></thead>
        <tbody>`;
    for (const [, a] of Object.entries(accTotals)) {
        html += `<tr>
            <td class="ref-cell">${esc(a.ref)}</td>
            <td>${esc(a.desc)}</td>
            <td>${esc(a.qty)}</td>
            <td class="note-cell">${esc(a.note)}</td>
        </tr>`;
    }
    html += `</tbody></table>`;

    html += `<p class="bom-note">Cotas S28: cara marco = 21.8 mm · desc. hoja = 43.6 mm · desc. puerta = 73.6 mm</p>`;

    resultsEl.innerHTML = html;
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
    buildAndRenderBOM();
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
    if (!n || n.split) return;
    n.system = e.target.value;
    n.extrualSystem = autoMapExtrual(e.target.value);
    if (e.target.value !== 'puerta') n.pasoLibre = null;
    render();
});

document.getElementById('panelOpening').addEventListener('change', e => {
    const n = find(state.tree, state.sel);
    if (n && !n.split) { n.opening = e.target.value; render(); }
});

document.getElementById('panelExtrualSystem').addEventListener('change', e => {
    const n = find(state.tree, state.sel);
    if (n && !n.split) { n.extrualSystem = e.target.value; render(); }
});

document.getElementById('panelGlassType').addEventListener('change', e => {
    const n = find(state.tree, state.sel);
    if (!n || n.split) return;
    n.glassType  = e.target.value;
    n.glassThick = GLASS_THICK_MAP[e.target.value] || 20;
    render();
});

document.getElementById('panelJunquillo').addEventListener('change', e => {
    const n = find(state.tree, state.sel);
    if (n && !n.split) { n.junquilloType = e.target.value; render(); }
});

document.getElementById('panelLabel').addEventListener('input', e => {
    const n = find(state.tree, state.sel);
    if (n && !n.split) { n.label = e.target.value; render(); }
});

document.getElementById('pasoLibre').addEventListener('input', e => {
    const n = find(state.tree, state.sel);
    if (!n || n.split) return;
    const pl = parseInt(e.target.value) || 0;
    n.pasoLibre = pl > 0 ? pl : null;
    if (pl > 0) {
        const parent = findParent(state.tree, state.sel);
        if (parent && parent.split && parent.split.dir === 'v') {
            const requiredW = pl + K.PUERTA_H_OFFSET;
            const bounds = getNodeBounds(parent.id);
            const parentMm = Math.round(state.facadeW * bounds.w);
            if (parentMm > 0) {
                const isA = parent.split.a.id === state.sel;
                const ratio = requiredW / parentMm;
                parent.split.ratio = Math.max(0.05, Math.min(0.95, isA ? ratio : 1 - ratio));
            }
        }
    }
    render();
});

// Slider %
document.getElementById('splitRatio').addEventListener('input', e => {
    const parent = findParent(state.tree, state.sel);
    if (parent && parent.split) {
        parent.split.ratio = parseInt(e.target.value) / 100;
        render();
    }
});

// Input mm
document.getElementById('splitMm').addEventListener('input', e => {
    const parent = findParent(state.tree, state.sel);
    if (!parent || !parent.split) return;
    const bounds = getNodeBounds(parent.id);
    const parentMm = parent.split.dir === 'v'
        ? Math.round(state.facadeW * bounds.w)
        : Math.round(state.facadeH * bounds.h);
    const mm = Math.max(10, Math.min(parentMm - 10, parseInt(e.target.value) || 0));
    parent.split.ratio = mm / parentMm;
    render();
});

// Render inicial
render();
</script>
</body>
</html>
