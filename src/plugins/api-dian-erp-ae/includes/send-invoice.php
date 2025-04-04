<?php

add_action( 'add_meta_boxes', 'ep_add_meta_boxes' );
if ( ! function_exists( 'ep_add_meta_boxes' ) )
{
    function ep_add_meta_boxes()
    {
        add_meta_box( 'ep_sunat_fields', 'DIAN', 'ep_add_sunat_fields', 'shop_order');
    }
}

// accion de boton de envio
function add_scripts_action_button_send() {
    global $post;
    wp_enqueue_script(
        'action_button_send',
        plugin_dir_url( __FILE__ ) . './js/action_button_send.js',
        array( 'jquery' )
    );

    wp_localize_script('action_button_send', 'api_script', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('admin_enqueue_scripts', 'add_scripts_action_button_send');

function generateJson($id) {
    global $post;

    $order = wc_get_order( $id );

    $is_production = get_option('facturaloperu_api_config_production');
    $resolution_number = get_option('facturaloperu_api_resolution');
    $send_email = get_option('facturaloperu_api_config_send_email');

    // DATA CUSTOMER
    $customer_first_name = get_post_meta( $id, '_billing_first_name', true );
    $customer_last_name  = get_post_meta( $id, '_billing_last_name', true );
    $customer_email      = get_post_meta( $id, '_billing_email', true );
    $customer_company    = get_post_meta( $id, '_billing_company', true );
    $customer_address    = get_post_meta( $id, '_billing_address_1', true );
    $customer_phone      = get_post_meta( $id, '_billing_phone', true );
    $customer_city       = get_post_meta( $id, '_billing_city', true );
    $customer_state      = get_post_meta( $id, '_billing_state', true );
    $customer_postcode   = get_post_meta( $id, '_billing_postcode', true );
    $customer_number     = get_post_meta( $id, '_billing_nif', true );
    $customer_type_doc   = get_post_meta( $id, 'type_document_identification', true );
    $customer_document_dv = get_post_meta( $id, 'document_dv', true );
    $customer_type_organization   = get_post_meta( $id, 'type_organization', true );
    $customer_type_regime   = get_post_meta( $id, 'type_regime', true );
    $merchant_registration = get_post_meta( $id, 'merchant_registration', true );
    $is_number_suffix = get_post_meta( $id, 'is_number_suffix', true );
    $number_suffix = get_post_meta( $id, 'number_suffix', true );

    // -------- INICIO DE CAMBIOS --------
    // Obtener rangos de resolución
    $resolution_number_from = intval(get_option('facturaloperu_api_resolution_number_from', '990000000'));
    $resolution_number_to = intval(get_option('facturaloperu_api_resolution_number_to', '995000000'));
    $resolution_number_current = intval(get_option('facturaloperu_api_resolution_number_current', $resolution_number_from));
    
    // Registrar valores para depuración
    error_log("DIAN Debug - Valores iniciales:");
    error_log("- ID de pedido: $id");
    error_log("- Rango de resolución: $resolution_number_from - $resolution_number_to");
    error_log("- Número actual: $resolution_number_current");
    error_log("- is_number_suffix: $is_number_suffix");
    error_log("- number_suffix: $number_suffix");
    
    // Validar y asignar número de factura
    if($is_number_suffix != '1' || empty($number_suffix)) {
        // Si el pedido aún no tiene un número asignado
        
        // Asegurarse de que el número actual está dentro del rango
        if($resolution_number_current < $resolution_number_from || $resolution_number_current > $resolution_number_to) {
            error_log("DIAN Error: Número actual fuera de rango, reiniciando a $resolution_number_from");
            $resolution_number_current = $resolution_number_from;
        }
        
        // Asignar el número actual al pedido
        $number_suffix = $resolution_number_current;
        
        // Actualizar metadatos del pedido
        update_post_meta($id, 'is_number_suffix', '1');
        update_post_meta($id, 'number_suffix', $number_suffix);
        
        // Incrementar el contador para el próximo pedido
        $next_number = $resolution_number_current + 1;
        if($next_number <= $resolution_number_to) {
            update_option('facturaloperu_api_resolution_number_current', $next_number);
        } else {
            error_log("DIAN Advertencia: Se ha alcanzado el límite del rango de resolución");
        }
        
        error_log("DIAN Info: Número asignado al pedido $id: $number_suffix");
    } else {
        // El pedido ya tiene un número asignado
        $number_suffix = intval($number_suffix);
        
        // Verificar que el número esté en el rango correcto
        if($number_suffix < $resolution_number_from || $number_suffix > $resolution_number_to) {
            error_log("DIAN Error: Número de pedido $id fuera de rango ($number_suffix). Corrigiendo...");
            $number_suffix = $resolution_number_current;
            update_post_meta($id, 'number_suffix', $number_suffix);
            
            // Incrementar para el próximo uso
            $next_number = $resolution_number_current + 1;
            if($next_number <= $resolution_number_to) {
                update_option('facturaloperu_api_resolution_number_current', $next_number);
            }
        }
        
        error_log("DIAN Info: Usando número existente para pedido $id: $number_suffix");
    }

    /*
     * DATA PAYMENT
     *
     * payment_form_id : [
     *    1: contado
     *    2: credito
     * ]
     * payment_method_id : [
     *    31: transferencia debito
     *    30: transferencia credito
     *    20: cheque
     *    10: efectivo
     *    1: instrumento no definido
     * ]
     * payment_due_date
     * duration_measure
     */

    $payment_form_id = 1;
    $payment_method_id = '';
    $payment_due_date = date("Y-m-d", strtotime($order->get_date_created()));
    $duration_measure = 0;
    switch ($order->payment_method) {
        case 'bacs':
            $payment_method_id = 31;
            break;
        case 'cheque':
            $payment_method_id = 20;
        case 'cod':
            $payment_method_id = 10;
        default:
            $payment_method_id = 1;
            break;
    }

    $items = [];
    foreach ($order->get_items() as $item_id => $item_data) {
        $quantity = $item_data->get_quantity(); // total quantity
        $subtotal = $item_data->get_subtotal(); // total line without tax
        $total = $item_data->get_total(); // total line without tax
        $product = $item_data->get_product();
        $product_name = $product->get_name();
        
        // Cálculo correcto de impuestos
        $tax_rate = 19.0; // Tasa de IVA en Colombia (%)
        $tax_decimal = $tax_rate / 100;
        
        // Calcular el impuesto sobre la base imponible
        $taxable_amount = round($subtotal, 2);
        $tax_amount = round($taxable_amount * $tax_decimal, 2);
        
        // Asegurarse de que los valores tengan la precisión correcta
        $taxable_amount_str = number_format($taxable_amount, 2, '.', '');
        $tax_amount_str = number_format($tax_amount, 2, '.', '');
        $unit_price = number_format(($total + $tax_amount) / $quantity, 2, '.', '');
        
        $item = [
            "code" => "2",
            "unit_measure_id" => 70,
            "free_of_charge_indicator" => false,
            "type_item_identification_id" => 4,
            "description" => $product_name,
            "invoiced_quantity" => (string)($quantity),
            "base_quantity" => (string)($quantity),
            "line_extension_amount" => $taxable_amount_str,
            "price_amount" => $unit_price,
            "tax_totals" => array(
                array(
                    "tax_id" => 1,
                    "tax_amount" => $tax_amount_str,
                    "taxable_amount" => $taxable_amount_str,
                    "percent" => number_format($tax_rate, 1)
                )
            ),
            "allowance_charges" => array(
                array(
                    "charge_indicator" => false,
                    "allowance_charge_reason" => "DESCUENTOGENERAL",
                    "amount" => "0.00",
                    "base_amount" => $taxable_amount_str
                )
            ),
        ];
    
        $items[] = $item;
    }

    // 2. Si hay envío, modificar también el cálculo de impuestos para el envío:
    if($order->shipping_total > 0){
        $shipping_total = round($order->shipping_total, 2);
        $shipping_tax_rate = 19.0;
        $shipping_tax_decimal = $shipping_tax_rate / 100;
        
        // Calcular el impuesto sobre el envío
        $shipping_tax = round($shipping_total * $shipping_tax_decimal, 2);
        
        // Formatear valores con precisión
        $shipping_total_str = number_format($shipping_total, 2, '.', '');
        $shipping_tax_str = number_format($shipping_tax, 2, '.', '');
        $shipping_price = number_format($shipping_total + $shipping_tax, 2, '.', '');
        
        $item = [
            "code" => "shipping_wp",
            "unit_measure_id" => 70,
            "invoiced_quantity" => "1",
            "free_of_charge_indicator" => false,
            "type_item_identification_id" => 4,
            "description" => "ENVIO",
            "base_quantity" => "1",
            "line_extension_amount" => $shipping_total_str,
            "price_amount" => $shipping_price,
            "tax_totals" => array(
                array(
                    "tax_id" => 1,
                    "tax_amount" => $shipping_tax_str,
                    "taxable_amount" => $shipping_total_str,
                    "percent" => number_format($shipping_tax_rate, 2)
                )
            )
        ];
        $items[] = $item;
    }
    
    // 3. Actualizar también los totales generales para que sean consistentes
    $total_taxable = round($order->total - $order->total_tax, 2);
    $total_tax = round($order->total_tax, 2);
    
    // Si el impuesto es cero pero hay un monto imponible, calcular el impuesto correctamente
    if ($total_tax == 0 && $total_taxable > 0) {
        $tax_rate = 19.0;
        $total_tax = round($total_taxable * ($tax_rate / 100), 2);
    }
    
    $total_with_tax = $total_taxable + $total_tax;
    
    // 4. En la creación del array principal, asegúrate de que los valores sean consistentes:
    
    $array = array(
        "number" => $number_suffix,
        "type_document_id" => 1,
        "resolution_number" => $resolution_number,
        "date" => $current_date, // Usa la fecha actual, no la del pedido
        "time" => $current_time, // Usa la hora actual, no la del pedido
        "sendmail" => filter_var($send_email, FILTER_VALIDATE_BOOLEAN),
        "customer" => array(
            "identification_number" => intval($customer_number),
            "dv" => $customer_document_dv > 1 ? intval($customer_document_dv) : null,
            "name" => $customer_company,
            "phone" => $customer_phone ? $customer_phone : '9999999',
            "address" => $customer_address,
            "email" => $customer_email != '' ? $customer_email : '',
            "merchant_registration" => $merchant_registration != '' ? $merchant_registration : '0000000000',
            "type_document_identification_id" => intval($customer_type_doc),
            "type_organization_id" => intval($customer_type_organization), // 1juridica 2natural
            "municipality_id" => $customer_postcode != '' ? intval($customer_postcode)  : '',
            "type_regime_id" => intval($customer_type_regime)
        ),
        "payment_form" => array(
            "payment_form_id" => $payment_form_id,
            "payment_method_id" => $payment_method_id,
            "payment_due_date" => $payment_due_date,
            "duration_measure" => $duration_measure
        ),
        "allowance_charges" => array(
            array(
                "discount_id" => 1,
                "charge_indicator" => false,
                "allowance_charge_reason" => "DESCUENTO GENERAL",
                "amount" => "0.00",
                "base_amount" => number_format($total_taxable, 2, '.', '')
            )
        ),
        "legal_monetary_totals" => array(
            "line_extension_amount" => number_format($total_taxable, 2, '.', ''),
            "tax_exclusive_amount" => number_format($total_taxable, 2, '.', ''),
            "tax_inclusive_amount" => number_format($total_with_tax, 2, '.', ''),
            "allowance_total_amount" => "0.00",
            "charge_total_amount" => "0.00",
            "payable_amount" => number_format($total_with_tax, 2, '.', '')
        ),
        "tax_totals" => array(
            array(
                "tax_id" => 1,
                "tax_amount" => number_format($total_tax, 2, '.', ''),
                "percent" => "19.0",
                "taxable_amount" => number_format($total_taxable, 2, '.', '')
            )
        ),
        "invoice_lines" => $items
    );

    if($is_production){
        $resolution_prefix = get_option('facturaloperu_api_resolution_prefix');
        $array['prefix'] = $resolution_prefix;
    }

    $json_invoice = json_encode($array, JSON_PRETTY_PRINT);
    update_post_meta( $order->id, 'json_invoice', $json_invoice);
    
    // Registrar el JSON final para depuración
    error_log("DIAN JSON generado para pedido $id: " . substr($json_invoice, 0, 200) . "...");
    
    return $json_invoice;
}

// envio a api
function ep_perform_api_consultation($post_id) {
    // obtengo el json del campo guardado
    generateJson($post_id);
    $json_invoice = get_post_meta( $post_id, 'json_invoice', true );

    // ENVIO A LA API
    $is_production = get_option('facturaloperu_api_config_production');
    $testSetId = get_option('facturaloperu_api_config_testsetid');
    $api_url = get_option('facturaloperu_api_config_url');
    $api_token = 'Bearer '.get_option('facturaloperu_api_config_token');

    $scheme = parse_url($api_url, PHP_URL_SCHEME) != '' ? parse_url($api_url, PHP_URL_SCHEME) : 'http';
    $host = parse_url($api_url, PHP_URL_HOST);
    $port = parse_url($api_url, PHP_URL_PORT) != '' ? ':' . parse_url($api_url, PHP_URL_PORT) : '';
    $service_url = $scheme . '://' . $host . $port . '/api/ubl2.1/invoice';

    // si no esta en produccion y existe el id de pruebas se añade el id
    if(!$is_production && isset($testSetId)) {
        $service_url = $service_url.'/'.$testSetId;
    }

    $response = wp_remote_post( $service_url, array(
        'method' => 'POST',
        'headers' => array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'timeout' => 15,
            'Authorization' => $api_token
        ),
        'sslverify' => false,
        'body' => $json_invoice,
    ));

    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        wp_send_json_error(array('message' => $error_message));
        echo "Something went wrong: $error_message";
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode($body, true);

    $error = '';
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = 'Error al decodificar la respuesta de la API.';
    }

    // Verifica si la respuesta contiene el dato sunat_api_response
    $success = false;
    if (isset($data)) {
        // Actualiza el metadato del post
        update_post_meta($post_id, '_ep_sunat_api_xml', wp_slash(json_encode($data, JSON_UNESCAPED_UNICODE)));
        $success = true;
    }

    // Suponiendo que la respuesta es correcta y contiene un mensaje
    return [
        'data' => $data,
        'success' => $success,
        'error' => $error
    ];
}

