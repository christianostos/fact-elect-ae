<?php
/**
 * Clase para la API REST del plugin
 *
 * Esta clase implementa los endpoints de la API REST para
 * interactuar con el plugin de facturación electrónica.
 *
 * @link       https://accioneficaz.com
 * @since      1.0.0
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/includes
 */

// Evitar acceso directo al archivo
if (!defined('WPINC')) {
    die;
}

/**
 * Clase para la API REST
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/includes
 * @author     Christian Ostos - Acción Eficaz
 */
class DIAN_API_REST {

    /**
     * Instancia de la clase de base de datos
     *
     * @since    1.0.0
     * @access   private
     * @var      DIAN_API_DB    $db    Instancia de la clase de base de datos
     */
    private $db;

    /**
     * Instancia de la clase generadora de XML
     *
     * @since    1.0.0
     * @access   private
     * @var      DIAN_API_XML_Generator    $xml_generator    Instancia de la clase generadora de XML
     */
    private $xml_generator;

    /**
     * Instancia de la clase de comunicación con la DIAN
     *
     * @since    1.0.0
     * @access   private
     * @var      DIAN_API_WebServices    $webservices    Instancia de la clase de comunicación con la DIAN
     */
    private $webservices;

    /**
     * Instancia de la clase generadora de PDF
     *
     * @since    1.0.0
     * @access   private
     * @var      DIAN_API_PDF_Generator    $pdf_generator    Instancia de la clase generadora de PDF
     */
    private $pdf_generator;

    /**
     * Namespace de la API
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $namespace    Namespace de la API
     */
    private $namespace = 'dian-api/v1';

    /**
     * Constructor de la clase
     *
     * @since    1.0.0
     * @param    DIAN_API_DB               $db               Instancia de la clase de base de datos
     * @param    DIAN_API_XML_Generator    $xml_generator    Instancia de la clase generadora de XML
     * @param    DIAN_API_WebServices      $webservices      Instancia de la clase de comunicación con la DIAN
     * @param    DIAN_API_PDF_Generator    $pdf_generator    Instancia de la clase generadora de PDF
     */
    public function __construct($db, $xml_generator, $webservices, $pdf_generator) {
        $this->db = $db;
        $this->xml_generator = $xml_generator;
        $this->webservices = $webservices;
        $this->pdf_generator = $pdf_generator;
    }

    /**
     * Inicializa la API REST
     *
     * @since    1.0.0
     * @return   void
     */
    public function inicializar() {
        add_action('rest_api_init', array($this, 'registrar_rutas'));
    }

    /**
     * Registra las rutas de la API REST
     *
     * @since    1.0.0
     * @return   void
     */
    public function registrar_rutas() {
        // Ruta para generar una factura electrónica
        register_rest_route($this->namespace, '/factura', array(
            'methods' => 'POST',
            'callback' => array($this, 'generar_factura'),
            'permission_callback' => array($this, 'verificar_permiso_api'),
        ));

        // Ruta para consultar una factura por número
        register_rest_route($this->namespace, '/factura/(?P<cliente_id>[a-zA-Z0-9_-]+)/(?P<prefijo>[a-zA-Z0-9_-]*)/(?P<numero>[0-9]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'consultar_factura'),
            'permission_callback' => array($this, 'verificar_permiso_api'),
        ));

