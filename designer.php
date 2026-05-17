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
            <a href="<?= h(url_with_lang('index.php', [], $lang)) ?>">Nuevo presupuesto</a>
            <a href="<?= h(url_with_lang('designer.php', [], $lang)) ?>" class="active">Diseñador</a>
            <a href="<?= h(url_with_lang('escaparate.php', [], $lang)) ?>">Escaparate</a>
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
                        <option value="tubo">Perfil / Tubo</option>
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
                <div id="heightControls" style="margin-top:0.5rem">
                    <label>Empieza en (% desde arriba)
                        <div class="range-row">
                            <input type="range" id="panelTopPct" min="0" max="80" value="0" step="1" style="width:100%">
                            <span id="panelTopPctLabel">0%</span>
                        </div>
                    </label>
                    <label style="margin-top:0.3rem">Altura del panel (%)
                        <div class="range-row">
                            <input type="range" id="panelHeightPct" min="20" max="100" value="100" step="1" style="width:100%">
                            <span id="panelHeightPctLabel">100%</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Forma del hueco</h3>
            <label>Tipo de hueco
                <select id="shapeSelect">
                    <option value="rectangular">Rectangular</option>
                    <option value="trapezoidal">Trapezoidal</option>
                </select>
            </label>
            <div id="slopeRow" style="display:none;margin-top:0.5rem">
                <label>Inclinación
                    <div class="range-row">
                        <input type="range" id="slopeRange" min="-30" max="30" value="0" step="1" style="width:100%">
                        <span id="slopeLabel">0°</span>
                    </div>
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

<script src="assets/designer-widget.js"></script>
<script>
document.getElementById('shapeSelect')?.addEventListener('change', function() {
    var slopeRow = document.getElementById('slopeRow');
    if (slopeRow) slopeRow.style.display = this.value === 'trapezoidal' ? '' : 'none';
});

window.DesignerWidget.init({
    canvasWrap:          'canvasWrap',
    btnSplitV:           'btnSplitV',
    btnSplitH:           'btnSplitH',
    btnUnsplit:          'btnUnsplit',
    panelSystem:         'panelSystem',
    panelOpening:        'panelOpening',
    panelLabel:          'panelLabel',
    splitRatio:          'splitRatio',
    splitInfo:           'splitInfo',
    splitControls:       'splitControls',
    panelInfo:           'panelInfo',
    leafControls:        'leafControls',
    openingRow:          'openingRow',
    heightControls:      'heightControls',
    panelHeightPct:      'panelHeightPct',
    panelHeightPctLabel: 'panelHeightPctLabel',
    panelTopPct:         'panelTopPct',
    panelTopPctLabel:    'panelTopPctLabel',
    panelList:           'panelList',
    sysLegend:           'sysLegend',
    facadeW:             'facadeW',
    facadeH:             'facadeH',
    shapeSelect:         'shapeSelect',
    slopeRange:          'slopeRange',
    slopeLabel:          'slopeLabel',
    facadeW_val:         2400,
    facadeH_val:         1200,
});
</script>
</body>
</html>
