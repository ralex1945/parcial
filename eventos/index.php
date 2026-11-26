<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/EventoController.php';

$controller = new EventoController($pdo);
$vista = $controller->procesar();

$mensaje = $vista['mensaje'];
$errores = $vista['errores'];
$form = $vista['form'];
$siguienteCodigo = $vista['siguienteCodigo'];
$eventos = $vista['eventos'];
$editando = !empty($form['id_evento']);
$codigoMostrar = $editando ? $form['codigo'] : $siguienteCodigo;
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
    <div class="container py-3" style="max-width: 860px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="h5 mb-0"><?= $editando ? 'Editar Evento' : 'Registro de Eventos' ?></h1>
            <a href="../index.php" class="btn btn-outline-secondary btn-sm">Inicio</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-success py-1 px-2 small mb-2"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger py-1 px-2 small mb-2">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errores as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="card card-body p-3 mb-3">
            <input type="hidden" name="accion" value="guardar">
            <input type="hidden" name="id_evento" value="<?= htmlspecialchars((string)$form['id_evento']) ?>">

            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small mb-0">Código (auto)</label>
                    <input type="text" name="codigo" class="form-control form-control-sm bg-light"
                           value="<?= htmlspecialchars((string)$codigoMostrar) ?>" readonly>
                </div>
                <div class="col-md-5">
                    <label class="form-label small mb-0">Nombre de evento *</label>
                    <input type="text" name="nombre" maxlength="100" class="form-control form-control-sm"
                           value="<?= htmlspecialchars((string)$form['nombre']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-0">Lugar *</label>
                    <input type="text" name="lugar" maxlength="100" class="form-control form-control-sm"
                           value="<?= htmlspecialchars((string)$form['lugar']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-0">Descripción</label>
                    <input type="text" name="descripcion" class="form-control form-control-sm"
                           value="<?= htmlspecialchars((string)$form['descripcion']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Fecha *</label>
                    <input type="date" name="fecha" class="form-control form-control-sm"
                           value="<?= htmlspecialchars((string)$form['fecha']) ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Hora *</label>
                    <input type="time" name="hora" class="form-control form-control-sm"
                           value="<?= htmlspecialchars((string)$form['hora']) ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Cupo *</label>
                    <input type="number" name="cupo_disponible" min="0" class="form-control form-control-sm"
                           value="<?= htmlspecialchars((string)$form['cupo_disponible']) ?>" required>
                </div>
            </div>
            <div class="mt-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= $editando ? 'Actualizar evento' : 'Agregar evento' ?></button>
                <?php if ($editando): ?>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">Cancelar</a>
                <?php endif; ?>
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
                        <th style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($eventos)): ?>
                        <tr><td colspan="7" class="text-center text-muted">Sin eventos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($eventos as $ev): ?>
                            <tr>
                                <td><?= htmlspecialchars($ev['codigo']) ?></td>
                                <td><?= htmlspecialchars($ev['nombre']) ?></td>
                                <td><?= htmlspecialchars($ev['lugar']) ?></td>
                                <td><?= htmlspecialchars($ev['fecha']) ?></td>
                                <td><?= htmlspecialchars(substr($ev['hora'], 0, 5)) ?></td>
                                <td><?= (int)$ev['cupo_disponible'] ?></td>
                                <td>
                                    <a href="?editar=<?= (int)$ev['id_evento'] ?>" class="btn btn-outline-primary btn-sm py-0 px-1">Editar</a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar evento?');">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_evento" value="<?= (int)$ev['id_evento'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1">Borrar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
