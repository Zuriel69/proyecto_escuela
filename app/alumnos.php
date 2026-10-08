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
        $pdo->prepare('DELETE FROM alumnos WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        flash('Alumno eliminado.');
        redirect('alumnos.php');
    }

    if ($accion === 'guardar') {
        $id = (int)($_POST['id'] ?? 0);
        $matricula = trim($_POST['matricula'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $fecha = trim($_POST['fecha_nacimiento'] ?? '') ?: null;
        $email = trim($_POST['email'] ?? '') ?: null;
        $grado = (int)($_POST['grado'] ?? 1);
        $grupo = strtoupper(trim($_POST['grupo'] ?? 'A'));
        $pass = (string)($_POST['password'] ?? '');

        if ($matricula === '' || $nombre === '' || $apellidos === '') {
            flash('Matrícula, nombre y apellidos son obligatorios.', 'error');
            redirect('alumnos.php' . ($id ? "?edit=$id" : ''));
        }
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('El correo no es válido.', 'error');
            redirect('alumnos.php' . ($id ? "?edit=$id" : ''));
        }

        try {
            if ($id) {
                $pdo->prepare('UPDATE alumnos SET matricula=?, nombre=?, apellidos=?, fecha_nacimiento=?, email=?, grado=?, grupo=? WHERE id=?')
                    ->execute([$matricula, $nombre, $apellidos, $fecha, $email, $grado, $grupo, $id]);
                if ($pass !== '') {
                    $pdo->prepare('UPDATE alumnos SET password=? WHERE id=?')
                        ->execute([password_hash($pass, PASSWORD_DEFAULT), $id]);
                }
                flash('Alumno actualizado.');
            } else {
                $pdo->prepare('INSERT INTO alumnos (matricula, nombre, apellidos, fecha_nacimiento, email, grado, grupo, password) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute([$matricula, $nombre, $apellidos, $fecha, $email, $grado, $grupo,
                        password_hash($pass !== '' ? $pass : $matricula, PASSWORD_DEFAULT)]);
                flash($pass !== ''
                    ? 'Alumno registrado.'
                    : 'Alumno registrado. Su contraseña inicial es su matrícula.');
            }
        } catch (PDOException $ex) {
            flash($ex->getCode() === '23000' ? 'Ya existe un alumno con esa matrícula.' : 'Error al guardar.', 'error');
        }
        redirect('alumnos.php');
    }
}

// Datos para el formulario (edición) y listado
$edit = ['id' => 0, 'matricula' => '', 'nombre' => '', 'apellidos' => '', 'fecha_nacimiento' => '', 'email' => '', 'grado' => 1, 'grupo' => 'A'];
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM alumnos WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: $edit;
}

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $st = $pdo->prepare('SELECT * FROM alumnos WHERE matricula LIKE ? OR nombre LIKE ? OR apellidos LIKE ? ORDER BY apellidos, nombre');
    $like = "%$q%";
    $st->execute([$like, $like, $like]);
} else {
    $st = $pdo->query('SELECT * FROM alumnos ORDER BY apellidos, nombre');
}
$alumnos = $st->fetchAll();

page_start('Alumnos');
?>
<div class="card">
    <h3><?= $edit['id'] ? 'Editar alumno' : 'Nuevo alumno' ?></h3>
    <form method="post" class="fields">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
        <label>Matrícula <input name="matricula" required maxlength="20" value="<?= e($edit['matricula']) ?>"></label>
        <label>Nombre(s) <input name="nombre" required maxlength="80" value="<?= e($edit['nombre']) ?>"></label>
        <label>Apellidos <input name="apellidos" required maxlength="100" value="<?= e($edit['apellidos']) ?>"></label>
        <label>Fecha de nacimiento <input type="date" name="fecha_nacimiento" value="<?= e($edit['fecha_nacimiento']) ?>"></label>
        <label>Correo <input type="email" name="email" maxlength="120" value="<?= e($edit['email']) ?>"></label>
        <label>Grado
            <select name="grado">
                <?php for ($g = 1; $g <= 9; $g++): ?>
                    <option value="<?= $g ?>" <?= (int)$edit['grado'] === $g ? 'selected' : '' ?>><?= $g ?>°</option>
                <?php endfor; ?>
            </select>
        </label>
        <label>Grupo <input name="grupo" maxlength="5" value="<?= e($edit['grupo']) ?>"></label>
        <label>Contraseña del alumno <input type="text" name="password" autocomplete="off"
               placeholder="<?= $edit['id'] ? 'Vacío = no cambiar' : 'Vacío = su matrícula' ?>"></label>
        <div>
            <button>Guardar</button>
            <?php if ($edit['id']): ?><a class="btn sec" href="alumnos.php">Cancelar</a><?php endif; ?>
        </div>
    </form>
</div>

<form method="get" class="search">
    <input name="q" placeholder="Buscar por matrícula, nombre o apellidos…" value="<?= e($q) ?>">
    <button>Buscar</button>
    <?php if ($q !== ''): ?><a class="btn sec" href="alumnos.php">Limpiar</a><?php endif; ?>
</form>

<table>
    <tr><th>Matrícula</th><th>Nombre</th><th>Grado/Grupo</th><th>Correo</th><th></th></tr>
    <?php foreach ($alumnos as $a): ?>
        <tr>
            <td><?= e($a['matricula']) ?></td>
            <td><?= e($a['apellidos'] . ', ' . $a['nombre']) ?></td>
            <td><?= e($a['grado'] . '° ' . $a['grupo']) ?></td>
            <td><?= e($a['email']) ?></td>
            <td class="acc">
                <a class="btn" href="calificaciones.php?alumno_id=<?= (int)$a['id'] ?>">Calificaciones</a>
                <a class="btn sec" href="alumnos.php?edit=<?= (int)$a['id'] ?>">Editar</a>
                <form method="post" onsubmit="return confirm('¿Eliminar este alumno y sus calificaciones?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                    <button class="danger">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$alumnos): ?><tr><td colspan="5">No hay alumnos.</td></tr><?php endif; ?>
</table>
<?php page_end();
