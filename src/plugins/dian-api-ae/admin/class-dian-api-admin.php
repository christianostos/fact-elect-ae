<?php
/**
 * Funcionalidad del plugin específica para el área de administración.
 *
 * @link       https://accioneficaz.com
 * @since      1.0.0
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/admin
 */

// Evitar acceso directo al archivo
if (!defined('WPINC')) {
    die;
}

/**
 * Clase para la administración del plugin
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/admin
 * @author     Christian Ostos - Acción Eficaz
 */
class DIAN_API_Admin {

    /**
     * Hook para el menú de administración
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $menu_hook    El hook del menú de administración
     */
    private $menu_hook;

    /**
     * Inicializa la clase y define los hooks de administración.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Hooks para el área de administración
        add_action('admin_menu', array($this, 'agregar_menu_admin'));
        add_action('admin_enqueue_scripts', array($this, 'cargar_estilos_scripts'));
        
        // Hooks para guardar opciones
        add_action('admin_init', array($this, 'registrar_opciones'));
        
        // Ajax handlers
        add_action('wp_ajax_dian_api_guardar_cliente', array($this, 'ajax_guardar_cliente'));
        add_action('wp_ajax_dian_api_guardar_resolucion', array($this, 'ajax_guardar_resolucion'));
        add_action('wp_ajax_dian_api_generar_api_key', array($this, 'ajax_generar_api_key'));
        add_action('wp_ajax_dian_api_obtener_documento', array($this, 'ajax_obtener_documento'));
        add_action('wp_ajax_dian_api_enviar_documento', array($this, 'ajax_enviar_documento'));
        add_action('wp_ajax_dian_api_verificar_estado', array($this, 'ajax_verificar_estado'));
    }

    /**
     * Agrega el menú de administración del plugin
     *
     * @since    1.0.0
     */
    public function agregar_menu_admin() {
        // Menú principal
        $this->menu_hook = add_menu_page(
            'API de Facturación Electrónica DIAN',         // Título de la página
            'Facturación DIAN',                           // Título del menú
            'manage_options',                             // Capacidad requerida
            'dian-api',                                  // Slug del menú
            array($this, 'mostrar_pagina_principal'),    // Callback para mostrar la página
            'dashicons-media-spreadsheet',               // Icono
            26                                           // Posición
        );
        
        // Submenús
        add_submenu_page(
            'dian-api',                                  // Slug del menú padre
            'Inicio - API de Facturación DIAN',          // Título de la página
            'Inicio',                                    // Título del submenú
            'manage_options',                            // Capacidad requerida
            'dian-api',                                  // Slug del submenú
            array($this, 'mostrar_pagina_principal')     // Callback para mostrar la página
        );
        
        add_submenu_page(
            'dian-api',
            'Configuración - API de Facturación DIAN',
            'Configuración',
            'manage_options',
            'dian-api-config',
            array($this, 'mostrar_pagina_configuracion')
        );
        
        add_submenu_page(
            'dian-api',
            'Documentos - API de Facturación DIAN',
            'Documentos',
            'manage_options',
            'dian-api-documentos',
            array($this, 'mostrar_pagina_documentos')
        );
        
        add_submenu_page(
            'dian-api',
            'Resoluciones - API de Facturación DIAN',
            'Resoluciones',
            'manage_options',
            'dian-api-resoluciones',
            array($this, 'mostrar_pagina_resoluciones')
        );
        
        add_submenu_page(
            'dian-api',
            'API Keys - API de Facturación DIAN',
            'API Keys',
            'manage_options',
            'dian-api-keys',
            array($this, 'mostrar_pagina_api_keys')
        );
        
        add_submenu_page(
            'dian-api',
            'Herramientas - API de Facturación DIAN',
            'Herramientas',
            'manage_options',
            'dian-api-herramientas',
            array($this, 'mostrar_pagina_herramientas')
        );
        
        add_submenu_page(
            'dian-api',
            'Logs - API de Facturación DIAN',
            'Logs',
            'manage_options',
            'dian-api-logs',
            array($this, 'mostrar_pagina_logs')
        );
    }

