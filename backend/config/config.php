<?php
// Rutas
define('BASE_URL', 'http://localhost/dentahistoria/');
define('BASE_PATH', dirname(__DIR__, 2) . '/');

// Datos del sistema (aparecen en el menú y en el PDF)
define('NOMBRE_SISTEMA', 'DentaHistoria');
define('NOMBRE_CONSULTORIO', 'Consultorio Odontológico');
define('NOMBRE_ODONTOLOGO', 'Dr. Nombre Apellido');

// Sesión: segundos de inactividad antes de cerrarla (30 minutos)
define('TIEMPO_SESION', 1800);

date_default_timezone_set('America/Caracas');

// Mostrar errores solo mientras desarrollas; ponlo en 0 al entregar
ini_set('display_errors', 1);
error_reporting(E_ALL);