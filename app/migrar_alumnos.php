<?php
declare(strict_types=1);

// Migración para quien YA instaló el sistema antes del portal de alumnos.
// Agrega la columna "password" a alumnos y pone como contraseña inicial la matrícula.
// Ejecútalo UNA vez: http://localhost/control_escolar/migrar_alumnos.php  y luego BÓRRALO.

require __DIR__ . '/config.php';
require_login(); // solo el administrador

$msg = '';
$ok = false;

try {
    $pdo = db();
    $existe = $pdo->query("SHOW COLUMNS FROM alumnos LIKE 'password'")->fetch();
    if (!$existe) {
        $pdo->exec('ALTER TABLE alumnos ADD COLUMN password VARCHAR(255) NULL AFTER grupo');
    }

    $sin = $pdo->query('SELECT id, matricula FROM alumnos WHERE password IS NULL')->fetchAll();
    $up = $pdo->prepare('UPDATE alumnos SET password = ? WHERE id = ?');
    foreach ($sin as $a) {
        $up->execute([password_hash($a['matricula'], PASSWORD_DEFAULT), $a['id']]);
    }

    $ok = true;
    $msg = 'Migración lista. Alumnos actualizados: ' . count($sin) . '.';
} catch (PDOException $ex) {
    $msg = 'Error: ' . $ex->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migración · Control Escolar</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="login">
    <div class="card">
        <h1>Migración</h1>
        <div class="flash <?= $ok ? 'ok' : 'error' ?>"><?= e($msg) ?></div>
        <?php if ($ok): ?>
            <p>Contraseña inicial de cada alumno: <strong>su matrícula</strong>.</p>
            <p>Ahora borra <code>migrar_alumnos.php</code>.</p>
            <a class="btn" href="alumnos.php">Ir a Alumnos</a>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
