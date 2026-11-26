<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requerirLogin(string $rutaLogin = '../auth/login.php'): void
{
    if (empty($_SESSION['id_usuario'])) {
        header("Location: $rutaLogin");
        exit;
    }
}

function requerirRol(array $rolesPermitidos, string $rutaInicio = '../index.php', string $rutaLogin = '../auth/login.php'): void
{
    requerirLogin($rutaLogin);

    $rol = $_SESSION['rol'] ?? '';
    if (!in_array($rol, $rolesPermitidos, true)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Acceso denegado</title>';
        echo '<link rel="stylesheet" href="../assets/css/bootstrap.min.css"></head>';
        echo '<body class="bg-light"><div class="container py-4" style="max-width:500px;">';
        echo '<div class="alert alert-danger">No tienes permiso para acceder a este módulo.</div>';
        echo '<a href="' . htmlspecialchars($rutaInicio) . '" class="btn btn-secondary btn-sm">Volver al inicio</a>';
        echo '</div></body></html>';
        exit;
    }
}

function esAdmin(): bool
{
    return ($_SESSION['rol'] ?? '') === 'admin';
}

function esEstudiante(): bool
{
    return ($_SESSION['rol'] ?? '') === 'estudiante';
}
