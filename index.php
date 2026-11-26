<?php
require_once __DIR__ . '/config/auth.php';

$usuario = $_SESSION['usuario'] ?? null;
$rol = $_SESSION['rol'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos Académicos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-4" style="max-width: 720px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">Control de Inscripciones a Eventos Académicos</h1>
            <div>
                <?php if ($usuario): ?>
                    <span class="me-2 small"><?= htmlspecialchars($usuario) ?> (<?= htmlspecialchars($rol) ?>)</span>
                    <a href="auth/logout.php" class="btn btn-outline-danger btn-sm">Cerrar sesión</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn btn-primary btn-sm">Iniciar sesión</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$usuario): ?>
            <div class="alert alert-info py-2 small">
                Inicia sesión para acceder a las opciones del sistema.
            </div>
        <?php elseif (esAdmin()): ?>
            <div class="list-group">
                <a href="estudiantes/" class="list-group-item list-group-item-action">Estudiantes</a>
                <a href="eventos/eventos.php" class="list-group-item list-group-item-action">Eventos</a>
                <a href="asistencia/" class="list-group-item list-group-item-action">Asistencia</a>
                <a href="informes/" class="list-group-item list-group-item-action">Informes</a>
            </div>
        <?php elseif (esEstudiante()): ?>
            <div class="list-group">
                <a href="inscripciones/" class="list-group-item list-group-item-action">Inscripciones a eventos</a>
                <a href="informes/" class="list-group-item list-group-item-action">Mis eventos y asistencia</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
