<?php

function add_scripts_api_resolution() {
    wp_enqueue_script(
        'api-resolution',
        plugin_dir_url( __FILE__ ) . '../../js/api-resolution.js',
        array( 'jquery' ),
        time() // Agregar una marca de tiempo para evitar la caché
    );

    wp_localize_script('api-resolution', 'api_script', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}

add_action('admin_enqueue_scripts', 'add_scripts_api_resolution');
add_action( 'wp_ajax_api_service_config_resolution', 'api_service_config_resolution' );
add_action( 'wp_ajax_nopriv_api_service_config_resolution', 'api_service_config_resolution' );

function api_service_config_resolution() {
    // Log para depuración
    error_log('api_service_config_resolution iniciada');
    
    // Hacer que el tipo sea opcional
    $type = isset($_POST['type']) ? $_POST['type'] : 'resolution';
    
    // Obtener parámetros de configuración
    $api_url = get_option('facturaloperu_api_config_url');
    $api_token = get_option('facturaloperu_api_config_token');
    
    // Validar que los parámetros necesarios existan
    if (empty($api_url)) {
        $response = array(
            'success' => false,
            'message' => 'URL de API no configurada'
        );
        echo json_encode($response);
        wp_die();
    }
    
    if (empty($api_token)) {
        $response = array(
            'success' => false,
            'message' => 'Token de API no configurado'
        );
        echo json_encode($response);
        wp_die();
    }
    
    $api_token = 'Bearer ' . $api_token;
    
    // Construir URL del servicio
    $scheme = parse_url($api_url, PHP_URL_SCHEME) != '' ? parse_url($api_url, PHP_URL_SCHEME) : 'http';
    $host = parse_url($api_url, PHP_URL_HOST);
    $port = parse_url($api_url, PHP_URL_PORT) != '' ? ':' . parse_url($api_url, PHP_URL_PORT) : '';
    $service_url = $scheme . '://' . $host . $port . '/api/ubl2.1/config/resolution';
    
    // Log de la URL del servicio
    error_log('URL del servicio: ' . $service_url);
    
    // Validar datos requeridos
    $type_document_id = get_option('facturaloperu_api_resolution_document_type');
    $resolution = get_option('facturaloperu_api_resolution');
    $prefix = get_option('facturaloperu_api_resolution_prefix');
    
    if (empty($type_document_id) || empty($resolution) || empty($prefix)) {
        $response = array(
            'success' => false,
            'message' => 'Parámetros de resolución incompletos',
            'missing_params' => array(
                'type_document_id' => empty($type_document_id),
                'resolution' => empty($resolution),
                'prefix' => empty($prefix)
            )
        );
        echo json_encode($response);
        wp_die();
    }
    
    // Preparar el cuerpo de la solicitud
    $body_data = array(
        'type_document_id' => $type_document_id,
        'resolution' => $resolution,
        'prefix' => $prefix,
        'resolution_date' => get_option('facturaloperu_api_resolution_date'),
        'technical_key' => get_option('facturaloperu_api_resolution_technical_key'),
        'from' => get_option('facturaloperu_api_resolution_number_from'),
        'to' => get_option('facturaloperu_api_resolution_number_to'),
        'generated_to_date' => get_option('facturaloperu_api_resolution_generated_date'),
        'date_from' => get_option('facturaloperu_api_resolution_date_start'),
        'date_to' => get_option('facturaloperu_api_resolution_date_stop')
    );
    
    $json_body = wp_json_encode($body_data);
    
    // Log del cuerpo de la solicitud
    error_log('Cuerpo de la solicitud: ' . $json_body);
    
    // Realizar la solicitud a la API
    $json_response = wp_remote_request($service_url, array(
        'method' => 'PUT',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'headers' => array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $api_token
        ),
        'sslverify' => false,
        'body' => $json_body
    ));
    
    // Procesar la respuesta
    if (is_wp_error($json_response)) {
        $error_message = $json_response->get_error_message();
        error_log('Error en la solicitud: ' . $error_message);
        
        $response = array(
            'success' => false,
            'message' => 'Error en la solicitud: ' . $error_message
        );
        echo json_encode($response);
    } else {
        $response_code = wp_remote_retrieve_response_code($json_response);
        $response_body = wp_remote_retrieve_body($json_response);
        
        error_log('Código de respuesta: ' . $response_code);
        error_log('Cuerpo de respuesta: ' . $response_body);
        
        // Intentar decodificar la respuesta JSON
        $json_data = json_decode($response_body, true);
        
        if ($response_code >= 200 && $response_code < 300) {
            // Actualizar la opción en WordPress
            update_option('facturaloperu_api_resolution_response', $response_body);
            
            if ($json_data !== null) {
                $response = array(
                    'success' => true,
                    'message' => 'Resolución configurada correctamente',
                    'data' => $json_data
                );
                echo json_encode($response);
            } else {
                $response = array(
                    'success' => true,
                    'message' => 'Respuesta recibida pero no es JSON válido',
                    'raw_response' => $response_body
                );
                echo json_encode($response);
            }
        } else {
            if ($json_data !== null) {
                $response = array(
                    'success' => false,
                    'message' => 'Error en la respuesta del servidor',
                    'status' => $response_code,
                    'errors' => $json_data
                );
                echo json_encode($response);
            } else {
                $response = array(
                    'success' => false,
                    'message' => 'Error en la respuesta del servidor',
                    'status' => $response_code,
                    'raw_response' => $response_body
                );
                echo json_encode($response);
            }
        }
    }
    
    wp_die();
}