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
$status = trim((string)($_POST['status'] ?? ''));
$validStatuses = ['draft', 'sent', 'accepted', 'rejected', 'ordered'];

if ($id <= 0 || !in_array($status, $validStatuses, true)) {
    http_response_code(400);
    echo h(tr('invalid_id', $lang));
    exit;
}

$redirect = url_with_lang('view_quote.php', ['id' => $id], $lang);
if (!empty($_POST['from_list'])) {
    $redirect = url_with_lang('list_quotes.php', [], $lang);
}

try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('UPDATE quotes SET status = :status WHERE id = :id');
    $stmt->execute([':status' => $status, ':id' => $id]);

    header('Location: ' . $redirect);
    exit;
} catch (Throwable $e) {
    error_log('[update_status] ' . $e->getMessage());
    http_response_code(500);
    echo h(tr('save_error', $lang));
}
