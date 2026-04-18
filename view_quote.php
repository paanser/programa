<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$lang = get_current_lang();
$config = [];
$items = [];
$quoteTotals = [];

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

    $decodedConfig = json_decode((string)($row['config_json'] ?? '{}'), true);
    $config = is_array($decodedConfig) ? $decodedConfig : [];
    $items = is_array($config['items'] ?? null) ? $config['items'] : [];
    $quoteTotals = is_array($config['quote_totals'] ?? null) ? $config['quote_totals'] : [];
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error: ' . h($e->getMessage());
    exit;
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h((string)$row['quote_number']) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header class="topbar">
    <h1><?= h(tr('quote', $lang)) ?> <?= h((string)$row['quote_number']) ?></h1>
    <div class="topbar-tools">
        <nav>
            <a href="<?= h(url_with_lang('index.php', [], $lang)) ?>"><?= h(tr('new', $lang)) ?></a>
            <a href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>"><?= h(tr('history', $lang)) ?></a>
        </nav>
        <label class="lang-switcher">
            <span><?= h(tr('language', $lang)) ?></span>
            <select onchange="window.location.href=this.value">
                <option value="<?= h(url_with_lang('view_quote.php', ['id' => $id], 'es')) ?>" <?= $lang === 'es' ? 'selected' : '' ?>><?= h(tr('spanish', $lang)) ?></option>
                <option value="<?= h(url_with_lang('view_quote.php', ['id' => $id], 'ca')) ?>" <?= $lang === 'ca' ? 'selected' : '' ?>><?= h(tr('catalan', $lang)) ?></option>
            </select>
        </label>
    </div>
</header>

