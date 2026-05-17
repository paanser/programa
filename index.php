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
$ralColors = [
    ['code' => '9010', 'name' => 'RAL 9010 — Blanco puro',      'hex' => '#f4f4f0'],
    ['code' => '9016', 'name' => 'RAL 9016 — Blanco tráfico',   'hex' => '#f6f6f6'],
    ['code' => '9003', 'name' => 'RAL 9003 — Blanco señal',     'hex' => '#f0f0ec'],
    ['code' => '7035', 'name' => 'RAL 7035 — Gris claro',       'hex' => '#d7d7d0'],
    ['code' => '7016', 'name' => 'RAL 7016 — Gris antracita',   'hex' => '#3e4349'],
    ['code' => '7021', 'name' => 'RAL 7021 — Gris negruzco',    'hex' => '#2e3234'],
    ['code' => '9005', 'name' => 'RAL 9005 — Negro intenso',    'hex' => '#0a0a0a'],
    ['code' => '8017', 'name' => 'RAL 8017 — Marrón chocolate', 'hex' => '#3b1f1c'],
    ['code' => '6005', 'name' => 'RAL 6005 — Verde musgo',      'hex' => '#1f3a2a'],
    ['code' => '5010', 'name' => 'RAL 5010 — Azul genciana',    'hex' => '#1a3a5c'],
    ['code' => '9006', 'name' => 'RAL 9006 — Aluminio blanco',  'hex' => '#a8a8a8'],
    ['code' => 'custom', 'name' => 'Personalizado',              'hex' => ''],
];
$colorPresets = array_map(fn($r) => ['value' => $r['hex'] ?: 'custom', 'label' => $r['name']], $ralColors);

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
</head>
<body>
<div class="background-shape shape-a"></div>
<div class="background-shape shape-b"></div>

