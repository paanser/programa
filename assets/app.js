// ── UTILIDADES DE COLOR (nivel módulo, accesibles en todo el archivo) ──────────
const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

const hexToRgb = (hex) => {
    const normalized = (hex ?? '').trim().replace('#', '');
    if (!/^[0-9a-fA-F]{6}$/.test(normalized)) {
        return { r: 242, g: 239, b: 232 };
    }
    return {
        r: Number.parseInt(normalized.slice(0, 2), 16),
        g: Number.parseInt(normalized.slice(2, 4), 16),
        b: Number.parseInt(normalized.slice(4, 6), 16),
    };
};

const rgbToHex = ({ r, g, b }) => `#${[r, g, b]
    .map((v) => clamp(Math.round(v), 0, 255).toString(16).padStart(2, '0'))
    .join('')}`;

const mixColor = (hex, targetHex, amount) => {
    const base = hexToRgb(hex);
    const target = hexToRgb(targetHex);
    return rgbToHex({
        r: base.r + ((target.r - base.r) * amount),
        g: base.g + ((target.g - base.g) * amount),
        b: base.b + ((target.b - base.b) * amount),
    });
};
// ─────────────────────────────────────────────────────────────────────────────

const form = document.getElementById('quoteForm');

if (form) {
    const uiText = window.APP_UI_TEXT || {};
    const drawingWrap = document.getElementById('drawingWrap');
    const totalsBox = document.getElementById('totalsBox');
    const drawingSvgInput = document.getElementById('drawingSvg');
    const configJsonInput = document.getElementById('configJson');
    const quoteItemsJsonInput = document.getElementById('quoteItemsJson');
    const quoteItemsList = document.getElementById('quoteItemsList');
    const glassSummaryBox = document.getElementById('glassSummaryBox');
    const profilePreviewSwatch = document.getElementById('profilePreviewSwatch');
    const profilePreviewLabel = document.getElementById('profilePreviewLabel');
    const addItemButton = document.getElementById('addItemButton');

    const fields = {
        systemType: document.getElementById('systemType'),
        openingType: document.getElementById('openingType'),
        carpentryModel: document.getElementById('carpentryModel'),
        carpentrySeriesSelect: document.getElementById('carpentrySeriesSelect'),
        carpentryReference: document.getElementById('carpentryReference'),
        trimSize: document.getElementById('trimSize'),
        tiltTurnConfig: document.getElementById('tiltTurnConfig'),
        tiltTurnLeaf: document.getElementById('tiltTurnLeaf'),
        frameCutType: document.getElementById('frameCutType'),
        glassType: document.getElementById('glassType'),
        glassDescription: document.getElementById('glassDescription'),
        glassWidthMm: document.getElementById('glassWidthMm'),
        glassHeightMm: document.getElementById('glassHeightMm'),
        glassPanels: document.getElementById('glassPanels'),
        suggestGlassButton: document.getElementById('suggestGlassButton'),
        profileColorPreset: document.getElementById('profileColorPreset'),
        profileColorHex: document.getElementById('profileColorHex'),
        profileColorName: document.getElementById('profileColorName'),
        pricingMode: document.getElementById('pricingMode'),
        isFactoryFinished: document.getElementById('isFactoryFinished'),
        purchasedUnitCost: document.getElementById('purchasedUnitCost'),
        widthMm: document.getElementById('widthMm'),
        heightMm: document.getElementById('heightMm'),
        leaves: document.getElementById('leaves'),
        priceAl: document.getElementById('priceAl'),
        priceGlass: document.getElementById('priceGlass'),
        labor: document.getElementById('labor'),
        internalExtraCost: document.getElementById('internalExtraCost'),
        margin: document.getElementById('margin'),
        commercialMargin: document.getElementById('commercialMargin'),
        iva: document.getElementById('iva'),
        quantity: form.querySelector('input[name="quantity"]'),
    };

    const numberValue = (input, fallback = 0) => {
        const parsed = Number.parseFloat(input?.value ?? '');
        return Number.isFinite(parsed) ? parsed : fallback;
    };

    const integerValue = (input, fallback = 0) => {
        const parsed = Number.parseInt(input?.value ?? '', 10);
        return Number.isFinite(parsed) ? parsed : fallback;
    };

    const roundMoney = (value) => Number(value.toFixed(2));
    const roundMetric = (value) => Number(value.toFixed(3));
    const formatMoney = (value) => `${Number(value).toFixed(2).replace('.', ',')} EUR`;
    const text = (key, fallback) => uiText[key] || fallback;
    const customColorPrefix = () => `${text('customColorLabel', 'Personalizado')} `;

    let glassPriceWasSuggested = true;
    let lastSuggestedGlassDescription = fields.glassDescription?.value.trim() || '';
    let quoteItems = [];
    let selectedItemId = null;
    let suppressSync = false;
    let itemSequence = 0;

    const formatHexLabel = (hex) => (hex ?? '').toUpperCase();
    const createItemId = () => {
        itemSequence += 1;
        return `item-${Date.now()}-${itemSequence}`;
    };

    const escapeSvgText = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const getProfilePalette = (colorHex = fields.profileColorHex?.value || '#f2efe8') => ({
        base: colorHex,
        light: mixColor(colorHex, '#ffffff', 0.55),
        mid: mixColor(colorHex, '#c9cdd2', 0.18),
        dark: mixColor(colorHex, '#2f343a', 0.22),
        shadow: mixColor(colorHex, '#000000', 0.28),
    });

    const getGlassPalette = () => ({
        fill: '#d8f3f4',
        stroke: '#7aa9ad',
        edge: '#edf9fa',
    });

    const glassPriceCatalog = window.GLASS_PRICE_CATALOG || {};
    const glassTypePresets = {
        camara_4_12_4: 'Camara 4/12/4',
        camara_4_16_4: 'Camara 4/16/4',
        camara_4_4_12_4: 'Camara 4+4/12/4',
        camara_4_4_16_4: 'Camara 4+4/16/4',
        camara_6_12_6: 'Camara 6/12/6',
        camara_6_16_6: 'Camara 6/16/6',
        laminar_3_3: 'Laminar 3+3',
        laminar_4_4: 'Laminar 4+4',
        laminar_5_5: 'Laminar 5+5',
        laminar_6_6: 'Laminar 6+6',
        bajo_emisivo_4_16_4: 'Bajo emisivo 4/16/4',
        bajo_emisivo_4_4_16_4: 'Bajo emisivo 4+4/16/4',
        bajo_emisivo_6_16_4: 'Bajo emisivo 6/16/4',
        control_solar_4_16_4: 'Control solar 4/16/4',
        control_solar_4_4_16_4: 'Control solar 4+4/16/4',
        control_solar_6_16_6: 'Control solar 6/16/6',
        acustico_4_4_16_4: 'Acustico 4+4/16/4',
        acustico_5_5_16_6: 'Acustico 5+5/16/6',
        acustico_6_6_16_6: 'Acustico 6+6/16/6',
        templado_6: 'Templado 6 mm',
        templado_8: 'Templado 8 mm',
        templado_10: 'Templado 10 mm',
        triple_4_10_4_10_4: 'Triple 4/10/4/10/4',
        triple_4_12_4_12_4: 'Triple 4/12/4/12/4',
        monolitico_4: 'Monolitico 4 mm',
        monolitico_6: 'Monolitico 6 mm',
        otro: '',
    };

    const syncProfileColorInputs = ({ preserveName = false } = {}) => {
        const preset = fields.profileColorPreset;
        const hexInput = fields.profileColorHex;
        const nameInput = fields.profileColorName;
        const selectedOption = preset?.selectedOptions?.[0];

        if (!preset || !hexInput || !nameInput) {
            return;
        }

        if (preset.value !== 'custom') {
            hexInput.value = preset.value;
            if (!preserveName && selectedOption?.dataset.label) {
                nameInput.value = selectedOption.dataset.label;
            }
            return;
        }

        if (!preserveName && !nameInput.value.trim()) {
            nameInput.value = `${customColorPrefix()}${formatHexLabel(hexInput.value)}`;
        }
    };

    const syncPresetFromHex = () => {
        const preset = fields.profileColorPreset;
        const hexInput = fields.profileColorHex;
        const nameInput = fields.profileColorName;

        if (!preset || !hexInput || !nameInput) {
            return;
        }

        const match = Array.from(preset.options).find((option) => option.value.toLowerCase() === hexInput.value.toLowerCase());
        if (match) {
            preset.value = match.value;
            if (!nameInput.value.trim() || nameInput.value.startsWith(customColorPrefix())) {
                nameInput.value = match.dataset.label ?? nameInput.value;
            }
            return;
        }

        preset.value = 'custom';
        if (!nameInput.value.trim() || nameInput.value.startsWith(customColorPrefix())) {
            nameInput.value = `${customColorPrefix()}${formatHexLabel(hexInput.value)}`;
        }
    };

    const updatePricingModeUI = () => {
        const pricingMode = fields.pricingMode?.value || 'fabricada';
        const purchasedFields = document.querySelectorAll('.purchased-cost-field, .commercial-margin-field');
        const fabricatedFields = document.querySelectorAll('.fabricated-cost-field');

        purchasedFields.forEach((element) => {
            element.classList.toggle('is-hidden', pricingMode !== 'comprada');
        });

        fabricatedFields.forEach((element) => {
            element.classList.toggle('is-hidden', pricingMode === 'comprada');
        });
    };

    const getSuggestedGlassMeasures = () => {
        const widthMm = Math.max(300, integerValue(fields.widthMm, 1500));
        const heightMm = Math.max(300, integerValue(fields.heightMm, 1200));
        const leaves = Math.min(6, Math.max(1, integerValue(fields.leaves, 2)));

        return {
            glassWidthMm: Math.max(1, Math.round((widthMm / leaves) - 80)),
            glassHeightMm: Math.max(1, Math.round(heightMm - 110)),
            glassPanels: leaves,
        };
    };

    const applySuggestedGlassMeasures = () => {
        const suggestion = getSuggestedGlassMeasures();

        if (fields.glassWidthMm) {
            fields.glassWidthMm.value = String(suggestion.glassWidthMm);
        }
        if (fields.glassHeightMm) {
            fields.glassHeightMm.value = String(suggestion.glassHeightMm);
        }
        if (fields.glassPanels) {
            fields.glassPanels.value = String(suggestion.glassPanels);
        }
    };

    const syncGlassDescriptionFromType = ({ force = false } = {}) => {
        const typeKey = fields.glassType?.value || '';
        const preset = glassTypePresets[typeKey] ?? '';
        const currentValue = fields.glassDescription?.value.trim() || '';

        if (!fields.glassDescription) {
            return;
        }

        if (force || currentValue === '' || currentValue === lastSuggestedGlassDescription) {
            fields.glassDescription.value = preset;
            lastSuggestedGlassDescription = preset;
        }
    };

    const syncGlassPriceFromType = ({ force = false } = {}) => {
        const typeKey = fields.glassType?.value || '';
        const catalogPrice = Number(glassPriceCatalog[typeKey]);

        if (!fields.priceGlass || !Number.isFinite(catalogPrice)) {
            return;
        }

        if (force || glassPriceWasSuggested || fields.priceGlass.value.trim() === '') {
            fields.priceGlass.value = catalogPrice.toFixed(2);
            glassPriceWasSuggested = true;
        }
    };

    const updateSystemDetailsUI = () => {
        const systemType = fields.systemType?.value || 'corredera';
        let leaves = Math.max(1, integerValue(fields.leaves, 2));

        if (systemType === 'fijo') {
            leaves = 1;
        }

        if (systemType === 'oscilobatiente') {
            leaves = Math.min(2, leaves);
        }

        if (fields.leaves) {
            fields.leaves.value = String(leaves);
            fields.leaves.max = systemType === 'oscilobatiente' ? '2' : '6';
        }

        if (fields.tiltTurnConfig) {
            fields.tiltTurnConfig.classList.toggle('is-hidden', systemType !== 'oscilobatiente');
        }

        if (!fields.tiltTurnLeaf) {
            return;
        }

        const rightOption = Array.from(fields.tiltTurnLeaf.options).find((option) => option.value === 'derecha');
        const unicaOption = Array.from(fields.tiltTurnLeaf.options).find((option) => option.value === 'unica');
        if (rightOption) {
            rightOption.hidden = systemType !== 'oscilobatiente' || leaves === 1;
        }
        if (unicaOption) {
            unicaOption.hidden = true;
        }

        if (systemType !== 'oscilobatiente') {
            fields.tiltTurnLeaf.value = '';
            fields.tiltTurnLeaf.disabled = true;
            return;
        }

        if (leaves === 1) {
            if (unicaOption) {
                unicaOption.hidden = false;
            }
            fields.tiltTurnLeaf.value = 'unica';
            fields.tiltTurnLeaf.disabled = true;
            return;
        }

        fields.tiltTurnLeaf.disabled = false;
        if (fields.tiltTurnLeaf.value !== 'derecha') {
            fields.tiltTurnLeaf.value = 'izquierda';
        }
    };

    const getTiltTurnLeafIndex = (quote) => {
        if (quote.systemType !== 'oscilobatiente') {
            return -1;
        }

        if (quote.leaves <= 1) {
            return 0;
        }

        return quote.tiltTurnLeaf === 'derecha' ? quote.leaves - 1 : 0;
    };

    const getSlidingDirection = (openingType, index, leaves) => {
        if (openingType === 'central') {
            return index < (leaves / 2) ? 1 : -1;
        }

        return openingType === 'derecha' ? 1 : -1;
    };

    const getCasementHingeSide = (openingType, index, leaves) => {
        if (openingType === 'central') {
            return index < (leaves / 2) ? 'left' : 'right';
        }

        return openingType === 'derecha' ? 'left' : 'right';
    };

    const getCasementHandleLeafIndex = (quote) => {
        if (quote.systemType === 'oscilobatiente') {
            return getTiltTurnLeafIndex(quote);
        }

        if (quote.openingType === 'derecha') {
            return quote.leaves - 1;
        }

        return 0;
    };

    const getCasementHandleSide = (quote) => {
        if (quote.openingType === 'central') {
            return 'right';
        }

        return quote.openingType === 'derecha' ? 'right' : 'left';
    };

    const buildArrowTag = (cx, cy, direction, label) => {
        const width = 46;
        const height = 20;
        const body = 28;
        const halfHeight = height / 2;
        const tailX = direction === 1 ? cx - (width / 2) : cx + (width / 2);
        const bodyX = tailX + (direction * body);
        const tipX = tailX + (direction * width);
        const points = [
            `${tailX},${cy - halfHeight}`,
            `${bodyX},${cy - halfHeight}`,
            `${tipX},${cy}`,
            `${bodyX},${cy + halfHeight}`,
            `${tailX},${cy + halfHeight}`,
        ];

        return `
            <polygon points="${points.join(' ')}" class="marker-tag" />
            <text x="${cx}" y="${cy + 5}" text-anchor="middle" class="marker-label">${label}</text>
        `;
    };

    const buildSlidingMarker = (leaf, index, quote) => {
        const cx = leaf.x + (leaf.width / 2);
        const cy = leaf.y + (leaf.height / 2);
        const direction = getSlidingDirection(quote.openingType, index, quote.leaves);
        return buildArrowTag(cx, cy, direction, index + 1);
    };

    const buildArrowHead = (x, y, direction, size = 10) => `
        <path d="M ${x} ${y} l ${direction === 1 ? -size : size} ${Math.round(size * 0.45)} l ${direction === 1 ? 2 : -2} ${Math.round(size * 0.8)}" class="opening-line-strong" />
    `;

    const buildCasementMarker = (leaf, index, quote, includeTilt = false) => {
        const hingeSide = getCasementHingeSide(quote.openingType, index, quote.leaves);
        const direction = hingeSide === 'left' ? 1 : -1;
        const hingeX = hingeSide === 'left' ? leaf.x + 12 : leaf.x + leaf.width - 12;
        const hingeTopY = leaf.y + 28;
        const hingeBottomY = leaf.y + leaf.height - 28;
        const endX = direction === 1 ? leaf.x + leaf.width - 24 : leaf.x + 24;
        const endY = leaf.y + 34;
        const startY = leaf.y + leaf.height - 18;
        const markerCx = leaf.x + (leaf.width / 2);
        const markerCy = leaf.y + (leaf.height / 2) + 2;
        const tiltY = leaf.y + 24;

        return `
            <line x1="${hingeX}" y1="${hingeTopY}" x2="${hingeX}" y2="${hingeBottomY}" class="hinge-line" />
            <path d="M ${hingeX} ${startY} Q ${markerCx} ${markerCy} ${endX} ${endY}" class="swing-arc" />
            <line x1="${hingeX}" y1="${startY}" x2="${endX}" y2="${endY}" class="opening-line-strong" />
            ${buildArrowHead(endX, endY, direction)}
            ${includeTilt ? `
                <path d="M ${markerCx - 22} ${tiltY} L ${markerCx} ${tiltY - 14} L ${markerCx + 22} ${tiltY}" class="tilt-mark" />
                <line x1="${markerCx - 18}" y1="${tiltY - 4}" x2="${markerCx + 18}" y2="${tiltY - 4}" class="tilt-mark" />
            ` : ''}
        `;
    };

    const getTrimOffset = (trimSize) => {
        if (trimSize >= 80) {
            return 8;
        }
        if (trimSize >= 60) {
            return 6;
        }
        if (trimSize >= 40) {
            return 4;
        }
        return 0;
    };

    const buildMiterMarks = (x, y, width, height, inset = 10) => `
        <line x1="${x}" y1="${y + inset}" x2="${x + inset}" y2="${y}" class="miter-line" />
        <line x1="${x + width - inset}" y1="${y}" x2="${x + width}" y2="${y + inset}" class="miter-line" />
        <line x1="${x}" y1="${y + height - inset}" x2="${x + inset}" y2="${y + height}" class="miter-line" />
        <line x1="${x + width - inset}" y1="${y + height}" x2="${x + width}" y2="${y + height - inset}" class="miter-line" />
    `;

    const getLeafGeometry = (quote, frame) => {
        if (quote.systemType === 'fijo') {
            return {
                geometry: [{
                    x: frame.innerX,
                    y: frame.innerY,
                    width: frame.innerWidth,
                    height: frame.innerHeight,
                }],
                barWidth: 0,
            };
        }

        const barWidth = quote.systemType === 'corredera' ? 12 : 10;
        const slotWidth = (frame.innerWidth - (barWidth * Math.max(0, quote.leaves - 1))) / quote.leaves;
        const geometry = [];

        for (let index = 0; index < quote.leaves; index += 1) {
            geometry.push({
                x: frame.innerX + (index * (slotWidth + barWidth)),
                y: frame.innerY,
                width: slotWidth,
                height: frame.innerHeight,
            });
        }

        return { geometry, barWidth };
    };

    const buildHandles = (leaf, side) => {
        const handleX = side === 'left' ? leaf.x + leaf.width - 8 : leaf.x + 4;
        const handleY = leaf.y + (leaf.height / 2) - 10;

        return `
            <rect x="${handleX}" y="${handleY}" width="4" height="20" rx="1.5" class="handle" />
            <line x1="${handleX + 2}" y1="${handleY + 4}" x2="${handleX + 2}" y2="${handleY + 16}" class="handle-line" />
        `;
    };

    const calculateQuote = () => {
        const widthMm = Math.max(300, integerValue(fields.widthMm, 1500));
        const heightMm = Math.max(300, integerValue(fields.heightMm, 1200));
        let leaves = Math.min(6, Math.max(1, integerValue(fields.leaves, 2)));
        const quantity = Math.max(1, integerValue(fields.quantity, 1));
        const systemType = fields.systemType?.value || 'corredera';
        if (systemType === 'fijo') {
            leaves = 1;
        } else if (systemType === 'oscilobatiente') {
            leaves = Math.min(2, leaves);
        }
        const tiltTurnLeaf = systemType === 'oscilobatiente'
            ? (leaves === 1 ? 'unica' : (fields.tiltTurnLeaf?.value === 'derecha' ? 'derecha' : 'izquierda'))
            : '';
        const tiltTurnLeafLabel = tiltTurnLeaf === 'derecha'
            ? text('right', 'Derecha')
            : tiltTurnLeaf === 'unica'
                ? text('onlyLeaf', 'Unica')
                : text('left', 'Izquierda');

        const glassWidthMm = Math.max(1, integerValue(fields.glassWidthMm, Math.max(1, Math.round(widthMm / Math.max(1, leaves)))));
        const glassHeightMm = Math.max(1, integerValue(fields.glassHeightMm, Math.max(1, heightMm - 100)));
        const glassPanels = Math.max(1, integerValue(fields.glassPanels, leaves));
        const aluminumPriceMl = Math.max(0, numberValue(fields.priceAl, 0));
        const glassPriceM2 = Math.max(0, numberValue(fields.priceGlass, 0));
        const laborCost = Math.max(0, numberValue(fields.labor, 0));
        const internalExtraCost = Math.max(0, numberValue(fields.internalExtraCost, 0));
        const marginPct = Math.max(0, numberValue(fields.margin, 0));
        const commercialMarginPct = Math.max(0, numberValue(fields.commercialMargin, marginPct));
        const ivaPct = Math.max(0, numberValue(fields.iva, 0));
        const purchasedUnitCost = Math.max(0, numberValue(fields.purchasedUnitCost, 0));
        const pricingMode = fields.pricingMode?.value || 'fabricada';

        const widthM = widthMm / 1000;
        const heightM = heightMm / 1000;
        const carpentrySeriesValue = fields.carpentrySeriesSelect?.value || '';
        const carpentrySeriesLabel = fields.carpentrySeriesSelect?.selectedOptions?.[0]?.textContent?.trim() || '';

        // ── COMPOSITE: calcular por panel del diseñador ──
        var dwState = (typeof window.DesignerWidget !== 'undefined' && window.DesignerWidget.getState) ? window.DesignerWidget.getState() : null;

        function flattenTreePanels(node, x, y, w, h) {
            x = x || 0; y = y || 0; w = w || 1; h = h || 1;
            if (!node) return [];
            if (!node.split) return [{ node: node, x: x, y: y, w: w, h: h }];
            var sp = node.split;
            if (sp.dir === 'v') return flattenTreePanels(sp.a, x, y, w * sp.ratio, h).concat(flattenTreePanels(sp.b, x + w * sp.ratio, y, w * (1 - sp.ratio), h));
            return flattenTreePanels(sp.a, x, y, w, h * sp.ratio).concat(flattenTreePanels(sp.b, x, y + h * sp.ratio, w, h * (1 - sp.ratio)));
        }

        var panelTypes = [];
        var compositeLabel = systemTypeLabel;
        var isComposite = false;
        var compAlMl = 0, compGlassM2 = 0;

        if (dwState && dwState.tree && dwState.tree.split) {
            var panels = flattenTreePanels(dwState.tree);
            panelTypes = panels.map(function (p) { return p.node.system || 'fijo'; });
            var uniqueTypes = [];
            panelTypes.forEach(function (t) { if (uniqueTypes.indexOf(t) === -1) uniqueTypes.push(t); });
            if (uniqueTypes.length > 1) {
                isComposite = true;
                var typeNames = { fijo: 'Fijo', puerta: 'Puerta', practicable: 'Practicable', oscilobatiente: 'Oscilo', corredera: 'Corredera', abatible: 'Abatible', tubo: 'Tubo' };
                compositeLabel = panelTypes.map(function (t) { return typeNames[t] || t; }).join(' + ');

                // Frame compartido (perimetro total)
                var frameMl = (widthM * 2) + (heightM * 2);

                // Calcular por panel
                panels.forEach(function (panel) {
                    var sys = panel.node.system || 'fijo';
                    var pW = Math.max(100, widthMm * panel.w);
                    var pH = Math.max(100, heightMm * panel.h * ((panel.node.heightPct || 100) / 100));
                    var pWM = pW / 1000, pHM = pH / 1000;
                    var leafMl = 0;
                    if (sys === 'fijo' || sys === 'tubo') {
                        leafMl = 0;
                    } else if (sys === 'puerta') {
                        leafMl = (pWM * 1.5 + pHM * 2) * 1.2;
                    } else {
                        leafMl = (pWM * 2 + pHM * 2) * 0.35;
                    }
                    compAlMl += leafMl;
                    compGlassM2 += (pWM * pHM);
                });

                compAlMl = roundMetric((frameMl + compAlMl) * quantity);
                compGlassM2 = roundMetric(compGlassM2 * quantity);
            }
        }

        // ── CALCULO ESTANDAR o COMPOSITE ──
        var aluminumMl, glassPieceAreaM2, glassM2;
        if (isComposite) {
            aluminumMl = compAlMl;
            glassM2 = compGlassM2;
            glassPieceAreaM2 = roundMetric(glassM2 / quantity);
        } else {
            var fMl = (widthM * 2) + (heightM * 2);
            var dMl = Math.max(0, leaves - 1) * heightM;
            var lpMl = leaves * (((widthM / leaves) * 2) + (heightM * 2));
            aluminumMl = roundMetric(((fMl + dMl) + (lpMl * 0.35)) * quantity);
            glassPieceAreaM2 = roundMetric((glassWidthMm / 1000) * (glassHeightMm / 1000));
            glassM2 = roundMetric(glassPieceAreaM2 * glassPanels * quantity);
        }
        const glassCost = roundMoney(glassM2 * glassPriceM2);
        const fabricatedBase = (aluminumMl * aluminumPriceMl) + glassCost + laborCost + internalExtraCost;
        const purchasedBase = (purchasedUnitCost * quantity) + internalExtraCost;
        const subtotal = roundMoney(pricingMode === 'comprada' ? purchasedBase : fabricatedBase);
        const appliedMarginPct = pricingMode === 'comprada' ? commercialMarginPct : marginPct;
        const marginAmount = roundMoney(subtotal * (appliedMarginPct / 100));
        const taxableBase = roundMoney(subtotal + marginAmount);
        const ivaAmount = roundMoney(taxableBase * (ivaPct / 100));
        const total = roundMoney(taxableBase + ivaAmount);

        return {
            systemType,
            isComposite: isComposite,
            compositeLabel: compositeLabel,
            panels: isComposite ? (function getPanelData(t, w, h) {
                if (!t || !t.split) return [];
                return flattenTreePanels(t).map(function (p) {
                    var pw = Math.round(w * p.w);
                    var ph = Math.round(h * p.h * ((p.node.heightPct || 100) / 100));
                    return { system: p.node.system || 'fijo', label: p.node.label || '', widthMm: pw, heightMm: ph };
                });
            })(dwState.tree, widthMm, heightMm) : [],
            systemTypeLabel: fields.systemType?.selectedOptions?.[0]?.textContent?.trim() || systemType,
            openingType: fields.openingType?.value || 'izquierda',
            openingTypeLabel: fields.openingType?.selectedOptions?.[0]?.textContent?.trim() || '',
            carpentryModelValue: fields.carpentryModel?.value || '',
            carpentryModel: fields.carpentryModel?.selectedOptions?.[0]?.textContent?.trim() || '',
            carpentrySeriesValue,
            carpentrySeriesLabel,
            carpentryReference: fields.carpentryReference?.value.trim() || '',
            trimSize: integerValue(fields.trimSize, 0),
            tiltTurnLeaf,
            tiltTurnLeafLabel,
            frameCutType: systemType === 'fijo' ? 'mitered' : (fields.frameCutType?.value || 'recto'),
            glassTypeValue: fields.glassType?.value || '',
            glassType: fields.glassType?.selectedOptions?.[0]?.textContent?.trim() || '',
            glassDescription: fields.glassDescription?.value.trim() || '',
            profileColor: fields.profileColorName?.value.trim() || `${customColorPrefix()}${formatHexLabel(fields.profileColorHex?.value || '#F2EFE8')}`,
            profileColorHex: fields.profileColorHex?.value || '#f2efe8',
            pricingMode,
            isFactoryFinished: !!fields.isFactoryFinished?.checked,
            purchasedUnitCost,
            internalExtraCost,
            commercialMarginPct,
            aluminumPriceMl,
            laborCost,
            marginPct,
            ivaPct,
            widthMm,
            heightMm,
            glassWidthMm,
            glassHeightMm,
            glassPanels,
            glassPieceAreaM2,
            glassPriceM2,
            leaves,
            quantity,
            aluminumMl,
            glassM2,
            glassCost,
            subtotal,
            marginAmount,
            taxableBase,
            ivaAmount,
            appliedMarginPct,
            total,
            drawingSvg: '',
            supplyCost: 0,
            installationCost: 0,
            hardwareCost: 0,
            extraLabor: 0,
            itemNotes: '',
            designerTree: dwState ? dwState.tree : null,
            designerSvg: (typeof window.DesignerWidget !== 'undefined' && window.designerSvgInput) ? (window.designerSvgInput.value || '') : '',
        };
    };

    const renderGlassSummary = (quote) => {
        if (!glassSummaryBox) {
            return;
        }

        const composition = quote.glassDescription || quote.glassType || text('undefined', 'Sin definir');
        glassSummaryBox.innerHTML = `
            <div>
                <strong>${text('selectedItem', 'Partida seleccionada')}</strong>
                <p class="field-hint">${composition} · ${quote.glassWidthMm} x ${quote.glassHeightMm} mm · ${quote.glassPanels} ${text('piecesPerUnitText', 'piezas por unidad')}.</p>
            </div>
            <div class="glass-summary-grid">
                <div class="glass-stat">
                    <span>${text('sqmPerPiece', 'm² por pieza')}</span>
                    <strong>${quote.glassPieceAreaM2.toFixed(3)} m2</strong>
                </div>
                <div class="glass-stat">
                    <span>${text('sqmTotal', 'm² totales')}</span>
                    <strong>${quote.glassM2.toFixed(3)} m2</strong>
                </div>
                <div class="glass-stat">
                    <span>${text('glassCost', 'Coste vidrio')}</span>
                    <strong>${formatMoney(quote.glassCost)}</strong>
                </div>
            </div>
        `;
    };

    const updateProfilePreview = (profileColorHex, profileColor) => {
        if (profilePreviewSwatch) {
            const palette = getProfilePalette(profileColorHex);
            profilePreviewSwatch.style.background = `linear-gradient(135deg, ${palette.light}, ${palette.base} 60%, ${palette.dark})`;
            profilePreviewSwatch.style.borderColor = palette.shadow;
        }
        if (profilePreviewLabel) {
            profilePreviewLabel.textContent = profileColor;
        }
    };

    const renderDrawing = (quote) => {
        // Para composiciones modulares, usar el SVG del diseñador directamente
        if (quote.isComposite && quote.designerSvg) {
            if (drawingWrap) drawingWrap.innerHTML = quote.designerSvg;
            if (drawingSvgInput) drawingSvgInput.value = quote.designerSvg;
            updateProfilePreview(quote.profileColorHex, quote.profileColor);
            return quote.designerSvg;
        }

        const PD = 16;
        const frame = {
            outerX: 90, outerY: 48,
            outerWidth: 358, outerHeight: 280,
            get innerX()     { return this.outerX + PD; },
            get innerY()     { return this.outerY + PD; },
            get innerWidth()  { return this.outerWidth - PD * 2; },
            get innerHeight() { return this.outerHeight - PD * 2; },
        };
        const { geometry: leavesGeo, barWidth } = getLeafGeometry(quote, frame);
        const trimOffset = getTrimOffset(quote.trimSize);
        const usesMiterCut = quote.systemType === 'fijo' || quote.frameCutType === 'mitered';

        const profFill = mixColor(quote.profileColorHex || '#c0c8d0', '#f4f6f8', 0.82);
        const fStroke = '#161616';

        const safeModel  = escapeSvgText(quote.carpentryModel || quote.systemTypeLabel || quote.systemType);
        const safeRef    = escapeSvgText(
            quote.carpentrySeriesLabel
                ? `${quote.carpentrySeriesLabel}${quote.carpentryReference ? ' · ' + quote.carpentryReference : ''}`
                : (quote.carpentryReference || '—')
        );
        const safeColor  = escapeSvgText(quote.profileColor || '—');
        const safeGlass  = escapeSvgText(quote.glassDescription || quote.glassType || '—');
        const trimLabel  = quote.trimSize > 0 ? `${quote.trimSize} mm` : '—';
        const cutLabel   = quote.frameCutType === 'mitered' ? '45°' : 'Recto';

        let leavesMarkup   = '';
        let barsMarkup     = '';
        let markersMarkup  = '';
        let trimMarkup     = '';

        if (trimOffset > 0) {
            const to = trimOffset * 1.8;
            trimMarkup = `<rect x="${frame.outerX - to}" y="${frame.outerY - to}" width="${frame.outerWidth + to * 2}" height="${frame.outerHeight + to * 2}" fill="none" stroke="#7a8fa0" stroke-width="0.9" stroke-dasharray="5 3"/>`;
        }

        leavesGeo.forEach((leaf, index) => {
            const isCasement = quote.systemType === 'abatible' || quote.systemType === 'oscilobatiente';
            const SD = quote.systemType === 'corredera' ? 10 : 8;
            const GI = SD + 5;
            const gx = leaf.x + GI, gy = leaf.y + GI;
            const gw = leaf.width - GI * 2, gh = leaf.height - GI * 2;

            if (quote.systemType === 'fijo') {
                const fx2 = leaf.x + 5, fy2 = leaf.y + 5;
                const fw2 = leaf.width - 10, fh2 = leaf.height - 10;
                leavesMarkup += `
                    <rect x="${fx2}" y="${fy2}" width="${fw2}" height="${fh2}" fill="url(#cad-glass)" stroke="#3a88bb" stroke-width="0.8"/>
                    <line x1="${fx2}" y1="${fy2}" x2="${fx2 + fw2}" y2="${fy2 + fh2}" stroke="#6aaecc" stroke-width="0.55" opacity="0.5"/>
                    <line x1="${fx2 + fw2}" y1="${fy2}" x2="${fx2}" y2="${fy2 + fh2}" stroke="#6aaecc" stroke-width="0.55" opacity="0.5"/>
                `;
            } else {
                leavesMarkup += `
                    <rect x="${leaf.x}" y="${leaf.y}" width="${leaf.width}" height="${leaf.height}" fill="${profFill}" stroke="${fStroke}" stroke-width="1.1"/>
                    <rect x="${leaf.x + SD}" y="${leaf.y + SD}" width="${leaf.width - SD * 2}" height="${leaf.height - SD * 2}" fill="none" stroke="${fStroke}" stroke-width="0.45"/>
                `;
                if (gw > 4 && gh > 4) {
                    leavesMarkup += `
                        <rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="url(#cad-glass)" stroke="#3a88bb" stroke-width="0.7"/>
                        <line x1="${gx}" y1="${gy}" x2="${gx + gw}" y2="${gy + gh}" stroke="#6aaecc" stroke-width="0.5" opacity="0.45"/>
                        <line x1="${gx + gw}" y1="${gy}" x2="${gx}" y2="${gy + gh}" stroke="#6aaecc" stroke-width="0.5" opacity="0.45"/>
                    `;
                }
                const showHandle = quote.systemType === 'corredera'
                    || (isCasement && index === getCasementHandleLeafIndex(quote));
                if (showHandle) {
                    const hSide = quote.systemType === 'corredera'
                        ? (getSlidingDirection(quote.openingType, index, quote.leaves) === 1 ? 'right' : 'left')
                        : getCasementHandleSide(quote);
                    const hx = hSide === 'left' ? leaf.x + leaf.width - 9 : leaf.x + 3;
                    const hy = leaf.y + leaf.height / 2 - 13;
                    leavesMarkup += `<rect x="${hx}" y="${hy}" width="6" height="26" rx="2" fill="#b0b8c0" stroke="#555" stroke-width="0.7"/>`;
                }
            }

            if (quote.systemType === 'corredera') {
                markersMarkup += buildSlidingMarker(leaf, index, quote);
            } else if (quote.systemType === 'abatible') {
                markersMarkup += buildCasementMarker(leaf, index, quote);
            } else if (quote.systemType === 'oscilobatiente') {
                markersMarkup += buildCasementMarker(leaf, index, quote, index === getTiltTurnLeafIndex(quote));
            }

            if (index < leavesGeo.length - 1) {
                const barX = leaf.x + leaf.width;
                barsMarkup += `
                    <rect x="${barX}" y="${frame.innerY}" width="${barWidth}" height="${frame.innerHeight}" fill="${profFill}" stroke="${fStroke}" stroke-width="0.8"/>
                    <line x1="${barX + barWidth / 2}" y1="${frame.innerY + 4}" x2="${barX + barWidth / 2}" y2="${frame.innerY + frame.innerHeight - 4}" stroke="#999" stroke-width="0.4"/>
                `;
            }
        });

        let miterMarkup = '';
        if (usesMiterCut) {
            const cx = frame.outerX, cy = frame.outerY, cw = frame.outerWidth, ch = frame.outerHeight;
            miterMarkup = `
                <line x1="${cx}" y1="${cy + PD}" x2="${cx + PD}" y2="${cy}" stroke="#444" stroke-width="1.1" fill="none" stroke-linecap="round"/>
                <line x1="${cx + cw - PD}" y1="${cy}" x2="${cx + cw}" y2="${cy + PD}" stroke="#444" stroke-width="1.1" fill="none" stroke-linecap="round"/>
                <line x1="${cx}" y1="${cy + ch - PD}" x2="${cx + PD}" y2="${cy + ch}" stroke="#444" stroke-width="1.1" fill="none" stroke-linecap="round"/>
                <line x1="${cx + cw - PD}" y1="${cy + ch}" x2="${cx + cw}" y2="${cy + ch - PD}" stroke="#444" stroke-width="1.1" fill="none" stroke-linecap="round"/>
            `;
        }

        const fx = frame.outerX, fy = frame.outerY, fw = frame.outerWidth, fh = frame.outerHeight;
        const DG = 28, DT = 5;

        const widthDim = `
            <line x1="${fx}" y1="${fy + fh}" x2="${fx}" y2="${fy + fh + DG + DT}" stroke="#333" stroke-width="0.6"/>
            <line x1="${fx + fw}" y1="${fy + fh}" x2="${fx + fw}" y2="${fy + fh + DG + DT}" stroke="#333" stroke-width="0.6"/>
            <line x1="${fx + 4}" y1="${fy + fh + DG}" x2="${fx + fw - 4}" y2="${fy + fh + DG}" stroke="#333" stroke-width="0.85" marker-start="url(#cad-arrL)" marker-end="url(#cad-arrR)"/>
            <text x="${fx + fw / 2}" y="${fy + fh + DG + 14}" text-anchor="middle" class="cad-dim">L = ${quote.widthMm} mm</text>
        `;

        const hdx = fx + fw + DG;
        const heightDim = `
            <line x1="${fx + fw}" y1="${fy}" x2="${hdx + DT}" y2="${fy}" stroke="#333" stroke-width="0.6"/>
            <line x1="${fx + fw}" y1="${fy + fh}" x2="${hdx + DT}" y2="${fy + fh}" stroke="#333" stroke-width="0.6"/>
            <line x1="${hdx}" y1="${fy + 4}" x2="${hdx}" y2="${fy + fh - 4}" stroke="#333" stroke-width="0.85" marker-start="url(#cad-arrL)" marker-end="url(#cad-arrR)"/>
            <text x="${hdx + 19}" y="${fy + fh / 2}" text-anchor="middle" class="cad-dim" transform="rotate(-90,${hdx + 19},${fy + fh / 2})">H = ${quote.heightMm} mm</text>
        `;

        const TY = 432, TH = 74, TX = 8, TW = 624;
        const R2 = TY + TH / 2;
        const titleBlock = `
            <rect x="${TX}" y="${TY}" width="${TW}" height="${TH}" fill="#f5f6f8" stroke="#222" stroke-width="0.8"/>
            <line x1="${TX}" y1="${R2}" x2="${TX + TW}" y2="${R2}" stroke="#666" stroke-width="0.4"/>
            <line x1="${TX + 138}" y1="${TY}" x2="${TX + 138}" y2="${TY + TH}" stroke="#888" stroke-width="0.4"/>
            <line x1="${TX + 296}" y1="${TY}" x2="${TX + 296}" y2="${TY + TH}" stroke="#888" stroke-width="0.4"/>
            <line x1="${TX + 422}" y1="${TY}" x2="${TX + 422}" y2="${TY + TH}" stroke="#888" stroke-width="0.4"/>
            <text x="${TX + 5}" y="${TY + 11}" class="cad-tb-lbl">SISTEMA</text>
            <text x="${TX + 143}" y="${TY + 11}" class="cad-tb-lbl">SERIE / REFERENCIA</text>
            <text x="${TX + 301}" y="${TY + 11}" class="cad-tb-lbl">COLOR PERFIL</text>
            <text x="${TX + 427}" y="${TY + 11}" class="cad-tb-lbl">MEDIDA</text>
            <text x="${TX + 5}" y="${TY + 27}" class="cad-tb-val">${safeModel}</text>
            <text x="${TX + 143}" y="${TY + 27}" class="cad-tb-val">${safeRef}</text>
            <text x="${TX + 301}" y="${TY + 27}" class="cad-tb-val">${safeColor}</text>
            <text x="${TX + 427}" y="${TY + 27}" class="cad-tb-val">${quote.widthMm} × ${quote.heightMm} mm</text>
            <line x1="${TX + 72}" y1="${R2}" x2="${TX + 72}" y2="${TY + TH}" stroke="#888" stroke-width="0.4"/>
            <line x1="${TX + 190}" y1="${R2}" x2="${TX + 190}" y2="${TY + TH}" stroke="#888" stroke-width="0.4"/>
            <line x1="${TX + 296}" y1="${R2}" x2="${TX + 296}" y2="${TY + TH}" stroke="#888" stroke-width="0.4"/>
            <line x1="${TX + 400}" y1="${R2}" x2="${TX + 400}" y2="${TY + TH}" stroke="#888" stroke-width="0.4"/>
            <text x="${TX + 5}" y="${R2 + 13}" class="cad-tb-lbl">HOJAS</text>
            <text x="${TX + 77}" y="${R2 + 13}" class="cad-tb-lbl">APERTURA</text>
            <text x="${TX + 195}" y="${R2 + 13}" class="cad-tb-lbl">CORTE</text>
            <text x="${TX + 301}" y="${R2 + 13}" class="cad-tb-lbl">TAPAJUNTAS</text>
            <text x="${TX + 405}" y="${R2 + 13}" class="cad-tb-lbl">VIDRIO</text>
            <text x="${TX + 5}" y="${R2 + 28}" class="cad-tb-val">${quote.leaves} hj.</text>
            <text x="${TX + 77}" y="${R2 + 28}" class="cad-tb-val">${escapeSvgText(quote.openingTypeLabel || quote.openingType)}</text>
            <text x="${TX + 195}" y="${R2 + 28}" class="cad-tb-val">${cutLabel}</text>
            <text x="${TX + 301}" y="${R2 + 28}" class="cad-tb-val">${trimLabel}</text>
            <text x="${TX + 405}" y="${R2 + 28}" class="cad-tb-val">${safeGlass}</text>
        `;

        const svg = `<svg viewBox="0 0 640 514" role="img" aria-label="Plano técnico de carpintería de aluminio" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="cad-glass" width="14" height="14" patternUnits="userSpaceOnUse">
                    <rect width="14" height="14" fill="#dceef8"/>
                    <line x1="0" y1="14" x2="14" y2="0" stroke="#5599cc" stroke-width="0.55" opacity="0.4"/>
                </pattern>
                <marker id="cad-arrR" markerWidth="6" markerHeight="6" refX="6" refY="3" orient="auto">
                    <polygon points="0,0 6,3 0,6" fill="#222"/>
                </marker>
                <marker id="cad-arrL" markerWidth="6" markerHeight="6" refX="0" refY="3" orient="auto-start-reverse">
                    <polygon points="0,0 6,3 0,6" fill="#222"/>
                </marker>
                <style>
                    .cad-sheet    { fill:#fff; stroke:#111; stroke-width:1.8; }
                    .cad-inner    { fill:none; stroke:#555; stroke-width:0.4; }
                    .opening-line-strong { stroke:#111; stroke-width:1.6; fill:none; stroke-linecap:round; stroke-linejoin:round; }
                    .hinge-line   { stroke:#333; stroke-width:1.4; fill:none; stroke-linecap:round; }
                    .swing-arc    { stroke:#555; stroke-width:1.1; fill:none; stroke-dasharray:5 3; }
                    .tilt-mark    { stroke:#222; stroke-width:1.3; fill:none; stroke-linecap:round; stroke-linejoin:round; }
                    .marker-tag   { fill:rgba(255,255,255,0.92); stroke:#555; stroke-width:0.7; }
                    .marker-label { fill:#111; font:bold 11px 'Arial Narrow',Arial,sans-serif; }
                    .cad-dim      { fill:#222; font:10px 'Arial Narrow',Arial,sans-serif; }
                    .cad-tb-lbl   { fill:#666; font:8px 'Arial Narrow',Arial,sans-serif; letter-spacing:.04em; }
                    .cad-tb-val   { fill:#111; font:bold 10.5px 'Arial Narrow',Arial,sans-serif; }
                </style>
            </defs>
            <rect x="4" y="4" width="632" height="506" class="cad-sheet"/>
            <rect x="8" y="8" width="624" height="498" class="cad-inner"/>
            ${trimMarkup}
            <rect x="${fx}" y="${fy}" width="${fw}" height="${fh}" fill="${profFill}" stroke="${fStroke}" stroke-width="1.6"/>
            <rect x="${frame.innerX}" y="${frame.innerY}" width="${frame.innerWidth}" height="${frame.innerHeight}" fill="#fff" stroke="${fStroke}" stroke-width="0.5"/>
            ${miterMarkup}
            ${leavesMarkup}
            ${barsMarkup}
            ${markersMarkup}
            ${widthDim}
            ${heightDim}
            ${titleBlock}
        </svg>`;

        drawingWrap.innerHTML = svg;
        drawingSvgInput.value = svg.trim();

        updateProfilePreview(quote.profileColorHex, quote.profileColor);

        return svg.trim();
    };

    const getSelectedItem = () => quoteItems.find((item) => item.id === selectedItemId) || null;

    const getAggregateQuote = () => quoteItems.reduce((acc, item) => ({
        itemCount: acc.itemCount + 1,
        quantity: acc.quantity + item.quantity,
        aluminumMl: roundMetric(acc.aluminumMl + item.aluminumMl),
        glassM2: roundMetric(acc.glassM2 + item.glassM2),
        glassCost: roundMoney(acc.glassCost + item.glassCost),
        subtotal: roundMoney(acc.subtotal + item.subtotal),
        marginAmount: roundMoney(acc.marginAmount + item.marginAmount),
        ivaAmount: roundMoney(acc.ivaAmount + item.ivaAmount),
        total: roundMoney(acc.total + item.total),
    }), {
        itemCount: 0,
        quantity: 0,
        aluminumMl: 0,
        glassM2: 0,
        glassCost: 0,
        subtotal: 0,
        marginAmount: 0,
        ivaAmount: 0,
        total: 0,
    });

    const renderTotals = () => {
        const aggregate = getAggregateQuote();

        totalsBox.innerHTML = `
            <div class="total-row"><span>${text('itemCount', 'Partidas')}</span><strong>${aggregate.itemCount}</strong></div>
            <div class="total-row"><span>${text('totalUnits', 'Unidades totales')}</span><strong>${aggregate.quantity} ${text('unitsShort', 'ud.')}</strong></div>
            <div class="total-row"><span>${text('aluminumPrice', 'Aluminio')}</span><strong>${aggregate.aluminumMl.toFixed(3)} ml</strong></div>
            <div class="total-row"><span>${text('glass', 'Vidrio')}</span><strong>${aggregate.glassM2.toFixed(3)} m2</strong></div>
            <div class="total-row"><span>${text('glassCost', 'Coste vidrio')}</span><strong>${formatMoney(aggregate.glassCost)}</strong></div>
            <div class="total-row"><span>${text('base', 'Base')}</span><strong>${formatMoney(aggregate.subtotal)}</strong></div>
            <div class="total-row"><span>${text('margin', 'Margen')}</span><strong>${formatMoney(aggregate.marginAmount)}</strong></div>
            <div class="total-row"><span>${text('iva', 'IVA')}</span><strong>${formatMoney(aggregate.ivaAmount)}</strong></div>
            <div class="total-row total-main"><span>${text('total', 'Total')}</span><strong>${formatMoney(aggregate.total)}</strong></div>
        `;
    };

    const editableFields = ['supply_cost', 'installation_cost', 'hardware_cost', 'extra_labor', 'item_notes'];

    const getItemEditable = (item) => ({
        supply_cost: item.supplyCost || 0,
        installation_cost: item.installationCost || 0,
        hardware_cost: item.hardwareCost || 0,
        extra_labor: item.extraLabor || 0,
        item_notes: item.itemNotes || '',
    });

    const serializeItemForSubmit = (item) => ({
        system_type: item.systemType,
        opening_type: item.openingType,
        carpentry_model: item.carpentryModelValue,
        carpentry_series: item.carpentrySeriesValue,
        carpentry_reference: item.carpentryReference,
        trim_size: item.trimSize,
        tilt_turn_leaf: item.tiltTurnLeaf,
        frame_cut_type: item.frameCutType,
        glass_type: item.glassTypeValue,
        glass_description: item.glassDescription,
        profile_color_hex: item.profileColorHex,
        profile_color: item.profileColor,
        pricing_mode: item.pricingMode,
        is_factory_finished: item.isFactoryFinished ? 1 : 0,
        purchased_unit_cost: item.purchasedUnitCost,
        aluminum_price_ml: item.aluminumPriceMl,
        glass_price_m2: item.glassPriceM2,
        labor_cost: item.laborCost,
        internal_extra_cost: item.internalExtraCost,
        margin_pct: item.marginPct,
        commercial_margin_pct: item.commercialMarginPct,
        iva_pct: item.ivaPct,
        width_mm: item.widthMm,
        height_mm: item.heightMm,
        leaves: item.leaves,
        quantity: item.quantity,
        glass_width_mm: item.glassWidthMm,
        glass_height_mm: item.glassHeightMm,
        glass_panels: item.glassPanels,
        drawing_svg: item.drawingSvg,
        is_composite: item.isComposite ? 1 : 0,
        composite_label: item.compositeLabel || '',
        panels: item.panels || [],
        supply_cost: item.supplyCost || 0,
        installation_cost: item.installationCost || 0,
        hardware_cost: item.hardwareCost || 0,
        extra_labor: item.extraLabor || 0,
        item_notes: item.itemNotes || '',
        designer_tree: item.designerTree || null,
        designer_svg: item.designerSvg || '',
    });

    const updateSerializedInputs = () => {
        const payload = quoteItems.map(serializeItemForSubmit);
        quoteItemsJsonInput.value = JSON.stringify(payload);
        configJsonInput.value = JSON.stringify({
            items: payload,
            totals: getAggregateQuote(),
        });

        const selected = getSelectedItem();
        drawingSvgInput.value = selected?.drawingSvg || '';
    };

    const renderItemsList = () => {
        if (!quoteItemsList) {
            return;
        }

        quoteItemsList.innerHTML = quoteItems.map((item, index) => {
            var itemLabel = item.isComposite ? item.compositeLabel : item.systemTypeLabel;
            return `
            <article class="quote-item-card ${item.id === selectedItemId ? 'is-selected' : ''}" data-item-id="${item.id}">
                <div class="quote-item-card__head">
                    <button type="button" class="quote-item-select" data-select-item="${item.id}">
                        <strong>${text('item', 'Partida')} ${index + 1}</strong>
                        <span>${itemLabel} · ${item.widthMm} x ${item.heightMm} mm</span>
                    </button>
                    ${quoteItems.length > 1 ? `<button type="button" class="quote-item-remove" data-remove-item="${item.id}">${text('removeItem', 'Eliminar ventana')}</button>` : ''}
                </div>
                <div class="quote-item-card__meta">
                    <span>${item.leaves} ${text('leavesShort', 'hojas')} · ${item.quantity} ${text('unitsShort', 'ud.')}</span>
                    <strong>${formatMoney(item.total)}</strong>
                </div>
                <div class="quote-item-card__descomp">
                    <button type="button" class="descomp-toggle-btn" data-descomp="${item.id}" aria-expanded="false">
                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.5" class="descomp-chevron"><path d="M2 3.5 L5 6.5 L8 3.5"/></svg>
                        Ver descompuesto S28
                    </button>
                    <div class="descomp-panel" id="descomp-${item.id}" hidden></div>
                </div>
            </article>`;
        }).join('');
    };

    const loadItemIntoForm = (item) => {
        if (!item) {
            return;
        }

        suppressSync = true;
        fields.systemType.value = item.systemType;
        fields.openingType.value = item.openingType;
        fields.carpentryModel.value = item.carpentryModelValue;
        if (fields.carpentrySeriesSelect) {
            fields.carpentrySeriesSelect.value = item.carpentrySeriesValue || '';
        }
        fields.carpentryReference.value = item.carpentryReference;
        fields.trimSize.value = String(item.trimSize);
        fields.tiltTurnLeaf.value = item.tiltTurnLeaf === 'derecha' ? 'derecha' : (item.tiltTurnLeaf === 'unica' ? 'unica' : 'izquierda');
        fields.frameCutType.value = item.frameCutType === 'mitered' ? 'mitered' : 'recto';
        fields.glassType.value = item.glassTypeValue;
        fields.glassDescription.value = item.glassDescription;
        fields.glassWidthMm.value = String(item.glassWidthMm);
        fields.glassHeightMm.value = String(item.glassHeightMm);
        fields.glassPanels.value = String(item.glassPanels);
        fields.profileColorHex.value = item.profileColorHex;
        fields.profileColorName.value = item.profileColor;
        fields.pricingMode.value = item.pricingMode;
        fields.isFactoryFinished.checked = !!item.isFactoryFinished;
        fields.purchasedUnitCost.value = item.purchasedUnitCost.toFixed(2);
        fields.widthMm.value = String(item.widthMm);
        fields.heightMm.value = String(item.heightMm);
        fields.leaves.value = String(item.leaves);
        fields.priceAl.value = item.aluminumPriceMl.toFixed(2);
        fields.priceGlass.value = item.glassPriceM2.toFixed(2);
        fields.labor.value = item.laborCost.toFixed(2);
        fields.internalExtraCost.value = item.internalExtraCost.toFixed(2);
        fields.margin.value = item.marginPct.toFixed(2);
        fields.commercialMargin.value = item.commercialMarginPct.toFixed(2);
        fields.iva.value = item.ivaPct.toFixed(2);
        fields.quantity.value = String(item.quantity);
        syncPresetFromHex();
        updateSystemDetailsUI();
        updatePricingModeUI();
        lastSuggestedGlassDescription = item.glassDescription;
        glassPriceWasSuggested = false;
        suppressSync = false;
        syncState();
        // Restaurar árbol de diseño de la partida
        reloadDesignerTree(item);
    };

    const upsertSelectedItem = (quote) => {
        const existingIndex = quoteItems.findIndex((item) => item.id === selectedItemId);
        const currentItem = {
            ...quote,
            id: selectedItemId || createItemId(),
        };

        if (existingIndex >= 0) {
            quoteItems[existingIndex] = currentItem;
        } else {
            quoteItems.push(currentItem);
        }

        selectedItemId = currentItem.id;
    };

    const syncState = () => {
        if (suppressSync) {
            return;
        }

        const quote = calculateQuote();
        quote.drawingSvg = renderDrawing(quote);
        upsertSelectedItem(quote);
        renderGlassSummary(quote);
        renderItemsList();
        renderTotals();
        updateSerializedInputs();
    };

    const addCurrentItem = () => {
        syncState();
        const baseItem = getSelectedItem();
        if (!baseItem) {
            return;
        }

        const newItem = {
            ...baseItem,
            id: createItemId(),
        };

        quoteItems.push(newItem);
        selectedItemId = newItem.id;
        loadItemIntoForm(newItem);
    };

    const removeItem = (itemId) => {
        if (quoteItems.length <= 1) {
            return;
        }

        quoteItems = quoteItems.filter((item) => item.id !== itemId);
        if (selectedItemId === itemId) {
            selectedItemId = quoteItems[0]?.id || null;
        }

        const selected = getSelectedItem();
        if (selected) {
            loadItemIntoForm(selected);
        }
    };

    fields.glassType?.addEventListener('change', () => {
        glassPriceWasSuggested = true;
        syncGlassDescriptionFromType();
        syncGlassPriceFromType();
        syncState();
    });

    fields.systemType?.addEventListener('change', () => {
        updateSystemDetailsUI();
        syncState();
    });

    fields.leaves?.addEventListener('input', () => {
        updateSystemDetailsUI();
        syncState();
    });

    fields.tiltTurnLeaf?.addEventListener('change', syncState);

    fields.glassDescription?.addEventListener('input', () => {
        lastSuggestedGlassDescription = fields.glassDescription.value.trim();
    });

    fields.priceGlass?.addEventListener('input', () => {
        glassPriceWasSuggested = false;
    });

    fields.suggestGlassButton?.addEventListener('click', () => {
        applySuggestedGlassMeasures();
        syncState();
    });

    fields.profileColorPreset?.addEventListener('change', () => {
        syncProfileColorInputs();
        syncState();
    });

    fields.pricingMode?.addEventListener('change', () => {
        updatePricingModeUI();
        syncState();
    });

    fields.profileColorHex?.addEventListener('input', () => {
        syncPresetFromHex();
        syncState();
    });

    fields.profileColorName?.addEventListener('input', () => {
        syncPresetFromHex();
    });

    addItemButton?.addEventListener('click', addCurrentItem);

    quoteItemsList?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-item]');
        if (removeButton) {
            removeItem(removeButton.getAttribute('data-remove-item'));
            return;
        }

        const selectButton = event.target.closest('[data-select-item]');
        if (selectButton) {
            const nextItem = quoteItems.find((item) => item.id === selectButton.getAttribute('data-select-item'));
            if (nextItem) {
                selectedItemId = nextItem.id;
                loadItemIntoForm(nextItem);
            }
            return;
        }

        const descompBtn = event.target.closest('[data-descomp]');
        if (descompBtn) {
            const itemId = descompBtn.getAttribute('data-descomp');
            const panel = document.getElementById(`descomp-${itemId}`);
            const item = quoteItems.find((i) => i.id === itemId);
            if (!panel || !item) { return; }
            const isOpen = !panel.hidden;
            panel.hidden = isOpen;
            descompBtn.setAttribute('aria-expanded', String(!isOpen));
            descompBtn.classList.toggle('is-open', !isOpen);
            if (!isOpen && !panel.dataset.loaded) {
                panel.innerHTML = renderS28Descompuesto(item);
                panel.dataset.loaded = '1';
            }
            return;
        }

        const saveBtn = event.target.closest('.de-save-btn');
        if (saveBtn) {
            var container = saveBtn.closest('.descomp-editable');
            if (!container) { return; }
            var itemId = container.getAttribute('data-item-id');
            var item = quoteItems.find(function (i) { return i.id === itemId; });
            if (!item) { return; }
            container.querySelectorAll('.de-field').forEach(function (field) {
                var fName = field.getAttribute('data-field');
                if (!fName) { return; }
                if (fName === 'itemNotes') {
                    item.itemNotes = field.value;
                } else {
                    item[fName] = parseFloat(field.value) || 0;
                }
            });
            syncState();
            saveBtn.textContent = '\u2713 Guardado';
            setTimeout(function () { saveBtn.textContent = 'Guardar costes de partida'; }, 1500);
        }
    });

    form.addEventListener('input', syncState);
    form.addEventListener('change', syncState);
    form.addEventListener('submit', () => {
        syncState();
        updateSerializedInputs();
    });

    try {
        syncProfileColorInputs();
        syncGlassDescriptionFromType({ force: true });
        syncGlassPriceFromType({ force: true });
        updateSystemDetailsUI();
        updatePricingModeUI();
        syncState();
        window.APP_PREVIEW_READY = true;
    } catch (error) {
        if (typeof window.reportAppRuntimeIssue === 'function') {
            window.reportAppRuntimeIssue(`Error iniciando la vista previa: ${error.message}`);
        }
        throw error;
    }

    // ── DISEÑADOR DE PANELES (siempre visible) ──
    var designerSvgInput = document.getElementById('designerSvg');
    var designerTreeJson = document.getElementById('designerTreeJson');
    var dwApplySvg       = document.getElementById('dwApplySvg');
    var dwSlopeRow       = document.getElementById('dw-slopeRow');
    var dwShapeSelect    = document.getElementById('dw-shapeSelect');
    var dwSection        = document.querySelector('.designer-section');
    var dwReady = false;

    function initDesigner(treeData) {
        if (!window.DesignerWidget) { return; }
        var w = parseInt(fields.widthMm?.value || '1500', 10);
        var h = parseInt(fields.heightMm?.value || '1200', 10);
        window.DesignerWidget.init({
            canvasWrap:          'dw-canvasWrap',
            btnSplitV:           'dw-btnSplitV',
            btnSplitH:           'dw-btnSplitH',
            btnUnsplit:          'dw-btnUnsplit',
            panelSystem:         'dw-panelSystem',
            panelOpening:        'dw-panelOpening',
            panelLabel:          'dw-panelLabel',
            splitRatio:          'dw-splitRatio',
            splitInfo:           'dw-splitInfo',
            splitControls:       'dw-splitControls',
            panelInfo:           'dw-panelInfo',
            leafControls:        'dw-leafControls',
            openingRow:          'dw-openingRow',
            heightControls:      'dw-heightControls',
            panelHeightPct:      'dw-panelHeightPct',
            panelHeightPctLabel: 'dw-panelHeightPctLabel',
            panelTopPct:         'dw-panelTopPct',
            panelTopPctLabel:    'dw-panelTopPctLabel',
            panelList:           'dw-panelList',
            sysLegend:           'dw-sysLegend',
            panelBadge:          'dwPanelBadge',
            facadeW:             'dw-facadeW',
            facadeH:             'dw-facadeH',
            shapeSelect:         'dw-shapeSelect',
            slopeRange:          'dw-slopeRange',
            slopeLabel:          'dw-slopeLabel',
            onSvgChange: function (svgStr, tree) {
                if (designerSvgInput) designerSvgInput.value = svgStr;
                if (designerTreeJson) designerTreeJson.value = JSON.stringify(tree);
            },
            facadeW_val: w,
            facadeH_val: h,
            tree: treeData || null,
        });
        dwReady = true;
    }

    function reloadDesignerTree(item) {
        if (!window.DesignerWidget || !dwReady) {
            if (dwSection) {
                initDesigner(item ? item.designerTree : null);
            }
            return;
        }
        if (item && item.designerTree) {
            window.DesignerWidget.loadState(item.designerTree);
        } else {
            var w = parseInt(fields.widthMm?.value || '1500', 10);
            var h = parseInt(fields.heightMm?.value || '1200', 10);
            window.DesignerWidget.applyPreset('default', w, h);
        }
    }

    if (dwSection && window.DesignerWidget) {
        // Inicializar inmediatamente (no esperar toggle)
        var initialTree = null;
        if (quoteItems.length > 0) {
            var firstItem = getSelectedItem() || quoteItems[0];
            if (firstItem) initialTree = firstItem.designerTree;
        }
        initDesigner(initialTree);

        dwShapeSelect?.addEventListener('change', function () {
            if (dwSlopeRow) dwSlopeRow.style.display = dwShapeSelect.value === 'trapezoidal' ? '' : 'none';
        });

        // Sincronizar dimensiones del hueco con el diseñador
        fields.widthMm?.addEventListener('input', function () {
            if (dwReady && window.DesignerWidget) {
                var w = parseInt(fields.widthMm?.value || '1500', 10);
                var h = parseInt(fields.heightMm?.value || '1200', 10);
                window.DesignerWidget.setDimensions(w, h);
            }
        });
        fields.heightMm?.addEventListener('input', function () {
            if (dwReady && window.DesignerWidget) {
                var w = parseInt(fields.widthMm?.value || '1500', 10);
                var h = parseInt(fields.heightMm?.value || '1200', 10);
                window.DesignerWidget.setDimensions(w, h);
            }
        });

        // Presets
        document.getElementById('dwPresetEscaparate')?.addEventListener('click', function () {
            var w = parseInt(fields.widthMm?.value || '1500', 10);
            var h = parseInt(fields.heightMm?.value || '1200', 10);
            if (window.DesignerWidget) window.DesignerWidget.applyPreset('escaparate', w, h);
            saveDesignerToCurrentItem();
            syncState();
        });
        document.getElementById('dwPresetFijoPF')?.addEventListener('click', function () {
            var w = parseInt(fields.widthMm?.value || '1500', 10);
            var h = parseInt(fields.heightMm?.value || '1200', 10);
            if (window.DesignerWidget) window.DesignerWidget.applyPreset('fijo_puerta_fijo', w, h);
            saveDesignerToCurrentItem();
            syncState();
        });
    }

    function saveDesignerToCurrentItem() {
        var selected = getSelectedItem();
        if (!selected) { return; }
        if (window.designerSvgInput) {
            selected.designerSvg = window.designerSvgInput.value || '';
        }
        if (window.DesignerWidget && window.DesignerWidget.getState) {
            var st = window.DesignerWidget.getState();
            selected.designerTree = st ? st.tree : null;
        }
        if (selected.designerSvg) {
            selected.drawingSvg = selected.designerSvg;
            if (drawingSvgInput) drawingSvgInput.value = selected.designerSvg;
        }
    }

    dwApplySvg?.addEventListener('click', function () {
        saveDesignerToCurrentItem();
        var selected = getSelectedItem();
        if (selected && selected.designerSvg && drawingWrap) {
            drawingWrap.innerHTML = selected.designerSvg;
        }
        syncState();
    });

    // Unificar actualizacion de RAL en el change del preset de color
    var carpentryRalInput = document.getElementById('carpentryRal');
    var origProfileChange = fields.profileColorPreset?.addEventListener;
    if (origProfileChange) {
        // Reemplazar listener existente por uno que tambien actualice RAL
        var ralHandler = function () {
            syncProfileColorInputs();
            var opt = fields.profileColorPreset?.selectedOptions?.[0];
            var ral = opt?.dataset?.ral || '';
            if (carpentryRalInput) carpentryRalInput.value = ral;
            syncState();
        };
        // Quitar listener anterior reemplazando el elemento
        var oldSelect = fields.profileColorPreset;
        var newSelect = oldSelect.cloneNode(true);
        oldSelect.parentNode.replaceChild(newSelect, oldSelect);
        fields.profileColorPreset = newSelect;
        fields.profileColorPreset.addEventListener('change', ralHandler);
    }
}

