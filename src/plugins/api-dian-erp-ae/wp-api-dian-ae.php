<?php
/**
 * Plugin Name: API DIAN ERP AE
 * Plugin URI: https://accioneficaz.com
 * Description: Envio de Facturas a la DIAN
 * Version: 2.0.0
 * Author: Acción Eficaz
 * Author URI: https://accioneficaz.com
 * Requires at least: 4.0
 * Tested up to: 5.0.1
 *
 * Text Domain: CO-2023
 * Domain Path: /languages/
 */


defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

/*
 * functions.php
 *
 */
require_once( __DIR__ . '/includes/json-generate.php');
require_once( __DIR__ . '/includes/send-invoice.php');
// require_once( __DIR__ . '/includes/send-option.php');
require_once( __DIR__ . '/includes/front/fields.php');
require_once( __DIR__ . '/includes/admin/api-config.php');
// require_once( __DIR__ . '/includes/query-document.php');
require_once( __DIR__ . '/includes/admin/send/api-company.php');
require_once( __DIR__ . '/includes/admin/send/api-software.php');
require_once( __DIR__ . '/includes/admin/send/api-certificate.php');
require_once( __DIR__ . '/includes/admin/send/api-resolution.php');
require_once( __DIR__ . '/includes/admin/send/api-initial.php');
require_once( __DIR__ . '/includes/admin/send/api-environment.php');
require_once( __DIR__ . '/includes/admin/send/api-numbering-ranges.php');

function register_dian_admin_assets() {
    // Registrar el CSS
    wp_register_style(
        'dian-admin-styles',
        plugins_url('assets/css/admin-styles.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/admin-styles.css')
    );
    
    // Registrar el JS
    wp_register_script(
        'dian-admin-script',
        plugins_url('assets/js/admin-script.js', __FILE__),
        array('jquery'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/admin-script.js'),
        true
    );
}
add_action('init', 'register_dian_admin_assets');