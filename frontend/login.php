<?php
require_once __DIR__ . '/../backend/includes/sesion.php';

if (sesion_activa()) {
    redirigir('frontend/dashboard.php');
}

$mensaje        = get_mensaje();
$usuario_previo = $_SESSION['usuario_previo'] ?? '';
unset($_SESSION['usuario_previo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | <?= e(NOMBRE_SISTEMA) ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div><i class="bi bi-heart-pulse-fill"></i></div>
            <h1><?= e(NOMBRE_SISTEMA) ?></h1>
            <p><?= e(NOMBRE_CONSULTORIO) ?></p>
        </div>

        <div class="login-body">
            <?php if (isset($_GET['expirada'])): ?>
                <div class="alert alert-warning d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history"></i>
                    Tu sesión expiró por inactividad. Vuelve a ingresar.
                </div>
            <?php endif; ?>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?= e($mensaje['tipo']) ?>"><?= e($mensaje['texto']) ?></div>
            <?php endif; ?>

            <form action="../backend/controllers/auth_controller.php?accion=login" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="mb-3">
                    <label for="usuario" class="form-label">
                        <i class="bi bi-person me-1"></i>Usuario
                    </label>
                    <input type="text" id="usuario" name="usuario" class="form-control"
                           value="<?= e($usuario_previo) ?>"
                           autocomplete="username" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="clave" class="form-label">
                        <i class="bi bi-lock me-1"></i>Contraseña
                    </label>
                    <input type="password" id="clave" name="clave" class="form-control"
                           autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
