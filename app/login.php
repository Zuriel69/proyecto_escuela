<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if (!empty($_SESSION['uid'])) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    $st = db()->prepare('SELECT * FROM usuarios WHERE usuario = ?');
    $st->execute([$usuario]);
    $u = $st->fetch();

    if ($u && password_verify($password, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        $_SESSION['nombre'] = $u['nombre'];
        redirect('index.php');
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso · Control Escolar</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="login">
    <form method="post" class="card">
        <h1>🎓 Control Escolar</h1>
        <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
        <?= csrf_field() ?>
        <label>Usuario <input name="usuario" required autofocus></label>
        <label>Contraseña <input type="password" name="password" required></label>
        <button>Entrar</button>
        <a href="login_alumno.php" style="text-align:center">Soy alumno →</a>
    </form>
</main>
</body>
</html>
