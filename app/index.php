<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';
require_login();

$pdo = db();
$totAlumnos = (int)$pdo->query('SELECT COUNT(*) FROM alumnos')->fetchColumn();
$totMaterias = (int)$pdo->query('SELECT COUNT(*) FROM materias')->fetchColumn();
$totCal = (int)$pdo->query('SELECT COUNT(*) FROM calificaciones')->fetchColumn();
$prom = $pdo->query('SELECT AVG(calificacion) FROM calificaciones')->fetchColumn();
$ultimos = $pdo->query('SELECT matricula, nombre, apellidos, grado, grupo FROM alumnos ORDER BY id DESC LIMIT 5')->fetchAll();

page_start('Inicio');
?>
<div class="grid" style="margin-bottom:20px">
    <div class="stat"><b><?= $totAlumnos ?></b>Alumnos</div>
    <div class="stat"><b><?= $totMaterias ?></b>Materias</div>
    <div class="stat"><b><?= $totCal ?></b>Calificaciones</div>
    <div class="stat"><b><?= $prom !== null ? number_format((float)$prom, 1) : '—' ?></b>Promedio general</div>
</div>
<div class="card">
    <h3>Últimos alumnos registrados</h3>
    <table>
        <tr><th>Matrícula</th><th>Nombre</th><th>Grado/Grupo</th></tr>
        <?php foreach ($ultimos as $a): ?>
            <tr>
                <td><?= e($a['matricula']) ?></td>
                <td><?= e($a['nombre'] . ' ' . $a['apellidos']) ?></td>
                <td><?= e($a['grado'] . '° ' . $a['grupo']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$ultimos): ?><tr><td colspan="3">Aún no hay alumnos.</td></tr><?php endif; ?>
    </table>
</div>
<?php page_end();
