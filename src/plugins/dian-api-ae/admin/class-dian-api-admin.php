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
        add_action('wp_ajax_dian_api_generar_xml_prueba', array($this, 'ajax_generar_xml_prueba'));
        add_action('wp_ajax_dian_api_validar_xml', array($this, 'ajax_validar_xml'));
        add_action('wp_ajax_dian_api_diagnosticar_db', array($this, 'ajax_diagnosticar_db'));
        add_action('wp_ajax_dian_api_reparar_db', array($this, 'ajax_reparar_db'));
        add_action('wp_ajax_dian_api_obtener_resolucion', array($this, 'ajax_obtener_resolucion'));
        add_action('wp_ajax_dian_api_eliminar_resolucion', array($this, 'ajax_eliminar_resolucion'));
        add_action('wp_ajax_dian_api_verificar_certificado', array($this, 'ajax_verificar_certificado'));
        add_action('wp_ajax_dian_api_cargar_certificado', array($this, 'ajax_cargar_certificado'));
        add_action('wp_ajax_dian_api_cambiar_estado_api_key', array($this, 'ajax_cambiar_estado_api_key'));
        add_action('wp_ajax_dian_api_eliminar_cliente', array($this, 'ajax_eliminar_cliente'));
        add_action('wp_ajax_dian_api_eliminar_api_key', array($this, 'ajax_eliminar_api_key'));
        add_action('wp_ajax_dian_api_enviar_documento_test', array($this, 'ajax_enviar_documento_test'));
        add_action('wp_ajax_dian_api_generar_pdf', array($this, 'ajax_generar_pdf'));
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
        // Grupo de opciones de empresa
        register_setting('dian_api_company_options', 'dian_api_company_logo');
        register_setting('dian_api_company_options', 'dian_api_company_name');
        register_setting('dian_api_company_options', 'dian_api_company_nit');
        register_setting('dian_api_company_options', 'dian_api_company_address');
        register_setting('dian_api_company_options', 'dian_api_company_phone');
        register_setting('dian_api_company_options', 'dian_api_company_email');
        register_setting('dian_api_company_options', 'dian_api_company_website');
        
        // Grupo de opciones de PDF
        register_setting('dian_api_pdf_options', 'dian_api_pdf_footer_text');
        register_setting('dian_api_pdf_options', 'dian_api_pdf_primary_color');
        register_setting('dian_api_pdf_options', 'dian_api_pdf_paper_size');
    }

    /**
     * Handler AJAX para reparar la estructura de la base de datos
     *
     * @since    1.0.0
     */
    public function ajax_reparar_db() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        global $wpdb;
        $tabla_configuracion = $wpdb->prefix . 'dian_configuracion';
        
        // Guardar datos existentes antes de recrear la tabla
        $datos_existentes = $wpdb->get_results("SELECT * FROM $tabla_configuracion", ARRAY_A);
        
        // Eliminar la tabla si existe
        $wpdb->query("DROP TABLE IF EXISTS $tabla_configuracion");
        
        // Recrear la tabla con la estructura correcta
        $sql_configuracion = "CREATE TABLE $tabla_configuracion (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cliente_id varchar(100) NOT NULL,
            certificado_ruta text NOT NULL,
            certificado_clave varchar(255) NOT NULL,
            id_software varchar(100) NOT NULL,
            software_pin varchar(100) NOT NULL,
            tecnologia_firma varchar(20) NOT NULL DEFAULT 'sha1',
            url_ws_validacion text NOT NULL,
            url_ws_produccion text NOT NULL,
            modo_operacion varchar(20) NOT NULL DEFAULT 'habilitacion',
            test_set_id varchar(100) NULL,
            fecha_creacion datetime NOT NULL,
            fecha_actualizacion datetime NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY cliente_id (cliente_id)
        ) " . $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_configuracion);
        
        // Restaurar datos si existían
        $restaurados = 0;
        if (!empty($datos_existentes)) {
            foreach ($datos_existentes as $dato) {
                // Asegurarse de que los campos estén completos
                $dato['tecnologia_firma'] = !empty($dato['tecnologia_firma']) ? $dato['tecnologia_firma'] : 'sha1';
                $dato['modo_operacion'] = !empty($dato['modo_operacion']) ? $dato['modo_operacion'] : 'habilitacion';
                
                $resultado = $wpdb->insert($tabla_configuracion, $dato);
                if ($resultado) {
                    $restaurados++;
                }
            }
        }
        
        // Verificar si la tabla se creó correctamente
        $columnas = $wpdb->get_results("SHOW COLUMNS FROM $tabla_configuracion");
        $columnas_esperadas = array(
            'id', 'cliente_id', 'certificado_ruta', 'certificado_clave', 
            'id_software', 'software_pin', 'tecnologia_firma', 
            'url_ws_validacion', 'url_ws_produccion', 'modo_operacion', 
            'test_set_id', 'fecha_creacion', 'fecha_actualizacion'
        );
        
        $faltantes = array();
        foreach ($columnas_esperadas as $esperada) {
            $encontrada = false;
            foreach ($columnas as $columna) {
                if ($columna->Field === $esperada) {
                    $encontrada = true;
                    break;
                }
            }
            if (!$encontrada) {
                $faltantes[] = $esperada;
            }
        }
        
        $mensaje = '';
        if (empty($faltantes)) {
            $mensaje = 'La tabla ha sido reparada correctamente.';
            if ($restaurados > 0) {
                $mensaje .= ' Se restauraron ' . $restaurados . ' registros.';
            }
        } else {
            $mensaje = 'La reparación no fue completamente exitosa. Faltan las siguientes columnas: ' . implode(', ', $faltantes);
        }
        
        wp_send_json_success($mensaje);
    }

    /**
     * Handler AJAX para verificar si existe un certificado
     *
     * @since    1.0.0
     */
    public function ajax_verificar_certificado() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener y sanitizar ruta del certificado
        $certificado_ruta = isset($_POST['certificado_ruta']) ? sanitize_text_field($_POST['certificado_ruta']) : '';
        
        if (empty($certificado_ruta)) {
            wp_send_json_error('Por favor, proporcione una ruta de certificado válida');
            return;
        }
        
        // Verificar si el archivo existe
        if (!file_exists($certificado_ruta)) {
            wp_send_json_error('El archivo del certificado no existe en la ruta especificada. Verifique la ruta y los permisos.');
            return;
        }
        
        // Verificar si es un archivo válido
        if (!is_file($certificado_ruta)) {
            wp_send_json_error('La ruta proporcionada no corresponde a un archivo válido.');
            return;
        }
        
        // Verificar extensión del archivo
        $extension = strtolower(pathinfo($certificado_ruta, PATHINFO_EXTENSION));
        if ($extension !== 'p12' && $extension !== 'pfx') {
            wp_send_json_error('El archivo debe tener extensión .p12 o .pfx (formato PKCS#12).');
            return;
        }
        
        // Verificar si el archivo es legible
        if (!is_readable($certificado_ruta)) {
            wp_send_json_error('El archivo existe pero no es legible. Verifique los permisos.');
            return;
        }
        
        // Intentar verificar el certificado
        $verificacion_adicional = false;
        
        if (function_exists('openssl_pkcs12_read')) {
            $verificacion_adicional = true;
            // No intentamos leer el certificado porque necesitaríamos la contraseña
            wp_send_json_success('Certificado encontrado en la ruta especificada. El formato parece correcto.');
        } else {
            wp_send_json_success('Certificado encontrado en la ruta especificada. No se pudo realizar una verificación completa porque la función OpenSSL no está disponible.');
        }
    }

    /**
     * Handler AJAX para cargar un certificado digital
     *
     * @since    1.0.0
     */
    public function ajax_cargar_certificado() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Verificar si se ha enviado un archivo
        if (!isset($_FILES['certificado']) || $_FILES['certificado']['error'] !== UPLOAD_ERR_OK) {
            $error = isset($_FILES['certificado']) ? $this->get_upload_error_message($_FILES['certificado']['error']) : 'No se envió ningún archivo';
            wp_send_json_error('Error al cargar el archivo: ' . $error);
            return;
        }
        
        // Verificar extensión
        $file_info = pathinfo($_FILES['certificado']['name']);
        $extension = strtolower($file_info['extension']);
        
        if ($extension !== 'p12' && $extension !== 'pfx') {
            wp_send_json_error('Tipo de archivo no válido. Solo se permiten certificados en formato PKCS#12 (.p12 o .pfx)');
            return;
        }
        
        // Crear directorio si no existe
        $upload_dir = DIAN_API_PATH . 'certificados/';
        
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0750, true)) {
                wp_send_json_error('No se pudo crear el directorio para almacenar los certificados');
                return;
            }
            
            // Crear archivo .htaccess para proteger el directorio
            $htaccess_content = "# Denegar acceso a todos los archivos\n";
            $htaccess_content .= "<FilesMatch \".*\">\n";
            $htaccess_content .= "  Order Allow,Deny\n";
            $htaccess_content .= "  Deny from all\n";
            $htaccess_content .= "</FilesMatch>";
            
            file_put_contents($upload_dir . '.htaccess', $htaccess_content);
            
            // Crear archivo index.php vacío
            file_put_contents($upload_dir . 'index.php', "<?php\n// Silencio es oro");
        }
        
        // Generar nombre de archivo único basado en la fecha y un hash
        $filename = 'cert_' . date('Ymd_His') . '_' . substr(md5(rand()), 0, 8) . '.' . $extension;
        $target_path = $upload_dir . $filename;
        
        // Mover el archivo cargado
        if (!move_uploaded_file($_FILES['certificado']['tmp_name'], $target_path)) {
            wp_send_json_error('Error al mover el archivo cargado');
            return;
        }
        
        // Establecer permisos restrictivos
        chmod($target_path, 0640);
        
        wp_send_json_success(array(
            'mensaje' => 'Certificado cargado correctamente',
            'ruta' => $target_path
        ));
    }

    /**
     * Obtiene el mensaje de error para códigos de carga de archivos
     *
     * @since    1.0.0
     * @param    int    $error_code    Código de error de carga
     * @return   string                Mensaje de error descriptivo
     */
    private function get_upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'El archivo excede el tamaño máximo permitido por el servidor';
            case UPLOAD_ERR_FORM_SIZE:
                return 'El archivo excede el tamaño máximo permitido por el formulario';
            case UPLOAD_ERR_PARTIAL:
                return 'El archivo se cargó parcialmente';
            case UPLOAD_ERR_NO_FILE:
                return 'No se seleccionó ningún archivo';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta la carpeta temporal del servidor';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Error al escribir el archivo en el disco';
            case UPLOAD_ERR_EXTENSION:
                return 'Una extensión PHP detuvo la carga del archivo';
            default:
                return 'Error desconocido al cargar el archivo';
        }
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
     * Handler AJAX para diagnosticar la estructura de la base de datos
     *
     * @since    1.0.0
     */
    public function ajax_diagnosticar_db() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        global $wpdb;
        $tabla_configuracion = $wpdb->prefix . 'dian_configuracion';
        
        // Obtener información de la tabla
        $columnas = $wpdb->get_results("SHOW COLUMNS FROM $tabla_configuracion");
        
        $mensaje = "<h3>Estructura de la tabla $tabla_configuracion</h3>";
        $mensaje .= "<table border='1' cellpadding='5'>";
        $mensaje .= "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th></tr>";
        
        foreach ($columnas as $columna) {
            $mensaje .= "<tr>";
            $mensaje .= "<td>{$columna->Field}</td>";
            $mensaje .= "<td>{$columna->Type}</td>";
            $mensaje .= "<td>{$columna->Null}</td>";
            $mensaje .= "<td>{$columna->Key}</td>";
            $mensaje .= "<td>{$columna->Default}</td>";
            $mensaje .= "</tr>";
        }
        
        $mensaje .= "</table>";
        
        // Verificar si falta la columna tecnologia_firma
        $falta_tecnologia_firma = true;
        foreach ($columnas as $columna) {
            if ($columna->Field === 'tecnologia_firma') {
                $falta_tecnologia_firma = false;
                break;
            }
        }
        
        if ($falta_tecnologia_firma) {
            $mensaje .= "<p>La columna 'tecnologia_firma' no existe en la tabla.</p>";
            $mensaje .= "<p>Puedes agregarla ejecutando esta consulta SQL:</p>";
            $mensaje .= "<code>ALTER TABLE $tabla_configuracion ADD COLUMN tecnologia_firma varchar(20) NOT NULL DEFAULT 'sha1' AFTER software_pin;</code>";
        }
        
        wp_send_json_success($mensaje);
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
        
        if (empty($certificado_ruta)) {
            wp_send_json_error('La ruta del certificado es obligatoria. Por favor, ingrese la ruta completa al archivo .p12');
            return;
        }
        if (empty($certificado_clave)) {
            wp_send_json_error('La clave del certificado es obligatoria');
            return;
        }

        // Verificar que el archivo del certificado existe si no estamos en modo de pruebas internas
        if ($modo_operacion !== 'pruebas_internas' && !file_exists($certificado_ruta)) {
            wp_send_json_error('El archivo del certificado no existe en la ruta especificada. Verifique la ruta y los permisos.');
            return;
        }
        
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
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
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
        
        // Verificar ID único de solicitud para prevenir duplicados
        $request_id = isset($_POST['request_id']) ? sanitize_text_field($_POST['request_id']) : '';
        $transient_key = 'dian_api_resolucion_' . md5($request_id);
        
        // Si ya se procesó esta solicitud
        if (!empty($request_id) && get_transient($transient_key)) {
            wp_send_json_success('Resolución procesada correctamente');
            return;
        }
        
        // Verificar si es edición o nueva
        $db = new DIAN_API_DB();
        $es_edicion = false;
        
        if ($id > 0) {
            // Es una edición, verificar si existe
            global $wpdb;
            $tabla_resoluciones = $wpdb->prefix . 'dian_resoluciones';
            $resolucion_existe = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tabla_resoluciones WHERE id = %d", $id));
            
            if ($resolucion_existe == 0) {
                wp_send_json_error('La resolución a editar no existe');
                return;
            }
            
            $es_edicion = true;
        } else {
            // Es nuevo, verificar si ya existe una resolución con los mismos datos
            $resoluciones_existentes = $db->listar_resoluciones($cliente_id);
            
            foreach ($resoluciones_existentes as $resolucion) {
                if ($resolucion['prefijo'] === $prefijo && 
                    $resolucion['numero_resolucion'] === $numero_resolucion &&
                    $resolucion['tipo_documento'] === $tipo_documento) {
                    wp_send_json_error('Ya existe una resolución con este prefijo y número para este tipo de documento');
                    return;
                }
            }
        }
        
        // Datos para guardar
        $datos = array(
            'id' => $id,
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
        $resultado = $db->guardar_resolucion($datos);
        
        if ($resultado) {
            // Guardar el ID de solicitud para prevenir duplicados
            if (!empty($request_id)) {
                set_transient($transient_key, true, 60); // Guarda por 1 minuto
            }
            
            $mensaje = $es_edicion ? 'Resolución actualizada correctamente' : 'Resolución guardada correctamente';
            wp_send_json_success($mensaje);
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
        
        // Verificar ID único de solicitud para prevenir duplicados
        $request_id = isset($_POST['_request_id']) ? sanitize_text_field($_POST['_request_id']) : '';
        $transient_key = 'dian_api_key_' . md5($request_id);
        
        // Si ya se procesó esta solicitud
        if (!empty($request_id) && get_transient($transient_key)) {
            wp_send_json_error('Esta solicitud ya ha sido procesada. Por favor, actualice la página e intente nuevamente.');
            return;
        }
        
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
            // Guardar el ID de solicitud para prevenir duplicados
            if (!empty($request_id)) {
                set_transient($transient_key, true, 60); // Guarda por 1 minuto
            }
            
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
     * Handler AJAX para eliminar una API Key
     *
     * @since    1.0.0
     */
    public function ajax_eliminar_api_key() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            wp_send_json_error('ID de API Key no válido');
        }
        
        // Eliminar de la base de datos
        global $wpdb;
        $tabla = $wpdb->prefix . 'dian_api_keys';
        
        $resultado = $wpdb->delete(
            $tabla,
            array('id' => $id),
            array('%d')
        );
        
        if ($resultado === false) {
            wp_send_json_error('Error al eliminar la API Key');
        } else {
            wp_send_json_success('API Key eliminada correctamente');
        }
    }

    /**
     * Handler AJAX para cambiar el estado de una API Key
     *
     * @since    1.0.0
     */
    public function ajax_cambiar_estado_api_key() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener datos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $estado_actual = isset($_POST['estado_actual']) ? sanitize_text_field($_POST['estado_actual']) : '';
        
        if ($id <= 0) {
            wp_send_json_error('ID de API Key no válido');
        }
        
        // Determinar nuevo estado
        $nuevo_estado = ($estado_actual == 'activo') ? 'inactivo' : 'activo';
        
        // Actualizar en la base de datos
        global $wpdb;
        $tabla = $wpdb->prefix . 'dian_api_keys';
        
        $resultado = $wpdb->update(
            $tabla,
            array('estado' => $nuevo_estado),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        if ($resultado === false) {
            wp_send_json_error('Error al actualizar el estado de la API Key');
        } else {
            wp_send_json_success(array(
                'mensaje' => 'Estado actualizado correctamente',
                'nuevo_estado' => $nuevo_estado,
                'id' => $id
            ));
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
            // Asegurarse de que el track_id existe
            $track_id = !empty($result['track_id']) ? $result['track_id'] : 'SIN_ID_' . uniqid();
            
            // Actualizar estado del documento
            $update_data = array(
                'estado' => 'enviado',
                'track_id' => $track_id,
                'respuesta_dian' => isset($result['response']) ? $result['response'] : '',
                'fecha_actualizacion' => current_time('mysql')
            );
            
            // Primero intentar actualizar por ID del documento si está disponible
            if (!empty($documento['id'])) {
                $updated = $db->actualizar_documento_por_id($documento['id'], $update_data);
            } else {
                $updated = false;
            }
            
            // Si no se pudo actualizar por ID, intentar por track_id
            if (!$updated) {
                $db->actualizar_documento_por_track_id($track_id, $update_data);
                
                // Como respaldo, actualizar también por prefijo y número
                $db->actualizar_documento($cliente_id, $prefijo, $numero, $tipo_documento, $update_data);
            }
            
            // Registrar en el log
            $db->registrar_log(array(
                'cliente_id' => $cliente_id,
                'accion' => 'Enviar documento ' . $tipo_documento . ' ' . $prefijo . $numero,
                'peticion' => 'Envío manual desde panel admin',
                'respuesta' => json_encode($result),
                'codigo_http' => 200
            ));
            
            wp_send_json_success(array(
                'message' => 'Documento enviado correctamente',
                'track_id' => $track_id
            ));
        } else {
            // Registrar el error en el log
            $db->registrar_log(array(
                'cliente_id' => $cliente_id,
                'accion' => 'Error al enviar documento ' . $tipo_documento . ' ' . $prefijo . $numero,
                'peticion' => 'Envío manual desde panel admin',
                'respuesta' => json_encode($result),
                'codigo_http' => 400
            ));
            
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

    /**
     * Handler AJAX para generar XML de prueba
     *
     * @since    1.0.0
     */
    public function ajax_generar_xml_prueba() {
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
        $resolucion_id = isset($_POST['resolucion_id']) ? sanitize_text_field($_POST['resolucion_id']) : '';
        $receptor_tipo = sanitize_text_field($_POST['receptor_tipo']);
        $receptor_documento = sanitize_text_field($_POST['receptor_documento']);
        $receptor_nombre = sanitize_text_field($_POST['receptor_nombre']);
        $receptor_direccion = sanitize_text_field($_POST['receptor_direccion']);
        $num_items = intval($_POST['num_items']);
        $incluir_impuestos = ($_POST['incluir_impuestos'] === 'si');
        $validar_xml = isset($_POST['validar_xml']) && $_POST['validar_xml'] == '1';
        
        // Validaciones básicas
        if (empty($cliente_id)) {
            wp_send_json_error('Debe seleccionar un cliente');
        }
        
        // Obtener datos del cliente
        $db = new DIAN_API_DB();
        $config = $db->obtener_configuracion($cliente_id);
        
        if (!$config) {
            wp_send_json_error('No se encontró la configuración del cliente');
        }
        
        // Obtener resolución
        $resolucion = null;
        if (!empty($resolucion_id)) {
            $resoluciones = $db->listar_resoluciones($cliente_id);
            foreach ($resoluciones as $res) {
                if ($res['id'] == $resolucion_id) {
                    $resolucion = $res;
                    break;
                }
            }
        }
        
        if (!$resolucion) {
            // Usar una resolución genérica
            $resolucion = array(
                'prefijo' => 'TEST',
                'desde_numero' => '1',
                'hasta_numero' => '9999',
                'numero_resolucion' => '18760000001',
                'fecha_resolucion' => date('Y-m-d'),
                'fecha_desde' => date('Y-m-d'),
                'fecha_hasta' => date('Y-m-d', strtotime('+1 year'))
            );
        }
        
        // Generar datos para la factura de prueba
        $invoice_data = array(
            'invoice_number' => $resolucion['prefijo'] . '001',
            'issue_date' => date('Y-m-d'),
            'issue_time' => date('H:i:s'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'note' => 'Esta es una factura de prueba generada por el plugin DIAN API.',
            'supplier' => array(
                'identification_number' => $config['cliente_id'],
                'name' => get_option('dian_api_company_name', 'EMPRESA DE PRUEBA S.A.S.'),
                'party_type' => '1', // 1=Persona Jurídica
                'tax_level_code' => 'O-23',
                'address' => get_option('dian_api_company_address', 'CALLE 123 # 45-67'),
                'city' => '11001',
                'city_name' => 'BOGOTÁ, D.C.',
                'postal_code' => '110111',
                'department' => 'BOGOTÁ, D.C.',
                'department_code' => '11',
                'country_code' => 'CO',
                'country_name' => 'Colombia'
            ),
            'customer' => array(
                'identification_number' => $receptor_documento,
                'name' => $receptor_nombre,
                'party_type' => $receptor_tipo == 'persona_juridica' ? '1' : '2', // 1=Jurídica, 2=Natural
                'id_type' => $receptor_tipo == 'persona_juridica' ? '31' : '13', // 31=NIT, 13=Cédula
                'tax_level_code' => $receptor_tipo == 'persona_juridica' ? 'O-23' : 'R-99-PN',
                'address' => $receptor_direccion,
                'city' => '11001',
                'city_name' => 'BOGOTÁ, D.C.',
                'postal_code' => '110111',
                'department' => 'BOGOTÁ, D.C.',
                'department_code' => '11',
                'country_code' => 'CO',
                'country_name' => 'Colombia'
            ),
            'taxes' => array(),
            'items' => array(),
            'monetary_totals' => array(
                'line_extension_amount' => 0,
                'tax_exclusive_amount' => 0,
                'tax_inclusive_amount' => 0,
                'payable_amount' => 0
            )
        );

        // Asegurarse de que supplier.name tenga un valor válido
        if (empty($invoice_data['supplier']['name'])) {
            $invoice_data['supplier']['name'] = 'EMPRESA DE PRUEBA S.A.S.';
        }

        // Asegurarse de que todos los campos requeridos tengan valores
        $required_fields = array(
            'supplier' => array('identification_number', 'name', 'tax_level_code', 'address', 'city', 'postal_code', 'department', 'country_code'),
            'customer' => array('identification_number', 'name', 'address', 'city', 'postal_code', 'department', 'country_code')
        );

        foreach ($required_fields as $section => $fields) {
            foreach ($fields as $field) {
                if (empty($invoice_data[$section][$field])) {
                    if ($section == 'supplier') {
                        switch ($field) {
                            case 'identification_number':
                                $invoice_data[$section][$field] = $config['cliente_id'];
                                break;
                            case 'name':
                                $invoice_data[$section][$field] = 'EMPRESA DE PRUEBA S.A.S.';
                                break;
                            case 'tax_level_code':
                                $invoice_data[$section][$field] = 'O-23';
                                break;
                            case 'address':
                                $invoice_data[$section][$field] = 'CALLE 123 # 45-67';
                                break;
                            case 'city':
                                $invoice_data[$section][$field] = '11001';
                                break;
                            case 'postal_code':
                                $invoice_data[$section][$field] = '110111';
                                break;
                            case 'department':
                                $invoice_data[$section][$field] = 'BOGOTÁ, D.C.';
                                break;
                            case 'country_code':
                                $invoice_data[$section][$field] = 'CO';
                                break;
                        }
                    } elseif ($section == 'customer') {
                        switch ($field) {
                            case 'identification_number':
                                $invoice_data[$section][$field] = $receptor_documento ?: '1095123456';
                                break;
                            case 'name':
                                $invoice_data[$section][$field] = $receptor_nombre ?: 'CLIENTE DE PRUEBA';
                                break;
                            case 'address':
                                $invoice_data[$section][$field] = $receptor_direccion ?: 'CALLE 123 # 45-67';
                                break;
                            case 'city':
                                $invoice_data[$section][$field] = '11001';
                                break;
                            case 'postal_code':
                                $invoice_data[$section][$field] = '110111';
                                break;
                            case 'department':
                                $invoice_data[$section][$field] = 'BOGOTÁ, D.C.';
                                break;
                            case 'country_code':
                                $invoice_data[$section][$field] = 'CO';
                                break;
                        }
                    }
                }
            }
        }
        
        // Generar items aleatorios
        $productos = array(
            array('code' => 'P001', 'name' => 'Producto de prueba 1', 'price' => 25000),
            array('code' => 'P002', 'name' => 'Producto de prueba 2', 'price' => 35000),
            array('code' => 'P003', 'name' => 'Producto de prueba 3', 'price' => 45000),
            array('code' => 'P004', 'name' => 'Producto de prueba 4', 'price' => 55000),
            array('code' => 'P005', 'name' => 'Producto de prueba 5', 'price' => 65000),
        );
        
        $line_extension_amount = 0;
        
        for ($i = 0; $i < $num_items; $i++) {
            $producto = $productos[$i % count($productos)];
            $quantity = rand(1, 5);
            $unit_price = $producto['price'];
            $line_amount = $quantity * $unit_price;
            $line_extension_amount += $line_amount;
            
            $item = array(
                'description' => $producto['name'],
                'code' => $producto['code'],
                'quantity' => $quantity,
                'unit_code' => 'EA', // EA=Unidad
                'unit_price' => $unit_price,
                'line_extension_amount' => $line_amount,
                'taxes' => array()
            );
            
            // Añadir impuestos si corresponde
            if ($incluir_impuestos) {
                $tax_amount = round($line_amount * 0.19, 2);
                $item['taxes'][] = array(
                    'tax_type' => '01', // 01=IVA
                    'tax_amount' => $tax_amount,
                    'taxable_amount' => $line_amount,
                    'percent' => 19.00
                );
            }
            
            $invoice_data['items'][] = $item;
        }
        
        // Calcular totales
        $invoice_data['monetary_totals']['line_extension_amount'] = $line_extension_amount;
        $invoice_data['monetary_totals']['tax_exclusive_amount'] = $line_extension_amount;
        
        // Añadir impuestos globales si corresponde
        $tax_amount = 0;
        if ($incluir_impuestos) {
            $tax_amount = round($line_extension_amount * 0.19, 2);
            $invoice_data['taxes'][] = array(
                'tax_type' => '01', // 01=IVA
                'tax_amount' => $tax_amount,
                'taxable_amount' => $line_extension_amount,
                'percent' => 19.00
            );
        }
        
        $invoice_data['monetary_totals']['tax_inclusive_amount'] = $line_extension_amount + $tax_amount;
        $invoice_data['monetary_totals']['payable_amount'] = $line_extension_amount + $tax_amount;
        
        // Generar el XML
        $xml_generator = new DIAN_API_XML_Generator($db);
        $result = $xml_generator->generate_invoice_xml($invoice_data);
        
        if (!$result['success']) {
            wp_send_json_error('Error al generar el XML: ' . $result['message']);
        }
        
        $xml = $result['xml'];
        
        // Validar el XML si se solicita
        $validation_result = array(
            'is_valid' => true,
            'errors' => array()
        );
        
        if ($validar_xml) {
            $validation_result = $this->validate_xml_against_ubl($xml);
        }
        
        if ($validar_xml && $validation_result['is_valid']) {
            // Extraer información básica del XML para guardar en la base de datos
            $dom = new DOMDocument();
            $dom->loadXML($xml);
            
            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            
            // Extraer fecha de emisión
            $issue_date_nodes = $xpath->query('//cbc:IssueDate');
            $issue_date = $issue_date_nodes->length > 0 ? $issue_date_nodes->item(0)->nodeValue : date('Y-m-d');
            
            // Extraer fecha de vencimiento
            $due_date_nodes = $xpath->query('//cbc:DueDate');
            $due_date = $due_date_nodes->length > 0 ? $due_date_nodes->item(0)->nodeValue : date('Y-m-d', strtotime('+30 days'));
            
            // Definir el prefijo basado en la resolución
            $prefijo = isset($resolucion['prefijo']) ? $resolucion['prefijo'] : '';
            
            // Crear array con los datos del documento
            $documento_data = array(
                'cliente_id' => $cliente_id, // Usar el cliente_id que ya tenemos
                'tipo_documento' => 'factura',
                'prefijo' => $prefijo,
                'numero' => substr($invoice_data['invoice_number'], strlen($prefijo)),
                'emisor_nit' => $invoice_data['supplier']['identification_number'],
                'emisor_razon_social' => $invoice_data['supplier']['name'],
                'receptor_documento' => $invoice_data['customer']['identification_number'],
                'receptor_razon_social' => $invoice_data['customer']['name'],
                'fecha_emision' => $issue_date . ' ' . $invoice_data['issue_time'],
                'fecha_vencimiento' => $due_date . ' 23:59:59',
                'valor_sin_impuestos' => $invoice_data['monetary_totals']['line_extension_amount'],
                'valor_impuestos' => $invoice_data['monetary_totals']['tax_inclusive_amount'] - $invoice_data['monetary_totals']['line_extension_amount'],
                'valor_total' => $invoice_data['monetary_totals']['payable_amount'],
                'moneda' => 'COP',
                'estado' => 'generado',
                'archivo_xml' => $xml,
                'ambiente' => $config['modo_operacion'] ?? 'habilitacion' // Usar el modo de operación del config
            );
            
            // Guardar el documento
            $resultado_guardado = $db->guardar_documento($documento_data);
            
            if ($resultado_guardado) {
                $validation_result['documento_guardado'] = true;
                $validation_result['documento_id'] = $resultado_guardado;
            } else {
                $validation_result['documento_guardado'] = false;
                $validation_result['error_guardado'] = 'No se pudo guardar el documento en la base de datos.';
            }
        }

        // Devolver resultado
        wp_send_json_success(array(
            'xml' => $xml,
            'validation_result' => $validation_result
        ));
    }

    /**
     * Handler AJAX para validar un XML
     *
     * @since    1.0.0
     */
    public function ajax_validar_xml() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener XML
        $xml_content = isset($_POST['xml_content']) ? $_POST['xml_content'] : '';
        
        if (empty($xml_content)) {
            wp_send_json_error('No se proporcionó contenido XML');
        }
        
        // Validar el XML
        $validation_result = $this->validate_xml_against_ubl($xml_content);
        
        // Devolver resultado
        wp_send_json_success($validation_result);
    }

    /**
     * Valida un XML contra el esquema UBL 2.1
     *
     * @since    1.0.0
     * @param    string    $xml    XML a validar
     * @return   array     Resultado de la validación
     */
    private function validate_xml_against_ubl($xml) {
        // Inicializar resultado
        $result = array(
            'is_valid' => true,
            'errors' => array()
        );
        
        try {
            // Cargar el XML
            $dom = new DOMDocument();
            $dom->loadXML($xml);
            
            // Configurar espacio de nombres correcto para XPath
            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('ubl', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
            $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            
            // 1. Verificar presencia de elementos obligatorios según UBL 2.1
            $required_elements = array(
                '/ubl:Invoice', // Nodo raíz con namespace
                '//cbc:UBLVersionID',
                '//cbc:CustomizationID',
                '//cbc:ProfileID',
                '//cbc:ID',
                '//cbc:IssueDate',
                '//cbc:IssueTime',
                '//cac:AccountingSupplierParty',
                '//cac:AccountingCustomerParty',
                '//cac:LegalMonetaryTotal'
            );
            
            foreach ($required_elements as $element) {
                $nodes = $xpath->query($element);
                
                if ($nodes === false || $nodes->length == 0) {
                    $result['is_valid'] = false;
                    $result['errors'][] = 'Elemento obligatorio no encontrado: ' . $element;
                }
            }
            
            // 2. Verificar que el ID de factura no esté vacío
            $id_nodes = $xpath->query('//cbc:ID');
            
            if ($id_nodes->length > 0) {
                $id_value = $id_nodes->item(0)->nodeValue;
                
                if (empty($id_value)) {
                    $result['is_valid'] = false;
                    $result['errors'][] = 'El ID de la factura no puede estar vacío';
                }
            }
            
            // 3. Verificar fechas
            $issue_date_nodes = $xpath->query('//cbc:IssueDate');
            
            if ($issue_date_nodes->length > 0) {
                $issue_date = $issue_date_nodes->item(0)->nodeValue;
                
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $issue_date)) {
                    $result['is_valid'] = false;
                    $result['errors'][] = 'Formato de fecha de emisión inválido. Debe ser YYYY-MM-DD';
                }
            }
            
            // 4. Verificar numeración
            $invoice_type_code_nodes = $xpath->query('//cbc:InvoiceTypeCode');
            if ($invoice_type_code_nodes->length > 0) {
                $invoice_type_code = $invoice_type_code_nodes->item(0)->nodeValue;
                if (!in_array($invoice_type_code, array('01', '02', '03', '91', '92'))) {
                    $result['is_valid'] = false;
                    $result['errors'][] = 'Código de tipo de factura inválido: ' . $invoice_type_code;
                }
            }
            
            // 5. Verificar moneda
            $currency_nodes = $xpath->query('//cbc:DocumentCurrencyCode');
            if ($currency_nodes->length > 0) {
                $currency = $currency_nodes->item(0)->nodeValue;
                if (empty($currency) || strlen($currency) != 3) {
                    $result['is_valid'] = false;
                    $result['errors'][] = 'Código de moneda inválido: ' . $currency . ' (debe ser un código ISO de 3 letras)';
                }
            }
            
        } catch (Exception $e) {
            $result['is_valid'] = false;
            $result['errors'][] = 'Error al procesar el XML: ' . $e->getMessage();
        }
        
        return $result;
    }

    /**
     * Handler AJAX para enviar documento de prueba a la DIAN
     *
     * @since    1.0.0
     */
    public function ajax_enviar_documento_test() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener ID del documento
        $documento_id = isset($_POST['documento_id']) ? intval($_POST['documento_id']) : 0;
        
        if ($documento_id <= 0) {
            wp_send_json_error('ID de documento no válido');
        }
        
        // Obtener documento
        $db = new DIAN_API_DB();
        $documento = $db->obtener_documento_por_id($documento_id);
        
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
        
        // Asegurarse de que el test_set_id esté configurado
        if (empty($config['test_set_id'])) {
            wp_send_json_error('Test Set ID no configurado para este cliente');
            return;
        }
        
        // Configurar credenciales
        $credentials = array(
            'software_id' => $config['id_software'],
            'software_pin' => $config['software_pin'],
            'company_id' => $config['cliente_id'],
            'company_pin' => isset($config['company_pin']) ? $config['company_pin'] : '',
            'test_set_id' => $config['test_set_id']
        );
        
        // Instanciar webservices
        $webservices = new DIAN_API_WebServices($db, $config['modo_operacion'], $credentials);
        
        // Enviar documento
        $xml_content = $documento['archivo_xml'];
        $result = $webservices->send_document($xml_content, $documento['tipo_documento']);
        
        if ($result['success']) {
            // Actualizar estado del documento
            $update_data = array(
                'estado' => 'enviado',
                'track_id' => $result['track_id'],
                'respuesta_dian' => isset($result['response']) ? $result['response'] : ''
            );
            
            $db->actualizar_documento_por_id($documento_id, $update_data);
            
            wp_send_json_success(array(
                'message' => 'Documento enviado correctamente',
                'track_id' => $result['track_id']
            ));
        } else {
            wp_send_json_error('Error al enviar el documento: ' . $result['message']);
        }
    }

    /**
     * Handler AJAX para generar PDF de un documento
     *
     * @since    1.0.0
     */
    public function ajax_generar_pdf() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener datos del documento
        $documento_id = isset($_POST['documento_id']) ? intval($_POST['documento_id']) : 0;
        
        if ($documento_id <= 0) {
            wp_send_json_error('ID de documento no válido');
        }
        
        // Obtener documento
        $db = new DIAN_API_DB();
        $documento = $db->obtener_documento_por_id($documento_id);
        
        if (!$documento) {
            wp_send_json_error('Documento no encontrado');
            return;
        }
        
        try {
            // Crear directorio para PDFs si no existe
            $pdf_dir = DIAN_API_PATH . 'cache/';
            if (!is_dir($pdf_dir)) {
                wp_mkdir_p($pdf_dir);
            }
            
            // Crear nombre del archivo
            $filename = 'factura_' . $documento['prefijo'] . $documento['numero'] . '_' . time() . '.pdf';
            $pdf_path = $pdf_dir . $filename;
            
            // Preparar datos para la plantilla
            $datos_factura = array(
                'prefijo' => $documento['prefijo'],
                'numero' => $documento['numero'],
                'fecha_emision' => $documento['fecha_emision'],
                'fecha_vencimiento' => $documento['fecha_vencimiento'],
                'emisor_nit' => $documento['emisor_nit'],
                'emisor_razon_social' => $documento['emisor_razon_social'],
                'receptor_documento' => $documento['receptor_documento'],
                'receptor_razon_social' => $documento['receptor_razon_social'],
                'valor_sin_impuestos' => $documento['valor_sin_impuestos'],
                'valor_impuestos' => $documento['valor_impuestos'],
                'valor_total' => $documento['valor_total'],
                'moneda' => $documento['moneda'],
                'cufe' => $documento['cufe'],
                'qr_data' => $documento['qr_data'],
                'ambiente' => $documento['ambiente'],
                'company_name' => get_option('dian_api_company_name', ''),
                'company_nit' => get_option('dian_api_company_nit', ''),
                'company_address' => get_option('dian_api_company_address', ''),
                'company_phone' => get_option('dian_api_company_phone', ''),
                'company_email' => get_option('dian_api_company_email', ''),
                'company_website' => get_option('dian_api_company_website', ''),
                'company_logo' => get_option('dian_api_company_logo', ''),
                'pdf_primary_color' => get_option('dian_api_pdf_primary_color', '#3498db'),
                'pdf_footer_text' => get_option('dian_api_pdf_footer_text', 'Documento generado electrónicamente'),
                'estado' => $documento['estado'],
                'track_id' => $documento['track_id'],
                'respuesta_dian' => $documento['respuesta_dian'],
                'fecha_validacion_dian' => isset($documento['fecha_actualizacion']) ? $documento['fecha_actualizacion'] : null,
                'qr_code' => '' // Inicializar con valor vacío para evitar warnings
            );
            
            // Si tenemos respuesta DIAN, intentar extraer más información
            if (!empty($documento['respuesta_dian'])) {
                $respuesta_json = json_decode($documento['respuesta_dian'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Si hay respuesta parseada correctamente
                    if (isset($respuesta_json['status'])) {
                        $datos_factura['estado_dian'] = $respuesta_json['status'];
                    }
                    if (isset($respuesta_json['status_description'])) {
                        $datos_factura['descripcion_estado'] = $respuesta_json['status_description'];
                    }
                }
            }
            
            // Generar QR si hay datos o usar el CUFE
            if (!empty($documento['qr_data'])) {
                $qr_data = $documento['qr_data'];
            } elseif (!empty($documento['cufe'])) {
                $qr_data = $documento['cufe'];
            } else {
                // Generar datos para el QR basados en información de la factura
                $qr_data = "NumFac: " . $documento['prefijo'] . $documento['numero'] . "\n" .
                        "FecFac: " . date('Y-m-d', strtotime($documento['fecha_emision'])) . "\n" .
                        "NitFac: " . $documento['emisor_nit'] . "\n" .
                        "DocAdq: " . $documento['receptor_documento'] . "\n" .
                        "ValFac: " . number_format($documento['valor_total'], 2, '.', '') . "\n" .
                        "ValIVA: " . number_format($documento['valor_impuestos'], 2, '.', '') . "\n" .
                        "ValTotal: " . number_format($documento['valor_total'], 2, '.', '') . "\n" .
                        "CUFE/CUDE: " . ($documento['cufe'] ?: 'En proceso de validación');
            }
    
            // Si existe qrcode.php, generar imagen para el PDF
            if (file_exists(DIAN_API_PATH . 'lib/phpqrcode/qrlib.php')) {
                require_once DIAN_API_PATH . 'lib/phpqrcode/qrlib.php';
                
                // Crear directorio de cache si no existe
                $cache_dir = DIAN_API_PATH . 'cache/';
                if (!is_dir($cache_dir)) {
                    wp_mkdir_p($cache_dir);
                    chmod($cache_dir, 0755); // Asegurar permisos correctos
                }
                
                $qr_file = $cache_dir . 'qr_' . md5($qr_data) . '.png';
                
                // Generar el QR solo si no existe ya
                if (!file_exists($qr_file) || filesize($qr_file) == 0) {
                    try {
                        \QRcode::png($qr_data, $qr_file, QR_ECLEVEL_L, 3);
                        chmod($qr_file, 0644); // Asegurar permisos para el archivo
                    } catch (Exception $e) {
                        error_log('Error al generar QR: ' . $e->getMessage());
                    }
                }
                
                // Verificar que el archivo se creó correctamente
                if (file_exists($qr_file) && filesize($qr_file) > 0) {
                    $datos_factura['qr_code'] = $qr_file;
                }
            }
            
            // Generar HTML usando la plantilla
            ob_start();
            include DIAN_API_PATH . 'templates/factura-default.php';
            $html = ob_get_clean();
            
            // Cargar TCPDF si no está cargado
            if (!class_exists('TCPDF')) {
                // Intentar cargar desde el plugin
                $tcpdf_path = DIAN_API_PATH . 'vendor/tcpdf/tcpdf.php';
                if (file_exists($tcpdf_path)) {
                    require_once $tcpdf_path;
                } else {
                    // Buscar en otros lugares comunes
                    $alt_paths = array(
                        DIAN_API_PATH . 'lib/tcpdf/tcpdf.php',
                        WP_PLUGIN_DIR . '/tcpdf/tcpdf.php',
                        ABSPATH . 'wp-includes/tcpdf/tcpdf.php'
                    );
                    
                    $loaded = false;
                    foreach ($alt_paths as $path) {
                        if (file_exists($path)) {
                            require_once $path;
                            $loaded = true;
                            break;
                        }
                    }
                    
                    if (!$loaded) {
                        // Intentar usar DomPDF o alguna otra biblioteca de PDF si está disponible
                        if (class_exists('Dompdf\Dompdf')) {
                            $dompdf = new \Dompdf\Dompdf();
                            $dompdf->loadHtml($html);
                            $dompdf->setPaper('A4', 'portrait');
                            $dompdf->render();
                            file_put_contents($pdf_path, $dompdf->output());
                        } else {
                            // Si ninguna biblioteca está disponible, generar un informe detallado
                            wp_send_json_error('No se encontró ninguna biblioteca PDF compatible. Por favor, instale TCPDF o MPDF.');
                            return;
                        }
                    }
                }
            }
            
            // Si TCPDF está disponible, generar PDF con él
            if (class_exists('TCPDF')) {
                $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                
                // Configuración básica
                $pdf->SetCreator($datos_factura['emisor_razon_social']);
                $pdf->SetAuthor($datos_factura['emisor_razon_social']);
                $pdf->SetTitle('Factura Electrónica ' . $datos_factura['prefijo'] . $datos_factura['numero']);
                $pdf->SetMargins(10, 10, 10);
                $pdf->SetAutoPageBreak(true, 10);
                $pdf->setFontSubsetting(true);
                
                // Agregar página
                $pdf->AddPage();
                
                // Agregar contenido HTML
                $pdf->writeHTML($html, true, false, true, false, '');
                
                // Guardar PDF
                $pdf->Output($pdf_path, 'F');
            }
            
            // Crear URL del PDF
            $pdf_dir_url = plugin_dir_url(dirname(__FILE__)) . 'cache/';
            $pdf_url = $pdf_dir_url . $filename;
            
            // Verificar que el archivo se haya creado
            if (!file_exists($pdf_path)) {
                wp_send_json_error('Error: El archivo PDF no se generó correctamente');
                return;
            }
            
            // Información de depuración
            $debug_info = array(
                'documento_id' => $documento_id,
                'pdf_path' => $pdf_path,
                'pdf_exists' => file_exists($pdf_path),
                'pdf_size' => file_exists($pdf_path) ? filesize($pdf_path) : 0,
                'pdf_url' => $pdf_url,
                'tcpdf_loaded' => class_exists('TCPDF'),
                'mpdf_loaded' => class_exists('\Mpdf\Mpdf')
            );
            error_log('Debug PDF: ' . print_r($debug_info, true));
            
            // Devolver respuesta exitosa
            wp_send_json_success(array(
                'pdf_url' => $pdf_url,
                'filename' => 'Factura_' . $documento['prefijo'] . $documento['numero'] . '.pdf',
                'debug' => $debug_info
            ));
        } catch (Exception $e) {
            error_log('Error al generar PDF: ' . $e->getMessage());
            wp_send_json_error('Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Handler AJAX para obtener una resolución
     *
     * @since    1.0.0
     */
    public function ajax_obtener_resolucion() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener ID de resolución
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            wp_send_json_error('ID de resolución no válido');
        }
        
        // Obtener resolución de la base de datos
        global $wpdb;
        $tabla_resoluciones = $wpdb->prefix . 'dian_resoluciones';
        
        $resolucion = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $tabla_resoluciones WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$resolucion) {
            wp_send_json_error('Resolución no encontrada');
        }
        
        wp_send_json_success($resolucion);
    }

    /**
     * Handler AJAX para eliminar una resolución
     *
     * @since    1.0.0
     */
    public function ajax_eliminar_resolucion() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener ID de resolución
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            wp_send_json_error('ID de resolución no válido');
        }
        
        // Eliminar resolución de la base de datos
        global $wpdb;
        $tabla_resoluciones = $wpdb->prefix . 'dian_resoluciones';
        
        $resultado = $wpdb->delete(
            $tabla_resoluciones,
            array('id' => $id),
            array('%d')
        );
        
        if ($resultado === false) {
            wp_send_json_error('Error al eliminar la resolución');
        }
        
        wp_send_json_success('Resolución eliminada correctamente');
    }

    /**
     * Handler AJAX para eliminar un cliente
     *
     * @since    1.0.0
     */
    public function ajax_eliminar_cliente() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian_api_nonce')) {
            wp_send_json_error('Nonce no válido');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        // Obtener ID del cliente
        $cliente_id = isset($_POST['cliente_id']) ? sanitize_text_field($_POST['cliente_id']) : '';
        
        if (empty($cliente_id)) {
            wp_send_json_error('ID de cliente no válido');
        }
        
        // Eliminar de la base de datos
        global $wpdb;
        $tabla = $wpdb->prefix . 'dian_configuracion';
        
        $resultado = $wpdb->delete(
            $tabla,
            array('cliente_id' => $cliente_id),
            array('%s')
        );
        
        if ($resultado === false) {
            wp_send_json_error('Error al eliminar el cliente');
        } else {
            wp_send_json_success('Cliente eliminado correctamente');
        }
    }
}