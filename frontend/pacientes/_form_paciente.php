<?php
// Variables que define la página que incluye este archivo:
// $datos, $errores, $accion (URL del controlador), $texto_boton, $url_cancelar
// $id_paciente (solo al editar)
$datos += [
    'cedula' => '', 'nombres' => '', 'apellidos' => '', 'fecha_nac' => '',
    'sexo' => '', 'telefono' => '', 'email' => '', 'direccion' => '',
    'contacto_emergencia' => '',
];
?>
<form action="<?= e($accion) ?>" method="POST" class="card">
    <div class="card-body">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <?php if (!empty($id_paciente)): ?>
            <input type="hidden" name="id" value="<?= (int) $id_paciente ?>">
        <?php endif; ?>

        <h2 class="h6 text-muted mb-3"><i class="bi bi-person-vcard me-2"></i>Datos personales</h2>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="cedula" class="form-label">Cédula</label>
                <input type="text" id="cedula" name="cedula" required
                       class="form-control<?= clase_error($errores, 'cedula') ?>"
                       value="<?= e($datos['cedula']) ?>" placeholder="V-12345678">
                <?= mensaje_error($errores, 'cedula') ?>
            </div>

            <div class="col-md-4">
                <label for="nombres" class="form-label">Nombres</label>
                <input type="text" id="nombres" name="nombres" required maxlength="80"
                       class="form-control<?= clase_error($errores, 'nombres') ?>"
                       value="<?= e($datos['nombres']) ?>">
                <?= mensaje_error($errores, 'nombres') ?>
            </div>

            <div class="col-md-4">
                <label for="apellidos" class="form-label">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" required maxlength="80"
                       class="form-control<?= clase_error($errores, 'apellidos') ?>"
                       value="<?= e($datos['apellidos']) ?>">
                <?= mensaje_error($errores, 'apellidos') ?>
            </div>

            <div class="col-md-4">
                <label for="fecha_nac" class="form-label">Fecha de nacimiento</label>
                <input type="date" id="fecha_nac" name="fecha_nac" required max="<?= date('Y-m-d') ?>"
                       class="form-control<?= clase_error($errores, 'fecha_nac') ?>"
                       value="<?= e($datos['fecha_nac']) ?>">
                <?= mensaje_error($errores, 'fecha_nac') ?>
            </div>

            <div class="col-md-4">
                <label for="sexo" class="form-label">Sexo</label>
                <select id="sexo" name="sexo" required
                        class="form-select<?= clase_error($errores, 'sexo') ?>">
                    <option value="">Selecciona</option>
                    <option value="F" <?= $datos['sexo'] === 'F' ? 'selected' : '' ?>>Femenino</option>
                    <option value="M" <?= $datos['sexo'] === 'M' ? 'selected' : '' ?>>Masculino</option>
                </select>
                <?= mensaje_error($errores, 'sexo') ?>
            </div>
        </div>

        <h2 class="h6 text-muted mb-3"><i class="bi bi-telephone me-2"></i>Contacto</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" maxlength="20"
                       class="form-control<?= clase_error($errores, 'telefono') ?>"
                       value="<?= e($datos['telefono']) ?>" placeholder="0414-1234567">
                <?= mensaje_error($errores, 'telefono') ?>
            </div>

            <div class="col-md-8">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" id="email" name="email" maxlength="100"
                       class="form-control<?= clase_error($errores, 'email') ?>"
                       value="<?= e($datos['email']) ?>">
                <?= mensaje_error($errores, 'email') ?>
            </div>

            <div class="col-12">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" id="direccion" name="direccion" maxlength="255"
                       class="form-control<?= clase_error($errores, 'direccion') ?>"
                       value="<?= e($datos['direccion']) ?>">
                <?= mensaje_error($errores, 'direccion') ?>
            </div>

            <div class="col-12">
                <label for="contacto_emergencia" class="form-label">Contacto de emergencia</label>
                <input type="text" id="contacto_emergencia" name="contacto_emergencia" maxlength="150"
                       class="form-control<?= clase_error($errores, 'contacto_emergencia') ?>"
                       value="<?= e($datos['contacto_emergencia']) ?>"
                       placeholder="Nombre, parentesco y teléfono">
                <?= mensaje_error($errores, 'contacto_emergencia') ?>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-end gap-2">
        <a href="<?= e($url_cancelar) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg me-1"></i>Cancelar
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i><?= e($texto_boton) ?>
        </button>
    </div>
</form>