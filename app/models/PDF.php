<?php
require_once './utilities/fpdf.php';

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, "Productos", 1, 0, 'C');
        $this->Ln(20);
    }

    function Body($contenido)
    {
        $this->SetFont('arial', 'B', 12);
        $this->MultiCell(0, 5, $contenido);
        $this->Ln();
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('arial', 'B', 8);
        $this->Cell(0, 10, 'Comanda - Lucas barbosa/{nb}', 0, 0, 'C');
    }
}
