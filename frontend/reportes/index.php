<?php
require_once __DIR__ . '/../../backend/includes/verificar_sesion.php';
require_once BASE_PATH . 'backend/models/Atencion.php';

$desde = is_string($_GET['desde'] ?? null) && fecha_valida($_GET['desde']) ? $_GET['desde'] : date('Y-m-01');
$hasta = is_string($_GET['hasta'] ?? null) && fecha_valida($_GET['hasta']) ? $_GET['hasta'] : date('Y-m-d');

if ($desde > $hasta) {
    [$desde, $hasta] = [$hasta, $desde];
}

$modelo  = new Atencion();
$totales = $modelo->totalesEntre($desde, $hasta);
$resumen = $modelo->resumenPorProcedimiento($desde, $hasta);
$detalle = $modelo->reporte($desde, $hasta);

$titulo        = 'Reportes';
$pagina_activa = 'reportes';
require_once BASE_PATH . 'frontend/layout/header.php';
?>

<!-- Encabezado que solo aparece en la hoja impresa -->
<div class="d-none d-print-block mb-3">
    <strong><?= e(NOMBRE_CONSULTORIO) ?></strong><br>
    <?= e(NOMBRE_ODONTOLOGO) ?>
</div>

<div class="page-header mb-3">
    <h1>
        <i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>
        Atenciones del <?= formatear_fecha($desde) ?> al <?= formatear_fecha($hasta) ?>
    </h1>
    <button type="button" class="btn btn-outline-primary d-print-none" onclick="window.print()">
        <i class="bi bi-printer me-1"></i>Imprimir
    </button>
</div>

<form method="GET" class="card card-body mb-3 d-print-none">
    <div class="row g-2 align-items-end">
        <div class="col-sm-4 col-md-3">
            <label for="desde" class="form-label">Desde</label>
            <input type="date" id="desde" name="desde" class="form-control" value="<?= e($desde) ?>">
        </div>
        <div class="col-sm-4 col-md-3">
            <label for="hasta" class="form-label">Hasta</label>
            <input type="date" id="hasta" name="hasta" class="form-control" value="<?= e($hasta) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Ver reporte</button>
        </div>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-body stat-card stat-card-blue h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-clipboard2-check-fill"></i></div>
                <div>
                    <div class="stat-value"><?= $totales['cantidad'] ?></div>
                    <div class="stat-label">Atenciones</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-body stat-card stat-card-amber h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                <div>
                    <div class="stat-value"><?= formatear_moneda($totales['ingresos']) ?></div>
                    <div class="stat-label">Ingresos</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$detalle): ?>
    <div class="card card-body">
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <p>No hay atenciones registradas en este rango de fechas.</p>
        </div>
    </div>
<?php else: ?>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Por procedimiento</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Procedimiento</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resumen as $r): ?>
                    <tr>
                        <td><?= e($r['procedimiento']) ?></td>
                        <td class="text-end"><?= (int) $r['cantidad'] ?></td>
                        <td class="text-end"><?= formatear_moneda($r['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white fw-semibold">Detalle</div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Paciente</th>
                        <th>Cédula</th>
                        <th>Procedimiento</th>
                        <th>Pieza</th>
                        <th class="text-end">Costo</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($detalle as $d): ?>
                    <tr>
                        <td class="text-nowrap"><?= formatear_fecha($d['fecha'], true) ?></td>
                        <td>
                            <a href="../historia/ver.php?id=<?= (int) $d['paciente_id'] ?>">
                                <?= e($d['apellidos'] . ', ' . $d['nombres']) ?>
                            </a>
                        </td>
                        <td class="text-nowrap"><?= e($d['cedula']) ?></td>
                        <td><?= e($d['procedimiento']) ?></td>
                        <td><?= $d['pieza_dental'] ? (int) $d['pieza_dental'] : '—' ?></td>
                        <td class="text-end text-nowrap"><?= formatear_moneda($d['costo']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<?php require_once BASE_PATH . 'frontend/layout/footer.php'; ?>