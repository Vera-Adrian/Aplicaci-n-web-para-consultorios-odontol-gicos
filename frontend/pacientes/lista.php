<?php
require_once __DIR__ . '/../../backend/includes/verificar_sesion.php';
require_once BASE_PATH . 'backend/models/Paciente.php';

$buscar     = is_string($_GET['buscar'] ?? null) ? trim($_GET['buscar']) : '';
$por_pagina = 15;

$modelo        = new Paciente();
$total         = $modelo->contar($buscar);
$total_paginas = max(1, (int) ceil($total / $por_pagina));
$pagina        = min(max(1, (int) ($_GET['pagina'] ?? 1)), $total_paginas);
$pacientes     = $modelo->listar($buscar, $por_pagina, ($pagina - 1) * $por_pagina);

$titulo        = 'Pacientes';
$pagina_activa = 'pacientes';
require_once BASE_PATH . 'frontend/layout/header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-people-fill me-2 text-primary"></i>Pacientes</h1>
    <a href="registrar.php" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i>Registrar paciente
    </a>
</div>

<form class="row g-2 mb-3" method="GET">
    <div class="col-sm-8 col-md-6">
        <input type="search" name="buscar" class="form-control"
               value="<?= e($buscar) ?>" placeholder="Nombre, apellido o cédula"
               aria-label="Buscar paciente">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary">
            <i class="bi bi-search me-1"></i>Buscar
        </button>
        <?php if ($buscar !== ''): ?>
            <a href="lista.php" class="btn btn-link">Ver todos</a>
        <?php endif; ?>
    </div>
</form>

<?php if (!$pacientes): ?>

    <div class="card card-body">
        <div class="empty-state">
            <?php if ($buscar !== ''): ?>
                <i class="bi bi-search"></i>
                <p>Ningún paciente coincide con «<?= e($buscar) ?>».</p>
                <a href="registrar.php" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-1"></i>Registrar un paciente nuevo
                </a>
            <?php else: ?>
                <i class="bi bi-people"></i>
                <p>Todavía no hay pacientes registrados.</p>
                <a href="registrar.php" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-1"></i>Registrar el primer paciente
                </a>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cédula</th>
                        <th>Paciente</th>
                        <th>Edad</th>
                        <th>Teléfono</th>
                        <th>Registrado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pacientes as $p): ?>
                    <tr>
                        <td class="text-nowrap"><?= e($p['cedula']) ?></td>
                        <td>
                            <a href="../historia/ver.php?id=<?= (int) $p['id'] ?>" class="fw-semibold text-decoration-none">
                                <?= e($p['apellidos'] . ', ' . $p['nombres']) ?>
                            </a>
                        </td>
                        <td><?= calcular_edad($p['fecha_nac']) ?> años</td>
                        <td class="text-nowrap"><?= e($p['telefono'] ?: '—') ?></td>
                        <td><?= formatear_fecha($p['fecha_registro']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="../historia/ver.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver historia">
                                <i class="bi bi-file-earmark-medical"></i>
                            </a>
                            <a href="editar.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-muted"><?= $total ?> paciente<?= $total === 1 ? '' : 's' ?></small>

            <?php if ($total_paginas > 1): ?>
                <nav aria-label="Paginación de pacientes">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item<?= $pagina <= 1 ? ' disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(['buscar' => $buscar, 'pagina' => $pagina - 1]) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Página <?= $pagina ?> de <?= $total_paginas ?></span>
                        </li>
                        <li class="page-item<?= $pagina >= $total_paginas ? ' disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(['buscar' => $buscar, 'pagina' => $pagina + 1]) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

<?php require_once BASE_PATH . 'frontend/layout/footer.php'; ?>
