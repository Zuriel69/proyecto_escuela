<?php
declare(strict_types=1);

// Instalador: crea la base de datos, las tablas y el usuario administrador.
// Ejecútalo UNA vez desde http://localhost/control_escolar/install.php y después BÓRRALO.

const DB_HOST = 'localhost';
const DB_NAME = 'control_escolar';
const DB_USER = 'root';
const DB_PASS = '';

$msg = '';
$ok = false;

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . DB_NAME . '`');

    $pdo->exec('CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nombre VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB');

    $pdo->exec('CREATE TABLE IF NOT EXISTS alumnos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        matricula VARCHAR(20) NOT NULL UNIQUE,
        nombre VARCHAR(80) NOT NULL,
        apellidos VARCHAR(100) NOT NULL,
        fecha_nacimiento DATE NULL,
        email VARCHAR(120) NULL,
        grado TINYINT NOT NULL DEFAULT 1,
        grupo VARCHAR(5) NOT NULL DEFAULT "A",
        password VARCHAR(255) NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB');

    $pdo->exec('CREATE TABLE IF NOT EXISTS materias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        clave VARCHAR(20) NOT NULL UNIQUE,
        nombre VARCHAR(100) NOT NULL,
        grado TINYINT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB');

    $pdo->exec('CREATE TABLE IF NOT EXISTS calificaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        alumno_id INT NOT NULL,
        materia_id INT NOT NULL,
        parcial TINYINT NOT NULL,
        calificacion DECIMAL(4,1) NOT NULL,
        UNIQUE KEY uq_cal (alumno_id, materia_id, parcial),
        FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE,
        FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE
    ) ENGINE=InnoDB');

    $existe = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE usuario = 'admin'")->fetchColumn();
    if (!$existe) {
        $st = $pdo->prepare('INSERT INTO usuarios (usuario, password, nombre) VALUES (?, ?, ?)');
        $st->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'Administrador']);
    }

    $ok = true;
    $msg = 'Instalación completada.';
} catch (PDOException $ex) {
    $msg = 'Error: ' . $ex->getMessage() . ' — ¿Está MySQL encendido en XAMPP?';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalación · Control Escolar</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="login">
    <div class="card">
        <h1>Instalación</h1>
        <div class="flash <?= $ok ? 'ok' : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
        <?php if ($ok): ?>
            <p>Usuario: <strong>admin</strong><br>Contraseña: <strong>admin123</strong></p>
            <p><strong>Importante:</strong> borra <code>install.php</code> y cambia la contraseña.</p>
            <a class="btn" href="login.php">Ir al login</a>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
