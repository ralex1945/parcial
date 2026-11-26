<?php
require_once __DIR__ . '/../config/auth.php';
requerirRol(['admin']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Estudiantes</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container py-3" style="max-width: 820px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="h5 mb-0">Registro de Estudiantes</h1>
            <a href="../index.php" class="btn btn-outline-secondary btn-sm">Inicio</a>
        </div>

        <div id="mensaje" class="alert py-1 px-2 small mb-2" role="alert" hidden></div>

        <form id="formRegistro" method="POST" action="DAORegistroEstudiante.php" class="card card-body p-3" autocomplete="off">
            <div class="row g-2">
                <div class="col-md-3">
                    <label for="carnet" class="form-label small mb-0">Carnet *</label>
                    <input type="text" id="carnet" name="carnet" maxlength="10" class="form-control form-control-sm" autocomplete="off" required>
                </div>
                <div class="col-md-5">
                    <label for="nombre" class="form-label small mb-0">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" maxlength="60" class="form-control form-control-sm" autocomplete="off" required>
                </div>
                <div class="col-md-4">
                    <label for="apellido" class="form-label small mb-0">Apellido *</label>
                    <input type="text" id="apellido" name="apellido" maxlength="60" class="form-control form-control-sm" autocomplete="off" required>
                </div>
                <div class="col-md-6">
                    <label for="usuario" class="form-label small mb-0">Usuario *</label>
                    <input type="text" id="usuario" name="usuario" minlength="3" maxlength="50" class="form-control form-control-sm" autocomplete="off" required>
                </div>
                <div class="col-md-6" id="contenedorClave" hidden>
                    <label for="claveGenerada" class="form-label small mb-0">Clave</label>
                    <input type="text" id="claveGenerada" class="form-control form-control-sm" autocomplete="off" readonly>
                </div>
            </div>
            <div class="mt-2">
                <button id="btnGuardar" type="submit" class="btn btn-primary btn-sm">Registrar</button>
            </div>
        </form>
    </div>
    <script src="../assets/js/JSRegistroEstudiante.js"></script>
</body>
</html>
