<?php
session_start();

include("auth.php");
include("conexion.php");

require('fpdf/fpdf.php');

ob_start(); // evita salida accidental


$actividad = $_GET['actividad'] ?? '';

if(empty($actividad)){
    die("Actividad no válida");
}


// ================= CONSULTA =================

$stmt = $conn->prepare("

SELECT

    a.fecha,
    a.hora_inicio,
    a.hora_fin,

    CONCAT(
        u.nombre,' ',
        IFNULL(u.nombre_s,''),' ',
        IFNULL(u.apellido_p,''),' ',
        IFNULL(u.apellido_m,'')
    ) AS alumno

FROM disponible d

INNER JOIN usuario u
ON d.id_usuario=u.id_usuario

INNER JOIN actividad a
ON d.id_actividad=a.id_actividad

WHERE a.nombre=?

ORDER BY alumno ASC

");

$stmt->bind_param(
    "s",
    $actividad
);

$stmt->execute();

$result = $stmt->get_result();


// Obtener datos generales de actividad
$datos = $result->fetch_assoc();

$fechaActividad = $datos['fecha'] ?? '';
$horaInicio = $datos['hora_inicio'] ?? '';
$horaFin = $datos['hora_fin'] ?? '';


// Reiniciar puntero
$result->data_seek(0);



// ================= CONVERTIR TEXTO =================

function convertir($texto){

    return mb_convert_encoding(
        $texto,
        'ISO-8859-1',
        'UTF-8'
    );

}



// ================= CLASE PDF =================

class PDF extends FPDF{

    function Header(){

        // Logo
        $this->Image(
            'img/uni_4.png',
            15,
            8,
            35
        );

        // Título universidad
        $this->SetFont(
            'Arial',
            'B',
            15
        );

        $this->Cell(
            0,
            10,
            convertir('Universidad Mundo Maya'),
            0,
            1,
            'C'
        );

        $this->SetFont(
            'Arial',
            'B',
            13
        );

        $this->Cell(
            0,
            8,
            'UMMA',
            0,
            1,
            'C'
        );

        $this->Ln(10);

    }


    function Footer(){

        $this->SetY(-15);

        $this->SetFont(
            'Arial',
            'I',
            8
        );

        $this->Cell(
            0,
            10,
            convertir('Página ').$this->PageNo(),
            0,
            0,
            'C'
        );

    }

}


// ================= PDF =================

$pdf = new PDF();

$pdf->AliasNbPages();

$pdf->AddPage();




// ================= TITULO ACTIVIDAD =================

$pdf->SetFont(
    'Arial',
    'B',
    13
);

$pdf->Cell(
    0,
    10,
    convertir("Actividad: ".$actividad),
    0,
    1
);



// ================= FECHA Y HORAS =================

$pdf->SetFont(
    'Arial',
    '',
    10
);

$pdf->Cell(
    0,
    7,
    convertir("Fecha: ".$fechaActividad),
    0,
    1
);

$pdf->Cell(
    0,
    7,
    convertir("Hora inicio: ".$horaInicio),
    0,
    1
);

$pdf->Cell(
    0,
    7,
    convertir("Hora fin: ".$horaFin),
    0,
    1
);

$pdf->Ln(5);



// ================= ENCABEZADOS =================

$pdf->SetFont(
    'Arial',
    'B',
    10
);

$pdf->SetFillColor(
    64,
    49,
    161
);

$pdf->SetTextColor(
    255,
    255,
    255
);


$pdf->Cell(
    15,
    10,
    '#',
    1,
    0,
    'C',
    true
);

$pdf->Cell(
    90,
    10,
    convertir('Alumno'),
    1,
    0,
    'C',
    true
);

$pdf->Cell(
    42,
    10,
    convertir('Firma Entrada'),
    1,
    0,
    'C',
    true
);

$pdf->Cell(
    42,
    10,
    convertir('Firma Salida'),
    1,
    1,
    'C',
    true
);




// ================= DATOS =================

$pdf->SetFont(
    'Arial',
    '',
    9
);

$pdf->SetTextColor(
    0,
    0,
    0
);


$contador=1;

while($row=$result->fetch_assoc()){

    $pdf->Cell(
        15,
        12,
        $contador,
        1,
        0,
        'C'
    );

    $pdf->Cell(
        90,
        12,
        convertir($row['alumno']),
        1,
        0
    );

    // Espacio firma entrada
    $pdf->Cell(
        42,
        12,
        '',
        1,
        0
    );

    // Espacio firma salida
    $pdf->Cell(
        42,
        12,
        '',
        1,
        1
    );

    $contador++;
}



// ================= SI NO HAY ALUMNOS =================

if($contador==1){

    $pdf->Cell(
        189,
        10,
        convertir('No existen alumnos inscritos'),
        1,
        1,
        'C'
    );

}



// ================= GENERAR =================

ob_end_clean();

$pdf->Output(
    'I',
    'Lista_'.$actividad.'.pdf'
);


$stmt->close();

$conn->close();

exit;

?>