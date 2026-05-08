<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/auth.php';

$lang = get_current_lang();
$currentUser = require_auth($lang);
$isAdmin = is_admin_user($currentUser);
$error = null;
$rows = [];
$allowedStatuses = ['pending', 'accepted', 'rejected'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'update_status') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);
    $status = (string)($_POST['status'] ?? '');

    if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => tr('status_update_error', $lang)]);
        exit;
    }

    try {
        $pdo = get_pdo();
        $sql = 'UPDATE quotes SET status = :status WHERE id = :id';
        $params = [':status' => $status, ':id' => $id];
        if (!$isAdmin) {
            $sql .= ' AND user_id = :user_id';
            $params[':user_id'] = (int)$currentUser['id'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['ok' => true, 'label' => humanize_quote_status($status, $lang)]);
        exit;
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => tr('status_update_error', $lang)]);
        exit;
    }
}

$search = trim((string)($_GET['q'] ?? ''));
$statusFilter = (string)($_GET['status'] ?? '');
$dateFrom = trim((string)($_GET['date_from'] ?? ''));
$dateTo = trim((string)($_GET['date_to'] ?? ''));
$langSwitchParams = ['q' => $search, 'status' => $statusFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo];
if (!in_array($statusFilter, array_merge([''], $allowedStatuses), true)) {
    $statusFilter = '';
}

