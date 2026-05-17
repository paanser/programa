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
    <title>Escaparate Comercial – <?= h(tr('app_title', $lang)) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        /* ── LAYOUT ─────────────────────────────────────── */
        .esc-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 1.5rem;
            padding: 1.25rem 1.5rem;
            min-height: calc(100vh - 56px);
            align-items: start;
        }
        @media (max-width: 900px) {
            .esc-layout { grid-template-columns: 1fr; }
        }

        /* ── PANEL IZQUIERDO ─────────────────────────────── */
        .esc-panel {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            padding: 1.25rem;
            position: sticky;
            top: 1rem;
        }
        .esc-panel h2 {
            margin: 0 0 .15rem;
            font-size: 1.05rem;
            color: #1a2a38;
        }
        .esc-panel .esc-subtitle {
            color: #5a6a78;
            font-size: .82rem;
            margin: 0 0 1rem;
        }

        /* ── SECCIONES ───────────────────────────────────── */
        .esc-section {
            margin-bottom: 1.1rem;
            border-bottom: 1px solid #eef0f2;
            padding-bottom: 1rem;
        }
        .esc-section:last-child { border-bottom: none; }
        .esc-section h3 {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #7a8a98;
            margin: 0 0 .6rem;
        }

        /* ── CAMPOS DIMENSIONES ──────────────────────────── */
        .esc-dim-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
        }
        .esc-dim-grid label,
        .esc-field {
            display: flex;
            flex-direction: column;
            gap: .18rem;
            font-size: .8rem;
            color: #3a4a58;
        }
        .esc-dim-grid input,
        .esc-field input,
        .esc-field select {
            padding: .3rem .5rem;
            border: 1px solid #dde2e8;
            border-radius: 5px;
            font-size: .85rem;
            background: #fafbfc;
            transition: border-color .15s;
        }
        .esc-dim-grid input:focus,
        .esc-field input:focus,
        .esc-field select:focus {
            border-color: #3a80c0;
            outline: none;
            background: #fff;
        }

        /* ── PRESETS ─────────────────────────────────────── */
        .esc-preset-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .4rem;
        }
        .esc-preset-btn {
            background: #f4f6f8;
            border: 1px solid #dde2e8;
            border-radius: 6px;
            padding: .38rem .5rem;
            font-size: .75rem;
            cursor: pointer;
            text-align: left;
            color: #2a3840;
            transition: background .13s, border-color .13s;
            line-height: 1.3;
        }
        .esc-preset-btn:hover {
            background: #e8f0f8;
            border-color: #3a80c0;
        }
        .esc-preset-btn strong { display: block; font-size: .78rem; }

        /* ── MÓDULOS ─────────────────────────────────────── */
        .esc-module-list {
            display: flex;
            flex-direction: column;
            gap: .55rem;
        }
        .esc-module-row {
            background: #f8fafc;
            border: 1px solid #e2e8ee;
            border-radius: 8px;
            padding: .6rem .7rem;
            border-left-width: 4px;
        }
        .esc-module-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .5rem;
        }
        .esc-module-badge {
            font-size: .72rem;
            font-weight: 600;
            padding: .15rem .5rem;
            border-radius: 4px;
            border: 1px solid;
        }
        .esc-module-controls {
            display: flex;
            gap: .25rem;
        }
        .esc-btn-icon {
            background: none;
            border: 1px solid #dde2e8;
            border-radius: 4px;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: .8rem;
            color: #4a5a68;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: background .12s;
        }
        .esc-btn-icon:hover:not(:disabled) { background: #e8f0f8; }
        .esc-btn-icon:disabled { opacity: .35; cursor: default; }
        .esc-btn-del { color: #c0392b; }
        .esc-btn-del:hover:not(:disabled) { background: #fdecea; border-color: #e07060; }

        .esc-module-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .4rem;
        }
        .esc-module-fields .esc-field:first-child { grid-column: 1 / -1; }

        /* ── BOTÓN AÑADIR ────────────────────────────────── */
        .esc-add-btn {
            width: 100%;
            padding: .52rem;
            background: #eaf3fc;
            border: 1.5px dashed #3a80c0;
            border-radius: 7px;
            color: #1a60a0;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .13s;
            margin-top: .4rem;
        }
        .esc-add-btn:hover { background: #d8eaf8; }

        /* ── CANVAS ──────────────────────────────────────── */
        .esc-canvas-area {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            padding: 1.25rem;
        }
        .esc-canvas-area h2 {
            margin: 0 0 .15rem;
            font-size: 1.05rem;
            color: #1a2a38;
        }
        .esc-canvas-hint {
            font-size: .78rem;
            color: #7a8a98;
            margin-bottom: 1rem;
        }
        .esc-canvas-wrap {
            background: #f0f2f5;
            border-radius: 8px;
            padding: .75rem;
            overflow-x: auto;
        }
        .esc-canvas-wrap svg {
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* ── INFORMACIÓN MÓDULOS ─────────────────────────── */
        .esc-module-summary {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin-top: .8rem;
        }
        .esc-module-chip {
            font-size: .72rem;
            padding: .15rem .45rem;
            border-radius: 4px;
            background: #eef2f6;
            color: #2a3a48;
            border: 1px solid #dde2e8;
        }

        /* ── RESUMEN ANCHO ───────────────────────────────── */
        .esc-width-bar {
            background: #f0f4f8;
            border-radius: 6px;
            padding: .5rem .7rem;
            margin-top: .6rem;
            font-size: .78rem;
            color: #3a4a58;
            display: flex;
            justify-content: space-between;
        }
        .esc-width-bar.warn { background: #fff3e0; color: #a05000; }
    </style>
</head>
<body>
<div class="background-shape shape-a"></div>
<div class="background-shape shape-b"></div>

<header class="topbar">
    <h1><?= h(tr('app_title', $lang)) ?></h1>
    <div class="topbar-tools">
        <nav>
            <a href="<?= h(url_with_lang('index.php', [], $lang)) ?>">Presupuesto</a>
            <a href="<?= h(url_with_lang('designer.php', [], $lang)) ?>">Diseñador</a>
            <a href="<?= h(url_with_lang('escaparate.php', [], $lang)) ?>" class="active">Escaparate</a>
            <a href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>"><?= h(tr('history', $lang)) ?></a>
        </nav>
    </div>
</header>

<main class="esc-layout">

    <!-- ── PANEL IZQUIERDO ── -->
    <aside class="esc-panel">
        <h2>Escaparate comercial</h2>
        <p class="esc-subtitle">Paneles en banda · Módulos libres</p>

        <!-- DIMENSIONES -->
        <div class="esc-section">
            <h3>Dimensiones del hueco</h3>
            <div class="esc-dim-grid">
                <label>Ancho total (mm)
                    <input type="number" id="escFacadeW" value="6500" min="200" max="30000" step="1">
                </label>
                <label>Alto total (mm)
                    <input type="number" id="escFacadeH" value="2200" min="200" max="6000" step="1">
                </label>
                <label>Tacha inferior (mm)
                    <input type="number" id="escTachaH" value="0" min="0" max="1000" step="10" placeholder="0 = sin tacha">
                </label>
            </div>
            <div class="esc-width-bar" id="escWidthBar">
                <span>Suma módulos</span><strong id="escWidthSum">—</strong>
            </div>
        </div>

        <!-- PRESETS -->
        <div class="esc-section">
            <h3>Presets rápidos</h3>
            <div class="esc-preset-grid">
                <button type="button" class="esc-preset-btn" data-preset="puerta">
                    <strong>[ P ]</strong>Puerta sola
                </button>
                <button type="button" class="esc-preset-btn" data-preset="puerta_fijo">
                    <strong>[ P ][ F ]</strong>Puerta + Fijo
                </button>
                <button type="button" class="esc-preset-btn" data-preset="fijo_puerta">
                    <strong>[ F ][ P ]</strong>Fijo + Puerta
                </button>
                <button type="button" class="esc-preset-btn" data-preset="fijo_puerta_fijo">
                    <strong>[ F ][ P ][ F ]</strong>Fijo + Puerta + Fijo
                </button>
                <button type="button" class="esc-preset-btn" data-preset="puerta_tacha">
                    <strong>[ P ] + tacha</strong>Puerta con tacha
                </button>
                <button type="button" class="esc-preset-btn" data-preset="escaparate_vitrina">
                    <strong>[ P ][ F ] + tacha</strong>Escaparate típico
                </button>
                <button type="button" class="esc-preset-btn" data-preset="escaparate_completo">
                    <strong>[ F ][ P ][ F ] + tacha</strong>Escaparate completo
                </button>
                <button type="button" class="esc-preset-btn" data-preset="corredera_fijo">
                    <strong>[ C ][ F ]</strong>Corredera + Fijo
                </button>
            </div>
        </div>

        <!-- MÓDULOS -->
        <div class="esc-section">
            <h3>Módulos <span id="escModuleBadge" style="font-size:.72rem;color:#7a8a98;font-weight:400;letter-spacing:0"></span></h3>
            <div class="esc-module-list" id="escModuleList"></div>
            <button type="button" class="esc-add-btn" id="escBtnAdd">+ Añadir módulo</button>
        </div>

        <!-- ACCIONES -->
        <div class="esc-section">
            <h3>Acciones</h3>
            <button type="button" class="print-btn" onclick="window.print()" style="width:100%">Imprimir / PDF</button>
        </div>
    </aside>

    <!-- ── CANVAS DERECHO ── -->
    <section class="esc-canvas-area">
        <h2>Vista técnica</h2>
        <p class="esc-canvas-hint">
            Previsualización proporcional a las dimensiones reales · Se actualiza automáticamente
        </p>
        <div class="esc-canvas-wrap" id="escCanvasWrap"></div>

        <!-- Resumen rápido de módulos -->
        <div class="esc-module-summary" id="escModuleSummary"></div>
    </section>

</main>

<!-- Campo oculto para serialización -->
<input type="hidden" id="escStateJson">

<script src="assets/escaparate-widget.js"></script>
<script>
window.EscaparateWidget.init({
    canvasWrap:  'escCanvasWrap',
    moduleList:  'escModuleList',
    btnAddModule:'escBtnAdd',
    facadeW:     'escFacadeW',
    facadeH:     'escFacadeH',
    tachaH:      'escTachaH',
    hiddenJson:  'escStateJson',
    preset:      'escaparate_vitrina',   // preset por defecto (imagen de ejemplo)
    onChanged: function(svg, state) {
        // Actualizar badge de módulos
        var badge = document.getElementById('escModuleBadge');
        if (badge) badge.textContent = state.modules.length + ' módulo' + (state.modules.length !== 1 ? 's' : '');

        // Actualizar barra de ancho
        var sumEl = document.getElementById('escWidthSum');
        var barEl = document.getElementById('escWidthBar');
        if (sumEl && state.modules) {
            var sum = state.modules.reduce(function(s, m) { return s + (m.width || 0); }, 0);
            sumEl.textContent = sum + ' mm';
            if (barEl) {
                var diff = Math.abs(sum - state.totalWidth);
                barEl.className = 'esc-width-bar' + (diff > 10 ? ' warn' : '');
                if (diff > 10) {
                    sumEl.textContent = sum + ' mm ≠ ' + state.totalWidth + ' mm';
                }
            }
        }

        // Resumen de módulos
        var summary = document.getElementById('escModuleSummary');
        if (summary && state.modules) {
            var types = window.EscaparateWidget.TYPES;
            summary.innerHTML = state.modules.map(function(m, i) {
                var t = types[m.type] || types.fijo;
                var op = m.opening ? ' · ' + m.opening.charAt(0).toUpperCase() : '';
                return '<span class="esc-module-chip" style="border-left:3px solid ' + t.stroke + '">'
                    + t.name + ' ' + m.width + 'mm' + op + '</span>';
            }).join('');
        }
    }
});

// Presets
document.querySelectorAll('[data-preset]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        window.EscaparateWidget.applyPreset(this.dataset.preset);
    });
});
</script>
</body>
</html>
