<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
$esAlumno = !empty($_SESSION['alumno_id']);
$_SESSION = [];
session_destroy();
redirect($esAlumno ? 'login_alumno.php' : 'login.php');
