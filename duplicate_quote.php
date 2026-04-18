<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$lang = get_current_lang();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo h(tr('invalid_id', $lang));
    exit;
}

try {
    $pdo = get_pdo();

    $stmt = $pdo->prepare('SELECT * FROM quotes WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo h(tr('quote_not_found', $lang));
        exit;
    }

    $newQuoteNumber = generate_quote_number();
    $createdAt = date('Y-m-d H:i:s');

    $insertSql = 'INSERT INTO quotes (
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

    $insert = $pdo->prepare($insertSql);
    $insert->execute([
        ':quote_number' => $newQuoteNumber,
        ':created_at' => $createdAt,
        ':client_name' => (string)$row['client_name'],
        ':client_email' => (string)$row['client_email'],
        ':client_phone' => (string)$row['client_phone'],
        ':system_type' => (string)$row['system_type'],
        ':opening_type' => (string)$row['opening_type'],
        ':profile_color' => (string)$row['profile_color'],
        ':glass_type' => (string)$row['glass_type'],
        ':width_mm' => (int)$row['width_mm'],
        ':height_mm' => (int)$row['height_mm'],
        ':leaves' => (int)$row['leaves'],
        ':quantity' => (int)$row['quantity'],
        ':aluminum_price_ml' => (float)$row['aluminum_price_ml'],
        ':glass_price_m2' => (float)$row['glass_price_m2'],
        ':labor_cost' => (float)$row['labor_cost'],
        ':margin_pct' => (float)$row['margin_pct'],
        ':iva_pct' => (float)$row['iva_pct'],
        ':aluminum_ml' => (float)$row['aluminum_ml'],
        ':glass_m2' => (float)$row['glass_m2'],
        ':subtotal' => (float)$row['subtotal'],
        ':margin_amount' => (float)$row['margin_amount'],
        ':taxable_base' => (float)$row['taxable_base'],
        ':iva_amount' => (float)$row['iva_amount'],
        ':total' => (float)$row['total'],
        ':drawing_svg' => (string)$row['drawing_svg'],
        ':config_json' => (string)$row['config_json'],
        ':notes' => (string)$row['notes'],
    ]);

    $newId = (int)$pdo->lastInsertId();
    header('Location: ' . url_with_lang('view_quote.php', ['id' => $newId], $lang));
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo h(tr('duplicate_error', $lang)) . ': ' . h($e->getMessage());
}
