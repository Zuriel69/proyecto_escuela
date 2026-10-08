<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';
require_login();

const PARCIALES = 3;
const MINIMA_APROBATORIA = 6.0;

$pdo = db();
$alumnoId = (int)($_GET['alumno_id'] ?? $_POST['alumno_id'] ?? 0);

// Guardar calificaciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $alumnoId) {
    csrf_check();
    $cal = $_POST['cal'] ?? [];

    $up = $pdo->prepare('INSERT INTO calificaciones (alumno_id, materia_id, parcial, calificacion) VALUES (?,?,?,?)
                         ON DUPLICATE KEY UPDATE calificacion = VALUES(calificacion)');
    $del = $pdo->prepare('DELETE FROM calificaciones WHERE alumno_id=? AND materia_id=? AND parcial=?');

    $pdo->beginTransaction();
    foreach ($cal as $materiaId => $parciales) {
        foreach ($parciales as $p => $valor) {
            $materiaId = (int)$materiaId;
            $p = (int)$p;
            if ($p < 1 || $p > PARCIALES) {
                continue;
            }
            $valor = trim((string)$valor);
            if ($valor === '') {
                $del->execute([$alumnoId, $materiaId, $p]);
                continue;
            }
            $n = (float)$valor;
            if ($n < 0 || $n > 10) {
                continue;
            }
            $up->execute([$alumnoId, $materiaId, $p, $n]);
        }
    }
    $pdo->commit();
    flash('Calificaciones guardadas.');
    redirect('calificaciones.php?alumno_id=' . $alumnoId);
}

$alumnos = $pdo->query('SELECT id, matricula, nombre, apellidos FROM alumnos ORDER BY apellidos, nombre')->fetchAll();

$alumno = null;
$filas = [];
if ($alumnoId) {
    $st = $pdo->prepare('SELECT * FROM alumnos WHERE id = ?');
    $st->execute([$alumnoId]);
    $alumno = $st->fetch();

    if ($alumno) {
        $st = $pdo->prepare('SELECT * FROM materias WHERE grado = ? ORDER BY nombre');
        $st->execute([$alumno['grado']]);
        $materias = $st->fetchAll();

        $st = $pdo->prepare('SELECT materia_id, parcial, calificacion FROM calificaciones WHERE alumno_id = ?');
        $st->execute([$alumnoId]);
        $notas = [];
        foreach ($st->fetchAll() as $r) {
            $notas[$r['materia_id']][$r['parcial']] = $r['calificacion'];
        }

        foreach ($materias as $m) {
            $vals = $notas[$m['id']] ?? [];
            $prom = $vals ? array_sum($vals) / count($vals) : null;
            $filas[] = ['materia' => $m, 'notas' => $vals, 'promedio' => $prom];
        }
    }
}

page_start('Calificaciones');
?>
<form method="get" class="search">
    <select name="alumno_id" onchange="this.form.submit()" style="flex:1">
        <option value="">— Selecciona un alumno —</option>
        <?php foreach ($alumnos as $a): ?>
            <option value="<?= (int)$a['id'] ?>" <?= $alumnoId === (int)$a['id'] ? 'selected' : '' ?>>
                <?= e($a['matricula'] . ' · ' . $a['apellidos'] . ', ' . $a['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button>Ver</button>
</form>

<?php if ($alumno): ?>
    <div class="card">
        <strong><?= e($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></strong>
        — <?= e($alumno['grado'] . '° ' . $alumno['grupo']) ?> (matrícula <?= e($alumno['matricula']) ?>)
    </div>

    <?php if (!$filas): ?>
        <div class="card">No hay materias registradas para el grado <?= (int)$alumno['grado'] ?>°. <a href="materias.php">Agrega materias</a>.</div>
    <?php else: ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="alumno_id" value="<?= (int)$alumno['id'] ?>">
            <table>
                <tr>
                    <th>Materia</th>
                    <?php for ($p = 1; $p <= PARCIALES; $p++): ?><th>Parcial <?= $p ?></th><?php endfor; ?>
                    <th>Promedio</th>
                </tr>
                <?php foreach ($filas as $f): $m = $f['materia']; ?>
                    <tr>
                        <td><?= e($m['clave'] . ' · ' . $m['nombre']) ?></td>
                        <?php for ($p = 1; $p <= PARCIALES; $p++): ?>
                            <td><input type="number" min="0" max="10" step="0.1"
                                       name="cal[<?= (int)$m['id'] ?>][<?= $p ?>]"
                                       value="<?= isset($f['notas'][$p]) ? e(number_format((float)$f['notas'][$p], 1, '.', '')) : '' ?>"></td>
                        <?php endfor; ?>
                        <td>
                            <?php if ($f['promedio'] !== null): ?>
                                <span class="<?= $f['promedio'] >= MINIMA_APROBATORIA ? 'aprob' : 'reprob' ?>">
                                    <?= number_format($f['promedio'], 1) ?>
                                </span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <p><button>Guardar calificaciones</button></p>
        </form>
    <?php endif; ?>
<?php endif; ?>
<?php page_end();
