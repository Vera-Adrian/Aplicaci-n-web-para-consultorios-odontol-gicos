<?php
// Cada página define antes de incluir este archivo:
// $titulo        → texto de la pestaña del navegador
// $pagina_activa → opción del menú que se resalta
$titulo        = $titulo ?? NOMBRE_SISTEMA;
$pagina_activa = $pagina_activa ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> | <?= e(NOMBRE_SISTEMA) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>frontend/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>frontend/assets/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>frontend/assets/css/estilos.css">
</head>
<body>
<div class="app">
    <?php require __DIR__ . '/sidebar.php'; ?>

    <div class="app-contenido">
        <?php require __DIR__ . '/topbar.php'; ?>

        <main class="p-3 p-lg-4">
            <?php require __DIR__ . '/alertas.php'; ?>