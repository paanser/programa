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
            <a href="designer.php">Configurador</a>
            <a href="descompuesto.php" class="active">Descompuesto S28</a>
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

<script src="assets/extrual_s28.js"></script>
<script>
function mm(v) { return Math.round(v); }

function renderResults() {
    const systemKey    = document.getElementById('systemType').value;
    const L            = parseFloat(document.getElementById('dimL').value) || 0;
    const H            = parseFloat(document.getElementById('dimH').value) || 0;
    const qty          = parseInt(document.getElementById('dimQty').value) || 1;
    const glassThick   = parseFloat(document.getElementById('glassThick').value) || 20;
    const junqType     = document.getElementById('junquilloType').value;
    const forroType    = document.getElementById('forroType').value;
    const premarcoType = document.getElementById('premarcoType').value;

    const sys = SYSTEMS[systemKey];
    if (!sys || L < 100 || H < 100) {
        document.getElementById('resultsBox').innerHTML =
            '<p class="field-hint" style="padding:2rem">Introduce dimensiones válidas (mín. 100 mm).</p>';
        return;
    }

    const opts = { glassThick, junquilloType: junqType };
    const result = sys.calc(L, H, qty, opts);

    if (forroType !== 'none') {
        const forroRefs = { '40': '6.755', '60': '6.756', '85': '6.757' };
        const ref = forroRefs[forroType];
        result.bars.push(
            { ref, desc: `Forro Registro ${forroType}mm (horizontal)`, cut: L + 10, qty: 2, formula: 'L + 10' },
            { ref, desc: `Forro Registro ${forroType}mm (vertical)`,   cut: H + 10, qty: 2, formula: 'H + 10' },
        );
    }

    if (premarcoType !== 'none') {
        const premarcoRefs = { '36': '9.213', '122': '9.214', '136': '9.215' };
        const ref = premarcoRefs[premarcoType];
        result.bars.push(
            { ref, desc: `Premarco ${premarcoType}mm (horizontal)`, cut: L + 20, qty: 2, formula: 'L + 20' },
            { ref, desc: `Premarco ${premarcoType}mm (vertical)`,   cut: H + 20, qty: 2, formula: 'H + 20' },
        );
    }

    const barTotals = {};
    for (const b of result.bars) {
        if (!barTotals[b.ref]) barTotals[b.ref] = { ref: b.ref, desc: b.desc, totalMm: 0, totalQty: 0 };
        barTotals[b.ref].totalMm  += b.cut * b.qty;
        barTotals[b.ref].totalQty += b.qty;
    }
    const totalAlMm = Object.values(barTotals).reduce((s, r) => s + r.totalMm, 0);

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
    <table class="result-table">
        <thead>
            <tr>
                <th>Referencia</th><th>Descripción</th><th>Fórmula</th>
                <th>Corte (mm)</th><th>Cant./ud</th><th>Total ml</th>
            </tr>
        </thead>
        <tbody>`;

    for (const b of result.bars) {
        const totalMl = (b.cut * b.qty * qty / 1000).toFixed(3);
        const cutCls  = b.cut < 0 ? 'cell-error' : '';
        html += `<tr>
            <td class="ref-cell">${b.ref}</td>
            <td>${PROFILES[b.ref] || b.desc}</td>
            <td class="formula-cell">${b.formula}</td>
            <td class="${cutCls}">${mm(b.cut)}</td>
            <td>${b.qty}</td>
            <td>${totalMl} ml</td>
        </tr>`;
    }

    html += `</tbody>
        <tfoot>
            <tr>
                <td colspan="5"><strong>Total aluminio (${qty} ud${qty>1?'s':''})</strong></td>
                <td><strong>${(totalAlMm * qty / 1000).toFixed(3)} ml</strong></td>
            </tr>
        </tfoot>
    </table>

    <h3 class="section-title">Resumen de compra por referencia</h3>
    <table class="result-table compact">
        <thead><tr><th>Referencia</th><th>Descripción</th><th>Total ml (×${qty} ud)</th></tr></thead>
        <tbody>`;
    for (const [, r] of Object.entries(barTotals)) {
        html += `<tr>
            <td class="ref-cell">${r.ref}</td>
            <td>${PROFILES[r.ref] || r.desc}</td>
            <td>${(r.totalMm * qty / 1000).toFixed(3)} ml</td>
        </tr>`;
    }
    html += `</tbody></table>

    <h3 class="section-title">Vidrio</h3>
    <table class="result-table compact">
        <thead><tr><th>Descripción</th><th>Ancho (mm)</th><th>Alto (mm)</th><th>Cant./ud</th><th>Total</th><th>m²</th></tr></thead>
        <tbody>`;
    let totalM2 = 0;
    for (const g of result.glass) {
        const m2unit  = (g.W / 1000) * (g.H / 1000);
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
    </table>

    <h3 class="section-title">Accesorios</h3>
    <table class="result-table compact">
        <thead><tr><th>Referencia</th><th>Descripción</th><th>Cant. por ud</th><th>Nota</th></tr></thead>
        <tbody>`;
    for (const a of result.accessories) {
        html += `<tr>
            <td class="ref-cell">${a.ref}</td>
            <td>${a.desc}</td>
            <td>${a.qty}</td>
            <td class="note-cell">${a.note || ''}</td>
        </tr>`;
    }
    html += `</tbody></table>

    <div class="formula-note">
        <strong>Cotas utilizadas (Serie 28 · EXTRUAL):</strong>
        L = ${mm(L)} mm · H = ${mm(H)} mm ·
        Cámara vidrio = ${glassThick} mm ·
        Cara marco = 21.8 mm · Descuento hoja = 43.6 mm
        <br><em>Las fórmulas son orientativas según cat. 28-B. Verificar siempre con muestra.</em>
    </div>`;

    document.getElementById('resultsBox').innerHTML = html;
}

document.getElementById('glassType').addEventListener('change', function() {
    const map = {
        '4_6_4': 16, '4_12_4': 20, '4_16_4': 24,
        '4_8_4_8_4': 36, 'laminar_6': 6, 'laminar_88': 8.8, 'simple_4': 4,
    };
    if (map[this.value]) document.getElementById('glassThick').value = map[this.value];
    renderResults();
});

document.querySelectorAll('input, select').forEach(el => {
    el.addEventListener('input', renderResults);
    el.addEventListener('change', renderResults);
});

renderResults();
</script>
</body>
</html>
