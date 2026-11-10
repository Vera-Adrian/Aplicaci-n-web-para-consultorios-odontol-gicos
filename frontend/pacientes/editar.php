<?php
require_once __DIR__ . '/../../backend/includes/verificar_sesion.php';
require_once BASE_PATH . 'backend/models/Paciente.php';

$id_paciente = (int) ($_GET['id'] ?? 0);
$paciente    = (new Paciente())->buscarPorId($id_paciente);

if (!$paciente) {
    set_mensaje('danger', 'El paciente que buscas no existe.');
    redirigir('frontend/pacientes/lista.php');
}

$formulario = recuperar_formulario();
$errores    = $formulario['errores'];
// Si vuelve con errores, se muestra lo que escribió; si no, los datos guardados
$datos      = $formulario['datos'] ?: $paciente;

$accion       = BASE_URL . 'backend/controllers/paciente_controller.php?accion=editar';
$texto_boton  = 'Guardar cambios';
$url_cancelar = '../historia/ver.php?id=' . $id_paciente;

$titulo        = 'Editar paciente';
$pagina_activa = 'pacientes';
require_once BASE_PATH . 'frontend/layout/header.php';
?>

<h1 class="h3 mb-1"><i class="bi bi-pencil-square me-2 text-primary"></i>Editar paciente</h1>
<p class="text-muted mb-3"><?= e($paciente['nombres'] . ' ' . $paciente['apellidos']) ?></p>

<?php require __DIR__ . '/_form_paciente.php'; ?>

<?php require_once BASE_PATH . 'frontend/layout/footer.php'; ?>