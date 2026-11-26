<?php
require_once __DIR__ . '/../config/db.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $lugar = trim($_POST['lugar'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $cupo = $_POST['cupo_disponible'] ?? '';

    if ($codigo === '' || $nombre === '' || $lugar === '' || $fecha === '' || $hora === '' || $cupo === '') {
        $error = 'Completa todos los campos obligatorios.';
    } elseif (!ctype_digit((string)$cupo)) {
        $error = 'El cupo debe ser un número entero mayor o igual a 0.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO evento (codigo, nombre, descripcion, lugar, fecha, hora, cupo_disponible)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$codigo, $nombre, $descripcion, $lugar, $fecha, $hora, (int)$cupo]);
            $mensaje = 'Evento registrado.';
        } catch (PDOException $e) {
            $error = $e->getCode() === '23000'
                ? 'Ya existe un evento con ese código.'
                : 'Error al guardar: ' . $e->getMessage();
        }
    }
}

$eventos = $pdo->query('SELECT * FROM evento ORDER BY fecha ASC, hora ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-3" style="max-width: 820px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="h5 mb-0">Registro de Eventos</h1>
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
                <div class="col-md-3">
                    <label class="form-label small mb-0">Código *</label>
                    <input type="text" name="codigo" maxlength="15" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small mb-0">Nombre *</label>
                    <input type="text" name="nombre" maxlength="100" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-0">Lugar *</label>
                    <input type="text" name="lugar" maxlength="100" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-0">Descripción</label>
                    <input type="text" name="descripcion" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Fecha *</label>
                    <input type="date" name="fecha" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Hora *</label>
                    <input type="time" name="hora" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Cupo *</label>
                    <input type="number" name="cupo_disponible" min="0" class="form-control form-control-sm" required>
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
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Lugar</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Cupo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($eventos)): ?>
                        <tr><td colspan="6" class="text-center text-muted">Sin eventos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($eventos as $ev): ?>
                            <tr>
                                <td><?= htmlspecialchars($ev['codigo']) ?></td>
                                <td><?= htmlspecialchars($ev['nombre']) ?></td>
                                <td><?= htmlspecialchars($ev['lugar']) ?></td>
                                <td><?= htmlspecialchars($ev['fecha']) ?></td>
                                <td><?= htmlspecialchars($ev['hora']) ?></td>
                                <td><?= (int)$ev['cupo_disponible'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
