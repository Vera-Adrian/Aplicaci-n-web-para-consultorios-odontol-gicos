<?php
require_once __DIR__ . '/config.php';

class Database
{
    private const HOST    = 'localhost';
    private const NOMBRE  = 'dentahistoria';
    private const USUARIO = 'root';
    private const CLAVE   = '';          // En XAMPP, root no tiene clave por defecto
    private const CHARSET = 'utf8mb4';

    private static ?PDO $conexion = null;

    public static function conectar(): PDO
    {
        if (self::$conexion === null) {
            $dsn = 'mysql:host=' . self::HOST
                 . ';dbname=' . self::NOMBRE
                 . ';charset=' . self::CHARSET;

            try {
                self::$conexion = new PDO($dsn, self::USUARIO, self::CLAVE, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('Error de conexión: ' . $e->getMessage());
                die('No se pudo conectar a la base de datos. Verifica que MySQL esté encendido en XAMPP.');
            }
        }
        return self::$conexion;
    }
}