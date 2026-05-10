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

<!-- ── MODAL: Galería de plantillas ── -->
<div id="templateModal" class="tpl-modal" style="display:none">
    <div class="tpl-modal-inner">
        <div class="tpl-modal-header">
            <h2>Nuevo elemento</h2>
            <button class="tpl-modal-close" onclick="closeTemplateModal()">✕</button>
        </div>
        <div id="tplGroups"></div>
    </div>
</div>

<header class="topbar cfg-topbar">
    <div class="topbar-brand"><h1><?= h(tr('app_title', $lang)) ?></h1></div>
    <input id="clientName" class="topbar-client" placeholder="Cliente / Proyecto…" autocomplete="off">
    <div class="topbar-tools">
        <button id="btnSave" class="primary-button">Guardar presupuesto</button>
        <button class="secondary-button" onclick="window.print()">Imprimir</button>
        <nav>
            <a href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>"><?= h(tr('history', $lang)) ?></a>
        </nav>
    </div>
</header>

<main class="cfg-layout">

<!-- ══ SIDEBAR IZQ ══ -->
<aside class="cfg-sidebar">
    <div class="cfg-sidebar-header">
        <span class="cfg-sidebar-title">Elementos</span>
        <button id="btnAddEl" class="add-el-btn" title="Nuevo elemento">＋</button>
    </div>
    <div id="elementList" class="element-list"></div>
    <div class="cfg-sidebar-totals" id="sidebarTotals"></div>
</aside>

<!-- ══ CANVAS AREA ══ -->
<section class="cfg-canvas-area">
    <div class="canvas-hint">Clic en un panel para seleccionarlo · Arrastra montantes/travesaños para moverlos</div>
    <div class="canvas-wrap" id="canvasWrap"></div>
    <div id="bomActions" class="bom-actions" style="display:none">
        <button id="btnBom" class="bom-action-btn">▦ Generar descompuesto EXTRUAL S28</button>
    </div>
    <div id="bomSection" class="bom-section" style="display:none">
        <div class="bom-header">
            <h3>Descompuesto EXTRUAL S28</h3>
            <span id="bomPanelCount" class="field-hint"></span>
            <button class="secondary-button" onclick="hideBom()">Ocultar</button>
        </div>
        <div id="bomResults"></div>
    </div>
</section>

<!-- ══ PANEL CONFIG ══ -->
<aside class="cfg-panel" id="cfgPanel">
    <div id="cfgPanelEmpty" class="cfg-panel-empty"><p>Añade un elemento para empezar</p></div>
    <div id="cfgPanelContent" style="display:none">

        <!-- §1 DIMENSIONES -->
        <div class="cfg-section">
            <h3 class="cfg-section-title">Dimensiones del hueco</h3>
            <div class="grid two">
                <label>Ancho (mm)<input type="number" id="cfgW" min="200" max="15000" step="1"></label>
                <label>Alto (mm)<input type="number" id="cfgH" min="200" max="6000" step="1"></label>
            </div>
            <label>Unidades<input type="number" id="cfgQty" min="1" max="999" step="1" value="1"></label>
            <label>Nombre<input type="text" id="cfgName" placeholder="Ventana sala, V1…"></label>
        </div>

        <!-- §2 SERIE Y ACABADO -->
        <div class="cfg-section">
            <h3 class="cfg-section-title">Serie y acabado</h3>
            <label>Carpintería
                <select id="cfgCarpentry">
                    <option value="extrual_s28">EXTRUAL Serie 28 (con BOM)</option>
                    <option value="exlabesa">Exlabesa</option>
                    <option value="cortizo">Cortizo</option>
                    <option value="otra">Otra</option>
                </select>
            </label>
            <label>Serie / Referencia<input type="text" id="cfgCarpentryRef" placeholder="Serie 28, 55N…"></label>
            <label>Color / Acabado
                <select id="cfgColor">
                    <optgroup label="Blancos">
                        <option value="9016|#f1f0eb">RAL 9016 – Blanco Tráfico</option>
                        <option value="9010|#f4f4f0">RAL 9010 – Blanco Puro</option>
                        <option value="1013|#e8dfc8">RAL 1013 – Blanco Ostra</option>
                    </optgroup>
                    <optgroup label="Grises">
                        <option value="9006|#a5a5a5">RAL 9006 – Aluminio Blanco</option>
                        <option value="9007|#8c8c8c">RAL 9007 – Aluminio Gris</option>
                        <option value="7035|#cdd0cc">RAL 7035 – Gris Claro</option>
                        <option value="7040|#9da3a6">RAL 7040 – Gris Ventana</option>
                        <option value="7016|#383e42">RAL 7016 – Gris Antracita</option>
                    </optgroup>
                    <optgroup label="Verdes">
                        <option value="6005|#1f4021">RAL 6005 – Verde Musgo</option>
                        <option value="6009|#284028">RAL 6009 – Verde Abeto</option>
                    </optgroup>
                    <optgroup label="Marrones">
                        <option value="8017|#442f1e">RAL 8017 – Marrón Chocolate</option>
                        <option value="8014|#362718">RAL 8014 – Marrón Sepia</option>
                    </optgroup>
                    <optgroup label="Otros">
                        <option value="3009|#6e2a1e">RAL 3009 – Rojo Óxido</option>
                        <option value="5003|#1d2f4a">RAL 5003 – Azul Zafiro</option>
                    </optgroup>
                    <optgroup label="Anodizados">
                        <option value="anod_natural|#b8bfc4">Anodizado Natural</option>
                        <option value="anod_bronce|#8c6f45">Anodizado Bronce</option>
                        <option value="anod_champan|#c4a85a">Anodizado Champán</option>
                    </optgroup>
                    <optgroup label="Efectos madera">
                        <option value="madera_roble|#8b6340">Madera Roble</option>
                        <option value="madera_nogal|#5a3a1e">Madera Nogal</option>
                    </optgroup>
                </select>
            </label>
            <div class="color-preview-row">
                <span id="cfgColorSwatch" class="color-swatch"></span>
                <input type="text" id="cfgColorCustom" placeholder="Otro RAL o descripción…">
            </div>
        </div>

        <!-- §3 CORTE Y TAPAJUNTAS -->
        <div class="cfg-section">
            <h3 class="cfg-section-title">Corte y tapajuntas</h3>
            <label>Tipo de corte
                <select id="cfgCutType">
                    <option value="recto">Corte recto + tapajuntas</option>
                    <option value="mitered45">Corte 45° (ingletes)</option>
                </select>
            </label>
            <div id="cfgTapajuntasRow">
                <label>Tapajuntas
                    <select id="cfgTapajuntas">
                        <option value="0">Sin tapajuntas</option>
                        <option value="40">40 mm</option>
                        <option value="60">60 mm</option>
                        <option value="80">80 mm</option>
                    </select>
                </label>
            </div>
        </div>

        <!-- §4 PERSIANA -->
        <div class="cfg-section">
            <h3 class="cfg-section-title">Persiana / Cajón</h3>
            <label>Tipo
                <select id="cfgPersiana">
                    <option value="ninguna">Sin persiana</option>
                    <option value="registro">Cajón Registro (tapeta exterior)</option>
                    <option value="compacto">Compacto integrado</option>
                </select>
            </label>
            <div id="cfgPersianaOpts" style="display:none">
                <label>Alto cajón (mm)<input type="number" id="cfgPersianaAlto" min="100" max="400" step="5" value="180"></label>
                <label id="cfgPersianaForroRow">Forro tapeta
                    <select id="cfgPersianaForro">
                        <option value="40">40 mm (Ref. 6.755)</option>
                        <option value="60" selected>60 mm (Ref. 6.756)</option>
                        <option value="85">85 mm (Ref. 6.757)</option>
                    </select>
                </label>
            </div>
            <p class="field-hint persiana-info" id="cfgPersianaInfo"></p>
        </div>

        <!-- §5 DIVIDIR PANEL -->
        <div class="cfg-section">
            <h3 class="cfg-section-title">Dividir panel seleccionado</h3>
            <div class="btn-group">
                <button id="btnSplitV" class="secondary-button split-btn">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="1" y="1" width="16" height="16" rx="2"/><line x1="9" y1="1" x2="9" y2="17"/>
                    </svg>
                    Montante
                </button>
                <button id="btnSplitH" class="secondary-button split-btn">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="1" y="1" width="16" height="16" rx="2"/><line x1="1" y1="9" x2="17" y2="9"/>
                    </svg>
                    Travesaño
                </button>
            </div>
            <button id="btnUnsplit" class="secondary-button" style="width:100%;margin-top:0.4rem" disabled>✕ Eliminar división</button>
        </div>

        <!-- §5 MÓDULO SELECCIONADO -->
        <div class="cfg-section">
            <h3 class="cfg-section-title">Módulo seleccionado</h3>
            <p class="field-hint" id="panelInfo">Haz clic en un panel del canvas</p>
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
                <label id="extrualSysRow">Tipo EXTRUAL S28<select id="panelExtrualSystem"></select></label>
                <div id="pasoLibreRow" style="display:none">
                    <label>Paso libre (mm)<input type="number" id="pasoLibre" min="400" max="2500" step="1" placeholder="ej. 950"></label>
                    <p class="field-hint" id="pasoLibreInfo"></p>
                </div>
                <label>Vidrio
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
                <label id="junquilloRow">Junquillo
                    <select id="panelJunquillo">
                        <option value="curvo_clip" selected>Curvo clip</option>
                        <option value="curvo_grapa">Curvo grapa</option>
                        <option value="recto">Recto</option>
                        <option value="redondo_grapa">Redondo grapa</option>
                        <option value="redondo_clip">Redondo clip</option>
                    </select>
                </label>
                <label>Etiqueta<input type="text" id="panelLabel" placeholder="V1, P1, F1…"></label>
            </div>
        </div>

        <!-- §6 POSICIÓN CORTE -->
        <div class="cfg-section" id="splitControls" style="display:none">
            <h3 class="cfg-section-title">Posición del corte</h3>
            <div class="split-pos-row">
                <input type="range" id="splitRatio" min="10" max="90" value="50" step="1">
                <input type="number" id="splitMm" min="10" step="1" class="split-mm-input">
                <span class="split-mm-unit">mm</span>
            </div>
            <p class="field-hint" id="splitInfo" style="text-align:center">50% · 50%</p>
        </div>

        <!-- §7 PRECIO -->
        <div class="cfg-section">
            <h3 class="cfg-section-title">Precio</h3>
            <label>Modo
                <select id="cfgPricingMode">
                    <option value="comprada">Carpintería comprada</option>
                    <option value="fabricada">Fabricación propia</option>
                </select>
            </label>
            <div id="pricingComprada">
                <label>Coste ud. (€)<input type="number" id="cfgPurchasedCost" min="0" step="0.01" value="0"></label>
                <label>Margen comercial (%)<input type="number" id="cfgCommercialMargin" min="0" max="100" step="0.1" value="25"></label>
            </div>
            <div id="pricingFabricada" style="display:none">
                <label>€/ml aluminio<input type="number" id="cfgAlPrice" min="0" step="0.01" value="18"></label>
                <label>Mano de obra (€)<input type="number" id="cfgLabor" min="0" step="0.01" value="65"></label>
                <label>Margen (%)<input type="number" id="cfgMargin" min="0" max="100" step="0.1" value="25"></label>
            </div>
            <label>Costes extra (€)<input type="number" id="cfgExtra" min="0" step="0.01" value="0"></label>
            <label>IVA (%)<input type="number" id="cfgIva" min="0" max="100" step="0.1" value="21"></label>
            <div class="price-summary" id="priceSummary"></div>
        </div>

        <!-- Paneles + leyenda -->
        <div class="cfg-section">
            <h3 class="cfg-section-title">Paneles</h3>
            <div id="panelList" class="panel-list"></div>
        </div>
        <div class="cfg-section">
            <h3 class="cfg-section-title">Leyenda</h3>
            <div class="sys-legend" id="sysLegend"></div>
        </div>
        <div class="cfg-section">
            <button id="btnDupeEl" class="secondary-button" style="width:100%;margin-bottom:0.4rem">Duplicar elemento</button>
            <button id="btnDeleteEl" class="danger-button" style="width:100%">Eliminar elemento</button>
        </div>

    </div><!-- /cfgPanelContent -->
