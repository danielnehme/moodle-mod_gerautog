<?php
//ini_set( 'default_charset', 'utf-8');
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
require('../fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$texto=utf8_decode('Olá Mundo!');
$pdf->Cell(40,10,$texto);
$pdf->Output();
?>
