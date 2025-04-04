<?php

function add_scripts_api_certificate() {
    // Enqueue el script JavaScript actualizado
    wp_enqueue_script(
        'api-certificate',
        plugin_dir_url( __FILE__ ) . '../../js/api-certificate.js',
        array( 'jquery' )
    );

    // Añadir icono de Dashicons para el botón de carga
    wp_enqueue_style('dashicons');

    wp_localize_script('api-certificate', 'api_script', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}

add_action('admin_enqueue_scripts', 'add_scripts_api_certificate');
add_action('wp_ajax_api_service_config_certificate', 'api_service_config_certificate');
add_action('wp_ajax_nopriv_api_service_config_certificate', 'api_service_config_certificate');

function api_service_config_certificate() {
    // Verificar que tengamos los datos necesarios
    $certificate = get_option('facturaloperu_api_certificate');
    $password = get_option('facturaloperu_api_certificate_password');
    $api_url = get_option('facturaloperu_api_config_url');
    $api_token = get_option('facturaloperu_api_config_token');

    if (empty($certificate) || empty($password) || empty($api_url)) {
        wp_send_json(array(
            'success' => false,
            'message' => 'Faltan datos de configuración (certificado, contraseña o URL API)'
        ));
        wp_die();
    }

    // Asegurar que la URL tenga el formato correcto
    if (substr($api_url, -1) != '/') {
        $api_url .= '/';
    }

    // Asegurar que la URL tenga el esquema correcto
    if (!preg_match('/^https?:\/\//', $api_url)) {
        $api_url = 'https://' . $api_url;
    }

    // Construir la URL completa para el servicio
    $service_url = $api_url . 'api/ubl2.1/config/certificate';

    // Preparar el token de autorización
    $api_token = 'Bearer ' . $api_token;

    // Construir el cuerpo de la solicitud
    $json_body = wp_json_encode([
        "certificate" => $certificate,
        "password" => $password
    ]);

    // Registrar información para depuración
    error_log("Enviando solicitud a: " . $service_url);

    // ENVIO A LA API
    $json_response = wp_remote_request($service_url, array(
        'method' => 'PUT',
        'headers' => array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $api_token
        ),
        'sslverify' => false,
        'body' => $json_body,
        'timeout' => 30 // Aumentar el tiempo de espera para evitar problemas de conexión
    ));

    if (is_wp_error($json_response)) {
        $error_message = $json_response->get_error_message();
        wp_send_json(array(
            'success' => false,
            'message' => "Error de conexión: " . $error_message
        ));
    } else {
        $response_code = wp_remote_retrieve_response_code($json_response);
        $response_body = wp_remote_retrieve_body($json_response);
        
        // Registrar la respuesta para depuración
        error_log("Código de respuesta HTTP: " . $response_code);
        error_log("Cuerpo de la respuesta: " . $response_body);
        
        // Intentar decodificar la respuesta JSON
        $response_data = json_decode($response_body, true);
        
        if ($response_code >= 200 && $response_code < 300 && $response_data) {
            // Añadir 'success' => true si la API no lo incluye
            if (!isset($response_data['success'])) {
                $response_data['success'] = true;
            }
            
            wp_send_json($response_data);
        } else {
            // Devolver error con información específica
            wp_send_json(array(
                'success' => false,
                'message' => "Error en la respuesta (HTTP $response_code)",
                'response' => $response_body
            ));
        }
    }

    wp_die();
}