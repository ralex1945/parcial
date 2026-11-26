<?php

class UsuarioDAO
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function buscarUsuario($usuario)
    {
        $sql = "SELECT * FROM usuario WHERE usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Crea usuario y estudiante en una sola transaccion; el rol siempre es 'estudiante'
    public function registrarEstudiante($usuario, $claveHash, $carnet, $nombre, $apellido)
    {
        $this->conexion->begin_transaction();

        try {
            $stmt = $this->conexion->prepare("INSERT INTO usuario (usuario, clave_hash, rol) VALUES (?, ?, 'estudiante')");
            if (!$stmt) {
                throw new mysqli_sql_exception($this->conexion->error, $this->conexion->errno);
            }
            $stmt->bind_param("ss", $usuario, $claveHash);
            if (!$stmt->execute()) {
                throw new mysqli_sql_exception($stmt->error, $stmt->errno);
            }
            $idUsuario = $this->conexion->insert_id;

            $stmt = $this->conexion->prepare("INSERT INTO estudiante (carnet, nombre, apellido, id_usuario) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new mysqli_sql_exception($this->conexion->error, $this->conexion->errno);
            }
            $stmt->bind_param("sssi", $carnet, $nombre, $apellido, $idUsuario);
            if (!$stmt->execute()) {
                throw new mysqli_sql_exception($stmt->error, $stmt->errno);
            }

            $this->conexion->commit();
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->conexion->rollback();
            throw $e;
        }
    }
}