</aside>

</main>

<script src="assets/extrual_s28.js"></script>
<script>
// ══════════════════════════════════════════════════════════
//  BASE
// ══════════════════════════════════════════════════════════
function uid(){return Math.random().toString(36).slice(2,8);}

const GLASS_THICK_MAP={'4_6_4':16,'4_12_4':20,'4_16_4':24,'4_8_4_8_4':36,'laminar_6':6,'laminar_88':8.8,'simple_4':4};

function mkLeaf(system,label,opening){
    return{id:uid(),split:null,system:system||'fijo',label:label||'',opening:opening||'izq',
        extrualSystem:null,pasoLibre:null,glassType:'4_12_4',glassThick:20,junquilloType:'curvo_clip'};
}

const SYS={
    fijo:          {name:'Fijo',          fill:'#d0e8f4',stroke:'#3a70a0'},
    practicable:   {name:'Practicable',   fill:'#cceedd',stroke:'#2a7a3a'},
    oscilobatiente:{name:'Oscilobatiente',fill:'#f0eacc',stroke:'#907020'},
    abatible:      {name:'Abatible',      fill:'#cceef4',stroke:'#1a7898'},
    corredera:     {name:'Corredera',     fill:'#e4d0f4',stroke:'#6828a0'},
    puerta:        {name:'Puerta',        fill:'#f4ddd0',stroke:'#a04820'},
};
const NEEDS_OPENING=['practicable','oscilobatiente','corredera','puerta'];
const SYSTEM_TO_EXTRUAL={
    fijo:          [['v_fijo','Ventana Fija']],
    practicable:   [['v1h_prac','Ventana 1H Practicable'],['v2h_prac','Ventana 2H Practicable'],
                    ['v3h_prac','Ventana 3H Practicable'],['b1h_prac','Balconera 1H Practicable'],
                    ['b2h_prac','Balconera 2H Practicable']],
    oscilobatiente:[['v1h_osci','Ventana 1H Oscilobatiente']],
    abatible:      [['v_abatible','Ventana Abatible'],['b1h_ext','Balconera Ap. Exterior']],
    corredera:     [],
    puerta:        [['p1h_int','Puerta 1H Interior'],['p1h_fijo','Puerta 1H + Fijo']],
};
function autoMapExtrual(system){const o=SYSTEM_TO_EXTRUAL[system];return o&&o.length?o[0][0]:null;}

