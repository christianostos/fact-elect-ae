<?php
/**
 * Clase para manejar notificaciones administrativas
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
 * Clase para manejar notificaciones administrativas
 */
class DIAN_API_Notices {

    /**
     * Inicializa los hooks para las notificaciones
     */
    public static function init() {
        add_action('admin_notices', array(__CLASS__, 'display_notices'));
        add_action('wp_ajax_dian_api_dismiss_notice', array(__CLASS__, 'dismiss_notice'));
        
        // Agregar scripts y estilos para las notificaciones
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }
    
    /**
     * Agrega scripts y estilos para las notificaciones
     */
    public static function enqueue_assets($hook) {
        // Solo cargar en las páginas del plugin
        if (strpos($hook, 'dian-api') === false) {
            return;
        }
        
        wp_enqueue_style(
            'dian-api-notices', 
            DIAN_API_URL . 'assets/css/notices.css',
            array(),
            DIAN_API_VERSION
        );
        
        wp_enqueue_script(
            'dian-api-notices',
            DIAN_API_URL . 'assets/js/notices.js',
            array('jquery'),
            DIAN_API_VERSION,
            true
        );
        
        wp_localize_script('dian-api-notices', 'dianApiNotices', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dian-api-notices')
        ));
    }
    
    /**
     * Muestra las notificaciones almacenadas
     */
    public static function display_notices() {
        $notices = self::get_notices();
        
        if (empty($notices)) {
            return;
        }
        
        foreach ($notices as $id => $notice) {
            $class = 'notice notice-' . $notice['type'] . ' dian-api-notice is-dismissible';
            
            if (isset($notice['dismissible']) && $notice['dismissible']) {
                $class .= ' dian-api-dismissible';
            }
            
            printf(
                '<div id="%s" class="%s"><p>%s</p></div>',
                'dian-api-notice-' . esc_attr($id),
                esc_attr($class),
                wp_kses_post($notice['message'])
            );
        }
        
        // Limpiar las notificaciones mostradas
        self::clear_notices();
    }
    
    /**
     * Agrega una notificación para mostrar
     */
    public static function add_notice($message, $type = 'info', $dismissible = true, $id = null) {
        $notices = self::get_notices();
        
        if ($id === null) {
            $id = 'notice_' . time() . '_' . mt_rand(100, 999);
        }
        
        $notices[$id] = array(
            'message' => $message,
            'type' => $type,
            'dismissible' => $dismissible
        );
        
        update_option('dian_api_admin_notices', $notices);
        
        return $id;
    }
    
    /**
     * Obtiene todas las notificaciones almacenadas
     */
    public static function get_notices() {
        return get_option('dian_api_admin_notices', array());
    }
    
    /**
     * Elimina todas las notificaciones
     */
    public static function clear_notices() {
        delete_option('dian_api_admin_notices');
    }
    
    /**
     * Elimina una notificación específica
     */
    public static function remove_notice($id) {
        $notices = self::get_notices();
        
        if (isset($notices[$id])) {
            unset($notices[$id]);
            update_option('dian_api_admin_notices', $notices);
        }
    }
    
    /**
     * Callback para el AJAX de descartar notificación
     */
    public static function dismiss_notice() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dian-api-notices')) {
            wp_send_json_error('Nonce inválido');
        }
        
        // Verificar ID de notificación
        if (!isset($_POST['notice_id'])) {
            wp_send_json_error('ID de notificación no proporcionado');
        }
        
        $notice_id = sanitize_text_field($_POST['notice_id']);
        
        // Remover la notificación
        self::remove_notice($notice_id);
        
        wp_send_json_success();
    }
}