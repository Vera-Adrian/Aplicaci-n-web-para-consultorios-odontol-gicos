<?php
require_once __DIR__ . '/../config/database.php';

class Antecedente
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function buscarPorPaciente(int $pacienteId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM antecedentes WHERE paciente_id = ?');
        $stmt->execute([$pacienteId]);
        return $stmt->fetch() ?: null;
    }

    public function guardar(int $pacienteId, array $datos): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO antecedentes
                (paciente_id, alergias, enfermedades, medicamentos, embarazo,
                 habitos, antecedentes_odontologicos, observaciones)
             VALUES
                (:paciente_id, :alergias, :enfermedades, :medicamentos, :embarazo,
                 :habitos, :antecedentes_odontologicos, :observaciones)
             ON DUPLICATE KEY UPDATE
                alergias                   = VALUES(alergias),
                enfermedades               = VALUES(enfermedades),
                medicamentos               = VALUES(medicamentos),
                embarazo                   = VALUES(embarazo),
                habitos                    = VALUES(habitos),
                antecedentes_odontologicos = VALUES(antecedentes_odontologicos),
                observaciones              = VALUES(observaciones)'
        );

        return $stmt->execute([
            ':paciente_id'                => $pacienteId,
            ':alergias'                   => $datos['alergias'],
            ':enfermedades'               => $datos['enfermedades'],
            ':medicamentos'               => $datos['medicamentos'],
            ':embarazo'                   => $datos['embarazo'],
            ':habitos'                    => $datos['habitos'],
            ':antecedentes_odontologicos' => $datos['antecedentes_odontologicos'],
            ':observaciones'              => $datos['observaciones'],
        ]);
    }
}