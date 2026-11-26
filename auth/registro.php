<?php
session_start();

if (!empty($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit();
}

$error = $_SESSION['auth_error'] ?? '';
$datos = $_SESSION['registro_datos'] ?? [];
unset($_SESSION['auth_error'], $_SESSION['registro_datos']);

function valorPrevio($datos, $campo)
{
    return htmlspecialchars($datos[$campo] ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de cuenta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login {
            background: white;
            padding: 30px;
            width: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 10px #ccc;
        }
        h2 {
            text-align: center;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0 15px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #243b55;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        p {
            text-align: center;
        }
        .msg-error {
            color: #b02a37;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
        }
        .nota {
            font-size: 13px;
            color: #555;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login">
        <h2>Crear cuenta</h2>

        <?php if ($error): ?>
            <div class="msg-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="controlador.php" method="POST" autocomplete="off">
            <label for="carnet">Carnet:</label>
            <input type="text" id="carnet" name="carnet" maxlength="10" placeholder="Ej: AB12345" value="<?= valorPrevio($datos, 'carnet') ?>" required>

            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" maxlength="60" placeholder="Ingrese su nombre" value="<?= valorPrevio($datos, 'nombre') ?>" required>

            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" maxlength="60" placeholder="Ingrese su apellido" value="<?= valorPrevio($datos, 'apellido') ?>" required>

            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" minlength="3" maxlength="50" placeholder="Elija un usuario" value="<?= valorPrevio($datos, 'usuario') ?>" required>

            <input type="hidden" name="opcion" value="registro">

            <p class="nota">La clave se generara automaticamente al crear la cuenta.</p>

            <button type="submit">Crear cuenta</button>
        </form>

        <p><a href="login.php">Ya tengo cuenta</a></p>
    </div>
</body>
</html>
