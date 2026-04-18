<?php

declare(strict_types=1);

$root = __DIR__;
$checks = [];

$addCheck = static function (string $label, bool $ok, string $detail) use (&$checks): void {
    $checks[] = [
        'label' => $label,
        'ok' => $ok,
        'detail' => $detail,
    ];
};

$addCheck(
    'PHP funcionando',
    true,
    'La pagina se ha ejecutado en el servidor. Version detectada: ' . PHP_VERSION
);

$isPhpCompatible = version_compare(PHP_VERSION, '7.4.0', '>=');
$addCheck(
    'Version minima de PHP',
    $isPhpCompatible,
    $isPhpCompatible ? 'Compatible con esta aplicacion.' : 'Se requiere PHP 7.4 o superior.'
);

$helpersPath = $root . '/lib/helpers.php';
$dbPath = $root . '/lib/db.php';
$configPath = $root . '/config.php';
$indexPath = $root . '/index.php';
$jsPath = $root . '/assets/app.js';
$cssPath = $root . '/assets/styles.css';

$requiredFiles = [
    'index.php' => $indexPath,
    'assets/app.js' => $jsPath,
    'assets/styles.css' => $cssPath,
    'lib/helpers.php' => $helpersPath,
    'lib/db.php' => $dbPath,
];

foreach ($requiredFiles as $label => $path) {
    $exists = file_exists($path);
    $addCheck(
        'Archivo ' . $label,
        $exists,
        $exists ? 'Existe y puede ser servido por la aplicacion.' : 'No existe en el servidor.'
    );
}

$config = null;
if (file_exists($configPath)) {
    $loaded = require $configPath;
    $config = is_array($loaded) ? $loaded : null;
}

$addCheck(
    'config.php',
    is_array($config),
    is_array($config) ? 'Archivo encontrado y cargado.' : 'No existe o no devuelve un array valido.'
);

$pdoLoaded = extension_loaded('pdo');
$pdoMysqlLoaded = extension_loaded('pdo_mysql');

$addCheck(
    'Extension PDO',
    $pdoLoaded,
    $pdoLoaded ? 'PDO esta disponible.' : 'Falta la extension PDO en PHP.'
);

$addCheck(
    'Extension pdo_mysql',
    $pdoMysqlLoaded,
    $pdoMysqlLoaded ? 'PDO MySQL esta disponible.' : 'Falta la extension pdo_mysql en PHP.'
);

$dbMessage = 'No comprobada.';
$dbOk = false;
$tableOk = false;
$tableMessage = 'No comprobada.';
$serverVersionMessage = 'No disponible.';

if ($pdoLoaded && $pdoMysqlLoaded && file_exists($dbPath)) {
    require_once $dbPath;

    try {
        $pdo = get_pdo();
        $dbOk = true;
        $dbMessage = 'Conexion correcta con la base de datos.';

        $serverVersion = $pdo->query('SELECT VERSION()')->fetchColumn();
        if (is_string($serverVersion) && $serverVersion !== '') {
            $serverVersionMessage = $serverVersion;
        }

        $stmt = $pdo->query("SHOW TABLES LIKE 'quotes'");
        $tableOk = (bool)$stmt->fetchColumn();
        $tableMessage = $tableOk
            ? 'La tabla quotes existe.'
            : 'La tabla quotes no existe. Importa database.sql.';
    } catch (Throwable $e) {
        $dbMessage = $e->getMessage();
    }
} elseif (!$pdoMysqlLoaded) {
    $dbMessage = 'No se puede probar la BD porque falta la extension pdo_mysql.';
}

$addCheck('Conexion a base de datos', $dbOk, $dbMessage);
$addCheck('Tabla quotes', $tableOk, $tableMessage);

$indexSource = file_exists($indexPath) ? (string)file_get_contents($indexPath) : '';
$loadsAssets = $indexSource !== ''
    && strpos($indexSource, 'assets/styles.css') !== false
    && strpos($indexSource, 'assets/app.js') !== false;

