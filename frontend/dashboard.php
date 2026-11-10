<?php
require_once __DIR__ . '/../backend/includes/verificar_sesion.php';
require_once BASE_PATH . 'backend/models/Paciente.php';
require_once BASE_PATH . 'backend/models/Atencion.php';

$hoy        = date('Y-m-d');
$inicio_mes = date('Y-m-01');

$atencion        = new Atencion();
$total_pacientes = (new Paciente())->contar();
$totales_hoy     = $atencion->totalesEntre($hoy, $hoy);
$totales_mes     = $atencion->totalesEntre($inicio_mes, $hoy);
$proximas        = $atencion->proximasCitas(7);
$ultimas         = $atencion->ultimas(6);

$titulo        = 'Panel principal';
$pagina_activa = 'dashboard';
require_once BASE_PATH . 'frontend/layout/header.php';
?>

<div class="page-header mb-4">
    <div>
        <h1>Hola, <?= e($_SESSION['usuario_nombre']) ?></h1>
        <p class="text-muted mb-0">Resumen de tu consultorio hoy <?= formatear_fecha($hoy) ?></p>
    </div>
    <a href="pacientes/registrar.php" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i>Registrar paciente
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body stat-card stat-card-teal h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="stat-value"><?= $total_pacientes ?></div>
                    <div class="stat-label">Pacientes registrados</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body stat-card stat-card-blue h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-calendar-check-fill"></i></div>
                <div>
                    <div class="stat-value"><?= $totales_hoy['cantidad'] ?></div>
                    <div class="stat-label">Atenciones de hoy</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body stat-card stat-card-purple h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-clipboard2-pulse-fill"></i></div>
                <div>
                    <div class="stat-value"><?= $totales_mes['cantidad'] ?></div>
                    <div class="stat-label">Atenciones del mes</div>
                </div>
            </div>
        </div>
    </div>
    <?php if (es_odontologo()): ?>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-body stat-card stat-card-amber h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                    <div>
                        <div class="stat-value"><?= formatear_moneda($totales_mes['ingresos']) ?></div>
                        <div class="stat-label">Ingresos del mes</div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-calendar-week text-primary"></i>
                Citas de los próximos 7 días
            </div>
            <?php if (!$proximas): ?>
                <div class="card-body">
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <p>No hay citas programadas para esta semana.</p>
                    </div>
                </div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($proximas as $c): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <a href="historia/ver.php?id=<?= (int) $c['paciente_id'] ?>" class="fw-semibold text-decoration-none">
                                    <?= e($c['nombres'] . ' ' . $c['apellidos']) ?>
                                </a>
                                <div class="small text-muted">
                                    <i class="bi bi-clipboard2-pulse me-1"></i><?= e($c['procedimiento']) ?>
                                    <?= $c['telefono'] ? ' <i class="bi bi-telephone"></i> ' . e($c['telefono']) : '' ?>
                                </div>
                            </div>
                            <span class="badge rounded-pill <?= $c['proxima_cita'] === $hoy ? 'text-bg-warning' : 'text-bg-light' ?>">
                                <?= $c['proxima_cita'] === $hoy ? 'Hoy' : formatear_fecha($c['proxima_cita']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i>
                Últimas atenciones
            </div>
            <?php if (!$ultimas): ?>
                <div class="card-body">
                    <div class="empty-state">
                        <i class="bi bi-clipboard-x"></i>
                        <p>Las atenciones que registres aparecerán aquí.</p>
                    </div>
                </div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($ultimas as $u): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <a href="historia/ver.php?id=<?= (int) $u['paciente_id'] ?>" class="fw-semibold text-decoration-none">
                                    <?= e($u['nombres'] . ' ' . $u['apellidos']) ?>
                                </a>
                                <div class="small text-muted">
                                    <i class="bi bi-clipboard2-pulse me-1"></i><?= e($u['procedimiento']) ?>
                                </div>
                            </div>
                            <small class="text-muted text-nowrap"><?= formatear_fecha($u['fecha']) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . 'frontend/layout/footer.php'; ?>
