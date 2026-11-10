<?php
require_once __DIR__ . '/../config/database.php';

class Paciente
{
    private const CAMPOS = [
        'cedula', 'nombres', 'apellidos', 'fecha_nac', 'sexo',
        'telefono', 'email', 'direccion', 'contacto_emergencia',
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    // Arma el WHERE de búsqueda. Cada marcador tiene nombre distinto porque
    // con ATTR_EMULATE_PREPARES en false no se puede repetir el mismo nombre.
    private function filtro(string $buscar): array
    {
        if ($buscar === '') {
            return ['', []];
        }
        $like  = '%' . $buscar . '%';
        $where = " WHERE cedula LIKE :b1
                      OR nombres LIKE :b2
                      OR apellidos LIKE :b3
                      OR CONCAT(nombres, ' ', apellidos) LIKE :b4";

        return [$where, [':b1' => $like, ':b2' => $like, ':b3' => $like, ':b4' => $like]];
    }

    public function listar(string $buscar = '', int $limite = 20, int $desde = 0): array
    {
        [$where, $params] = $this->filtro($buscar);

        $stmt = $this->db->prepare(
            'SELECT id, cedula, nombres, apellidos, fecha_nac, sexo, telefono, fecha_registro
               FROM pacientes' . $where . '
           ORDER BY apellidos, nombres
              LIMIT :limite OFFSET :desde'
        );

        foreach ($params as $marcador => $valor) {
            $stmt->bindValue($marcador, $valor);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':desde', $desde, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contar(string $buscar = ''): int
    {
        [$where, $params] = $this->filtro($buscar);
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM pacientes' . $where);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function recientes(int $cantidad = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, cedula, nombres, apellidos, fecha_registro
               FROM pacientes
           ORDER BY fecha_registro DESC
              LIMIT :cantidad'
        );
        $stmt->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM pacientes WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function existeCedula(string $cedula, ?int $excluirId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM pacientes WHERE cedula = ?';
        $params = [$cedula];

        if ($excluirId !== null) {
            $sql     .= ' AND id <> ?';
            $params[] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pacientes
                (cedula, nombres, apellidos, fecha_nac, sexo, telefono, email, direccion, contacto_emergencia)
             VALUES
                (:cedula, :nombres, :apellidos, :fecha_nac, :sexo, :telefono, :email, :direccion, :contacto_emergencia)'
        );
        $stmt->execute($this->soloCampos($datos));
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE pacientes SET
                cedula = :cedula, nombres = :nombres, apellidos = :apellidos,
                fecha_nac = :fecha_nac, sexo = :sexo, telefono = :telefono,
                email = :email, direccion = :direccion,
                contacto_emergencia = :contacto_emergencia
             WHERE id = :id'
        );
        return $stmt->execute($this->soloCampos($datos) + [':id' => $id]);
    }

    // Toma solo los campos permitidos y los prepara como :marcador => valor
    private function soloCampos(array $datos): array
    {
        $resultado = [];
        foreach (self::CAMPOS as $campo) {
            $resultado[':' . $campo] = $datos[$campo] ?? null;
        }
        return $resultado;
    }
}