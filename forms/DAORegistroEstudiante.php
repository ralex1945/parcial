<?php
header('Content-Type: application/json; charset=utf-8');

function responder($codigo, $datos)
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function largoTexto($texto)
{
    return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
}

function generarClave($largo = 12)
{
    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $clave = '';
    $ultimoIndice = strlen($caracteres) - 1;

    for ($i = 0; $i < $largo; $i++) {
        $clave .= $caracteres[random_int(0, $ultimoIndice)];
    }

    return $clave;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['ok' => false, 'mensaje' => 'Usa el formulario para registrar al estudiante.']);
}

$carnet = strtoupper(trim((string) ($_POST['carnet'] ?? '')));
$nombre = trim((string) ($_POST['nombre'] ?? ''));
$apellido = trim((string) ($_POST['apellido'] ?? ''));
$usuario = trim((string) ($_POST['usuario'] ?? ''));
$errores = [];

if (!preg_match('/^[A-Z0-9-]{1,10}$/', $carnet)) {
    $errores[] = 'Escribe un carnet de hasta 10 caracteres, usando letras, números o guion.';
}
if ($nombre === '' || largoTexto($nombre) > 60) {
    $errores[] = 'Escribe el nombre del estudiante (máximo 60 caracteres).';
} elseif (preg_match('/^[\p{L}\p{M}]+(?: +[\p{L}\p{M}]+)*$/u', $nombre) !== 1) {
    $errores[] = 'El nombre solo puede contener letras y espacios.';
}
if ($apellido === '' || largoTexto($apellido) > 60) {
    $errores[] = 'Escribe el apellido (máximo 60 caracteres).';
} elseif (preg_match('/^[\p{L}\p{M}]+(?: +[\p{L}\p{M}]+)*$/u', $apellido) !== 1) {
    $errores[] = 'El apellido solo puede contener letras y espacios.';
}
if (largoTexto($usuario) < 3 || largoTexto($usuario) > 50 || preg_match('/\s/u', $usuario)) {
    $errores[] = 'El usuario debe tener entre 3 y 50 caracteres y no llevar espacios.';
}

if ($errores) {
    responder(422, ['ok' => false, 'mensaje' => implode(' ', $errores)]);
}

$pdo = null;

try {
    require_once __DIR__ . '/../config/db.php';
    $claveGenerada = generarClave();
    $claveHash = password_hash($claveGenerada, PASSWORD_DEFAULT);
    $pdo->beginTransaction();

    $guardarUsuario = $pdo->prepare(
        'INSERT INTO usuario (usuario, clave_hash) VALUES (:usuario, :clave_hash)'
    );
    $guardarUsuario->execute([
        'usuario' => $usuario,
        'clave_hash' => $claveHash,
    ]);
    $idUsuario = (int) $pdo->lastInsertId();

    $guardarEstudiante = $pdo->prepare(
        'INSERT INTO estudiante (carnet, nombre, apellido, id_usuario) VALUES (?, ?, ?, ?)'
    );
    $guardarEstudiante->execute([$carnet, $nombre, $apellido, $idUsuario]);
    $pdo->commit();

    responder(200, [
        'ok' => true,
        'clave' => $claveGenerada,
    ]);
} catch (PDOException $error) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (isset($error->errorInfo[1]) && (int) $error->errorInfo[1] === 1062) {
        responder(409, ['ok' => false, 'mensaje' => 'Ese carnet o usuario ya está registrado. Revisa los datos e inténtalo de nuevo.']);
    }

    responder(500, ['ok' => false, 'mensaje' => 'No se pudo guardar. Revisa que MySQL esté iniciado y que la base de datos esté importada.']);
} catch (Throwable $error) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    responder(500, ['ok' => false, 'mensaje' => 'Ocurrió un error al registrar al estudiante.']);
}
