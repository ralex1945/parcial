<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

requerirRol(['admin', 'estudiante']);

$esAdmin = esAdmin();
$estudianteActual = null;
$registros = [];

if ($esAdmin) {
    $filtroEstudiante = isset($_GET['id_estudiante']) && ctype_digit($_GET['id_estudiante']) ? (int)$_GET['id_estudiante'] : 0;
    $filtroEvento = isset($_GET['id_evento']) && ctype_digit($_GET['id_evento']) ? (int)$_GET['id_evento'] : 0;

    $estudiantes = $pdo->query(
        'SELECT id_estudiante, carnet, nombre, apellido FROM estudiante ORDER BY apellido, nombre'
    )->fetchAll();

    $eventos = $pdo->query(
        'SELECT id_evento, codigo, nombre, fecha FROM evento ORDER BY fecha DESC, nombre'
    )->fetchAll();

    $sql = 'SELECT e.carnet, e.nombre AS est_nombre, e.apellido AS est_apellido,
                   ev.codigo, ev.nombre AS ev_nombre, ev.lugar, ev.fecha AS ev_fecha, ev.hora AS ev_hora,
                   i.fecha_inscripcion, a.estado AS asistencia_estado, a.fecha AS asistencia_fecha
            FROM inscripcion i
            INNER JOIN estudiante e ON e.id_estudiante = i.id_estudiante
            INNER JOIN evento ev ON ev.id_evento = i.id_evento
            LEFT JOIN asistencia a ON a.id_inscripcion = i.id_inscripcion
            WHERE 1=1';
    $params = [];

    if ($filtroEstudiante > 0) {
        $sql .= ' AND i.id_estudiante = ?';
        $params[] = $filtroEstudiante;
    }
    if ($filtroEvento > 0) {
        $sql .= ' AND i.id_evento = ?';
        $params[] = $filtroEvento;
    }

    $sql .= ' ORDER BY ev.fecha DESC, ev.hora DESC, e.apellido, e.nombre';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $registros = $stmt->fetchAll();
} else {
    $stmtEst = $pdo->prepare(
        'SELECT id_estudiante, carnet, nombre, apellido
         FROM estudiante
         WHERE id_usuario = ?'
    );
    $stmtEst->execute([$_SESSION['id_usuario']]);
    $estudianteActual = $stmtEst->fetch() ?: null;

    if ($estudianteActual !== null) {
        $stmt = $pdo->prepare(
            'SELECT ev.codigo, ev.nombre AS ev_nombre, ev.lugar, ev.fecha AS ev_fecha, ev.hora AS ev_hora,
                    i.fecha_inscripcion, a.estado AS asistencia_estado, a.fecha AS asistencia_fecha
             FROM inscripcion i
             INNER JOIN evento ev ON ev.id_evento = i.id_evento
             LEFT JOIN asistencia a ON a.id_inscripcion = i.id_inscripcion
             WHERE i.id_estudiante = ?
             ORDER BY ev.fecha DESC, ev.hora DESC'
        );
        $stmt->execute([$estudianteActual['id_estudiante']]);
        $registros = $stmt->fetchAll();
    }
}

$totales = [
    'total' => count($registros),
    'presente' => 0,
    'ausente' => 0,
    'justificado' => 0,
    'pendiente' => 0,
];

foreach ($registros as $fila) {
    $estado = $fila['asistencia_estado'] ?? null;
    if ($estado !== null && isset($totales[$estado])) {
        $totales[$estado]++;
    } else {
        $totales['pendiente']++;
    }
}

