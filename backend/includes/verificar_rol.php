<?php
require_once __DIR__ . '/verificar_sesion.php';

if (!es_odontologo()) {
    set_mensaje('danger', 'No tienes permiso para realizar esta acción.');
    redirigir('frontend/dashboard.php');
}