/**
 * Función mejorada para validar si la respuesta de DIAN es exitosa
 *
 * @param array $response La respuesta de la API DIAN
 * @return boolean Verdadero si la factura fue aceptada, falso en caso contrario
 */
function validateResponse($response) {
    // 1. Verificar mensajes de error estándar
    $error_messages = [
        "Trying to get property 'resolutions' of non-object",
        "No se encontró la URL especificada",
        "Server Error",
        "The given data was invalid.",
        "ErrorException"
    ];

    // Por defecto asumimos que es válida (exitosa)
    $is_accepted = true;
    
    // 2. Verificar por condiciones estándar de error
    if (isset($response['message']) && in_array($response['message'], $error_messages)) {
        $is_accepted = false;
    }
    if (isset($response['success']) && $response['success'] === false) {
        $is_accepted = false;
    }
    if (isset($response['errors']) && is_array($response['errors'])) {
        $is_accepted = false;
    }
    if (array_key_exists('exception', $response)) {
        $is_accepted = false;
    }

    // 3. IMPORTANTE: Verificar específicamente la respuesta de la DIAN
    if (isset($response['ResponseDian'])) {
        // Comprobar si la DIAN aceptó la factura (IsValid = true)
        if (isset($response['ResponseDian']['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['IsValid'])) {
            $is_valid = $response['ResponseDian']['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['IsValid'];
            // Si IsValid es 'false', la factura fue rechazada
            if ($is_valid === 'false' || $is_valid === false) {
                $is_accepted = false;
                
                // Extraer y registrar los errores para depuración
                if (isset($response['ResponseDian']['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['ErrorMessage']['string'])) {
                    $errors = $response['ResponseDian']['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['ErrorMessage']['string'];
                    if (is_array($errors)) {
                        error_log('DIAN Error: ' . implode(' | ', $errors));
                    } else {
                        error_log('DIAN Error: ' . $errors);
                    }
                }
            }
        }
    }

    return $is_accepted;
}

// funcion que devuelve el correlativo si la respuesta de dian es incorrecta
function changeResolutionNumberCurrent($id) {
    $resolution_number_current = get_option('facturaloperu_api_resolution_number_current');
    $number_suffix = get_post_meta( $id, 'number_suffix', true );

    // valido si ya tiene correlativo
    $next_number_current = $resolution_number_current - 1;
    if($next_number_current == $number_suffix) {
        // actualizo el correlativo
        update_post_meta( $id, 'is_number_suffix', 0 );
        // asigno el correlativo al pedido actual
        update_post_meta( $id, 'number_suffix', 0 );
        // actualizo el proximo correlativo en la configuración del plugin
        update_option("facturaloperu_api_resolution_number_current", $next_number_current);
    }
}

// Función para validar la configuración de la resolución antes de enviar a DIAN
function validate_resolution_config() {
    $errors = [];
    
    // Validar fecha de resolución
    $resolution_date = get_option('facturaloperu_api_resolution_date');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $resolution_date)) {
        $errors[] = 'La fecha de resolución debe tener el formato YYYY-MM-DD. Ejemplo: 2019-01-19';
    }
    
    // Validar fecha de inicio
    $date_from = get_option('facturaloperu_api_resolution_date_start');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
        $errors[] = 'La fecha de inicio debe tener el formato YYYY-MM-DD. Ejemplo: 2019-01-19';
    }
    
    // Validar fecha final
    $date_to = get_option('facturaloperu_api_resolution_date_stop');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
        $errors[] = 'La fecha final debe tener el formato YYYY-MM-DD. Ejemplo: 2030-01-19';
    }
    
    return $errors;
}

// action desde boton para enviar json
function ep_send_json_to_api() {
    // Verificar nonce para seguridad (opcional)
    // check_ajax_referer('my_nonce', 'security');
    
    // Verificar que se haya proporcionado un ID de post
    if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
        wp_send_json_error(array('message' => 'ID de pedido no especificado'));
        return;
    }
    
    $post_id = intval($_POST['post_id']);
    
    // Registrar para depuración
    error_log("DIAN: Iniciando envío de factura para pedido $post_id");
    
    try {
        // Regenerar el JSON con los números actualizados
        generateJson($post_id);
        
        // Validar configuración de resolución
        $resolution_errors = validate_resolution_config();
        if (!empty($resolution_errors)) {
            wp_send_json_error([
                'message' => 'Error en la configuración de resolución: ' . implode('. ', $resolution_errors),
                'resolution_errors' => $resolution_errors
            ]);
            return;
        }
        
        // Llamar a la función para realizar la consulta a la API
        $response = ep_perform_api_consultation($post_id);
        
        error_log("DIAN: Respuesta API recibida para pedido $post_id: " . print_r($response, true));
        
        
        // Validar respuesta DIAN
        $is_validate = validateResponse($response);
        
        if ($is_validate) {
            error_log("DIAN: Factura aceptada para pedido $post_id");
            wp_send_json_success(array(
                'message' => 'Factura enviada correctamente a la DIAN',
                'details' => $response
            ));
        } else {
            error_log("DIAN: Error validando factura para pedido $post_id");
            
            // Devolver correlativo solo si es un error de la API
            changeResolutionNumberCurrent($post_id);
            
            // Extraer mensaje de error específico si existe
            $error_message = 'Error al procesar la factura';
            
            if (isset($response['message'])) {
                $error_message = $response['message'];
            } elseif (isset($response['errors']) && is_array($response['errors'])) {
                foreach ($response['errors'] as $field => $errors) {
                    if (is_array($errors) && !empty($errors)) {
                        $error_message = $field . ': ' . $errors[0];
                        break;
                    }
                }
            }
            
            wp_send_json_error(array(
                'message' => $error_message,
                'details' => $response
            ));
        }
    } catch (Exception $e) {
        error_log("DIAN: Excepción al enviar factura: " . $e->getMessage());
        wp_send_json_error(array(
            'message' => 'Error interno: ' . $e->getMessage()
        ));
    }
}
add_action('wp_ajax_send_json_to_api', 'ep_send_json_to_api');

// action automatico desde cambio de status
add_action('woocommerce_order_status_completed', 'ep_consult_api_on_order_complete', 10,1);
function ep_consult_api_on_order_complete($order_id) {
    // Llamar a la función para realizar la consulta a la API
    $response = ep_perform_api_consultation($order_id);
    //validar respuesta dian
    $is_validate = validateResponse($response);

    if (!$is_validate) {
        changeResolutionNumberCurrent($order_id);
    }
}


/*
 * Contenido de BOX DIAN
 *
 *
 * ----------- statuses--------------------------------------------------------------
 * Pending payment – Order received, no payment initiated. Awaiting payment (unpaid).
 * Failed – Payment failed or was declined (unpaid). Note that this status may not show immediately and instead show as Pending until verified (e.g., PayPal).
 * Processing – Payment received (paid) and stock has been reduced; order is awaiting fulfillment. All product orders require processing, except those that only contain products which are both Virtual and Downloadable.
 * Completed – Order fulfilled and complete – requires no further action.
 * On-Hold – Awaiting payment – stock is reduced, but you need to confirm payment.
 * Cancelled – Cancelled by an admin or the customer – stock is increased, no further action required.
 * Refunded – Refunded by an admin – no further action required.
 *
 */
if ( ! function_exists( 'ep_add_sunat_fields' ) )
{
    function ep_add_sunat_fields() {
        global $post;
    
        $order = wc_get_order($post->ID);
        $order_status = $order->get_status(); // statuses
        $field_send_invoice = get_post_meta($post->ID, '_send_invoice', true) ?? ''; // check validar con 'on'
        $field_ep_sunat_api_response = get_post_meta($post->ID, '_ep_sunat_api_response', true) ?? ''; // booleano
        $field_ep_sunat_api_xml = get_post_meta($post->ID, '_ep_sunat_api_xml', true) ?? ''; // respuesta api
        $field_json_invoice = get_post_meta($post->ID, 'json_invoice', true); // json
    
        if ('completed' !== $order_status && empty($field_ep_sunat_api_xml)) {
            echo "<div class='notice notice-warning' style='padding: 10px;'>";
            echo "<p>La facturación estará disponible cuando el estado del pedido se encuentre 'Completado'.</p>";
            echo "</div>";
            return;
        }
    
        if (!empty($field_ep_sunat_api_xml)) {
            $decoded_json = json_decode($field_ep_sunat_api_xml, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo '<div class="notice notice-error" style="padding: 10px;">';
                echo '<p>Error al decodificar el JSON de respuesta: ' . json_last_error_msg() . '</p>';
                echo '</div>';
                return;
            }
    
            // Determinar si la factura fue aceptada o rechazada
            $is_valid = true;
            $status_message = "La factura fue aceptada correctamente.";
            $dian_errors = [];
    
            // Verificar la respuesta de DIAN
            if (isset($decoded_json['ResponseDian']) && 
                isset($decoded_json['ResponseDian']['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['IsValid'])) {
                
                $is_valid_value = $decoded_json['ResponseDian']['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['IsValid'];
                $is_valid = ($is_valid_value === 'true' || $is_valid_value === true);
                
                // Obtener mensaje de estado
                if (isset($decoded_json['ResponseDian']['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['StatusMessage'])) {
                    $status_message = $decoded_json['ResponseDian']['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['StatusMessage'];
                }
                
                // Obtener mensajes de error si existen
                if (isset($decoded_json['ResponseDian']['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['ErrorMessage']['string'])) {
                    $dian_errors = $decoded_json['ResponseDian']['Envelope']['Body']['SendBillSyncResponse']['SendBillSyncResult']['ErrorMessage']['string'];
                    if (!is_array($dian_errors)) {
                        $dian_errors = [$dian_errors];
                    }
                }
            }
    
            // También verificar otros patrones de error
            if (isset($decoded_json['message']) && in_array($decoded_json['message'], [
                "Trying to get property 'resolutions' of non-object",
                "No se encontró la URL especificada",
                "Server Error",
                "The given data was invalid.",
                "ErrorException"
            ])) {
                $is_valid = false;
                $status_message = $decoded_json['message'];
            }
    
            if (isset($decoded_json['success']) && $decoded_json['success'] === false) {
                $is_valid = false;
                $status_message = isset($decoded_json['message']) ? $decoded_json['message'] : 'La operación no fue exitosa.';
            }
    
            if (isset($decoded_json['errors']) && is_array($decoded_json['errors'])) {
                $is_valid = false;
                $error_msgs = [];
                foreach ($decoded_json['errors'] as $field => $msgs) {
                    if (is_array($msgs)) {
                        foreach ($msgs as $msg) {
                            $error_msgs[] = "$field: $msg";
                        }
                    } else {
                        $error_msgs[] = "$field: $msgs";
                    }
                }
                $dian_errors = array_merge($dian_errors, $error_msgs);
            }
    
            // Mostrar información de la factura
            echo '<div class="dian-response-container" style="margin-bottom: 20px;">';
            
            // Encabezado con el estado
            if ($is_valid) {
                echo '<div class="notice notice-success" style="padding: 10px;">';
                echo '<h3 style="margin-top: 0;">✅ Factura Electrónica Aceptada</h3>';
            } else {
                echo '<div class="notice notice-error" style="padding: 10px;">';
                echo '<h3 style="margin-top: 0;">❌ Factura Electrónica Rechazada</h3>';
            }
            
            echo '<p><strong>Estado:</strong> ' . esc_html($status_message) . '</p>';
            
            // Información adicional si está disponible
            if (isset($decoded_json['cufe'])) {
                echo '<p><strong>CUFE:</strong> ' . esc_html($decoded_json['cufe']) . '</p>';
            }
            
            // Enlaces a documentos si están disponibles
            echo '<div style="margin-top: 10px;">';
            if (isset($decoded_json['urlinvoicepdf'])) {
                echo '<a href="#" class="button" target="_blank" onclick="alert(\'Los enlaces de documentos solo están disponibles en el sistema de la DIAN\');">Ver PDF</a> ';
            }
            if (isset($decoded_json['urlinvoicexml'])) {
                echo '<a href="#" class="button" target="_blank" onclick="alert(\'Los enlaces de documentos solo están disponibles en el sistema de la DIAN\');">Ver XML</a>';
            }
            echo '</div>';
            
            echo '</div>'; // Fin del aviso de éxito/error
            
            // Mostrar errores específicos si la factura fue rechazada
            if (!$is_valid && !empty($dian_errors)) {
                echo '<div class="dian-errors" style="margin-bottom: 20px; background: #f8f8f8; padding: 15px; border: 1px solid #ddd; border-radius: 4px;">';
                echo '<h3>Errores reportados por la DIAN:</h3>';
                echo '<ul style="margin-left: 20px;">';
                foreach ($dian_errors as $error) {
                    echo '<li>' . esc_html($error) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            
            // Mostrar botón para reenviar si fue rechazada
            if (!$is_valid) {
                echo '<div style="margin-bottom: 20px;">';
                echo '<button id="on_send_json" data-post-id="' . esc_attr($post->ID) . '" class="button button-primary" style="background-color: #0073aa; color: white;">Reenviar a DIAN</button>';
                echo '<span id="sending-status" style="display:none; margin-left: 10px;">Enviando...</span>';
                echo '</div>';
            }
            
            // Mostrar detalles técnicos (colapsable para no ocupar tanto espacio)
            echo '<div class="dian-technical-details">';
            echo '<h3 style="cursor: pointer;" onclick="document.getElementById(\'dian-response-details\').style.display = document.getElementById(\'dian-response-details\').style.display === \'none\' ? \'block\' : \'none\';">Detalles Técnicos <span style="font-size: 0.8em;">(click para mostrar/ocultar)</span></h3>';
            echo '<div id="dian-response-details" style="display: none;">';
            echo '<textarea readonly style="width: 100%; min-height: 300px; font-family: monospace;">' . esc_textarea(json_encode($decoded_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</textarea>';
            echo '</div>';
            echo '</div>';
            
            echo '</div>'; // Fin del contenedor principal
            
            return;
        }
    
        // Si no hay respuesta de la API pero el pedido está completado, mostrar botón de envío
        if ('completed' === $order_status) {
            echo '<div style="padding-top: 5px; padding-bottom: 5px;">';
            echo '<button id="on_send_json" data-post-id="' . esc_attr($post->ID) . '" class="button button-primary">Enviar a DIAN</button>';
            echo '<span id="sending-status" style="display:none; margin-left: 10px;">Enviando...</span>';
            echo '</div>';
        }
    }
}

// FUNCION QUE SE EJECUTA AL GUARDAR/ACTUALIZAR UNA ORDEN
add_action( 'save_post', 'ep_save_sunat_field', 10, 1 );
if ( ! function_exists( 'ep_save_sunat_field' ) )
{

    function ep_save_sunat_field( $post_id ) {

        // We need to verify this with the proper authorization (security stuff).

        // CHEQUEO SI EL CAMPO EXISTE (CHECKBOX)
        if ( ! isset( $_POST[ 'ep_sunat_meta_field_nonce' ] ) ) {
            return $post_id;
        }
        $nonce = $_REQUEST[ 'ep_sunat_meta_field_nonce' ];

        //Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce ) ) {
            return $post_id;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // ACTUALIZO EL VALOR DEL CAMPO
        update_post_meta( $post_id, '_send_invoice', $_POST[ 'send_invoice' ] );
    }
}

// FUNCION QUE SE EJECUTA AL GUARDAR/ACTUALIZAR UNA ORDEN
add_action( 'save_post', 'ep_save_sunat_field_response', 10, 1 );
if ( ! function_exists( 'ep_save_sunat_field_response' ) )
{

    function ep_save_sunat_field_response( $post_id ) {

        // We need to verify this with the proper authorization (security stuff).

        // SI EXISTE EL CAMPO (HIDDEN CON DATOS DE RESPUESTA DE API)
        if ( ! isset( $_POST[ 'ep_sunat_meta_fields_api_nonce' ] ) ) {
            return $post_id;
        }
        $nonce = $_REQUEST[ 'ep_sunat_meta_fields_api_nonce' ];

        //Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce ) ) {
            return $post_id;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // GUARDO LOS CAMPOS
        update_post_meta( $post_id, '_ep_sunat_api_response', true );
        update_post_meta( $post_id, '_ep_sunat_api_xml', $_POST[ 'ep_sunat_api_xml' ] );
    }
}

add_filter( 'http_request_timeout', 'wp9838c_timeout_extend' );

function wp9838c_timeout_extend( $time )
{
    // Default timeout is 5
    return 20;
}
