<?php
session_start();
$error = $_SESSION['auth_error'] ?? '';
$ok = $_SESSION['auth_ok'] ?? '';
unset($_SESSION['auth_error'], $_SESSION['auth_ok']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de sesion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
        .msg-ok {
            color: #198754;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <div class="login">
        <h2>Iniciar sesion</h2>

        <?php if ($error): ?>
            <div class="msg-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($ok): ?>
            <div class="msg-ok"><?= htmlspecialchars($ok) ?></div>
        <?php endif; ?>

        <form action="controlador.php" method="POST">
            <label>Usuario:</label>
            <input type="text" name="usuario" placeholder="Ingrese su usuario" required>

            <label>Clave:</label>
            <input type="password" name="clave" placeholder="Ingrese su clave" required>

            <input type="hidden" name="opcion" value="login">

            <button type="submit">Ingresar</button>
            <button type="button" style="margin-top: 10px; background: #6c757d;">Registrarse</button>
        </form>

        <p><a href="../index.php">Volver al inicio</a></p>
    </div>
</body>
</html>
