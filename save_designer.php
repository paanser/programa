<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$lang = get_current_lang($_POST);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$clientName = trim((string)($_POST['client_name'] ?? ''));
if ($clientName === '') {
    http_response_code(422);
    echo 'El nombre del cliente es obligatorio.';
    exit;
}

$elementsJson = (string)($_POST['elements_json'] ?? '[]');
$drawingSvg   = (string)($_POST['drawing_svg']   ?? '');
$total        = (float)($_POST['total']          ?? 0);
$aluminumMl   = (float)($_POST['aluminum_ml']    ?? 0);
$glassM2      = (float)($_POST['glass_m2']       ?? 0);

$elements = json_decode($elementsJson, true) ?: [];

// Derive fields from first element for legacy columns
$firstEl      = $elements[0] ?? [];
$systemType   = $firstEl['carpentry'] ?? 'extrual_s28';
$profileColor = ($firstEl['colorRal'] ?? '') ? 'RAL '.($firstEl['colorRal'] ?? '') : ($firstEl['colorName'] ?? '');
$widthMm      = (int)($firstEl['facadeW'] ?? 0);
$heightMm     = (int)($firstEl['facadeH'] ?? 0);
$qty          = (int)($firstEl['qty'] ?? 1);

$quoteNumber = generate_quote_number();
$createdAt   = date('Y-m-d H:i:s');

$configJson = json_encode([
    'version'  => 2,
    'elements' => $elements,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

try {
    $pdo = get_pdo();

    $sql = 'INSERT INTO quotes (
        quote_number, created_at, client_name,
        system_type, opening_type, profile_color, glass_type,
        width_mm, height_mm, leaves, quantity,
        aluminum_price_ml, glass_price_m2, labor_cost, margin_pct, iva_pct,
        aluminum_ml, glass_m2,
        subtotal, margin_amount, taxable_base, iva_amount, total,
        drawing_svg, config_json
    ) VALUES (
        :quote_number, :created_at, :client_name,
        :system_type, :opening_type, :profile_color, :glass_type,
        :width_mm, :height_mm, :leaves, :quantity,
        :aluminum_price_ml, :glass_price_m2, :labor_cost, :margin_pct, :iva_pct,
        :aluminum_ml, :glass_m2,
        :subtotal, :margin_amount, :taxable_base, :iva_amount, :total,
        :drawing_svg, :config_json
    )';

    // Derive pricing defaults from first element
    $alPrice   = (float)($firstEl['alPriceMl']     ?? 18);
    $glassPrice = (float)($firstEl['glassPriceM2'] ?? 35);
    $laborCost  = (float)($firstEl['laborCost']    ?? 65);
    $marginPct  = (float)($firstEl['pricingMode'] === 'comprada'
        ? ($firstEl['commercialMarginPct'] ?? 25)
        : ($firstEl['marginPct'] ?? 25));
    $ivaPct    = (float)($firstEl['ivaPct']        ?? 21);

    $subtotal   = round($total / (1 + $ivaPct / 100), 2);
    $ivaAmount  = round($total - $subtotal, 2);
    $marginAmt  = round($subtotal * $marginPct / (100 + $marginPct), 2);
    $base       = round($subtotal - $marginAmt, 2);

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':quote_number'     => $quoteNumber,
        ':created_at'       => $createdAt,
        ':client_name'      => $clientName,
        ':system_type'      => $systemType,
        ':opening_type'     => null,
        ':profile_color'    => $profileColor,
        ':glass_type'       => null,
        ':width_mm'         => $widthMm,
        ':height_mm'        => $heightMm,
        ':leaves'           => max(1, count($elements)),
        ':quantity'         => $qty,
        ':aluminum_price_ml'=> $alPrice,
        ':glass_price_m2'   => $glassPrice,
        ':labor_cost'       => $laborCost,
        ':margin_pct'       => $marginPct,
        ':iva_pct'          => $ivaPct,
        ':aluminum_ml'      => $aluminumMl,
        ':glass_m2'         => $glassM2,
        ':subtotal'         => $base,
        ':margin_amount'    => $marginAmt,
        ':taxable_base'     => $subtotal,
        ':iva_amount'       => $ivaAmount,
        ':total'            => $total,
        ':drawing_svg'      => $drawingSvg,
        ':config_json'      => $configJson,
    ]);

    $id = (int)$pdo->lastInsertId();
    echo url_with_lang('view_quote.php', ['id' => $id], $lang);
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error al guardar: ' . h($e->getMessage());
}
