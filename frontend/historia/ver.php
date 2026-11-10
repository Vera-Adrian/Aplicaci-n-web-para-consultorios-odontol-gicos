<?php
require_once __DIR__ . '/../../backend/includes/verificar_sesion.php';
require_once BASE_PATH . 'backend/models/Paciente.php';
require_once BASE_PATH . 'backend/models/Antecedente.php';
require_once BASE_PATH . 'backend/models/Atencion.php';

$id       = (int) ($_GET['id'] ?? 0);
$paciente = (new Paciente())->buscarPorId($id);

if (!$paciente) {
    set_mensaje('danger', 'El paciente que buscas no existe.');
    redirigir('frontend/pacientes/lista.php');
}

$antecedente = (new Antecedente())->buscarPorPaciente($id);
$atenciones  = (new Atencion())->listarPorPaciente($id);

$proxima_cita = null;
foreach ($atenciones as $a) {
    if (!$a['anulada'] && $a['proxima_cita'] && $a['proxima_cita'] >= date('Y-m-d')) {
        if ($proxima_cita === null || $a['proxima_cita'] < $proxima_cita) {
            $proxima_cita = $a['proxima_cita'];
        }
    }
}

$titulo        = 'Historia clínica';
$pagina_activa = 'pacientes';
require_once BASE_PATH . 'frontend/layout/header.php';
?>

<div class="historia-header d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
        <h1><i class="bi bi-file-earmark-medical me-2"></i><?= e($paciente['nombres'] . ' ' . $paciente['apellidos']) ?></h1>
        <p class="info-paciente mb-0">
            <i class="bi bi-person-vcard me-1"></i><?= e($paciente['cedula']) ?>
            &nbsp;&middot;&nbsp;
            <?= calcular_edad($paciente['fecha_nac']) ?> años
            &nbsp;&middot;&nbsp;
            <?= $paciente['sexo'] === 'F' ? 'Femenino' : 'Masculino' ?>
            <?php if ($proxima_cita): ?>
                &nbsp;&middot;&nbsp;
                <i class="bi bi-calendar-event me-1"></i>Próxima cita: <?= formatear_fecha($proxima_cita) ?>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="../pacientes/editar.php?id=<?= $id ?>" class="btn btn-sm btn-outline-light">
            <i class="bi bi-pencil-square me-1"></i>Editar
        </a>
        <a href="../pacientes/antecedentes.php?id=<?= $id ?>" class="btn btn-sm btn-outline-light">
            <i class="bi bi-heart-pulse me-1"></i>Antecedentes
        </a>
        <a href="<?= BASE_URL ?>backend/reportes/historia_pdf.php?id=<?= $id ?>" target="_blank"
           class="btn btn-sm btn-outline-light">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <?php if (es_odontologo()): ?>
            <a href="registrar_atencion.php?paciente=<?= $id ?>" class="btn btn-sm btn-warning text-dark">
                <i class="bi bi-plus-circle me-1"></i>Registrar atención
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!$antecedente): ?>
    <div class="alert alert-info d-flex align-items-center gap-2">
        <i class="bi bi-info-circle-fill"></i>
        Este paciente no tiene antecedentes médicos registrados.
        <a href="../pacientes/antecedentes.php?id=<?= $id ?>" class="alert-link">Completarlos ahora</a>
    </div>
