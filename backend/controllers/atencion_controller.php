<?php
require_once __DIR__ . '/../includes/verificar_rol.php';
require_once __DIR__ . '/../models/Paciente.php';
require_once __DIR__ . '/../models/Procedimiento.php';
require_once __DIR__ . '/../models/Atencion.php';

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'registrar':
        registrar();
        break;
    case 'anular':
        anular();
        break;
    default:
        redirigir('frontend/pacientes/lista.php');
}

function registrar(): void
{
    $pacienteId = (int) post('paciente_id');
    $volver     = 'frontend/historia/registrar_atencion.php?paciente=' . $pacienteId;

    if (!es_post() || !verificar_csrf()) {
        set_mensaje('danger', 'La solicitud no es válida. Vuelve a intentarlo.');
        redirigir($volver);
    }

    if (!(new Paciente())->buscarPorId($pacienteId)) {
        set_mensaje('danger', 'El paciente no existe.');
        redirigir('frontend/pacientes/lista.php');
    }

    $datos = [
        'procedimiento_id' => (int) post('procedimiento_id'),
        'fecha'            => post('fecha'),
        'pieza_dental'     => post('pieza_dental'),
        'diagnostico'      => post('diagnostico'),
        'tratamiento'      => post('tratamiento'),
        'materiales'       => post('materiales'),
        'indicaciones'     => post('indicaciones'),
        'observaciones'    => post('observaciones'),
        'proxima_cita'     => post('proxima_cita'),
        'costo'            => str_replace(',', '.', post('costo')),
    ];

    $errores       = [];
    $procedimiento = (new Procedimiento())->buscarPorId($datos['procedimiento_id']);

    if (!$procedimiento || !$procedimiento['activo']) {
        $errores['procedimiento_id'] = 'Selecciona un procedimiento del catálogo.';
    }

    if (!fecha_valida($datos['fecha'], 'Y-m-d\TH:i')) {
        $errores['fecha'] = 'Indica la fecha y hora de la atención.';
    } elseif ($datos['fecha'] > date('Y-m-d\TH:i')) {
        $errores['fecha'] = 'La fecha no puede ser posterior al momento actual.';
    }

    $piezas = piezas_dentales();
    if ($datos['pieza_dental'] !== '' && !isset($piezas[(int) $datos['pieza_dental']])) {
        $errores['pieza_dental'] = 'Selecciona una pieza dental válida.';
    } elseif ($datos['pieza_dental'] === '' && $procedimiento && $procedimiento['requiere_pieza']) {
        $errores['pieza_dental'] = 'Este procedimiento exige indicar la pieza dental.';
    }

    if ($datos['diagnostico'] === '') {
        $errores['diagnostico'] = 'Escribe el diagnóstico.';
    }

    foreach (['diagnostico', 'tratamiento', 'materiales', 'indicaciones', 'observaciones'] as $campo) {
        if (!isset($errores[$campo]) && mb_strlen($datos[$campo]) > 2000) {
            $errores[$campo] = 'Este campo admite máximo 2000 caracteres.';
        }
    }

    if ($datos['proxima_cita'] !== '') {
        if (!fecha_valida($datos['proxima_cita'])) {
            $errores['proxima_cita'] = 'La fecha de la próxima cita no es válida.';
        } elseif ($datos['proxima_cita'] < substr($datos['fecha'], 0, 10)) {
            $errores['proxima_cita'] = 'La próxima cita debe ser posterior a esta atención.';
        }
    }

    // Si el costo queda vacío se usa el precio del catálogo
    if ($datos['costo'] === '' && $procedimiento) {
        $datos['costo'] = $procedimiento['precio'];
    }
    if (!is_numeric($datos['costo']) || $datos['costo'] < 0) {
        $errores['costo'] = 'Indica un costo válido (0 o mayor).';
    }

    if ($errores) {
        guardar_formulario($datos, $errores);
        set_mensaje('danger', 'Revisa los campos marcados.');
        redirigir($volver);
    }

    (new Atencion())->crear([
        'paciente_id'      => $pacienteId,
        'procedimiento_id' => $datos['procedimiento_id'],
        'usuario_id'       => (int) $_SESSION['usuario_id'],
        'fecha'            => str_replace('T', ' ', $datos['fecha']) . ':00',
        'pieza_dental'     => $datos['pieza_dental'] === '' ? null : (int) $datos['pieza_dental'],
        'diagnostico'      => $datos['diagnostico'],
        'tratamiento'      => nulo_si_vacio($datos['tratamiento']),
        'materiales'       => nulo_si_vacio($datos['materiales']),
        'observaciones'    => nulo_si_vacio($datos['observaciones']),
        'indicaciones'     => nulo_si_vacio($datos['indicaciones']),
        'proxima_cita'     => nulo_si_vacio($datos['proxima_cita']),
        'costo'            => round((float) $datos['costo'], 2),
    ]);

    set_mensaje('success', 'Atención registrada en la historia clínica.');
    redirigir('frontend/historia/ver.php?id=' . $pacienteId);
}

// Las atenciones no se borran: se marcan como anuladas con su motivo
function anular(): void
{
    $id       = (int) post('id');
    $modelo   = new Atencion();
    $atencion = $modelo->buscarPorId($id);

    if (!$atencion) {
        set_mensaje('danger', 'La atención no existe.');
        redirigir('frontend/pacientes/lista.php');
    }

    $volver = 'frontend/historia/ver.php?id=' . $atencion['paciente_id'];

    if (!es_post() || !verificar_csrf()) {
        set_mensaje('danger', 'La solicitud no es válida. Vuelve a intentarlo.');
        redirigir($volver);
    }

    $motivo = post('motivo');

    if ($motivo === '' || mb_strlen($motivo) > 255) {
        set_mensaje('danger', 'Escribe el motivo de la anulación (máximo 255 caracteres).');
        redirigir($volver);
    }

    if ($modelo->anular($id, $motivo)) {
        set_mensaje('success', 'Atención anulada. Seguirá visible en la historia, marcada como anulada.');
    } else {
        set_mensaje('warning', 'Esta atención ya estaba anulada.');
    }

    redirigir($volver);
}