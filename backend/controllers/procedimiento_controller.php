<?php
require_once __DIR__ . '/../includes/verificar_rol.php';
require_once __DIR__ . '/../models/Procedimiento.php';

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'guardar':
        guardar();
        break;
    case 'estado':
        cambiar_estado();
        break;
    default:
        redirigir('frontend/procedimientos/lista.php');
}

// Crea un procedimiento si id = 0, o lo actualiza si trae id
function guardar(): void
{
    $id     = (int) post('id');
    $volver = 'frontend/procedimientos/formulario.php' . ($id ? '?id=' . $id : '');

    if (!es_post() || !verificar_csrf()) {
        set_mensaje('danger', 'La solicitud no es válida. Vuelve a intentarlo.');
        redirigir($volver);
    }

    $modelo = new Procedimiento();

    if ($id && !$modelo->buscarPorId($id)) {
        set_mensaje('danger', 'El procedimiento no existe.');
        redirigir('frontend/procedimientos/lista.php');
    }

    $datos = [
        'nombre'         => post('nombre'),
        'categoria'      => post('categoria'),
        'descripcion'    => post('descripcion'),
        'duracion_min'   => post('duracion_min'),
        'precio'         => str_replace(',', '.', post('precio')),
        'requiere_pieza' => post('requiere_pieza') === '1' ? 1 : 0,
    ];

    $errores = [];

    if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 100) {
        $errores['nombre'] = 'Escribe el nombre (máximo 100 caracteres).';
    } elseif ($modelo->existeNombre($datos['nombre'], $id ?: null)) {
        $errores['nombre'] = 'Ya existe un procedimiento con ese nombre.';
    }

    if ($datos['categoria'] === '' || mb_strlen($datos['categoria']) > 50) {
        $errores['categoria'] = 'Escribe la categoría (máximo 50 caracteres).';
    }

    if (mb_strlen($datos['descripcion']) > 1000) {
        $errores['descripcion'] = 'La descripción admite máximo 1000 caracteres.';
    }

    $duracion = filter_var($datos['duracion_min'], FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 5, 'max_range' => 600]]);
    if ($duracion === false) {
        $errores['duracion_min'] = 'Indica la duración en minutos (entre 5 y 600).';
    }

    if (!is_numeric($datos['precio']) || $datos['precio'] < 0 || $datos['precio'] > 99999999) {
        $errores['precio'] = 'Indica un precio válido (0 o mayor).';
    }

    if ($errores) {
        guardar_formulario($datos, $errores);
        set_mensaje('danger', 'Revisa los campos marcados.');
        redirigir($volver);
    }

    $datos['duracion_min'] = $duracion;
    $datos['precio']       = round((float) $datos['precio'], 2);
    $datos['descripcion']  = nulo_si_vacio($datos['descripcion']);

    if ($id) {
        $modelo->actualizar($id, $datos);
        set_mensaje('success', 'Procedimiento actualizado.');
    } else {
        $modelo->crear($datos);
        set_mensaje('success', 'Procedimiento agregado al catálogo.');
    }

    redirigir('frontend/procedimientos/lista.php');
}

// Activa o desactiva. No se borra porque las atenciones antiguas lo referencian.
function cambiar_estado(): void
{
    if (!es_post() || !verificar_csrf()) {
        set_mensaje('danger', 'La solicitud no es válida. Vuelve a intentarlo.');
        redirigir('frontend/procedimientos/lista.php');
    }

    $id            = (int) post('id');
    $modelo        = new Procedimiento();
    $procedimiento = $modelo->buscarPorId($id);

    if (!$procedimiento) {
        set_mensaje('danger', 'El procedimiento no existe.');
        redirigir('frontend/procedimientos/lista.php');
    }

    $activar = !$procedimiento['activo'];
    $modelo->cambiarEstado($id, $activar);

    set_mensaje('success', $activar
        ? 'Procedimiento activado.'
        : 'Procedimiento desactivado. Ya no aparecerá al registrar atenciones.');

    redirigir('frontend/procedimientos/lista.php');
}