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

if ($opcion === 'registro') {
    $carnet = strtoupper(trim((string) ($_POST['carnet'] ?? '')));
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $apellido = trim((string) ($_POST['apellido'] ?? ''));

    $_SESSION['registro_datos'] = [
        'carnet' => $carnet,
        'nombre' => $nombre,
        'apellido' => $apellido,
        'usuario' => $usuario,
    ];

    $largo = function ($texto) {
        return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
    };
    $soloLetras = '/^[\p{L}\p{M}]+(?: +[\p{L}\p{M}]+)*$/u';
    $errores = [];

    if (!preg_match('/^[A-Z0-9-]{1,10}$/', $carnet)) {
        $errores[] = 'El carnet debe tener hasta 10 caracteres (letras, numeros o guion).';
    }
    if ($nombre === '' || $largo($nombre) > 60 || preg_match($soloLetras, $nombre) !== 1) {
        $errores[] = 'El nombre solo puede contener letras y espacios (maximo 60).';
    }
    if ($apellido === '' || $largo($apellido) > 60 || preg_match($soloLetras, $apellido) !== 1) {
        $errores[] = 'El apellido solo puede contener letras y espacios (maximo 60).';
    }
    if ($largo($usuario) < 3 || $largo($usuario) > 50 || preg_match('/\s/u', $usuario)) {
        $errores[] = 'El usuario debe tener entre 3 y 50 caracteres y sin espacios.';
    }

    if ($errores) {
        $_SESSION['auth_error'] = implode(' ', $errores);
        header('Location: registro.php');
        exit();
    }

    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $claveGenerada = '';
    for ($i = 0; $i < 12; $i++) {
        $claveGenerada .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }

    try {
        $dao->registrarEstudiante($usuario, password_hash($claveGenerada, PASSWORD_DEFAULT), $carnet, $nombre, $apellido);
    } catch (mysqli_sql_exception $e) {
        $_SESSION['auth_error'] = ((int) $e->getCode() === 1062)
            ? 'Ese carnet o usuario ya esta registrado.'
            : 'No se pudo crear la cuenta. Intente de nuevo.';
        header('Location: registro.php');
        exit();
    }

    unset($_SESSION['registro_datos']);
    $_SESSION['auth_ok'] = "Cuenta creada. Usuario: $usuario | Clave: $claveGenerada (guardela, la necesitara para ingresar).";
    header('Location: login.php');
    exit();
}

header('Location: login.php');
exit();
