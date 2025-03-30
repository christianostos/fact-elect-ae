<?php
/**
 * Clase para generación de PDFs sin dependencias adicionales
 *
 * @link       https://accioneficaz.com
 * @since      1.0.0
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/includes
 */

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para generación de PDFs
 */
class DIAN_API_PDF_Generator {

    /**
     * Instancia de la clase DB
     */
    private $db;

    /**
     * Datos del documento actual
     */
    private $document;

    /**
     * Datos de la empresa
     */
    private $company;

    /**
     * Instancia de TCPDF
     */
    private $tcpdf;

    /**
     * Constructor
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Genera el PDF para un documento
     *
     * @param int $document_id ID del documento
     * @return string Contenido del PDF o false en caso de error
     */
    public function generate_document_pdf($document_id) {
        // Obtener datos del documento
        $document = $this->db->get_document($document_id);
        
        if (!$document) {
            return false;
        }
        
        $this->document = $document;
        $this->company = json_decode($document['company_data'], true);
        
        // Cargar TCPDF
        $this->load_tcpdf();
        
        // Inicializar TCPDF
        $this->tcpdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Configuración básica
        $this->tcpdf->SetCreator($this->company['name']);
        $this->tcpdf->SetAuthor($this->company['name']);
        $this->tcpdf->SetTitle('Factura Electrónica ' . $document['prefix'] . '-' . $document['consecutive']);
        $this->tcpdf->SetMargins(10, 10, 10);
        $this->tcpdf->SetAutoPageBreak(true, 10);
        $this->tcpdf->setFontSubsetting(true);
        
        // Generar contenido
        return $this->generate_pdf_content();
    }
    
    /**
     * Carga TCPDF si no está disponible
     */
    private function load_tcpdf() {
        // Verificar si TCPDF ya está incluido
        if (!class_exists('TCPDF')) {
            // Primero intentar con la versión existente en el plugin
            $tcpdf_path = DIAN_API_PATH . 'lib/phpqrcode/bindings/tcpdf/tcpdf.php';
            
            if (file_exists($tcpdf_path)) {
                require_once $tcpdf_path;
            } else {
                // Si no existe, intentar con la versión del vendor
                $tcpdf_vendor_path = DIAN_API_PATH . 'vendor/tcpdf/tcpdf.php';
                
                if (file_exists($tcpdf_vendor_path)) {
                    require_once $tcpdf_vendor_path;
                } else {
                    // Si no existe, intentar descargarla
                    $this->install_tcpdf();
                    
                    // Verificar si se instaló correctamente
                    if (file_exists($tcpdf_vendor_path)) {
                        require_once $tcpdf_vendor_path;
                    } else {
                        // Error si no se pudo cargar
                        throw new Exception('No se pudo cargar TCPDF. Por favor instálelo manualmente.');
                    }
                }
            }
        }
    }

    /**
     * Genera el contenido del PDF
     *
     * @return string Contenido del PDF
     */
    private function generate_pdf_content() {
        // Agregamos una página
        $this->tcpdf->AddPage();
        
        // Establecemos la fuente
        $this->tcpdf->SetFont('helvetica', '', 10);
        
        // Generamos el encabezado
        $this->generate_header();
        
        // Generamos la información del cliente
        $this->generate_customer_info();
        
        // Generamos la tabla de productos
        $this->generate_products_table();
        
        // Generamos los totales
        $this->generate_totals();
        
        // Generamos el pie de página
        $this->generate_footer();
        
        // Generamos el código QR
        $this->generate_qr();
        
        // Devolvemos el PDF como string
        return $this->tcpdf->Output('', 'S');
    }
    
    /**
     * Genera el encabezado del documento
     */
    private function generate_header() {
        // Logo de la empresa
        if (!empty($this->company['logo'])) {
            $this->tcpdf->Image($this->company['logo'], 10, 10, 30);
        }
        
        // Información de la empresa
        $this->tcpdf->SetXY(45, 10);
        $this->tcpdf->SetFont('helvetica', 'B', 12);
        $this->tcpdf->Cell(0, 6, $this->company['name'], 0, 1);
        
        $this->tcpdf->SetX(45);
        $this->tcpdf->SetFont('helvetica', '', 9);
        $this->tcpdf->Cell(0, 5, 'NIT: ' . $this->company['tax_id'], 0, 1);
        $this->tcpdf->SetX(45);
        $this->tcpdf->Cell(0, 5, $this->company['address'], 0, 1);
        $this->tcpdf->SetX(45);
        $this->tcpdf->Cell(0, 5, $this->company['phone'], 0, 1);
        $this->tcpdf->SetX(45);
        $this->tcpdf->Cell(0, 5, $this->company['email'], 0, 1);
        
        // Información del documento
        $this->tcpdf->SetXY(130, 10);
        $this->tcpdf->SetFont('helvetica', 'B', 11);
        $this->tcpdf->Cell(0, 6, 'FACTURA ELECTRÓNICA DE VENTA', 0, 1, 'R');
        
        $this->tcpdf->SetX(130);
        $this->tcpdf->SetFont('helvetica', '', 10);
        $this->tcpdf->Cell(0, 5, $this->document['prefix'] . '-' . $this->document['consecutive'], 0, 1, 'R');
        
        $date = date('Y-m-d', strtotime($this->document['issue_date']));
        $this->tcpdf->SetX(130);
        $this->tcpdf->Cell(0, 5, 'Fecha de emisión: ' . $date, 0, 1, 'R');
        
        // Agregamos una línea separadora
        $this->tcpdf->Ln(5);
        $this->tcpdf->Line(10, $this->tcpdf->GetY(), 200, $this->tcpdf->GetY());
        $this->tcpdf->Ln(5);
    }

