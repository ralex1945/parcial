<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conexion->set_charset(DB_CHARSET);
} catch (mysqli_sql_exception $e) {
    die('Error de conexión: ' . $e->getMessage());
}
