<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

requerirRol(['admin']);

$mensaje = '';
$error = '';
$idEvento = isset($_GET['id_evento']) && ctype_digit($_GET['id_evento']) ? (int)$_GET['id_evento'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEventoPost = isset($_POST['id_evento']) && ctype_digit($_POST['id_evento']) ? (int)$_POST['id_evento'] : 0;
    $idEvento = $idEventoPost;
    $fechaAsistencia = trim($_POST['fecha'] ?? date('Y-m-d'));
    $estados = $_POST['estado'] ?? [];

    $dt = DateTime::createFromFormat('!Y-m-d', $fechaAsistencia);
    if ($dt === false || $dt->format('Y-m-d') !== $fechaAsistencia) {
        $error = 'La fecha de asistencia no tiene un formato válido.';
    } elseif ($idEvento <= 0) {
        $error = 'Selecciona un evento válido.';
    } elseif (!is_array($estados) || empty($estados)) {
        $error = 'No se recibieron registros de asistencia.';
    } else {
        $estadosValidos = ['presente', 'ausente', 'justificado'];
        $stmtUpsert = $pdo->prepare(
            'INSERT INTO asistencia (id_inscripcion, fecha, estado)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE fecha = VALUES(fecha), estado = VALUES(estado)'
        );

        $guardados = 0;
        foreach ($estados as $idInscripcion => $estado) {
            if (!ctype_digit((string)$idInscripcion) || !in_array($estado, $estadosValidos, true)) {
                continue;
            }
            $stmtUpsert->execute([(int)$idInscripcion, $fechaAsistencia, $estado]);
            $guardados++;
        }

        $mensaje = "Asistencia guardada correctamente ($guardados registros actualizados).";
    }
}

$eventos = $pdo->query(
    'SELECT id_evento, codigo, nombre, fecha, hora, lugar
     FROM evento
     ORDER BY fecha DESC, hora DESC'
)->fetchAll();

if ($idEvento === 0 && !empty($eventos)) {
    $idEvento = (int)$eventos[0]['id_evento'];
}

$inscritos = [];
if ($idEvento > 0) {
    $stmtInscritos = $pdo->prepare(
        'SELECT i.id_inscripcion, e.carnet, e.nombre, e.apellido,
                a.estado AS asistencia_estado, a.fecha AS asistencia_fecha
         FROM inscripcion i
         INNER JOIN estudiante e ON e.id_estudiante = i.id_estudiante
         LEFT JOIN asistencia a ON a.id_inscripcion = i.id_inscripcion
         WHERE i.id_evento = ?
         ORDER BY e.apellido, e.nombre'
    );
    $stmtInscritos->execute([$idEvento]);
    $inscritos = $stmtInscritos->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Asistencia</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">Control de Asistencia</h1>
            <a href="../index.php" class="btn btn-outline-secondary btn-sm">Volver al inicio</a>
        </div>

        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-success py-2 small"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="get" class="card card-body mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <label for="id_evento" class="form-label form-label-sm">Seleccionar evento</label>
                    <select name="id_evento" id="id_evento" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php if (empty($eventos)): ?>
                            <option value="">No hay eventos registrados</option>
                        <?php else: ?>
                            <?php foreach ($eventos as $ev): ?>
                                <option value="<?= (int)$ev['id_evento'] ?>" <?= $idEvento === (int)$ev['id_evento'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ev['codigo'] . ' - ' . $ev['nombre'] . ' (' . $ev['fecha'] . ' ' . substr($ev['hora'], 0, 5) . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Cargar</button>
                </div>
            </div>
        </form>

        <?php if ($idEvento > 0 && empty($inscritos)): ?>
            <div class="alert alert-info">No hay estudiantes inscritos en este evento.</div>
        <?php elseif (!empty($inscritos)): ?>
            <form method="post" class="card">
                <input type="hidden" name="id_evento" value="<?= $idEvento ?>">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-white">
                    <span class="fw-semibold small">Estudiantes inscritos (<?= count($inscritos) ?>)</span>
                    <div class="d-flex align-items-center gap-2">
                        <label for="fecha" class="form-label small mb-0">Fecha:</label>
                        <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?= htmlspecialchars(date('Y-m-d')) ?>" required>
                        <button type="submit" class="btn btn-success btn-sm">Guardar asistencia</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Carnet</th>
                                <th>Estudiante</th>
                                <th>Última fecha</th>
                                <th>Estado de asistencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscritos as $est): ?>
                                <?php $estadoActual = $est['asistencia_estado'] ?? 'presente'; ?>
                                <tr>
                                    <td><?= htmlspecialchars($est['carnet']) ?></td>
                                    <td><?= htmlspecialchars($est['apellido'] . ', ' . $est['nombre']) ?></td>
                                    <td class="small text-muted"><?= htmlspecialchars($est['asistencia_fecha'] ?? 'Sin registro') ?></td>
                                    <td>
                                        <select name="estado[<?= (int)$est['id_inscripcion'] ?>]" class="form-select form-select-sm" style="max-width: 180px;">
                                            <option value="presente" <?= $estadoActual === 'presente' ? 'selected' : '' ?>>Presente</option>
                                            <option value="ausente" <?= $estadoActual === 'ausente' ? 'selected' : '' ?>>Ausente</option>
                                            <option value="justificado" <?= $estadoActual === 'justificado' ? 'selected' : '' ?>>Justificado</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
