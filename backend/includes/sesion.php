<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,      // JavaScript no puede leer la cookie
        'samesite' => 'Strict',
    ]);
    session_start();
}

function iniciar_sesion(array $usuario): void
{
    session_regenerate_id(true);   // Evita robo de sesión (session fixation)
    $_SESSION['usuario_id']     = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_rol']    = $usuario['rol'];
    $_SESSION['ultimo_acceso']  = time();
}

function sesion_activa(): bool
{
    if (empty($_SESSION['usuario_id'])) {
        return false;
    }
    if (time() - ($_SESSION['ultimo_acceso'] ?? 0) > TIEMPO_SESION) {
        cerrar_sesion();
        return false;
    }
    $_SESSION['ultimo_acceso'] = time();
    return true;
}

function cerrar_sesion(): void
{
    $_SESSION = [];                 // Borra todos los datos del usuario
    session_regenerate_id(true);    // Elimina la sesión anterior y crea una nueva vacía
}

function es_odontologo(): bool
{
    return ($_SESSION['usuario_rol'] ?? '') === 'odontologo';
}

// Mensajes de una sola lectura (éxito o error tras guardar un formulario)
function set_mensaje(string $tipo, string $texto): void
{
    $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $texto];
}

function get_mensaje(): ?array
{
    $mensaje = $_SESSION['mensaje'] ?? null;
    unset($_SESSION['mensaje']);
    return $mensaje;
}

// Protección CSRF: cada formulario lleva un token oculto
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificar_csrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token)
        && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function guardar_formulario(array $datos, array $errores): void
{
    $_SESSION['form_datos']   = $datos;
    $_SESSION['form_errores'] = $errores;
}

function recuperar_formulario(): array
{
    $formulario = [
        'datos'   => $_SESSION['form_datos'] ?? [],
        'errores' => $_SESSION['form_errores'] ?? [],
    ];
    unset($_SESSION['form_datos'], $_SESSION['form_errores']);
    return $formulario;
}