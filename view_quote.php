<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/helpers.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo 'ID inválido / ID invàlid';
    exit;
}

try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT * FROM quotes WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo 'Presupuesto no encontrado / Pressupost no trobat';
        exit;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error: ' . h($e->getMessage());
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h((string)$row['quote_number']) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header class="topbar">
    <h1>Presupuesto <?= h((string)$row['quote_number']) ?> <span>/ Pressupost</span></h1>
    <nav>
        <a href="index.php">Nuevo / Nou</a>
        <a href="list_quotes.php">Historial / Historial</a>
    </nav>
</header>

<main class="layout">
    <section class="panel">
        <div class="action-bar no-print">
            <a class="link-button" href="index.php">Nuevo / Nou</a>
            <a class="link-button" href="list_quotes.php">Historial / Historial</a>
            <a class="link-button" href="duplicate_quote.php?id=<?= (int)$row['id'] ?>">Duplicar / Duplicar</a>
            <button type="button" onclick="window.print()">Imprimir o PDF / Imprimir o PDF</button>
        </div>

        <h2>Cliente / Client</h2>
        <p><strong><?= h((string)$row['client_name']) ?></strong></p>
        <p><?= h((string)$row['client_email']) ?> - <?= h((string)$row['client_phone']) ?></p>

        <h3>Configuración / Configuració</h3>
        <p>Sistema: <?= h((string)$row['system_type']) ?></p>
        <p>Apertura: <?= h((string)$row['opening_type']) ?></p>
        <p>Medidas: <?= (int)$row['width_mm'] ?> x <?= (int)$row['height_mm'] ?> mm</p>
        <p>Hojas: <?= (int)$row['leaves'] ?> | Cantidad: <?= (int)$row['quantity'] ?></p>
        <p>Perfil: <?= h((string)$row['profile_color']) ?></p>
        <p>Vidrio: <?= h((string)$row['glass_type']) ?></p>

        <h3>Importes / Imports</h3>
        <div class="totals">
            <div class="total-row"><span>Aluminio / Alumini</span><strong><?= number_format((float)$row['aluminum_ml'], 3, ',', '.') ?> ml</strong></div>
            <div class="total-row"><span>Vidrio / Vidre</span><strong><?= number_format((float)$row['glass_m2'], 3, ',', '.') ?> m2</strong></div>
            <div class="total-row"><span>Subtotal</span><strong><?= number_format((float)$row['subtotal'], 2, ',', '.') ?> EUR</strong></div>
            <div class="total-row"><span>Margen / Marge</span><strong><?= number_format((float)$row['margin_amount'], 2, ',', '.') ?> EUR</strong></div>
            <div class="total-row"><span>IVA</span><strong><?= number_format((float)$row['iva_amount'], 2, ',', '.') ?> EUR</strong></div>
            <div class="total-row total-main"><span>Total</span><strong><?= number_format((float)$row['total'], 2, ',', '.') ?> EUR</strong></div>
        </div>

        <h3>Notas / Notes</h3>
        <p><?= nl2br(h((string)$row['notes'])) ?></p>
    </section>

    <section class="panel">
        <h2>Dibujo técnico / Dibuix tècnic</h2>
        <div class="drawing-wrap">
            <?= (string)$row['drawing_svg'] ?>
        </div>
    </section>
</main>
</body>
</html>
