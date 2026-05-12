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
        carpentryReference: document.getElementById('carpentryReference'),
        trimSize: document.getElementById('trimSize'),
        tiltTurnConfig: document.getElementById('tiltTurnConfig'),
        tiltTurnLeaf: document.getElementById('tiltTurnLeaf'),
        frameCutType: document.getElementById('frameCutType'),
        serieKey: document.getElementById('serieKey'),
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
    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

    let glassPriceWasSuggested = true;
    let lastSuggestedGlassDescription = fields.glassDescription?.value.trim() || '';
    let quoteItems = [];
    let selectedItemId = null;
    let suppressSync = false;
    let itemSequence = 0;

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
        .map((value) => clamp(Math.round(value), 0, 255).toString(16).padStart(2, '0'))
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
        if (rightOption) {
            rightOption.hidden = systemType !== 'oscilobatiente' || leaves === 1;
        }

        if (systemType !== 'oscilobatiente' || leaves === 1) {
            fields.tiltTurnLeaf.value = 'izquierda';
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
        const frameMl = (widthM * 2) + (heightM * 2);
        const dividerMl = Math.max(0, leaves - 1) * heightM;
        const leafPerimeterMl = leaves * (((widthM / leaves) * 2) + (heightM * 2));
        const aluminumMl = roundMetric(((frameMl + dividerMl) + (leafPerimeterMl * 0.35)) * quantity);
        const glassPieceAreaM2 = roundMetric((glassWidthMm / 1000) * (glassHeightMm / 1000));
        const glassM2 = roundMetric(glassPieceAreaM2 * glassPanels * quantity);
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
            systemTypeLabel: fields.systemType?.selectedOptions?.[0]?.textContent?.trim() || systemType,
            openingType: fields.openingType?.value || 'izquierda',
            openingTypeLabel: fields.openingType?.selectedOptions?.[0]?.textContent?.trim() || '',
            carpentryModelValue: fields.carpentryModel?.value || '',
            carpentryModel: fields.carpentryModel?.selectedOptions?.[0]?.textContent?.trim() || '',
            carpentryReference: fields.carpentryReference?.value.trim() || '',
            trimSize: integerValue(fields.trimSize, 0),
            tiltTurnLeaf,
            tiltTurnLeafLabel,
            frameCutType: systemType === 'fijo' ? 'mitered' : (fields.frameCutType?.value || 'recto'),
            serieKey: fields.serieKey?.value || '',
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

    const renderDrawing = (quote) => {
        const safeModel = escapeSvgText(quote.carpentryModel || quote.systemTypeLabel || quote.systemType);
        const safeReference = escapeSvgText(quote.carpentryReference || text('noReference', 'Sin ref.'));
        const safeColor = escapeSvgText(quote.profileColor);
        const safeGlass = escapeSvgText(quote.glassDescription || quote.glassType || text('glass', 'Vidrio'));
        const palette = getProfilePalette(quote.profileColorHex);

        const CANVAS_X = 76, CANVAS_Y = 44;
        const CANVAS_W = 340, CANVAS_H = 268;
        const scaleX = CANVAS_W / quote.widthMm;
        const scaleY = CANVAS_H / quote.heightMm;
        const scale = Math.min(scaleX, scaleY, 0.48);
        const drawW = Math.round(quote.widthMm * scale);
        const drawH = Math.round(quote.heightMm * scale);
        const frameX = Math.round(CANVAS_X + (CANVAS_W - drawW) / 2);
        const frameY = Math.round(CANVAS_Y + (CANVAS_H - drawH) / 2);
        const FT = 7;
        const FI = 3;
        const scaleN = Math.max(1, Math.round(1 / scale));

        const frame = {
            outerX: frameX,
            outerY: frameY,
            outerWidth: drawW,
            outerHeight: drawH,
            innerX: frameX + FT,
            innerY: frameY + FT,
            innerWidth: drawW - FT * 2,
            innerHeight: drawH - FT * 2,
        };

        const { geometry: leaves, barWidth } = getLeafGeometry(quote, frame);
        const trimOffset = getTrimOffset(quote.trimSize);
        const usesMiterCut = quote.systemType === 'fijo' || quote.frameCutType === 'mitered';

        const pLight = mixColor(palette.base, '#ffffff', 0.78);
        const pDark  = mixColor(palette.base, '#5a6470', 0.18);

        let leavesMarkup = '';
        let barsMarkup = '';
        let markersMarkup = '';
        let trimMarkup = '';

        if (trimOffset > 0) {
            trimMarkup = `
                <rect x="${frame.outerX - trimOffset}" y="${frame.outerY - trimOffset}"
                      width="${frame.outerWidth + trimOffset * 2}" height="${frame.outerHeight + trimOffset * 2}"
                      fill="#f5f8fa" stroke="rgba(78,90,101,0.30)" stroke-width="0.7" stroke-dasharray="4,3"/>
            `;
        }

        leaves.forEach((leaf, index) => {
            const slidingDirection = getSlidingDirection(quote.openingType, index, quote.leaves);
            const isCasementFamily = quote.systemType === 'abatible' || quote.systemType === 'oscilobatiente';
            const handleSide = quote.systemType === 'corredera'
                ? (slidingDirection === 1 ? 'left' : 'right')
                : getCasementHandleSide(quote);
            const shouldRenderHandle = quote.systemType === 'corredera'
                || (isCasementFamily && index === getCasementHandleLeafIndex(quote));

            if (quote.systemType === 'fijo') {
                const gx = leaf.x + FI + 2, gy = leaf.y + FI + 2;
                const gw = leaf.width - (FI + 2) * 2, gh = leaf.height - (FI + 2) * 2;
                leavesMarkup += `
                    <rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="#e8f3f6"/>
                    <rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="url(#glassHatch)" stroke="#7aafc0" stroke-width="0.5"/>
                `;
            } else {
                const sashInset = quote.systemType === 'corredera' ? 7 : 5;
                const glassInset = quote.systemType === 'corredera' ? 12 : 10;
                const gx = leaf.x + glassInset, gy = leaf.y + glassInset;
                const gw = leaf.width - glassInset * 2, gh = leaf.height - glassInset * 2;
                leavesMarkup += `
                    <rect x="${leaf.x}" y="${leaf.y}" width="${leaf.width}" height="${leaf.height}"
                          fill="url(#alProfile)" stroke="rgba(50,60,68,0.85)" stroke-width="0.8"/>
                    <rect x="${leaf.x + sashInset}" y="${leaf.y + sashInset}"
                          width="${leaf.width - sashInset * 2}" height="${leaf.height - sashInset * 2}"
                          fill="none" stroke="rgba(180,190,198,0.55)" stroke-width="0.5"/>
                    <rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="#e8f3f6"/>
                    <rect x="${gx}" y="${gy}" width="${gw}" height="${gh}" fill="url(#glassHatch)" stroke="#7aafc0" stroke-width="0.5"/>
                    ${shouldRenderHandle ? buildHandles(leaf, handleSide) : ''}
                `;
            }

            if (quote.systemType === 'corredera') {
                markersMarkup += buildSlidingMarker(leaf, index, quote);
            } else if (quote.systemType === 'abatible') {
                markersMarkup += buildCasementMarker(leaf, index, quote);
            } else if (quote.systemType === 'oscilobatiente') {
                markersMarkup += buildCasementMarker(leaf, index, quote, index === getTiltTurnLeafIndex(quote));
            }

            if (index < leaves.length - 1) {
                const barX = leaf.x + leaf.width;
                barsMarkup += `
                    <rect x="${barX}" y="${frame.innerY}" width="${barWidth}" height="${frame.innerHeight}"
                          fill="url(#alProfile)" stroke="rgba(60,70,78,0.6)" stroke-width="0.6"/>
                    <line x1="${barX + barWidth / 2}" y1="${frame.innerY + 3}"
                          x2="${barX + barWidth / 2}" y2="${frame.innerY + frame.innerHeight - 3}"
                          stroke="rgba(130,142,152,0.45)" stroke-width="0.5" fill="none"/>
                `;
            }
        });

        const dimGap  = 22;
        const bx = frame.outerX, by = frame.outerY, bw = frame.outerWidth, bh = frame.outerHeight;
        const dimY  = by + bh + dimGap;
        const dimX  = bx - dimGap;
        const dimCx = bx + bw / 2;
        const dimCy = by + bh / 2;
        const arrowW = 5, arrowH = 3;
        const dimMarkup = `
            <line x1="${bx}" y1="${dimY}" x2="${bx + bw}" y2="${dimY}" stroke="#333" stroke-width="0.7" fill="none"/>
            <line x1="${bx}" y1="${by + bh}" x2="${bx}" y2="${dimY + 3}" stroke="#555" stroke-width="0.5" fill="none"/>
            <line x1="${bx + bw}" y1="${by + bh}" x2="${bx + bw}" y2="${dimY + 3}" stroke="#555" stroke-width="0.5" fill="none"/>
            <polygon points="${bx},${dimY} ${bx + arrowW},${dimY - arrowH} ${bx + arrowW},${dimY + arrowH}" fill="#333"/>
            <polygon points="${bx + bw},${dimY} ${bx + bw - arrowW},${dimY - arrowH} ${bx + bw - arrowW},${dimY + arrowH}" fill="#333"/>
            <rect x="${dimCx - 28}" y="${dimY - 9}" width="56" height="14" fill="#fff"/>
            <text x="${dimCx}" y="${dimY + 4}" text-anchor="middle" fill="#1a1a1a"
                  font="400 9.5px 'Courier New',monospace" style="font:9.5px 'Courier New',monospace">${quote.widthMm} mm</text>
            <line x1="${dimX}" y1="${by}" x2="${dimX}" y2="${by + bh}" stroke="#333" stroke-width="0.7" fill="none"/>
            <line x1="${bx}" y1="${by}" x2="${dimX - 3}" y2="${by}" stroke="#555" stroke-width="0.5" fill="none"/>
            <line x1="${bx}" y1="${by + bh}" x2="${dimX - 3}" y2="${by + bh}" stroke="#555" stroke-width="0.5" fill="none"/>
            <polygon points="${dimX},${by} ${dimX - arrowH},${by + arrowW} ${dimX + arrowH},${by + arrowW}" fill="#333"/>
            <polygon points="${dimX},${by + bh} ${dimX - arrowH},${by + bh - arrowW} ${dimX + arrowH},${by + bh - arrowW}" fill="#333"/>
            <rect x="${dimX - 7}" y="${dimCy - 26}" width="14" height="52" fill="#fff"/>
            <text x="${dimX}" y="${dimCy + 4}" text-anchor="middle" fill="#1a1a1a"
                  transform="rotate(-90 ${dimX} ${dimCy})"
                  font="400 9.5px 'Courier New',monospace" style="font:9.5px 'Courier New',monospace">${quote.heightMm} mm</text>
        `;

        const tbX = 370, tbY = 348, tbW = 182, tbH = 78;
        const trimLabel = quote.trimSize > 0 ? `${quote.trimSize} mm` : 'Sin';
        const frameCutLabel = quote.frameCutType === 'mitered' ? '45°' : 'Recto';
        const titleBlock = `
            <rect x="${tbX}" y="${tbY}" width="${tbW}" height="${tbH}" fill="#fafbfc" stroke="#bbb" stroke-width="0.7"/>
            <line x1="${tbX}" y1="${tbY + 18}" x2="${tbX + tbW}" y2="${tbY + 18}" stroke="#bbb" stroke-width="0.5"/>
            <line x1="${tbX}" y1="${tbY + 34}" x2="${tbX + tbW}" y2="${tbY + 34}" stroke="#bbb" stroke-width="0.5"/>
            <line x1="${tbX}" y1="${tbY + 50}" x2="${tbX + tbW}" y2="${tbY + 50}" stroke="#bbb" stroke-width="0.5"/>
            <line x1="${tbX}" y1="${tbY + 64}" x2="${tbX + tbW}" y2="${tbY + 64}" stroke="#bbb" stroke-width="0.5"/>
            <rect x="${tbX + tbW - 16}" y="${tbY + 3}" width="12" height="12" fill="${palette.base}" stroke="#aaa" stroke-width="0.5"/>
            <text x="${tbX + 4}" y="${tbY + 13}" fill="#111" style="font:700 10.5px 'Courier New',monospace">${safeModel}</text>
            <text x="${tbX + 4}" y="${tbY + 29}" fill="#333" style="font:9px 'Courier New',monospace">Ref: ${safeReference} · Color: ${safeColor}</text>
            <text x="${tbX + 4}" y="${tbY + 46}" fill="#333" style="font:9px 'Courier New',monospace">${quote.widthMm} × ${quote.heightMm} mm · ${safeGlass}</text>
            <text x="${tbX + 4}" y="${tbY + 61}" fill="#333" style="font:9px 'Courier New',monospace">${quote.leaves} hj · Tapaj. ${trimLabel} · Corte ${frameCutLabel}</text>
            <text x="${tbX + 4}" y="${tbY + 75}" fill="#555" style="font:8.5px 'Courier New',monospace">Escala 1:${scaleN} · S28 EXTRUAL · ${quote.quantity} ud.</text>
        `;

        const barMm = 100;
        const barPx = Math.round(barMm * scale);
        const sbX = tbX, sbY = tbY - 20;
        const scaleBar = `
            <rect x="${sbX}" y="${sbY}" width="${barPx / 2}" height="5" fill="#333"/>
            <rect x="${sbX + barPx / 2}" y="${sbY}" width="${barPx / 2}" height="5" fill="#fff" stroke="#333" stroke-width="0.5"/>
            <text x="${sbX}" y="${sbY + 14}" fill="#444" style="font:8px 'Courier New',monospace">0</text>
            <text x="${sbX + barPx / 2 - 4}" y="${sbY + 14}" fill="#444" style="font:8px 'Courier New',monospace">50</text>
            <text x="${sbX + barPx - 2}" y="${sbY + 14}" fill="#444" style="font:8px 'Courier New',monospace">100mm</text>
        `;

        const svg = `
<svg viewBox="0 0 560 430" role="img" aria-label="Dibujo tecnico del cerramiento" xmlns="http://www.w3.org/2000/svg">
<defs>
  <pattern id="glassHatch" patternUnits="userSpaceOnUse" width="8" height="8" patternTransform="rotate(45 0 0)">
    <line x1="0" y1="0" x2="0" y2="8" stroke="#8bbcce" stroke-width="0.6" opacity="0.7"/>
  </pattern>
  <linearGradient id="alProfile" x1="0%" y1="0%" x2="100%" y2="100%">
    <stop offset="0%"   stop-color="${pLight}"/>
    <stop offset="40%"  stop-color="${palette.base}"/>
    <stop offset="100%" stop-color="${pDark}"/>
  </linearGradient>
</defs>
<rect width="560" height="430" fill="#ffffff"/>
${trimMarkup}
<rect x="${bx}" y="${by}" width="${bw}" height="${bh}"
      fill="url(#alProfile)" stroke="#2a2a2a" stroke-width="1.1"/>
<rect x="${bx + FT}" y="${by + FT}" width="${bw - FT * 2}" height="${bh - FT * 2}"
      fill="#ffffff" stroke="#666" stroke-width="0.5"/>
<rect x="${bx + FT + FI}" y="${by + FT + FI}" width="${bw - (FT + FI) * 2}" height="${bh - (FT + FI) * 2}"
      fill="none" stroke="#999" stroke-width="0.35"/>
${usesMiterCut ? buildMiterMarks(bx, by, bw, bh, Math.min(12, FT + 4)) : ''}
${leavesMarkup}
${barsMarkup}
${markersMarkup}
${dimMarkup}
${titleBlock}
${scaleBar}
</svg>`.trim();

        drawingWrap.innerHTML = svg;
        drawingSvgInput.value = svg;

        if (profilePreviewSwatch) {
            profilePreviewSwatch.style.background = `linear-gradient(135deg, ${palette.light}, ${palette.base} 60%, ${palette.dark})`;
            profilePreviewSwatch.style.borderColor = palette.shadow;
        }
        if (profilePreviewLabel) {
            profilePreviewLabel.textContent = quote.profileColor;
        }

        return svg;
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

    const serializeItemForSubmit = (item) => ({
        system_type: item.systemType,
        opening_type: item.openingType,
        carpentry_model: item.carpentryModelValue,
        carpentry_reference: item.carpentryReference,
        trim_size: item.trimSize,
        tilt_turn_leaf: item.tiltTurnLeaf,
        frame_cut_type: item.frameCutType,
        serie_key: item.serieKey,
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

        quoteItemsList.innerHTML = quoteItems.map((item, index) => `
            <article class="quote-item-card ${item.id === selectedItemId ? 'is-selected' : ''}" data-item-id="${item.id}">
                <div class="quote-item-card__head">
                    <button type="button" class="quote-item-select" data-select-item="${item.id}">
                        <strong>${text('item', 'Partida')} ${index + 1}</strong>
                        <span>${item.systemTypeLabel} · ${item.widthMm} x ${item.heightMm} mm</span>
                    </button>
                    ${quoteItems.length > 1 ? `<button type="button" class="quote-item-remove" data-remove-item="${item.id}">${text('removeItem', 'Eliminar ventana')}</button>` : ''}
                </div>
                <div class="quote-item-card__meta">
                    <span>${item.leaves} ${text('leavesShort', 'hojas')} · ${item.quantity} ${text('unitsShort', 'ud.')}</span>
                    <strong>${formatMoney(item.total)}</strong>
                </div>
                ${item.serieKey ? `
                <div class="quote-item-bom-bar">
                    <button type="button" class="secondary-button quote-item-bom-toggle"
                            data-bom-item="${item.id}">Ver descompuesto</button>
                    <span class="bom-serie-label">${item.serieKey === 's28_extrual' ? 'S28 · EXTRUAL' : item.serieKey}</span>
                </div>
                <div class="quote-item-bom-result" id="bom-${item.id}" hidden></div>
                ` : ''}
            </article>
        `).join('');
    };

    const loadItemIntoForm = (item) => {
        if (!item) {
            return;
        }

        suppressSync = true;
        fields.systemType.value = item.systemType;
        fields.openingType.value = item.openingType;
        fields.carpentryModel.value = item.carpentryModelValue;
        fields.carpentryReference.value = item.carpentryReference;
        fields.trimSize.value = String(item.trimSize);
        fields.tiltTurnLeaf.value = item.tiltTurnLeaf === 'derecha' ? 'derecha' : 'izquierda';
        fields.frameCutType.value = item.frameCutType === 'mitered' ? 'mitered' : 'recto';
        if (fields.serieKey) fields.serieKey.value = item.serieKey || '';
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

        const bomToggle = event.target.closest('[data-bom-item]');
        if (bomToggle) {
            const itemId = bomToggle.getAttribute('data-bom-item');
            const resultDiv = document.getElementById(`bom-${itemId}`);
            if (!resultDiv || !window.S28) return;
            if (resultDiv.hidden) {
                const it = quoteItems.find((i) => i.id === itemId);
                if (!it) return;
                const s28Key = window.S28.SYSTEM_MAP[it.systemType] || 'v1h_prac';
                const glassThick = (window.S28.GLASS_THICK_MAP[it.glassTypeValue] || 20);
                const bom = window.S28.buildBOM(s28Key, it.widthMm, it.heightMm, it.quantity, { glassThick });
                resultDiv.innerHTML = window.S28.renderBOM(bom);
            }
            resultDiv.hidden = !resultDiv.hidden;
            bomToggle.textContent = resultDiv.hidden ? 'Ver descompuesto' : 'Ocultar descompuesto';
            return;
        }

        const selectButton = event.target.closest('[data-select-item]');
        if (selectButton) {
            const nextItem = quoteItems.find((item) => item.id === selectButton.getAttribute('data-select-item'));
            if (nextItem) {
                selectedItemId = nextItem.id;
                loadItemIntoForm(nextItem);
            }
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

    // ── DESIGNER WIDGET ──────────────────────────────────
    const designerEmbed    = document.getElementById('designerEmbed');
    const designerSvgInput = document.getElementById('designerSvg');
    const designerTreeJson = document.getElementById('designerTreeJson');
    const dwApplySvg       = document.getElementById('dwApplySvg');
    const dwSlopeRow       = document.getElementById('dw-slopeRow');
    const dwShapeSelect    = document.getElementById('dw-shapeSelect');
    let dwReady = false;

    function syncDwDimensions() {
        if (dwReady && window.DesignerWidget) {
            const w = parseInt(fields.widthMm?.value || '1500', 10);
            const h = parseInt(fields.heightMm?.value || '1200', 10);
            window.DesignerWidget.setDimensions(w, h);
        }
    }

    if (designerEmbed && window.DesignerWidget) {
        designerEmbed.addEventListener('toggle', () => {
            if (designerEmbed.open) {
                if (!dwReady) {
                    dwReady = true;
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
                        onSvgChange: (svgStr, tree) => {
                            if (designerSvgInput) designerSvgInput.value = svgStr;
                            if (designerTreeJson) designerTreeJson.value = JSON.stringify(tree);
                        },
                        facadeW_val: parseInt(fields.widthMm?.value || '1500', 10),
                        facadeH_val: parseInt(fields.heightMm?.value || '1200', 10),
                    });
                } else {
                    syncDwDimensions();
                }
            }
        });

        dwShapeSelect?.addEventListener('change', () => {
            if (dwSlopeRow) dwSlopeRow.style.display = dwShapeSelect.value === 'trapezoidal' ? '' : 'none';
        });

        fields.widthMm?.addEventListener('input', syncDwDimensions);
        fields.heightMm?.addEventListener('input', syncDwDimensions);

        dwApplySvg?.addEventListener('click', () => {
            const svg = designerSvgInput?.value;
            if (svg && drawingWrap) {
                drawingWrap.innerHTML = svg;
                if (drawingSvgInput) drawingSvgInput.value = svg;
            }
        });
    }

    const carpentryRalInput = document.getElementById('carpentryRal');
    fields.profileColorPreset?.addEventListener('change', () => {
        const opt = fields.profileColorPreset.selectedOptions[0];
        const ral = opt?.dataset?.ral || '';
        if (carpentryRalInput) carpentryRalInput.value = ral;
    });
}