<main class="layout">
    <section class="panel">
        <div class="action-bar no-print">
            <a class="link-button" href="<?= h(url_with_lang('index.php', [], $lang)) ?>"><?= h(tr('new', $lang)) ?></a>
            <a class="link-button" href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>"><?= h(tr('history', $lang)) ?></a>
            <a class="link-button" href="<?= h(url_with_lang('duplicate_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('duplicate', $lang)) ?></a>
            <button type="button" onclick="window.print()"><?= h(tr('print_pdf', $lang)) ?></button>
        </div>

        <h2><?= h(tr('client', $lang)) ?></h2>
        <p><strong><?= h((string)$row['client_name']) ?></strong></p>
        <p><?= h((string)$row['client_email']) ?> - <?= h((string)$row['client_phone']) ?></p>

        <h3><?= h(tr('configuration', $lang)) ?></h3>
        <p><?= h(tr('system', $lang)) ?>: <?= h(humanize_system_type((string)$row['system_type'], $lang)) ?></p>
        <?php if (($config['item_count'] ?? 1) > 1): ?>
            <p><?= h(tr('item_count', $lang)) ?>: <?= (int)$config['item_count'] ?></p>
            <p><?= h(tr('quantity', $lang)) ?>: <?= (int)($config['total_quantity'] ?? $row['quantity']) ?></p>
        <?php else: ?>
            <p><?= h(tr('opening', $lang)) ?>: <?= h(humanize_opening_type((string)$row['opening_type'], $lang)) ?></p>
            <p><?= h(tr('measurements', $lang)) ?>: <?= (int)$row['width_mm'] ?> x <?= (int)$row['height_mm'] ?> mm</p>
            <p><?= h(tr('leaves', $lang)) ?>: <?= (int)$row['leaves'] ?> | <?= h(tr('quantity', $lang)) ?>: <?= (int)$row['quantity'] ?></p>
        <?php endif; ?>
        <?php if (($config['carpentry_model'] ?? '') !== ''): ?>
            <p><?= h(tr('carpentry', $lang)) ?>: <?= h((string)$config['carpentry_model']) ?></p>
        <?php endif; ?>
        <?php if (($config['carpentry_reference'] ?? '') !== ''): ?>
            <p><?= h(tr('reference', $lang)) ?>: <?= h((string)$config['carpentry_reference']) ?></p>
        <?php endif; ?>
        <?php if (!empty($config['trim_size'])): ?>
            <p><?= h(tr('trim', $lang)) ?>: <?= (int)$config['trim_size'] ?> mm</p>
        <?php endif; ?>
        <?php if (($config['frame_cut_type'] ?? '') !== ''): ?>
            <p><?= h(tr('frame_cut', $lang)) ?>: <?= (($config['frame_cut_type'] ?? 'recto') === 'mitered') ? h(tr('mitered_cut', $lang)) : h(tr('straight_cut', $lang)) ?></p>
        <?php endif; ?>
        <?php if (($row['system_type'] ?? '') === 'oscilobatiente' && ($config['tilt_turn_leaf'] ?? '') !== ''): ?>
            <p><?= h(tr('tilt_turn_leaf', $lang)) ?>: <?= h(humanize_tilt_turn_leaf((string)$config['tilt_turn_leaf'], $lang)) ?></p>
        <?php endif; ?>
        <p><?= h(tr('profile', $lang)) ?>: <?= h((string)$row['profile_color']) ?></p>
        <p><?= h(tr('glass', $lang)) ?>: <?= h((string)$row['glass_type']) ?></p>
        <?php if (($config['glass_description'] ?? '') !== ''): ?>
            <p><?= h(tr('glass_composition', $lang)) ?>: <?= h((string)$config['glass_description']) ?></p>
        <?php endif; ?>
        <?php if (!empty($config['glass_width_mm']) && !empty($config['glass_height_mm'])): ?>
            <p><?= h(tr('glass_measure', $lang)) ?>: <?= (int)$config['glass_width_mm'] ?> x <?= (int)$config['glass_height_mm'] ?> mm</p>
        <?php endif; ?>
        <?php if (!empty($config['glass_panels'])): ?>
            <p><?= h(tr('glass_pieces_per_unit', $lang)) ?>: <?= (int)$config['glass_panels'] ?></p>
        <?php endif; ?>
        <p><?= h(tr('pricing_mode_label', $lang)) ?>: <?= (($config['pricing_mode'] ?? 'fabricada') === 'comprada') ? h(tr('purchased_carpentry', $lang)) : h(tr('own_fabrication', $lang)) ?></p>
        <?php if (!empty($config['is_factory_finished'])): ?>
            <p><?= h(tr('supplier', $lang)) ?>: <?= h(tr('already_finished', $lang)) ?></p>
        <?php endif; ?>

        <h3><?= h(tr('amounts', $lang)) ?></h3>
        <div class="totals">
            <div class="total-row"><span><?= h(tr('aluminum_price', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['aluminum_ml'] ?? $row['aluminum_ml']), 3, ',', '.') ?> ml</strong></div>
            <div class="total-row"><span><?= h(tr('glass', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['glass_m2'] ?? $row['glass_m2']), 3, ',', '.') ?> m2</strong></div>
            <?php if (isset($quoteTotals['glass_cost']) || isset($config['glass_cost'])): ?>
                <div class="total-row"><span><?= h(tr('glass_cost', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['glass_cost'] ?? $config['glass_cost']), 2, ',', '.') ?> EUR</strong></div>
            <?php endif; ?>
            <?php if (($config['pricing_mode'] ?? 'fabricada') === 'comprada'): ?>
                <div class="total-row"><span><?= h(tr('purchase_cost_me', $lang)) ?></span><strong><?= number_format((float)($config['purchased_unit_cost'] ?? 0), 2, ',', '.') ?> EUR/ud</strong></div>
                <div class="total-row"><span><?= h(tr('commercial_margin', $lang)) ?></span><strong><?= number_format((float)($config['commercial_margin_pct'] ?? $row['margin_pct']), 2, ',', '.') ?> %</strong></div>
            <?php endif; ?>
            <div class="total-row"><span><?= h(tr('subtotal', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['subtotal'] ?? $row['subtotal']), 2, ',', '.') ?> EUR</strong></div>
            <div class="total-row"><span><?= h(tr('margin', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['margin_amount'] ?? $row['margin_amount']), 2, ',', '.') ?> EUR</strong></div>
            <div class="total-row"><span><?= h(tr('iva', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['iva_amount'] ?? $row['iva_amount']), 2, ',', '.') ?> EUR</strong></div>
            <div class="total-row total-main"><span><?= h(tr('total', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['total'] ?? $row['total']), 2, ',', '.') ?> EUR</strong></div>
        </div>

        <h3><?= h(tr('notes', $lang)) ?></h3>
        <p><?= nl2br(h((string)$row['notes'])) ?></p>
    </section>

    <section class="panel">
        <h2><?= h(tr('technical_preview', $lang)) ?></h2>
        <?php if ($items !== []): ?>
            <div class="quote-detail-items">
                <?php foreach ($items as $index => $item): ?>
                    <article class="quote-detail-card">
                        <div class="quote-detail-card__header">
                            <div>
                                <h3><?= h(tr('item', $lang)) ?> <?= $index + 1 ?></h3>
                                <p><?= h(humanize_system_type((string)($item['system_type'] ?? 'corredera'), $lang)) ?> · <?= (int)($item['width_mm'] ?? 0) ?> x <?= (int)($item['height_mm'] ?? 0) ?> mm</p>
                            </div>
                            <strong><?= number_format((float)($item['total'] ?? 0), 2, ',', '.') ?> EUR</strong>
                        </div>
                        <div class="quote-detail-card__meta">
                            <span><?= h(tr('opening', $lang)) ?>: <?= h(humanize_opening_type((string)($item['opening_type'] ?? 'izquierda'), $lang)) ?></span>
                            <?php if ((string)($item['system_type'] ?? '') === 'oscilobatiente' && ($item['tilt_turn_leaf'] ?? '') !== ''): ?>
                                <span><?= h(tr('tilt_turn_leaf', $lang)) ?>: <?= h(humanize_tilt_turn_leaf((string)$item['tilt_turn_leaf'], $lang)) ?></span>
                            <?php endif; ?>
                            <span><?= h(tr('leaves', $lang)) ?>: <?= (int)($item['leaves'] ?? 1) ?></span>
                            <span><?= h(tr('quantity', $lang)) ?>: <?= (int)($item['quantity'] ?? 1) ?></span>
                            <?php if (($item['glass_description'] ?? '') !== ''): ?>
                                <span><?= h(tr('glass_composition', $lang)) ?>: <?= h((string)$item['glass_description']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="drawing-wrap drawing-wrap--item">
                            <?= (string)($item['drawing_svg'] ?? '') ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="drawing-wrap">
                <?= (string)$row['drawing_svg'] ?>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