// ══════════════════════════════════════════════════════════
//  PLANTILLAS
// ══════════════════════════════════════════════════════════
const TEMPLATES={
    v_fija:{name:'Ventana Fija',group:'Ventanas',defaultW:1000,defaultH:1200,
        icon:'<rect x="1" y="1" width="22" height="22" rx="2" fill="#d0e8f4" stroke="#3a70a0" stroke-width="1.5"/>',
        buildTree:()=>{const n=mkLeaf('fijo','F1','izq');n.extrualSystem='v_fijo';return n;}},
    v1h_prac:{name:'Practicable 1H',group:'Ventanas',defaultW:800,defaultH:1200,
        icon:'<rect x="1" y="1" width="22" height="22" rx="2" fill="#cceedd" stroke="#2a7a3a" stroke-width="1.5"/><path d="M3,3 L3,21 L21,12 Z" fill="#2a7a3a" opacity="0.35"/>',
        buildTree:()=>{const n=mkLeaf('practicable','V1','izq');n.extrualSystem='v1h_prac';return n;}},
    v1h_osci:{name:'Oscilobatiente',group:'Ventanas',defaultW:800,defaultH:1200,
        icon:'<rect x="1" y="1" width="22" height="22" rx="2" fill="#f0eacc" stroke="#907020" stroke-width="1.5"/><path d="M3,3 L3,21 L21,12 Z" fill="#907020" opacity="0.3"/><path d="M3,21 L21,21 L12,10 Z" fill="#907020" opacity="0.3"/>',
        buildTree:()=>{const n=mkLeaf('oscilobatiente','V1','izq');n.extrualSystem='v1h_osci';return n;}},
    v2h_prac:{name:'Practicable 2H',group:'Ventanas',defaultW:1400,defaultH:1200,
        icon:'<rect x="1" y="1" width="22" height="22" rx="2" fill="#cceedd" stroke="#2a7a3a" stroke-width="1.5"/><line x1="12" y1="1" x2="12" y2="23" stroke="#2a7a3a" stroke-width="1.5"/><path d="M3,3 L3,21 L11,12 Z" fill="#2a7a3a" opacity="0.3"/><path d="M21,3 L21,21 L13,12 Z" fill="#2a7a3a" opacity="0.3"/>',
        buildTree:()=>{const a=mkLeaf('practicable','V1','izq');a.extrualSystem='v1h_prac';const b=mkLeaf('practicable','V2','der');b.extrualSystem='v1h_prac';return{id:uid(),split:{dir:'v',ratio:0.5,a,b},system:null,label:null,opening:null};}},
    v2h_cor:{name:'Corredera 2H',group:'Ventanas',defaultW:1500,defaultH:1200,
        icon:'<rect x="1" y="1" width="22" height="22" rx="2" fill="#e4d0f4" stroke="#6828a0" stroke-width="1.5"/><line x1="12" y1="1" x2="12" y2="23" stroke="#6828a0" stroke-width="1.5" stroke-dasharray="3,2"/><line x1="5" y1="12" x2="11" y2="12" stroke="#6828a0" stroke-width="1.5"/><polyline points="9,9 12,12 9,15" fill="none" stroke="#6828a0" stroke-width="1.3"/>',
        buildTree:()=>{const a=mkLeaf('corredera','H1','izq');const b=mkLeaf('corredera','H2','der');return{id:uid(),split:{dir:'v',ratio:0.5,a,b},system:null,label:null,opening:null};}},
    v_abatible:{name:'Abatible',group:'Ventanas',defaultW:800,defaultH:600,
        icon:'<rect x="1" y="1" width="22" height="22" rx="2" fill="#cceef4" stroke="#1a7898" stroke-width="1.5"/><path d="M3,19 L21,19 L12,8 Z" fill="#1a7898" opacity="0.35"/>',
        buildTree:()=>{const n=mkLeaf('abatible','V1','izq');n.extrualSystem='v_abatible';return n;}},
    b1h_prac:{name:'Balconera 1H',group:'Balconeras',defaultW:900,defaultH:2100,
        icon:'<rect x="2" y="1" width="20" height="30" rx="2" fill="#cceedd" stroke="#2a7a3a" stroke-width="1.5"/><path d="M4,3 L4,29 L22,16 Z" fill="#2a7a3a" opacity="0.35"/>',
        buildTree:()=>{const n=mkLeaf('practicable','B1','izq');n.extrualSystem='b1h_prac';return n;}},
    b2h_prac:{name:'Balconera 2H',group:'Balconeras',defaultW:1600,defaultH:2100,
        icon:'<rect x="2" y="1" width="20" height="30" rx="2" fill="#cceedd" stroke="#2a7a3a" stroke-width="1.5"/><line x1="12" y1="1" x2="12" y2="31" stroke="#2a7a3a" stroke-width="1.5"/><path d="M4,3 L4,29 L11,16 Z" fill="#2a7a3a" opacity="0.3"/><path d="M22,3 L22,29 L13,16 Z" fill="#2a7a3a" opacity="0.3"/>',
        buildTree:()=>{const a=mkLeaf('practicable','B1','izq');a.extrualSystem='b1h_prac';const b=mkLeaf('practicable','B2','der');b.extrualSystem='b1h_prac';return{id:uid(),split:{dir:'v',ratio:0.5,a,b},system:null,label:null,opening:null};}},
    p1h_int:{name:'Puerta Interior',group:'Puertas',defaultW:900,defaultH:2100,
        icon:'<rect x="2" y="1" width="20" height="30" rx="2" fill="#f4ddd0" stroke="#a04820" stroke-width="1.5"/><rect x="2" y="25" width="20" height="6" fill="#a04820" opacity="0.2"/><path d="M6,3 A14,14 0 0 1 22,3" fill="#a04820" opacity="0.2" stroke="#a04820" stroke-width="1" stroke-dasharray="3,2"/>',
        buildTree:()=>{const n=mkLeaf('puerta','P1','der');n.extrualSystem='p1h_int';n.pasoLibre=900;return n;}},
    prac_fijo:{name:'Practicable + Fijo',group:'Combinaciones',defaultW:1600,defaultH:1200,
        icon:'<rect x="1" y="1" width="22" height="22" rx="2" fill="#d0e8f4" stroke="#3a70a0" stroke-width="1.5"/><line x1="13" y1="1" x2="13" y2="23" stroke="#3a70a0" stroke-width="1.5"/><path d="M3,3 L3,21 L12,12 Z" fill="#2a7a3a" opacity="0.4"/>',
        buildTree:()=>{const p=mkLeaf('practicable','V1','izq');p.extrualSystem='v1h_prac';const f=mkLeaf('fijo','F1','izq');f.extrualSystem='v_fijo';return{id:uid(),split:{dir:'v',ratio:0.55,a:p,b:f},system:null,label:null,opening:null};}},
    fijo_prac:{name:'Fijo + Practicable',group:'Combinaciones',defaultW:1600,defaultH:1200,
        icon:'<rect x="1" y="1" width="22" height="22" rx="2" fill="#d0e8f4" stroke="#3a70a0" stroke-width="1.5"/><line x1="10" y1="1" x2="10" y2="23" stroke="#3a70a0" stroke-width="1.5"/><path d="M12,3 L12,21 L21,12 Z" fill="#2a7a3a" opacity="0.4"/>',
        buildTree:()=>{const f=mkLeaf('fijo','F1','izq');f.extrualSystem='v_fijo';const p=mkLeaf('practicable','V1','der');p.extrualSystem='v1h_prac';return{id:uid(),split:{dir:'v',ratio:0.45,a:f,b:p},system:null,label:null,opening:null};}},
    fijo_puerta:{name:'Fijo + Puerta',group:'Combinaciones',defaultW:2000,defaultH:2200,
        icon:'<rect x="2" y="1" width="20" height="30" rx="2" fill="#d0e8f4" stroke="#3a70a0" stroke-width="1.5"/><line x1="10" y1="1" x2="10" y2="31" stroke="#3a70a0" stroke-width="1.5"/><rect x="10" y="25" width="12" height="6" fill="#a04820" opacity="0.25"/>',
        buildTree:()=>{const f=mkLeaf('fijo','F1','izq');f.extrualSystem='v_fijo';const p=mkLeaf('puerta','P1','der');p.extrualSystem='p1h_int';p.pasoLibre=900;return{id:uid(),split:{dir:'v',ratio:0.4,a:f,b:p},system:null,label:null,opening:null};}},
    escaparate:{name:'Escaparate (F+P+F)',group:'Combinaciones',defaultW:3000,defaultH:2500,
        icon:'<rect x="1" y="1" width="22" height="30" rx="2" fill="#d0e8f4" stroke="#3a70a0" stroke-width="1.5"/><line x1="8" y1="1" x2="8" y2="31" stroke="#3a70a0" stroke-width="1.5"/><line x1="16" y1="1" x2="16" y2="31" stroke="#3a70a0" stroke-width="1.5"/><rect x="9" y="25" width="6" height="6" fill="#a04820" opacity="0.3"/>',
        buildTree:()=>{const f1=mkLeaf('fijo','F1','izq');f1.extrualSystem='v_fijo';const p=mkLeaf('puerta','P1','der');p.extrualSystem='p1h_int';p.pasoLibre=900;const f2=mkLeaf('fijo','F2','izq');f2.extrualSystem='v_fijo';const inner={id:uid(),split:{dir:'v',ratio:0.5,a:p,b:f2},system:null,label:null,opening:null};return{id:uid(),split:{dir:'v',ratio:0.33,a:f1,b:inner},system:null,label:null,opening:null};}},
    fijo_fijo:{name:'Fijo + Fijo',group:'Combinaciones',defaultW:2000,defaultH:1200,
        icon:'<rect x="1" y="1" width="22" height="22" rx="2" fill="#d0e8f4" stroke="#3a70a0" stroke-width="1.5"/><line x1="12" y1="1" x2="12" y2="23" stroke="#3a70a0" stroke-width="1.5"/>',
        buildTree:()=>{const f1=mkLeaf('fijo','F1','izq');f1.extrualSystem='v_fijo';const f2=mkLeaf('fijo','F2','izq');f2.extrualSystem='v_fijo';return{id:uid(),split:{dir:'v',ratio:0.5,a:f1,b:f2},system:null,label:null,opening:null};}},
    personalizado:{name:'Personalizado',group:'Combinaciones',defaultW:1200,defaultH:1200,
        icon:'<rect x="1" y="1" width="22" height="22" rx="2" fill="#eee" stroke="#888" stroke-width="1.5" stroke-dasharray="4,2"/><text x="12" y="16" text-anchor="middle" font-size="11" fill="#888">?</text>',
        buildTree:()=>mkLeaf('fijo','','izq')},
};

// ══════════════════════════════════════════════════════════
//  ESTADO
// ══════════════════════════════════════════════════════════
const state={clientName:'',elements:[],activeElementId:null};
let elSel=null;

function activeEl(){return state.elements.find(e=>e.id===state.activeElementId)||null;}

function mkElement(templateKey){
    const tpl=TEMPLATES[templateKey]||TEMPLATES.personalizado;
    const counter=state.elements.filter(e=>e.templateKey===templateKey).length+1;
    return{id:uid(),name:tpl.name+(counter>1?' '+counter:''),templateKey,
        facadeW:tpl.defaultW,facadeH:tpl.defaultH,qty:1,
        carpentry:'extrual_s28',carpentryRef:'',
        colorRal:'9016',colorHex:'#f1f0eb',colorName:'RAL 9016 – Blanco Tráfico',
        cutType:'recto',tapajuntas:0,
        pricingMode:'comprada',purchasedCost:0,commercialMarginPct:25,
        alPriceMl:18,laborCost:65,marginPct:25,extraCost:0,ivaPct:21,glassPriceM2:35,
        persiana:'ninguna',persianaAlto:180,persianaForroMm:60,
        tree:tpl.buildTree(),selModuleId:null};
}

// ══════════════════════════════════════════════════════════
//  ÁRBOL
// ══════════════════════════════════════════════════════════
function find(node,id){if(node.id===id)return node;if(node.split)return find(node.split.a,id)||find(node.split.b,id);return null;}
function findParent(node,id){if(!node.split)return null;if(node.split.a.id===id||node.split.b.id===id)return node;return findParent(node.split.a,id)||findParent(node.split.b,id);}
function leaves(node,x,y,w,h){x=x||0;y=y||0;w=w===undefined?1:w;h=h===undefined?1:h;if(!node.split)return[{node,x,y,w,h}];const sp=node.split;if(sp.dir==='v')return leaves(sp.a,x,y,w*sp.ratio,h).concat(leaves(sp.b,x+w*sp.ratio,y,w*(1-sp.ratio),h));return leaves(sp.a,x,y,w,h*sp.ratio).concat(leaves(sp.b,x,y+h*sp.ratio,w,h*(1-sp.ratio)));}
function allSplits(node,x,y,w,h){x=x||0;y=y||0;w=w===undefined?1:w;h=h===undefined?1:h;if(!node.split)return[];const sp=node.split,res=[{node,x,y,w,h}];if(sp.dir==='v')return res.concat(allSplits(sp.a,x,y,w*sp.ratio,h)).concat(allSplits(sp.b,x+w*sp.ratio,y,w*(1-sp.ratio),h));return res.concat(allSplits(sp.a,x,y,w,h*sp.ratio)).concat(allSplits(sp.b,x,y+h*sp.ratio,w,h*(1-sp.ratio)));}
function getNodeBounds(tree,nodeId){function t(node,x,y,w,h){if(node.id===nodeId)return{x,y,w,h};if(!node.split)return null;const sp=node.split;if(sp.dir==='v')return t(sp.a,x,y,w*sp.ratio,h)||t(sp.b,x+w*sp.ratio,y,w*(1-sp.ratio),h);return t(sp.a,x,y,w,h*sp.ratio)||t(sp.b,x,y+h*sp.ratio,w,h*(1-sp.ratio));}return t(tree,0,0,1,1);}

function splitNode(dir){const el=activeEl();if(!el)return;const node=find(el.tree,elSel);if(!node||node.split)return;const a=mkLeaf(node.system,node.label,node.opening),b=mkLeaf(node.system,'',node.opening);a.glassType=node.glassType;a.glassThick=node.glassThick;a.junquilloType=node.junquilloType;b.glassType=node.glassType;b.glassThick=node.glassThick;b.junquilloType=node.junquilloType;node.split={dir,ratio:0.5,a,b};node.system=null;node.label=null;node.opening=null;elSel=a.id;render();}
function unsplitParent(){const el=activeEl();if(!el)return;const parent=findParent(el.tree,elSel);if(!parent||parent.split.a.split||parent.split.b.split)return;const sys=parent.split.a.system||parent.split.b.system||'fijo';parent.split=null;parent.system=sys;parent.label='';parent.opening='izq';elSel=parent.id;render();}

