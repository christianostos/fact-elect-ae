<?php

function add_scripts_api_software() {
    wp_enqueue_script(
        'api-software',
        plugin_dir_url( __FILE__ ) . '../../js/api-software.js',
        array( 'jquery' )
    );

    wp_localize_script('api-software', 'api_script', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}

add_action('admin_enqueue_scripts', 'add_scripts_api_software');
add_action( 'wp_ajax_api_service_config_software', 'api_service_config_software' );
add_action( 'wp_ajax_nopriv_api_service_config_software', 'api_service_config_software' );

function api_service_config_software() {
    $type = $_POST['type'];

    $api_url = get_option('facturaloperu_api_config_url');
    $api_token = 'Bearer '.get_option('facturaloperu_api_config_token');

    $scheme = parse_url($api_url, PHP_URL_SCHEME) != '' ? parse_url($api_url, PHP_URL_SCHEME) : 'http';
    $host = parse_url($api_url, PHP_URL_HOST);
    $port = parse_url($api_url, PHP_URL_PORT) != '' ? ':' . parse_url($api_url, PHP_URL_PORT) : '';
    $service_url = $scheme . '://' . $host . $port . '/api/ubl2.1/config/software';

    $json_body = wp_json_encode([
        "id" => get_option('facturaloperu_api_software_id'),
        "pin" => get_option('facturaloperu_api_software_pin')
    ]);

    // ENVIO A LA API
    $json_response = wp_remote_request( $service_url, array(
        'method' => 'PUT',
        'headers' => array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $api_token
        ),
        'sslverify' => false,
        'body' => $json_body
    ));

    if ( is_wp_error( $json_response ) ) {
        $error_message = $json_response->get_error_message();
        echo "Something went wrong: $error_message";
    } else {
        echo $json_response;
        $json = wp_remote_retrieve_body( $json_response );
        echo $json;
    }

    wp_die();
}