<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/helpers.php';

$configExists = file_exists(__DIR__ . '/config.php');
$defaultIva = 21;
if ($configExists) {
    $cfg = require __DIR__ . '/config.php';
    $defaultIva = (float)($cfg['iva_pct_default'] ?? 21);
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Presupuestos Vidrio y Aluminio / Pressupostos Vidre i Alumini</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<div class="background-shape shape-a"></div>
<div class="background-shape shape-b"></div>

<header class="topbar">
    <h1>Presupuestos Vidrio y Aluminio <span>/ Pressupostos Vidre i Alumini</span></h1>
    <nav>
        <a href="index.php" class="active">Nuevo / Nou</a>
        <a href="list_quotes.php">Historial / Historial</a>
    </nav>
</header>

<main class="layout">
    <section class="panel form-panel">
        <h2>Datos del presupuesto / Dades del pressupost</h2>

        <?php if (!$configExists): ?>
            <div class="alert">
                Falta <strong>config.php</strong>. Copia <strong>config.php.example</strong> a <strong>config.php</strong> y completa credenciales de base de datos.
                <br>
                Falta <strong>config.php</strong>. Copia <strong>config.php.example</strong> a <strong>config.php</strong> i completa credencials de base de dades.
            </div>
        <?php endif; ?>

        <form id="quoteForm" method="post" action="save_quote.php">
            <div class="grid two">
                <label>Cliente / Client
                    <input type="text" name="client_name" required>
                </label>
                <label>Email
                    <input type="email" name="client_email">
                </label>
                <label>Teléfono / Telèfon
                    <input type="text" name="client_phone">
                </label>
                <label>Cantidad / Quantitat
                    <input type="number" name="quantity" min="1" value="1" required>
                </label>
            </div>

            <h3>Selector visual / Selector visual</h3>
            <div class="grid two">
                <label>Sistema / Sistema
                    <select name="system_type" id="systemType">
                        <option value="corredera">Corredera / Corredissa</option>
                        <option value="abatible">Abatible</option>
                        <option value="fijo">Fijo / Fix</option>
                        <option value="oscilobatiente">Oscilobatiente / Oscilobatent</option>
                    </select>
                </label>
                <label>Apertura / Obertura
                    <select name="opening_type" id="openingType">
                        <option value="izquierda">Izquierda / Esquerra</option>
                        <option value="derecha">Derecha / Dreta</option>
                        <option value="central">Central</option>
                    </select>
                </label>
                <label>Color perfil / Color perfil
                    <input type="text" name="profile_color" value="Blanco / Blanc">
                </label>
                <label>Tipo de vidrio / Tipus de vidre
                    <input type="text" name="glass_type" value="Doble 4+4/12/4">
                </label>
                <label>Ancho (mm) / Ample (mm)
                    <input type="number" name="width_mm" id="widthMm" min="300" value="1500" required>
                </label>
                <label>Alto (mm) / Alt (mm)
                    <input type="number" name="height_mm" id="heightMm" min="300" value="1200" required>
                </label>
                <label>Hojas / Fulles
                    <input type="number" name="leaves" id="leaves" min="1" max="6" value="2" required>
                </label>
            </div>

            <h3>Costes y márgenes / Costos i marges</h3>
            <div class="grid two">
                <label>Precio aluminio €/ml / Preu alumini €/ml
                    <input type="number" name="aluminum_price_ml" id="priceAl" min="0" step="0.01" value="18.00" required>
                </label>
                <label>Precio vidrio €/m² / Preu vidre €/m²
                    <input type="number" name="glass_price_m2" id="priceGlass" min="0" step="0.01" value="42.00" required>
                </label>
                <label>Mano de obra € / Mà d'obra €
                    <input type="number" name="labor_cost" id="labor" min="0" step="0.01" value="65.00" required>
                </label>
                <label>Margen % / Marge %
                    <input type="number" name="margin_pct" id="margin" min="0" step="0.01" value="25.00" required>
                </label>
                <label>IVA %
                    <input type="number" name="iva_pct" id="iva" min="0" step="0.01" value="<?= h((string)$defaultIva) ?>" required>
                </label>
            </div>

            <label>Notas / Notes
                <textarea name="notes" rows="3" placeholder="Observaciones del trabajo / Observacions de la feina"></textarea>
            </label>

            <input type="hidden" name="drawing_svg" id="drawingSvg">
            <input type="hidden" name="config_json" id="configJson">

            <div class="actions">
                <button type="submit" <?= !$configExists ? 'disabled' : '' ?>>Guardar presupuesto / Desar pressupost</button>
            </div>
        </form>
    </section>

    <section class="panel preview-panel">
        <h2>Vista previa técnica / Vista prèvia tècnica</h2>
        <div id="drawingWrap" class="drawing-wrap"></div>

        <h3>Resumen económico / Resum econòmic</h3>
        <div class="totals" id="totalsBox"></div>
    </section>
</main>

<script src="assets/app.js"></script>
</body>
</html>
