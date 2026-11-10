<?php
$alerta        = get_mensaje();
$tipos_validos = ['success', 'danger', 'warning', 'info'];

if ($alerta && in_array($alerta['tipo'], $tipos_validos, true)):
?>
    <div class="alert alert-<?= $alerta['tipo'] ?> alert-dismissible fade show" role="alert">
        <?= e($alerta['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>