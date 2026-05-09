<?php

declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function get_current_lang(?array $source = null): string
{
    $candidate = $source['lang'] ?? $_GET['lang'] ?? $_POST['lang'] ?? 'es';

    return in_array($candidate, ['es', 'ca'], true) ? $candidate : 'es';
}

function tr(string $key, ?string $lang = null): string
{
    static $cache = [];

    $lang = $lang ?? get_current_lang();

    if (!isset($cache[$lang])) {
        $path = __DIR__ . ‘/../lang/’ . $lang . ‘.php’;
        $cache[$lang] = file_exists($path) ? (require $path) : [];
    }

    if (!isset($cache[‘es’])) {
        $path = __DIR__ . ‘/../lang/es.php’;
        $cache[‘es’] = file_exists($path) ? (require $path) : [];
    }

    return (string)($cache[$lang][$key] ?? $cache[‘es’][$key] ?? $key);
}

function url_with_lang(string $path, array $params = [], ?string $lang = null): string
{
    $lang = $lang ?? get_current_lang();
    $params['lang'] = $lang;
    $query = http_build_query($params);

    return $path . ($query !== '' ? '?' . $query : '');
}

function generate_quote_number(): string
{
    return 'P-' . date('Ymd-His') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

function get_carpentry_options(): array
{
    return [
        'corredera'  => 'Serie corredera',
        'abatible'   => 'Serie abatible',
        'fijo'       => 'Serie fijo',
        'oscilo'     => 'Serie oscilobatiente',
        'rpt'        => 'Serie RPT',
        'otra'       => 'other_carpentry',
    ];
}

function humanize_carpentry_model(string $value): string
{
    $map = get_carpentry_options();
    $label = $map[$value] ?? trim(str_replace('_', ' ', $value));

    return array_key_exists($value, $map) ? tr((string)$label) : $label;
}

function get_status_options(?string $lang = null): array
{
    return [
        'draft'    => tr('status_draft', $lang),
        'sent'     => tr('status_sent', $lang),
        'accepted' => tr('status_accepted', $lang),
        'rejected' => tr('status_rejected', $lang),
        'ordered'  => tr('status_ordered', $lang),
    ];
}

function get_status_css_class(string $status): string
{
    return match ($status) {
        'accepted' => 'status-badge--accepted',
        'rejected' => 'status-badge--rejected',
        'sent'     => 'status-badge--sent',
        'ordered'  => 'status-badge--ordered',
        default    => 'status-badge--draft',
    };
}

function humanize_system_type(string $value, ?string $lang = null): string
{
    $map = [
        'corredera' => 'sliding',
        'abatible' => 'casement',
        'fijo' => 'fixed',
        'oscilobatiente' => 'tilt_turn',
        'multiple' => 'multiple_system',
    ];

    return isset($map[$value]) ? tr($map[$value], $lang) : trim(str_replace('_', ' ', $value));
}

function humanize_opening_type(string $value, ?string $lang = null): string
{
    $map = [
        'izquierda' => 'left',
        'derecha' => 'right',
        'central' => 'center',
        'multiple' => 'multiple_values',
    ];

    return isset($map[$value]) ? tr($map[$value], $lang) : trim(str_replace('_', ' ', $value));
}

function humanize_tilt_turn_leaf(string $value, ?string $lang = null): string
{
    $map = [
        'izquierda' => 'left',
        'derecha' => 'right',
        'unica' => 'only_leaf',
    ];

    return isset($map[$value]) ? tr($map[$value], $lang) : trim(str_replace('_', ' ', $value));
}

function get_default_glass_price_catalog(): array
{
    return [
        'camara_4_12_4' => 38.00,
        'camara_4_16_4' => 40.00,
        'camara_4_4_12_4' => 42.00,
        'camara_4_4_16_4' => 45.00,
        'camara_6_12_6' => 48.00,
        'camara_6_16_6' => 52.00,
        'laminar_3_3' => 34.00,
        'laminar_4_4' => 39.00,
        'laminar_5_5' => 48.00,
        'laminar_6_6' => 56.00,
        'bajo_emisivo_4_16_4' => 49.00,
        'bajo_emisivo_4_4_16_4' => 54.00,
        'bajo_emisivo_6_16_4' => 58.00,
        'control_solar_4_16_4' => 53.00,
        'control_solar_4_4_16_4' => 58.00,
        'control_solar_6_16_6' => 64.00,
        'acustico_4_4_16_4' => 57.00,
        'acustico_5_5_16_6' => 66.00,
        'acustico_6_6_16_6' => 74.00,
        'templado_6' => 36.00,
        'templado_8' => 44.00,
        'templado_10' => 52.00,
        'triple_4_10_4_10_4' => 68.00,
        'triple_4_12_4_12_4' => 74.00,
        'monolitico_4' => 22.00,
        'monolitico_6' => 28.00,
        'otro' => 42.00,
    ];
}

function get_glass_options(): array
{
    return [
        'Camaras' => [
            'camara_4_12_4' => 'Camara 4/12/4',
            'camara_4_16_4' => 'Camara 4/16/4',
            'camara_4_4_12_4' => 'Camara 4+4/12/4',
            'camara_4_4_16_4' => 'Camara 4+4/16/4',
            'camara_6_12_6' => 'Camara 6/12/6',
            'camara_6_16_6' => 'Camara 6/16/6',
        ],
        'Laminados' => [
            'laminar_3_3' => 'Laminar 3+3',
            'laminar_4_4' => 'Laminar 4+4',
            'laminar_5_5' => 'Laminar 5+5',
            'laminar_6_6' => 'Laminar 6+6',
        ],
        'Bajo Emisivo' => [
            'bajo_emisivo_4_16_4' => 'Bajo emisivo 4/16/4',
            'bajo_emisivo_4_4_16_4' => 'Bajo emisivo 4+4/16/4',
            'bajo_emisivo_6_16_4' => 'Bajo emisivo 6/16/4',
        ],
        'Control Solar' => [
            'control_solar_4_16_4' => 'Control solar 4/16/4',
            'control_solar_4_4_16_4' => 'Control solar 4+4/16/4',
            'control_solar_6_16_6' => 'Control solar 6/16/6',
        ],
        'Acusticos' => [
            'acustico_4_4_16_4' => 'Acustico 4+4/16/4',
            'acustico_5_5_16_6' => 'Acustico 5+5/16/6',
            'acustico_6_6_16_6' => 'Acustico 6+6/16/6',
        ],
        'Templados' => [
            'templado_6' => 'Templado 6 mm',
            'templado_8' => 'Templado 8 mm',
            'templado_10' => 'Templado 10 mm',
        ],
        'Triples' => [
            'triple_4_10_4_10_4' => 'Triple 4/10/4/10/4',
            'triple_4_12_4_12_4' => 'Triple 4/12/4/12/4',
        ],
        'Otros' => [
            'monolitico_4' => 'Monolitico 4 mm',
            'monolitico_6' => 'Monolitico 6 mm',
            'otro' => 'Otro vidrio',
        ],
    ];
}

function get_glass_group_translation_key(string $group): string
{
    $map = [
        'Camaras' => 'glass_group_camaras',
        'Laminados' => 'glass_group_laminados',
        'Bajo Emisivo' => 'glass_group_lowe',
        'Control Solar' => 'glass_group_solar',
        'Acusticos' => 'glass_group_acoustic',
        'Templados' => 'glass_group_tempered',
        'Triples' => 'glass_group_triple',
    ];

    return $map[$group] ?? 'glass_group_other';
}

function flatten_glass_options(): array
{
    $flattened = [];

    foreach (get_glass_options() as $group => $options) {
        foreach ($options as $value => $label) {
            $flattened[$value] = $label;
        }
    }

    return $flattened;
}

function humanize_glass_type(string $value): string
{
    $map = flatten_glass_options();

    return $map[$value] ?? trim(str_replace('_', ' ', $value));
}

function build_quote_item_config(array $data, array $calc): array
{
    $pricingMode = (string)($data['pricing_mode'] ?? 'fabricada');
    $commercialMarginPct = max(0.0, (float)($data['commercial_margin_pct'] ?? ($data['margin_pct'] ?? 0)));
    $purchasedUnitCost = max(0.0, (float)($data['purchased_unit_cost'] ?? 0));
    $hardwareCostPerUnit = max(0.0, (float)($data['hardware_cost'] ?? 0));
    $installationCostPerUnit = max(0.0, (float)($data['installation_cost'] ?? 0));
    $internalExtraCost = max(0.0, (float)($data['internal_extra_cost'] ?? 0));

    return [
        'system_type' => trim((string)($data['system_type'] ?? 'corredera')),
        'opening_type' => trim((string)($data['opening_type'] ?? 'izquierda')),
        'pricing_mode' => $pricingMode,
        'is_factory_finished' => !empty($data['is_factory_finished']),
        'purchased_unit_cost' => round($purchasedUnitCost, 2),
        'hardware_cost' => round($hardwareCostPerUnit, 2),
        'installation_cost' => round($installationCostPerUnit, 2),
        'internal_extra_cost' => round($internalExtraCost, 2),
        'commercial_margin_pct' => round($commercialMarginPct, 2),
        'margin_pct' => $calc['margin_pct'],
        'iva_pct' => $calc['iva_pct'],
        'carpentry_model' => humanize_carpentry_model(trim((string)($data['carpentry_model'] ?? ''))),
        'carpentry_reference' => trim((string)($data['carpentry_reference'] ?? '')),
        'trim_size' => max(0, (int)($data['trim_size'] ?? 0)),
        'tilt_turn_leaf' => (string)($calc['tilt_turn_leaf'] ?? ''),
        'tilt_turn_leaf_label' => humanize_tilt_turn_leaf((string)($calc['tilt_turn_leaf'] ?? '')),
        'frame_cut_type' => trim((string)($data['frame_cut_type'] ?? 'recto')),
        'profile_color_hex' => trim((string)($data['profile_color_hex'] ?? '')),
        'profile_color_name' => trim((string)($data['profile_color'] ?? '')),
        'profile_color' => trim((string)($data['profile_color'] ?? '')),
        'width_mm' => $calc['width_mm'],
        'height_mm' => $calc['height_mm'],
        'leaves' => $calc['leaves'],
        'quantity' => $calc['quantity'],
        'aluminum_ml' => $calc['aluminum_ml'],
        'glass_m2' => $calc['glass_m2'],
        'glass_type_label' => humanize_glass_type(trim((string)($data['glass_type'] ?? ''))),
        'glass_type' => trim((string)($data['glass_type'] ?? '')),
        'glass_description' => trim((string)($data['glass_description'] ?? '')),
        'glass_width_mm' => $calc['glass_width_mm'],
        'glass_height_mm' => $calc['glass_height_mm'],
        'glass_panels' => $calc['glass_panels'],
        'glass_piece_area_m2' => $calc['glass_piece_area_m2'],
        'glass_cost' => $calc['glass_cost'],
        'pricing_breakdown' => [
            'base_cost' => $calc['base_cost'],
            'base_cost_label' => $calc['base_cost_label'],
        ],
        'subtotal' => $calc['subtotal'],
        'margin_amount' => $calc['margin_amount'],
        'taxable_base' => $calc['taxable_base'],
        'iva_amount' => $calc['iva_amount'],
        'total' => $calc['total'],
        'drawing_svg' => trim((string)($data['drawing_svg'] ?? '')),
    ];
}

function get_quote_items_from_request(array $data): array
{
    $rawItems = $data['quote_items_json'] ?? null;

    if (is_string($rawItems) && trim($rawItems) !== '') {
        $decoded = json_decode($rawItems, true);
        if (is_array($decoded) && $decoded !== []) {
            return array_values(array_filter($decoded, static fn ($item): bool => is_array($item)));
        }
    }

    return [$data];
}

function calculate_quote_item(array $data): array
{
    $widthMm = max(300, (int)($data['width_mm'] ?? 0));
    $heightMm = max(300, (int)($data['height_mm'] ?? 0));
    $systemType = (string)($data['system_type'] ?? 'corredera');
    $leaves = min(6, max(1, (int)($data['leaves'] ?? 1)));
    if ($systemType === 'fijo') {
        $leaves = 1;
    }
    $tiltTurnLeaf = trim((string)($data['tilt_turn_leaf'] ?? 'izquierda'));
    if ($systemType === 'oscilobatiente') {
        $leaves = min(2, max(1, $leaves));
        $tiltTurnLeaf = $leaves === 1 ? 'unica' : ($tiltTurnLeaf === 'derecha' ? 'derecha' : 'izquierda');
    } else {
        $tiltTurnLeaf = '';
    }
    $quantity = max(1, (int)($data['quantity'] ?? 1));
    $glassWidthMm = max(1, (int)($data['glass_width_mm'] ?? $widthMm));
    $glassHeightMm = max(1, (int)($data['glass_height_mm'] ?? $heightMm));
    $glassPanels = max(1, (int)($data['glass_panels'] ?? $leaves));

    $aluminumPriceMl = max(0.0, (float)($data['aluminum_price_ml'] ?? 0));
    $glassPriceM2 = max(0.0, (float)($data['glass_price_m2'] ?? 0));
    $laborCost = max(0.0, (float)($data['labor_cost'] ?? 0));
    $hardwareCost = max(0.0, (float)($data['hardware_cost'] ?? 0));
    $installationCost = max(0.0, (float)($data['installation_cost'] ?? 0));
    $internalExtraCost = max(0.0, (float)($data['internal_extra_cost'] ?? 0));
    $marginPct = max(0.0, (float)($data['margin_pct'] ?? 0));
    $commercialMarginPct = max(0.0, (float)($data['commercial_margin_pct'] ?? $marginPct));
    $ivaPct = max(0.0, (float)($data['iva_pct'] ?? 0));
    $pricingMode = (string)($data['pricing_mode'] ?? 'fabricada');
    $purchasedUnitCost = max(0.0, (float)($data['purchased_unit_cost'] ?? 0));

    $widthM = $widthMm / 1000;
    $heightM = $heightMm / 1000;

    $frameMl = ($widthM * 2) + ($heightM * 2);
    $leafDividerMl = max(0, $leaves - 1) * $heightM;
    $leafPerimeterMl = $leaves * ((($widthM / $leaves) * 2) + ($heightM * 2));
    $aluminumMl = round(($frameMl + $leafDividerMl + ($leafPerimeterMl * 0.35)) * $quantity, 3);

    $glassPieceAreaM2 = round(($glassWidthMm / 1000) * ($glassHeightMm / 1000), 3);
    $glassM2 = round($glassPieceAreaM2 * $glassPanels * $quantity, 3);

    $aluminumCost = $aluminumMl * $aluminumPriceMl;
    $glassCost = $glassM2 * $glassPriceM2;
    $hardwareTotalCost = $hardwareCost * $quantity;
    $installationTotalCost = $installationCost * $quantity;
    $fabricatedBaseCost = $aluminumCost + $glassCost + $laborCost + $hardwareTotalCost + $installationTotalCost + $internalExtraCost;
    $purchasedBaseCost = ($purchasedUnitCost * $quantity) + $hardwareTotalCost + $installationTotalCost + $internalExtraCost;

    $baseCost = $pricingMode === 'comprada' ? $purchasedBaseCost : $fabricatedBaseCost;
    $effectiveMarginPct = $pricingMode === 'comprada' ? $commercialMarginPct : $marginPct;

    $subtotal = round($baseCost, 2);
    $marginAmount = round($subtotal * ($effectiveMarginPct / 100), 2);
    $taxableBase = round($subtotal + $marginAmount, 2);
    $ivaAmount = round($taxableBase * ($ivaPct / 100), 2);
    $total = round($taxableBase + $ivaAmount, 2);

    return [
        'width_mm' => $widthMm,
        'height_mm' => $heightMm,
        'leaves' => $leaves,
        'quantity' => $quantity,
        'glass_width_mm' => $glassWidthMm,
        'glass_height_mm' => $glassHeightMm,
        'glass_panels' => $glassPanels,
        'tilt_turn_leaf' => $tiltTurnLeaf,
        'glass_piece_area_m2' => $glassPieceAreaM2,
        'aluminum_price_ml' => round($aluminumPriceMl, 2),
        'glass_price_m2' => round($glassPriceM2, 2),
        'glass_cost' => round($glassCost, 2),
        'labor_cost' => round($laborCost, 2),
        'hardware_cost' => round($hardwareCost, 2),
        'hardware_total_cost' => round($hardwareTotalCost, 2),
        'installation_cost' => round($installationCost, 2),
        'installation_total_cost' => round($installationTotalCost, 2),
        'internal_extra_cost' => round($internalExtraCost, 2),
        'margin_pct' => round($effectiveMarginPct, 2),
        'commercial_margin_pct' => round($commercialMarginPct, 2),
        'purchased_unit_cost' => round($purchasedUnitCost, 2),
        'pricing_mode' => $pricingMode,
        'iva_pct' => round($ivaPct, 2),
        'aluminum_ml' => $aluminumMl,
        'glass_m2' => $glassM2,
        'base_cost' => round($baseCost, 2),
        'base_cost_label' => $pricingMode === 'comprada' ? 'Coste compra / Cost de compra' : 'Coste fabricación / Cost de fabricació',
        'subtotal' => $subtotal,
        'margin_amount' => $marginAmount,
        'taxable_base' => $taxableBase,
        'iva_amount' => $ivaAmount,
        'total' => $total,
    ];
}

function calculate_quote(array $data): array
{
    $items = get_quote_items_from_request($data);
    $itemCount = count($items);
    $calculatedItems = [];
    $firstItem = $items[0] ?? $data;
    $firstCalc = null;

    $totals = [
        'quantity' => 0,
        'aluminum_ml' => 0.0,
        'glass_m2' => 0.0,
        'base_cost' => 0.0,
        'glass_cost' => 0.0,
        'subtotal' => 0.0,
        'margin_amount' => 0.0,
        'taxable_base' => 0.0,
        'iva_amount' => 0.0,
        'total' => 0.0,
    ];

    foreach ($items as $item) {
        $itemCalc = calculate_quote_item($item);
        $calculatedItems[] = [
            'input' => $item,
            'calc' => $itemCalc,
        ];

        if ($firstCalc === null) {
            $firstCalc = $itemCalc;
        }

        $totals['quantity'] += (int)$itemCalc['quantity'];
        $totals['aluminum_ml'] += (float)$itemCalc['aluminum_ml'];
        $totals['glass_m2'] += (float)$itemCalc['glass_m2'];
        $totals['base_cost'] += (float)$itemCalc['base_cost'];
        $totals['glass_cost'] += (float)$itemCalc['glass_cost'];
        $totals['subtotal'] += (float)$itemCalc['subtotal'];
        $totals['margin_amount'] += (float)$itemCalc['margin_amount'];
        $totals['taxable_base'] += (float)$itemCalc['taxable_base'];
        $totals['iva_amount'] += (float)$itemCalc['iva_amount'];
        $totals['total'] += (float)$itemCalc['total'];
    }

    $firstCalc = $firstCalc ?? calculate_quote_item($firstItem);
    $summaryLabel = $itemCount > 1 ? 'multiple_values' : null;

    return [
        'item_count' => $itemCount,
        'items' => $calculatedItems,
        'system_type' => $itemCount > 1 ? 'multiple' : trim((string)($firstItem['system_type'] ?? 'corredera')),
        'opening_type' => $itemCount > 1 ? 'multiple' : trim((string)($firstItem['opening_type'] ?? 'izquierda')),
        'profile_color' => $itemCount > 1 ? tr('multiple_values') : trim((string)($firstItem['profile_color'] ?? '')),
        'glass_type' => $itemCount > 1 ? tr('multiple_values') : humanize_glass_type(trim((string)($firstItem['glass_type'] ?? ''))),
        'drawing_svg' => trim((string)($firstItem['drawing_svg'] ?? ($data['drawing_svg'] ?? ''))),
        'width_mm' => (int)$firstCalc['width_mm'],
        'height_mm' => (int)$firstCalc['height_mm'],
        'leaves' => (int)$firstCalc['leaves'],
        'quantity' => (int)$totals['quantity'],
        'glass_width_mm' => (int)$firstCalc['glass_width_mm'],
        'glass_height_mm' => (int)$firstCalc['glass_height_mm'],
        'glass_panels' => (int)$firstCalc['glass_panels'],
        'glass_piece_area_m2' => (float)$firstCalc['glass_piece_area_m2'],
        'aluminum_price_ml' => (float)$firstCalc['aluminum_price_ml'],
        'glass_price_m2' => (float)$firstCalc['glass_price_m2'],
        'glass_cost' => round($totals['glass_cost'], 2),
        'labor_cost' => (float)$firstCalc['labor_cost'],
        'internal_extra_cost' => (float)$firstCalc['internal_extra_cost'],
        'margin_pct' => (float)$firstCalc['margin_pct'],
        'commercial_margin_pct' => (float)$firstCalc['commercial_margin_pct'],
        'purchased_unit_cost' => (float)$firstCalc['purchased_unit_cost'],
        'pricing_mode' => (string)$firstCalc['pricing_mode'],
        'iva_pct' => (float)$firstCalc['iva_pct'],
        'aluminum_ml' => round($totals['aluminum_ml'], 3),
        'glass_m2' => round($totals['glass_m2'], 3),
        'base_cost' => round($totals['base_cost'], 2),
        'base_cost_label' => $itemCount > 1 ? tr('quote_items') : (string)$firstCalc['base_cost_label'],
        'subtotal' => round($totals['subtotal'], 2),
        'margin_amount' => round($totals['margin_amount'], 2),
        'taxable_base' => round($totals['taxable_base'], 2),
        'iva_amount' => round($totals['iva_amount'], 2),
        'total' => round($totals['total'], 2),
    ];
}

function build_quote_config(array $data, array $calc): array
{
    $itemsConfig = [];

    foreach ($calc['items'] as $index => $itemBundle) {
        $itemConfig = build_quote_item_config($itemBundle['input'], $itemBundle['calc']);
        $itemConfig['item_number'] = $index + 1;
        $itemsConfig[] = $itemConfig;
    }

    $firstItem = $itemsConfig[0] ?? [];

    return array_merge($firstItem, [
        'item_count' => $calc['item_count'],
        'total_quantity' => $calc['quantity'],
        'items' => $itemsConfig,
        'quote_totals' => [
            'aluminum_ml' => $calc['aluminum_ml'],
            'glass_m2' => $calc['glass_m2'],
            'glass_cost' => $calc['glass_cost'],
            'subtotal' => $calc['subtotal'],
            'margin_amount' => $calc['margin_amount'],
            'taxable_base' => $calc['taxable_base'],
            'iva_amount' => $calc['iva_amount'],
            'total' => $calc['total'],
        ],
    ]);
}