<?php
require_once __DIR__ . '/sesion.php';

$tenia_sesion = !empty($_SESSION['usuario_id']);

if (!sesion_activa()) {
    $parametro = $tenia_sesion ? '?expirada=1' : '';
    header('Location: ' . BASE_URL . 'frontend/login.php' . $parametro);
    exit;
}