// ══════════════════════════════════════════════════════════
//  SVG
// ══════════════════════════════════════════════════════════
const SVG_W=960,SVG_H=560,MARGIN=52;
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function hexToRgb(h){return[parseInt(h.slice(1,3),16),parseInt(h.slice(3,5),16),parseInt(h.slice(5,7),16)];}
function rgbToHex(r,g,b){return'#'+[r,g,b].map(v=>Math.max(0,Math.min(255,Math.round(v))).toString(16).padStart(2,'0')).join('');}
function darkenHex(h,a){if(!h||!h.startsWith('#'))return'#252e35';const[r,g,b]=hexToRgb(h);return rgbToHex(r*(1-a),g*(1-a),b*(1-a));}
function lightenHex(h,a){if(!h||!h.startsWith('#'))return'#5a6870';const[r,g,b]=hexToRgb(h);return rgbToHex(r+(255-r)*a,g+(255-g)*a,b+(255-b)*a);}

let svgMeta={sc:1,ox:0,oy:0};

function buildSVG(el){
    const fw=el.facadeW,fh=el.facadeH,avW=SVG_W-MARGIN*2,avH=SVG_H-MARGIN*2;
    const cajMm=(el.persiana&&el.persiana!=='ninguna')?(el.persianaAlto||180):0;
    const sc=Math.min(avW/fw,avH/(fh+cajMm)),W=fw*sc,H=fh*sc;
    const cajH=cajMm*sc; // cajón height in px
    const ox=(SVG_W-W)/2,oy=(SVG_H-H-cajH)/2+cajH,FR=Math.max(6,Math.min(14,sc*24));
    svgMeta={sc,ox,oy,W,H,FR};
    const fc=el.colorHex||'#3a4750',fd=darkenHex(fc,0.35),fm=darkenHex(fc,0.15),ft=lightenHex(fc,0.15);

    let s=`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${SVG_W} ${SVG_H}" width="${SVG_W}" height="${SVG_H}" id="mainSvg">`;
    s+=`<defs><pattern id="glass" patternUnits="userSpaceOnUse" width="14" height="14"><line x1="0" y1="14" x2="14" y2="0" stroke="#8ab4cc" stroke-width="0.6" opacity="0.5"/></pattern><linearGradient id="glassGrad" x1="0" y1="0" x2="0.25" y2="1"><stop offset="0%" stop-color="#cce0f0" stop-opacity="0.95"/><stop offset="100%" stop-color="#96c0dc" stop-opacity="0.85"/></linearGradient></defs>`;

    // Shadow + frame
    s+=`<rect x="${ox-FR-1}" y="${oy-FR-1}" width="${W+FR*2+2}" height="${H+FR*2+2}" fill="#1a2530" opacity="0.22" rx="5" transform="translate(3,4)"/>`;
    s+=`<rect x="${ox-FR}" y="${oy-FR}" width="${W+FR*2}" height="${H+FR*2}" fill="${fc}" rx="3"/>`;
    s+=`<polygon points="${ox-FR},${oy-FR} ${ox+W+FR},${oy-FR} ${ox+W},${oy} ${ox},${oy}" fill="${ft}"/>`;
    s+=`<polygon points="${ox},${oy+H} ${ox+W},${oy+H} ${ox+W+FR},${oy+H+FR} ${ox-FR},${oy+H+FR}" fill="${fd}"/>`;
    s+=`<polygon points="${ox-FR},${oy-FR} ${ox},${oy} ${ox},${oy+H} ${ox-FR},${oy+H+FR}" fill="${fm}"/>`;
    s+=`<polygon points="${ox+W},${oy} ${ox+W+FR},${oy-FR} ${ox+W+FR},${oy+H+FR} ${ox+W},${oy+H}" fill="${fd}"/>`;

    // Tapajuntas
    const tapMm=el.tapajuntas||0;
    if(tapMm>0&&el.cutType==='recto'){const tp=tapMm*sc,tx=ox-FR-tp,ty=oy-FR-tp,tw=W+FR*2+tp*2,th=H+FR*2+tp*2;s+=`<rect x="${tx}" y="${ty}" width="${tw}" height="${th}" fill="#c8b898" rx="3" opacity="0.85"/>`;s+=`<rect x="${ox-FR}" y="${oy-FR}" width="${W+FR*2}" height="${H+FR*2}" fill="none" stroke="#8a6a48" stroke-width="1.2"/>`;}
    // Corte 45°
    if(el.cutType==='mitered45'){const f=FR*0.9;[[ox-FR,oy-FR,1,1],[ox+W+FR,oy-FR,-1,1],[ox-FR,oy+H+FR,1,-1],[ox+W+FR,oy+H+FR,-1,-1]].forEach(([cx,cy,dx,dy])=>{s+=`<line x1="${cx}" y1="${cy}" x2="${cx+f*dx}" y2="${cy+f*dy}" stroke="#888" stroke-width="1.8" opacity="0.8"/>`;});}

    // Panels
    const leafs=leaves(el.tree);
    for(const{node,x,y,w,h}of leafs){
        const px=ox+x*W,py=oy+y*H,pw=w*W,ph=h*H,sel=node.id===elSel;
        const col=SYS[node.system]||SYS.fijo,panW=Math.round(fw*w),panH=Math.round(fh*h);
        s+=`<rect x="${px}" y="${py}" width="${pw}" height="${ph}" fill="${fc}"/>`;
        const gi=FR*0.5,gx=px+gi,gy=py+gi,gw=pw-gi*2,gh=ph-gi*2;
        if(gw>4&&gh>4){
            const isFijo=node.system==='fijo',HJ=isFijo?0:Math.max(3,FR*0.6);
            s+=`<rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="${col.fill}"/>`;
            if(!isFijo&&HJ>0&&gw>HJ*3&&gh>HJ*3){s+=`<rect x="${gx}" y="${gy}" width="${gw}" height="${HJ}" fill="${fc}"/>`;s+=`<rect x="${gx}" y="${gy+gh-HJ}" width="${gw}" height="${HJ}" fill="${fd}"/>`;s+=`<rect x="${gx}" y="${gy}" width="${HJ}" height="${gh}" fill="${fm}"/>`;s+=`<rect x="${gx+gw-HJ}" y="${gy}" width="${HJ}" height="${gh}" fill="${fd}"/>`;s+=`<rect x="${gx+HJ*.15}" y="${gy+HJ*.1}" width="${gw-HJ*.3}" height="${HJ*.3}" fill="white" opacity="0.12"/>`;}
            const vx=gx+HJ,vy=gy+HJ,vw=gw-HJ*2,vh=gh-HJ*2;
            if(vw>2&&vh>2){
                s+=`<rect x="${vx}" y="${vy}" width="${vw}" height="${vh}" fill="url(#glassGrad)" class="panel-r" data-id="${esc(node.id)}" style="cursor:pointer"/>`;
                s+=`<rect x="${vx}" y="${vy}" width="${vw}" height="${vh}" fill="url(#glass)" pointer-events="none" opacity="0.5"/>`;
                s+=`<polygon points="${vx},${vy} ${vx+vw*.3},${vy} ${vx+vw*.09},${vy+vh} ${vx},${vy+vh}" fill="white" opacity="0.13" pointer-events="none"/>`;
                if(sel)s+=`<rect x="${vx}" y="${vy}" width="${vw}" height="${vh}" fill="none" stroke="#1d4ed8" stroke-width="2.5" stroke-dasharray="7,3" pointer-events="none"/>`;
                s+=sysIndicator(node.system,node.opening,vx,vy,vw,vh,col.stroke);
                const cx=gx+gw/2,cy=gy+gh/2,exKey=node.extrualSystem||autoMapExtrual(node.system);
                const exName=exKey&&SYSTEMS[exKey]?SYSTEMS[exKey].name:'',lbl=esc(node.label||col.name);
                const fs=Math.min(11.5,gw*.085,gh*.13);
                if(fs>4.5&&gw>35&&gh>22){s+=`<rect x="${cx-lbl.length*fs*.33-4}" y="${cy-fs-(gh>50?10:0)-4}" width="${lbl.length*fs*.66+8}" height="${fs+6}" fill="white" opacity="0.55" rx="3" pointer-events="none"/>`;s+=`<text x="${cx}" y="${cy-(gh>50?8:0)}" text-anchor="middle" dominant-baseline="middle" font-size="${fs.toFixed(1)}" font-family="system-ui,sans-serif" fill="#1a2730" font-weight="700" pointer-events="none">${lbl}</text>`;if(gh>50){const fsm=Math.min(9,fs*.78);s+=`<text x="${cx}" y="${cy+10}" text-anchor="middle" font-size="${fsm.toFixed(1)}" font-family="monospace" fill="#364a58" pointer-events="none">${panW}×${panH}</text>`;if(exName&&gh>70){const fse=Math.min(8,fsm*.85);s+=`<text x="${cx}" y="${cy+10+fsm+3}" text-anchor="middle" font-size="${fse.toFixed(1)}" font-family="system-ui" fill="#5070a0" pointer-events="none">${esc(exName)}</text>`;}}}
            }
        }
    }

    // Split handles
    for(const{node,x,y,w,h}of allSplits(el.tree)){const sp=node.split,nx=ox+x*W,ny=oy+y*H,nw=w*W,nh=h*H;if(sp.dir==='v'){const dx=nx+nw*sp.ratio,my=ny+nh/2;s+=`<line x1="${dx}" y1="${ny}" x2="${dx}" y2="${ny+nh}" stroke="${fd}" stroke-width="3" pointer-events="none"/>`;s+=`<rect class="div-handle" data-splitid="${esc(node.id)}" data-dir="v" x="${dx-6}" y="${ny}" width="12" height="${nh}" fill="rgba(255,255,255,0.01)" style="cursor:col-resize"/>`;s+=`<circle cx="${dx}" cy="${my}" r="5" fill="#5a7080" opacity="0.7" pointer-events="none"/>`;s+=`<line x1="${dx-9}" y1="${my}" x2="${dx+9}" y2="${my}" stroke="white" stroke-width="1.5" opacity="0.8" pointer-events="none"/>`;}else{const dy=ny+nh*sp.ratio,mx=nx+nw/2;s+=`<line x1="${nx}" y1="${dy}" x2="${nx+nw}" y2="${dy}" stroke="${fd}" stroke-width="3" pointer-events="none"/>`;s+=`<rect class="div-handle" data-splitid="${esc(node.id)}" data-dir="h" x="${nx}" y="${dy-6}" width="${nw}" height="12" fill="rgba(255,255,255,0.01)" style="cursor:row-resize"/>`;s+=`<circle cx="${mx}" cy="${dy}" r="5" fill="#5a7080" opacity="0.7" pointer-events="none"/>`;s+=`<line x1="${mx}" y1="${dy-9}" x2="${mx}" y2="${dy+9}" stroke="white" stroke-width="1.5" opacity="0.8" pointer-events="none"/>`;}}

    // Dim lines
    const dyL=oy-34;s+=dimLine(ox,dyL+10,ox+W,dyL+10,true);s+=`<rect x="${ox+W/2-28}" y="${dyL-1}" width="56" height="14" fill="white" opacity="0.7" rx="3"/>`;s+=`<text x="${ox+W/2}" y="${dyL+10}" text-anchor="middle" dominant-baseline="middle" font-size="11" font-family="monospace" fill="#1a2830" font-weight="600">${fw} mm</text>`;
    const dxL=ox-34;s+=dimLine(dxL+10,oy,dxL+10,oy+H,false);s+=`<text x="${dxL+4}" y="${oy+H/2}" text-anchor="middle" dominant-baseline="middle" font-size="11" font-family="monospace" fill="#1a2830" font-weight="600" transform="rotate(-90 ${dxL+4} ${oy+H/2})">${fh} mm</text>`;
    for(const{x,w}of leafs){if(leafs.length>1){const px=ox+x*W,pe=px+w*W,pW=Math.round(fw*w),dy2=oy+H+18;s+=`<line x1="${px+2}" y1="${dy2-3}" x2="${px+2}" y2="${dy2+3}" stroke="#6a8090" stroke-width="1"/>`;s+=`<line x1="${pe-2}" y1="${dy2-3}" x2="${pe-2}" y2="${dy2+3}" stroke="#6a8090" stroke-width="1"/>`;s+=`<line x1="${px+2}" y1="${dy2}" x2="${pe-2}" y2="${dy2}" stroke="#6a8090" stroke-width="0.8" stroke-dasharray="3,2"/>`;s+=`<text x="${px+w*W/2}" y="${dy2+9}" text-anchor="middle" font-size="8.5" font-family="monospace" fill="#6a8090">${pW}</text>`;}}

    // Cajón de persiana
    if(cajMm>0){
        const bx=ox-FR,by=oy-FR-cajH,bw=W+FR*2,bh=cajH;
        if(el.persiana==='registro'){
            s+=`<defs><pattern id="cajhatch" patternUnits="userSpaceOnUse" width="7" height="7"><line x1="0" y1="7" x2="7" y2="0" stroke="${ft}" stroke-width="0.9" opacity="0.6"/></pattern></defs>`;
            s+=`<rect x="${bx}" y="${by}" width="${bw}" height="${bh}" fill="${lightenHex(fc,0.22)}" stroke="${fd}" stroke-width="1.5" rx="3"/>`;
            s+=`<rect x="${bx}" y="${by}" width="${bw}" height="${bh}" fill="url(#cajhatch)" rx="3" pointer-events="none"/>`;
            // Maintenance access line
            s+=`<line x1="${bx+8}" y1="${by+bh-5}" x2="${bx+bw-8}" y2="${by+bh-5}" stroke="${fd}" stroke-width="1" stroke-dasharray="6,3" opacity="0.7" pointer-events="none"/>`;
            const lh=Math.min(10,bh*0.52);if(lh>5){s+=`<text x="${bx+bw/2}" y="${by+bh/2+lh*0.3}" text-anchor="middle" font-size="${lh.toFixed(1)}" font-family="system-ui" fill="${fd}" font-weight="600" pointer-events="none">REGISTRO ${cajMm} mm</text>`;}
        } else {
            s+=`<rect x="${bx}" y="${by}" width="${bw}" height="${bh}" fill="${fc}" stroke="${fd}" stroke-width="1.5" rx="3"/>`;
            s+=`<rect x="${bx+FR*0.5}" y="${by+bh*0.2}" width="${bw-FR}" height="${bh*0.6}" fill="${lightenHex(fc,0.18)}" rx="2" pointer-events="none"/>`;
            const lh=Math.min(10,bh*0.48);if(lh>5){s+=`<text x="${bx+bw/2}" y="${by+bh/2+lh*0.3}" text-anchor="middle" font-size="${lh.toFixed(1)}" font-family="system-ui" fill="${ft}" font-weight="600" pointer-events="none">COMPACTO ${cajMm} mm</text>`;}
        }
        // Cota cajón
        const dyC=by-16;s+=dimLine(bx,dyC,bx+bw,dyC,true);
        s+=`<rect x="${bx+bw/2-22}" y="${dyC-11}" width="44" height="13" fill="white" opacity="0.75" rx="2"/>`;
        s+=`<text x="${bx+bw/2}" y="${dyC-1}" text-anchor="middle" font-size="9" font-family="monospace" fill="#1a2830">${cajMm} mm</text>`;
    }

    s+=`</svg>`;return s;
}

