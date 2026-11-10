<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../models/Paciente.php';

$accion = $_GET['accion'] ?? '';

// Las peticiones AJAX reciben un 401 en JSON; las demás vuelven al login
if (!sesion_activa()) {
    if ($accion === 'buscar') {
        responder_json(['error' => 'La sesión expiró.'], 401);
    }
    redirigir('frontend/login.php?expirada=1');
}

switch ($accion) {
    case 'registrar':
        registrar();
        break;
    case 'editar':
        editar();
        break;
    case 'buscar':
        buscar();
        break;
    default:
        redirigir('frontend/pacientes/lista.php');
}

function datos_paciente(): array
{
    return [
        'cedula'              => normalizar_cedula(post('cedula')),
        'nombres'             => post('nombres'),
        'apellidos'           => post('apellidos'),
        'fecha_nac'           => post('fecha_nac'),
        'sexo'                => post('sexo'),
        'telefono'            => post('telefono'),
        'email'               => post('email'),
        'direccion'           => post('direccion'),
        'contacto_emergencia' => post('contacto_emergencia'),
    ];
}

function validar_paciente(array $d, Paciente $modelo, ?int $id = null): array
{
    $errores = [];

    if (!preg_match('/^[VE]-\d{6,9}$/', $d['cedula'])) {
        $errores['cedula'] = 'Escribe una cédula válida, por ejemplo V-12345678.';
    } elseif ($modelo->existeCedula($d['cedula'], $id)) {
        $errores['cedula'] = 'Ya existe un paciente registrado con esta cédula.';
    }

    if ($d['nombres'] === '' || mb_strlen($d['nombres']) > 80) {
        $errores['nombres'] = 'Escribe los nombres (máximo 80 caracteres).';
    }

    if ($d['apellidos'] === '' || mb_strlen($d['apellidos']) > 80) {
        $errores['apellidos'] = 'Escribe los apellidos (máximo 80 caracteres).';
    }

    if (!fecha_valida($d['fecha_nac'])) {
        $errores['fecha_nac'] = 'Indica una fecha de nacimiento válida.';
    } elseif ($d['fecha_nac'] > date('Y-m-d')) {
        $errores['fecha_nac'] = 'La fecha de nacimiento no puede ser posterior a hoy.';
    }

    if (!in_array($d['sexo'], ['F', 'M'], true)) {
        $errores['sexo'] = 'Selecciona el sexo del paciente.';
    }

    if ($d['telefono'] !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $d['telefono'])) {
        $errores['telefono'] = 'El teléfono solo puede tener números, espacios, +, - y paréntesis.';
    }

    if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El correo no tiene un formato válido.';
    }

    if (mb_strlen($d['direccion']) > 255) {
        $errores['direccion'] = 'La dirección admite máximo 255 caracteres.';
    }

    if (mb_strlen($d['contacto_emergencia']) > 150) {
        $errores['contacto_emergencia'] = 'El contacto de emergencia admite máximo 150 caracteres.';
    }

    return $errores;
}

// Los campos opcionales vacíos se guardan como NULL
function preparar_para_bd(array $d): array
{
    foreach (['telefono', 'email', 'direccion', 'contacto_emergencia'] as $campo) {
        $d[$campo] = nulo_si_vacio($d[$campo]);
    }
    return $d;
}

function registrar(): void
{
    $volver = 'frontend/pacientes/registrar.php';

    if (!es_post() || !verificar_csrf()) {
        set_mensaje('danger', 'La solicitud no es válida. Vuelve a intentarlo.');
        redirigir($volver);
    }

    $modelo  = new Paciente();
    $datos   = datos_paciente();
    $errores = validar_paciente($datos, $modelo);

    if ($errores) {
        guardar_formulario($datos, $errores);
        set_mensaje('danger', 'Revisa los campos marcados.');
        redirigir($volver);
    }

    $id = $modelo->crear(preparar_para_bd($datos));

    set_mensaje('success', 'Paciente registrado. Ahora completa sus antecedentes médicos.');
    redirigir('frontend/pacientes/antecedentes.php?id=' . $id);
}

function editar(): void
{
    $id     = (int) post('id');
    $volver = 'frontend/pacientes/editar.php?id=' . $id;

    if (!es_post() || !verificar_csrf()) {
        set_mensaje('danger', 'La solicitud no es válida. Vuelve a intentarlo.');
        redirigir($volver);
    }

    $modelo = new Paciente();

    if (!$modelo->buscarPorId($id)) {
        set_mensaje('danger', 'El paciente que intentas editar no existe.');
        redirigir('frontend/pacientes/lista.php');
    }

    $datos   = datos_paciente();
    $errores = validar_paciente($datos, $modelo, $id);

    if ($errores) {
        guardar_formulario($datos, $errores);
        set_mensaje('danger', 'Revisa los campos marcados.');
        redirigir($volver);
    }

    $modelo->actualizar($id, preparar_para_bd($datos));

    set_mensaje('success', 'Datos del paciente actualizados.');
    redirigir('frontend/historia/ver.php?id=' . $id);
}

// Responde JSON para el buscador del frontend (se usa con fetch)
function buscar(): void
{
    $q = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';

    if (mb_strlen($q) < 2) {
        responder_json(['resultados' => []]);
    }

    $modelo     = new Paciente();
    $resultados = array_map(fn($p) => [
        'id'     => (int) $p['id'],
        'cedula' => $p['cedula'],
        'nombre' => $p['nombres'] . ' ' . $p['apellidos'],
        'edad'   => calcular_edad($p['fecha_nac']),
    ], $modelo->listar($q, 10));

    responder_json(['resultados' => $resultados]);
}