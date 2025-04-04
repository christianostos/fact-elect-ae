<?php

function add_scripts_api_numbering_ranges() {
    wp_enqueue_script(
        'api-numbering-ranges',
        plugin_dir_url( __FILE__ ) . '../../js/api-numbering-ranges.js',
        array( 'jquery' )
    );

    wp_localize_script('api-numbering-ranges', 'api_script', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}

add_action('admin_enqueue_scripts', 'add_scripts_api_numbering_ranges');
add_action( 'wp_ajax_api_service_config_numbering_ranges', 'api_service_config_numbering_ranges' );
add_action( 'wp_ajax_nopriv_api_service_config_numbering_ranges', 'api_service_config_numbering_ranges' );

function api_service_config_numbering_ranges() {
    $type = $_POST['type'];

    $api_url = get_option('facturaloperu_api_config_url');
    $api_token = 'Bearer '.get_option('facturaloperu_api_config_token');

    $scheme = parse_url($api_url, PHP_URL_SCHEME) != '' ? parse_url($api_url, PHP_URL_SCHEME) : 'http';
    $host = parse_url($api_url, PHP_URL_HOST);
    $port = parse_url($api_url, PHP_URL_PORT) != '' ? ':' . parse_url($api_url, PHP_URL_PORT) : '';
    $service_url = $scheme . '://' . $host . $port . '/api/ubl2.1/numbering-range';

    $json_body = wp_json_encode([
        'IDSoftware' => get_option('facturaloperu_api_software_id')
    ]);

    // ENVIO A LA API
    $json_response = wp_remote_request( $service_url, array(
        'method' => 'POST',
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