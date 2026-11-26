<?php
$conexion = new mysqli('localhost', 'root', '', 'eventos_academicos');

if ($conexion->connect_error) {
    die('Error de conexión: ' . $conexion->connect_error);
}

$conexion->set_charset('utf8mb4');
