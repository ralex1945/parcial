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
}
