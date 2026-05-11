<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/helpers.php';
$lang = get_current_lang();
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Descompuesto Serie 28 – <?= h(tr('app_title', $lang)) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/descompuesto.css">
</head>
<body>
<div class="background-shape shape-a"></div>
<div class="background-shape shape-b"></div>

<header class="topbar">
    <h1><?= h(tr('app_title', $lang)) ?></h1>
    <div class="topbar-tools">
        <nav>
            <a href="<?= h(url_with_lang('index.php', [], $lang)) ?>"><?= h(tr('new', $lang)) ?></a>
            <a href="descompuesto.php" class="active">Descompuesto</a>
            <a href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>"><?= h(tr('history', $lang)) ?></a>
        </nav>
    </div>
</header>

<main class="descomp-layout">

    <!-- ── PANEL IZQUIERDO: Formulario ── -->
    <aside class="descomp-form-panel">
        <h2>Descompuesto de barras</h2>
        <p class="field-hint">Serie 28 · EXTRUAL</p>

        <div class="form-section">
            <h3>Sistema</h3>
            <label>Tipo de carpintería
                <select id="systemType">
                    <optgroup label="Ventanas">
                        <option value="v1h_prac">Ventana 1 Hoja Practicable</option>
                        <option value="v1h_osci">Ventana 1 Hoja Oscilobatiente</option>
                        <option value="v1h_fijo">Ventana 1 Hoja + Fijo</option>
                        <option value="v2h_prac">Ventana 2 Hojas Practicable</option>
                        <option value="v2h_fijo">Ventana 2 Hojas + Fijo</option>
                        <option value="v3h_prac">Ventana 3 Hojas Practicable</option>
                        <option value="v_abatible">Ventana Abatible (proyectante)</option>
                        <option value="v_fijo">Ventana Fija</option>
                    </optgroup>
                    <optgroup label="Balconeras">
                        <option value="b1h_prac">Balconera 1 Hoja Practicable</option>
                        <option value="b2h_prac">Balconera 2 Hojas Practicable</option>
                        <option value="b1h_ext">Balconera 1 Hoja Apertura Exterior</option>
                    </optgroup>
                    <optgroup label="Puertas">
                        <option value="p1h_int">Puerta 1 Hoja Interior</option>
                        <option value="p1h_fijo">Puerta 1 Hoja + Fijo</option>
                    </optgroup>
                </select>
            </label>
        </div>

        <div class="form-section">
            <h3>Dimensiones del hueco</h3>
            <div class="grid two">
                <label>Ancho L (mm)
                    <input type="number" id="dimL" min="300" max="3000" value="1200" step="1">
                </label>
                <label>Alto H (mm)
                    <input type="number" id="dimH" min="300" max="3000" value="1400" step="1">
                </label>
            </div>
            <label>Unidades
                <input type="number" id="dimQty" min="1" max="999" value="1" step="1">
            </label>
        </div>

        <div class="form-section">
            <h3>Vidrio</h3>
            <label>Tipo de cámara
                <select id="glassType">
                    <option value="4_6_4">4+6+4 (C-16mm)</option>
                    <option value="4_12_4" selected>4+12+4 (C-20mm)</option>
                    <option value="4_16_4">4+16+4 (C-24mm)</option>
                    <option value="4_8_4_8_4">4+8+4+8+4 doble cámara</option>
                    <option value="laminar_6">Laminado 6mm</option>
                    <option value="laminar_88">Laminado 8.8mm</option>
                    <option value="simple_4">Simple 4mm</option>
                </select>
            </label>
            <label>Espesor total vidrio (mm)
                <input type="number" id="glassThick" min="4" max="50" value="20" step="1">
            </label>
        </div>

        <div class="form-section">
            <h3>Opciones de marco</h3>
            <label>Forro/Tapeta
                <select id="forroType">
                    <option value="none">Sin forro</option>
                    <option value="40">Forro Registro 40mm (6.755)</option>
                    <option value="60">Forro Registro 60mm (6.756)</option>
                    <option value="85">Forro Registro 85mm (6.757)</option>
                </select>
            </label>
            <label>Premarco de obra
                <select id="premarcoType">
                    <option value="none">Sin premarco</option>
                    <option value="36">Premarco Obra 36mm (9.213)</option>
                    <option value="122">Premarco Obra 122mm (9.214)</option>
                    <option value="136">Premarco Obra 136mm (9.215)</option>
                </select>
            </label>
        </div>

        <div class="form-section">
            <h3>Junquillo</h3>
            <label>Tipo de junquillo
                <select id="junquilloType">
                    <option value="curvo_grapa">Curvo grapa (5.070/5.071)</option>
                    <option value="curvo_clip" selected>Curvo clip (6.179/6.180)</option>
                    <option value="recto">Recto C-14.5 (3.360)</option>
                    <option value="redondo_grapa">Redondo grapa (5.612/5.613)</option>
                    <option value="redondo_clip">Redondo clip (6.181/6.182)</option>
                </select>
            </label>
        </div>

        <button class="print-btn" onclick="window.print()">Imprimir / PDF</button>
    </aside>

    <!-- ── PANEL DERECHO: Resultados ── -->
    <section class="descomp-results-panel">
        <div id="resultsBox">
            <!-- JS rellena aquí -->
        </div>
    </section>

</main>

<script>
// ══════════════════════════════════════════════════════════
//  DATOS SERIE 28 · EXTRUAL
// ══════════════════════════════════════════════════════════

