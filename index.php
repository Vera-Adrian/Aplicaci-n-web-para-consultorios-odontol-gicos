<?php
require_once __DIR__ . '/backend/includes/sesion.php';

if (sesion_activa()) {
    redirigir('frontend/dashboard.php');
}

redirigir('frontend/login.php');