<header class="topbar">
    <h1><?= h(tr('app_title', $lang)) ?></h1>
    <div class="topbar-tools">
        <nav>
            <a href="<?= h(url_with_lang('index.php', [], $lang)) ?>" class="active">Nuevo presupuesto</a>
            <a href="<?= h(url_with_lang('designer.php', [], $lang)) ?>">Diseñador</a>
            <a href="<?= h(url_with_lang('escaparate.php', [], $lang)) ?>">Escaparate</a>
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
            <input type="hidden" name="_csrf_token" value="<?= h(csrf_token()) ?>">
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

            <h3>Configuración de carpintería</h3>
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
                <label>Serie carpintería
                    <select name="carpentry_series" id="carpentrySeriesSelect">
                        <option value="">Sin serie específica</option>
                        <option value="s28_extrual">S28 · EXTRUAL</option>
                        <option value="s26_extrual">S26 · EXTRUAL</option>
                        <option value="cor70_cortizo">COR 70 · CORTIZO</option>
                        <option value="cor60_cortizo">COR 60 · CORTIZO</option>
                        <option value="cor60s_cortizo">COR 60 S · CORTIZO</option>
                        <option value="otra_serie">Otra serie</option>
                    </select>
                </label>
                <label><?= h(tr('reference', $lang)) ?>
                    <input type="text" name="carpentry_reference" id="carpentryReference" value="" placeholder="Ref. o acabado concreto">
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
                        <option value="unica"><?= h(tr('only_leaf', $lang)) ?></option>
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
                        <?php foreach ($ralColors as $rc): ?>
                            <option value="<?= h($rc['hex'] ?: 'custom') ?>"
                                    data-label="<?= h($rc['name']) ?>"
                                    data-ral="<?= h($rc['code']) ?>"><?= h($rc['name']) ?></option>
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

            <input type="hidden" name="carpentry_ral" id="carpentryRal" value="9010">
            <input type="hidden" name="designer_svg" id="designerSvg">
            <input type="hidden" name="designer_tree_json" id="designerTreeJson">

            <div class="actions">
                <button type="submit" <?= !$configExists ? 'disabled' : '' ?>><?= h(tr('save_budget', $lang)) ?></button>
            </div>
        </form>

    <!-- ── DISEÑADOR DE PANELES (siempre visible) ── -->
    <div class="designer-section">
        <!-- Pestañas: Paneles clásico / Escaparate -->
        <div class="dw-tabs">
            <button type="button" class="dw-tab dw-tab--active" id="tabPaneles" data-tab="paneles">Composición de paneles</button>
            <button type="button" class="dw-tab" id="tabEscaparate" data-tab="escaparate">Escaparate comercial</button>
        </div>
        <style>
            .dw-tabs { display:flex; gap:.25rem; padding:.7rem 1rem .3rem; border-bottom:2px solid #e8edf2; background:#f8fafc; }
            .dw-tab { background:none; border:1.5px solid transparent; border-radius:6px 6px 0 0; padding:.35rem .9rem; font-size:.82rem; font-weight:600; cursor:pointer; color:#5a6a78; transition:background .13s,color .13s; }
            .dw-tab:hover { background:#eef2f7; color:#1a3a58; }
            .dw-tab--active { background:#fff; border-color:#dde2e8; border-bottom-color:#fff; color:#1a3a58; margin-bottom:-2px; }
            .dw-tab-panel { display:none; }
            .dw-tab-panel--active { display:block; }

            /* Escaparate inline */
            .esc-inline { padding:1rem 1rem .5rem; }
            .esc-inline__top { display:grid; grid-template-columns:1fr 1fr; gap:.7rem 1rem; margin-bottom:.8rem; align-items:end; }
            .esc-inline__dim { display:grid; grid-template-columns:1fr 1fr 1fr; gap:.4rem; }
            .esc-inline__dim label, .esc-inline__presets-label { font-size:.78rem; color:#3a4a58; display:flex; flex-direction:column; gap:.15rem; }
            .esc-inline__dim input { padding:.28rem .4rem; border:1px solid #dde2e8; border-radius:5px; font-size:.82rem; background:#fafbfc; width:100%; }
            .esc-inline__presets { display:flex; flex-wrap:wrap; gap:.3rem; }
            .esc-preset-pill { padding:.25rem .6rem; background:#eef2f7; border:1px solid #dde2e8; border-radius:20px; font-size:.73rem; cursor:pointer; color:#2a3a50; transition:background .12s; white-space:nowrap; }
            .esc-preset-pill:hover { background:#d8eaf8; border-color:#3a80c0; }
            .esc-inline__body { display:grid; grid-template-columns:260px 1fr; gap:1rem; }
            @media (max-width:800px) { .esc-inline__body { grid-template-columns:1fr; } .esc-inline__top { grid-template-columns:1fr; } }
            .esc-inline__modules { display:flex; flex-direction:column; gap:.4rem; }
            .esc-module-row { background:#f8fafc; border:1px solid #e2e8ee; border-radius:7px; padding:.5rem .6rem; border-left-width:4px; }
            .esc-module-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:.4rem; }
            .esc-module-badge { font-size:.7rem; font-weight:600; padding:.12rem .4rem; border-radius:4px; border:1px solid; }
            .esc-module-controls { display:flex; gap:.2rem; }
            .esc-btn-icon { background:none; border:1px solid #dde2e8; border-radius:4px; width:22px; height:22px; cursor:pointer; font-size:.75rem; color:#4a5a68; display:flex; align-items:center; justify-content:center; padding:0; }
            .esc-btn-icon:hover:not(:disabled) { background:#e8f0f8; }
            .esc-btn-icon:disabled { opacity:.35; cursor:default; }
            .esc-btn-del:hover:not(:disabled) { background:#fdecea; border-color:#e07060; color:#c0392b; }
            .esc-module-fields { display:grid; grid-template-columns:1fr 1fr; gap:.3rem; }
            .esc-module-fields .esc-field:first-child { grid-column:1/-1; }
            .esc-field { font-size:.76rem; color:#3a4a58; display:flex; flex-direction:column; gap:.12rem; }
            .esc-field input, .esc-field select { padding:.25rem .38rem; border:1px solid #dde2e8; border-radius:5px; font-size:.78rem; background:#fafbfc; }
            .esc-add-btn { width:100%; padding:.42rem; background:#eaf3fc; border:1.5px dashed #3a80c0; border-radius:6px; color:#1a60a0; font-size:.8rem; font-weight:600; cursor:pointer; margin-top:.3rem; }
            .esc-add-btn:hover { background:#d8eaf8; }
            .esc-canvas-wrap { background:#f0f2f5; border-radius:7px; overflow-x:auto; padding:.5rem; }
            .esc-canvas-wrap svg { max-width:100%; height:auto; display:block; }
            .esc-width-bar { background:#f0f4f8; border-radius:5px; padding:.35rem .6rem; margin-top:.5rem; font-size:.76rem; color:#3a4a58; display:flex; justify-content:space-between; }
            .esc-width-bar.warn { background:#fff3e0; color:#a05000; }
        </style>

        <!-- ─── PANEL: DISEÑADOR CLÁSICO ─── -->
        <div class="dw-tab-panel dw-tab-panel--active" id="tabPanelPaneles">
        <div class="designer-section__toolbar">
            <h3 style="display:none">Composición de paneles</h3>
            <div class="designer-section__actions">
                <button type="button" class="secondary-button" id="dwPresetEscaparate" style="font-size:0.82rem">🏬 Escaparate (puerta + fijo)</button>
                <button type="button" class="secondary-button" id="dwPresetEscaparateVitrina" style="font-size:0.82rem">🏪 Escaparate vitrina (puerta estrecha + gran fijo)</button>
                <button type="button" class="secondary-button" id="dwPresetFijoPF" style="font-size:0.82rem">Fijo + Puerta + Fijo</button>
                <button type="button" class="secondary-button" id="dwPresetPuertaTacha" style="font-size:0.82rem">🚪 Puerta + Tacha</button>
                <button type="button" class="secondary-button" id="dwPresetFijoPuertaTacha" style="font-size:0.82rem">Fijo + Puerta + Tacha</button>
                <button type="button" class="secondary-button" id="dwPresetFijoCorredera" style="font-size:0.82rem">Fijo + Corredera</button>
                <button type="button" class="secondary-button" id="dwPresetEscaparateCompleto" style="font-size:0.82rem">🏬 Escaparate completo</button>
                <button type="button" class="secondary-button" id="dwApplySvg" style="white-space:nowrap;padding:0.4rem 0.8rem;font-size:0.82rem">Usar este dibujo →</button>
                <span class="designer-embed__badge" id="dwPanelBadge"></span>
            </div>
        </div>
        <div class="designer-embed__hint">
            <span>Diseña la distribución de paneles. La serie de carpintería se toma del campo de arriba. Cada panel puede tener un tipo distinto (fijo, puerta, practicable...).</span>
        </div>
        <div class="designer-embed__body">
            <!-- CONTROLES -->
            <div class="designer-embed__controls">
                <div class="form-section">
                    <h3>Dimensiones</h3>
                    <div class="grid two">
                        <label style="font-size:0.82rem">Ancho (mm)<input type="number" id="dw-facadeW" value="1500" min="200" max="15000" step="1"></label>
                        <label style="font-size:0.82rem">Alto (mm)<input type="number" id="dw-facadeH" value="1200" min="200" max="6000" step="1"></label>
                    </div>
                </div>
                <div class="form-section">
                    <h3>Forma</h3>
                    <label style="font-size:0.82rem">Tipo de hueco
                        <select id="dw-shapeSelect">
                            <option value="rectangular">Rectangular</option>
                            <option value="trapezoidal">Trapezoidal</option>
                        </select>
                    </label>
                    <div id="dw-slopeRow" style="display:none;margin-top:0.5rem">
                        <label style="font-size:0.82rem">Inclinación
                            <div class="range-row">
                                <input type="range" id="dw-slopeRange" min="-30" max="30" value="0" step="1">
                                <span id="dw-slopeLabel">0°</span>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="form-section">
                    <h3>Dividir panel</h3>
                    <div class="btn-group">
                        <button type="button" id="dw-btnSplitV" class="secondary-button split-btn">
                            <svg width="16" height="16" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="1" width="16" height="16" rx="2"/><line x1="9" y1="1" x2="9" y2="17"/></svg>
                            Montante
                        </button>
                        <button type="button" id="dw-btnSplitH" class="secondary-button split-btn">
                            <svg width="16" height="16" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="1" width="16" height="16" rx="2"/><line x1="1" y1="9" x2="17" y2="9"/></svg>
                            Travesaño
                        </button>
                    </div>
                    <button type="button" id="dw-btnUnsplit" class="secondary-button" style="width:100%;margin-top:0.4rem;font-size:0.82rem" disabled>✕ Eliminar división</button>
                </div>
                <div class="form-section" id="dw-splitControls" style="display:none">
                    <h3>Posición del corte</h3>
                    <div class="range-row">
                        <input type="range" id="dw-splitRatio" min="10" max="90" value="50" step="1">
                        <span id="dw-splitInfo">50% · 50%</span>
                    </div>
                </div>
                <div class="form-section">
                    <h3>Panel seleccionado</h3>
                    <p class="field-hint" id="dw-panelInfo" style="margin-bottom:0.5rem">Haz clic en un panel</p>
                    <div id="dw-leafControls">
                        <label style="font-size:0.82rem">Sistema
                            <select id="dw-panelSystem">
                                <option value="fijo">Fijo</option>
                                <option value="practicable">Practicable</option>
                                <option value="oscilobatiente">Oscilobatiente</option>
                                <option value="abatible">Abatible</option>
                                <option value="corredera">Corredera</option>
                                <option value="puerta">Puerta</option>
                                <option value="tubo">Perfil / Tubo</option>
                            </select>
                        </label>
                        <label id="dw-openingRow" style="font-size:0.82rem;margin-top:0.4rem">Apertura
                            <select id="dw-panelOpening">
                                <option value="izq">Izquierda / Arriba</option>
                                <option value="der">Derecha / Abajo</option>
                            </select>
                        </label>
                        <label style="font-size:0.82rem;margin-top:0.4rem">Etiqueta
                            <input type="text" id="dw-panelLabel" placeholder="V1, P1…">
                        </label>
                        <div id="dw-heightControls" style="margin-top:0.5rem">
                            <label style="font-size:0.82rem">Empieza en (% desde arriba)
                                <div class="range-row">
                                    <input type="range" id="dw-panelTopPct" min="0" max="80" value="0" step="1">
                                    <span id="dw-panelTopPctLabel">0%</span>
                                </div>
                            </label>
                            <label style="font-size:0.82rem;margin-top:0.3rem">Altura del panel (%)
                                <div class="range-row">
                                    <input type="range" id="dw-panelHeightPct" min="20" max="100" value="100" step="1">
                                    <span id="dw-panelHeightPctLabel">100%</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-section">
                    <h3>Paneles</h3>
                    <div id="dw-panelList" class="panel-list"></div>
                </div>
                <div class="form-section">
                    <h3>Leyenda</h3>
                    <div class="sys-legend" id="dw-sysLegend"></div>
                </div>
            </div>
            <!-- CANVAS -->
            <div class="designer-embed__canvas" id="dw-canvasWrap"></div>
        </div>
        </div><!-- /dw-tab-panel tabPanelPaneles -->

        <!-- ─── PANEL: ESCAPARATE COMERCIAL ─── -->
        <div class="dw-tab-panel" id="tabPanelEscaparate">
            <div class="esc-inline">
                <!-- Dimensiones + Presets -->
                <div class="esc-inline__top">
                    <div>
                        <div class="esc-inline__dim">
                            <label>Ancho total (mm)
                                <input type="number" id="esc-facadeW" value="6500" min="200" max="30000" step="1">
                            </label>
                            <label>Alto total (mm)
                                <input type="number" id="esc-facadeH" value="2200" min="200" max="6000" step="1">
                            </label>
                            <label>Tacha inferior (mm)
                                <input type="number" id="esc-tachaH" value="0" min="0" max="1000" step="10" placeholder="0=sin tacha">
                            </label>
                        </div>
                        <div class="esc-width-bar" id="esc-widthBar">
                            <span>Suma módulos</span><strong id="esc-widthSum">—</strong>
                        </div>
                    </div>
                    <div>
                        <p style="font-size:.76rem;color:#5a6a78;margin:0 0 .4rem">Presets rápidos:</p>
                        <div class="esc-inline__presets">
                            <button type="button" class="esc-preset-pill" data-esc-preset="puerta">[ P ]</button>
                            <button type="button" class="esc-preset-pill" data-esc-preset="puerta_fijo">[ P ][ F ]</button>
                            <button type="button" class="esc-preset-pill" data-esc-preset="fijo_puerta">[ F ][ P ]</button>
                            <button type="button" class="esc-preset-pill" data-esc-preset="fijo_puerta_fijo">[ F ][ P ][ F ]</button>
                            <button type="button" class="esc-preset-pill" data-esc-preset="puerta_tacha">[ P ] + tacha</button>
                            <button type="button" class="esc-preset-pill" data-esc-preset="escaparate_vitrina">Escaparate vitrina</button>
                            <button type="button" class="esc-preset-pill" data-esc-preset="escaparate_completo">Escaparate completo</button>
                            <button type="button" class="esc-preset-pill" data-esc-preset="corredera_fijo">[ C ][ F ]</button>
                        </div>
                    </div>
                </div>

                <!-- Lista módulos + Canvas -->
                <div class="esc-inline__body">
                    <!-- Módulos -->
                    <div>
                        <p style="font-size:.76rem;font-weight:600;color:#2a3a50;margin:0 0 .4rem">Módulos <span id="esc-badge" style="font-weight:400;color:#7a8a98"></span></p>
                        <div class="esc-inline__modules" id="esc-moduleList"></div>
                        <button type="button" class="esc-add-btn" id="esc-btnAdd">+ Añadir módulo</button>
                    </div>
                    <!-- Vista SVG -->
                    <div class="esc-canvas-wrap" id="esc-canvasWrap"></div>
                </div>
            </div>
            <input type="hidden" id="esc-stateJson" name="escaparate_state_json">
        </div><!-- /dw-tab-panel tabPanelEscaparate -->

    </div><!-- /designer-section -->
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
<script src="assets/s28-engine.js"></script>
<script src="assets/designer-widget.js"></script>
<script src="assets/escaparate-widget.js"></script>
<script src="assets/app.js"></script>
<script>
// ── PESTAÑAS Paneles / Escaparate ──────────────────────────────────────────
(function () {
    var tabs    = document.querySelectorAll('.dw-tab');
    var panels  = document.querySelectorAll('.dw-tab-panel');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var target = tab.dataset.tab;
            tabs.forEach(function (t) { t.classList.remove('dw-tab--active'); });
            panels.forEach(function (p) { p.classList.remove('dw-tab-panel--active'); });
            tab.classList.add('dw-tab--active');
            var panel = document.getElementById('tabPanel' + target.charAt(0).toUpperCase() + target.slice(1));
            if (panel) panel.classList.add('dw-tab-panel--active');
        });
    });

    // ── INIT ESCAPARATE WIDGET ─────────────────────────────────────────────
    if (window.EscaparateWidget) {
        window.EscaparateWidget.init({
            canvasWrap:  'esc-canvasWrap',
            moduleList:  'esc-moduleList',
            btnAddModule:'esc-btnAdd',
            facadeW:     'esc-facadeW',
            facadeH:     'esc-facadeH',
            tachaH:      'esc-tachaH',
            hiddenJson:  'esc-stateJson',
            preset:      'escaparate_vitrina',
            onChanged: function (svg, state) {
                var badge = document.getElementById('esc-badge');
                if (badge) badge.textContent = state.modules.length + ' módulo' + (state.modules.length !== 1 ? 's' : '');
                var sumEl = document.getElementById('esc-widthSum');
                var barEl = document.getElementById('esc-widthBar');
                if (sumEl && state.modules) {
                    var sum = state.modules.reduce(function (s, m) { return s + (m.width || 0); }, 0);
                    if (barEl) {
                        var diff = Math.abs(sum - state.totalWidth);
                        barEl.className = 'esc-width-bar' + (diff > 10 ? ' warn' : '');
                        sumEl.textContent = diff > 10
                            ? sum + ' mm ≠ ' + state.totalWidth + ' mm (total)'
                            : sum + ' mm ✓';
                    }
                }
            },
        });

        document.querySelectorAll('[data-esc-preset]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                window.EscaparateWidget.applyPreset(btn.dataset.escPreset);
            });
        });
    }
})();
</script>
</body>
</html>