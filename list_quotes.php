<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$error = null;
$rows = [];

try {
    $pdo = get_pdo();
    $rows = $pdo->query('SELECT id, quote_number, created_at, client_name, system_type, total FROM quotes ORDER BY id DESC LIMIT 200')->fetchAll();
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial / Historial</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header class="topbar">
    <h1>Historial de presupuestos <span>/ Historial de pressupostos</span></h1>
    <nav>
        <a href="index.php">Nuevo / Nou</a>
        <a href="list_quotes.php" class="active">Historial / Historial</a>
    </nav>
</header>

<main class="layout" style="grid-template-columns: 1fr;">
    <section class="panel">
        <?php if ($error): ?>
            <div class="alert">Error: <?= h($error) ?></div>
        <?php endif; ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Número / Número</th>
                    <th>Fecha / Data</th>
                    <th>Cliente / Client</th>
                    <th>Sistema</th>
                    <th>Total</th>
                    <th>Acciones / Accions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= h((string)$row['quote_number']) ?></td>
                        <td><?= h((string)$row['created_at']) ?></td>
                        <td><?= h((string)$row['client_name']) ?></td>
                        <td><?= h((string)$row['system_type']) ?></td>
                        <td><?= number_format((float)$row['total'], 2, ',', '.') ?> EUR</td>
                        <td>
                            <a href="view_quote.php?id=<?= (int)$row['id'] ?>">Ver / Veure</a>
                            |
                            <a href="duplicate_quote.php?id=<?= (int)$row['id'] ?>">Duplicar / Duplicar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