// Diccionario de perfiles: ref → descripción
const PROFILES = {
    '5.980':  'Marco Ventana',
    '5.981':  'Marco Ventana con solape 28mm',
    '5.982':  'Hoja Ventana 47mm',
    '5.984':  'Inversor recto',
    '5.985':  'Pilastra Ventana',
    '5.986':  'Marco Puerta',
    '5.987':  'Hoja Balconera',
    '5.988':  'Hoja Balconera apertura exterior',
    '5.989':  'Pilastra Puerta',
    '5.228':  'Marco Fijo liso',
    '9.619':  'Vierteaguas hoja',
    '9.621':  'Marco bajo Puerta',
    '9.622':  'Marco Puerta bajo',
    '9.623':  'Tope marco inferior',
    '6.064':  'Solape Grapa 30mm',
    '6.069':  'Guía Compacto 120mm',
    '6.755':  'Forro Registro Recto 40mm',
    '6.756':  'Forro Registro Recto 60mm',
    '6.757':  'Forro Registro Recto 85mm',
    '9.213':  'Premarco de Obra 36mm',
    '9.214':  'Premarco de Obra 122mm',
    '9.215':  'Premarco de Obra 136mm',
    '3.360':  'Junquillo Recto C-14.5',
    '5.070':  'Junquillo Curvo grapa C-8.5',
    '5.071':  'Junquillo Curvo grapa C-20.5',
    '5.612':  'Junquillo Redondo grapa C-8.5',
    '5.613':  'Junquillo Redondo grapa C-20.5',
    '6.179':  'Junquillo Curvo clip C-8.5',
    '6.180':  'Junquillo Curvo clip C-20.5',
    '6.181':  'Junquillo Redondo clip C-8.5',
    '6.182':  'Junquillo Redondo clip C-20.5',
};

// ── Formulas de corte ──────────────────────────────────────
// L = ancho hueco, H = alto hueco
// Constantes geométricas Serie 28
const K = {
    MARCO_CARA:     21.8,   // mm que ocupa el marco en cada lateral
    MARCO_CARA2:    43.6,   // 2 × MARCO_CARA
    GLASS_OFFSET_1H: 108,   // L - 108 = ancho vidrio en 1 hoja
    GLASS_OFFSET_H:  108,   // H - 108 = alto vidrio
    HOJA_OFFSET:    43.6,   // L/n - 43.6 = corte horizontal hoja (1h)
    HOJA_OFFSET_2H: 26,     // L/2 - 26 = corte horizontal hoja (2h)
    GLASS_OFFSET_2H: 88,    // L/2 - 88 = ancho vidrio en 2 hojas
    VIERTEG_EXTRA:   5,     // hoja - 5 = longitud vierteaguas
    JUNQ_EXTRA:     12,     // vidrio + 12 = longitud junquillo
    // Puerta
    PUERTA_H_OFFSET: 73.6,  // L - 73.6 = hoja puerta horizontal
    PUERTA_H_GLASS:  138,   // L - 138 = vidrio puerta
    FIJO_OFFSET:    48,     // fijo: L/H - 48 = espacio vidrio
};

// Junquillo refs según cámara y tipo
function junquilloRef(glassThick, junqType) {
    // Cámara ≤ 20.5mm → C-8.5 o C-14.5; > 20.5mm → C-20.5 o C-26.5
    const heavy = glassThick > 20;
    const types = {
        'curvo_grapa':   heavy ? '5.071' : '5.070',
        'curvo_clip':    heavy ? '6.180' : '6.179',
        'recto':         '3.360',
        'redondo_grapa': heavy ? '5.613' : '5.612',
        'redondo_clip':  heavy ? '6.182' : '6.181',
    };
    return types[junqType] || '6.179';
}

// ── Definición de sistemas ─────────────────────────────────
// Cada sistema devuelve barras y accesorios dado (L, H, qty, opts)
// bars: [{ ref, desc, cut_formula_str, cut_mm, qty_per_unit }]
// glass: [{ desc, W, H, qty_per_unit }]
// accessories: [{ ref, desc, qty_per_unit, qty_note }]