        // Ruta para obtener el PDF de una factura
        register_rest_route($this->namespace, '/factura/pdf/(?P<cliente_id>[a-zA-Z0-9_-]+)/(?P<prefijo>[a-zA-Z0-9_-]*)/(?P<numero>[0-9]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'obtener_pdf_factura'),
            'permission_callback' => array($this, 'verificar_permiso_api'),
        ));

        // Ruta para enviar una factura a la DIAN
        register_rest_route($this->namespace, '/factura/enviar/(?P<cliente_id>[a-zA-Z0-9_-]+)/(?P<prefijo>[a-zA-Z0-9_-]*)/(?P<numero>[0-9]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'enviar_factura'),
            'permission_callback' => array($this, 'verificar_permiso_api'),
        ));

        // Ruta para verificar el estado de una factura en la DIAN
        register_rest_route($this->namespace, '/factura/estado/(?P<track_id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'verificar_estado_factura'),
            'permission_callback' => array($this, 'verificar_permiso_api'),
        ));

        // Ruta para obtener las resoluciones de numeración
        register_rest_route($this->namespace, '/resoluciones/(?P<cliente_id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'obtener_resoluciones'),
            'permission_callback' => array($this, 'verificar_permiso_api'),
        ));

        // Ruta para crear una resolución de numeración
        register_rest_route($this->namespace, '/resoluciones', array(
            'methods' => 'POST',
            'callback' => array($this, 'crear_resolucion'),
            'permission_callback' => array($this, 'verificar_permiso_api'),
        ));

        // Ruta para obtener clientes
        register_rest_route($this->namespace, '/clientes', array(
            'methods' => 'GET',
            'callback' => array($this, 'obtener_clientes'),
            'permission_callback' => array($this, 'verificar_permiso_api'),
        ));

        // Ruta para generar una nota crédito
        register_rest_route($this->namespace, '/nota-credito', array(
            'methods' => 'POST',
            'callback' => array($this, 'generar_nota_credito'),
            'permission_callback' => array($this, 'verificar_permiso_api'),
        ));

        // Ruta para generar una nota débito
        register_rest_route($this->namespace, '/nota-debito', array(
            'methods' => 'POST',
            'callback' => array($this, 'generar_nota_debito'),
            'permission_callback' => array($this, 'verificar_permiso_api'),
        ));
    }

    /**
     * Verifica el permiso para acceder a la API
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Objeto de solicitud REST
     * @return   bool|WP_Error
     */
    public function verificar_permiso_api($request) {
        // Verificar si se proporciona una API Key
        $api_key = $request->get_header('X-API-Key');
        
        if (empty($api_key)) {
            return new WP_Error(
                'rest_forbidden',
                'Se requiere una API Key válida',
                array('status' => 401)
            );
        }
        
        // Consultar la API Key en la base de datos
        $api_key_data = $this->db->obtener_api_key($api_key);
        
        if (!$api_key_data) {
            return new WP_Error(
                'rest_forbidden',
                'API Key inválida',
                array('status' => 401)
            );
        }
        
        // Verificar si la API Key está activa
        if ($api_key_data['estado'] !== 'activo') {
            return new WP_Error(
                'rest_forbidden',
                'API Key inactiva',
                array('status' => 401)
            );
        }
        
        // Verificar permisos específicos (si es necesario)
        // TODO: Implementar verificación de permisos más específicos
        
        return true;
    }

    /**
     * Genera una factura electrónica
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Objeto de solicitud REST
     * @return   WP_REST_Response|WP_Error
     */
    public function generar_factura($request) {
        // Obtener parámetros de la solicitud
        $params = $request->get_json_params();
        
        // Validar parámetros mínimos necesarios
        $required_params = array('cliente_id', 'invoice_data');
        foreach ($required_params as $param) {
            if (!isset($params[$param]) || empty($params[$param])) {
                return new WP_Error(
                    'missing_parameter',
                    'Falta el parámetro requerido: ' . $param,
                    array('status' => 400)
                );
            }
        }
        
        // Generar el XML
        $result = $this->xml_generator->generate_invoice_xml($params['invoice_data']);
        
        if (!$result['success']) {
            return new WP_Error(
                'xml_generation_error',
                $result['message'],
                array('status' => 400)
            );
        }
        
        // Guardar la factura en la base de datos
        $invoice_data = $params['invoice_data'];
        $cliente_id = $params['cliente_id'];
        
        // Determinar prefijo y número
        $prefijo = isset($invoice_data['prefix']) ? $invoice_data['prefix'] : '';
        $numero = $invoice_data['invoice_number'];
        if (strpos($numero, $prefijo) === 0) {
            $numero = substr($numero, strlen($prefijo));
        }
        
        // Preparar datos del documento
        $documento_data = array(
            'cliente_id' => $cliente_id,
            'tipo_documento' => 'factura',
            'prefijo' => $prefijo,
            'numero' => $numero,
            'emisor_nit' => $invoice_data['supplier']['identification_number'],
            'emisor_razon_social' => $invoice_data['supplier']['name'],
            'receptor_documento' => $invoice_data['customer']['identification_number'],
            'receptor_razon_social' => $invoice_data['customer']['name'],
            'fecha_emision' => $invoice_data['issue_date'] . ' ' . $invoice_data['issue_time'],
            'fecha_vencimiento' => $invoice_data['due_date'] . ' ' . $invoice_data['issue_time'],
            'valor_sin_impuestos' => $invoice_data['monetary_totals']['tax_exclusive_amount'],
            'valor_impuestos' => $invoice_data['monetary_totals']['tax_inclusive_amount'] - $invoice_data['monetary_totals']['tax_exclusive_amount'],
            'valor_total' => $invoice_data['monetary_totals']['payable_amount'],
            'moneda' => 'COP',
            'estado' => 'generado',
            'archivo_xml' => $result['xml'],
            'ambiente' => isset($params['ambiente']) ? $params['ambiente'] : 'habilitacion',
            'items' => json_encode($invoice_data['items']),
            'observaciones' => isset($invoice_data['note']) ? $invoice_data['note'] : ''
        );
        
        // Guardar en la base de datos
        $documento_id = $this->db->guardar_documento($documento_data);
        
        if (!$documento_id) {
            return new WP_Error(
                'database_error',
                'Error al guardar la factura en la base de datos',
                array('status' => 500)
            );
        }
        
        // Generar PDF si se solicita
        if (isset($params['generar_pdf']) && $params['generar_pdf']) {
            $pdf_result = $this->pdf_generator->generar_pdf_factura($cliente_id, $prefijo, $numero);
            $has_pdf = $pdf_result['success'];
        } else {
            $has_pdf = false;
        }
        
        // Preparar respuesta
        $response = array(
            'success' => true,
            'message' => 'Factura generada correctamente',
            'factura' => array(
                'id' => $documento_id,
                'cliente_id' => $cliente_id,
                'prefijo' => $prefijo,
                'numero' => $numero,
                'fecha_emision' => $invoice_data['issue_date'],
                'valor_total' => $invoice_data['monetary_totals']['payable_amount'],
                'estado' => 'generado',
                'has_pdf' => $has_pdf
            )
        );
        
        return rest_ensure_response($response);
    }

    /**
     * Consulta una factura por número
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Objeto de solicitud REST
     * @return   WP_REST_Response|WP_Error
     */
    public function consultar_factura($request) {
        // Obtener parámetros de la ruta
        $cliente_id = $request['cliente_id'];
        $prefijo = $request['prefijo'];
        $numero = $request['numero'];
        
        // Obtener la factura de la base de datos
        $factura = $this->db->obtener_documento_por_numero($cliente_id, 'factura', $prefijo, $numero);
        
        if (!$factura) {
            return new WP_Error(
                'not_found',
                'No se encontró la factura especificada',
                array('status' => 404)
            );
        }
        
        // Formatear la respuesta
        $response = array(
            'success' => true,
            'factura' => array(
                'id' => $factura['id'],
                'cliente_id' => $factura['cliente_id'],
                'tipo_documento' => $factura['tipo_documento'],
                'prefijo' => $factura['prefijo'],
                'numero' => $factura['numero'],
                'emisor_nit' => $factura['emisor_nit'],
                'emisor_razon_social' => $factura['emisor_razon_social'],
                'receptor_documento' => $factura['receptor_documento'],
                'receptor_razon_social' => $factura['receptor_razon_social'],
                'fecha_emision' => $factura['fecha_emision'],
                'fecha_vencimiento' => $factura['fecha_vencimiento'],
                'valor_sin_impuestos' => $factura['valor_sin_impuestos'],
                'valor_impuestos' => $factura['valor_impuestos'],
                'valor_total' => $factura['valor_total'],
                'moneda' => $factura['moneda'],
                'estado' => $factura['estado'],
                'cufe' => $factura['cufe'],
                'track_id' => $factura['track_id'],
                'ambiente' => $factura['ambiente'],
                'items' => json_decode($factura['items'] ?? '[]', true),
                'observaciones' => $factura['observaciones'],
                'has_pdf' => !empty($factura['archivo_pdf']),
                'has_xml' => !empty($factura['archivo_xml'])
            )
        );
        
        return rest_ensure_response($response);
    }

    /**
     * Obtiene el PDF de una factura
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Objeto de solicitud REST
     * @return   WP_REST_Response|WP_Error
     */
    public function obtener_pdf_factura($request) {
        // Obtener parámetros de la ruta
        $cliente_id = $request['cliente_id'];
        $prefijo = $request['prefijo'];
        $numero = $request['numero'];
        
        // Generar o recuperar el PDF
        $result = $this->pdf_generator->generar_pdf_factura($cliente_id, $prefijo, $numero, false);
        
        if (!$result['success']) {
            return new WP_Error(
                'pdf_generation_error',
                $result['message'],
                array('status' => 503) // Service Unavailable
            );
        }
        
        // Determinar el tipo de respuesta (descarga o base64)
        $format = $request->get_param('format');
        
        if ($format === 'base64') {
            // Devolver el PDF en formato base64
            $response = array(
                'success' => true,
                'message' => 'PDF generado correctamente',
                'pdf_base64' => base64_encode($result['pdf_content']),
                'filename' => 'Factura_' . $prefijo . $numero . '.pdf'
            );
            
            return rest_ensure_response($response);
        } else {
            // Configurar encabezados para descarga directa
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Factura_' . $prefijo . $numero . '.pdf"');
            header('Content-Length: ' . strlen($result['pdf_content']));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            // Enviar el contenido del PDF
            echo $result['pdf_content'];
            exit;
        }
    }

    /**
     * Envía una factura a la DIAN
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Objeto de solicitud REST
     * @return   WP_REST_Response|WP_Error
     */
    public function enviar_factura($request) {
        // Obtener parámetros de la ruta
        $cliente_id = $request['cliente_id'];
        $prefijo = $request['prefijo'];
        $numero = $request['numero'];
        
        // Obtener la factura de la base de datos
        $factura = $this->db->obtener_documento_por_numero($cliente_id, 'factura', $prefijo, $numero);
        
        if (!$factura) {
            return new WP_Error(
                'not_found',
                'No se encontró la factura especificada',
                array('status' => 404)
            );
        }
        
        // Verificar si ya fue enviada
        if (!empty($factura['track_id']) && $factura['estado'] != 'generado') {
            return new WP_Error(
                'already_sent',
                'La factura ya fue enviada a la DIAN',
                array('status' => 400)
            );
        }
        
        // Obtener la configuración del cliente
        $config = $this->db->obtener_configuracion($cliente_id);
        if (!$config) {
            return new WP_Error(
                'config_not_found',
                'No se encontró la configuración del cliente',
                array('status' => 404)
            );
        }
        
        // Configurar el modo de operación
        $modo = $request->get_param('modo') ?: $config['modo_operacion'];
        
        // Configurar credenciales
        $credentials = array(
            'software_id' => $config['id_software'],
            'software_pin' => $config['software_pin'],
            'company_id' => $config['cliente_id'],
            'company_pin' => isset($config['company_pin']) ? $config['company_pin'] : '',
            'test_set_id' => isset($config['test_set_id']) ? $config['test_set_id'] : ''
        );
        
        // Instanciar webservices con las credenciales y modo
        $webservices = new DIAN_API_WebServices($this->db, $modo, $credentials);
        
        // Enviar la factura a la DIAN
        $xml_content = $factura['archivo_xml'];
        $result = $webservices->send_document($xml_content, 'invoice');
        
        if (!$result['success']) {
            return new WP_Error(
                'dian_error',
                'Error al enviar a la DIAN: ' . $result['message'],
                array('status' => 500)
            );
        }
        
        // Actualizar el estado de la factura
        $update_data = array(
            'estado' => 'enviado',
            'track_id' => $result['track_id'],
            'respuesta_dian' => $result['response']
        );
        
        $this->db->actualizar_documento_por_track_id($result['track_id'], $update_data);
        
        // Preparar respuesta
        $response = array(
            'success' => true,
            'message' => 'Factura enviada correctamente a la DIAN',
            'track_id' => $result['track_id'],
            'estado' => 'enviado'
        );
        
        return rest_ensure_response($response);
    }

    /**
     * Verifica el estado de una factura en la DIAN
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Objeto de solicitud REST
     * @return   WP_REST_Response|WP_Error
     */
    public function verificar_estado_factura($request) {
        // Obtener parámetros de la ruta
        $track_id = $request['track_id'];
        
        // Obtener la factura de la base de datos
        $factura = $this->db->obtener_documento_por_track_id($track_id);
        
        if (!$factura) {
            return new WP_Error(
                'not_found',
                'No se encontró la factura con el track_id especificado',
                array('status' => 404)
            );
        }
        
        // Obtener la configuración del cliente
        $config = $this->db->obtener_configuracion($factura['cliente_id']);
        if (!$config) {
            return new WP_Error(
                'config_not_found',
                'No se encontró la configuración del cliente',
                array('status' => 404)
            );
        }
        
        // Configurar el modo de operación
        $modo = $request->get_param('modo') ?: $config['modo_operacion'];
        
        // Configurar credenciales
        $credentials = array(
            'software_id' => $config['id_software'],
            'software_pin' => $config['software_pin'],
            'company_id' => $config['cliente_id'],
            'company_pin' => isset($config['company_pin']) ? $config['company_pin'] : '',
            'test_set_id' => isset($config['test_set_id']) ? $config['test_set_id'] : ''
        );
        
        // Instanciar webservices con las credenciales y modo
        $webservices = new DIAN_API_WebServices($this->db, $modo, $credentials);
        
        // Verificar el estado en la DIAN
        $result = $webservices->check_document_status($track_id);
        
        if (!$result['success']) {
            return new WP_Error(
                'dian_error',
                'Error al consultar estado en la DIAN: ' . $result['message'],
                array('status' => 500)
            );
        }
        
        // Preparar respuesta
        $response = array(
            'success' => true,
            'message' => 'Estado consultado correctamente',
            'track_id' => $track_id,
            'estado' => $result['status']['status'],
            'codigo_estado' => $result['status']['status_code'],
            'descripcion_estado' => $result['status']['status_description'],
            'es_valido' => $result['status']['is_valid'],
            'errores' => $result['status']['errors']
        );
        
        return rest_ensure_response($response);
    }

    /**
     * Obtiene las resoluciones de numeración de un cliente
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Objeto de solicitud REST
     * @return   WP_REST_Response|WP_Error
     */
    public function obtener_resoluciones($request) {
        // Obtener parámetros de la ruta
        $cliente_id = $request['cliente_id'];
        
        // Obtener tipo de documento (opcional)
        $tipo_documento = $request->get_param('tipo_documento');
        
        // Obtener resoluciones de la base de datos
        $resoluciones = $this->db->obtener_resoluciones_vigentes($cliente_id, $tipo_documento);
        
        // Preparar respuesta
        $response = array(
            'success' => true,
            'resoluciones' => $resoluciones
        );
        
        return rest_ensure_response($response);
    }

    /**
     * Crea una resolución de numeración
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Objeto de solicitud REST
     * @return   WP_REST_Response|WP_Error
     */
    public function crear_resolucion($request) {
        // Obtener parámetros de la solicitud
        $params = $request->get_json_params();
        
        // Validar parámetros mínimos necesarios
        $required_params = array(
            'cliente_id', 'desde_numero', 'hasta_numero', 
            'numero_resolucion', 'fecha_resolucion', 
            'fecha_desde', 'fecha_hasta'
        );
        
        foreach ($required_params as $param) {
            if (!isset($params[$param]) || empty($params[$param])) {
                return new WP_Error(
                    'missing_parameter',
                    'Falta el parámetro requerido: ' . $param,
                    array('status' => 400)
                );
            }
        }
        
        // Guardar la resolución en la base de datos
        $result = $this->db->guardar_resolucion($params);
        
        if (!$result) {
            return new WP_Error(
                'database_error',
                'Error al guardar la resolución en la base de datos',
                array('status' => 500)
            );
        }
        
        // Preparar respuesta
        $response = array(
            'success' => true,
            'message' => 'Resolución guardada correctamente',
            'id' => $result
        );
        
        return rest_ensure_response($response);
    }

    /**
     * Obtiene la lista de clientes configurados
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Objeto de solicitud REST
     * @return   WP_REST_Response|WP_Error
     */
    public function obtener_clientes($request) {
        global $wpdb;
        
        // Obtener la tabla de configuración
        $tabla_configuracion = $wpdb->prefix . 'dian_configuracion';
        
        // Consultar clientes
        $clientes = $wpdb->get_results(
            "SELECT cliente_id, id_software, modo_operacion, fecha_creacion 
             FROM {$tabla_configuracion} 
             ORDER BY fecha_creacion DESC",
            ARRAY_A
        );
        
        // Preparar respuesta
        $response = array(
            'success' => true,
            'clientes' => $clientes
        );
        
        return rest_ensure_response($response);
    }

    /**
     * Genera una nota crédito
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Objeto de solicitud REST
     * @return   WP_REST_Response|WP_Error
     */
    public function generar_nota_credito($request) {
        // Obtener parámetros de la solicitud
        $params = $request->get_json_params();
        
        // Implementación pendiente
        return new WP_Error(
            'not_implemented',
            'Esta funcionalidad aún no está implementada',
            array('status' => 501)
        );
    }

    /**
     * Genera una nota débito
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Objeto de solicitud REST
     * @return   WP_REST_Response|WP_Error
     */
    public function generar_nota_debito($request) {
        // Obtener parámetros de la solicitud
        $params = $request->get_json_params();
        
        // Implementación pendiente
        return new WP_Error(
            'not_implemented',
            'Esta funcionalidad aún no está implementada',
            array('status' => 501)
        );
    }
}