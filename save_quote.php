<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$clientName = trim((string)($_POST['client_name'] ?? ''));
if ($clientName === '') {
    http_response_code(422);
    echo 'Cliente obligatorio / Client obligatori';
    exit;
}

$calc = calculate_quote($_POST);
$quoteNumber = generate_quote_number();
$createdAt = date('Y-m-d H:i:s');

try {
    $pdo = get_pdo();

    $sql = 'INSERT INTO quotes (
        quote_number, created_at, client_name, client_email, client_phone,
        system_type, opening_type, profile_color, glass_type,
        width_mm, height_mm, leaves, quantity,
        aluminum_price_ml, glass_price_m2, labor_cost, margin_pct, iva_pct,
        aluminum_ml, glass_m2, subtotal, margin_amount, taxable_base, iva_amount, total,
        drawing_svg, config_json, notes
    ) VALUES (
        :quote_number, :created_at, :client_name, :client_email, :client_phone,
        :system_type, :opening_type, :profile_color, :glass_type,
        :width_mm, :height_mm, :leaves, :quantity,
        :aluminum_price_ml, :glass_price_m2, :labor_cost, :margin_pct, :iva_pct,
        :aluminum_ml, :glass_m2, :subtotal, :margin_amount, :taxable_base, :iva_amount, :total,
        :drawing_svg, :config_json, :notes
    )';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':quote_number' => $quoteNumber,
        ':created_at' => $createdAt,
        ':client_name' => $clientName,
        ':client_email' => trim((string)($_POST['client_email'] ?? '')),
        ':client_phone' => trim((string)($_POST['client_phone'] ?? '')),
        ':system_type' => trim((string)($_POST['system_type'] ?? 'corredera')),
        ':opening_type' => trim((string)($_POST['opening_type'] ?? 'izquierda')),
        ':profile_color' => trim((string)($_POST['profile_color'] ?? '')),
        ':glass_type' => trim((string)($_POST['glass_type'] ?? '')),
        ':width_mm' => $calc['width_mm'],
        ':height_mm' => $calc['height_mm'],
        ':leaves' => $calc['leaves'],
        ':quantity' => $calc['quantity'],
        ':aluminum_price_ml' => $calc['aluminum_price_ml'],
        ':glass_price_m2' => $calc['glass_price_m2'],
        ':labor_cost' => $calc['labor_cost'],
        ':margin_pct' => $calc['margin_pct'],
        ':iva_pct' => $calc['iva_pct'],
        ':aluminum_ml' => $calc['aluminum_ml'],
        ':glass_m2' => $calc['glass_m2'],
        ':subtotal' => $calc['subtotal'],
        ':margin_amount' => $calc['margin_amount'],
        ':taxable_base' => $calc['taxable_base'],
        ':iva_amount' => $calc['iva_amount'],
        ':total' => $calc['total'],
        ':drawing_svg' => (string)($_POST['drawing_svg'] ?? ''),
        ':config_json' => (string)($_POST['config_json'] ?? '{}'),
        ':notes' => trim((string)($_POST['notes'] ?? '')),
    ]);

    $id = (int)$pdo->lastInsertId();
    header('Location: view_quote.php?id=' . $id);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error guardando presupuesto / Error desant pressupost: ' . h($e->getMessage());
}
