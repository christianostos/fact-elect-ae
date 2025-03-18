<?php
/**
 * Plugin Name: API de Facturación Electrónica DIAN
 * Plugin URI: https://accioneficaz.com
 * Description: API REST para generar documentos electrónicos válidos ante la DIAN en Colombia
 * Version: 1.0.0
 * Author: Christian Ostos - Acción Eficaz
 * License: GPL-2.0+
 */

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('DIAN_API_VERSION', '1.0.0');
define('DIAN_API_PATH', plugin_dir_path(__FILE__));
define('DIAN_API_URL', plugin_dir_url(__FILE__));

// Cargar dependencias básicas
require_once DIAN_API_PATH . 'includes/class-dian-api-dependencies.php';
require_once DIAN_API_PATH . 'includes/class-dian-api-db.php';
require_once DIAN_API_PATH . 'includes/class-dian-api-xml-generator.php';
require_once DIAN_API_PATH . 'includes/class-dian-api-webservices.php';
require_once DIAN_API_PATH . 'includes/class-dian-api-pdf-generator.php';
require_once DIAN_API_PATH . 'includes/class-dian-api-rest.php';
require_once DIAN_API_PATH . 'includes/class-dian-api-directory-structure.php';

// Función de carga automática de clases
function dian_api_autoloader($class_name) {
    // Solo procesar clases de nuestro plugin
    if (strpos($class_name, 'DIAN_API_') === 0) {
        // Convertir el nombre de la clase a un formato de archivo
        $class_name = str_replace('DIAN_API_', '', $class_name);
        $class_file = 'class-dian-api-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
        
        // Directorios donde buscar las clases
        $directories = array(
            'admin/',
            'includes/',
            'core/',
            'services/'
        );
        
        // Buscar la clase en cada directorio
        foreach ($directories as $directory) {
            $file_path = DIAN_API_PATH . $directory . $class_file;
            if (file_exists($file_path)) {
                require_once $file_path;
                break;
            }
        }
    }
}

// Registrar el autoloader
spl_autoload_register('dian_api_autoloader');

/**
 * Clase principal del plugin
 */
class DIAN_Facturacion_API {
    
    private static $instance = null;
    
    /**
     * Instancias de las clases principales
     */
    public $db;
    public $xml_generator;
    public $webservices;
    public $pdf_generator;
    public $rest_api;
    
    /**
     * Constructor
     */
    private function __construct() {
        // Registrar hooks de activación/desactivación
        register_activation_hook(__FILE__, array($this, 'activar'));
        register_deactivation_hook(__FILE__, array($this, 'desactivar'));
        
        // Inicializar componentes
        add_action('plugins_loaded', array($this, 'inicializar'));
    }
    
    /**
     * Obtener instancia (patrón Singleton)
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializar componentes del plugin
     */
    public function inicializar() {
        // Comprobar dependencias
        DIAN_API_Dependencies::check_dependencies();
        
        // Inicializar clases principales
        $this->db = new DIAN_API_DB();
        $this->xml_generator = new DIAN_API_XML_Generator($this->db);
        $this->webservices = new DIAN_API_WebServices($this->db);
        $this->pdf_generator = new DIAN_API_PDF_Generator($this->db);
        $this->rest_api = new DIAN_API_REST($this->db, $this->xml_generator, $this->webservices, $this->pdf_generator);
        
        // Inicializar panel de administración
        if (is_admin()) {
            if (class_exists('DIAN_API_Admin')) {
                new DIAN_API_Admin();
            } else {
                // Si la clase no existe, cargarla manualmente
                require_once DIAN_API_PATH . 'admin/class-dian-api-admin.php';
                new DIAN_API_Admin();
            }
        }
        
        // Inicializar la API REST
        $this->rest_api->inicializar();
    }
    
    /**
     * Activar el plugin
     */
    public function activar() {
        // Crear directorios necesarios
        DIAN_API_Directory_Structure::crear_estructura();
        
        // Crear tablas en la base de datos
        $db = new DIAN_API_DB();
        $db->crear_tablas();
        
        // Intentar descargar bibliotecas necesarias
        DIAN_API_Dependencies::check_dependencies();
        DIAN_API_Dependencies::download_phpqrcode();
        
        // Limpiar caché de reglas de reescritura
        flush_rewrite_rules();
    }
    
    /**
     * Desactivar el plugin
     */
    public function desactivar() {
        // Limpiar caché de reglas de reescritura
        flush_rewrite_rules();
    }
    
    /**
     * Crear directorios necesarios
     */
    private function crear_directorios() {
        $directorios = array(
            DIAN_API_PATH . 'logs',
            DIAN_API_PATH . 'vendor',
            DIAN_API_PATH . 'assets/css',
            DIAN_API_PATH . 'assets/js',
            DIAN_API_PATH . 'admin',
            DIAN_API_PATH . 'includes',
            DIAN_API_PATH . 'core',
            DIAN_API_PATH . 'services',
            DIAN_API_PATH . 'templates',
            DIAN_API_PATH . 'lib',
            DIAN_API_PATH . 'lib/phpqrcode',
        );
        
        foreach ($directorios as $directorio) {
            if (!file_exists($directorio)) {
                wp_mkdir_p($directorio);
            }
        }
    }
}

// Inicializar el plugin
DIAN_Facturacion_API::get_instance();