<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../models/Paciente.php';
require_once __DIR__ . '/../models/Antecedente.php';
require_once __DIR__ . '/../models/Atencion.php';
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

$id       = (int) ($_GET['id'] ?? 0);
$paciente = (new Paciente())->buscarPorId($id);

if (!$paciente) {
    set_mensaje('danger', 'El paciente que buscas no existe.');
    redirigir('frontend/pacientes/lista.php');
}

$antecedente = (new Antecedente())->buscarPorPaciente($id);
// En el documento se muestran en orden cronológico: de la más antigua a la más reciente
$atenciones  = array_reverse((new Atencion())->listarPorPaciente($id));

function t(?string $texto): string
{
    return mb_convert_encoding($texto ?? '', 'Windows-1252', 'UTF-8');
}

class HistoriaPDF extends FPDF
{
    public function Header(): void
    {
        $this->SetTextColor(15, 118, 110);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 7, t(NOMBRE_CONSULTORIO), 0, 1);

        $this->SetTextColor(90, 90, 90);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, t(NOMBRE_ODONTOLOGO), 0, 1);
        $this->Cell(110, 5, t('Historia clínica odontológica'), 0, 0);
        $this->Cell(0, 5, t('Emitida el ' . date('d/m/Y h:i A')), 0, 1, 'R');

        $y = $this->GetY() + 2;
        $this->SetDrawColor(15, 118, 110);
        $this->SetLineWidth(0.4);
        $this->Line($this->lMargin, $y, $this->GetPageWidth() - $this->rMargin, $y);

        $this->Ln(6);
        $this->SetTextColor(0, 0, 0);
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetTextColor(120, 120, 120);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, t('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }

    public function seccion(string $titulo): void
    {
        if ($this->GetY() > 245) {
            $this->AddPage();
        }
        $this->Ln(2);
        $this->SetTextColor(15, 118, 110);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 7, t($titulo), 'B', 1);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(2);
    }

    // Etiqueta a la izquierda y valor a la derecha (el valor puede ocupar varias líneas)
    public function campo(string $etiqueta, ?string $valor, bool $mostrarVacio = false): void
    {
        $valor = trim((string) $valor);
        if ($valor === '' && !$mostrarVacio) {
            return;
        }
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(45, 5.5, t($etiqueta), 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->MultiCell(0, 5.5, t($valor === '' ? 'No registrado' : $valor), 0, 'L');
    }

    public function nota(string $texto): void
    {
        $this->SetFont('Arial', 'I', 9);
        $this->MultiCell(0, 5.5, t($texto));
    }
}

$pdf = new HistoriaPDF('P', 'mm', 'Letter');
$pdf->SetTitle('Historia clínica - ' . $paciente['nombres'] . ' ' . $paciente['apellidos'], true);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AliasNbPages();
$pdf->AddPage();

// Datos del paciente
$pdf->seccion('Datos del paciente');
$pdf->campo('Nombre', $paciente['nombres'] . ' ' . $paciente['apellidos']);
$pdf->campo('Cédula', $paciente['cedula']);
$pdf->campo('Fecha de nacimiento',
    formatear_fecha($paciente['fecha_nac']) . ' (' . calcular_edad($paciente['fecha_nac']) . ' años)');
$pdf->campo('Sexo', $paciente['sexo'] === 'F' ? 'Femenino' : 'Masculino');
$pdf->campo('Teléfono', $paciente['telefono']);
$pdf->campo('Correo', $paciente['email']);
$pdf->campo('Dirección', $paciente['direccion']);
$pdf->campo('Contacto de emergencia', $paciente['contacto_emergencia']);

// Antecedentes
$pdf->seccion('Antecedentes médicos');
if (!$antecedente) {
    $pdf->nota('No se registraron antecedentes médicos.');
} else {
    $pdf->campo('Alergias', $antecedente['alergias'], true);
    $pdf->campo('Enfermedades', $antecedente['enfermedades'], true);
    $pdf->campo('Medicamentos actuales', $antecedente['medicamentos'], true);
    if ($paciente['sexo'] === 'F') {
        $pdf->campo('Embarazo', (int) $antecedente['embarazo'] === 1 ? 'Sí' : 'No');
    }
    $pdf->campo('Hábitos', $antecedente['habitos']);
    $pdf->campo('Antec. odontológicos', $antecedente['antecedentes_odontologicos']);
    $pdf->campo('Observaciones', $antecedente['observaciones']);
}

// Atenciones
$pdf->seccion('Atenciones realizadas');
if (!$atenciones) {
    $pdf->nota('No hay atenciones registradas.');
}

foreach ($atenciones as $a) {
    if ($pdf->GetY() > 235) {
        $pdf->AddPage();
    }

    $encabezado = formatear_fecha($a['fecha'], true) . '    ' . $a['procedimiento'];
    if ($a['anulada']) {
        $encabezado .= '    (ANULADA)';
    }

    $pdf->SetFillColor(230, 242, 241);
    $pdf->SetFont('Arial', 'B', 9.5);
    $pdf->Cell(0, 7, t($encabezado), 0, 1, 'L', true);
    $pdf->Ln(1);

    $pdf->campo('Pieza dental', nombre_pieza($a['pieza_dental'] ? (int) $a['pieza_dental'] : null));
    $pdf->campo('Diagnóstico', $a['diagnostico']);
    $pdf->campo('Tratamiento', $a['tratamiento']);
    $pdf->campo('Materiales', $a['materiales']);
    $pdf->campo('Indicaciones', $a['indicaciones']);
    $pdf->campo('Observaciones', $a['observaciones']);
    $pdf->campo('Próxima cita', $a['proxima_cita'] ? formatear_fecha($a['proxima_cita']) : '');
    $pdf->campo('Costo', formatear_moneda($a['costo']));
    $pdf->campo('Atendido por', $a['registrado_por']);
    if ($a['anulada']) {
        $pdf->campo('Motivo de anulación', $a['motivo_anulacion']);
    }
    $pdf->Ln(3);
}

// Firma
if ($pdf->GetY() > 225) {
    $pdf->AddPage();
}
$pdf->Ln(20);
$x = $pdf->GetPageWidth() / 2 - 40;
$pdf->Line($x, $pdf->GetY(), $x + 80, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, t(NOMBRE_ODONTOLOGO), 0, 1, 'C');
$pdf->Cell(0, 5, t('Firma y sello'), 0, 1, 'C');

$archivo = 'historia_' . preg_replace('/[^A-Za-z0-9\-]/', '', $paciente['cedula']) . '.pdf';
$pdf->Output('I', $archivo);