    /**
     * Carga los estilos y scripts necesarios para el área de administración
     *
     * @since    1.0.0
     * @param    string    $hook_suffix    Hook del menú actual
     */
    public function cargar_estilos_scripts($hook_suffix) {
        // Solo cargar en las páginas del plugin
        if (strpos($hook_suffix, 'dian-api') === false) {
            return;
        }
        
        // Estilos
        wp_enqueue_style(
            'dian-api-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin.css',
            array(),
            DIAN_API_VERSION,
            'all'
        );
        
        // Scripts
        wp_enqueue_script(
            'dian-api-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin.js',
            array('jquery'),
            DIAN_API_VERSION,
            true
        );
        
        // Agregar datos para el script
        wp_localize_script(
            'dian-api-admin',
            'dian_api_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('dian_api_nonce'),
                'messages' => array(
                    'saving' => 'Guardando...',
                    'saved' => 'Guardado correctamente',
                    'error' => 'Error al guardar',
                    'confirm_delete' => '¿Está seguro de eliminar este registro?',
                    'confirm_send' => '¿Está seguro de enviar este documento a la DIAN?',
                    'sending' => 'Enviando...',
                    'sent' => 'Documento enviado correctamente',
                    'checking' => 'Verificando estado...',
                    'status_updated' => 'Estado actualizado',
                    'generating' => 'Generando...',
                    'generated' => 'Generado correctamente'
                )
            )
        );
    }

    /**
     * Registra las opciones del plugin
     *
     * @since    1.0.0
     */
    public function registrar_opciones() {
        // Registrar grupo de opciones
        register_setting('dian_api_options', 'dian_api_company_logo');
        register_setting('dian_api_options', 'dian_api_company_name');
        register_setting('dian_api_options', 'dian_api_company_nit');
        register_setting('dian_api_options', 'dian_api_company_address');
        register_setting('dian_api_options', 'dian_api_company_phone');
        register_setting('dian_api_options', 'dian_api_company_email');
        register_setting('dian_api_options', 'dian_api_company_website');
        register_setting('dian_api_options', 'dian_api_pdf_footer_text');
        register_setting('dian_api_options', 'dian_api_pdf_primary_color');
        register_setting('dian_api_options', 'dian_api_pdf_paper_size');
    }

    /**
     * Muestra la página principal del plugin
     *
     * @since    1.0.0
     */
    public function mostrar_pagina_principal() {
        include_once DIAN_API_PATH . 'admin/partials/dian-api-admin-main.php';
    }

    /**
     * Muestra la página de configuración
     *
     * @since    1.0.0
     */
    public function mostrar_pagina_configuracion() {
        include_once DIAN_API_PATH . 'admin/partials/dian-api-admin-config.php';
    }

    /**
     * Muestra la página de documentos
     *
     * @since    1.0.0
     */
    public function mostrar_pagina_documentos() {
        include_once DIAN_API_PATH . 'admin/partials/dian-api-admin-documentos.php';
    }

    /**
     * Muestra la página de resoluciones
     *
     * @since    1.0.0
     */
    public function mostrar_pagina_resoluciones() {
        include_once DIAN_API_PATH . 'admin/partials/dian-api-admin-resoluciones.php';
    }

    /**
     * Muestra la página de API Keys
     *
     * @since    1.0.0
     */
    public function mostrar_pagina_api_keys() {
        include_once DIAN_API_PATH . 'admin/partials/dian-api-admin-api-keys.php';
    }

    /**
     * Muestra la página de herramientas
     *
     * @since    1.0.0
     */
    public function mostrar_pagina_herramientas() {
        include_once DIAN_API_PATH . 'admin/partials/dian-api-admin-herramientas.php';
    }

    /**
     * Muestra la página de logs
     *
     * @since    1.0.0
     */
    public function mostrar_pagina_logs() {
        include_once DIAN_API_PATH . 'admin/partials/dian-api-admin-logs.php';
    }

    /**
     * Handler AJAX para guardar datos de cliente
     *
     * @since    1.0.0
     */
    public function ajax_guardar_cliente() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener y sanitizar datos
        $cliente_id = sanitize_text_field($_POST['cliente_id']);
        $id_software = sanitize_text_field($_POST['id_software']);
        $software_pin = sanitize_text_field($_POST['software_pin']);
        $certificado_ruta = sanitize_text_field($_POST['certificado_ruta']);
        $certificado_clave = sanitize_text_field($_POST['certificado_clave']);
        $url_ws_validacion = sanitize_text_field($_POST['url_ws_validacion']);
        $url_ws_produccion = sanitize_text_field($_POST['url_ws_produccion']);
        $modo_operacion = sanitize_text_field($_POST['modo_operacion']);
        $test_set_id = sanitize_text_field($_POST['test_set_id']);
        
        // Datos para guardar
        $datos = array(
            'cliente_id' => $cliente_id,
            'id_software' => $id_software,
            'software_pin' => $software_pin,
            'certificado_ruta' => $certificado_ruta,
            'certificado_clave' => $certificado_clave,
            'url_ws_validacion' => $url_ws_validacion,
            'url_ws_produccion' => $url_ws_produccion,
            'modo_operacion' => $modo_operacion,
            'test_set_id' => $test_set_id
        );
        
        // Guardar en la base de datos
        $db = new DIAN_API_DB();
        $resultado = $db->guardar_configuracion($datos);
        
        if ($resultado) {
            wp_send_json_success('Cliente guardado correctamente');
        } else {
            wp_send_json_error('Error al guardar el cliente');
        }
    }

    /**
     * Handler AJAX para guardar resolución
     *
     * @since    1.0.0
     */
    public function ajax_guardar_resolucion() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener y sanitizar datos
        $cliente_id = sanitize_text_field($_POST['cliente_id']);
        $prefijo = sanitize_text_field($_POST['prefijo']);
        $desde_numero = sanitize_text_field($_POST['desde_numero']);
        $hasta_numero = sanitize_text_field($_POST['hasta_numero']);
        $numero_resolucion = sanitize_text_field($_POST['numero_resolucion']);
        $fecha_resolucion = sanitize_text_field($_POST['fecha_resolucion']);
        $fecha_desde = sanitize_text_field($_POST['fecha_desde']);
        $fecha_hasta = sanitize_text_field($_POST['fecha_hasta']);
        $tipo_documento = sanitize_text_field($_POST['tipo_documento']);
        $es_vigente = isset($_POST['es_vigente']) ? 1 : 0;
        
        // Datos para guardar
        $datos = array(
            'cliente_id' => $cliente_id,
            'prefijo' => $prefijo,
            'desde_numero' => $desde_numero,
            'hasta_numero' => $hasta_numero,
            'numero_resolucion' => $numero_resolucion,
            'fecha_resolucion' => $fecha_resolucion,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'tipo_documento' => $tipo_documento,
            'es_vigente' => $es_vigente
        );
        
        // Guardar en la base de datos
        $db = new DIAN_API_DB();
        $resultado = $db->guardar_resolucion($datos);
        
        if ($resultado) {
            wp_send_json_success('Resolución guardada correctamente');
        } else {
            wp_send_json_error('Error al guardar la resolución');
        }
    }

    /**
     * Handler AJAX para generar API Key
     *
     * @since    1.0.0
     */
    public function ajax_generar_api_key() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener y sanitizar datos
        $nombre = sanitize_text_field($_POST['nombre']);
        $permisos = isset($_POST['permisos']) ? sanitize_text_field($_POST['permisos']) : 'read';
        
        // Datos para guardar
        $datos = array(
            'nombre' => $nombre,
            'permisos' => $permisos,
            'usuario_id' => get_current_user_id()
        );
        
        // Guardar en la base de datos
        $db = new DIAN_API_DB();
        $resultado = $db->crear_api_key($datos);
        
        if ($resultado) {
            wp_send_json_success(array(
                'message' => 'API Key generada correctamente',
                'api_key' => $resultado['api_key'],
                'api_secret' => $resultado['api_secret']
            ));
        } else {
            wp_send_json_error('Error al generar la API Key');
        }
    }

    /**
     * Handler AJAX para obtener documento
     *
     * @since    1.0.0
     */
    public function ajax_obtener_documento() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener y sanitizar datos
        $cliente_id = sanitize_text_field($_POST['cliente_id']);
        $prefijo = sanitize_text_field($_POST['prefijo']);
        $numero = sanitize_text_field($_POST['numero']);
        $tipo_documento = sanitize_text_field($_POST['tipo_documento']);
        
        // Obtener documento
        $db = new DIAN_API_DB();
        $documento = $db->obtener_documento_por_numero($cliente_id, $tipo_documento, $prefijo, $numero);
        
        if ($documento) {
            wp_send_json_success(array(
                'documento' => $documento
            ));
        } else {
            wp_send_json_error('Documento no encontrado');
        }
    }

    /**
     * Handler AJAX para enviar documento a la DIAN
     *
     * @since    1.0.0
     */
    public function ajax_enviar_documento() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener y sanitizar datos
        $cliente_id = sanitize_text_field($_POST['cliente_id']);
        $prefijo = sanitize_text_field($_POST['prefijo']);
        $numero = sanitize_text_field($_POST['numero']);
        $tipo_documento = sanitize_text_field($_POST['tipo_documento']);
        
        // Obtener documento
        $db = new DIAN_API_DB();
        $documento = $db->obtener_documento_por_numero($cliente_id, $tipo_documento, $prefijo, $numero);
        
        if (!$documento) {
            wp_send_json_error('Documento no encontrado');
            return;
        }
        
        // Obtener configuración del cliente
        $config = $db->obtener_configuracion($cliente_id);
        if (!$config) {
            wp_send_json_error('Configuración de cliente no encontrada');
            return;
        }
        
        // Configurar credenciales
        $credentials = array(
            'software_id' => $config['id_software'],
            'software_pin' => $config['software_pin'],
            'company_id' => $config['cliente_id'],
            'company_pin' => isset($config['company_pin']) ? $config['company_pin'] : '',
            'test_set_id' => isset($config['test_set_id']) ? $config['test_set_id'] : ''
        );
        
        // Instanciar webservices
        $webservices = new DIAN_API_WebServices($db, $config['modo_operacion'], $credentials);
        
        // Enviar documento
        $xml_content = $documento['archivo_xml'];
        $result = $webservices->send_document($xml_content, $tipo_documento);
        
        if ($result['success']) {
            // Actualizar estado del documento
            $update_data = array(
                'estado' => 'enviado',
                'track_id' => $result['track_id'],
                'respuesta_dian' => isset($result['response']) ? $result['response'] : ''
            );
            
            $db->actualizar_documento_por_track_id($result['track_id'], $update_data);
            
            wp_send_json_success(array(
                'message' => 'Documento enviado correctamente',
                'track_id' => $result['track_id']
            ));
        } else {
            wp_send_json_error('Error al enviar el documento: ' . $result['message']);
        }
    }

    /**
     * Handler AJAX para verificar estado de documento
     *
     * @since    1.0.0
     */
    public function ajax_verificar_estado() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener y sanitizar datos
        $track_id = sanitize_text_field($_POST['track_id']);
        
        // Obtener documento
        $db = new DIAN_API_DB();
        $documento = $db->obtener_documento_por_track_id($track_id);
        
        if (!$documento) {
            wp_send_json_error('Documento no encontrado');
            return;
        }
        
        // Obtener configuración del cliente
        $config = $db->obtener_configuracion($documento['cliente_id']);
        if (!$config) {
            wp_send_json_error('Configuración de cliente no encontrada');
            return;
        }
        
        // Configurar credenciales
        $credentials = array(
            'software_id' => $config['id_software'],
            'software_pin' => $config['software_pin'],
            'company_id' => $config['cliente_id'],
            'company_pin' => isset($config['company_pin']) ? $config['company_pin'] : '',
            'test_set_id' => isset($config['test_set_id']) ? $config['test_set_id'] : ''
        );
        
        // Instanciar webservices
        $webservices = new DIAN_API_WebServices($db, $config['modo_operacion'], $credentials);
        
        // Verificar estado
        $result = $webservices->check_document_status($track_id);
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => 'Estado consultado correctamente',
                'estado' => $result['status']['status'],
                'codigo_estado' => $result['status']['status_code'],
                'descripcion_estado' => $result['status']['status_description'],
                'es_valido' => $result['status']['is_valid'],
                'errores' => $result['status']['errors']
            ));
        } else {
            wp_send_json_error('Error al consultar estado: ' . $result['message']);
        }
    }
}