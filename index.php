<?php
session_start();
$usuario = $_SESSION['usuario'] ?? null;
$rol = $_SESSION['rol'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eventos Académicos</title>
</head>
<body>
    <h1>Control de Inscripciones a Eventos Académicos</h1>

    <?php if ($usuario): ?>
        <p>Usuario: <?= htmlspecialchars($usuario) ?> (<?= htmlspecialchars($rol) ?>) | <a href="auth/logout.php">Cerrar sesión</a></p>
    <?php else: ?>
        <p><a href="auth/login.php">Iniciar sesión</a> | <a href="auth/registro.php">Registrarse</a></p>
    <?php endif; ?>

    <h2>Menú</h2>
    <ul>
        <li><a href="estudiantes/">Estudiantes</a></li>
        <li><a href="eventos/">Eventos</a></li>
        <li><a href="inscripciones/">Inscripciones</a></li>
        <li><a href="asistencia/">Asistencia</a></li>
        <li><a href="informes/">Informes</a></li>
    </ul>
</body>
</html>