function dimLine(x1,y1,x2,y2,h){const t=h?`<line x1="${x1}" y1="${y1-5}" x2="${x1}" y2="${y1+5}" stroke="#5a6a78" stroke-width="1.2"/><line x1="${x2}" y1="${y1-5}" x2="${x2}" y2="${y1+5}" stroke="#5a6a78" stroke-width="1.2"/>`:` <line x1="${x1-5}" y1="${y1}" x2="${x1+5}" y2="${y1}" stroke="#5a6a78" stroke-width="1.2"/><line x1="${x1-5}" y1="${y2}" x2="${x1+5}" y2="${y2}" stroke="#5a6a78" stroke-width="1.2"/>`;return`<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#5a6a78" stroke-width="1"/>${t}`;}

function sysIndicator(sys,op,x,y,w,h,col){
    if(w<18||h<18)return'';const sw=1.5,der=op==='der';
    switch(sys){
        case'practicable':{const hx=der?x+w-4:x+4,tx=der?x+4:x+w-4;return`<path d="M${hx},${y+4} L${hx},${y+h-4} L${tx},${y+h/2} Z" fill="${col}28" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/><circle cx="${hx}" cy="${y+h/2}" r="3.2" fill="${col}" pointer-events="none"/>`;}
        case'oscilobatiente':{const hx=der?x+w-4:x+4,tx=der?x+4:x+w-4;return`<path d="M${hx},${y+4} L${hx},${y+h-4} L${tx},${y+h/2} Z" fill="${col}20" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/><path d="M${x+4},${y+h-4} L${x+w-4},${y+h-4} L${x+w/2},${y+h*.44} Z" fill="${col}20" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/><circle cx="${hx}" cy="${y+h/2}" r="3.2" fill="${col}" pointer-events="none"/>`;}
        case'abatible':return`<path d="M${x+4},${y+4} L${x+w-4},${y+4} L${x+w/2},${y+h*.54} Z" fill="${col}20" stroke="${col}" stroke-width="${sw}" stroke-linejoin="round" pointer-events="none"/>`;
        case'corredera':{const dr=der?1:-1,cx=x+w/2,cy=y+h/2,a=Math.min(24,w*.28);return`<line x1="${cx-a}" y1="${cy}" x2="${cx+a}" y2="${cy}" stroke="${col}" stroke-width="${sw+.2}" pointer-events="none"/><polyline points="${cx+a*.5*dr},${cy-7} ${cx+a*dr},${cy} ${cx+a*.5*dr},${cy+7}" fill="none" stroke="${col}" stroke-width="${sw}" pointer-events="none"/><line x1="${cx}" y1="${y+6}" x2="${cx}" y2="${y+h-6}" stroke="${col}" stroke-width="0.9" stroke-dasharray="4,3" pointer-events="none"/>`;}
        case'puerta':{const hx=der?x+w-4:x+4,s2=Math.min(w*.62,h*.62),ex=der?hx-s2:hx+s2;return`<rect x="${x+4}" y="${y+h-10}" width="${w-8}" height="7" fill="${col}30" stroke="${col}" stroke-width="1" rx="1.5" pointer-events="none"/><line x1="${hx}" y1="${y+4}" x2="${hx}" y2="${y+h-10}" stroke="${col}" stroke-width="2.5" pointer-events="none"/><path d="M${hx},${y+4} A${s2},${s2} 0 0 ${der?0:1} ${ex},${y+4}" fill="${col}18" stroke="${col}" stroke-width="${sw}" stroke-dasharray="5,2.5" pointer-events="none"/>`;}
        default:return'';
    }
}

// ══════════════════════════════════════════════════════════
//  DRAG
// ══════════════════════════════════════════════════════════
let dragState=null;
document.addEventListener('mousedown',e=>{
    const handle=e.target.closest('.div-handle');if(!handle)return;
    const el=activeEl();if(!el)return;e.preventDefault();
    const sn=find(el.tree,handle.dataset.splitid);if(!sn||!sn.split)return;
    const dir=sn.split.dir,bounds=getNodeBounds(el.tree,sn.id);
    dragState={sn,dir,startRatio:sn.split.ratio,startMouse:dir==='v'?e.clientX:e.clientY,
        parentWmm:Math.round(el.facadeW*bounds.w),parentHmm:Math.round(el.facadeH*bounds.h)};
});
document.addEventListener('mousemove',e=>{
    if(!dragState)return;const svgEl=document.getElementById('mainSvg');if(!svgEl)return;
    const r=svgEl.getBoundingClientRect(),pxU=(dragState.dir==='v'?r.width:r.height)/(dragState.dir==='v'?SVG_W:SVG_H);
    const pxMm=svgMeta.sc*pxU,delta=(dragState.dir==='v'?e.clientX:e.clientY)-dragState.startMouse;
    const dimMm=dragState.dir==='v'?dragState.parentWmm:dragState.parentHmm;
    dragState.sn.split.ratio=Math.max(.05,Math.min(.95,dragState.startRatio+delta/pxMm/dimMm));render();
});
document.addEventListener('mouseup',()=>{dragState=null;});

