<?php
session_start();

require_once __DIR__ . '/UsuarioDAO.php';

$conexion = null;

if (!file_exists(__DIR__ . '/../config/conexion.php')) {
    exit('Conexion pendiente de configurar');
}

require_once __DIR__ . '/../config/conexion.php';

if (!$conexion instanceof mysqli) {
    exit('Conexion no disponible');
}

$usuario = trim($_POST['usuario'] ?? '');
$clave = $_POST['clave'] ?? '';
$opcion = $_POST['opcion'] ?? '';

$dao = new UsuarioDAO($conexion);

if ($opcion === 'login') {
    if ($usuario === '' || $clave === '') {
        $_SESSION['auth_error'] = 'Complete todos los campos.';
        header('Location: login.php');
        exit();
    }

    $datos = $dao->buscarUsuario($usuario);

    if ($datos && password_verify($clave, $datos['clave_hash'])) {
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = $datos['id_usuario'];
        $_SESSION['usuario'] = $datos['usuario'];
        $_SESSION['rol'] = $datos['rol'];

        header('Location: ../index.php');
        exit();
    }

    $_SESSION['auth_error'] = 'Usuario o clave incorrectos.';
    header('Location: login.php');
    exit();
}

header('Location: login.php');
exit();