function badgeAsistencia(?string $estado): string
{
    return match ($estado) {
        'presente' => '<span class="badge bg-success">Presente</span>',
        'ausente' => '<span class="badge bg-danger">Ausente</span>',
        'justificado' => '<span class="badge bg-warning text-dark">Justificado</span>',
        default => '<span class="badge bg-secondary">Pendiente</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $esAdmin ? 'Informes de Eventos y Asistencia' : 'Mis eventos y asistencia' ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-1"><?= $esAdmin ? 'Informes de Eventos y Asistencia' : 'Mis eventos y asistencia' ?></h1>
                <?php if (!$esAdmin && $estudianteActual !== null): ?>
                    <p class="text-muted small mb-0">
                        <?= htmlspecialchars($estudianteActual['nombre'] . ' ' . $estudianteActual['apellido']) ?>
                        (Carnet: <?= htmlspecialchars($estudianteActual['carnet']) ?>)
                    </p>
                <?php endif; ?>
            </div>
            <a href="../index.php" class="btn btn-outline-secondary btn-sm">Volver al inicio</a>
        </div>

        <?php if ($esAdmin): ?>
            <form method="get" class="card card-body mb-4">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label for="id_estudiante" class="form-label form-label-sm">Estudiante</label>
                        <select name="id_estudiante" id="id_estudiante" class="form-select form-select-sm">
                            <option value="">Todos los estudiantes</option>
                            <?php foreach ($estudiantes as $est): ?>
                                <option value="<?= (int)$est['id_estudiante'] ?>" <?= $filtroEstudiante === (int)$est['id_estudiante'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($est['carnet'] . ' - ' . $est['apellido'] . ', ' . $est['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="id_evento" class="form-label form-label-sm">Evento</label>
                        <select name="id_evento" id="id_evento" class="form-select form-select-sm">
                            <option value="">Todos los eventos</option>
                            <?php foreach ($eventos as $ev): ?>
                                <option value="<?= (int)$ev['id_evento'] ?>" <?= $filtroEvento === (int)$ev['id_evento'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ev['codigo'] . ' - ' . $ev['nombre'] . ' (' . $ev['fecha'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <div class="row g-2 mb-4">
            <div class="col-6 col-md">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <div class="small text-muted">Inscritos</div>
                        <div class="h5 mb-0"><?= $totales['total'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <div class="small text-muted">Presentes</div>
                        <div class="h5 mb-0 text-success"><?= $totales['presente'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <div class="small text-muted">Ausentes</div>
                        <div class="h5 mb-0 text-danger"><?= $totales['ausente'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <div class="small text-muted">Justificados</div>
                        <div class="h5 mb-0 text-warning"><?= $totales['justificado'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <div class="small text-muted">Pendientes</div>
                        <div class="h5 mb-0 text-secondary"><?= $totales['pendiente'] ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$esAdmin && $estudianteActual === null): ?>
            <div class="alert alert-warning">
                No se encontró un perfil de estudiante asociado a tu usuario.
            </div>
        <?php elseif (empty($registros)): ?>
            <div class="alert alert-info">
                <?= $esAdmin ? 'No hay inscripciones registradas con los filtros seleccionados.' : 'Aún no te has inscrito a ningún evento.' ?>
            </div>
        <?php else: ?>
            <div class="table-responsive bg-white rounded border">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <?php if ($esAdmin): ?>
                                <th>Carnet</th>
                                <th>Estudiante</th>
                            <?php endif; ?>
                            <th>Código</th>
                            <th>Evento</th>
                            <th>Lugar</th>
                            <th>Fecha del evento</th>
                            <th>Fecha inscripción</th>
                            <th>Asistencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registros as $fila): ?>
                            <tr>
                                <?php if ($esAdmin): ?>
                                    <td><?= htmlspecialchars($fila['carnet']) ?></td>
                                    <td><?= htmlspecialchars($fila['est_apellido'] . ', ' . $fila['est_nombre']) ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($fila['codigo']) ?></td>
                                <td><?= htmlspecialchars($fila['ev_nombre']) ?></td>
                                <td><?= htmlspecialchars($fila['lugar']) ?></td>
                                <td><?= htmlspecialchars($fila['ev_fecha'] . ' ' . substr($fila['ev_hora'], 0, 5)) ?></td>
                                <td><?= htmlspecialchars($fila['fecha_inscripcion']) ?></td>
                                <td><?= badgeAsistencia($fila['asistencia_estado'] ?? null) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