// ══════════════════════════════════════════════════════════
//  PRECIO
// ══════════════════════════════════════════════════════════
function calcElement(el){
    const fw=el.facadeW,fh=el.facadeH,leafs=leaves(el.tree);
    let alMl=(fw*2+fh*2)/1000;
    for(const{w,h,node}of leafs){if(node.system!=='fijo'){const lW=fw*w/1000,lH=fh*h/1000;alMl+=(lW*2+lH*2)*.35;}}
    alMl*=el.qty;
    let glassM2=0;for(const{w,h}of leafs)glassM2+=(fw*w/1000)*(fh*h/1000);glassM2*=el.qty;
    let base;if(el.pricingMode==='comprada'){base=el.purchasedCost*el.qty+el.extraCost;}
    else{base=alMl*el.alPriceMl+glassM2*(el.glassPriceM2||35)+el.laborCost+el.extraCost;}
    const mp=el.pricingMode==='comprada'?el.commercialMarginPct:el.marginPct;
    const margin=base*mp/100,iva=(base+margin)*el.ivaPct/100;
    return{alMl,glassM2,base,margin,iva,total:base+margin+iva,marginPct:mp};
}

// ══════════════════════════════════════════════════════════
//  BOM
// ══════════════════════════════════════════════════════════
function buildAndRenderBOM(){
    const el=activeEl();if(!el)return;
    const leafs=leaves(el.tree),bomPanels=[];
    for(const{node,w,h}of leafs){const sk=node.extrualSystem||autoMapExtrual(node.system);if(!sk||!SYSTEMS[sk])continue;const L=Math.round(el.facadeW*w),H=Math.round(el.facadeH*h),opts={glassThick:node.glassThick||20,junquilloType:node.junquilloType||'curvo_clip'};bomPanels.push({label:node.label||(SYS[node.system]?SYS[node.system].name:sk),sk,L,H,result:SYSTEMS[sk].calc(L,H,el.qty,opts)});}
    if(!bomPanels.length){document.getElementById('bomResults').innerHTML='<p class="field-hint bom-empty">Asigna tipo EXTRUAL a cada panel.</p>';document.getElementById('bomPanelCount').textContent='';return;}
    document.getElementById('bomPanelCount').textContent=`${bomPanels.length} panel${bomPanels.length>1?'es':''}`;
    const barT={},allG=[],accT={};
    for(const{label,sk,result}of bomPanels){for(const b of result.bars){const k=b.ref+'|'+b.desc;if(!barT[k])barT[k]={ref:b.ref,desc:b.desc,cuts:[]};barT[k].cuts.push({cut:b.cut,qty:b.qty,panel:label});}for(const g of result.glass)allG.push({...g,panel:label});for(const a of result.accessories){const k=a.ref+'|'+a.desc;if(!accT[k])accT[k]={ref:a.ref,desc:a.desc,qty:a.qty,note:a.note||''};}}
    let html='',totAlMm=0;
    html+=`<h4 class="bom-table-title">Perfiles de aluminio</h4><table class="bom-table"><thead><tr><th>Ref.</th><th>Descripción</th><th>Panel</th><th>Corte (mm)</th><th>Cant.</th><th>Total ml</th></tr></thead><tbody>`;
    for(const[,b]of Object.entries(barT))for(const c of b.cuts){const ml=(c.cut*c.qty/1000).toFixed(3);totAlMm+=c.cut*c.qty;html+=`<tr><td class="ref-cell">${esc(b.ref)}</td><td>${esc(PROFILES[b.ref]||b.desc)}</td><td class="panel-cell">${esc(c.panel)}</td><td>${Math.round(c.cut)}</td><td>${c.qty}</td><td>${ml}</td></tr>`;}
    html+=`</tbody><tfoot><tr><td colspan="5"><strong>Total aluminio</strong></td><td><strong>${(totAlMm/1000).toFixed(3)} ml</strong></td></tr></tfoot></table>`;
    let totM2=0;
    html+=`<h4 class="bom-table-title">Vidrio</h4><table class="bom-table"><thead><tr><th>Panel</th><th>Descripción</th><th>Ancho</th><th>Alto</th><th>Cant.</th><th>m²</th></tr></thead><tbody>`;
    for(const g of allG){const m2=(g.W/1000)*(g.H/1000)*g.qty;totM2+=m2;const cl=g.W<=0||g.H<=0?'cell-error':'';html+=`<tr><td class="panel-cell">${esc(g.panel)}</td><td>${esc(g.desc)}</td><td class="${cl}">${Math.round(g.W)}</td><td class="${cl}">${Math.round(g.H)}</td><td>${g.qty}</td><td>${m2.toFixed(3)}</td></tr>`;}
    html+=`</tbody><tfoot><tr><td colspan="5"><strong>Total m² vidrio</strong></td><td><strong>${totM2.toFixed(3)} m²</strong></td></tr></tfoot></table>`;
    html+=`<h4 class="bom-table-title">Accesorios</h4><table class="bom-table"><thead><tr><th>Ref.</th><th>Descripción</th><th>Cant.</th><th>Nota</th></tr></thead><tbody>`;
    for(const[,a]of Object.entries(accT))html+=`<tr><td class="ref-cell">${esc(a.ref)}</td><td>${esc(a.desc)}</td><td>${esc(a.qty)}</td><td class="note-cell">${esc(a.note)}</td></tr>`;
    html+=`</tbody></table><p class="bom-note">S28: cara marco 21.8 mm · desc. hoja 43.6 mm · desc. puerta 73.6 mm</p>`;
    if(el.persiana==='registro'){
        const forroRefs={'40':'6.755','60':'6.756','85':'6.757'};
        const fmm=String(el.persianaForroMm||60);
        const ref=forroRefs[fmm]||'6.756';
        const fw=el.facadeW,qty=el.qty;
        const forroMl=((fw/1000)*qty).toFixed(3);
        html+=`<h4 class="bom-table-title">Cajón Registro ${fmm}mm</h4>`;
        html+=`<table class="bom-table"><thead><tr><th>Ref.</th><th>Descripción</th><th>Longitud (mm)</th><th>Cant.</th><th>Total ml</th></tr></thead><tbody>`;
        html+=`<tr><td class="ref-cell">${esc(ref)}</td><td>Forro tapeta registro ${fmm}mm</td><td>${fw}</td><td>${qty}</td><td>${forroMl}</td></tr>`;
        html+=`</tbody><tfoot><tr><td colspan="4"><strong>Total forro</strong></td><td><strong>${forroMl} ml</strong></td></tr></tfoot></table>`;
    }
    document.getElementById('bomResults').innerHTML=html;
}

function showBom(){buildAndRenderBOM();document.getElementById('bomSection').style.display='';document.getElementById('btnBom').textContent='▦ Ocultar descompuesto';document.getElementById('btnBom').onclick=hideBom;}
function hideBom(){document.getElementById('bomSection').style.display='none';document.getElementById('btnBom').textContent='▦ Generar descompuesto EXTRUAL S28';document.getElementById('btnBom').onclick=showBom;}

// ══════════════════════════════════════════════════════════
//  SIDEBAR
// ══════════════════════════════════════════════════════════
function renderSidebar(){
    const list=document.getElementById('elementList');
    if(!state.elements.length){list.innerHTML='<p class="cfg-empty-hint">Pulsa ＋ para añadir un elemento</p>';}
    else{list.innerHTML=state.elements.map(el=>{const calc=calcElement(el),sel=el.id===state.activeElementId?' active':'';return`<div class="el-list-item${sel}" data-elid="${esc(el.id)}"><div class="el-list-item-name">${esc(el.name)}</div><div class="el-list-item-info">${el.facadeW}×${el.facadeH} mm · ${el.qty} ud${el.qty>1?'s':''}</div><div class="el-list-item-price">${calc.total.toFixed(2)} €</div></div>`;}).join('');list.querySelectorAll('.el-list-item').forEach(i=>i.addEventListener('click',()=>selectElement(i.dataset.elid)));}
    const tot=document.getElementById('sidebarTotals');
    if(!state.elements.length){tot.innerHTML='';return;}
    const tTotal=state.elements.reduce((s,e)=>s+calcElement(e).total,0);
    const tAl=state.elements.reduce((s,e)=>s+calcElement(e).alMl,0);
    const tGl=state.elements.reduce((s,e)=>s+calcElement(e).glassM2,0);
    tot.innerHTML=`<div class="totals-row"><span>Aluminio</span><span>${tAl.toFixed(2)} ml</span></div><div class="totals-row"><span>Vidrio</span><span>${tGl.toFixed(2)} m²</span></div><div class="totals-row totals-total"><span>Total</span><span>${tTotal.toFixed(2)} €</span></div>`;
}

