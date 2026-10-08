<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if (empty($_SESSION['alumno_id'])) {
    redirect('login_alumno.php');
}

const PARCIALES = 3;
const MINIMA_APROBATORIA = 6.0;

$pdo = db();
$id = (int)$_SESSION['alumno_id'];

// Cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $actual = (string)($_POST['actual'] ?? '');
    $nueva = (string)($_POST['nueva'] ?? '');
    $confirma = (string)($_POST['confirma'] ?? '');

    $hash = $pdo->prepare('SELECT password FROM alumnos WHERE id = ?');
    $hash->execute([$id]);
    $hash = $hash->fetchColumn();

    if (!$hash || !password_verify($actual, (string)$hash)) {
        flash('La contraseña actual no es correcta.', 'error');
    } elseif (strlen($nueva) < 6) {
        flash('La nueva contraseña debe tener al menos 6 caracteres.', 'error');
    } elseif ($nueva !== $confirma) {
        flash('La confirmación no coincide.', 'error');
    } else {
        $pdo->prepare('UPDATE alumnos SET password = ? WHERE id = ?')
            ->execute([password_hash($nueva, PASSWORD_DEFAULT), $id]);
        flash('Contraseña actualizada.');
    }
    redirect('alumno.php');
}

$st = $pdo->prepare('SELECT * FROM alumnos WHERE id = ?');
$st->execute([$id]);
$alumno = $st->fetch();
if (!$alumno) {
    $_SESSION = [];
    redirect('login_alumno.php');
}

$st = $pdo->prepare('SELECT * FROM materias WHERE grado = ? ORDER BY nombre');
$st->execute([$alumno['grado']]);
$materias = $st->fetchAll();

$st = $pdo->prepare('SELECT materia_id, parcial, calificacion FROM calificaciones WHERE alumno_id = ?');
$st->execute([$id]);
$notas = [];
foreach ($st->fetchAll() as $r) {
    $notas[$r['materia_id']][$r['parcial']] = (float)$r['calificacion'];
}

$promedios = [];
foreach ($materias as $m) {
    $v = $notas[$m['id']] ?? [];
    if ($v) {
        $promedios[] = array_sum($v) / count($v);
    }
}
$general = $promedios ? array_sum($promedios) / count($promedios) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis calificaciones · Control Escolar</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="top">
    <strong>🎒 Portal del alumno</strong>
    <nav></nav>
    <span class="user"><?= e($alumno['nombre']) ?> · <a href="logout.php">Salir</a></span>
</header>
<main>
    <h1>Mis calificaciones</h1>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="flash <?= e($_SESSION['flash']['type']) ?>"><?= e($_SESSION['flash']['msg']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="grid" style="margin-bottom:20px">
        <div class="stat"><b><?= e($alumno['matricula']) ?></b>Matrícula</div>
        <div class="stat"><b><?= e($alumno['grado'] . '° ' . $alumno['grupo']) ?></b>Grado y grupo</div>
        <div class="stat"><b class="<?= $general !== null ? ($general >= MINIMA_APROBATORIA ? 'aprob' : 'reprob') : '' ?>">
            <?= $general !== null ? number_format($general, 1) : '—' ?></b>Promedio general</div>
    </div>

    <p><strong><?= e($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></strong></p>

    <table>
        <tr>
            <th>Materia</th>
            <?php for ($p = 1; $p <= PARCIALES; $p++): ?><th>Parcial <?= $p ?></th><?php endfor; ?>
            <th>Promedio</th>
        </tr>
        <?php foreach ($materias as $m):
            $v = $notas[$m['id']] ?? [];
            $prom = $v ? array_sum($v) / count($v) : null; ?>
            <tr>
                <td><?= e($m['nombre']) ?></td>
                <?php for ($p = 1; $p <= PARCIALES; $p++): ?>
                    <td><?= isset($v[$p]) ? number_format($v[$p], 1) : '—' ?></td>
                <?php endfor; ?>
                <td>
                    <?php if ($prom !== null): ?>
                        <span class="<?= $prom >= MINIMA_APROBATORIA ? 'aprob' : 'reprob' ?>"><?= number_format($prom, 1) ?></span>
                    <?php else: ?>—<?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$materias): ?><tr><td colspan="<?= PARCIALES + 2 ?>">Aún no hay materias para tu grado.</td></tr><?php endif; ?>
    </table>

    <div class="card" style="margin-top:24px">
        <h3>Cambiar contraseña</h3>
        <form method="post" class="fields">
            <?= csrf_field() ?>
            <label>Contraseña actual <input type="password" name="actual" required></label>
            <label>Nueva contraseña <input type="password" name="nueva" required minlength="6"></label>
            <label>Confirmar nueva <input type="password" name="confirma" required minlength="6"></label>
            <div><button>Actualizar</button></div>
        </form>
    </div>
</main>
</body>
</html>