$addCheck(
    'Carga de assets en index.php',
    $loadsAssets,
    $loadsAssets ? 'index.php referencia styles.css y app.js.' : 'index.php no referencia correctamente los assets del frontend.'
);

$notes = [];
if (!$isPhpCompatible) {
    $notes[] = 'Actualiza el hosting a PHP 7.4 o superior.';
}
if (!$dbOk) {
    $notes[] = 'Revisa host, puerto, base de datos, usuario y contraseña en config.php.';
}
if ($dbOk && !$tableOk) {
    $notes[] = 'Importa database.sql en la base de datos configurada.';
}
if ($dbOk && $tableOk) {
    $notes[] = 'La aplicacion ya tiene servidor PHP, assets y base de datos listos.';
}
if (!$loadsAssets) {
    $notes[] = 'Comprueba rutas y subida de assets/app.js y assets/styles.css.';
}

function h_diag(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Diagnostico de la aplicacion</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #eef2f5;
            --panel: #ffffff;
            --text: #1e2933;
            --muted: #5f6b76;
            --ok: #1f7a45;
            --bad: #a33a2a;
            --ok-bg: #e9f7ef;
            --bad-bg: #fbe9e6;
            --border: #d7dfe6;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(180deg, #f8fafb, var(--bg));
            color: var(--text);
        }

        main {
            max-width: 960px;
            margin: 0 auto;
            padding: 24px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 28px rgba(30, 41, 51, 0.08);
        }

        h1, h2, p {
            margin-top: 0;
        }

        .grid {
            display: grid;
            gap: 12px;
        }

        .check {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 16px;
        }

        .check.ok {
            background: var(--ok-bg);
            border-color: #c9e8d3;
        }

        .check.bad {
            background: var(--bad-bg);
            border-color: #efcdc7;
        }

        .status {
            font-weight: 700;
            margin-bottom: 6px;
        }

        .check.ok .status {
            color: var(--ok);
        }

        .check.bad .status {
            color: var(--bad);
        }

        code {
            font-family: Consolas, monospace;
        }

        ul {
            margin: 0;
            padding-left: 18px;
        }

        .meta {
            color: var(--muted);
            margin-bottom: 18px;
        }

        .links {
            margin-top: 18px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .links a {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            background: #31424f;
        }
    </style>
</head>
<body>
<main>
    <section class="panel">
        <h1>Diagnostico de la aplicacion</h1>
        <p class="meta">Esta pagina confirma si el servidor PHP responde, si el frontend esta presente y si la base de datos esta accesible.</p>

        <div class="grid">
            <?php foreach ($checks as $check): ?>
                <div class="check <?= $check['ok'] ? 'ok' : 'bad' ?>">
                    <div class="status"><?= $check['ok'] ? 'OK' : 'ERROR' ?> - <?= h_diag($check['label']) ?></div>
                    <div><?= h_diag($check['detail']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2 style="margin-top:20px;">Datos utiles</h2>
        <div class="grid">
            <div class="check">
                <div class="status">Servidor SQL</div>
                <div><?= h_diag($serverVersionMessage) ?></div>
            </div>
            <div class="check">
                <div class="status">Base configurada</div>
                <div><?= h_diag((string)($config['db_name'] ?? 'No definida')) ?></div>
            </div>
            <div class="check">
                <div class="status">Host configurado</div>
                <div><?= h_diag((string)($config['db_host'] ?? 'No definido')) ?>:<?= h_diag((string)($config['db_port'] ?? '')) ?></div>
            </div>
        </div>

        <h2 style="margin-top:20px;">Siguientes pasos</h2>
        <ul>
            <?php foreach ($notes as $note): ?>
                <li><?= h_diag($note) ?></li>
            <?php endforeach; ?>
        </ul>

        <div class="links">
            <a href="index.php">Abrir la aplicacion</a>
            <a href="list_quotes.php">Abrir historial</a>
        </div>
    </section>
</main>
</body>
</html>