// ══════════════════════════════════════════════════════════
//  CONFIG PANEL
// ══════════════════════════════════════════════════════════
function syncConfigPanel(){
    const el=activeEl();
    const empty=document.getElementById('cfgPanelEmpty'),content=document.getElementById('cfgPanelContent');
    if(!el){empty.style.display='';content.style.display='none';return;}
    empty.style.display='none';content.style.display='';
    document.getElementById('cfgW').value=el.facadeW;
    document.getElementById('cfgH').value=el.facadeH;
    document.getElementById('cfgQty').value=el.qty;
    document.getElementById('cfgName').value=el.name;
    document.getElementById('cfgCarpentry').value=el.carpentry;
    document.getElementById('cfgCarpentryRef').value=el.carpentryRef;
    const colorSel=document.getElementById('cfgColor');
    let found=false;for(const opt of colorSel.options){if(opt.value.startsWith(el.colorRal+'|')){colorSel.value=opt.value;found=true;break;}}
    if(!found)colorSel.selectedIndex=0;
    document.getElementById('cfgColorSwatch').style.background=el.colorHex;
    document.getElementById('cfgColorCustom').value=found?'':el.colorName;
    document.getElementById('cfgCutType').value=el.cutType;
    document.getElementById('cfgTapajuntas').value=el.tapajuntas;
    document.getElementById('cfgTapajuntasRow').style.display=el.cutType==='recto'?'':'none';
    document.getElementById('bomActions').style.display=el.carpentry==='extrual_s28'?'':'none';
    if(el.carpentry!=='extrual_s28')document.getElementById('bomSection').style.display='none';
    document.getElementById('cfgPricingMode').value=el.pricingMode;
    document.getElementById('pricingComprada').style.display=el.pricingMode==='comprada'?'':'none';
    document.getElementById('pricingFabricada').style.display=el.pricingMode==='fabricada'?'':'none';
    document.getElementById('cfgPurchasedCost').value=el.purchasedCost;
    document.getElementById('cfgCommercialMargin').value=el.commercialMarginPct;
    document.getElementById('cfgAlPrice').value=el.alPriceMl;
    document.getElementById('cfgLabor').value=el.laborCost;
    document.getElementById('cfgMargin').value=el.marginPct;
    document.getElementById('cfgExtra').value=el.extraCost;
    document.getElementById('cfgIva').value=el.ivaPct;
    document.getElementById('cfgPersiana').value=el.persiana||'ninguna';
    document.getElementById('cfgPersianaAlto').value=el.persianaAlto||180;
    document.getElementById('cfgPersianaForro').value=String(el.persianaForroMm||60);
    const hasPers=el.persiana&&el.persiana!=='ninguna';
    document.getElementById('cfgPersianaOpts').style.display=hasPers?'':'none';
    document.getElementById('cfgPersianaForroRow').style.display=el.persiana==='registro'?'':'none';
    document.getElementById('cfgPersianaInfo').textContent=hasPers?`Cajón ${el.persianaAlto}mm sobre el marco (${el.persiana==='registro'?'apertura mantenimiento':'integrado'})`:'';
    updatePriceSummary(el);updateModuleControls(el);updatePanelList(el);updateLegend(el);
}

function updatePriceSummary(el){const c=calcElement(el);document.getElementById('priceSummary').innerHTML=`<div class="price-row"><span>Base</span><span>${c.base.toFixed(2)} €</span></div><div class="price-row"><span>Margen (${c.marginPct}%)</span><span>${c.margin.toFixed(2)} €</span></div><div class="price-row"><span>IVA (${el.ivaPct}%)</span><span>${c.iva.toFixed(2)} €</span></div><div class="price-row price-total"><span>Total (${el.qty} ud${el.qty>1?'s':''})</span><span>${c.total.toFixed(2)} €</span></div>`;}

function updateModuleControls(el){
    const node=elSel?find(el.tree,elSel):null,parent=node?findParent(el.tree,elSel):null,isLeaf=node&&!node.split;
    if(node){const lf=leaves(el.tree).find(l=>l.node.id===elSel);if(lf){const pw=Math.round(el.facadeW*lf.w),ph=Math.round(el.facadeH*lf.h);document.getElementById('panelInfo').textContent=`${pw} × ${ph} mm`;}}
    else document.getElementById('panelInfo').textContent='Haz clic en un panel del canvas';
    document.getElementById('leafControls').style.opacity=isLeaf?'1':'0.35';
    document.getElementById('leafControls').style.pointerEvents=isLeaf?'':'none';
    if(isLeaf){
        document.getElementById('panelSystem').value=node.system||'fijo';
        document.getElementById('panelLabel').value=node.label||'';
        document.getElementById('panelOpening').value=node.opening||'izq';
        document.getElementById('panelGlassType').value=node.glassType||'4_12_4';
        document.getElementById('panelJunquillo').value=node.junquilloType||'curvo_clip';
        document.getElementById('openingRow').style.display=NEEDS_OPENING.includes(node.system)?'':'none';
        updateExtrualOptions(el,node.system,node.extrualSystem||autoMapExtrual(node.system));
        const isPuerta=node.system==='puerta';
        document.getElementById('pasoLibreRow').style.display=isPuerta?'':'none';
        document.getElementById('junquilloRow').style.display=el.carpentry==='extrual_s28'?'':'none';
        if(isPuerta){document.getElementById('pasoLibre').value=node.pasoLibre||'';if(parent&&parent.split&&parent.split.dir==='v'){const b=getNodeBounds(el.tree,parent.id),pm=Math.round(el.facadeW*b.w),pl=node.pasoLibre||0;document.getElementById('pasoLibreInfo').textContent=pl>0?`Panel = ${Math.round(pl+K.PUERTA_H_OFFSET)} mm · Hueco = ${pm} mm`:`Hueco = ${pm} mm`;}else document.getElementById('pasoLibreInfo').textContent='Divide con montante vertical';}
    }
    if(parent&&parent.split){const pct=Math.round(parent.split.ratio*100);document.getElementById('splitControls').style.display='';document.getElementById('splitRatio').value=pct;document.getElementById('splitInfo').textContent=`${pct}% · ${100-pct}%`;const pb=getNodeBounds(el.tree,parent.id),pm=parent.split.dir==='v'?Math.round(el.facadeW*pb.w):Math.round(el.facadeH*pb.h),posMm=Math.round(parent.split.ratio*pm);document.getElementById('splitMm').value=posMm;document.getElementById('splitMm').max=pm-10;document.getElementById('splitMm').min=10;}
    else document.getElementById('splitControls').style.display='none';
    const canU=parent&&parent.split&&!parent.split.a.split&&!parent.split.b.split;
    document.getElementById('btnUnsplit').disabled=!canU;
    document.getElementById('btnSplitV').disabled=!isLeaf;
    document.getElementById('btnSplitH').disabled=!isLeaf;
}

function updateExtrualOptions(el,system,currentExtrual){
    const opts=SYSTEM_TO_EXTRUAL[system]||[],sel=document.getElementById('panelExtrualSystem'),row=document.getElementById('extrualSysRow');
    if(!opts.length||el.carpentry!=='extrual_s28'){row.style.display='none';return;}
    row.style.display='';sel.innerHTML=opts.map(([k,n])=>`<option value="${esc(k)}" ${k===currentExtrual?'selected':''}>${esc(n)}</option>`).join('');
}

function updatePanelList(el){let i=1;document.getElementById('panelList').innerHTML=leaves(el.tree).map(({node,w,h})=>{const col=SYS[node.system]||SYS.fijo,pw=Math.round(el.facadeW*w),ph=Math.round(el.facadeH*h),lbl=node.label||`${col.name} ${i++}`,sel=node.id===elSel?' selected':'';return`<div class="panel-list-item${sel}" data-id="${esc(node.id)}" style="border-left-color:${col.stroke}"><strong>${esc(lbl)}</strong><span>${pw}×${ph}</span></div>`;}).join('');document.querySelectorAll('.panel-list-item').forEach(i=>i.addEventListener('click',()=>{elSel=i.dataset.id;render();}));}

function updateLegend(el){const used=[...new Set(leaves(el.tree).map(l=>l.node.system))];document.getElementById('sysLegend').innerHTML=used.map(s=>{const c=SYS[s]||SYS.fijo;return`<div class="sys-legend-item"><span class="sys-swatch" style="background:${c.fill};border-color:${c.stroke}"></span>${c.name}</div>`;}).join('');}

// ══════════════════════════════════════════════════════════
//  RENDER PRINCIPAL
// ══════════════════════════════════════════════════════════
function render(){
    const el=activeEl(),wrap=document.getElementById('canvasWrap');
    if(!el){wrap.innerHTML='<div class="canvas-empty">Añade un elemento para configurarlo</div>';}
    else{wrap.innerHTML=buildSVG(el);wrap.querySelectorAll('.panel-r').forEach(r=>r.addEventListener('click',()=>{elSel=r.dataset.id;render();}));}
    renderSidebar();syncConfigPanel();
}

// ══════════════════════════════════════════════════════════
//  GESTIÓN ELEMENTOS
// ══════════════════════════════════════════════════════════
function selectElement(id){state.activeElementId=id;const el=activeEl();elSel=el?leaves(el.tree)[0]?.node.id||null:null;document.getElementById('bomSection').style.display='none';document.getElementById('btnBom').textContent='▦ Generar descompuesto EXTRUAL S28';document.getElementById('btnBom').onclick=showBom;render();}
function addElement(key){const el=mkElement(key);state.elements.push(el);selectElement(el.id);closeTemplateModal();}
function removeElement(id){state.elements=state.elements.filter(e=>e.id!==id);if(state.activeElementId===id){state.activeElementId=state.elements.length?state.elements[0].id:null;const el=activeEl();elSel=el?leaves(el.tree)[0]?.node.id||null:null;}render();}

// ══════════════════════════════════════════════════════════
//  MODAL PLANTILLAS
// ══════════════════════════════════════════════════════════
function openTemplateModal(){
    const groups={};for(const[k,t]of Object.entries(TEMPLATES)){if(!groups[t.group])groups[t.group]=[];groups[t.group].push({k,t});}
    document.getElementById('tplGroups').innerHTML=Object.entries(groups).map(([g,items])=>`<div class="tpl-group"><h3 class="tpl-group-title">${esc(g)}</h3><div class="tpl-grid">${items.map(({k,t})=>`<div class="tpl-card" data-key="${esc(k)}"><svg viewBox="0 0 24 32" class="tpl-icon">${t.icon}</svg><span>${esc(t.name)}</span></div>`).join('')}</div></div>`).join('');
    document.querySelectorAll('.tpl-card').forEach(c=>c.addEventListener('click',()=>addElement(c.dataset.key)));
    document.getElementById('templateModal').style.display='flex';
}
function closeTemplateModal(){document.getElementById('templateModal').style.display='none';}

