<?php
$menu = [
    ['clave' => 'dashboard',      'texto' => 'Panel principal',    'icono' => 'bi-grid-1x2-fill',    'url' => 'frontend/dashboard.php',            'solo_odontologo' => false],
    ['clave' => 'pacientes',      'texto' => 'Pacientes',          'icono' => 'bi-people-fill',       'url' => 'frontend/pacientes/lista.php',      'solo_odontologo' => false],
    ['clave' => 'registrar',      'texto' => 'Registrar paciente', 'icono' => 'bi-person-plus-fill',  'url' => 'frontend/pacientes/registrar.php',  'solo_odontologo' => false],
    ['clave' => 'procedimientos', 'texto' => 'Procedimientos',     'icono' => 'bi-clipboard2-pulse-fill', 'url' => 'frontend/procedimientos/lista.php', 'solo_odontologo' => true],
    ['clave' => 'reportes',       'texto' => 'Reportes',           'icono' => 'bi-bar-chart-line-fill', 'url' => 'frontend/reportes/index.php',       'solo_odontologo' => false],
];
?>
<aside class="offcanvas-lg offcanvas-start sidebar d-print-none" tabindex="-1" id="menuLateral" aria-labelledby="menuLateralTitulo">

    <!-- Header solo visible en móvil (el offcanvas-lg lo oculta en desktop) -->
    <div class="offcanvas-header d-lg-none">
        <span class="sidebar-marca p-0" id="menuLateralTitulo">
            <i class="bi bi-heart-pulse-fill"></i>
            <?= e(NOMBRE_SISTEMA) ?>
        </span>
        <button type="button" class="btn-close btn-close-white"
                data-bs-dismiss="offcanvas" data-bs-target="#menuLateral"
                aria-label="Cerrar menú"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0">
        <!-- Marca solo visible en desktop -->
        <div class="sidebar-marca d-none d-lg-flex">
            <i class="bi bi-heart-pulse-fill"></i>
            <div>
                <?= e(NOMBRE_SISTEMA) ?>
                <small class="d-block fw-normal"><?= e(NOMBRE_CONSULTORIO) ?></small>
            </div>
        </div>

        <nav class="nav flex-column px-2 pb-3">
            <?php foreach ($menu as $item): ?>
                <?php if ($item['solo_odontologo'] && !es_odontologo()) continue; ?>
                <?php $activa = $item['clave'] === $pagina_activa; ?>
                <a class="nav-link<?= $activa ? ' active' : '' ?>"
                   href="<?= BASE_URL . $item['url'] ?>"
                   <?= $activa ? 'aria-current="page"' : '' ?>>
                    <i class="bi <?= $item['icono'] ?>"></i>
                    <?= e($item['texto']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</aside>
