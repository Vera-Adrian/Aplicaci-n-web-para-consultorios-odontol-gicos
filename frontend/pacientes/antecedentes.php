<?php
require_once __DIR__ . '/../../backend/includes/verificar_sesion.php';
require_once BASE_PATH . 'backend/models/Paciente.php';
require_once BASE_PATH . 'backend/models/Antecedente.php';

$id       = (int) ($_GET['id'] ?? 0);
$paciente = (new Paciente())->buscarPorId($id);

if (!$paciente) {
    set_mensaje('danger', 'El paciente que buscas no existe.');
    redirigir('frontend/pacientes/lista.php');
}

$formulario = recuperar_formulario();
$errores    = $formulario['errores'];
$datos      = $formulario['datos'] ?: ((new Antecedente())->buscarPorPaciente($id) ?? []);
$datos     += [
    'alergias' => '', 'enfermedades' => '', 'medicamentos' => '', 'embarazo' => 0,
    'habitos' => '', 'antecedentes_odontologicos' => '', 'observaciones' => '',
];

// campo => [etiqueta, texto de ayuda]
$campos = [
    'alergias'                   => ['Alergias', 'Medicamentos, anestésicos, látex, alimentos. Escribe «Ninguna» si no tiene.'],
    'enfermedades'               => ['Enfermedades o condiciones', 'Diabetes, hipertensión, cardiopatías, problemas de coagulación...'],
    'medicamentos'               => ['Medicamentos que toma actualmente', 'Nombre y dosis, si la conoce.'],
    'habitos'                    => ['Hábitos', 'Bruxismo, tabaquismo, morderse las uñas...'],
    'antecedentes_odontologicos' => ['Antecedentes odontológicos', 'Tratamientos previos, reacciones a la anestesia...'],
    'observaciones'              => ['Observaciones', ''],
];

$titulo        = 'Antecedentes médicos';
$pagina_activa = 'pacientes';
require_once BASE_PATH . 'frontend/layout/header.php';
?>

<h1 class="h3 mb-1"><i class="bi bi-heart-pulse me-2 text-primary"></i>Antecedentes médicos</h1>
<p class="text-muted mb-3">
    <?= e($paciente['nombres'] . ' ' . $paciente['apellidos']) ?>,
    <?= e($paciente['cedula']) ?>, <?= calcular_edad($paciente['fecha_nac']) ?> años
</p>

<form action="<?= BASE_URL ?>backend/controllers/antecedente_controller.php?accion=guardar"
      method="POST" class="card">
    <div class="card-body">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="paciente_id" value="<?= $id ?>">

        <div class="row g-3">
            <?php foreach ($campos as $campo => [$etiqueta, $ayuda]): ?>
                <div class="col-md-6">
                    <label for="<?= $campo ?>" class="form-label"><?= e($etiqueta) ?></label>
                    <textarea id="<?= $campo ?>" name="<?= $campo ?>" rows="3" maxlength="2000"
                              class="form-control<?= clase_error($errores, $campo) ?>"><?= e($datos[$campo]) ?></textarea>
                    <?php if ($ayuda): ?>
                        <div class="form-text"><?= e($ayuda) ?></div>
                    <?php endif; ?>
                    <?= mensaje_error($errores, $campo) ?>
                </div>
            <?php endforeach; ?>

            <?php if ($paciente['sexo'] === 'F'): ?>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" id="embarazo" name="embarazo" value="1"
                               class="form-check-input" <?= (int) $datos['embarazo'] === 1 ? 'checked' : '' ?>>
                        <label for="embarazo" class="form-check-label">La paciente está embarazada</label>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-end gap-2">
        <a href="../historia/ver.php?id=<?= $id ?>" class="btn btn-outline-secondary">Completar después</a>
        <button type="submit" class="btn btn-primary">Guardar antecedentes</button>
    </div>
</form>

<?php require_once BASE_PATH . 'frontend/layout/footer.php'; ?>