<?php
require_once __DIR__ . '/../config/database.php';

class Atencion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO atenciones
                (paciente_id, procedimiento_id, usuario_id, fecha, pieza_dental, diagnostico,
                 tratamiento, materiales, observaciones, indicaciones, proxima_cita, costo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $d['paciente_id'], $d['procedimiento_id'], $d['usuario_id'], $d['fecha'],
            $d['pieza_dental'], $d['diagnostico'], $d['tratamiento'], $d['materiales'],
            $d['observaciones'], $d['indicaciones'], $d['proxima_cita'], $d['costo'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM atenciones WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function listarPorPaciente(int $pacienteId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, pr.nombre AS procedimiento, u.nombre AS registrado_por
               FROM atenciones a
               JOIN procedimientos pr ON pr.id = a.procedimiento_id
               JOIN usuarios u        ON u.id  = a.usuario_id
              WHERE a.paciente_id = ?
           ORDER BY a.fecha DESC, a.id DESC'
        );
        $stmt->execute([$pacienteId]);
        return $stmt->fetchAll();
    }

    // Devuelve false si la atención ya estaba anulada
    public function anular(int $id, string $motivo): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE atenciones SET anulada = 1, motivo_anulacion = ?
              WHERE id = ? AND anulada = 0'
        );
        $stmt->execute([$motivo, $id]);
        return $stmt->rowCount() > 0;
    }

    public function totalesEntre(string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS cantidad, COALESCE(SUM(costo), 0) AS ingresos
               FROM atenciones
              WHERE anulada = 0
                AND fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY)'
        );
        $stmt->execute([$desde, $hasta]);
        $fila = $stmt->fetch();

        return ['cantidad' => (int) $fila['cantidad'], 'ingresos' => (float) $fila['ingresos']];
    }

    public function resumenPorProcedimiento(string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare(
            'SELECT pr.nombre AS procedimiento, COUNT(*) AS cantidad, SUM(a.costo) AS total
               FROM atenciones a
               JOIN procedimientos pr ON pr.id = a.procedimiento_id
              WHERE a.anulada = 0
                AND a.fecha >= ? AND a.fecha < DATE_ADD(?, INTERVAL 1 DAY)
           GROUP BY pr.id, pr.nombre
           ORDER BY cantidad DESC'
        );
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll();
    }

    public function reporte(string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.fecha, a.pieza_dental, a.costo, pr.nombre AS procedimiento,
                    p.id AS paciente_id, p.nombres, p.apellidos, p.cedula
               FROM atenciones a
               JOIN procedimientos pr ON pr.id = a.procedimiento_id
               JOIN pacientes p       ON p.id  = a.paciente_id
              WHERE a.anulada = 0
                AND a.fecha >= ? AND a.fecha < DATE_ADD(?, INTERVAL 1 DAY)
           ORDER BY a.fecha DESC
              LIMIT 500'
        );
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll();
    }

    public function ultimas(int $cantidad = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.fecha, pr.nombre AS procedimiento,
                    p.id AS paciente_id, p.nombres, p.apellidos
               FROM atenciones a
               JOIN procedimientos pr ON pr.id = a.procedimiento_id
               JOIN pacientes p       ON p.id  = a.paciente_id
              WHERE a.anulada = 0
           ORDER BY a.fecha DESC, a.id DESC
              LIMIT ?'
        );
        $stmt->bindValue(1, $cantidad, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Las fechas se calculan en PHP para respetar la zona horaria de config.php
    public function proximasCitas(int $dias = 7): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.proxima_cita, pr.nombre AS procedimiento,
                    p.id AS paciente_id, p.nombres, p.apellidos, p.telefono
               FROM atenciones a
               JOIN procedimientos pr ON pr.id = a.procedimiento_id
               JOIN pacientes p       ON p.id  = a.paciente_id
              WHERE a.anulada = 0
                AND a.proxima_cita BETWEEN ? AND ?
           ORDER BY a.proxima_cita'
        );
        $stmt->execute([date('Y-m-d'), date('Y-m-d', strtotime("+$dias days"))]);
        return $stmt->fetchAll();
    }
}