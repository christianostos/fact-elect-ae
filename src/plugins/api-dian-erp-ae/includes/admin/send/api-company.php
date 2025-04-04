<?php
function add_scripts_api_queries() {
    wp_enqueue_script(
        'api-script',
        plugin_dir_url( __FILE__ ) . '../../js/api-queries.js',
        array( 'jquery' )
    );

    wp_localize_script('api-script', 'api_script_object', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}

add_action('admin_enqueue_scripts', 'add_scripts_api_queries');
add_action( 'wp_ajax_api_service_config_company', 'api_service_config_company' );
add_action( 'wp_ajax_nopriv_api_service_config_company', 'api_service_config_company' );

function api_service_config_company() {
    // Verificar que tengamos los datos necesarios
    $dv = get_option('facturaloperu_api_config_dv');
    $api_url = get_option('facturaloperu_api_config_url');
    $document = get_option('facturaloperu_api_config_document');

    if (empty($api_url) || empty($document)) {
        wp_send_json(array(
            'success' => false,
            'message' => 'Faltan datos de configuración (URL API o documento)'
        ));
        wp_die();
    }

    // Asegurar que la URL tenga el formato correcto
    if (substr($api_url, -1) != '/') {
        $api_url .= '/';
    }

    // Construir la URL completa para el servicio
    $service_url = $api_url . 'api/ubl2.1/config/' . $document . '/' . $dv;

    // Construir el cuerpo de la solicitud
    $body = wp_json_encode([
        "type_document_identification_id" => get_option('facturaloperu_api_config_document_type'),
        "type_organization_id" => get_option('facturaloperu_api_config_organization_type'),
        "type_regime_id" => get_option('facturaloperu_api_config_regime_type'),
        "type_liability_id" => get_option('facturaloperu_api_config_liability_type'),
        "business_name" => get_option('facturaloperu_api_config_business_name'),
        "merchant_registration" => get_option('facturaloperu_api_config_merchant_registration'),
        "municipality_id" => get_option('facturaloperu_api_config_municipality'),
        "address" => get_option('facturaloperu_api_config_business_address'),
        "phone" => get_option('facturaloperu_api_config_business_phone'),
        "email" => get_option('facturaloperu_api_config_business_email')
    ]);

    // Registrar información para depuración
    error_log("Enviando solicitud a: " . $service_url);
    error_log("Cuerpo de la solicitud: " . $body);

    // Enviar a la API
    $response = wp_remote_post($service_url, array(
        'method' => 'POST',
        'headers' => array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ),
        'sslverify' => false,
        'body' => $body,
        'timeout' => 30 // Aumentar el tiempo de espera para evitar problemas de conexión
    ));

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        wp_send_json(array(
            'success' => false,
            'message' => "Error de conexión: " . $error_message
        ));
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        // Registrar la respuesta para depuración
        error_log("Código de respuesta HTTP: " . $response_code);
        error_log("Cuerpo de la respuesta: " . $response_body);
        
        // Intentar decodificar la respuesta JSON
        $json_response = json_decode($response_body, true);
        
        if ($response_code >= 200 && $response_code < 300 && $json_response) {
            // Añadir 'success' => true si la API no lo incluye
            if (!isset($json_response['success'])) {
                $json_response['success'] = true;
            }
            
            // Actualizar el token si está presente
            if (isset($json_response['token'])) {
                update_option('facturaloperu_api_config_token', $json_response['token']);
            }
            
            wp_send_json($json_response);
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