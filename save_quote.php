<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$lang = get_current_lang($_POST);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url_with_lang('index.php', [], $lang));
    exit;
}

$clientName = trim((string)($_POST['client_name'] ?? ''));
if ($clientName === '') {
    http_response_code(422);
    echo h(tr('required_client', $lang));
    exit;
}

$calc = calculate_quote($_POST);
$config = build_quote_config($_POST, $calc);
$quoteNumber = generate_quote_number();
$createdAt = date('Y-m-d H:i:s');

try {
    $pdo = get_pdo();

    $validUntilRaw = trim((string)($_POST['valid_until'] ?? ''));
    $validUntil = ($validUntilRaw !== '' && strtotime($validUntilRaw)) ? $validUntilRaw : null;

    $sql = 'INSERT INTO quotes (
        quote_number, created_at, client_name, client_email, client_phone,
        system_type, opening_type, profile_color, glass_type,
        width_mm, height_mm, leaves, quantity,
        aluminum_price_ml, glass_price_m2, labor_cost, hardware_cost, installation_cost, margin_pct, iva_pct,
        aluminum_ml, glass_m2, subtotal, margin_amount, taxable_base, iva_amount, total,
        drawing_svg, config_json, notes, status, valid_until
    ) VALUES (
        :quote_number, :created_at, :client_name, :client_email, :client_phone,
        :system_type, :opening_type, :profile_color, :glass_type,
        :width_mm, :height_mm, :leaves, :quantity,
        :aluminum_price_ml, :glass_price_m2, :labor_cost, :hardware_cost, :installation_cost, :margin_pct, :iva_pct,
        :aluminum_ml, :glass_m2, :subtotal, :margin_amount, :taxable_base, :iva_amount, :total,
        :drawing_svg, :config_json, :notes, :status, :valid_until
    )';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':quote_number' => $quoteNumber,
        ':created_at' => $createdAt,
        ':client_name' => $clientName,
        ':client_email' => trim((string)($_POST['client_email'] ?? '')),
        ':client_phone' => trim((string)($_POST['client_phone'] ?? '')),
        ':system_type' => (string)$calc['system_type'],
        ':opening_type' => (string)$calc['opening_type'],
        ':profile_color' => (string)$calc['profile_color'],
        ':glass_type' => (string)$calc['glass_type'],
        ':width_mm' => $calc['width_mm'],
        ':height_mm' => $calc['height_mm'],
        ':leaves' => $calc['leaves'],
        ':quantity' => $calc['quantity'],
        ':aluminum_price_ml' => $calc['aluminum_price_ml'],
        ':glass_price_m2' => $calc['glass_price_m2'],
        ':labor_cost' => $calc['labor_cost'],
        ':hardware_cost' => $calc['hardware_cost'] ?? 0,
        ':installation_cost' => $calc['installation_cost'] ?? 0,
        ':margin_pct' => $calc['margin_pct'],
        ':iva_pct' => $calc['iva_pct'],
        ':aluminum_ml' => $calc['aluminum_ml'],
        ':glass_m2' => $calc['glass_m2'],
        ':subtotal' => $calc['subtotal'],
        ':margin_amount' => $calc['margin_amount'],
        ':taxable_base' => $calc['taxable_base'],
        ':iva_amount' => $calc['iva_amount'],
        ':total' => $calc['total'],
        ':drawing_svg' => (string)$calc['drawing_svg'],
        ':config_json' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':notes' => trim((string)($_POST['notes'] ?? '')),
        ':status' => 'draft',
        ':valid_until' => $validUntil,
    ]);

    $id = (int)$pdo->lastInsertId();
    header('Location: ' . url_with_lang('view_quote.php', ['id' => $id], $lang));
    exit;
} catch (Throwable $e) {
    error_log('[save_quote] ' . $e->getMessage());
    http_response_code(500);
    echo h(tr('save_error', $lang));
}