// ══════════════════════════════════════════════════════════
//  GUARDAR
// ══════════════════════════════════════════════════════════
async function saveQuote(){
    const clientName=document.getElementById('clientName').value.trim();
    if(!clientName){alert('Introduce el nombre del cliente / proyecto.');document.getElementById('clientName').focus();return;}
    const svgEl=document.getElementById('mainSvg'),svgStr=svgEl?svgEl.outerHTML:'';
    const totals=state.elements.reduce((acc,e)=>{const c=calcElement(e);acc.total+=c.total;acc.alMl+=c.alMl;acc.glassM2+=c.glassM2;return acc;},{total:0,alMl:0,glassM2:0});
    const body=new FormData();
    body.append('client_name',clientName);
    body.append('elements_json',JSON.stringify(state.elements));
    body.append('drawing_svg',svgStr);
    body.append('total',totals.total.toFixed(2));
    body.append('aluminum_ml',totals.alMl.toFixed(3));
    body.append('glass_m2',totals.glassM2.toFixed(3));
    const btn=document.getElementById('btnSave');btn.disabled=true;btn.textContent='Guardando…';
    try{const r=await fetch('save_designer.php',{method:'POST',body});if(r.redirected){window.location.href=r.url;return;}const text=await r.text();if(r.ok)window.location.href=text;else alert('Error: '+text);}
    catch(e){alert('Error de red: '+e.message);}
    finally{btn.disabled=false;btn.textContent='Guardar presupuesto';}
}

// ══════════════════════════════════════════════════════════
//  EVENTOS
// ══════════════════════════════════════════════════════════
document.getElementById('btnAddEl').addEventListener('click',openTemplateModal);
document.getElementById('btnSave').addEventListener('click',saveQuote);
document.getElementById('btnDeleteEl').addEventListener('click',()=>{if(state.activeElementId&&confirm('¿Eliminar este elemento?'))removeElement(state.activeElementId);});
document.getElementById('templateModal').addEventListener('click',e=>{if(e.target===document.getElementById('templateModal'))closeTemplateModal();});
document.getElementById('cfgW').addEventListener('input',e=>{const el=activeEl();if(!el)return;el.facadeW=Math.max(200,parseFloat(e.target.value)||200);render();});
document.getElementById('cfgH').addEventListener('input',e=>{const el=activeEl();if(!el)return;el.facadeH=Math.max(200,parseFloat(e.target.value)||200);render();});
document.getElementById('cfgQty').addEventListener('input',e=>{const el=activeEl();if(!el)return;el.qty=Math.max(1,parseInt(e.target.value)||1);render();});
document.getElementById('cfgName').addEventListener('input',e=>{const el=activeEl();if(!el)return;el.name=e.target.value;renderSidebar();});
document.getElementById('cfgCarpentry').addEventListener('change',e=>{const el=activeEl();if(!el)return;el.carpentry=e.target.value;render();});
document.getElementById('cfgCarpentryRef').addEventListener('input',e=>{const el=activeEl();if(!el)return;el.carpentryRef=e.target.value;});
document.getElementById('cfgColor').addEventListener('change',e=>{const el=activeEl();if(!el)return;const p=e.target.value.split('|');el.colorRal=p[0];el.colorHex=p[1]||'#f1f0eb';el.colorName=e.target.options[e.target.selectedIndex].text;document.getElementById('cfgColorSwatch').style.background=el.colorHex;render();});
document.getElementById('cfgColorCustom').addEventListener('input',e=>{const el=activeEl();if(!el)return;el.colorName=e.target.value;});
document.getElementById('cfgCutType').addEventListener('change',e=>{const el=activeEl();if(!el)return;el.cutType=e.target.value;document.getElementById('cfgTapajuntasRow').style.display=e.target.value==='recto'?'':'none';render();});
document.getElementById('cfgTapajuntas').addEventListener('change',e=>{const el=activeEl();if(!el)return;el.tapajuntas=parseInt(e.target.value)||0;render();});
document.getElementById('cfgPricingMode').addEventListener('change',e=>{const el=activeEl();if(!el)return;el.pricingMode=e.target.value;document.getElementById('pricingComprada').style.display=e.target.value==='comprada'?'':'none';document.getElementById('pricingFabricada').style.display=e.target.value==='fabricada'?'':'none';updatePriceSummary(el);renderSidebar();});
[['cfgPurchasedCost','purchasedCost'],['cfgCommercialMargin','commercialMarginPct'],['cfgAlPrice','alPriceMl'],['cfgLabor','laborCost'],['cfgMargin','marginPct'],['cfgExtra','extraCost'],['cfgIva','ivaPct']].forEach(([id,prop])=>document.getElementById(id).addEventListener('input',e=>{const el=activeEl();if(!el)return;el[prop]=parseFloat(e.target.value)||0;updatePriceSummary(el);renderSidebar();}));
document.getElementById('btnSplitV').addEventListener('click',()=>splitNode('v'));
document.getElementById('btnSplitH').addEventListener('click',()=>splitNode('h'));
document.getElementById('btnUnsplit').addEventListener('click',unsplitParent);
document.getElementById('panelSystem').addEventListener('change',e=>{const el=activeEl();if(!el)return;const n=find(el.tree,elSel);if(!n||n.split)return;n.system=e.target.value;n.extrualSystem=autoMapExtrual(e.target.value);if(e.target.value!=='puerta')n.pasoLibre=null;render();});
document.getElementById('panelOpening').addEventListener('change',e=>{const el=activeEl();if(!el)return;const n=find(el.tree,elSel);if(n&&!n.split){n.opening=e.target.value;render();}});
document.getElementById('panelExtrualSystem').addEventListener('change',e=>{const el=activeEl();if(!el)return;const n=find(el.tree,elSel);if(n&&!n.split){n.extrualSystem=e.target.value;render();}});
document.getElementById('panelGlassType').addEventListener('change',e=>{const el=activeEl();if(!el)return;const n=find(el.tree,elSel);if(!n||n.split)return;n.glassType=e.target.value;n.glassThick=GLASS_THICK_MAP[e.target.value]||20;render();});
document.getElementById('panelJunquillo').addEventListener('change',e=>{const el=activeEl();if(!el)return;const n=find(el.tree,elSel);if(n&&!n.split){n.junquilloType=e.target.value;render();}});
document.getElementById('panelLabel').addEventListener('input',e=>{const el=activeEl();if(!el)return;const n=find(el.tree,elSel);if(n&&!n.split){n.label=e.target.value;render();}});
document.getElementById('pasoLibre').addEventListener('input',e=>{const el=activeEl();if(!el)return;const n=find(el.tree,elSel);if(!n||n.split)return;const pl=parseInt(e.target.value)||0;n.pasoLibre=pl>0?pl:null;if(pl>0){const parent=findParent(el.tree,elSel);if(parent&&parent.split&&parent.split.dir==='v'){const b=getNodeBounds(el.tree,parent.id),pm=Math.round(el.facadeW*b.w),isA=parent.split.a.id===elSel;if(pm>0)parent.split.ratio=Math.max(.05,Math.min(.95,isA?(pl+K.PUERTA_H_OFFSET)/pm:1-(pl+K.PUERTA_H_OFFSET)/pm));}}render();});
document.getElementById('splitRatio').addEventListener('input',e=>{const el=activeEl();if(!el)return;const p=findParent(el.tree,elSel);if(p&&p.split){p.split.ratio=parseInt(e.target.value)/100;render();}});
document.getElementById('splitMm').addEventListener('input',e=>{const el=activeEl();if(!el)return;const p=findParent(el.tree,elSel);if(!p||!p.split)return;const b=getNodeBounds(el.tree,p.id),pm=p.split.dir==='v'?Math.round(el.facadeW*b.w):Math.round(el.facadeH*b.h);p.split.ratio=Math.max(.05,Math.min(.95,(parseInt(e.target.value)||0)/pm));render();});
document.getElementById('btnBom').addEventListener('click',showBom);
document.getElementById('clientName').addEventListener('input',e=>{state.clientName=e.target.value;});
document.getElementById('cfgPersiana').addEventListener('change',e=>{const el=activeEl();if(!el)return;el.persiana=e.target.value;const hp=e.target.value!=='ninguna';document.getElementById('cfgPersianaOpts').style.display=hp?'':'none';document.getElementById('cfgPersianaForroRow').style.display=e.target.value==='registro'?'':'none';document.getElementById('cfgPersianaInfo').textContent=hp?`Cajón ${el.persianaAlto}mm sobre el marco (${e.target.value==='registro'?'apertura mantenimiento':'integrado'})`:'';render();});
document.getElementById('cfgPersianaAlto').addEventListener('input',e=>{const el=activeEl();if(!el)return;el.persianaAlto=Math.max(100,parseInt(e.target.value)||180);document.getElementById('cfgPersianaInfo').textContent=el.persiana!=='ninguna'?`Cajón ${el.persianaAlto}mm sobre el marco (${el.persiana==='registro'?'apertura mantenimiento':'integrado'})`:'';render();});
document.getElementById('cfgPersianaForro').addEventListener('change',e=>{const el=activeEl();if(!el)return;el.persianaForroMm=parseInt(e.target.value)||60;});
document.getElementById('btnDupeEl').addEventListener('click',()=>{const el=activeEl();if(!el)return;const copy=JSON.parse(JSON.stringify(el));copy.id=uid();copy.name=el.name+' (copia)';(function reId(n){n.id=uid();if(n.split){reId(n.split.a);reId(n.split.b);}})(copy.tree);copy.selModuleId=null;state.elements.push(copy);selectElement(copy.id);});

render();
</script>
</body>
</html>
