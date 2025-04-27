<?php
require_once '../../libs/TCPDF/tcpdf.php'; // Ajusta la ruta según tu estructura

// Clase personalizada que extiende TCPDF para agregar el header con fondo y logo escalado
class InformeEmmausTemplate extends TCPDF {

    private $customTitle; // Variable para almacenar el título personalizado

    // Constructor modificado para aceptar el título personalizado
    public function __construct($title = 'Emmaus - Historial de Cursos') {
        parent::__construct();
        $this->customTitle = $title; // Asignar el título recibido
    }

    // Sobrescribir el método Header() para agregar tu propio header con fondo y logo escalado
    public function Header() {
        // Establecer color de fondo (RGB para #102B4F: Azul oscuro)
        $this->SetFillColor(16, 43, 79); // Fondo del header
        $this->SetTextColor(255, 255, 255); // Texto en blanco

        // Dibujar una celda que cubra todo el ancho de la página con una altura ajustada (15 puntos)
        $this->Cell(0, 15, '', 0, 1, 'C', 1); // La celda es más baja (15 puntos)

        // Escalar el logo proporcionalmente (ajusta la ruta del logo)
        $image_file = '../../images/emmaus/header.png'; // Ajusta la ruta de acuerdo a la ubicación de tu imagen
        list($width, $height) = getimagesize($image_file); // Obtener las dimensiones originales de la imagen
        $logo_width = 40; // Ancho deseado para el logo
        $logo_height = ($logo_width / $width) * $height; // Calcular la altura proporcional
        
        $this->Image($image_file, 17, 6, $logo_width, $logo_height, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // Estilo de la fuente para el header (blanco)
        $this->SetFont('helvetica', 'B', 14);

        // Título centrado en blanco sobre el fondo azul
        $this->SetY(10); // Ajustar la posición vertical del título
        $this->Cell(170, 10, $this->customTitle, 0, false, 'R', 0, '', 0, false, 'M', 'M');

        // Eliminar la línea adicional debajo del encabezado
        $this->Ln(15); // Espacio después del header
    }

    // Sobrescribir el método Footer() para agregar pie de página si es necesario
    public function Footer() {
        // Posicionar a 15mm del final de la página
        $this->SetY(-15);
        // Establecer fuente
        $this->SetFont('helvetica', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}