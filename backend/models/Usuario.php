<?php
require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function buscarPorUsuario(string $usuario): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, usuario, clave, rol, activo
               FROM usuarios
              WHERE usuario = ?
              LIMIT 1'
        );
        $stmt->execute([$usuario]);
        return $stmt->fetch() ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, usuario, rol, activo, creado_en
               FROM usuarios
              WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Devuelve el usuario (sin la clave) si las credenciales son correctas, o null
    public function verificarCredenciales(string $usuario, string $clave): ?array
    {
        $datos = $this->buscarPorUsuario($usuario);

        if (!$datos || !$datos['activo'] || !password_verify($clave, $datos['clave'])) {
            return null;
        }

        // Si PHP mejora su algoritmo de cifrado, actualiza el hash automáticamente
        if (password_needs_rehash($datos['clave'], PASSWORD_DEFAULT)) {
            $this->actualizarClave((int) $datos['id'], $clave);
        }

        unset($datos['clave']);
        return $datos;
    }

    public function actualizarClave(int $id, string $clave): bool
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET clave = ? WHERE id = ?');
        return $stmt->execute([password_hash($clave, PASSWORD_DEFAULT), $id]);
    }
}