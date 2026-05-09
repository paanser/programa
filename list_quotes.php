<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$lang = get_current_lang();
$error = null;
$rows = [];
$stats = ['total' => 0, 'amount' => 0.0, 'accepted' => 0, 'pending' => 0];

$search = trim((string)($_GET['q'] ?? ''));
$filterSystem = trim((string)($_GET['system'] ?? ''));
$filterStatus = trim((string)($_GET['status'] ?? ''));
$deleted = !empty($_GET['deleted']);

try {
    $pdo = get_pdo();

    // Estadisticas globales
    $statsRow = $pdo->query(
        'SELECT COUNT(*) as total, COALESCE(SUM(total),0) as amount,
                SUM(status = "accepted") as accepted,
                SUM(status IN ("draft","sent")) as pending
         FROM quotes'
    )->fetch();
    if (is_array($statsRow)) {
        $stats = [
            'total'    => (int)$statsRow['total'],
            'amount'   => (float)$statsRow['amount'],
            'accepted' => (int)$statsRow['accepted'],
            'pending'  => (int)$statsRow['pending'],
        ];
    }

    // Listado filtrado
    $conditions = [];
    $params = [];

    if ($search !== '') {
        $conditions[] = 'client_name LIKE :search';
        $params[':search'] = '%' . $search . '%';
    }
    if ($filterSystem !== '') {
        $conditions[] = 'system_type = :system_type';
        $params[':system_type'] = $filterSystem;
    }
    if ($filterStatus !== '') {
        $conditions[] = 'status = :status';
        $params[':status'] = $filterStatus;
    }

    $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $sql = 'SELECT id, quote_number, created_at, client_name, system_type, total, status, valid_until, config_json
            FROM quotes ' . $where . ' ORDER BY id DESC LIMIT 200';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('[list_quotes] ' . $e->getMessage());
    $error = tr('save_error', $lang);
}

$systemOptions = [
    'corredera'      => tr('sliding', $lang),
    'abatible'       => tr('casement', $lang),
    'fijo'           => tr('fixed', $lang),
    'oscilobatiente' => tr('tilt_turn', $lang),
    'multiple'       => tr('multiple_system', $lang),
];
$statusOptions = get_status_options($lang);
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
                <option value="<?= h(url_with_lang('list_quotes.php', [], 'es')) ?>" <?= $lang === 'es' ? 'selected' : '' ?>><?= h(tr('spanish', $lang)) ?></option>
                <option value="<?= h(url_with_lang('list_quotes.php', [], 'ca')) ?>" <?= $lang === 'ca' ? 'selected' : '' ?>><?= h(tr('catalan', $lang)) ?></option>
            </select>
        </label>
    </div>
</header>

<main class="layout" style="grid-template-columns: 1fr;">
    <section class="panel">

        <?php if ($deleted): ?>
            <div class="alert alert--success"><?= h(tr('delete_success', $lang)) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert">Error: <?= h($error) ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-value"><?= $stats['total'] ?></span>
                <span class="stat-label"><?= h(tr('stats_total', $lang)) ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?= number_format($stats['amount'], 0, ',', '.') ?> €</span>
                <span class="stat-label"><?= h(tr('stats_amount', $lang)) ?></span>
            </div>
            <div class="stat-card stat-card--accepted">
                <span class="stat-value"><?= $stats['accepted'] ?></span>
                <span class="stat-label"><?= h(tr('stats_accepted', $lang)) ?></span>
            </div>
            <div class="stat-card stat-card--pending">
                <span class="stat-value"><?= $stats['pending'] ?></span>
                <span class="stat-label"><?= h(tr('stats_pending', $lang)) ?></span>
            </div>
        </div>

        <form method="get" action="list_quotes.php" class="list-filters">
            <input type="hidden" name="lang" value="<?= h($lang) ?>">
            <input type="search" name="q" value="<?= h($search) ?>" placeholder="<?= h(tr('search_client', $lang)) ?>">
            <select name="system">
                <option value=""><?= h(tr('filter_system', $lang)) ?></option>
                <?php foreach ($systemOptions as $sv => $sl): ?>
                    <option value="<?= h($sv) ?>" <?= $filterSystem === $sv ? 'selected' : '' ?>><?= h($sl) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status">
                <option value=""><?= h(tr('status_all', $lang)) ?></option>
                <?php foreach ($statusOptions as $sv => $sl): ?>
                    <option value="<?= h($sv) ?>" <?= $filterStatus === $sv ? 'selected' : '' ?>><?= h($sl) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit"><?= h(tr('view', $lang)) ?></button>
            <?php if ($search !== '' || $filterSystem !== '' || $filterStatus !== ''): ?>
                <a href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>" class="secondary-button">&times;</a>
            <?php endif; ?>
        </form>

        <div class="table-wrap">
            <?php if ($rows === []): ?>
                <p><?= h(tr('no_results', $lang)) ?></p>
            <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th><?= h(tr('quote', $lang)) ?></th>
                    <th><?= h(tr('date', $lang)) ?></th>
                    <th><?= h(tr('client', $lang)) ?></th>
                    <th><?= h(tr('system', $lang)) ?></th>
                    <th><?= h(tr('item_count', $lang)) ?></th>
                    <th><?= h(tr('total', $lang)) ?></th>
                    <th><?= h(tr('status', $lang)) ?></th>
                    <th><?= h(tr('actions', $lang)) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $decodedConfig = json_decode((string)($row['config_json'] ?? '{}'), true);
                    $itemCount = is_array($decodedConfig) ? (int)($decodedConfig['item_count'] ?? 1) : 1;
                    $rowStatus = (string)($row['status'] ?? 'draft');
                    $isExpired = ($row['valid_until'] ?? '') !== '' && $row['valid_until'] !== null
                        && strtotime((string)$row['valid_until']) < strtotime('today')
                        && !in_array($rowStatus, ['accepted', 'ordered'], true);
                    ?>
                    <tr>
                        <td><a href="<?= h(url_with_lang('view_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h((string)$row['quote_number']) ?></a></td>
                        <td><?= h((string)$row['created_at']) ?></td>
                        <td><?= h((string)$row['client_name']) ?></td>
                        <td><?= h(humanize_system_type((string)$row['system_type'], $lang)) ?></td>
                        <td><?= $itemCount ?></td>
                        <td><?= number_format((float)$row['total'], 2, ',', '.') ?> €</td>
                        <td>
                            <form method="post" action="update_status.php" class="inline-form">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <input type="hidden" name="from_list" value="1">
                                <input type="hidden" name="lang" value="<?= h($lang) ?>">
                                <select name="status" onchange="this.form.submit()" class="status-select <?= h(get_status_css_class($rowStatus)) ?>">
                                    <?php foreach ($statusOptions as $sv => $sl): ?>
                                        <option value="<?= h($sv) ?>" <?= $rowStatus === $sv ? 'selected' : '' ?>><?= h($sl) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($isExpired): ?>
                                <span class="expired-badge"><?= h($lang === 'ca' ? 'Caducat' : 'Caducado') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                            <a href="<?= h(url_with_lang('view_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('view', $lang)) ?></a>
                            <a href="<?= h(url_with_lang('duplicate_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('duplicate', $lang)) ?></a>
                            <form method="post" action="delete_quote.php" class="inline-form" onsubmit="return confirm(<?= json_encode(tr('delete_confirm', $lang)) ?>)">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <input type="hidden" name="lang" value="<?= h($lang) ?>">
                                <button type="submit" class="delete-button"><?= h(tr('delete', $lang)) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