try {
    $pdo = get_pdo();
    $where = [];
    $params = [];

    if (!$isAdmin) {
        $where[] = 'user_id = :user_id';
        $params[':user_id'] = (int)$currentUser['id'];
    }

    if ($search !== '') {
        $where[] = 'client_name LIKE :client_name';
        $params[':client_name'] = '%' . $search . '%';
    }

    if ($statusFilter !== '') {
        $where[] = 'status = :status';
        $params[':status'] = $statusFilter;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) === 1) {
        $where[] = 'created_at >= :date_from';
        $params[':date_from'] = $dateFrom . ' 00:00:00';
    } else {
        $dateFrom = '';
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) === 1) {
        $where[] = 'created_at <= :date_to';
        $params[':date_to'] = $dateTo . ' 23:59:59';
    } else {
        $dateTo = '';
    }

    $sql = 'SELECT id, quote_number, created_at, client_name, system_type, total, status FROM quotes';
    if ($where !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY id DESC LIMIT 500';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h(tr('history_title', $lang)) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header class="topbar">
    <h1><?= h(tr('history_title', $lang)) ?></h1>
    <div class="topbar-tools">
        <nav>
            <a href="<?= h(url_with_lang('index.php', [], $lang)) ?>"><?= h(tr('new', $lang)) ?></a>
            <a href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>" class="active"><?= h(tr('history', $lang)) ?></a>
        </nav>
        <label class="lang-switcher">
            <span><?= h(tr('language', $lang)) ?></span>
            <select onchange="window.location.href=this.value">
                <option value="<?= h(url_with_lang('list_quotes.php', $langSwitchParams, 'es')) ?>" <?= $lang === 'es' ? 'selected' : '' ?>><?= h(tr('spanish', $lang)) ?></option>
                <option value="<?= h(url_with_lang('list_quotes.php', $langSwitchParams, 'ca')) ?>" <?= $lang === 'ca' ? 'selected' : '' ?>><?= h(tr('catalan', $lang)) ?></option>
            </select>
        </label>
        <div class="user-session">
            <span><?= h(tr('logged_in_as', $lang)) ?>: <?= h((string)$currentUser['name']) ?></span>
            <a href="<?= h(url_with_lang('logout.php', [], $lang)) ?>"><?= h(tr('logout', $lang)) ?></a>
        </div>
    </div>
</header>

<main class="layout" style="grid-template-columns: 1fr;">
    <section class="panel">
        <?php if ($error): ?>
            <div class="alert">Error: <?= h($error) ?></div>
        <?php endif; ?>
        <form method="get" class="history-filters">
            <input type="hidden" name="lang" value="<?= h($lang) ?>">
            <label><?= h(tr('filter_client', $lang)) ?>
                <input type="text" name="q" value="<?= h($search) ?>" placeholder="<?= h(tr('search', $lang)) ?>">
            </label>
            <label><?= h(tr('status', $lang)) ?>
                <select name="status">
                    <option value=""><?= h(tr('status_all', $lang)) ?></option>
                    <?php foreach ($allowedStatuses as $statusOption): ?>
                        <option value="<?= h($statusOption) ?>" <?= $statusFilter === $statusOption ? 'selected' : '' ?>><?= h(humanize_quote_status($statusOption, $lang)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label><?= h(tr('filter_from', $lang)) ?>
                <input type="date" name="date_from" value="<?= h($dateFrom) ?>">
            </label>
            <label><?= h(tr('filter_to', $lang)) ?>
                <input type="date" name="date_to" value="<?= h($dateTo) ?>">
            </label>
            <div class="actions">
                <button type="submit"><?= h(tr('search', $lang)) ?></button>
            </div>
        </form>

        <p><strong><?= count($rows) ?></strong> <?= h(tr('results_found', $lang)) ?></p>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th><?= h(tr('quote', $lang)) ?></th>
                    <th><?= h(tr('date', $lang)) ?></th>
                    <th><?= h(tr('client', $lang)) ?></th>
                    <th><?= h(tr('system', $lang)) ?></th>
                    <th><?= h(tr('status', $lang)) ?></th>
                    <th><?= h(tr('total', $lang)) ?></th>
                    <th><?= h(tr('actions', $lang)) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= h((string)$row['quote_number']) ?></td>
                        <td><?= h((string)$row['created_at']) ?></td>
                        <td><?= h((string)$row['client_name']) ?></td>
                        <td><?= h(humanize_system_type((string)$row['system_type'], $lang)) ?></td>
                        <?php $rowStatus = in_array((string)$row['status'], $allowedStatuses, true) ? (string)$row['status'] : 'pending'; ?>
                        <td>
                            <span class="status-badge status-<?= h($rowStatus) ?>" id="status-label-<?= (int)$row['id'] ?>"><?= h(humanize_quote_status($rowStatus, $lang)) ?></span>
                            <select class="quote-status-select" data-id="<?= (int)$row['id'] ?>">
                                <?php foreach ($allowedStatuses as $statusOption): ?>
                                    <option value="<?= h($statusOption) ?>" <?= $rowStatus === $statusOption ? 'selected' : '' ?>><?= h(humanize_quote_status($statusOption, $lang)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><?= number_format((float)$row['total'], 2, ',', '.') ?> EUR</td>
                        <td>
                            <a href="<?= h(url_with_lang('view_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('view', $lang)) ?></a>
                            |
                            <a href="<?= h(url_with_lang('duplicate_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('duplicate', $lang)) ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script>
document.querySelectorAll('.quote-status-select').forEach(function (el) {
    el.addEventListener('change', function () {
        var quoteId = el.getAttribute('data-id');
        var selectedStatus = el.value;
        var formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('id', quoteId);
        formData.append('status', selectedStatus);
        formData.append('lang', <?= json_encode($lang) ?>);

        fetch('list_quotes.php?lang=' + encodeURIComponent(<?= json_encode($lang) ?>), {
            method: 'POST',
            body: formData
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.ok) {
                    throw new Error(data.message || <?= json_encode(tr('status_update_error', $lang)) ?>);
                }

                var statusLabel = document.getElementById('status-label-' + quoteId);
                if (statusLabel) {
                    statusLabel.textContent = data.label;
                    statusLabel.className = 'status-badge status-' + selectedStatus;
                }
            })
            .catch(function (err) {
                alert(err.message || <?= json_encode(tr('status_update_error', $lang)) ?>);
            });
    });
});
</script>
</body>
</html>
