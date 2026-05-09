<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$lang = get_current_lang();
$error = null;
$rows = [];

$search = trim((string)($_GET['q'] ?? ''));
$filterSystem = trim((string)($_GET['system'] ?? ''));

try {
    $pdo = get_pdo();

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

    $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $sql = 'SELECT id, quote_number, created_at, client_name, system_type, total, config_json
            FROM quotes ' . $where . ' ORDER BY id DESC LIMIT 200';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('[list_quotes] ' . $e->getMessage());
    $error = tr('save_error', $lang);
}

$systemOptions = [
    'corredera' => tr('sliding', $lang),
    'abatible' => tr('casement', $lang),
    'fijo' => tr('fixed', $lang),
    'oscilobatiente' => tr('tilt_turn', $lang),
    'multiple' => tr('multiple_system', $lang),
];
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
        <?php if ($error): ?>
            <div class="alert">Error: <?= h($error) ?></div>
        <?php endif; ?>

        <form method="get" action="list_quotes.php" class="list-filters">
            <input type="hidden" name="lang" value="<?= h($lang) ?>">
            <input type="search" name="q" value="<?= h($search) ?>" placeholder="<?= h(tr('search_client', $lang)) ?>">
            <select name="system">
                <option value=""><?= h(tr('filter_system', $lang)) ?></option>
                <?php foreach ($systemOptions as $systemValue => $systemLabel): ?>
                    <option value="<?= h($systemValue) ?>" <?= $filterSystem === $systemValue ? 'selected' : '' ?>><?= h($systemLabel) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit"><?= h(tr('view', $lang)) ?></button>
            <?php if ($search !== '' || $filterSystem !== ''): ?>
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
                    <th>ID</th>
                    <th><?= h(tr('quote', $lang)) ?></th>
                    <th><?= h(tr('date', $lang)) ?></th>
                    <th><?= h(tr('client', $lang)) ?></th>
                    <th><?= h(tr('system', $lang)) ?></th>
                    <th><?= h(tr('item_count', $lang)) ?></th>
                    <th><?= h(tr('total', $lang)) ?></th>
                    <th><?= h(tr('actions', $lang)) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $decodedConfig = json_decode((string)($row['config_json'] ?? '{}'), true);
                    $itemCount = is_array($decodedConfig) ? (int)($decodedConfig['item_count'] ?? 1) : 1;
                    ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= h((string)$row['quote_number']) ?></td>
                        <td><?= h((string)$row['created_at']) ?></td>
                        <td><?= h((string)$row['client_name']) ?></td>
                        <td><?= h(humanize_system_type((string)$row['system_type'], $lang)) ?></td>
                        <td><?= $itemCount ?></td>
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
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
