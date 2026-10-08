<?php
declare(strict_types=1);

function page_start(string $title): void
{
    $nombre = $_SESSION['nombre'] ?? '';
    $actual = basename($_SERVER['SCRIPT_NAME']);
    $items = [
        'index.php' => 'Inicio',
        'alumnos.php' => 'Alumnos',
        'materias.php' => 'Materias',
        'calificaciones.php' => 'Calificaciones',
    ];
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · Control Escolar</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="top">
    <strong>🎓 Control Escolar</strong>
    <nav>
        <?php foreach ($items as $file => $label): ?>
            <a href="<?= $file ?>" class="<?= $actual === $file ? 'active' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <span class="user"><?= e($nombre) ?> · <a href="logout.php">Salir</a></span>
</header>
<main>
    <h1><?= e($title) ?></h1>
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="flash <?= e($_SESSION['flash']['type']) ?>"><?= e($_SESSION['flash']['msg']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
<?php
}

function page_end(): void
{
    echo "</main>\n</body>\n</html>";
}
