<?php
require_once __DIR__ . '/../../backend/includes/verificar_rol.php';
require_once BASE_PATH . 'backend/models/Procedimiento.php';

$modelo        = new Procedimiento();
$id            = (int) ($_GET['id'] ?? 0);
$procedimiento = $id ? $modelo->buscarPorId($id) : null;

if ($id && !$procedimiento) {
    set_mensaje('danger', 'El procedimiento no existe.');
    redirigir('frontend/procedimientos/lista.php');
}

$formulario = recuperar_formulario();
$errores    = $formulario['errores'];
$datos      = ($formulario['datos'] ?: ($procedimiento ?? [])) + [
    'nombre' => '', 'categoria' => '', 'descripcion' => '',
    'duracion_min' => 30, 'precio' => '', 'requiere_pieza' => 0,
];
$categorias = $modelo->categorias();

$titulo        = $id ? 'Editar procedimiento' : 'Agregar procedimiento';
$pagina_activa = 'procedimientos';
require_once BASE_PATH . 'frontend/layout/header.php';
?>

<h1 class="h3 mb-3"><i class="bi bi-clipboard2-pulse-fill me-2 text-primary"></i><?= e($titulo) ?></h1>

<form action="<?= BASE_URL ?>backend/controllers/procedimiento_controller.php?accion=guardar"
      method="POST" class="card">
    <div class="card-body">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" id="nombre" name="nombre" required maxlength="100"
                       class="form-control<?= clase_error($errores, 'nombre') ?>"
                       value="<?= e($datos['nombre']) ?>">
                <?= mensaje_error($errores, 'nombre') ?>
            </div>

            <div class="col-md-6">
                <label for="categoria" class="form-label">Categoría</label>
                <input type="text" id="categoria" name="categoria" required maxlength="50"
                       list="listaCategorias"
                       class="form-control<?= clase_error($errores, 'categoria') ?>"
                       value="<?= e($datos['categoria']) ?>">
                <datalist id="listaCategorias">
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= e($categoria) ?>">
                    <?php endforeach; ?>
                </datalist>
                <?= mensaje_error($errores, 'categoria') ?>
            </div>

            <div class="col-12">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="2" maxlength="1000"
                          class="form-control<?= clase_error($errores, 'descripcion') ?>"><?= e($datos['descripcion']) ?></textarea>
                <?= mensaje_error($errores, 'descripcion') ?>
            </div>

            <div class="col-md-4">
                <label for="duracion_min" class="form-label">Duración estimada (minutos)</label>
                <input type="number" id="duracion_min" name="duracion_min" required min="5" max="600" step="5"
                       class="form-control<?= clase_error($errores, 'duracion_min') ?>"
                       value="<?= e((string) $datos['duracion_min']) ?>">
                <?= mensaje_error($errores, 'duracion_min') ?>
            </div>

            <div class="col-md-4">
                <label for="precio" class="form-label">Precio base</label>
                <input type="number" id="precio" name="precio" required min="0" step="0.01"
                       class="form-control<?= clase_error($errores, 'precio') ?>"
                       value="<?= e((string) $datos['precio']) ?>">
                <?= mensaje_error($errores, 'precio') ?>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input type="checkbox" id="requiere_pieza" name="requiere_pieza" value="1"
                           class="form-check-input" <?= (int) $datos['requiere_pieza'] === 1 ? 'checked' : '' ?>>
                    <label for="requiere_pieza" class="form-check-label">Exige indicar la pieza dental</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-end gap-2">
        <a href="lista.php" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $id ? 'Guardar cambios' : 'Agregar procedimiento' ?></button>
    </div>
</form>

<?php require_once BASE_PATH . 'frontend/layout/footer.php'; ?>