<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../models/Paciente.php';
require_once __DIR__ . '/../models/Antecedente.php';

if (!sesion_activa()) {
    redirigir('frontend/login.php?expirada=1');
}

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'guardar':
        guardar();
        break;
    default:
        redirigir('frontend/pacientes/lista.php');
}

function guardar(): void
{
    $pacienteId = (int) post('paciente_id');
    $volver     = 'frontend/pacientes/antecedentes.php?id=' . $pacienteId;

    if (!es_post() || !verificar_csrf()) {
        set_mensaje('danger', 'La solicitud no es válida. Vuelve a intentarlo.');
        redirigir($volver);
    }

    $paciente = (new Paciente())->buscarPorId($pacienteId);

    if (!$paciente) {
        set_mensaje('danger', 'El paciente no existe.');
        redirigir('frontend/pacientes/lista.php');
    }

    $camposTexto = [
        'alergias', 'enfermedades', 'medicamentos',
        'habitos', 'antecedentes_odontologicos', 'observaciones',
    ];

    $datos   = [];
    $errores = [];

    foreach ($camposTexto as $campo) {
        $datos[$campo] = post($campo);
        if (mb_strlen($datos[$campo]) > 2000) {
            $errores[$campo] = 'Este campo admite máximo 2000 caracteres.';
        }
    }

    // El embarazo solo aplica a pacientes de sexo femenino
    $datos['embarazo'] = ($paciente['sexo'] === 'F' && post('embarazo') === '1') ? 1 : 0;

    if ($errores) {
        guardar_formulario($datos, $errores);
        set_mensaje('danger', 'Revisa los campos marcados.');
        redirigir($volver);
    }

    foreach ($camposTexto as $campo) {
        $datos[$campo] = nulo_si_vacio($datos[$campo]);
    }

    (new Antecedente())->guardar($pacienteId, $datos);

    set_mensaje('success', 'Antecedentes médicos guardados.');
    redirigir('frontend/historia/ver.php?id=' . $pacienteId);
}