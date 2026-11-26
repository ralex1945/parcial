<?php
session_start();
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
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Control de Inscripciones a Eventos Académicos</h1>
            <div>
                <?php if ($usuario): ?>
                    <span class="me-2"><?= htmlspecialchars($usuario) ?> (<?= htmlspecialchars($rol) ?>)</span>
                    <a href="auth/logout.php" class="btn btn-outline-danger btn-sm">Cerrar sesión</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn btn-primary btn-sm">Iniciar sesión</a>
                    <a href="forms/registroEstudiante.php" class="btn btn-outline-secondary btn-sm">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="list-group">
            <a href="estudiantes/" class="list-group-item list-group-item-action">Estudiantes</a>
            <a href="eventos/" class="list-group-item list-group-item-action">Eventos</a>
            <a href="inscripciones/" class="list-group-item list-group-item-action">Inscripciones</a>
            <a href="asistencia/" class="list-group-item list-group-item-action">Asistencia</a>
            <a href="informes/" class="list-group-item list-group-item-action">Informes</a>
        </div>
    </div>
</body>
</html>
