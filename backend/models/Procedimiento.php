<?php
require_once __DIR__ . '/../config/database.php';

class Procedimiento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function listar(bool $soloActivos = false): array
    {
        $sql = 'SELECT * FROM procedimientos';
        if ($soloActivos) {
            $sql .= ' WHERE activo = 1';
        }
        $sql .= ' ORDER BY categoria, nombre';

        return $this->db->query($sql)->fetchAll();
    }

    public function categorias(): array
    {
        return $this->db
            ->query('SELECT DISTINCT categoria FROM procedimientos ORDER BY categoria')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM procedimientos WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM procedimientos WHERE nombre = ?';
        $params = [$nombre];

        if ($excluirId !== null) {
            $sql     .= ' AND id <> ?';
            $params[] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function crear(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO procedimientos
                (nombre, categoria, descripcion, duracion_min, precio, requiere_pieza)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute($this->valores($d));
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE procedimientos SET
                nombre = ?, categoria = ?, descripcion = ?,
                duracion_min = ?, precio = ?, requiere_pieza = ?
             WHERE id = ?'
        );
        return $stmt->execute([...$this->valores($d), $id]);
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $stmt = $this->db->prepare('UPDATE procedimientos SET activo = ? WHERE id = ?');
        return $stmt->execute([$activo ? 1 : 0, $id]);
    }

    private function valores(array $d): array
    {
        return [
            $d['nombre'], $d['categoria'], $d['descripcion'],
            $d['duracion_min'], $d['precio'], $d['requiere_pieza'],
        ];
    }
}