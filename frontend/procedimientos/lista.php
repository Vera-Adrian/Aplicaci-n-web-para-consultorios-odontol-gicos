<?php
require_once __DIR__ . '/../../backend/includes/verificar_rol.php';
require_once BASE_PATH . 'backend/models/Procedimiento.php';

$procedimientos = (new Procedimiento())->listar();

$titulo        = 'Procedimientos';
$pagina_activa = 'procedimientos';
require_once BASE_PATH . 'frontend/layout/header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-clipboard2-pulse-fill me-2 text-primary"></i>Procedimientos</h1>
    <a href="formulario.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Agregar procedimiento
    </a>
</div>

<?php if (!$procedimientos): ?>
    <div class="card card-body">
        <div class="empty-state">
            <i class="bi bi-clipboard2-pulse"></i>
            <p>El catálogo está vacío. Agrega los servicios que ofreces.</p>
            <a href="formulario.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Agregar procedimiento
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Procedimiento</th>
                        <th>Categoría</th>
                        <th>Duración</th>
                        <th class="text-end">Precio</th>
                        <th>Pieza dental</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($procedimientos as $p): ?>
                    <tr class="<?= $p['activo'] ? '' : 'text-muted' ?>">
                        <td>
                            <div class="fw-semibold"><?= e($p['nombre']) ?></div>
                            <?php if ($p['descripcion']): ?>
                                <small class="text-muted"><?= e($p['descripcion']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($p['categoria']) ?></td>
                        <td class="text-nowrap"><?= (int) $p['duracion_min'] ?> min</td>
                        <td class="text-end text-nowrap"><?= formatear_moneda($p['precio']) ?></td>
                        <td><?= $p['requiere_pieza'] ? 'Obligatoria' : 'Opcional' ?></td>
                        <td>
                            <span class="badge <?= $p['activo'] ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                <?= $p['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="formulario.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <form action="<?= BASE_URL ?>backend/controllers/procedimiento_controller.php?accion=estado"
                                  method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <?php if ($p['activo']): ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            data-confirmar="¿Desactivar «<?= e($p['nombre']) ?>»? Las atenciones anteriores se conservan.">
                                        Desactivar
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success">Activar</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once BASE_PATH . 'frontend/layout/footer.php'; ?>