const SYSTEMS = {

    // ────────────────────────────────────────────────────
    v1h_prac: {
        name: 'Ventana 1 Hoja Practicable',
        page: '28-B1',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            const gW = L - K.GLASS_OFFSET_1H;
            const gH = H - K.GLASS_OFFSET_H;
            const hojaH = L - K.HOJA_OFFSET;
            const hojaV = H - K.HOJA_OFFSET;
            return {
                bars: [
                    { ref:'5.980', desc:'Marco horizontal',   cut: L,                 qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',     cut: H,                 qty: 2, formula: 'H' },
                    { ref:'5.982', desc:'Hoja horizontal',    cut: hojaH,             qty: 2, formula: 'L − 43.6' },
                    { ref:'5.982', desc:'Hoja vertical',      cut: hojaV,             qty: 2, formula: 'H − 43.6' },
                    { ref:'9.619', desc:'Vierteaguas hoja',   cut: hojaH - K.VIERTEG_EXTRA, qty: 1, formula: 'L − 48.6' },
                    { ref: jRef,   desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: `vidrio_L + 12` },
                    { ref: jRef,   desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: `vidrio_H + 12` },
                ],
                glass: [{ desc: 'Vidrio hoja', W: gW, H: gH, qty: 1 }],
                accessories: accesorios_v1h_prac(false),
            };
        },
    },

    // ────────────────────────────────────────────────────
    v1h_osci: {
        name: 'Ventana 1 Hoja Oscilobatiente',
        page: '28-B1',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            const gW = L - K.GLASS_OFFSET_1H;
            const gH = H - K.GLASS_OFFSET_H;
            const hojaH = L - K.HOJA_OFFSET;
            const hojaV = H - K.HOJA_OFFSET;
            return {
                bars: [
                    { ref:'5.980', desc:'Marco horizontal',   cut: L,      qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',     cut: H,      qty: 2, formula: 'H' },
                    { ref:'5.982', desc:'Hoja horizontal',    cut: hojaH,  qty: 2, formula: 'L − 43.6' },
                    { ref:'5.982', desc:'Hoja vertical',      cut: hojaV,  qty: 2, formula: 'H − 43.6' },
                    { ref:'9.619', desc:'Vierteaguas hoja',   cut: hojaH - K.VIERTEG_EXTRA, qty: 1, formula: 'L − 48.6' },
                    { ref: jRef,   desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,   desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H + 12' },
                ],
                glass: [{ desc: 'Vidrio hoja', W: gW, H: gH, qty: 1 }],
                accessories: accesorios_v1h_prac(true),
            };
        },
    },

    // ────────────────────────────────────────────────────
    v1h_fijo: {
        name: 'Ventana 1 Hoja + Fijo',
        page: '28-B2',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            // Hoja ocupa ~55% del ancho, fijo el 45% (ajustar según diseño)
            const L1 = Math.round(L * 0.55);
            const L2 = L - L1;
            const hojaH = L1 - K.HOJA_OFFSET;
            const hojaV = H - K.HOJA_OFFSET;
            const gWHoja = L1 - K.GLASS_OFFSET_1H;
            const gWFijo = L2 - K.FIJO_OFFSET;
            const gH = H - K.GLASS_OFFSET_H;
            return {
                bars: [
                    { ref:'5.980', desc:'Marco horizontal',    cut: L,      qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',      cut: H,      qty: 2, formula: 'H' },
                    { ref:'5.985', desc:'Pilastra ventana',    cut: H,      qty: 1, formula: 'H' },
                    { ref:'5.982', desc:'Hoja horizontal',     cut: hojaH,  qty: 2, formula: 'L1 − 43.6' },
                    { ref:'5.982', desc:'Hoja vertical',       cut: hojaV,  qty: 2, formula: 'H − 43.6' },
                    { ref:'9.619', desc:'Vierteaguas hoja',    cut: hojaH - K.VIERTEG_EXTRA, qty: 1, formula: 'L1 − 48.6' },
                    { ref: jRef,   desc:'Junquillo H (hoja)',  cut: gWHoja + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H_L + 12' },
                    { ref: jRef,   desc:'Junquillo V (hoja)',  cut: gH + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_H + 12' },
                    { ref: jRef,   desc:'Junquillo H (fijo)',  cut: gWFijo + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_F_L + 12' },
                    { ref: jRef,   desc:'Junquillo V (fijo)',  cut: gH + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_H + 12' },
                ],
                glass: [
                    { desc: 'Vidrio hoja',  W: gWHoja, H: gH, qty: 1 },
                    { desc: 'Vidrio fijo',  W: gWFijo, H: gH, qty: 1 },
                ],
                accessories: accesorios_v1h_prac(false),
            };
        },
    },

    // ────────────────────────────────────────────────────
    v2h_prac: {
        name: 'Ventana 2 Hojas Practicable',
        page: '28-B3',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            const hojaH = Math.round(L / 2 - K.HOJA_OFFSET_2H);
            const hojaV = H - K.HOJA_OFFSET;
            const gW = Math.round(L / 2 - K.GLASS_OFFSET_2H);
            const gH = H - K.GLASS_OFFSET_H;
            const vierteH = hojaH - K.VIERTEG_EXTRA;
            return {
                bars: [
                    { ref:'5.980', desc:'Marco horizontal',    cut: L,      qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',      cut: H,      qty: 2, formula: 'H' },
                    { ref:'5.984', desc:'Inversor recto',      cut: hojaV,  qty: 1, formula: 'H − 43.6' },
                    { ref:'5.982', desc:'Hoja horizontal',     cut: hojaH,  qty: 4, formula: 'L/2 − 26' },
                    { ref:'5.982', desc:'Hoja vertical',       cut: hojaV,  qty: 4, formula: 'H − 43.6' },
                    { ref:'9.619', desc:'Vierteaguas (×2)',    cut: vierteH, qty: 2, formula: 'L/2 − 31' },
                    { ref: jRef,   desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 4, formula: 'vidrio_L + 12' },
                    { ref: jRef,   desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 4, formula: 'vidrio_H + 12' },
                ],
                glass: [{ desc: 'Vidrio por hoja', W: gW, H: gH, qty: 2 }],
                accessories: accesorios_v2h_prac(),
            };
        },
    },

    // ────────────────────────────────────────────────────
    v2h_fijo: {
        name: 'Ventana 2 Hojas + Fijo',
        page: '28-B4',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            // Las 2 hojas ocupan ~65%, fijo ~35%
            const L_hojas = Math.round(L * 0.65);
            const L_fijo  = L - L_hojas;
            const hojaH = Math.round(L_hojas / 2 - K.HOJA_OFFSET_2H);
            const hojaV = H - K.HOJA_OFFSET;
            const gW = Math.round(L_hojas / 2 - K.GLASS_OFFSET_2H);
            const gWFijo = L_fijo - K.FIJO_OFFSET;
            const gH = H - K.GLASS_OFFSET_H;
            return {
                bars: [
                    { ref:'5.980', desc:'Marco horizontal',     cut: L,       qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',       cut: H,       qty: 2, formula: 'H' },
                    { ref:'5.985', desc:'Pilastra ventana',     cut: H,       qty: 1, formula: 'H' },
                    { ref:'5.984', desc:'Inversor recto',       cut: hojaV,   qty: 1, formula: '(H−43.6)' },
                    { ref:'5.982', desc:'Hoja horizontal',      cut: hojaH,   qty: 4, formula: 'L_hojas/2 − 26' },
                    { ref:'5.982', desc:'Hoja vertical',        cut: hojaV,   qty: 4, formula: 'H − 43.6' },
                    { ref:'9.619', desc:'Vierteaguas (×2)',     cut: hojaH-5, qty: 2, formula: 'hoja_H − 5' },
                    { ref: jRef,   desc:'Junquillo H (hojas)',  cut: gW + K.JUNQ_EXTRA,     qty: 4, formula: 'vidrio_L + 12' },
                    { ref: jRef,   desc:'Junquillo V (hojas)',  cut: gH + K.JUNQ_EXTRA,     qty: 4, formula: 'vidrio_H + 12' },
                    { ref: jRef,   desc:'Junquillo H (fijo)',   cut: gWFijo + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_F_L + 12' },
                    { ref: jRef,   desc:'Junquillo V (fijo)',   cut: gH + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_H + 12' },
                ],
                glass: [
                    { desc: 'Vidrio por hoja', W: gW,     H: gH, qty: 2 },
                    { desc: 'Vidrio fijo',     W: gWFijo, H: gH, qty: 1 },
                ],
                accessories: accesorios_v2h_prac(),
            };
        },
    },

    // ────────────────────────────────────────────────────
    v3h_prac: {
        name: 'Ventana 3 Hojas Practicable',
        page: '28-B5',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            const hojaH = Math.round(L / 3 - K.HOJA_OFFSET_2H);
            const hojaV = H - K.HOJA_OFFSET;
            const gW = Math.round(L / 3 - K.GLASS_OFFSET_2H);
            const gH = H - K.GLASS_OFFSET_H;
            return {
                bars: [
                    { ref:'5.980', desc:'Marco horizontal',    cut: L,      qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',      cut: H,      qty: 2, formula: 'H' },
                    { ref:'5.984', desc:'Inversor recto (×2)', cut: hojaV,  qty: 2, formula: 'H − 43.6' },
                    { ref:'5.982', desc:'Hoja horizontal',     cut: hojaH,  qty: 6, formula: 'L/3 − 26' },
                    { ref:'5.982', desc:'Hoja vertical',       cut: hojaV,  qty: 6, formula: 'H − 43.6' },
                    { ref:'9.619', desc:'Vierteaguas (×3)',    cut: hojaH-5, qty: 3, formula: 'hoja_H − 5' },
                    { ref: jRef,   desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 6, formula: 'vidrio_L + 12' },
                    { ref: jRef,   desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 6, formula: 'vidrio_H + 12' },
                ],
                glass: [{ desc: 'Vidrio por hoja', W: gW, H: gH, qty: 3 }],
                accessories: accesorios_v3h_prac(),
            };
        },
    },

    // ────────────────────────────────────────────────────
    v_abatible: {
        name: 'Ventana Abatible (proyectante)',
        page: '28-B6',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            const gW = L - K.GLASS_OFFSET_1H;
            const gH = H - K.GLASS_OFFSET_H;
            const hojaH = L - K.HOJA_OFFSET;
            const hojaV = H - K.HOJA_OFFSET;
            return {
                bars: [
                    { ref:'5.980', desc:'Marco horizontal',   cut: L,     qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',     cut: H,     qty: 2, formula: 'H' },
                    { ref:'5.982', desc:'Hoja horizontal',    cut: hojaH, qty: 2, formula: 'L − 43.6' },
                    { ref:'5.982', desc:'Hoja vertical',      cut: hojaV, qty: 2, formula: 'H − 43.6' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H + 12' },
                ],
                glass: [{ desc: 'Vidrio', W: gW, H: gH, qty: 1 }],
                accessories: accesorios_abatible(),
            };
        },
    },

    // ────────────────────────────────────────────────────
    v_fijo: {
        name: 'Ventana Fija',
        page: '28',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            const gW = L - K.FIJO_OFFSET;
            const gH = H - K.FIJO_OFFSET;
            return {
                bars: [
                    { ref:'5.980', desc:'Marco horizontal',   cut: L,                   qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',     cut: H,                   qty: 2, formula: 'H' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA,  qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA,  qty: 2, formula: 'vidrio_H + 12' },
                ],
                glass: [{ desc: 'Vidrio fijo', W: gW, H: gH, qty: 1 }],
                accessories: accesorios_fijo(),
            };
        },
    },

    // ────────────────────────────────────────────────────
    b1h_prac: {
        name: 'Balconera 1 Hoja Practicable',
        page: '28-B10',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            const gW = L - K.GLASS_OFFSET_1H;
            const gH = H - K.GLASS_OFFSET_H;
            const hojaH = L - K.HOJA_OFFSET;
            const hojaV = H - K.HOJA_OFFSET;
            return {
                bars: [
                    { ref:'5.980', desc:'Marco horizontal',       cut: L,     qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',         cut: H,     qty: 2, formula: 'H' },
                    { ref:'5.987', desc:'Hoja Balconera horizontal', cut: hojaH, qty: 2, formula: 'L − 43.6' },
                    { ref:'5.987', desc:'Hoja Balconera vertical',   cut: hojaV, qty: 2, formula: 'H − 43.6' },
                    { ref:'9.619', desc:'Vierteaguas hoja',       cut: hojaH - K.VIERTEG_EXTRA, qty: 1, formula: 'L − 48.6' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H + 12' },
                ],
                glass: [{ desc: 'Vidrio hoja', W: gW, H: gH, qty: 1 }],
                accessories: accesorios_balconera(1),
            };
        },
    },

    // ────────────────────────────────────────────────────
    b2h_prac: {
        name: 'Balconera 2 Hojas Practicable',
        page: '28-B12',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            const hojaH = Math.round(L / 2 - K.HOJA_OFFSET_2H);
            const hojaV = H - K.HOJA_OFFSET;
            const gW = Math.round(L / 2 - K.GLASS_OFFSET_2H);
            const gH = H - K.GLASS_OFFSET_H;
            return {
                bars: [
                    { ref:'5.980', desc:'Marco horizontal',        cut: L,      qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',          cut: H,      qty: 2, formula: 'H' },
                    { ref:'5.984', desc:'Inversor recto',          cut: hojaV,  qty: 1, formula: 'H − 43.6' },
                    { ref:'5.987', desc:'Hoja Balconera horizontal', cut: hojaH, qty: 4, formula: 'L/2 − 26' },
                    { ref:'5.987', desc:'Hoja Balconera vertical',   cut: hojaV, qty: 4, formula: 'H − 43.6' },
                    { ref:'9.619', desc:'Vierteaguas (×2)',        cut: hojaH-5, qty: 2, formula: 'hoja_H − 5' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 4, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 4, formula: 'vidrio_H + 12' },
                ],
                glass: [{ desc: 'Vidrio por hoja', W: gW, H: gH, qty: 2 }],
                accessories: accesorios_balconera(2),
            };
        },
    },

    // ────────────────────────────────────────────────────
    b1h_ext: {
        name: 'Balconera 1 Hoja Apertura Exterior',
        page: '28-B12',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            const gW = L - K.GLASS_OFFSET_1H;
            const gH = H - K.GLASS_OFFSET_H;
            const hojaH = L - K.HOJA_OFFSET;
            const hojaV = H - K.HOJA_OFFSET;
            return {
                bars: [
                    { ref:'5.980', desc:'Marco horizontal',       cut: L,     qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',         cut: H,     qty: 2, formula: 'H' },
                    { ref:'5.988', desc:'Hoja Balconera Ap.Ext horizontal', cut: hojaH, qty: 2, formula: 'L − 43.6' },
                    { ref:'5.988', desc:'Hoja Balconera Ap.Ext vertical',   cut: hojaV, qty: 2, formula: 'H − 43.6' },
                    { ref:'9.619', desc:'Vierteaguas hoja',       cut: hojaH - K.VIERTEG_EXTRA, qty: 1, formula: 'L − 48.6' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H + 12' },
                ],
                glass: [{ desc: 'Vidrio hoja', W: gW, H: gH, qty: 1 }],
                accessories: accesorios_balconera_ext(),
            };
        },
    },

    // ────────────────────────────────────────────────────
    p1h_int: {
        name: 'Puerta 1 Hoja Interior',
        page: '28-B17',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            const hojaH = L - K.PUERTA_H_OFFSET;
            const hojaV = H - K.HOJA_OFFSET;
            const gW = L - K.PUERTA_H_GLASS;
            const gH = H - K.GLASS_OFFSET_H;
            return {
                bars: [
                    { ref:'5.986', desc:'Marco Puerta horizontal top',  cut: L,     qty: 1, formula: 'L' },
                    { ref:'5.986', desc:'Marco Puerta vertical (×2)',   cut: H,     qty: 2, formula: 'H' },
                    { ref:'9.622', desc:'Marco bajo Puerta',            cut: L,     qty: 1, formula: 'L' },
                    { ref:'5.987', desc:'Hoja Balconera horizontal',    cut: hojaH, qty: 2, formula: 'L − 73.6' },
                    { ref:'5.987', desc:'Hoja Balconera vertical',      cut: hojaV, qty: 2, formula: 'H − 43.6' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H + 12' },
                ],
                glass: [{ desc: 'Vidrio puerta', W: gW, H: gH, qty: 1 }],
                accessories: accesorios_puerta(),
            };
        },
    },

    // ────────────────────────────────────────────────────
    p1h_fijo: {
        name: 'Puerta 1 Hoja + Fijo',
        page: '28-B22',
        calc(L, H, qty, opts) {
            const jRef = junquilloRef(opts.glassThick, opts.junquilloType);
            const L_puerta = Math.round(L * 0.6);
            const L_fijo = L - L_puerta;
            const hojaH = L_puerta - K.PUERTA_H_OFFSET;
            const hojaV = H - K.HOJA_OFFSET;
            const gW = L_puerta - K.PUERTA_H_GLASS;
            const gWFijo = L_fijo - K.FIJO_OFFSET;
            const gH = H - K.GLASS_OFFSET_H;
            return {
                bars: [
                    { ref:'5.986', desc:'Marco Puerta horizontal',   cut: L,     qty: 1, formula: 'L' },
                    { ref:'5.986', desc:'Marco Puerta vertical (×2)', cut: H,    qty: 2, formula: 'H' },
                    { ref:'5.989', desc:'Pilastra Puerta',            cut: H,    qty: 1, formula: 'H' },
                    { ref:'9.622', desc:'Marco bajo Puerta',          cut: L_puerta, qty: 1, formula: 'L_puerta' },
                    { ref:'5.987', desc:'Hoja horizontal',            cut: hojaH, qty: 2, formula: 'L_puerta − 73.6' },
                    { ref:'5.987', desc:'Hoja vertical',              cut: hojaV, qty: 2, formula: 'H − 43.6' },
                    { ref: jRef,  desc:'Junquillo H (hoja)',  cut: gW + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo V (hoja)',  cut: gH + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_H + 12' },
                    { ref: jRef,  desc:'Junquillo H (fijo)',  cut: gWFijo + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_F_L + 12' },
                    { ref: jRef,  desc:'Junquillo V (fijo)',  cut: gH + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_H + 12' },
                ],
                glass: [
                    { desc: 'Vidrio puerta', W: gW,     H: gH, qty: 1 },
                    { desc: 'Vidrio fijo',   W: gWFijo, H: gH, qty: 1 },
                ],
                accessories: accesorios_puerta(),
            };
        },
    },
};

// ══════════════════════════════════════════════════════════
//  ACCESORIOS por sistema
// ══════════════════════════════════════════════════════════

function accesorios_v1h_prac(osci) {
    const list = [
        { ref:'04.APS.B11', desc:'Bisagra 80 Kg',                    qty:'2' },
        { ref:'04.APS.C01', desc:'Cremona Practicable',              qty:'1' },
        { ref:'04.APS.001', desc:'Kit de cierre Hoja Practicable',   qty:'1' },
        { ref:'04.OBS.020', desc:'Kit Base Ancho Hoja 360/1.400',    qty:'1', note:'según ancho' },
        { ref:'04.OBS.021', desc:'Kit Base Ancho Hoja 1.401/1.700',  qty:'1', note:'si ancho >1400' },
        { ref:'04.OBS.022', desc:'Compás Corte Ancho Hoja 360/500',  qty:'1', note:'según corte' },
        { ref:'04.OBS.023', desc:'Compás Estándar Ancho Hoja 510/794', qty:'1' },
        { ref:'04.OBS.030', desc:'Kit bisagras ambidextras 110 Kg',  qty:'1' },
        { ref:'04.ES.003',  desc:'Escuadra ventana',                 qty:'8' },
        { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',      qty:'4' },
        { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',       qty:'4' },
        { ref:'04.TA.016',  desc:'Tapa desagüe',                     qty:'2' },
        { ref:'04.AC.T01',  desc:'Tubo desagüe hoja',                qty:'2' },
        { ref:'04.JU.001',  desc:'Junta Central',                    qty:'2H+2L' },
        { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',              qty:'2H+2L' },
        { ref:'04.JU.004.D',desc:'Junta Interior EPDM',              qty:'2H+2L' },
        { ref:'04.AC.008',  desc:'Tornillo clipaje vierteaguas',     qty:'4 ml' },
        { ref:'04.TA.020',  desc:'Juego tapas vierteaguas hoja',     qty:'1' },
        { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',       qty:'2H+2L' },
        { ref:'SEGÚN VIDRIO',desc:'Junta Acristalamiento Int.',      qty:'2H+2L' },
    ];
    if (osci) {
        list.push({ ref:'04.OBS.005', desc:'Cremona Oscilobatiente', qty:'1' });
    }
    return list;
}

function accesorios_v2h_prac() {
    return [
        { ref:'04.APS.B11', desc:'Bisagra 80 Kg',                    qty:'4' },
        { ref:'04.APS.C01', desc:'Cremona Practicable',              qty:'1' },
        { ref:'04.APS.001', desc:'Kit de cierre Hoja Practicable',   qty:'1' },
        { ref:'04.APS.002', desc:'Kit de cierre Hoja Pasiva',        qty:'1' },
        { ref:'04.OBS.020', desc:'Kit Base Ancho Hoja 360/1.400',    qty:'1', note:'según ancho' },
        { ref:'04.OBS.044', desc:'Kit bisagras Hoja Pasiva 80 Kg',   qty:'1' },
        { ref:'04.TA.003',  desc:'Juego tapas inversor',             qty:'1' },
        { ref:'04.ES.003',  desc:'Escuadra ventana',                 qty:'12' },
        { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',      qty:'4' },
        { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',       qty:'8' },
        { ref:'04.TA.016',  desc:'Tapa desagüe',                     qty:'2' },
        { ref:'04.AC.T01',  desc:'Tubo desagüe hoja',                qty:'4' },
        { ref:'04.JU.001',  desc:'Junta Central',                    qty:'3H+2L' },
        { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',              qty:'4H+2L' },
        { ref:'04.JU.004.D',desc:'Junta Interior EPDM',              qty:'3H+2L' },
        { ref:'04.AC.008',  desc:'Tornillo clipaje vierteaguas',     qty:'4 ml' },
        { ref:'04.TA.020',  desc:'Juego tapas vierteaguas hoja',     qty:'2' },
        { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',       qty:'4H+4L' },
        { ref:'SEGÚN VIDRIO',desc:'Junta Acristalamiento Int.',      qty:'4H+4L' },
    ];
}

function accesorios_v3h_prac() {
    return [
        { ref:'04.APS.B11', desc:'Bisagra 80 Kg',                    qty:'6' },
        { ref:'04.APS.C01', desc:'Cremona Practicable',              qty:'1' },
        { ref:'04.APS.001', desc:'Kit de cierre Hoja Practicable',   qty:'1' },
        { ref:'04.APS.002', desc:'Kit de cierre Hoja Pasiva',        qty:'2' },
        { ref:'04.TA.003',  desc:'Juego tapas inversor',             qty:'2' },
        { ref:'04.ES.003',  desc:'Escuadra ventana',                 qty:'16' },
        { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',      qty:'4' },
        { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',       qty:'12' },
        { ref:'04.TA.016',  desc:'Tapa desagüe',                     qty:'2' },
        { ref:'04.AC.T01',  desc:'Tubo desagüe hoja',                qty:'6' },
        { ref:'04.JU.001',  desc:'Junta Central',                    qty:'4H+2L' },
        { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',              qty:'4H+2L' },
        { ref:'04.AC.008',  desc:'Tornillo clipaje vierteaguas',     qty:'4 ml' },
        { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',       qty:'6H+6L' },
        { ref:'SEGÚN VIDRIO',desc:'Junta Acristalamiento Int.',      qty:'6H+6L' },
    ];
}

function accesorios_abatible() {
    return [
        { ref:'04.APS.B11', desc:'Bisagra 80 Kg',                    qty:'2' },
        { ref:'04.AP.G01',  desc:'Cierre de golpete',                qty:'1' },
        { ref:'04.APL.C01', desc:'Cierre de golpete marco enrasado', qty:'1' },
        { ref:'04.AP.G10',  desc:'Mando a distancia',               qty:'1', note:'opcional' },
        { ref:'04.AP.G02',  desc:'Compás desmontable',              qty:'2' },
        { ref:'04.ES.003',  desc:'Escuadra ventana',                 qty:'8' },
        { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',      qty:'4' },
        { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',       qty:'4' },
        { ref:'04.TA.016',  desc:'Tapa desagüe',                     qty:'2' },
        { ref:'04.AC.T01',  desc:'Tubo desagüe hoja',                qty:'2' },
        { ref:'04.JU.001',  desc:'Junta Central',                    qty:'2H+2L' },
        { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',              qty:'2H+2L' },
        { ref:'04.JU.004.D',desc:'Junta Interior EPDM',              qty:'2H+2L' },
        { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',       qty:'2H+2L' },
        { ref:'SEGÚN VIDRIO',desc:'Junta Acristalamiento Int.',      qty:'2H+2L' },
    ];
}

function accesorios_fijo() {
    return [
        { ref:'04.ES.003',   desc:'Escuadra ventana',               qty:'8' },
        { ref:'04.TA.016',   desc:'Tapa desagüe',                   qty:'2' },
        { ref:'04.GR.001',   desc:'Grapa junquillo curvo',          qty:'según L' },
        { ref:'04.JA.001',   desc:'Junta Acristalamiento Ext.',     qty:'2H+2L' },
        { ref:'SEGÚN VIDRIO',desc:'Junta Acristalamiento Int.',     qty:'2H+2L' },
    ];
}

function accesorios_balconera(nHojas) {
    return [
        { ref:'04.APS.B11', desc:'Bisagra 80 Kg',                   qty: String(nHojas * 3) },
        { ref:'04.APS.C01', desc:'Cremona Practicable',             qty:'1' },
        { ref:'04.APS.001', desc:'Kit de cierre Hoja Practicable',  qty:'1' },
        { ref:'04.OBS.030', desc:'Kit bisagras ambidextras 110 Kg', qty:'1' },
        { ref:'04.APL.C05', desc:'Cremona Apertura exterior',       qty: nHojas > 1 ? '1' : '–' },
        { ref:'04.ES.003',  desc:'Escuadra ventana',                qty: String(nHojas * 4 + 4) },
        { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',     qty:'4' },
        { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',      qty: String(nHojas * 4) },
        { ref:'04.TA.016',  desc:'Tapa desagüe',                    qty:'2' },
        { ref:'04.AC.T01',  desc:'Tubo desagüe hoja',               qty: String(nHojas * 2) },
        { ref:'04.JU.001',  desc:'Junta Central',                   qty: nHojas === 1 ? '2H+2L' : '3H+2L' },
        { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',             qty: nHojas === 1 ? '2H+2L' : '4H+2L' },
        { ref:'04.AC.008',  desc:'Tornillo clipaje vierteaguas',    qty:'4 ml' },
        { ref:'04.TA.020',  desc:'Juego tapas vierteaguas hoja',    qty: String(nHojas) },
        { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',      qty: nHojas === 1 ? '2H+2L' : '4H+4L' },
        { ref:'SEGÚN VIDRIO',desc:'Junta Acristalamiento Int.',     qty: nHojas === 1 ? '2H+2L' : '4H+4L' },
    ];
}

function accesorios_balconera_ext() {
    return [
        { ref:'04.APS.B11', desc:'Bisagra 90 Kg',                   qty:'3' },
        { ref:'04.APL.C05', desc:'Cremona Apertura exterior',       qty:'1' },
        { ref:'04.APL.C07', desc:'Perno de conexión',               qty:'2' },
        { ref:'04.APL.C13', desc:'Alargador polo',                  qty:'2' },
        { ref:'04.APS.006', desc:'Kit de cierre Apertura Exterior', qty:'1' },
        { ref:'04.APS.003', desc:'Kit de cierre Hoja Pasiva con leva', qty:'1' },
        { ref:'04.TA.003',  desc:'Juego tapas inversor',            qty:'1' },
        { ref:'04.ES.003',  desc:'Escuadra Marco Ventana',          qty:'4' },
        { ref:'04.EA.005',  desc:'Escuadra alineamiento Hoja Balconera', qty:'8' },
        { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',     qty:'4' },
        { ref:'04.JU.001',  desc:'Junta Central',                   qty:'2H+2L' },
        { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',             qty:'2H+2L' },
        { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',      qty:'2H+2L' },
        { ref:'SEGÚN VIDRIO',desc:'Junta Acristalamiento Int.',     qty:'2H+2L' },
    ];
}

function accesorios_puerta() {
    return [
        { ref:'04.MH.008',  desc:'Juego manillas recuperables',     qty:'1' },
        { ref:'04.BP.017',  desc:'Bisagra puerta 100 Kg',           qty:'2' },
        { ref:'04.BP.001',  desc:'Bisagra puerta',                   qty:'2' },
        { ref:'04.TA.034',  desc:'Tope unión Marco Interior',       qty:'2' },
        { ref:'04.ES.006',  desc:'Escuadra puerta',                  qty:'4' },
        { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',      qty:'2' },
        { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',       qty:'4' },
        { ref:'04.TO.012',  desc:'Techo de unión pilastra',         qty:'2', note:'si lleva pilastra' },
        { ref:'04.JU.001',  desc:'Junta Central',                    qty:'2H+1L' },
        { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',             qty:'2H+1L' },
        { ref:'04.JU.004.D',desc:'Junta Interior EPDM',             qty:'2H+1L' },
        { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',      qty:'2H+4L' },
        { ref:'SEGÚN VIDRIO',desc:'Junta Acristalamiento Int.',     qty:'2H+4L' },
        { ref:'04.AC.F06',  desc:'Felpudo inferior',                qty:'L' },
        { ref:'04.TA.018',  desc:'Juego tapas vierteaguas',         qty:'1' },
    ];
}

// ══════════════════════════════════════════════════════════
//  RENDER
// ══════════════════════════════════════════════════════════

function mm(v) { return Math.round(v); }
function m3(v) { return (v / 1000).toFixed(3); }

function renderResults() {
    const systemKey = document.getElementById('systemType').value;
    const L    = parseFloat(document.getElementById('dimL').value) || 0;
    const H    = parseFloat(document.getElementById('dimH').value) || 0;
    const qty  = parseInt(document.getElementById('dimQty').value) || 1;
    const glassThick  = parseFloat(document.getElementById('glassThick').value) || 20;
    const junqType    = document.getElementById('junquilloType').value;
    const forroType   = document.getElementById('forroType').value;
    const premarcoType= document.getElementById('premarcoType').value;

    const sys = SYSTEMS[systemKey];
    if (!sys || L < 100 || H < 100) {
        document.getElementById('resultsBox').innerHTML =
            '<p class="field-hint" style="padding:2rem">Introduce dimensiones válidas (mín. 100 mm).</p>';
        return;
    }

    const opts = { glassThick, junquilloType: junqType };
    const result = sys.calc(L, H, qty, opts);

    // Añadir forro si se seleccionó
    if (forroType !== 'none') {
        const forroRefs = { '40': '6.755', '60': '6.756', '85': '6.757' };
        const ref = forroRefs[forroType];
        result.bars.push(
            { ref, desc: `Forro Registro ${forroType}mm (horizontal)`, cut: L + 10, qty: 2, formula: 'L + 10' },
            { ref, desc: `Forro Registro ${forroType}mm (vertical)`,   cut: H + 10, qty: 2, formula: 'H + 10' },
        );
    }

    // Añadir premarco si se seleccionó
    if (premarcoType !== 'none') {
        const premarcoRefs = { '36': '9.213', '122': '9.214', '136': '9.215' };
        const ref = premarcoRefs[premarcoType];
        result.bars.push(
            { ref, desc: `Premarco ${premarcoType}mm (horizontal)`, cut: L + 20, qty: 2, formula: 'L + 20' },
            { ref, desc: `Premarco ${premarcoType}mm (vertical)`,   cut: H + 20, qty: 2, formula: 'H + 20' },
        );
    }

    // Agrupar barras por ref para totales
    const barTotals = {};
    for (const b of result.bars) {
        if (!barTotals[b.ref]) barTotals[b.ref] = { ref: b.ref, desc: b.desc, totalMm: 0, totalQty: 0 };
        barTotals[b.ref].totalMm  += b.cut * b.qty;
        barTotals[b.ref].totalQty += b.qty;
    }
    const totalAlMm = Object.values(barTotals).reduce((s, r) => s + r.totalMm, 0);

    // ── HTML ──
    let html = `
    <div class="result-header">
        <div>
            <h2>${sys.name}</h2>
            <p class="field-hint">Serie 28 · EXTRUAL &nbsp;|&nbsp; ${sys.page}
                &nbsp;|&nbsp; Hueco: <strong>${mm(L)} × ${mm(H)} mm</strong>
                &nbsp;|&nbsp; <strong>${qty} ud${qty>1?'s':''}</strong>
            </p>
        </div>
    </div>

    <h3 class="section-title">Perfiles de aluminio – Barras de corte</h3>
    <div class="result-table-wrap"><table class="result-table">
        <thead>
            <tr>
                <th>Referencia</th>
                <th>Descripción</th>
                <th>Fórmula</th>
                <th>Corte (mm)</th>
                <th>Cant./ud</th>
                <th>Total ml</th>
            </tr>
        </thead>
        <tbody>`;

    for (const b of result.bars) {
        const totalMl = (b.cut * b.qty * qty / 1000).toFixed(3);
        const cutCls  = b.cut < 0 ? 'cell-error' : '';
        html += `
            <tr>
                <td class="ref-cell">${b.ref}</td>
                <td>${PROFILES[b.ref] ? `${PROFILES[b.ref]}` : b.desc}</td>
                <td class="formula-cell">${b.formula}</td>
                <td class="${cutCls}">${mm(b.cut)}</td>
                <td>${b.qty}</td>
                <td>${totalMl} ml</td>
            </tr>`;
    }

    html += `
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"><strong>Total aluminio (${qty} ud${qty>1?'s':''})</strong></td>
                <td><strong>${(totalAlMm * qty / 1000).toFixed(3)} ml</strong></td>
            </tr>
        </tfoot>
    </table></div>`;

    // ── Resumen por referencia ──
    html += `
    <h3 class="section-title">Resumen de compra por referencia</h3>
    <div class="result-table-wrap"><table class="result-table compact">
        <thead>
            <tr><th>Referencia</th><th>Descripción</th><th>Total ml (×${qty} ud)</th></tr>
        </thead>
        <tbody>`;
    for (const [, r] of Object.entries(barTotals)) {
        html += `<tr>
            <td class="ref-cell">${r.ref}</td>
            <td>${PROFILES[r.ref] || r.desc}</td>
            <td>${(r.totalMm * qty / 1000).toFixed(3)} ml</td>
        </tr>`;
    }
    html += `</tbody></table></div>`;

    // ── Vidrio ──
    html += `
    <h3 class="section-title">Vidrio</h3>
    <div class="result-table-wrap"><table class="result-table compact">
        <thead>
            <tr><th>Descripción</th><th>Ancho (mm)</th><th>Alto (mm)</th><th>Cant./ud</th><th>Total</th><th>m²</th></tr>
        </thead>
        <tbody>`;
    let totalM2 = 0;
    for (const g of result.glass) {
        const m2unit = (g.W / 1000) * (g.H / 1000);
        const m2total = m2unit * g.qty * qty;
        totalM2 += m2total;
        const cls = g.W <= 0 || g.H <= 0 ? 'cell-error' : '';
        html += `<tr>
            <td>${g.desc}</td>
            <td class="${cls}">${mm(g.W)}</td>
            <td class="${cls}">${mm(g.H)}</td>
            <td>${g.qty}</td>
            <td>${g.qty * qty}</td>
            <td>${m2total.toFixed(3)} m²</td>
        </tr>`;
    }
    html += `</tbody>
        <tfoot><tr><td colspan="5"><strong>Total m² vidrio</strong></td><td><strong>${totalM2.toFixed(3)} m²</strong></td></tr></tfoot>
    </table></div>`;

    // ── Accesorios ──
    html += `
    <h3 class="section-title">Accesorios</h3>
    <div class="result-table-wrap"><table class="result-table compact">
        <thead>
            <tr><th>Referencia</th><th>Descripción</th><th>Cant. por ud</th><th>Nota</th></tr>
        </thead>
        <tbody>`;
    for (const a of result.accessories) {
        html += `<tr>
            <td class="ref-cell">${a.ref}</td>
            <td>${a.desc}</td>
            <td>${a.qty}</td>
            <td class="note-cell">${a.note || ''}</td>
        </tr>`;
    }
    html += `</tbody></table></div>`;

    // ── Nota de cotas ──
    html += `
    <div class="formula-note">
        <strong>Cotas utilizadas (Serie 28 · EXTRUAL):</strong>
        L = ${mm(L)} mm · H = ${mm(H)} mm ·
        Cámara vidrio = ${glassThick} mm ·
        Cara marco = 21.8 mm · Descuento hoja = 43.6 mm
        <br><em>Las fórmulas son orientativas según cat. 28-B. Verificar siempre con muestra.</em>
    </div>`;

    document.getElementById('resultsBox').innerHTML = html;
}

// Sincronizar glasThick con glassType
document.getElementById('glassType').addEventListener('change', function() {
    const map = {
        '4_6_4': 16, '4_12_4': 20, '4_16_4': 24,
        '4_8_4_8_4': 36, 'laminar_6': 6, 'laminar_88': 8.8, 'simple_4': 4,
    };
    if (map[this.value]) document.getElementById('glassThick').value = map[this.value];
    renderResults();
});

// Escuchar cambios en todos los campos
document.querySelectorAll('input, select').forEach(el => {
    el.addEventListener('input', renderResults);
    el.addEventListener('change', renderResults);
});

// Render inicial
renderResults();
</script>
</body>
</html>
