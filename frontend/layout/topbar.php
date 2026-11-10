<header class="topbar d-flex align-items-center gap-2 px-3 py-2 d-print-none">

    <button class="btn btn-outline-secondary d-lg-none" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#menuLateral"
            aria-controls="menuLateral">
        <i class="bi bi-list"></i>
    </button>

    <form class="flex-grow-1 buscador-global position-relative"
          action="<?= BASE_URL ?>frontend/pacientes/lista.php" method="GET" role="search"
          data-buscador
          data-url="<?= BASE_URL ?>backend/controllers/paciente_controller.php?accion=buscar"
          data-historia="<?= BASE_URL ?>frontend/historia/ver.php?id=">
        <input type="search" name="buscar" class="form-control" autocomplete="off"
               placeholder="Buscar paciente por nombre o cédula..."
               aria-label="Buscar paciente">
        <div class="list-group buscador-resultados d-none"></div>
    </form>

    <div class="ms-auto d-flex align-items-center gap-3">
        <div class="text-end d-none d-sm-block lh-sm">
            <div class="fw-semibold"><?= e($_SESSION['usuario_nombre']) ?></div>
            <small class="text-muted"><?= es_odontologo() ? 'Odontólogo' : 'Asistente' ?></small>
        </div>

        <form action="<?= BASE_URL ?>backend/controllers/auth_controller.php?accion=logout" method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i><span class="d-none d-sm-inline ms-1">Salir</span>
            </button>
        </form>
    </div>
</header>
