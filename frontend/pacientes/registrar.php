<?php
require_once __DIR__ . '/../../backend/includes/verificar_sesion.php';

$formulario = recuperar_formulario();
$datos      = $formulario['datos'];
$errores    = $formulario['errores'];

$accion       = BASE_URL . 'backend/controllers/paciente_controller.php?accion=registrar';
$texto_boton  = 'Registrar paciente';
$url_cancelar = 'lista.php';

$titulo        = 'Registrar paciente';
$pagina_activa = 'registrar';
require_once BASE_PATH . 'frontend/layout/header.php';
?>

<h1 class="h3 mb-3"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Registrar paciente</h1>

<?php require __DIR__ . '/_form_paciente.php'; ?>

<?php require_once BASE_PATH . 'frontend/layout/footer.php'; ?>