<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/helpers.php';

$lang = get_current_lang();
$carpentryOptions = get_carpentry_options();
$glassOptions = get_glass_options();
$glassPriceCatalog = get_default_glass_price_catalog();
$systemOptions = [
    'corredera' => tr('sliding', $lang),
    'abatible' => tr('casement', $lang),
    'fijo' => tr('fixed', $lang),
    'oscilobatiente' => tr('tilt_turn', $lang),
];
$openingOptions = [
    'izquierda' => tr('left', $lang),
    'derecha' => tr('right', $lang),
    'central' => tr('center', $lang),
];
$colorPresets = [
    ['value' => '#f2efe8', 'label' => tr('white', $lang)],
    ['value' => '#1f2329', 'label' => tr('matte_black', $lang)],
    ['value' => '#565b61', 'label' => tr('anthracite', $lang)],
    ['value' => '#8b7458', 'label' => tr('bronze', $lang)],
    ['value' => '#7a5436', 'label' => tr('walnut', $lang)],
    ['value' => 'custom', 'label' => tr('custom', $lang)],
];

$configExists = file_exists(__DIR__ . '/config.php');
$defaultIva = 21;
if ($configExists) {
    $cfg = require __DIR__ . '/config.php';
    $defaultIva = (float)($cfg['iva_pct_default'] ?? 21);
    $glassPriceCatalog = is_array($cfg['glass_price_catalog'] ?? null)
        ? $cfg['glass_price_catalog']
        : $glassPriceCatalog;
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h(tr('app_title', $lang)) ?></title>
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
            <a href="<?= h(url_with_lang('index.php', [], $lang)) ?>" class="active"><?= h(tr('new', $lang)) ?></a>
            <a href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>"><?= h(tr('history', $lang)) ?></a>
        </nav>
        <label class="lang-switcher">
            <span><?= h(tr('language', $lang)) ?></span>
            <select onchange="window.location.href=this.value">
                <option value="<?= h(url_with_lang('index.php', [], 'es')) ?>" <?= $lang === 'es' ? 'selected' : '' ?>><?= h(tr('spanish', $lang)) ?></option>
                <option value="<?= h(url_with_lang('index.php', [], 'ca')) ?>" <?= $lang === 'ca' ? 'selected' : '' ?>><?= h(tr('catalan', $lang)) ?></option>
            </select>
        </label>
    </div>
</header>

<main class="layout">
    <section class="panel form-panel">
        <h2><?= h(tr('budget_data', $lang)) ?></h2>

        <?php if (!$configExists): ?>
            <div class="alert"><?= h(tr('missing_config', $lang)) ?></div>
        <?php endif; ?>

        <form id="quoteForm" method="post" action="save_quote.php">
            <input type="hidden" name="lang" value="<?= h($lang) ?>">
            <div class="grid two">
                <label><?= h(tr('client', $lang)) ?>
                    <input type="text" name="client_name" required>
                </label>
                <label><?= h(tr('email', $lang)) ?>
                    <input type="email" name="client_email">
                </label>
                <label><?= h(tr('phone', $lang)) ?>
                    <input type="text" name="client_phone">
                </label>
                <label><?= h(tr('quantity', $lang)) ?>
                    <input type="number" name="quantity" min="1" value="1" required>
                </label>
            </div>

            <h3><?= h(tr('quote_items', $lang)) ?></h3>
            <div class="section-toolbar quote-items-toolbar">
                <p class="field-hint"><?= h(tr('quote_items_hint', $lang)) ?></p>
                <button type="button" class="secondary-button" id="addItemButton"><?= h(tr('add_item', $lang)) ?></button>
            </div>
            <div class="quote-items-list" id="quoteItemsList"></div>

            <h3><?= h(tr('visual_selector', $lang)) ?></h3>
            <div class="grid two">
                <label><?= h(tr('system', $lang)) ?>
                    <select name="system_type" id="systemType">
                        <?php foreach ($systemOptions as $systemValue => $systemLabel): ?>
                            <option value="<?= h($systemValue) ?>"><?= h($systemLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= h(tr('opening', $lang)) ?>
                    <select name="opening_type" id="openingType">
                        <?php foreach ($openingOptions as $openingValue => $openingLabel): ?>
                            <option value="<?= h($openingValue) ?>"><?= h($openingLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= h(tr('carpentry', $lang)) ?>
                    <select name="carpentry_model" id="carpentryModel">
                        <?php foreach ($carpentryOptions as $carpentryValue => $carpentryLabel): ?>
                            <option value="<?= h($carpentryValue) ?>"><?= h($carpentryValue === 'otra' ? tr((string)$carpentryLabel, $lang) : $carpentryLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= h(tr('reference', $lang)) ?>
                    <input type="text" name="carpentry_reference" id="carpentryReference" value="" placeholder="Serie concreta, acabado o referencia">
                </label>
                <label><?= h(tr('trim', $lang)) ?>
                    <select name="trim_size" id="trimSize">
                        <option value="0" selected><?= h(tr('no_trim', $lang)) ?></option>
                        <option value="40">40 mm</option>
                        <option value="60">60 mm</option>
                        <option value="80">80 mm</option>
                    </select>
                </label>
                <label class="system-detail-card is-hidden" id="tiltTurnConfig"><?= h(tr('tilt_turn_leaf', $lang)) ?>
                    <select name="tilt_turn_leaf" id="tiltTurnLeaf">
                        <option value="izquierda"><?= h(tr('left', $lang)) ?></option>
                        <option value="derecha"><?= h(tr('right', $lang)) ?></option>
                    </select>
                    <span class="field-hint"><?= h(tr('tilt_turn_leaf_hint', $lang)) ?></span>
                </label>
                <label><?= h(tr('frame_cut', $lang)) ?>
                    <select name="frame_cut_type" id="frameCutType">
                        <option value="recto" selected><?= h(tr('straight_cut', $lang)) ?></option>
                        <option value="mitered"><?= h(tr('mitered_cut', $lang)) ?></option>
                    </select>
                    <span class="field-hint"><?= h(tr('fixed_always_mitered', $lang)) ?></span>
                </label>
                <label><?= h(tr('profile_finish', $lang)) ?>
                    <select id="profileColorPreset">
                        <?php foreach ($colorPresets as $colorPreset): ?>
                            <option value="<?= h($colorPreset['value']) ?>" data-label="<?= h($colorPreset['label']) ?>"><?= h($colorPreset['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= h(tr('profile_color', $lang)) ?>
                    <div class="color-input-row">
                        <input type="color" name="profile_color_hex" id="profileColorHex" value="#f2efe8" aria-label="Seleccionar color del perfil">
                        <input type="text" name="profile_color" id="profileColorName" value="<?= h(tr('white', $lang)) ?>" placeholder="<?= h(tr('profile_color_placeholder', $lang)) ?>">
                    </div>
                </label>
                <label><?= h(tr('width_mm', $lang)) ?>
                    <input type="number" name="width_mm" id="widthMm" min="300" value="1500" required>
                </label>
                <label><?= h(tr('height_mm', $lang)) ?>
                    <input type="number" name="height_mm" id="heightMm" min="300" value="1200" required>
                </label>
                <label><?= h(tr('leaves', $lang)) ?>
                    <input type="number" name="leaves" id="leaves" min="1" max="6" value="2" required>
                </label>
            </div>

            <!-- ── Descompuesto S28: visible solo cuando carpintería = Serie S28 EXTRUAL ── -->
            <div id="s28Panel" hidden>
                <h3>Descompuesto Serie S28 · EXTRUAL</h3>
                <p class="field-hint">Perfiles, vidrios y accesorios según catálogo 28-B. Se actualiza en tiempo real con las medidas del formulario.</p>
                <div class="grid two">
                    <label>Sistema S28
                        <select id="s28SystemType">
                            <optgroup label="Ventanas">
                                <option value="v1h_prac">Ventana 1 Hoja Practicable</option>
                                <option value="v1h_osci">Ventana 1 Hoja Oscilobatiente</option>
                                <option value="v1h_fijo">Ventana 1 Hoja + Fijo</option>
                                <option value="v2h_prac" selected>Ventana 2 Hojas Practicable</option>
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
                    <label>Tipo de cámara S28
                        <select id="s28GlassType">
                            <option value="4_6_4">4+6+4 (C-16mm)</option>
                            <option value="4_12_4" selected>4+12+4 (C-20mm)</option>
                            <option value="4_16_4">4+16+4 (C-24mm)</option>
                            <option value="4_8_4_8_4">4+8+4+8+4 doble cámara</option>
                            <option value="laminar_6">Laminado 6mm</option>
                            <option value="laminar_88">Laminado 8.8mm</option>
                            <option value="simple_4">Simple 4mm</option>
                        </select>
                    </label>
                    <label>Espesor vidrio (mm)
                        <input type="number" id="s28GlassThick" min="4" max="50" value="20" step="1">
                    </label>
                    <label>Junquillo
                        <select id="s28JunquilloType">
                            <option value="curvo_grapa">Curvo grapa (5.070/5.071)</option>
                            <option value="curvo_clip" selected>Curvo clip (6.179/6.180)</option>
                            <option value="recto">Recto C-14.5 (3.360)</option>
                            <option value="redondo_grapa">Redondo grapa (5.612/5.613)</option>
                            <option value="redondo_clip">Redondo clip (6.181/6.182)</option>
                        </select>
                    </label>
                    <label>Forro/Tapeta
                        <select id="s28ForroType">
                            <option value="none">Sin forro</option>
                            <option value="40">Forro Registro 40mm (6.755)</option>
                            <option value="60">Forro Registro 60mm (6.756)</option>
                            <option value="85">Forro Registro 85mm (6.757)</option>
                        </select>
                    </label>
                    <label>Premarco de obra
                        <select id="s28PremarcoType">
                            <option value="none">Sin premarco</option>
                            <option value="36">Premarco Obra 36mm (9.213)</option>
                            <option value="122">Premarco Obra 122mm (9.214)</option>
                            <option value="136">Premarco Obra 136mm (9.215)</option>
                        </select>
                    </label>
                </div>
                <details class="s28-results-details" open>
                    <summary>Ver descompuesto de barras y accesorios</summary>
                    <div id="s28ResultsBox"></div>
                </details>
            </div>

            <h3><?= h(tr('glass', $lang)) ?></h3>
            <div class="section-toolbar">
                <p class="field-hint"><?= h(tr('glass_hint', $lang)) ?></p>
                <button type="button" class="secondary-button" id="suggestGlassButton"><?= h(tr('suggest_measures', $lang)) ?></button>
            </div>
            <div class="grid two">
                <label><?= h(tr('glass_selector', $lang)) ?>
                    <select name="glass_type" id="glassType">
                        <?php foreach ($glassOptions as $glassGroup => $groupOptions): ?>
                            <optgroup label="<?= h(tr(get_glass_group_translation_key($glassGroup), $lang)) ?>">
                                <?php foreach ($groupOptions as $glassValue => $glassLabel): ?>
                                    <option value="<?= h($glassValue) ?>" <?= $glassValue === 'camara_4_4_12_4' ? 'selected' : '' ?>><?= h($glassLabel) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= h(tr('glass_reference', $lang)) ?>
                    <input type="text" name="glass_description" id="glassDescription" value="Camara 4+4/12/4" placeholder="Ej. 4+4/12/4, laminar 3+3...">
                    <span class="field-hint"><?= h(tr('glass_reference_hint', $lang)) ?></span>
                </label>
                <label><?= h(tr('glass_width', $lang)) ?>
                    <input type="number" name="glass_width_mm" id="glassWidthMm" min="1" value="700" required>
                </label>
                <label><?= h(tr('glass_height', $lang)) ?>
                    <input type="number" name="glass_height_mm" id="glassHeightMm" min="1" value="1100" required>
                </label>
                <label><?= h(tr('pieces_per_unit', $lang)) ?>
                    <input type="number" name="glass_panels" id="glassPanels" min="1" value="2" required>
                </label>
                <label class="fabricated-cost-field"><?= h(tr('glass_price_m2', $lang)) ?>
                    <input type="number" name="glass_price_m2" id="priceGlass" min="0" step="0.01" value="42.00" required>
                    <span class="field-hint"><?= h(tr('glass_price_hint', $lang)) ?></span>
                </label>
            </div>
            <div class="glass-summary-box" id="glassSummaryBox"></div>

            <h3><?= h(tr('costs_margins', $lang)) ?></h3>
            <div class="pricing-mode-box">
                <label><?= h(tr('pricing_mode', $lang)) ?>
                    <select name="pricing_mode" id="pricingMode">
                        <option value="fabricada"><?= h(tr('own_fabrication', $lang)) ?></option>
                        <option value="comprada" selected><?= h(tr('purchased_carpentry', $lang)) ?></option>
                    </select>
                </label>
                <label class="checkbox-row">
                    <input type="checkbox" name="is_factory_finished" id="isFactoryFinished" value="1" checked>
                    <span><?= h(tr('supplier_finished', $lang)) ?></span>
                </label>
            </div>
            <div class="grid two">
                <label class="purchased-cost-field"><?= h(tr('purchase_cost_me', $lang)) ?>
                    <input type="number" name="purchased_unit_cost" id="purchasedUnitCost" min="0" step="0.01" value="250.00">
                </label>
                <label class="fabricated-cost-field"><?= h(tr('aluminum_price', $lang)) ?>
                    <input type="number" name="aluminum_price_ml" id="priceAl" min="0" step="0.01" value="18.00" required>
                </label>
                <label class="fabricated-cost-field"><?= h(tr('labor', $lang)) ?>
                    <input type="number" name="labor_cost" id="labor" min="0" step="0.01" value="65.00" required>
                </label>
                <label><?= h(tr('internal_extra_cost', $lang)) ?>
                    <input type="number" name="internal_extra_cost" id="internalExtraCost" min="0" step="0.01" value="0.00">
                    <span class="field-hint"><?= h(tr('internal_extra_hint', $lang)) ?></span>
                </label>
                <label class="fabricated-cost-field"><?= h(tr('margin', $lang)) ?>
                    <input type="number" name="margin_pct" id="margin" min="0" step="0.01" value="25.00" required>
                </label>
                <label class="commercial-margin-field"><?= h(tr('commercial_margin', $lang)) ?>
                    <input type="number" name="commercial_margin_pct" id="commercialMargin" min="0" step="0.01" value="25.00">
                </label>
                <label><?= h(tr('iva', $lang)) ?>
                    <input type="number" name="iva_pct" id="iva" min="0" step="0.01" value="<?= h((string)$defaultIva) ?>" required>
                </label>
            </div>

            <label><?= h(tr('notes', $lang)) ?>
                <textarea name="notes" rows="3" placeholder="<?= h(tr('notes_placeholder', $lang)) ?>"></textarea>
            </label>

            <input type="hidden" name="drawing_svg" id="drawingSvg">
            <input type="hidden" name="config_json" id="configJson">
            <input type="hidden" name="quote_items_json" id="quoteItemsJson">

            <div class="actions">
                <button type="submit" <?= !$configExists ? 'disabled' : '' ?>><?= h(tr('save_budget', $lang)) ?></button>
            </div>
        </form>
    </section>

    <section class="panel preview-panel">
        <h2><?= h(tr('technical_preview', $lang)) ?></h2>
        <div class="alert frontend-alert" id="appRuntimeStatus" hidden></div>
        <noscript>
            <div class="alert frontend-alert"><?= h('La vista previa requiere JavaScript. Activa JavaScript en el navegador.') ?></div>
        </noscript>
        <div class="profile-preview-chip" id="profilePreviewChip">
            <span class="swatch" id="profilePreviewSwatch"></span>
            <strong id="profilePreviewLabel"><?= h(tr('white', $lang)) ?></strong>
        </div>
        <div id="drawingWrap" class="drawing-wrap"></div>

        <h3><?= h(tr('financial_summary', $lang)) ?></h3>
        <div class="totals" id="totalsBox"></div>
    </section>
</main>

<script>
window.APP_LANG = <?= json_encode($lang) ?>;
window.GLASS_PRICE_CATALOG = <?= json_encode($glassPriceCatalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.APP_PREVIEW_READY = false;
window.reportAppRuntimeIssue = function (message) {
    var runtimeBox = document.getElementById('appRuntimeStatus');
    if (!runtimeBox) {
        return;
    }

    runtimeBox.hidden = false;
    runtimeBox.textContent = message;
};

window.addEventListener('error', function (event) {
    var source = event && typeof event.filename === 'string' ? event.filename : '';
    if (source.indexOf('assets/app.js') !== -1) {
        window.reportAppRuntimeIssue('Error cargando la vista previa: ' + (event.message || 'fallo en app.js'));
    }
});

window.addEventListener('DOMContentLoaded', function () {
    window.setTimeout(function () {
        if (!window.APP_PREVIEW_READY) {
            window.reportAppRuntimeIssue('La vista previa no ha llegado a iniciarse. Revisa si el navegador esta bloqueando JavaScript o si assets/app.js no se ha cargado.');
        }
    }, 1200);
});
window.APP_UI_TEXT = <?= json_encode([
    'glassSummary' => tr('glass_summary', $lang),
    'undefined' => tr('undefined', $lang),
    'noReference' => tr('no_reference', $lang),
    'sqmPerPiece' => tr('sqm_per_piece', $lang),
    'sqmTotal' => tr('sqm_total', $lang),
    'glassCost' => tr('glass_cost', $lang),
    'piecesPerUnitShort' => tr('pieces_per_unit_short', $lang),
    'piecesPerUnitText' => tr('pieces_per_unit_text', $lang),
    'sheetSeries' => tr('sheet_series', $lang),
    'sheetColor' => tr('sheet_color', $lang),
    'sheetTrim' => tr('sheet_trim', $lang),
    'sheetCut' => tr('sheet_cut', $lang),
    'sheetGlass' => tr('sheet_glass', $lang),
    'sheetSize' => tr('sheet_size', $lang),
    'trimNone' => tr('trim_none', $lang),
    'noTrimLabel' => tr('no_trim', $lang),
    'cutRecto' => tr('cut_recto', $lang),
    'cutMitered' => tr('cut_mitered', $lang),
    'unitsShort' => tr('units_short', $lang),
    'leavesShort' => tr('leaves_short', $lang),
    'tiltTurnLeaf' => tr('tilt_turn_leaf', $lang),
    'tiltTurnLeafShort' => tr('tilt_turn_leaf_short', $lang),
    'customColorLabel' => tr('custom_color_label', $lang),
    'base' => tr('base', $lang),
    'subtotal' => tr('subtotal', $lang),
    'total' => tr('total', $lang),
    'margin' => tr('margin', $lang),
    'commercialMargin' => tr('commercial_margin', $lang),
    'iva' => tr('iva', $lang),
    'purchaseCostMe' => tr('purchase_cost_me', $lang),
    'glass' => tr('glass', $lang),
    'aluminumPrice' => tr('aluminum_price', $lang),
    'glassPrice' => tr('glass_price_m2', $lang),
    'quoteItems' => tr('quote_items', $lang),
    'addItem' => tr('add_item', $lang),
    'removeItem' => tr('remove_item', $lang),
    'item' => tr('item', $lang),
    'itemCount' => tr('item_count', $lang),
    'totalUnits' => tr('total_units', $lang),
    'selectedItem' => tr('selected_item', $lang),
    'multipleSystem' => tr('multiple_system', $lang),
    'left' => tr('left', $lang),
    'right' => tr('right', $lang),
    'onlyLeaf' => tr('only_leaf', $lang),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="assets/descompuesto.js"></script>
<script src="assets/app.js"></script>
<script>
(function () {
    var GLASS_THICK_MAP = {
        '4_6_4': 16, '4_12_4': 20, '4_16_4': 24,
        '4_8_4_8_4': 36, 'laminar_6': 6, 'laminar_88': 8.8, 'simple_4': 4,
    };

    function renderS28Inline() {
        var panel = document.getElementById('s28Panel');
        if (!panel || panel.hidden) { return; }
        renderS28Results(
            document.getElementById('s28ResultsBox'),
            document.getElementById('s28SystemType').value,
            parseFloat(document.getElementById('widthMm').value) || 0,
            parseFloat(document.getElementById('heightMm').value) || 0,
            parseInt(document.querySelector('[name="quantity"]').value, 10) || 1,
            {
                glassThick:    parseFloat(document.getElementById('s28GlassThick').value) || 20,
                junquilloType: document.getElementById('s28JunquilloType').value,
                forroType:     document.getElementById('s28ForroType').value,
                premarcoType:  document.getElementById('s28PremarcoType').value,
            }
        );
    }

    function updateS28Visibility() {
        var panel = document.getElementById('s28Panel');
        var isS28 = document.getElementById('carpentryModel').value === 'serie_s28';
        panel.hidden = !isS28;
        if (isS28) { renderS28Inline(); }
    }

    document.getElementById('s28GlassType').addEventListener('change', function () {
        if (GLASS_THICK_MAP[this.value]) {
            document.getElementById('s28GlassThick').value = GLASS_THICK_MAP[this.value];
        }
        renderS28Inline();
    });

    ['s28SystemType', 's28GlassThick', 's28JunquilloType', 's28ForroType', 's28PremarcoType'].forEach(function (id) {
        var el = document.getElementById(id);
        el.addEventListener('input', renderS28Inline);
        el.addEventListener('change', renderS28Inline);
    });

    ['widthMm', 'heightMm'].forEach(function (id) {
        var el = document.getElementById(id);
        el.addEventListener('input', renderS28Inline);
        el.addEventListener('change', renderS28Inline);
    });

    var qtyEl = document.querySelector('[name="quantity"]');
    qtyEl.addEventListener('input', renderS28Inline);
    qtyEl.addEventListener('change', renderS28Inline);

    document.getElementById('carpentryModel').addEventListener('change', updateS28Visibility);
    updateS28Visibility();
}());
</script>
</body>
</html>