    /**
     * Genera la información del cliente
     */
    private function generate_customer_info() {
        $customer = json_decode($this->document['customer_data'], true);
        
        $this->tcpdf->SetFont('helvetica', 'B', 11);
        $this->tcpdf->Cell(0, 6, 'DATOS DEL CLIENTE', 0, 1);
        
        $this->tcpdf->SetFont('helvetica', '', 9);
        $this->tcpdf->Cell(40, 5, 'Razón Social:', 0);
        $this->tcpdf->Cell(0, 5, $customer['name'], 0, 1);
        
        $this->tcpdf->Cell(40, 5, 'NIT/CC:', 0);
        $this->tcpdf->Cell(0, 5, $customer['tax_id'], 0, 1);
        
        $this->tcpdf->Cell(40, 5, 'Dirección:', 0);
        $this->tcpdf->Cell(0, 5, $customer['address'], 0, 1);
        
        $this->tcpdf->Cell(40, 5, 'Teléfono:', 0);
        $this->tcpdf->Cell(0, 5, $customer['phone'], 0, 1);
        
        $this->tcpdf->Cell(40, 5, 'Email:', 0);
        $this->tcpdf->Cell(0, 5, $customer['email'], 0, 1);
        
        $this->tcpdf->Ln(5);
        $this->tcpdf->Line(10, $this->tcpdf->GetY(), 200, $this->tcpdf->GetY());
        $this->tcpdf->Ln(5);
    }
    
    /**
     * Genera la tabla de productos
     */
    private function generate_products_table() {
        $items = json_decode($this->document['items'], true);
        
        // Encabezados de la tabla
        $this->tcpdf->SetFont('helvetica', 'B', 9);
        $this->tcpdf->Cell(15, 7, 'CANT.', 1, 0, 'C');
        $this->tcpdf->Cell(85, 7, 'DESCRIPCIÓN', 1, 0, 'C');
        $this->tcpdf->Cell(30, 7, 'VALOR UNIT.', 1, 0, 'C');
        $this->tcpdf->Cell(25, 7, 'IVA', 1, 0, 'C');
        $this->tcpdf->Cell(35, 7, 'VALOR TOTAL', 1, 1, 'C');
        
        // Contenido de la tabla
        $this->tcpdf->SetFont('helvetica', '', 8);
        
        foreach ($items as $item) {
            $this->tcpdf->Cell(15, 6, $item['quantity'], 1, 0, 'C');
            $this->tcpdf->Cell(85, 6, $item['description'], 1, 0, 'L');
            $this->tcpdf->Cell(30, 6, number_format($item['price'], 2, ',', '.'), 1, 0, 'R');
            $this->tcpdf->Cell(25, 6, number_format($item['tax_value'], 2, ',', '.'), 1, 0, 'R');
            $this->tcpdf->Cell(35, 6, number_format($item['total'], 2, ',', '.'), 1, 1, 'R');
        }
        
        $this->tcpdf->Ln(5);
    }
    
    /**
     * Genera los totales
     */
    private function generate_totals() {
        $totals = json_decode($this->document['totals'], true);
        
        $this->tcpdf->SetFont('helvetica', '', 9);
        
        // Calculamos la posición para que quede a la derecha
        $startX = 120;
        $width = 40;
        $valueWidth = 30;
        
        $this->tcpdf->SetX($startX);
        $this->tcpdf->Cell($width, 6, 'SUBTOTAL:', 0, 0, 'R');
        $this->tcpdf->Cell($valueWidth, 6, number_format($totals['gross_total'], 2, ',', '.'), 0, 1, 'R');
        
        $this->tcpdf->SetX($startX);
        $this->tcpdf->Cell($width, 6, 'DESCUENTO:', 0, 0, 'R');
        $this->tcpdf->Cell($valueWidth, 6, number_format($totals['discount'], 2, ',', '.'), 0, 1, 'R');
        
        $this->tcpdf->SetX($startX);
        $this->tcpdf->Cell($width, 6, 'IVA:', 0, 0, 'R');
        $this->tcpdf->Cell($valueWidth, 6, number_format($totals['tax'], 2, ',', '.'), 0, 1, 'R');
        
        $this->tcpdf->SetFont('helvetica', 'B', 10);
        $this->tcpdf->SetX($startX);
        $this->tcpdf->Cell($width, 6, 'TOTAL:', 0, 0, 'R');
        $this->tcpdf->Cell($valueWidth, 6, number_format($totals['payable_amount'], 2, ',', '.'), 0, 1, 'R');
        
        $this->tcpdf->Ln(5);
    }

