<?php
/*
Copyright 2007, 2008 Nicolás Gudiño

This file is part of Asternic Call Center Stats.

Asternic Call Center Stats is free software: you can redistribute it
and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

Asternic Call Center Stats is distributed in the hope that it will be
useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Asternic Call Center Stats.  If not, see
<http://www.gnu.org/licenses/>.
 */

require_once "config.php";
require 'fpdf.php';

class PDF extends FPDF {

	function Footer() {
		global $lang;
		global $language;
		//Go to 1.5 cm from bottom
		$this->SetY(-15);
		//Select Arial italic 8
		$this->SetFont('ArialMT', '', 8);
		//Print centered page number
		$this->Cell(0, 10, $lang["$language"]['page'] . ' ' . $this->PageNo(), 0, 0, 'C');
		//$this->Cell(0, 10, ' c. ' . $this->PageNo(), 0, 0, 'C');
	}

	function Cover($cover) {
		$this->SetFont('ArialMT', '', 15);
		$this->MultiCell(250, 9, $cover);
		$this->Ln();
	}

	function Header() {
		global $title;
		//Select Arial bold 15
		$this->SetFont('ArialMT', '', 15);
		//Move to the right
		//$this->Cell(85);
		//Framed title
		$this->Cell(30, 10, $title, 0, 0, 'C');
		//Line break
		$this->Ln(10);
	}

	function TableHeader($header, $w) {
		$this->SetFillColor(255, 0, 0);
		$this->SetTextColor(255);
		$this->SetDrawColor(128, 0, 0);
		$this->SetLineWidth(.3);
		$this->SetFont('', '', 11);

		for ($i = 0; $i < count($header); $i++) {
			//$this->SetX($x_axis);
			$this->Cell($w[$i], 12, $header[$i], 'LTRB', 0, 'L', 1);
		}

		$this->Ln();
	}

//Colored table
	function FancyTable($header, $data, $w) {

		//$this->TableHeader($header, $w);

		//Color and font restoration
		$this->SetFillColor(224, 235, 255);
		$this->SetTextColor(0);
		$this->SetFont('');
		//Data
		$fill = 0;
		$supercont = 1;
		foreach ($data as $row) {
			$contador = 0;
			foreach ($row as $valor) {
				$this->Cell($w[$contador], 6, $valor, 'LR', 0, 'C', $fill);
				$contador++;
			}
			$this->Ln();
			$fill = !$fill;
			if ($supercont % 40 == 0) {
				$this->Cell(array_sum($w), 0, '', 'T');
				$this->AddPage();
				//$this->TableHeader($header, $w);
				$this->SetFillColor(224, 235, 255);
				$this->SetTextColor(0);
				$this->SetFont('');
			}
			$supercont++;
		}
		$this->Cell(array_sum($w), 0, '', 'T');
	}
}

function export_csv($header, $data) {
	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=table.csv");

	$linea = "";
	foreach ($header as $valor) {
		$valor = iconv('UTF-8', 'windows-1251', $valor);
		$linea .= "\"$valor\";";
	}
	$linea = substr($linea, 0, -1);

	print $linea . "\r\n";

	foreach ($data as $valor) {
		$linea = "";
		foreach ($valor as $subvalor) {
			$subvalor = iconv('UTF-8', 'windows-1251', $subvalor);
			$linea .= "\"$subvalor\";";
		}
		$linea = substr($linea, 0, -1);
		print $linea . "\r\n";
	}
}

$headercsv = unserialize(rawurldecode($_POST['headcsv']));
$header = unserialize(rawurldecode($_POST['head']));
$data = unserialize(rawurldecode($_POST['rawdata']));
$width = unserialize(rawurldecode($_POST['width']));
$title = unserialize(rawurldecode($_POST['title']));
$cover = unserialize(rawurldecode($_POST['cover']));

if (isset($_POST['pdf']) || isset($_POST['pdf_x'])) {
	$pdf = new PDF();
	$pdf->AddFont('ArialMT','','arialuni.php');
	// $pdf->AddFont('ArialMT','B','arial.php');
	$pdf->SetFont('ArialMT','',12);
	// $pdf->SetFont('ArialMT','B',12); 
	$pdf->SetAutoPageBreak(false);
	$pdf->SetLeftMargin(1);
	$pdf->SetRightMargin(1);
	$pdf->AddPage();
	$pdf->TableHeader($header, $width);
	$pdf->FancyTable($header, $data, $width);
	$pdf->AddPage();
	if ($cover != "") {
		$pdf->Cover($cover);
	}
	$fn = str_replace(" ", "_", $title);
	$filename = $fn . ".pdf";
	$pdf->Output($filename,"D");
	//$pdf->Output('F', '/var/www/html/queue-stats/pdf/export.pdf', true);
} else {
	export_csv($headercsv, $data);
}
?>
