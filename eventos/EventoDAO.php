<?php
class EventoDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listar(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM evento ORDER BY fecha ASC, hora ASC');
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM evento WHERE id_evento = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function existeCodigo(string $codigo, ?int $excluirId = null): bool
    {
        if ($excluirId !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM evento WHERE codigo = ? AND id_evento <> ? LIMIT 1');
            $stmt->execute([$codigo, $excluirId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT 1 FROM evento WHERE codigo = ? LIMIT 1');
            $stmt->execute([$codigo]);
        }
        return (bool)$stmt->fetchColumn();
    }

    public function generarCodigo(): string
    {
        $maxId = (int)$this->pdo->query('SELECT COALESCE(MAX(id_evento), 0) FROM evento')->fetchColumn();
        $num = $maxId + 1;
        do {
            $codigo = sprintf('EV-%03d', $num);
            $num++;
        } while ($this->existeCodigo($codigo));
        return $codigo;
    }

    public function crear(array $datos): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO evento (codigo, nombre, descripcion, lugar, fecha, hora, cupo_disponible)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        return $stmt->execute([
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'],
            $datos['lugar'],
            $datos['fecha'],
            $datos['hora'],
            $datos['cupo_disponible'],
        ]);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE evento
             SET codigo = ?, nombre = ?, descripcion = ?, lugar = ?, fecha = ?, hora = ?, cupo_disponible = ?
             WHERE id_evento = ?'
        );
        return $stmt->execute([
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'],
            $datos['lugar'],
            $datos['fecha'],
            $datos['hora'],
            $datos['cupo_disponible'],
            $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM evento WHERE id_evento = ?');
        return $stmt->execute([$id]);
    }
}
