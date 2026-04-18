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
        const safeReference = escapeSvgText(quote.carpentryReference || text('noReference', 'Sin referencia'));
        const safeColor = escapeSvgText(quote.profileColor);
        const palette = getProfilePalette(quote.profileColorHex);
        const glassPalette = getGlassPalette();
        const frame = {
            outerX: 160,
            outerY: 122,
            outerWidth: 238,
            outerHeight: 198,
            innerX: 174,
            innerY: 136,
            innerWidth: 210,
            innerHeight: 170,
        };
        const { geometry: leaves, barWidth } = getLeafGeometry(quote, frame);
        const trimOffset = getTrimOffset(quote.trimSize);
        const trimLabel = quote.trimSize > 0 ? `${quote.trimSize} mm` : text('trimNone', 'Sin');
        const frameCutLabel = quote.frameCutType === 'mitered' ? text('cutMitered', '45 grados') : text('cutRecto', 'Recto');
        const usesMiterCut = quote.systemType === 'fijo' || quote.frameCutType === 'mitered';

        let leavesMarkup = '';
        let barsMarkup = '';
        let markersMarkup = '';
        let trimMarkup = '';

        if (trimOffset > 0) {
            trimMarkup = `
                <rect x="${frame.outerX - trimOffset}" y="${frame.outerY - trimOffset}" width="${frame.outerWidth + (trimOffset * 2)}" height="${frame.outerHeight + (trimOffset * 2)}" class="trim-band" />
                <rect x="${frame.outerX - trimOffset + 3}" y="${frame.outerY - trimOffset + 3}" width="${frame.outerWidth + (trimOffset * 2) - 6}" height="${frame.outerHeight + (trimOffset * 2) - 6}" class="trim-outline" />
            `;
        }

        leaves.forEach((leaf, index) => {
            const slidingDirection = getSlidingDirection(quote.openingType, index, quote.leaves);
            const casementHingeSide = getCasementHingeSide(quote.openingType, index, quote.leaves);
            const isCasementFamily = quote.systemType === 'abatible' || quote.systemType === 'oscilobatiente';
            const handleSide = quote.systemType === 'corredera'
                ? (slidingDirection === 1 ? 'left' : 'right')
                : getCasementHandleSide(quote);
            const shouldRenderHandle = quote.systemType === 'corredera'
                || (isCasementFamily && index === getCasementHandleLeafIndex(quote));

            if (quote.systemType === 'fijo') {
                leavesMarkup += `
                    <rect x="${leaf.x + 4}" y="${leaf.y + 4}" width="${leaf.width - 8}" height="${leaf.height - 8}" class="fixed-lite-frame" />
                    <rect x="${leaf.x + 10}" y="${leaf.y + 10}" width="${leaf.width - 20}" height="${leaf.height - 20}" class="glass-pane" />
                    <rect x="${leaf.x + 14}" y="${leaf.y + 14}" width="${leaf.width - 28}" height="${leaf.height - 28}" class="glass-shine" />
                `;
            } else {
                const sashInset = quote.systemType === 'corredera' ? 8 : 6;
                const glassInsetX = quote.systemType === 'corredera' ? 14 : 12;
                const glassInsetY = quote.systemType === 'corredera' ? 12 : 10;
                leavesMarkup += `
                    <rect x="${leaf.x}" y="${leaf.y}" width="${leaf.width}" height="${leaf.height}" class="sash" />
                    <rect x="${leaf.x + sashInset}" y="${leaf.y + sashInset}" width="${leaf.width - (sashInset * 2)}" height="${leaf.height - (sashInset * 2)}" class="sash-inner" />
                    <rect x="${leaf.x + glassInsetX}" y="${leaf.y + glassInsetY}" width="${leaf.width - (glassInsetX * 2)}" height="${leaf.height - (glassInsetY * 2)}" class="glass-pane" />
                    <rect x="${leaf.x + glassInsetX + 3}" y="${leaf.y + glassInsetY + 3}" width="${leaf.width - ((glassInsetX + 3) * 2)}" height="${leaf.height - ((glassInsetY + 3) * 2)}" class="glass-shine" />
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
                    <rect x="${barX}" y="${frame.innerY}" width="${barWidth}" height="${frame.innerHeight}" class="meeting-rail" />
                    <line x1="${barX + (barWidth / 2)}" y1="${frame.innerY + 4}" x2="${barX + (barWidth / 2)}" y2="${frame.innerY + frame.innerHeight - 4}" class="technical-line" />
                `;
            }
        });

        const tiltTurnDetail = quote.systemType === 'oscilobatiente'
            ? ` · ${text('tiltTurnLeafShort', 'Oscilo')} ${quote.tiltTurnLeafLabel}`
            : '';
        const detailsLabel = `${quote.leaves} ${text('leavesShort', 'hojas')} · ${quote.quantity} ${text('unitsShort', 'ud.')} · ${text('sheetTrim', 'Tapajuntas')} ${trimLabel} · ${text('sheetCut', 'Corte')} ${frameCutLabel}${tiltTurnDetail}`;
        const safeGlass = escapeSvgText(quote.glassDescription || quote.glassType || text('glass', 'Vidrio'));
        const verticalTextX = frame.outerX + frame.outerWidth + 38;
        const verticalTextY = frame.outerY + (frame.outerHeight / 2);

        const svg = `
            <svg viewBox="0 0 560 430" role="img" aria-label="Dibujo tecnico del cerramiento" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="frameFill" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="${mixColor(palette.base, '#ffffff', 0.72)}" />
                        <stop offset="100%" stop-color="${mixColor(palette.base, '#d9e0e6', 0.2)}" />
                    </linearGradient>
                    <linearGradient id="glassFill" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#f6fbfc" />
                        <stop offset="100%" stop-color="#d9eef0" />
                    </linearGradient>
                    <style>
                        .sheet { fill: #ffffff; }
                        .title { fill: #111111; font: 700 13px 'Trebuchet MS', Arial, sans-serif; }
                        .meta { fill: #40454a; font: 11px 'Trebuchet MS', Arial, sans-serif; }
                        .caption { fill: #40454a; font: 11px 'Trebuchet MS', Arial, sans-serif; }
                        .trim-band { fill: #fafcfd; stroke: rgba(78, 90, 101, 0.34); stroke-width: 0.8; }
                        .trim-outline { fill: none; stroke: rgba(78, 90, 101, 0.16); stroke-width: 0.7; }
                        .profile-frame { fill: url(#frameFill); stroke: rgba(59, 70, 79, 0.86); stroke-width: 1.05; }
                        .profile-inner-edge { fill: none; stroke: rgba(98, 110, 120, 0.38); stroke-width: 0.7; }
                        .miter-line { stroke: rgba(95, 104, 112, 0.5); stroke-width: 0.85; fill: none; stroke-linecap: round; }
                        .sash { fill: url(#frameFill); stroke: rgba(65, 75, 84, 0.84); stroke-width: 0.95; }
                        .sash-inner { fill: none; stroke: rgba(255,255,255,0.55); stroke-width: 0.55; }
                        .meeting-rail { fill: #edf2f5; stroke: rgba(77, 88, 98, 0.5); stroke-width: 0.75; }
                        .fixed-lite-frame { fill: none; stroke: rgba(59, 70, 79, 0.76); stroke-width: 0.9; }
                        .glass-pane { fill: url(#glassFill); stroke: rgba(100, 154, 159, 0.7); stroke-width: 0.7; }
                        .glass-shine { fill: none; stroke: rgba(255, 255, 255, 0.45); stroke-width: 0.45; }
                        .technical-line { stroke: rgba(120, 131, 141, 0.5); stroke-width: 0.55; fill: none; }
                        .opening-line-strong { stroke: #4c565f; stroke-width: 1.45; fill: none; stroke-linecap: round; stroke-linejoin: round; }
                        .hinge-line { stroke: #5d666f; stroke-width: 1.3; fill: none; stroke-linecap: round; }
                        .swing-arc { stroke: #6f7981; stroke-width: 1.15; fill: none; stroke-dasharray: 4 3; }
                        .tilt-mark { stroke: #5f6972; stroke-width: 1.2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
                        .marker-tag { fill: rgba(255,255,255,0.92); stroke: rgba(99, 109, 118, 0.6); stroke-width: 0.8; }
                        .marker-label { fill: #2d2d2d; font: 700 13px 'Trebuchet MS', Arial, sans-serif; }
                        .handle { fill: rgba(255,255,255,0.96); stroke: rgba(111, 120, 128, 0.55); stroke-width: 0.8; }
                        .handle-line { stroke: rgba(111, 120, 128, 0.55); stroke-width: 0.8; }
                        .dimension { stroke: rgba(76, 86, 95, 0.8); stroke-width: 0.9; fill: none; }
                        .dimension-text { fill: #222222; font: 400 13px 'Trebuchet MS', Arial, sans-serif; }
                    </style>
                </defs>
                <rect x="0" y="0" width="560" height="430" class="sheet" />
                <text x="24" y="28" class="title">${safeModel}</text>
                <text x="24" y="48" class="meta">${text('sheetSeries', 'Serie')}: ${safeReference}</text>
                <text x="24" y="66" class="meta">${text('sheetColor', 'Color')}: ${safeColor}</text>
                <text x="24" y="84" class="meta">${text('sheetTrim', 'Tapajuntas')}: ${trimLabel}</text>
                <text x="24" y="102" class="meta">${text('sheetCut', 'Corte')}: ${frameCutLabel}</text>
                <text x="24" y="120" class="meta">${text('sheetGlass', 'Vidrio')}: ${safeGlass}</text>
                <text x="24" y="138" class="meta">${text('sheetSize', 'Medida')}: ${quote.widthMm} x ${quote.heightMm} mm</text>
                <text x="24" y="156" class="caption">${detailsLabel}</text>
                ${trimMarkup}
                <rect x="${frame.outerX}" y="${frame.outerY}" width="${frame.outerWidth}" height="${frame.outerHeight}" class="profile-frame" />
                <rect x="${frame.innerX}" y="${frame.innerY}" width="${frame.innerWidth}" height="${frame.innerHeight}" class="profile-inner-edge" />
                ${leavesMarkup}
                ${barsMarkup}
                ${markersMarkup}
                <line x1="${frame.outerX}" y1="${frame.outerY + frame.outerHeight + 24}" x2="${frame.outerX + frame.outerWidth}" y2="${frame.outerY + frame.outerHeight + 24}" class="dimension" />
                <line x1="${frame.outerX}" y1="${frame.outerY + frame.outerHeight}" x2="${frame.outerX}" y2="${frame.outerY + frame.outerHeight + 24}" class="dimension" />
                <line x1="${frame.outerX + frame.outerWidth}" y1="${frame.outerY + frame.outerHeight}" x2="${frame.outerX + frame.outerWidth}" y2="${frame.outerY + frame.outerHeight + 24}" class="dimension" />
                <text x="${frame.outerX + (frame.outerWidth / 2)}" y="${frame.outerY + frame.outerHeight + 42}" text-anchor="middle" class="dimension-text">H1=${quote.widthMm}</text>
                <line x1="${frame.outerX + frame.outerWidth + 18}" y1="${frame.outerY}" x2="${frame.outerX + frame.outerWidth + 18}" y2="${frame.outerY + frame.outerHeight}" class="dimension" />
                <line x1="${frame.outerX + frame.outerWidth}" y1="${frame.outerY}" x2="${frame.outerX + frame.outerWidth + 18}" y2="${frame.outerY}" class="dimension" />
                <line x1="${frame.outerX + frame.outerWidth}" y1="${frame.outerY + frame.outerHeight}" x2="${frame.outerX + frame.outerWidth + 18}" y2="${frame.outerY + frame.outerHeight}" class="dimension" />
                <text x="${verticalTextX}" y="${verticalTextY}" text-anchor="middle" class="dimension-text" transform="rotate(90 ${verticalTextX} ${verticalTextY})">V1=${quote.heightMm}</text>
            </svg>
        `;

        drawingWrap.innerHTML = svg;
        drawingSvgInput.value = svg.trim();

        if (profilePreviewSwatch) {
            profilePreviewSwatch.style.background = `linear-gradient(135deg, ${palette.light}, ${palette.base} 60%, ${palette.dark})`;
            profilePreviewSwatch.style.borderColor = palette.shadow;
        }
        if (profilePreviewLabel) {
            profilePreviewLabel.textContent = quote.profileColor;
        }

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

    const serializeItemForSubmit = (item) => ({
        system_type: item.systemType,
        opening_type: item.openingType,
        carpentry_model: item.carpentryModelValue,
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
}