<?php else: ?>
    <?php if (tiene_dato_clinico($antecedente['alergias'])): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><strong>Alergias:</strong> <?= e($antecedente['alergias']) ?></div>
        </div>
    <?php endif; ?>
    <?php if ((int) $antecedente['embarazo'] === 1): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Paciente embarazada. Tenlo en cuenta antes de indicar radiografías, anestesia o medicamentos.
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-telephone text-primary"></i> Contacto
            </div>
            <div class="card-body">
                <dl class="mb-0">
                    <?php foreach ([
                        ['bi-telephone',       'Teléfono',               $paciente['telefono']],
                        ['bi-envelope',        'Correo',                 $paciente['email']],
                        ['bi-geo-alt',         'Dirección',              $paciente['direccion']],
                        ['bi-shield-plus',     'Contacto de emergencia', $paciente['contacto_emergencia']],
                        ['bi-calendar-plus',   'Paciente desde',         formatear_fecha($paciente['fecha_registro'])],
                    ] as [$icono, $etiqueta, $valor]): ?>
                        <?php if ($valor): ?>
                            <dt class="small text-muted"><i class="bi <?= $icono ?> me-1"></i><?= e($etiqueta) ?></dt>
                            <dd><?= e($valor) ?></dd>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>

        <?php if ($antecedente): ?>
            <div class="card">
                <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-heart-pulse text-primary"></i> Antecedentes médicos
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <?php foreach ([
                            'Enfermedades o condiciones' => $antecedente['enfermedades'],
                            'Medicamentos actuales'      => $antecedente['medicamentos'],
                            'Hábitos'                    => $antecedente['habitos'],
                            'Antecedentes odontológicos' => $antecedente['antecedentes_odontologicos'],
                            'Observaciones'              => $antecedente['observaciones'],
                        ] as $etiqueta => $valor): ?>
                            <?php if ($valor): ?>
                                <dt class="small text-muted"><?= e($etiqueta) ?></dt>
                                <dd class="texto-clinico"><?= e($valor) ?></dd>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </dl>
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>Actualizado el <?= formatear_fecha($antecedente['actualizado_en']) ?>
                    </small>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-8">
        <h2 class="h5 mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-journal-medical text-primary"></i>
            Atenciones
            <span class="badge rounded-pill text-bg-primary"><?= count($atenciones) ?></span>
        </h2>

        <?php if (!$atenciones): ?>
            <div class="card card-body">
                <div class="empty-state">
                    <i class="bi bi-clipboard-x"></i>
                    <p>Este paciente aún no tiene atenciones registradas.</p>
                    <?php if (es_odontologo()): ?>
                        <a href="registrar_atencion.php?paciente=<?= $id ?>" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Registrar la primera atención
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($atenciones as $a): ?>
            <article class="card atencion-card mb-3<?= $a['anulada'] ? ' atencion-anulada' : '' ?>">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <strong><?= e($a['procedimiento']) ?></strong>
                        <?php if ($a['anulada']): ?>
                            <span class="badge text-bg-secondary ms-1">Anulada</span>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i><?= formatear_fecha($a['fecha'], true) ?>
                    </small>
                </div>

                <div class="card-body">
                    <dl class="row mb-0">
                        <?php foreach ([
                            'Pieza dental'  => nombre_pieza($a['pieza_dental'] ? (int) $a['pieza_dental'] : null),
                            'Diagnóstico'   => $a['diagnostico'],
                            'Tratamiento'   => $a['tratamiento'],
                            'Materiales'    => $a['materiales'],
                            'Indicaciones'  => $a['indicaciones'],
                            'Observaciones' => $a['observaciones'],
                            'Próxima cita'  => $a['proxima_cita'] ? formatear_fecha($a['proxima_cita']) : '',
                            'Costo'         => formatear_moneda($a['costo']),
                            'Atendido por'  => $a['registrado_por'],
                        ] as $etiqueta => $valor): ?>
                            <?php if ($valor): ?>
                                <dt class="col-sm-3 small text-muted"><?= e($etiqueta) ?></dt>
                                <dd class="col-sm-9 texto-clinico"><?= e($valor) ?></dd>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </dl>

                    <?php if ($a['anulada']): ?>
                        <div class="alert alert-secondary small mb-0 mt-2">
                            <i class="bi bi-x-circle me-1"></i>Motivo de anulación: <?= e($a['motivo_anulacion']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (es_odontologo() && !$a['anulada']): ?>
                    <div class="card-footer bg-white">
                        <button class="btn btn-sm btn-link text-danger p-0" type="button"
                                data-bs-toggle="collapse" data-bs-target="#anular<?= (int) $a['id'] ?>"
                                aria-expanded="false" aria-controls="anular<?= (int) $a['id'] ?>">
                            <i class="bi bi-x-circle me-1"></i>Anular esta atención
                        </button>

                        <div class="collapse mt-2" id="anular<?= (int) $a['id'] ?>">
                            <form action="<?= BASE_URL ?>backend/controllers/atencion_controller.php?accion=anular"
                                  method="POST" class="d-flex flex-column flex-sm-row gap-2">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                <input type="text" name="motivo" class="form-control form-control-sm"
                                       maxlength="255" required placeholder="Motivo de la anulación"
                                       aria-label="Motivo de la anulación">
                                <button type="submit" class="btn btn-sm btn-danger text-nowrap"
                                        data-confirmar="¿Anular esta atención? Quedará visible como anulada.">
                                    Confirmar anulación
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once BASE_PATH . 'frontend/layout/footer.php'; ?>