// ════════════════════════════════════════════════════════════════
//  DESCOMPUESTO S28 · EXTRUAL — integrado por ventana
//  Usa S28Engine compartido (s28-engine.js)
// ════════════════════════════════════════════════════════════════

var S28 = window.S28Engine;

function s28SystemId(quote) {
    var st = quote.systemType, leaves = quote.leaves;
    if (st === 'fijo') { return 'v_fijo'; }
    if (st === 'oscilobatiente') { return 'v1h_osci'; }
    if (st === 'corredera') { return null; }
    if (leaves === 1) { return 'v1h_prac'; }
    if (leaves === 2) { return 'v2h_prac'; }
    if (leaves === 3) { return 'v3h_prac'; }
    return 'v1h_prac';
}

function renderS28Descompuesto(item) {
    var itemId = item.id || '';
    var sysId = s28SystemId(item);
    var sysNames = { v_fijo: 'Ventana Fija', v1h_prac: 'Ventana 1H Practicable', v1h_osci: 'Ventana 1H Oscilobatiente', v2h_prac: 'Ventana 2H Practicable', v3h_prac: 'Ventana 3H Practicable' };
    var systemName = sysNames[sysId] || sysId || 'Compuesto';
    var isComposite = item.isComposite;

    var s28html = '';
    if (sysId && !isComposite) {
        var glassThick = S28.thickFromGlassType(item.glassTypeValue);
        var result = S28.calculate(sysId, item.widthMm, item.heightMm, 1, { glassThick: glassThick, junquilloType: 'curvo_clip' });
        if (result) {
            var totalMl = 0;
            var barsRows = '';
            for (var i = 0; i < result.bars.length; i++) {
                var b = result.bars[i];
                var ml = (b.cut * b.qty / 1000).toFixed(3);
                totalMl += b.cut * b.qty / 1000;
                var errCls = b.cut < 0 ? ' class="descomp-err"' : '';
                barsRows += '<tr><td class="descomp-ref">' + b.ref + '</td><td>' + (S28.PROFILES[b.ref] || b.desc) +
                    '</td><td' + errCls + '>' + Math.round(b.cut) + '</td><td>' + b.qty + '</td><td>' + ml + '</td></tr>';
            }
            var glassRows = '';
            for (var j = 0; j < result.glass.length; j++) {
                var g = result.glass[j];
                var errClsG = (g.W <= 0 || g.H <= 0) ? ' class="descomp-err"' : '';
                glassRows += '<tr><td>Vidrio</td><td' + errClsG + '>' + Math.round(g.W) + '</td><td' + errClsG + '>' +
                    Math.round(g.H) + '</td><td>' + g.qty + '</td><td>' + ((g.W / 1000) * (g.H / 1000) * g.qty).toFixed(3) + ' m\u00b2</td></tr>';
            }
            s28html = '<div class="descomp-meta"><strong>Serie 28 \u00b7 EXTRUAL</strong>' +
                '<span>' + systemName + ' \u00b7 ' + item.widthMm + ' \u00d7 ' + item.heightMm + ' mm</span></div>' +
                '<table class="descomp-table"><thead><tr><th>Ref.</th><th>Descripcion</th><th>Corte mm</th><th>Cant.</th><th>Total ml</th></tr></thead><tbody>' +
                barsRows + '</tbody><tfoot><tr><td colspan="4"><strong>Total aluminio</strong></td><td><strong>' + totalMl.toFixed(3) + ' ml</strong></td></tr></tfoot></table>' +
                '<table class="descomp-table descomp-table--glass"><thead><tr><th>Vidrio</th><th>Ancho mm</th><th>Alto mm</th><th>Cant.</th><th>m\u00b2</th></tr></thead><tbody>' +
                glassRows + '</tbody></table>' +
                '<p class="descomp-note">Catalogo S28 EXTRUAL \u00b7 Cara marco 21.8 mm \u00b7 Descuento hoja 43.6 mm \u00b7 Verificar siempre con muestra.</p>';
        }
    } else if (isComposite && item.panels && item.panels.length > 0) {
        var compRows = '';
        for (var pi = 0; pi < item.panels.length; pi++) {
            var p = item.panels[pi];
            compRows += '<tr><td>' + (p.label || (p.system || '')) + '</td><td>' + (p.system || '') + '</td><td>' + p.widthMm + '</td><td>' + p.heightMm + '</td><td>' + ((p.widthMm * p.heightMm) / 1000000).toFixed(3) + ' m\u00b2</td></tr>';
        }
        s28html = '<div class="descomp-meta"><strong>Composicion de paneles</strong>' +
            '<span>' + systemName + ' \u00b7 ' + item.widthMm + ' \u00d7 ' + item.heightMm + ' mm</span></div>' +
            '<table class="descomp-table"><thead><tr><th>Panel</th><th>Tipo</th><th>Ancho</th><th>Alto</th><th>Superficie</th></tr></thead><tbody>' +
            compRows + '</tbody></table>';
    } else {
        s28html = '<p class="field-hint" style="padding:0.5rem">S28 no disponible para este sistema.</p>';
    }

    // ── CAMPOS EDITABLES POR PARTIDA ──
    var sc = item.supplyCost || 0, ic = item.installationCost || 0;
    var hc = item.hardwareCost || 0, el = item.extraLabor || 0;
    var inotes = item.itemNotes || '';

    var editableHtml = '<div class="descomp-editable" data-item-id="' + itemId + '">' +
        '<h4 style="margin:0.5rem 0 0.3rem;font-size:0.82rem;color:var(--muted)">Costes internos por partida</h4>' +
        '<div class="descomp-editable__grid">' +
        '<label>Suministro (€)<input type="number" class="de-field" data-field="supplyCost" value="' + sc.toFixed(2) + '" min="0" step="0.01"></label>' +
        '<label>Colocacion (€)<input type="number" class="de-field" data-field="installationCost" value="' + ic.toFixed(2) + '" min="0" step="0.01"></label>' +
        '<label>Herrajes (€)<input type="number" class="de-field" data-field="hardwareCost" value="' + hc.toFixed(2) + '" min="0" step="0.01"></label>' +
        '<label>Mano de obra extra (€)<input type="number" class="de-field" data-field="extraLabor" value="' + el.toFixed(2) + '" min="0" step="0.01"></label>' +
        '</div>' +
        '<label style="margin-top:0.3rem">Observaciones<textarea class="de-field" data-field="itemNotes" rows="2" style="font-size:0.82rem">' + escHtml(inotes) + '</textarea></label>' +
        '<button type="button" class="secondary-button de-save-btn" style="margin-top:0.4rem;font-size:0.82rem">Guardar costes de partida</button>' +
        '</div>';

    return '<div class="descomp-body">' + s28html + editableHtml + '</div>';
}

function escHtml(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}