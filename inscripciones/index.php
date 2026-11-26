<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

$estudianteSesion = null;
if (!empty($_SESSION['usuario'])) {
    $stmtEst = $pdo->prepare(
        'SELECT e.id_estudiante, e.carnet, e.nombre, e.apellido, u.usuario
         FROM usuario u
         INNER JOIN estudiante e ON e.id_usuario = u.id_usuario
         WHERE u.usuario = ? AND u.rol = ?'
    );
    $stmtEst->execute([$_SESSION['usuario'], 'estudiante']);
    $estudianteSesion = $stmtEst->fetch() ?: null;
}

$mensaje = '';
$error = '';
$usuarioInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_evento = trim($_POST['id_evento'] ?? '');
    $fecha_inscripcion = trim($_POST['fecha_inscripcion'] ?? '');
    $id_estudiante = null;
    $fechaNormalizada = null;

    if ($estudianteSesion !== null) {
        $id_estudiante = (int)$estudianteSesion['id_estudiante'];
        if ($id_evento === '') {
            $error = 'Completa todos los campos obligatorios.';
        } elseif (!ctype_digit($id_evento) || (int)$id_evento <= 0) {
            $error = 'Selecciona un evento válido.';
        }
    } else {
        $usuarioInput = trim($_POST['usuario'] ?? '');
        $claveInput = $_POST['clave'] ?? '';
        if ($usuarioInput === '' || $claveInput === '' || $id_evento === '') {
            $error = 'Completa todos los campos obligatorios.';
        } elseif (!ctype_digit($id_evento) || (int)$id_evento <= 0) {
            $error = 'Selecciona un evento válido.';
        }
    }

    if ($error === '' && $fecha_inscripcion !== '') {
        foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s'] as $formato) {
            $dt = DateTime::createFromFormat('!' . $formato, $fecha_inscripcion);
            if ($dt !== false && $dt->format($formato) === $fecha_inscripcion) {
                $fechaNormalizada = $dt->format('Y-m-d H:i:s');
                break;
            }
        }
        if ($fechaNormalizada === null) {
            $error = 'La fecha de inscripción no tiene un formato válido.';
        }
    }

    if ($error === '' && $estudianteSesion === null) {
        $stmtUser = $pdo->prepare(
            'SELECT u.id_usuario, u.clave_hash, u.rol, e.id_estudiante
             FROM usuario u
             LEFT JOIN estudiante e ON e.id_usuario = u.id_usuario
             WHERE u.usuario = ?'
        );
        $stmtUser->execute([$usuarioInput]);
        $userRow = $stmtUser->fetch();

        if (!$userRow || !password_verify($claveInput, $userRow['clave_hash'])) {
            $error = 'Usuario o clave incorrectos.';
        } elseif ($userRow['rol'] !== 'estudiante' || empty($userRow['id_estudiante'])) {
            $error = 'Solo los estudiantes registrados pueden inscribirse a eventos.';
        } else {
            $id_estudiante = (int)$userRow['id_estudiante'];
        }
    }

    if ($error === '') {
        try {
            $pdo->beginTransaction();

            $stmtEvento = $pdo->prepare('SELECT cupo_disponible FROM evento WHERE id_evento = ? FOR UPDATE');
            $stmtEvento->execute([(int)$id_evento]);
            $evento = $stmtEvento->fetch();

            if (!$evento) {
                $pdo->rollBack();
                $error = 'El evento seleccionado no existe.';
            } elseif ((int)$evento['cupo_disponible'] <= 0) {
                $pdo->rollBack();
                $error = 'No hay cupo disponible para el evento seleccionado.';
            } else {
                if ($fechaNormalizada !== null) {
                    $stmt = $pdo->prepare(
                        'INSERT INTO inscripcion (id_estudiante, id_evento, fecha_inscripcion)
                         VALUES (?, ?, ?)'
                    );
                    $stmt->execute([$id_estudiante, (int)$id_evento, $fechaNormalizada]);
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO inscripcion (id_estudiante, id_evento)
                         VALUES (?, ?)'
                    );
                    $stmt->execute([$id_estudiante, (int)$id_evento]);
                }

                $stmtCupo = $pdo->prepare(
                    'UPDATE evento SET cupo_disponible = cupo_disponible - 1 WHERE id_evento = ?'
                );
                $stmtCupo->execute([(int)$id_evento]);

                $pdo->commit();
                $mensaje = 'Inscripción registrada.';
                $usuarioInput = '';
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mysqlErr = (int)($e->errorInfo[1] ?? 0);
            if ($mysqlErr === 1062) {
                $error = 'El estudiante ya está inscrito en este evento.';
            } elseif ($mysqlErr === 1452) {
                $error = 'El estudiante o evento seleccionado no existe.';
            } else {
                $error = 'Error al guardar: ' . $e->getMessage();
            }
        }
    }
}

