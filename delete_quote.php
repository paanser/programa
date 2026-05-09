<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$lang = get_current_lang();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url_with_lang('list_quotes.php', [], $lang));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo h(tr('invalid_id', $lang));
    exit;
}

try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('DELETE FROM quotes WHERE id = :id');
    $stmt->execute([':id' => $id]);

    header('Location: ' . url_with_lang('list_quotes.php', ['deleted' => '1'], $lang));
    exit;
} catch (Throwable $e) {
    error_log('[delete_quote] ' . $e->getMessage());
    http_response_code(500);
    echo h(tr('delete_error', $lang));
}
