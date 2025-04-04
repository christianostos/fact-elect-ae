<?php
/**
 * Implementación revisada de API Initial
 */

// Asegurar que esta función solo se registre una vez
remove_action('admin_enqueue_scripts', 'add_scripts_api_initial', 10);
add_action('admin_enqueue_scripts', 'add_scripts_api_initial_revised', 10);

function add_scripts_api_initial_revised($hook) {
    // Opcionalmente, limitar a páginas específicas
    if (strpos($hook, 'facturaloperu-api') === false) {
        return;
    }
    
    // Registrar y encolar el script
    wp_enqueue_script(
        'api-initial-revised',
        plugin_dir_url(__FILE__) . '../../js/api-initial-revised.js',
        array('jquery'),
        time() // Usar timestamp para evitar caché
    );
    
    // Pasar datos al script
    wp_localize_script('api-initial-revised', 'api_script', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('api_initial_nonce')
    ));
    
    // Registrar también el script original para compatibilidad
    wp_enqueue_script(
        'api-initial',
        plugin_dir_url(__FILE__) . '../../js/api-initial.js',
        array('jquery'),
        time()
    );
    
    wp_localize_script('api-initial', 'api_script', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}

// Añadir nuevas acciones de AJAX para garantizar que se registren correctamente
remove_action('wp_ajax_api_service_config_initial', 'api_service_config_initial');
remove_action('wp_ajax_nopriv_api_service_config_initial', 'api_service_config_initial');

add_action('wp_ajax_api_service_config_initial', 'api_service_config_initial_revised');
add_action('wp_ajax_nopriv_api_service_config_initial', 'api_service_config_initial_revised');

/**
 * Función revisada para manejar la solicitud AJAX
 */
function api_service_config_initial_revised() {
    // Verificar permisos
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permisos insuficientes'));
        return;
    }
    
    // PASO 1: Registrar el inicio de la operación
    error_log("[API_INITIAL] Iniciando solicitud de configuración inicial");
    
    // PASO 2: Obtener los parámetros necesarios
    $api_url = get_option('facturaloperu_api_config_url');
    $api_token = get_option('facturaloperu_api_config_token');
    $identification_number = get_option('facturaloperu_api_config_document');
    $type_document_id = get_option('facturaloperu_api_resolution_document_type');
    $prefix = get_option('facturaloperu_api_resolution_prefix');
    $number = get_option('facturaloperu_api_initial_docs');
    
    // PASO 3: Validar parámetros
    $errors = array();
    
    if (empty($api_url)) {
        $errors[] = 'URL de API no configurada';
    }
    
    if (empty($api_token)) {
        $errors[] = 'Token de API no configurado';
    }
    
    if (empty($identification_number)) {
        $errors[] = 'Número de identificación no configurado';
    }
    
    if (empty($type_document_id)) {
        $errors[] = 'Tipo de documento no configurado';
    }
    
    if (empty($prefix)) {
        $errors[] = 'Prefijo no configurado';
    }
    
    if (empty($number)) {
        $errors[] = 'Número inicial no configurado';
    }
    
    if (!empty($errors)) {
        error_log("[API_INITIAL] Errores de validación: " . implode(', ', $errors));
        wp_send_json_error(array(
            'message' => 'Errores de configuración',
            'errors' => $errors
        ));
        return;
    }
    
    // PASO 4: Construir URL del servicio
    $scheme = parse_url($api_url, PHP_URL_SCHEME) ?: 'http';
    $host = parse_url($api_url, PHP_URL_HOST);
    $port = parse_url($api_url, PHP_URL_PORT) ? ':' . parse_url($api_url, PHP_URL_PORT) : '';
    $path = '/api/ubl2.1/config/generateddocuments';
    
    // Asegurarse de que la URL no termine con slash antes de añadir la ruta
    if (substr($api_url, -1) === '/') {
        $service_url = $api_url . ltrim($path, '/');
    } else {
        $service_url = $api_url . $path;
    }
    
    // Alternativa: construir manualmente la URL
    if (!filter_var($service_url, FILTER_VALIDATE_URL)) {
        $service_url = $scheme . '://' . $host . $port . $path;
    }
    
    // PASO 5: Preparar la solicitud
    $json_body = json_encode(array(
        'identification_number' => $identification_number,
        'type_document_id' => $type_document_id,
        'prefix' => $prefix,
        'number' => $number
    ));
    
    $auth_token = 'Bearer ' . $api_token;
    
    // Registrar la solicitud para depuración
    error_log("[API_INITIAL] URL del servicio: " . $service_url);
    error_log("[API_INITIAL] Cuerpo de la solicitud: " . $json_body);
    
    // PASO 6: Enviar la solicitud a la API
    $response = wp_remote_request($service_url, array(
        'method' => 'PUT',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $auth_token
        ),
        'body' => $json_body,
        'cookies' => array(),
        'sslverify' => false
    ));
    
    // PASO 7: Procesar la respuesta
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("[API_INITIAL] Error en la solicitud: " . $error_message);
        wp_send_json_error(array(
            'message' => 'Error en la solicitud: ' . $error_message
        ));
        return;
    }
    
    // PASO 8: Analizar la respuesta
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    error_log("[API_INITIAL] Código de respuesta: " . $response_code);
    error_log("[API_INITIAL] Cuerpo de respuesta: " . substr($response_body, 0, 500) . (strlen($response_body) > 500 ? '...' : ''));
    
    // Intentar decodificar la respuesta JSON
    $json_data = json_decode($response_body, true);
    
    // PASO 9: Preparar la respuesta al cliente
    if ($response_code >= 200 && $response_code < 300) {
        if ($json_data !== null) {
            // Almacenar la respuesta en la opción de WordPress
            update_option('facturaloperu_api_initial_response', $response_body);
            
            // Enviar respuesta exitosa
            wp_send_json_success(array(
                'message' => 'Operación completada correctamente',
                'data' => $json_data
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'La respuesta no es un JSON válido',
                'raw_response' => $response_body
            ));
        }
    } else {
        if ($json_data !== null) {
            wp_send_json_error(array(
                'message' => 'Error en la respuesta del servidor',
                'status' => $response_code,
                'errors' => $json_data
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Error en la respuesta del servidor',
                'status' => $response_code,
                'raw_response' => $response_body
            ));
        }
    }
}

// Función para pruebas
function api_test_connection() {
    check_ajax_referer('api_initial_nonce', 'nonce');
    
    $api_url = get_option('facturaloperu_api_config_url');
    
    if (empty($api_url)) {
        wp_send_json_error(array('message' => 'URL de API no configurada'));
        return;
    }
    
    $response = wp_remote_get($api_url, array(
        'timeout' => 15,
        'sslverify' => false
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error(array(
            'message' => 'Error de conexión: ' . $response->get_error_message()
        ));
        return;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    
    if ($response_code >= 200 && $response_code < 300) {
        wp_send_json_success(array(
            'message' => 'Conexión exitosa',
            'status' => $response_code
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Error de conexión',
            'status' => $response_code
        ));
    }
}
add_action('wp_ajax_api_test_connection', 'api_test_connection');