$eventos = $pdo->query(
    'SELECT id_evento, codigo, nombre, fecha, hora, cupo_disponible FROM evento ORDER BY fecha ASC, hora ASC'
)->fetchAll();

$baseInscripcionesSql =
    'SELECT i.id_inscripcion,
            e.carnet,
            e.nombre AS estudiante_nombre,
            e.apellido AS estudiante_apellido,
            ev.codigo AS evento_codigo,
            ev.nombre AS evento_nombre,
            i.fecha_inscripcion
     FROM inscripcion i
     INNER JOIN estudiante e ON i.id_estudiante = e.id_estudiante
     INNER JOIN evento ev ON i.id_evento = ev.id_evento';

if ($estudianteSesion !== null) {
    $stmtIns = $pdo->prepare(
        $baseInscripcionesSql . ' WHERE i.id_estudiante = ? ORDER BY i.fecha_inscripcion DESC, i.id_inscripcion DESC'
    );
    $stmtIns->execute([(int)$estudianteSesion['id_estudiante']]);
    $inscripciones = $stmtIns->fetchAll();
} else {
    $inscripciones = $pdo->query(
        $baseInscripcionesSql . ' ORDER BY i.fecha_inscripcion DESC, i.id_inscripcion DESC'
    )->fetchAll();
}

$colEvento = $estudianteSesion !== null ? 'col-md-4' : 'col-md-7';
$colFecha = $estudianteSesion !== null ? 'col-md-4' : 'col-md-5';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripciones</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-3" style="max-width: 820px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="h5 mb-0">Registro de Inscripciones</h1>
            <a href="../index.php" class="btn btn-outline-secondary btn-sm">Inicio</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-success py-1 px-2 small mb-2"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-1 px-2 small mb-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="card card-body p-3 mb-3">
            <div class="row g-2">
                <?php if ($estudianteSesion !== null): ?>
                    <div class="col-md-4">
                        <label class="form-label small mb-0">Estudiante</label>
                        <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($estudianteSesion['carnet'] . ' - ' . $estudianteSesion['nombre'] . ' ' . $estudianteSesion['apellido'] . ' (' . $estudianteSesion['usuario'] . ')') ?>" readonly disabled>
                    </div>
                <?php else: ?>
                    <div class="col-md-6">
                        <label class="form-label small mb-0">Usuario *</label>
                        <input type="text" name="usuario" maxlength="50" class="form-control form-control-sm" value="<?= htmlspecialchars($usuarioInput) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-0">Clave *</label>
                        <input type="password" name="clave" class="form-control form-control-sm" required>
                    </div>
                <?php endif; ?>
                <div class="<?= $colEvento ?>">
                    <label class="form-label small mb-0">Evento *</label>
                    <select name="id_evento" class="form-select form-select-sm" required>
                        <option value="">Seleccione un evento</option>
                        <?php foreach ($eventos as $ev): ?>
                            <option value="<?= (int)$ev['id_evento'] ?>" <?= (int)$ev['cupo_disponible'] <= 0 ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($ev['codigo'] . ' - ' . $ev['nombre'] . ' (' . $ev['fecha'] . ' | Cupo: ' . (int)$ev['cupo_disponible'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="<?= $colFecha ?>">
                    <label class="form-label small mb-0">Fecha de inscripción</label>
                    <input type="datetime-local" name="fecha_inscripcion" class="form-control form-control-sm">
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-sm small bg-white mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Estudiante</th>
                        <th>Evento</th>
                        <th>Fecha de inscripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inscripciones)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Sin inscripciones registradas.</td></tr>
                    <?php else: ?>
                        <?php foreach ($inscripciones as $ins): ?>
                            <tr>
                                <td><?= htmlspecialchars($ins['carnet'] . ' - ' . $ins['estudiante_nombre'] . ' ' . $ins['estudiante_apellido']) ?></td>
                                <td><?= htmlspecialchars($ins['evento_codigo'] . ' - ' . $ins['evento_nombre']) ?></td>
                                <td><?= htmlspecialchars($ins['fecha_inscripcion']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
