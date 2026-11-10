<?php
// Escapa texto antes de mostrarlo en HTML (evita XSS)
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

// Quita espacios sobrantes de lo que llega por formulario
function limpiar(?string $valor): string
{
    return trim($valor ?? '');
}

function es_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Redirige a una ruta relativa a BASE_URL
function redirigir(string $ruta): void
{
    header('Location: ' . BASE_URL . $ruta);
    exit;
}

function formatear_fecha(?string $fecha, bool $con_hora = false): string
{
    if (empty($fecha)) {
        return '—';
    }
    $f = new DateTime($fecha);
    return $f->format($con_hora ? 'd/m/Y h:i A' : 'd/m/Y');
}

function calcular_edad(?string $fecha_nac): ?int
{
    if (empty($fecha_nac)) {
        return null;
    }
    return (new DateTime($fecha_nac))->diff(new DateTime())->y;
}

function formatear_moneda($monto): string
{
    return '$' . number_format((float) $monto, 2, ',', '.');
}

// Para las peticiones AJAX (buscador y dashboard)
function responder_json(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

// Lee un campo de $_POST como texto limpio (si llega un arreglo, devuelve vacío)
function post(string $campo): string
{
    $valor = $_POST[$campo] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

// Convierte '' en NULL para guardar campos opcionales en la BD
function nulo_si_vacio(string $valor): ?string
{
    return $valor === '' ? null : $valor;
}

// Deja la cédula siempre con el formato V-12345678 o E-12345678
function normalizar_cedula(string $cedula): string
{
    $cedula = strtoupper(str_replace(['.', ' '], '', $cedula));

    if (ctype_digit($cedula)) {
        return 'V-' . $cedula;
    }
    if (preg_match('/^([VE])-?(\d+)$/', $cedula, $partes)) {
        return $partes[1] . '-' . $partes[2];
    }
    return $cedula;
}

function fecha_valida(string $fecha, string $formato = 'Y-m-d'): bool
{
    $f = DateTime::createFromFormat($formato, $fecha);
    return $f && $f->format($formato) === $fecha;
}

function clase_error(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? ' is-invalid' : '';
}

function mensaje_error(array $errores, string $campo): string
{
    return isset($errores[$campo])
        ? '<div class="invalid-feedback">' . e($errores[$campo]) . '</div>'
        : '';
}

// Numeración FDI: permanentes (11–48) y temporales (51–85)
function piezas_dentales(): array
{
    $permanentes = [1 => 'Incisivo central', 2 => 'Incisivo lateral', 3 => 'Canino',
                    4 => 'Primer premolar', 5 => 'Segundo premolar', 6 => 'Primer molar',
                    7 => 'Segundo molar', 8 => 'Tercer molar (cordal)'];
    $temporales  = [1 => 'Incisivo central', 2 => 'Incisivo lateral', 3 => 'Canino',
                    4 => 'Primer molar', 5 => 'Segundo molar'];
    $zonas       = [1 => 'superior derecho', 2 => 'superior izquierdo',
                    3 => 'inferior izquierdo', 4 => 'inferior derecho'];

    $piezas = [];
    foreach ($zonas as $c => $zona) {
        foreach ($permanentes as $n => $nombre) {
            $piezas[$c * 10 + $n] = "$nombre $zona";
        }
    }
    foreach ($zonas as $c => $zona) {
        foreach ($temporales as $n => $nombre) {
            $piezas[($c + 4) * 10 + $n] = "$nombre $zona (temporal)";
        }
    }
    return $piezas;
}

function nombre_pieza(?int $numero): string
{
    if (!$numero) {
        return '';
    }
    $piezas = piezas_dentales();
    return isset($piezas[$numero]) ? $numero . ' – ' . $piezas[$numero] : (string) $numero;
}

// True si el texto tiene un dato clínico real (no "ninguna", "no", "niega"...)
function tiene_dato_clinico(?string $texto): bool
{
    $t = mb_strtolower(trim($texto ?? ''));
    return $t !== '' && !in_array($t, ['ninguna', 'ninguno', 'no', 'niega', 'no refiere', 'n/a', 'na', '-'], true);
}