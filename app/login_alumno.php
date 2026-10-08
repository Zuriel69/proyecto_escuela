<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if (!empty($_SESSION['alumno_id'])) {
    redirect('alumno.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $matricula = trim($_POST['matricula'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $st = db()->prepare('SELECT id, nombre, password FROM alumnos WHERE matricula = ?');
    $st->execute([$matricula]);
    $a = $st->fetch();

    if ($a && $a['password'] && password_verify($password, $a['password'])) {
        session_regenerate_id(true);
        $_SESSION = ['alumno_id' => (int)$a['id'], 'nombre' => $a['nombre']];
        redirect('alumno.php');
    }
    $error = 'Matrícula o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso alumnos · Control Escolar</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="login">
    <form method="post" class="card">
        <h1>🎒 Portal del alumno</h1>
        <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
        <?= csrf_field() ?>
        <label>Matrícula <input name="matricula" required autofocus></label>
        <label>Contraseña <input type="password" name="password" required></label>
        <button>Entrar</button>
        <a href="login.php" style="text-align:center">Soy administrador →</a>
    </form>
</main>
</body>
</html>
