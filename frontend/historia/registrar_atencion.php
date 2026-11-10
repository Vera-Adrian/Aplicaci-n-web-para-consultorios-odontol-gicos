<?php
require_once __DIR__ . '/../../backend/includes/verificar_rol.php';
require_once BASE_PATH . 'backend/models/Paciente.php';
require_once BASE_PATH . 'backend/models/Antecedente.php';
require_once BASE_PATH . 'backend/models/Procedimiento.php';

$id       = (int) ($_GET['paciente'] ?? 0);
$paciente = (new Paciente())->buscarPorId($id);

if (!$paciente) {
    set_mensaje('danger', 'El paciente que buscas no existe.');
    redirigir('frontend/pacientes/lista.php');
}

$antecedente    = (new Antecedente())->buscarPorPaciente($id);
$procedimientos = (new Procedimiento())->listar(true);
$piezas         = piezas_dentales();

$formulario = recuperar_formulario();
$errores    = $formulario['errores'];
$datos      = $formulario['datos'] + [
    'procedimiento_id' => '', 'fecha' => date('Y-m-d\TH:i'), 'pieza_dental' => '',
    'diagnostico' => '', 'tratamiento' => '', 'materiales' => '', 'indicaciones' => '',
    'observaciones' => '', 'proxima_cita' => '', 'costo' => '',
];

$textos = [
    'diagnostico'   => ['Diagnóstico', true],
    'tratamiento'   => ['Tratamiento realizado', false],
    'materiales'    => ['Materiales y anestesia', false],
    'indicaciones'  => ['Indicaciones al paciente', false],
    'observaciones' => ['Observaciones', false],
];

$titulo        = 'Registrar atención';
$pagina_activa = 'pacientes';
$scripts       = ['validaciones.js'];
require_once BASE_PATH . 'frontend/layout/header.php';
?>

<h1 class="h3 mb-1"><i class="bi bi-plus-circle me-2 text-primary"></i>Registrar atención</h1>
<p class="text-muted mb-3">
    <a href="ver.php?id=<?= $id ?>"><?= e($paciente['nombres'] . ' ' . $paciente['apellidos']) ?></a>,
    <?= e($paciente['cedula']) ?>, <?= calcular_edad($paciente['fecha_nac']) ?> años
</p>

<?php if ($antecedente && tiene_dato_clinico($antecedente['alergias'])): ?>
    <div class="alert alert-danger"><strong>Alergias:</strong> <?= e($antecedente['alergias']) ?></div>
<?php endif; ?>

<?php if (!$procedimientos): ?>
    <div class="alert alert-warning">
        No hay procedimientos activos en el catálogo.
        <a href="../procedimientos/formulario.php" class="alert-link">Agrega uno</a> para poder registrar atenciones.
    </div>
<?php else: ?>

<form id="formAtencion" action="<?= BASE_URL ?>backend/controllers/atencion_controller.php?accion=registrar"
      method="POST" class="card">
    <div class="card-body">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="paciente_id" value="<?= $id ?>">

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="procedimiento_id" class="form-label">Procedimiento</label>
                <select id="procedimiento_id" name="procedimiento_id" required
                        class="form-select<?= clase_error($errores, 'procedimiento_id') ?>">
                    <option value="">Selecciona</option>
                    <?php foreach ($procedimientos as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"
                                data-requiere-pieza="<?= (int) $p['requiere_pieza'] ?>"
                                data-precio="<?= e((string) $p['precio']) ?>"
                                <?= (int) $datos['procedimiento_id'] === (int) $p['id'] ? 'selected' : '' ?>>
                            <?= e($p['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= mensaje_error($errores, 'procedimiento_id') ?>
            </div>

            <div class="col-md-6">
                <label for="fecha" class="form-label">Fecha y hora</label>
                <input type="datetime-local" id="fecha" name="fecha" required
                       max="<?= date('Y-m-d\TH:i') ?>"
                       class="form-control<?= clase_error($errores, 'fecha') ?>"
                       value="<?= e($datos['fecha']) ?>">
                <?= mensaje_error($errores, 'fecha') ?>
            </div>

            <div class="col-md-6">
                <label for="pieza_dental" class="form-label">
                    Pieza dental <span id="piezaRequerida" class="text-danger d-none">(obligatoria)</span>
                </label>
                <select id="pieza_dental" name="pieza_dental"
                        class="form-select<?= clase_error($errores, 'pieza_dental') ?>">
                    <option value="">No aplica</option>
                    <optgroup label="Dentición permanente">
                        <?php foreach ($piezas as $numero => $nombre): if ($numero > 50) continue; ?>
                            <option value="<?= $numero ?>" <?= (string) $datos['pieza_dental'] === (string) $numero ? 'selected' : '' ?>>
                                <?= $numero ?> – <?= e($nombre) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Dentición temporal">
                        <?php foreach ($piezas as $numero => $nombre): if ($numero < 50) continue; ?>
                            <option value="<?= $numero ?>" <?= (string) $datos['pieza_dental'] === (string) $numero ? 'selected' : '' ?>>
                                <?= $numero ?> – <?= e($nombre) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
                <?= mensaje_error($errores, 'pieza_dental') ?>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <?php foreach ($textos as $campo => [$etiqueta, $obligatorio]): ?>
                <div class="<?= $campo === 'diagnostico' ? 'col-12' : 'col-md-6' ?>">
                    <label for="<?= $campo ?>" class="form-label"><?= e($etiqueta) ?></label>
                    <textarea id="<?= $campo ?>" name="<?= $campo ?>" rows="3" maxlength="2000"
                              <?= $obligatorio ? 'required' : '' ?>
                              class="form-control<?= clase_error($errores, $campo) ?>"><?= e($datos[$campo]) ?></textarea>
                    <?= mensaje_error($errores, $campo) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="proxima_cita" class="form-label">Próxima cita</label>
                <input type="date" id="proxima_cita" name="proxima_cita" min="<?= date('Y-m-d') ?>"
                       class="form-control<?= clase_error($errores, 'proxima_cita') ?>"
                       value="<?= e($datos['proxima_cita']) ?>">
                <?= mensaje_error($errores, 'proxima_cita') ?>
            </div>

            <div class="col-md-6">
                <label for="costo" class="form-label">Costo</label>
                <input type="number" id="costo" name="costo" min="0" step="0.01"
                       class="form-control<?= clase_error($errores, 'costo') ?>"
                       value="<?= e((string) $datos['costo']) ?>">
                <div class="form-text">Se llena con el precio del catálogo; puedes cambiarlo.</div>
                <?= mensaje_error($errores, 'costo') ?>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-end gap-2">
        <a href="ver.php?id=<?= $id ?>" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Registrar atención</button>
    </div>
</form>

<?php endif; ?>

<?php require_once BASE_PATH . 'frontend/layout/footer.php'; ?>