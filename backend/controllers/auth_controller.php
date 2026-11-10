<?php
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../models/Usuario.php';

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
    default:
        redirigir('frontend/login.php');
}

function login(): void
{
    if (!es_post() || !verificar_csrf()) {
        set_mensaje('danger', 'La solicitud no es válida. Vuelve a intentarlo.');
        redirigir('frontend/login.php');
    }

    $usuario = limpiar($_POST['usuario'] ?? '');
    $clave   = (string) ($_POST['clave'] ?? '');

    if ($usuario === '' || $clave === '') {
        set_mensaje('warning', 'Escribe tu usuario y tu contraseña.');
        redirigir('frontend/login.php');
    }

    $modelo = new Usuario();
    $datos  = $modelo->verificarCredenciales($usuario, $clave);

    if (!$datos) {
        // Mensaje genérico: no revela si el usuario existe o no
        set_mensaje('danger', 'Usuario o contraseña incorrectos.');
        $_SESSION['usuario_previo'] = $usuario;
        redirigir('frontend/login.php');
    }

    iniciar_sesion($datos);
    set_mensaje('success', 'Bienvenido, ' . $datos['nombre'] . '.');
    redirigir('frontend/dashboard.php');
}

function logout(): void
{
    // El cierre se hace por POST con token para que nadie pueda forzarlo con un enlace
    if (!es_post() || !verificar_csrf()) {
        redirigir('frontend/dashboard.php');
    }

    cerrar_sesion();
    set_mensaje('info', 'Cerraste sesión correctamente.');
    redirigir('frontend/login.php');
}