    /**
     * Genera el pie de página
     */
    private function generate_footer() {
        $this->tcpdf->SetY(-50);
        $this->tcpdf->SetFont('helvetica', 'I', 8);
        
        // Notas
        $this->tcpdf->Cell(0, 5, 'Factura emitida bajo la Resolución DIAN No. ' . $this->document['resolution_number'], 0, 1);
        
        // CUFE
        if (!empty($this->document['cufe'])) {
            $this->tcpdf->Cell(0, 5, 'CUFE: ' . $this->document['cufe'], 0, 1);
        }
        
        // Información de validación
        if (!empty($this->document['validation_date'])) {
            $this->tcpdf->Cell(0, 5, 'Fecha validación DIAN: ' . $this->document['validation_date'], 0, 1);
        }
        
        // Pie de página estándar
        $this->tcpdf->Cell(0, 5, 'Esta factura se asimila en sus efectos a la letra de cambio según Art. 774 Código de Comercio', 0, 1, 'C');
    }
    
    /**
     * Genera el código QR
     */
    private function generate_qr() {
        // Verificamos si existe la biblioteca QR en el plugin
        if (file_exists(DIAN_API_PATH . 'lib/phpqrcode/phpqrcode.php')) {
            require_once DIAN_API_PATH . 'lib/phpqrcode/phpqrcode.php';
            
            // Crear directorio de cache si no existe
            $cache_dir = DIAN_API_PATH . 'lib/phpqrcode/cache/';
            if (!is_dir($cache_dir)) {
                wp_mkdir_p($cache_dir);
            }
            
            $qr_data = !empty($this->document['qr_data']) ? $this->document['qr_data'] : 
                       $this->document['prefix'] . $this->document['consecutive'];
            $temp_file = $cache_dir . 'qr_' . md5($qr_data) . '.png';
            
            // Generamos el QR como imagen
            QRcode::png($qr_data, $temp_file, QR_ECLEVEL_L, 3);
            
            // Posicionamos el QR
            if (file_exists($temp_file)) {
                $this->tcpdf->Image($temp_file, 10, $this->tcpdf->GetY() - 30, 30, 30);
                
                // No eliminamos el archivo temporal para poder reusarlo
            }
        }
    }
    
    /**
     * Instala TCPDF si no está disponible
     *
     * @return bool Éxito o fracaso
     */
    public static function install_tcpdf() {
        // Crear carpeta vendor si no existe
        $vendor_dir = DIAN_API_PATH . 'vendor';
        if (!is_dir($vendor_dir)) {
            wp_mkdir_p($vendor_dir);
        }
        
        // Crear carpeta tcpdf si no existe
        $tcpdf_dir = $vendor_dir . '/tcpdf';
        if (!is_dir($tcpdf_dir)) {
            wp_mkdir_p($tcpdf_dir);
        }
        
        // Si ya está instalado, salir
        if (file_exists($tcpdf_dir . '/tcpdf.php')) {
            return true;
        }
        
        // Descargar TCPDF desde su GitHub
        $zip_file = $vendor_dir . '/tcpdf.zip';
        $zip_url = 'https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.5.0.zip';
        
        // Usar WordPress para descargar
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();
        global $wp_filesystem;
        
        $response = wp_remote_get($zip_url);
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $wp_filesystem->put_contents($zip_file, $body);
        
        // Descomprimir archivo
        $unzipped = unzip_file($zip_file, $vendor_dir);
        
        if (is_wp_error($unzipped)) {
            return false;
        }
        
        // Mover archivos
        $extracted_dir = $vendor_dir . '/TCPDF-6.5.0';
        if ($wp_filesystem->is_dir($extracted_dir)) {
            $wp_filesystem->copy_dir($extracted_dir, $tcpdf_dir);
            $wp_filesystem->delete($extracted_dir, true);
        }
        
        // Eliminar zip
        $wp_filesystem->delete($zip_file);
        
        return file_exists($tcpdf_dir . '/tcpdf.php');
    }
}