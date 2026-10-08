<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';
require_login();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'eliminar') {
        $pdo->prepare('DELETE FROM materias WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        flash('Materia eliminada.');
        redirect('materias.php');
    }

    if ($accion === 'guardar') {
        $id = (int)($_POST['id'] ?? 0);
        $clave = trim($_POST['clave'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $grado = (int)($_POST['grado'] ?? 1);

        if ($clave === '' || $nombre === '') {
            flash('Clave y nombre son obligatorios.', 'error');
            redirect('materias.php' . ($id ? "?edit=$id" : ''));
        }

        try {
            if ($id) {
                $pdo->prepare('UPDATE materias SET clave=?, nombre=?, grado=? WHERE id=?')->execute([$clave, $nombre, $grado, $id]);
                flash('Materia actualizada.');
            } else {
                $pdo->prepare('INSERT INTO materias (clave, nombre, grado) VALUES (?,?,?)')->execute([$clave, $nombre, $grado]);
                flash('Materia registrada.');
            }
        } catch (PDOException $ex) {
            flash($ex->getCode() === '23000' ? 'Ya existe una materia con esa clave.' : 'Error al guardar.', 'error');
        }
        redirect('materias.php');
    }
}

$edit = ['id' => 0, 'clave' => '', 'nombre' => '', 'grado' => 1];
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM materias WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: $edit;
}
$materias = $pdo->query('SELECT * FROM materias ORDER BY grado, nombre')->fetchAll();

page_start('Materias');
?>
<div class="card">
    <h3><?= $edit['id'] ? 'Editar materia' : 'Nueva materia' ?></h3>
    <form method="post" class="fields">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
        <label>Clave <input name="clave" required maxlength="20" value="<?= e($edit['clave']) ?>"></label>
        <label>Nombre <input name="nombre" required maxlength="100" value="<?= e($edit['nombre']) ?>"></label>
        <label>Grado
            <select name="grado">
                <?php for ($g = 1; $g <= 9; $g++): ?>
                    <option value="<?= $g ?>" <?= (int)$edit['grado'] === $g ? 'selected' : '' ?>><?= $g ?>°</option>
                <?php endfor; ?>
            </select>
        </label>
        <div>
            <button>Guardar</button>
            <?php if ($edit['id']): ?><a class="btn sec" href="materias.php">Cancelar</a><?php endif; ?>
        </div>
    </form>
</div>

<table>
    <tr><th>Clave</th><th>Nombre</th><th>Grado</th><th></th></tr>
    <?php foreach ($materias as $m): ?>
        <tr>
            <td><?= e($m['clave']) ?></td>
            <td><?= e($m['nombre']) ?></td>
            <td><?= (int)$m['grado'] ?>°</td>
            <td class="acc">
                <a class="btn sec" href="materias.php?edit=<?= (int)$m['id'] ?>">Editar</a>
                <form method="post" onsubmit="return confirm('¿Eliminar esta materia y sus calificaciones?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                    <button class="danger">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$materias): ?><tr><td colspan="4">No hay materias.</td></tr><?php endif; ?>
</table>
<?php page_end();
