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

<script src="assets/descompuesto.js"></script>
<script>
(function () {
    var GLASS_THICK_MAP = {
        '4_6_4': 16, '4_12_4': 20, '4_16_4': 24,
        '4_8_4_8_4': 36, 'laminar_6': 6, 'laminar_88': 8.8, 'simple_4': 4,
    };

    function renderResults() {
        renderS28Results(
            document.getElementById('resultsBox'),
            document.getElementById('systemType').value,
            parseFloat(document.getElementById('dimL').value) || 0,
            parseFloat(document.getElementById('dimH').value) || 0,
            parseInt(document.getElementById('dimQty').value, 10) || 1,
            {
                glassThick:    parseFloat(document.getElementById('glassThick').value) || 20,
                junquilloType: document.getElementById('junquilloType').value,
                forroType:     document.getElementById('forroType').value,
                premarcoType:  document.getElementById('premarcoType').value,
            }
        );
    }

    document.getElementById('glassType').addEventListener('change', function () {
        if (GLASS_THICK_MAP[this.value]) {
            document.getElementById('glassThick').value = GLASS_THICK_MAP[this.value];
        }
        renderResults();
    });

    document.querySelectorAll('input, select').forEach(function (el) {
        el.addEventListener('input', renderResults);
        el.addEventListener('change', renderResults);
    });

    renderResults();
}());
</script>
</body>
</html>
