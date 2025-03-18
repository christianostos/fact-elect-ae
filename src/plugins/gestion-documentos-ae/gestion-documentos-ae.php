<?php
/**
 * Plugin Name: Gestión de Documentos AE
 * Plugin URI: https://accioneficaz.com/plugins/gestion-documentos-ae
 * Description: Plugin para gestionar carpetas compartidas con usuarios de WordPress.
 * Version: 1.0.0
 * Author: Acción Eficaz
 * Author URI: https://accioneficaz.com
 * Text Domain: gestion-documentos-ae
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// Si este archivo es llamado directamente, abortamos
if (!defined('WPINC')) {
    die;
}

// Definición de constantes
define('GDAE_VERSION', '1.0.0');
define('GDAE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GDAE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GDAE_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('GDAE_PLUGIN_FILE', __FILE__);

function gdae_admin_ajaxurl() {
    echo '<script type="text/javascript">
            var ajaxurl = "' . admin_url('admin-ajax.php') . '";
          </script>';
}
add_action('admin_head', 'gdae_admin_ajaxurl', 5); // Prioridad 5 para asegurar carga temprana

// Activar el plugin
function gdae_activate() {
    // Crear página para mostrar carpetas si no existe
    gdae_create_carpetas_page();
    
    // Reescribir reglas de URL
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'gdae_activate');

// Crear la página para mostrar las carpetas
function gdae_create_carpetas_page() {
    // Comprobar si ya existe la página
    $page = get_page_by_path('mis-carpetas-compartidas');
    
    if (!$page) {
        // Crear la página
        $page_id = wp_insert_post(array(
            'post_title'    => __('Mis Carpetas Compartidas', 'gestion-documentos-ae'),
            'post_content'  => '[gdae_carpetas_usuario]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'mis-carpetas-compartidas'
        ));
        
        // Guardar el ID de la página en las opciones
        update_option('gdae_carpetas_page_id', $page_id);
    }
}

// Desactivar el plugin
function gdae_deactivate() {
    // Limpiar las reglas de reescritura
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'gdae_deactivate');

// Registrar scripts y estilos para el admin
function gdae_admin_scripts($hook) {
    // Cargar estilos y scripts específicos de la página de ajustes
    if ('carpeta-compartir_page_gdae-settings' === $hook) {
        wp_enqueue_media(); // Para el selector de imágenes
        wp_enqueue_style('gdae-settings-css', GDAE_PLUGIN_URL . 'admin/css/gdae-settings.css', array(), GDAE_VERSION);
        wp_enqueue_script('gdae-settings-js', GDAE_PLUGIN_URL . 'admin/js/gdae-settings.js', array('jquery', 'wp-media'), GDAE_VERSION, true);
        return;
    }
    
    // Solo cargar en la pantalla de edición de carpetas
    if ('post.php' != $hook && 'post-new.php' != $hook) {
        return;
    }
    
    global $post;
    if (isset($post) && 'carpeta-compartir' == $post->post_type) {
        wp_enqueue_style('gdae-admin-css', GDAE_PLUGIN_URL . 'admin/css/gdae-admin.css', array(), GDAE_VERSION);
        wp_enqueue_script('gdae-admin-js', GDAE_PLUGIN_URL . 'admin/js/gdae-admin.js', array('jquery'), GDAE_VERSION, true);
        
        // Agregar datepicker para el campo de caducidad
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    }
}
add_action('admin_enqueue_scripts', 'gdae_admin_scripts');

// Registrar scripts y estilos para el frontend
function gdae_public_scripts() {
    wp_enqueue_style('gdae-public-css', GDAE_PLUGIN_URL . 'public/css/gdae-public.css', array(), GDAE_VERSION);
    wp_enqueue_script('gdae-public-js', GDAE_PLUGIN_URL . 'public/js/gdae-public.js', array('jquery'), GDAE_VERSION, true);
    
    // Localizamos el script para AJAX
    wp_localize_script('gdae-public-js', 'gdae_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('gdae-public-nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'gdae_public_scripts');

// Registrar el Custom Post Type: Carpeta Compartir
function gdae_register_post_types() {
    $labels = array(
        'name'                  => _x('Carpetas Compartidas', 'Post type general name', 'gestion-documentos-ae'),
        'singular_name'         => _x('Carpeta Compartida', 'Post type singular name', 'gestion-documentos-ae'),
        'menu_name'             => _x('Carpetas', 'Admin Menu text', 'gestion-documentos-ae'),
        'name_admin_bar'        => _x('Carpeta', 'Add New on Toolbar', 'gestion-documentos-ae'),
        'add_new'               => __('Añadir Nueva', 'gestion-documentos-ae'),
        'add_new_item'          => __('Añadir Nueva Carpeta', 'gestion-documentos-ae'),
        'new_item'              => __('Nueva Carpeta', 'gestion-documentos-ae'),
        'edit_item'             => __('Editar Carpeta', 'gestion-documentos-ae'),
        'view_item'             => __('Ver Carpeta', 'gestion-documentos-ae'),
        'all_items'             => __('Todas las Carpetas', 'gestion-documentos-ae'),
        'search_items'          => __('Buscar Carpetas', 'gestion-documentos-ae'),
        'parent_item_colon'     => __('Carpeta Padre:', 'gestion-documentos-ae'),
        'not_found'             => __('No se encontraron carpetas.', 'gestion-documentos-ae'),
        'not_found_in_trash'    => __('No se encontraron carpetas en la papelera.', 'gestion-documentos-ae'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'carpeta-compartir'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => array('title', 'editor', 'author', 'thumbnail'),
    );

    register_post_type('carpeta-compartir', $args);
}
add_action('init', 'gdae_register_post_types');

// Registrar taxonomía para Categorías de Carpetas
function gdae_register_taxonomies() {
    $labels = array(
        'name'                       => _x('Categorías de Carpetas', 'taxonomy general name', 'gestion-documentos-ae'),
        'singular_name'              => _x('Categoría de Carpeta', 'taxonomy singular name', 'gestion-documentos-ae'),
        'search_items'               => __('Buscar Categorías', 'gestion-documentos-ae'),
        'popular_items'              => __('Categorías Populares', 'gestion-documentos-ae'),
        'all_items'                  => __('Todas las Categorías', 'gestion-documentos-ae'),
        'parent_item'                => __('Categoría Padre', 'gestion-documentos-ae'),
        'parent_item_colon'          => __('Categoría Padre:', 'gestion-documentos-ae'),
        'edit_item'                  => __('Editar Categoría', 'gestion-documentos-ae'),
        'update_item'                => __('Actualizar Categoría', 'gestion-documentos-ae'),
        'add_new_item'               => __('Añadir Nueva Categoría', 'gestion-documentos-ae'),
        'new_item_name'              => __('Nuevo Nombre de Categoría', 'gestion-documentos-ae'),
        'menu_name'                  => __('Categorías', 'gestion-documentos-ae'),
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'categoria-carpeta'),
    );

    register_taxonomy('categoria-carpeta', array('carpeta-compartir'), $args);
}
add_action('init', 'gdae_register_taxonomies');

// Agregar metabox para los campos personalizados de la carpeta
function gdae_add_meta_boxes() {
    add_meta_box(
        'gdae_carpeta_detalles',
        __('Detalles de la Carpeta', 'gestion-documentos-ae'),
        'gdae_render_meta_box',
        'carpeta-compartir',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'gdae_add_meta_boxes');

// Renderizar el metabox
function gdae_render_meta_box($post) {
    // Añadir nonce para verificación
    wp_nonce_field('gdae_save_meta_box_data', 'gdae_meta_box_nonce');
    
    // Recuperar los valores existentes
    $usuario_asignado = get_post_meta($post->ID, '_gdae_usuario_asignado', true);
    $estado = get_post_meta($post->ID, '_gdae_estado', true) ?: 'publicada';
    $fecha_caducidad = get_post_meta($post->ID, '_gdae_fecha_caducidad', true);
    $url_carpeta = get_post_meta($post->ID, '_gdae_url_carpeta', true);
    
    // Obtener todos los usuarios
    $usuarios = get_users(array('role__not_in' => array('administrator')));
    
    // Mostrar el formulario
    ?>
    <div class="gdae-meta-box-container">
        <div class="gdae-meta-field">
            <label for="gdae_usuario_asignado" style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php _e('Usuario Asignado', 'gestion-documentos-ae'); ?> <span class="required">*</span>
            </label>
            <select name="gdae_usuario_asignado" id="gdae_usuario_asignado" class="widefat" required>
                <option value=""><?php _e('Seleccionar un usuario', 'gestion-documentos-ae'); ?></option>
                <?php foreach ($usuarios as $usuario) : ?>
                    <option value="<?php echo esc_attr($usuario->ID); ?>" <?php selected($usuario_asignado, $usuario->ID); ?>>
                        <?php echo esc_html($usuario->display_name) . ' (' . esc_html($usuario->user_email) . ')'; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e('Seleccione el usuario al que se asignará esta carpeta.', 'gestion-documentos-ae'); ?></p>
        </div>

        <div class="gdae-meta-field">
            <label for="gdae_estado" style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php _e('Estado', 'gestion-documentos-ae'); ?> <span class="required">*</span>
            </label>
            <select name="gdae_estado" id="gdae_estado" class="widefat" required>
                <option value="publicada" <?php selected($estado, 'publicada'); ?>><?php _e('Publicada', 'gestion-documentos-ae'); ?></option>
                <option value="contestada" <?php selected($estado, 'contestada'); ?>><?php _e('Contestada', 'gestion-documentos-ae'); ?></option>
            </select>
            <p class="description"><?php _e('Estado actual de la carpeta compartida.', 'gestion-documentos-ae'); ?></p>
        </div>

        <div class="gdae-meta-field">
            <label for="gdae_fecha_caducidad" style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php _e('Fecha de Caducidad', 'gestion-documentos-ae'); ?>
            </label>
            <input type="text" id="gdae_fecha_caducidad" name="gdae_fecha_caducidad" value="<?php echo esc_attr($fecha_caducidad); ?>" class="widefat gdae-datepicker" />
            <p class="description"><?php _e('Fecha en la que caducará el acceso a esta carpeta (formato YYYY-MM-DD).', 'gestion-documentos-ae'); ?></p>
        </div>

        <div class="gdae-meta-field">
            <label for="gdae_url_carpeta" style="display: block; margin-bottom: 5px; font-weight: bold;">
                <?php _e('URL de la Carpeta', 'gestion-documentos-ae'); ?> <span class="required">*</span>
            </label>
            <input type="url" id="gdae_url_carpeta" name="gdae_url_carpeta" value="<?php echo esc_url($url_carpeta); ?>" class="widefat" required />
            <p class="description"><?php _e('URL completa de la carpeta compartida.', 'gestion-documentos-ae'); ?></p>
        </div>
    </div>
    <?php
}

// Guardar los datos del metabox
function gdae_save_meta_box_data($post_id) {
    // Verificar el nonce
    if (!isset($_POST['gdae_meta_box_nonce']) || !wp_verify_nonce($_POST['gdae_meta_box_nonce'], 'gdae_save_meta_box_data')) {
        return;
    }
    
    // Verificar autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Verificar permisos
    if (isset($_POST['post_type']) && 'carpeta-compartir' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }
    
    // Verificar campos requeridos
    if (empty($_POST['gdae_usuario_asignado']) || empty($_POST['gdae_url_carpeta'])) {
        // No guardamos y agregamos un mensaje de error
        add_filter('redirect_post_location', 'gdae_add_notice_query_var', 99);
        return;
    }
    
    // Guardar los datos
    if (isset($_POST['gdae_usuario_asignado'])) {
        update_post_meta($post_id, '_gdae_usuario_asignado', sanitize_text_field($_POST['gdae_usuario_asignado']));
    }
    
    if (isset($_POST['gdae_estado'])) {
        update_post_meta($post_id, '_gdae_estado', sanitize_text_field($_POST['gdae_estado']));
    }
    
    if (isset($_POST['gdae_fecha_caducidad'])) {
        update_post_meta($post_id, '_gdae_fecha_caducidad', sanitize_text_field($_POST['gdae_fecha_caducidad']));
    }
    
    if (isset($_POST['gdae_url_carpeta'])) {
        update_post_meta($post_id, '_gdae_url_carpeta', esc_url_raw($_POST['gdae_url_carpeta']));
    }
}
add_action('save_post', 'gdae_save_meta_box_data');

// Agregar mensaje de error
function gdae_add_notice_query_var($location) {
    remove_filter('redirect_post_location', 'gdae_add_notice_query_var', 99);
    return add_query_arg(array('gdae_error' => 'missing_fields'), $location);
}

// Mostrar mensaje de error
function gdae_admin_notices() {
    if (!isset($_GET['gdae_error'])) {
        return;
    }
    
    if ('missing_fields' === $_GET['gdae_error']) {
        echo '<div class="error"><p>' . __('Error: Faltan campos requeridos. La carpeta no se ha guardado correctamente.', 'gestion-documentos-ae') . '</p></div>';
    }
}
add_action('admin_notices', 'gdae_admin_notices');

// Shortcode para mostrar carpetas asignadas al usuario
function gdae_carpetas_usuario_shortcode($atts) {
    // Si el usuario no está logueado, mostrar mensaje de login
    if (!is_user_logged_in()) {
        return '<div class="gdae-login-required">
                <p>' . __('Debe iniciar sesión para ver sus carpetas compartidas.', 'gestion-documentos-ae') . '</p>
                <a href="' . esc_url(wp_login_url(get_permalink())) . '" class="gdae-button gdae-button--primary">' . 
                __('Iniciar Sesión', 'gestion-documentos-ae') . '</a>
                </div>';
    }
    
    // Obtener el usuario actual
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    
    // Buscar carpetas asignadas al usuario
    $args = array(
        'post_type' => 'carpeta-compartir',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_gdae_usuario_asignado',
                'value' => $user_id,
                'compare' => '='
            )
        )
    );
    
    $carpetas = get_posts($args);
    
    // Incluir la plantilla para mostrar las carpetas
    ob_start();
    include GDAE_PLUGIN_DIR . 'public/partials/gdae-carpetas-usuario.php';
    return ob_get_clean();
}
add_shortcode('gdae_carpetas_usuario', 'gdae_carpetas_usuario_shortcode');

// Manejar la acción AJAX para marcar como contestada
function gdae_marcar_contestada() {
    // Verificar nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gdae-public-nonce')) {
        wp_send_json_error(array('message' => __('Error de seguridad.', 'gestion-documentos-ae')));
    }
    
    // Verificar que el usuario está logueado
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Debe iniciar sesión.', 'gestion-documentos-ae')));
    }
    
    // Verificar ID de carpeta
    if (!isset($_POST['carpeta_id']) || empty($_POST['carpeta_id'])) {
        wp_send_json_error(array('message' => __('ID de carpeta no válido.', 'gestion-documentos-ae')));
    }
    
    $carpeta_id = intval($_POST['carpeta_id']);
    $user_id = get_current_user_id();
    
    // Verificar que la carpeta pertenece al usuario
    $usuario_asignado = get_post_meta($carpeta_id, '_gdae_usuario_asignado', true);
    if ($usuario_asignado != $user_id) {
        wp_send_json_error(array('message' => __('No tiene permisos para esta acción.', 'gestion-documentos-ae')));
    }
    
    // Actualizar estado de la carpeta
    update_post_meta($carpeta_id, '_gdae_estado', 'contestada');
    
    wp_send_json_success(array(
        'message' => __('Carpeta marcada como contestada correctamente.', 'gestion-documentos-ae')
    ));
}
add_action('wp_ajax_gdae_marcar_contestada', 'gdae_marcar_contestada');
add_action('wp_ajax_nopriv_gdae_marcar_contestada', function() {
    wp_send_json_error(array('message' => __('Debe iniciar sesión.', 'gestion-documentos-ae')));
});

// Agregar sección de ajustes en el menú de administración
function gdae_add_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=carpeta-compartir',
        __('Ajustes', 'gestion-documentos-ae'),
        __('Ajustes', 'gestion-documentos-ae'),
        'manage_options',
        'gdae-settings',
        'gdae_settings_page'
    );
}
add_action('admin_menu', 'gdae_add_admin_menu');

// Registrar opciones de ajustes
function gdae_register_settings() {
    register_setting('gdae_settings_group', 'gdae_license_key');
    register_setting('gdae_settings_group', 'gdae_author_name');
    register_setting('gdae_settings_group', 'gdae_author_description');
    register_setting('gdae_settings_group', 'gdae_author_image');
    register_setting('gdae_settings_group', 'gdae_video_url');
    register_setting('gdae_settings_group', 'gdae_support_email');
}
add_action('admin_init', 'gdae_register_settings');

// Renderizar la página de ajustes
function gdae_settings_page() {
    // Incluir la plantilla de ajustes
    include GDAE_PLUGIN_DIR . 'admin/partials/gdae-settings-page.php';
}

// Cargar la clase de licencia
require_once GDAE_PLUGIN_DIR . 'includes/class-gdae-license.php';

// Variable global para la licencia
global $gdae_license;

// Inicializar la licencia - Enganchado a un hook temprano
function gdae_init_license() {
    global $gdae_license;
    if (!isset($gdae_license)) {
        $gdae_license = new GDAE_License();
    }
}
// Usar un hook más temprano que plugins_loaded
add_action('init', 'gdae_init_license', 5); // Prioridad 5 para que se ejecute temprano

// Función auxiliar para obtener la instancia de licencia
function gdae_get_license() {
    global $gdae_license;
    if (!isset($gdae_license)) {
        gdae_init_license();
    }
    return $gdae_license;
}