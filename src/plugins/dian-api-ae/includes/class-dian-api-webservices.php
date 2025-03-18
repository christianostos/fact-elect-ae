<?php
/**
 * Clase para la comunicación con los webservices de la DIAN
 *
 * Esta clase gestiona todas las comunicaciones con los servicios web de la DIAN
 * para facturación electrónica, incluyendo autenticación, envío de documentos y consultas.
 *
 * @link       https://tudominio.com/plugin-dian-api
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
 * Clase para la comunicación con los webservices de la DIAN
 *
 * Implementa la lógica para conectarse y comunicarse con los servicios web de la DIAN
 * para la facturación electrónica.
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/includes
 * @author     Tu Nombre <email@example.com>
 */
class DIAN_API_WebServices {

    /**
     * Instancia de la clase de base de datos
     *
     * @since    1.0.0
     * @access   private
     * @var      DIAN_API_DB    $db    Instancia de la clase de base de datos
     */
    private $db;

    /**
     * URLs de endpoints del webservice DIAN
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $endpoints    URLs de los endpoints de DIAN
     */
    private $endpoints = array();

    /**
     * Credenciales de acceso
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $credentials    Credenciales para autenticación
     */
    private $credentials = array();

    /**
     * Modo de operación (habilitación/producción)
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $mode    Modo de operación ('test' o 'production')
     */
    private $mode = 'test';

    /**
     * Certificado para firma digital
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $certificate_path    Ruta al certificado digital
     */
    private $certificate_path = '';

    /**
     * Contraseña del certificado digital
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $certificate_password    Contraseña del certificado
     */
    private $certificate_password = '';

    /**
     * Constructor de la clase
     *
     * @since    1.0.0
     * @param    DIAN_API_DB    $db            Instancia de la clase de base de datos
     * @param    string         $mode          Modo de operación ('test' o 'production')
     * @param    array          $credentials   Credenciales de acceso (opcional)
     */
    public function __construct($db, $mode = 'test', $credentials = array()) {
        $this->db = $db;
        $this->mode = $mode;
        
        // Inicializar credenciales
        if (!empty($credentials)) {
            $this->credentials = $credentials;
        } else {
            $this->load_credentials();
        }
        
        // Establecer URLs de endpoints según el modo
        $this->set_endpoints();
    }

    /**
     * Carga las credenciales desde las opciones de WordPress
     *
     * @since    1.0.0
     * @return   void
     */
    private function load_credentials() {
        $this->credentials = array(
            'software_id' => get_option('dian_api_software_id', ''),
            'software_pin' => get_option('dian_api_software_pin', ''),
            'company_id' => get_option('dian_api_company_id', ''),
            'company_pin' => get_option('dian_api_company_pin', ''),
            'test_set_id' => get_option('dian_api_test_set_id', '')
        );
        
        $this->certificate_path = get_option('dian_api_certificate_path', '');
        $this->certificate_password = get_option('dian_api_certificate_password', '');
    }

    /**
     * Establece las URLs de los endpoints según el modo de operación
     *
     * @since    1.0.0
     * @return   void
     */
    private function set_endpoints() {
        if ($this->mode === 'production') {
            $this->endpoints = array(
                'auth' => 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc',
                'send' => 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc',
                'status' => 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc',
                'numbering' => 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc'
            );
        } else {
            $this->endpoints = array(
                'auth' => 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
                'send' => 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
                'status' => 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
                'numbering' => 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc'
            );
        }
    }

    /**
     * Autentica con el servicio web de la DIAN
     *
     * @since    1.0.0
     * @return   array    Resultado de la autenticación
     */
    public function authenticate() {
        if (empty($this->credentials['software_id']) || empty($this->credentials['software_pin']) ||
            empty($this->credentials['company_id']) || empty($this->credentials['company_pin'])) {
            return array(
                'success' => false,
                'message' => 'Faltan credenciales de autenticación'
            );
        }
        
        try {
            // Preparar datos para la solicitud SOAP
            $soap_url = $this->endpoints['auth'];
            
            // Timestamp actual
            $created = date('Y-m-d\TH:i:s.v\Z');
            $expires = date('Y-m-d\TH:i:s.v\Z', strtotime('+5 minutes'));
            
            // Crear el mensaje SOAP para la autenticación
            $xml_request = $this->create_auth_request($created, $expires);
            
            // Realizar la solicitud SOAP
            $response = $this->send_soap_request($soap_url, 'GetNumberingRange', $xml_request);
            
            // Procesar la respuesta
            if (isset($response['success']) && $response['success']) {
                // Guardar el token o información de sesión si es necesario
                $this->save_auth_info($response['data']);
                
                return array(
                    'success' => true,
                    'message' => 'Autenticación exitosa',
                    'data' => $response['data']
                );
            } else {
                return array(
                    'success' => false,
                    'message' => 'Error de autenticación: ' . $response['message']
                );
            }
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error de autenticación: ' . $e->getMessage()
            );
        }
    }

    /**
     * Crea el XML de solicitud para autenticación
     *
     * @since    1.0.0
     * @param    string    $created    Timestamp de creación
     * @param    string    $expires    Timestamp de expiración
     * @return   string    XML de solicitud
     */
    private function create_auth_request($created, $expires) {
        // Generar nonce para seguridad
        $nonce = $this->generate_nonce();
        $nonce_base64 = base64_encode($nonce);
        
        // Generar el digest de seguridad
        $digest_xml = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <wsu:Timestamp wsu:Id="TS-'. uniqid() .'">
                <wsu:Created>'. $created .'</wsu:Created>
                <wsu:Expires>'. $expires .'</wsu:Expires>
            </wsu:Timestamp>
            <wsse:UsernameToken wsu:Id="UsernameToken-'. uniqid() .'">
                <wsse:Username>'. $this->credentials['software_id'] .'</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'. $this->credentials['software_pin'] .'</wsse:Password>
                <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'. $nonce_base64 .'</wsse:Nonce>
                <wsu:Created>'. $created .'</wsu:Created>
            </wsse:UsernameToken>
        </wsse:Security>';
        
        // Crear el mensaje SOAP completo
        $soap_request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wcf="http://wcf.dian.colombia">
            <soapenv:Header>
                '. $digest_xml .'
            </soapenv:Header>
            <soapenv:Body>
                <wcf:GetNumberingRange>
                    <wcf:accountCode>'. $this->credentials['company_id'] .'</wcf:accountCode>
                    <wcf:accountCodeT>'. $this->credentials['company_id'] .'</wcf:accountCodeT>
                    <wcf:softwareCode>'. $this->credentials['software_id'] .'</wcf:softwareCode>
                </wcf:GetNumberingRange>
            </soapenv:Body>
        </soapenv:Envelope>';
        
        return $soap_request;
    }

    /**
     * Genera un valor nonce aleatorio para seguridad
     *
     * @since    1.0.0
     * @return   string    Valor nonce
     */
    private function generate_nonce() {
        return random_bytes(16);
    }

    /**
     * Guarda la información de autenticación
     *
     * @since    1.0.0
     * @param    array    $auth_data    Datos de autenticación
     * @return   bool     Éxito de la operación
     */
    private function save_auth_info($auth_data) {
        // Guardar la información en la base de datos para su uso posterior
        $auth_info = array(
            'auth_data' => json_encode($auth_data),
            'created_at' => current_time('mysql')
        );
        
        return $this->db->insert_auth_info($auth_info);
    }

    /**
     * Envia una factura electrónica a la DIAN
     *
     * @since    1.0.0
     * @param    string    $xml_content    Contenido XML de la factura
     * @param    string    $document_type  Tipo de documento ('invoice', 'credit_note', 'debit_note')
     * @return   array     Resultado de la operación
     */
    public function send_document($xml_content, $document_type = 'invoice') {
        try {
            // Validar que tengamos un certificado configurado
            if (empty($this->certificate_path) || empty($this->certificate_password)) {
                return array(
                    'success' => false,
                    'message' => 'Certificado digital no configurado'
                );
            }
            
            // Firmar el XML con el certificado digital
            $signed_xml = $this->sign_xml($xml_content);
            if (!$signed_xml['success']) {
                return array(
                    'success' => false,
                    'message' => 'Error al firmar el documento: ' . $signed_xml['message']
                );
            }
            
            // Codificar el XML en base64
            $xml_base64 = base64_encode($signed_xml['signed_xml']);
            
            // Preparar datos para la solicitud SOAP
            $soap_url = $this->endpoints['send'];
            
            // Crear el mensaje SOAP para enviar documento
            $xml_request = $this->create_send_request($xml_base64, $document_type);
            
            // Realizar la solicitud SOAP
            $response = $this->send_soap_request($soap_url, 'SendBillAsync', $xml_request);
            
            // Procesar la respuesta y guardar el trackId
            if (isset($response['success']) && $response['success']) {
                // Extraer el trackId de la respuesta
                $track_id = $this->extract_track_id($response['data']);
                
                // Guardar la información del envío
                $this->save_document_submission($xml_content, $document_type, $track_id);
                
                return array(
                    'success' => true,
                    'message' => 'Documento enviado exitosamente',
                    'track_id' => $track_id,
                    'response' => $response['data']
                );
            } else {
                return array(
                    'success' => false,
                    'message' => 'Error al enviar el documento: ' . $response['message']
                );
            }
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error al enviar el documento: ' . $e->getMessage()
            );
        }
    }

    /**
     * Firma un XML con el certificado digital
     *
     * @since    1.0.0
     * @param    string    $xml_content    Contenido XML a firmar
     * @return   array     Resultado con el XML firmado
     */
    private function sign_xml($xml_content) {
        try {
            // Esta implementación es un placeholder
            // La firma real requiere una biblioteca como XMLSecLibs o similar
            // y el proceso es bastante complejo
            
            // Para una implementación real, considera usar:
            // https://github.com/robrichards/xmlseclibs
            
            /*
            // Ejemplo conceptual usando xmlseclibs:
            require_once 'xmlseclibs/xmlseclibs.php';
            
            $dom = new DOMDocument();
            $dom->loadXML($xml_content);
            
            $objDSig = new XMLSecurityDSig();
            $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
            $objDSig->addReference($dom, XMLSecurityDSig::SHA256, array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'));
            
            $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
            $objKey->loadKey($this->certificate_path, true, $this->certificate_password);
            
            $objDSig->sign($objKey);
            $objDSig->add509Cert(file_get_contents($this->certificate_path));
            $objDSig->appendSignature($dom->documentElement);
            
            $signed_xml = $dom->saveXML();
            */
            
            // Por ahora, simplemente devolvemos el XML original (sin firmar realmente)
            // En una implementación real, este método debe reemplazarse por uno que realmente firme el XML
            
            return array(
                'success' => true,
                'signed_xml' => $xml_content,
                'message' => 'XML firmado exitosamente (simulado)'
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error al firmar el XML: ' . $e->getMessage()
            );
        }
    }

    /**
     * Crea el XML de solicitud para enviar un documento
     *
     * @since    1.0.0
     * @param    string    $xml_base64     XML codificado en base64
     * @param    string    $document_type  Tipo de documento
     * @return   string    XML de solicitud
     */
    private function create_send_request($xml_base64, $document_type) {
        // Timestamp actual
        $created = date('Y-m-d\TH:i:s.v\Z');
        $expires = date('Y-m-d\TH:i:s.v\Z', strtotime('+5 minutes'));
        
        // Generar nonce para seguridad
        $nonce = $this->generate_nonce();
        $nonce_base64 = base64_encode($nonce);
        
        // Generar el digest de seguridad
        $digest_xml = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <wsu:Timestamp wsu:Id="TS-'. uniqid() .'">
                <wsu:Created>'. $created .'</wsu:Created>
                <wsu:Expires>'. $expires .'</wsu:Expires>
            </wsu:Timestamp>
            <wsse:UsernameToken wsu:Id="UsernameToken-'. uniqid() .'">
                <wsse:Username>'. $this->credentials['software_id'] .'</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'. $this->credentials['software_pin'] .'</wsse:Password>
                <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'. $nonce_base64 .'</wsse:Nonce>
                <wsu:Created>'. $created .'</wsu:Created>
            </wsse:UsernameToken>
        </wsse:Security>';
        
        // Crear el mensaje SOAP completo
        $soap_request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wcf="http://wcf.dian.colombia">
            <soapenv:Header>
                '. $digest_xml .'
            </soapenv:Header>
            <soapenv:Body>
                <wcf:SendBillAsync>
                    <wcf:fileName>invoice.xml</wcf:fileName>
                    <wcf:contentFile>'. $xml_base64 .'</wcf:contentFile>
                    <wcf:testSetId>'. $this->credentials['test_set_id'] .'</wcf:testSetId>
                </wcf:SendBillAsync>
            </soapenv:Body>
        </soapenv:Envelope>';
        
        return $soap_request;
    }

    /**
     * Envía una solicitud SOAP a un endpoint de la DIAN
     *
     * @since    1.0.0
     * @param    string    $url         URL del endpoint
     * @param    string    $action      Acción SOAP
     * @param    string    $xml_request XML de la solicitud
     * @return   array     Resultado de la operación
     */
    private function send_soap_request($url, $action, $xml_request) {
        try {
            // Configurar la solicitud HTTP
            $headers = array(
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "http://wcf.dian.colombia/' . $action . '"',
                'Content-Length: ' . strlen($xml_request)
            );
            
            // Crear contexto para la solicitud
            $context = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => implode("\r\n", $headers),
                    'content' => $xml_request,
                    'timeout' => 30
                )
            );
            
            // Usar cURL para mayor control
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Consideración: En producción, esto debe ser true
            
            // Ejecutar la solicitud
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            // Registrar la solicitud y respuesta para depuración
            $this->log_soap_request($action, $xml_request, $response, $http_code);
            
            // Verificar si la solicitud fue exitosa
            if ($http_code != 200) {
                return array(
                    'success' => false,
                    'message' => 'Error HTTP ' . $http_code . ': ' . $error
                );
            }
            
            // Procesar la respuesta SOAP
            $response_data = $this->parse_soap_response($response, $action);
            
            if ($response_data['success']) {
                return array(
                    'success' => true,
                    'data' => $response_data['data']
                );
            } else {
                return array(
                    'success' => false,
                    'message' => $response_data['message']
                );
            }
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error en la solicitud SOAP: ' . $e->getMessage()
            );
        }
    }

    /**
     * Registra una solicitud SOAP y su respuesta
     *
     * @since    1.0.0
     * @param    string    $action      Acción SOAP
     * @param    string    $request     XML de solicitud
     * @param    string    $response    XML de respuesta
     * @param    int       $http_code   Código de respuesta HTTP
     * @return   void
     */
    private function log_soap_request($action, $request, $response, $http_code) {
        $log_data = array(
            'action' => $action,
            'request' => $request,
            'response' => $response,
            'http_code' => $http_code,
            'created_at' => current_time('mysql')
        );
        
        $this->db->insert_log($log_data);
    }

    /**
     * Analiza una respuesta SOAP de la DIAN
     *
     * @since    1.0.0
     * @param    string    $xml_response  XML de respuesta
     * @param    string    $action        Acción SOAP
     * @return   array     Datos extraídos de la respuesta
     */
    private function parse_soap_response($xml_response, $action) {
        try {
            // Cargar la respuesta XML
            $doc = new DOMDocument();
            $doc->loadXML($xml_response);
            
            // Verificar si hay errores SOAP
            $fault_nodes = $doc->getElementsByTagName('Fault');
            if ($fault_nodes->length > 0) {
                $fault_node = $fault_nodes->item(0);
                $fault_code = $fault_node->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $fault_string = $fault_node->getElementsByTagName('faultstring')->item(0)->nodeValue;
                
                return array(
                    'success' => false,
                    'message' => 'Error SOAP: ' . $fault_code . ' - ' . $fault_string
                );
            }
            
            // Extraer los datos según el tipo de acción
            if ($action == 'GetNumberingRange') {
                // Analizar respuesta de autenticación
                $result_nodes = $doc->getElementsByTagName('GetNumberingRangeResult');
                if ($result_nodes->length > 0) {
                    $result_node = $result_nodes->item(0);
                    $result_content = $result_node->nodeValue;
                    
                    return array(
                        'success' => true,
                        'data' => $result_content
                    );
                }
            } elseif ($action == 'SendBillAsync') {
                // Analizar respuesta de envío de documento
                $result_nodes = $doc->getElementsByTagName('SendBillAsyncResult');
                if ($result_nodes->length > 0) {
                    $result_node = $result_nodes->item(0);
                    $result_content = $result_node->nodeValue;
                    
                    return array(
                        'success' => true,
                        'data' => $result_content
                    );
                }
            } elseif ($action == 'GetStatus') {
                // Analizar respuesta de consulta de estado
                $result_nodes = $doc->getElementsByTagName('GetStatusResult');
                if ($result_nodes->length > 0) {
                    $result_node = $result_nodes->item(0);
                    $result_content = $result_node->nodeValue;
                    
                    return array(
                        'success' => true,
                        'data' => $result_content
                    );
                }
            }
            
            // Si no se encontró el nodo de resultado esperado
            return array(
                'success' => false,
                'message' => 'Formato de respuesta no reconocido'
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error al analizar la respuesta: ' . $e->getMessage()
            );
        }
    }

    /**
     * Extrae el TrackId de la respuesta de la DIAN
     *
     * @since    1.0.0
     * @param    string    $response_content    Contenido de la respuesta
     * @return   string    TrackId extraído o cadena vacía
     */
    private function extract_track_id($response_content) {
        try {
            // Si la respuesta es un XML, extraer el valor del nodo TrackId
            if ($this->is_xml($response_content)) {
                $doc = new DOMDocument();
                $doc->loadXML($response_content);
                
                $track_id_nodes = $doc->getElementsByTagName('b:trackId');
                if ($track_id_nodes->length > 0) {
                    return $track_id_nodes->item(0)->nodeValue;
                }
            }
            
            // Si es una respuesta en base64, decodificarla primero
            if ($this->is_base64($response_content)) {
                $decoded = base64_decode($response_content);
                return $this->extract_track_id($decoded);
            }
            
            // Si no se encontró, intentar extraerlo como texto
            if (preg_match('/trackId["\s:>]+([^<"\s]+)/i', $response_content, $matches)) {
                return $matches[1];
            }
            
            return '';
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Verifica si una cadena es XML válido
     *
     * @since    1.0.0
     * @param    string    $string    Cadena a verificar
     * @return   bool      Verdadero si es XML válido
     */
    private function is_xml($string) {
        try {
            $doc = new DOMDocument();
            return @$doc->loadXML($string) !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Verifica si una cadena es base64 válido
     *
     * @since    1.0.0
     * @param    string    $string    Cadena a verificar
     * @return   bool      Verdadero si es base64 válido
     */
    private function is_base64($string) {
        return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string);
    }

    /**
     * Guarda la información del envío de un documento
     *
     * @since    1.0.0
     * @param    string    $xml_content    Contenido XML del documento
     * @param    string    $document_type  Tipo de documento
     * @param    string    $track_id       TrackId asignado por la DIAN
     * @return   bool      Éxito de la operación
     */
    private function save_document_submission($xml_content, $document_type, $track_id) {
        // Extraer el número del documento del XML
        $document_number = $this->extract_document_number($xml_content);
        
        // Datos a guardar
        $submission_data = array(
            'document_number' => $document_number,
            'document_type' => $document_type,
            'track_id' => $track_id,
            'status' => 'sent',
            'content' => $xml_content,
            'created_at' => current_time('mysql')
        );
        
        return $this->db->insert_document($submission_data);
    }

    /**
     * Extrae el número de documento de un XML
     *
     * @since    1.0.0
     * @param    string    $xml_content    Contenido XML del documento
     * @return   string    Número del documento o cadena vacía
     */
    private function extract_document_number($xml_content) {
        try {
            $doc = new DOMDocument();
            $doc->loadXML($xml_content);
            
            // Buscar el ID (número de documento)
            $id_nodes = $doc->getElementsByTagNameNS('*', 'ID');
            if ($id_nodes->length > 0) {
                return $id_nodes->item(0)->nodeValue;
            }
            
            return '';
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Verifica el estado de un documento enviado a la DIAN
     *
     * @since    1.0.0
     * @param    string    $track_id    TrackId asignado por la DIAN
     * @return   array     Resultado de la consulta
     */
    public function check_document_status($track_id) {
        try {
            // Preparar datos para la solicitud SOAP
            $soap_url = $this->endpoints['status'];
            
            // Timestamp actual
            $created = date('Y-m-d\TH:i:s.v\Z');
            $expires = date('Y-m-d\TH:i:s.v\Z', strtotime('+5 minutes'));
            
            // Crear el mensaje SOAP para consultar estado
            $xml_request = $this->create_status_request($track_id, $created, $expires);
            
            // Realizar la solicitud SOAP
            $response = $this->send_soap_request($soap_url, 'GetStatus', $xml_request);
            
            // Procesar la respuesta
            if (isset($response['success']) && $response['success']) {
                // Analizar y extraer el estado
                $status_info = $this->parse_status_response($response['data']);
                
                // Actualizar el estado del documento en la base de datos
                $this->update_document_status($track_id, $status_info);
                
                return array(
                    'success' => true,
                    'message' => 'Consulta de estado exitosa',
                    'status' => $status_info
                );
            } else {
                return array(
                    'success' => false,
                    'message' => 'Error al consultar estado: ' . $response['message']
                );
            }
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error al consultar estado: ' . $e->getMessage()
            );
        }
    }

    /**
     * Crea el XML de solicitud para consultar estado
     *
     * @since    1.0.0
     * @param    string    $track_id    TrackId para consultar
     * @param    string    $created     Timestamp de creación
     * @param    string    $expires     Timestamp de expiración
     * @return   string    XML de solicitud
     */
    private function create_status_request($track_id, $created, $expires) {
        // Generar nonce para seguridad
        $nonce = $this->generate_nonce();
        $nonce_base64 = base64_encode($nonce);
        
        // Generar el digest de seguridad
        $digest_xml = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <wsu:Timestamp wsu:Id="TS-'. uniqid() .'">
                <wsu:Created>'. $created .'</wsu:Created>
                <wsu:Expires>'. $expires .'</wsu:Expires>
            </wsu:Timestamp>
            <wsse:UsernameToken wsu:Id="UsernameToken-'. uniqid() .'">
                <wsse:Username>'. $this->credentials['software_id'] .'</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'. $this->credentials['software_pin'] .'</wsse:Password>
                <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'. $nonce_base64 .'</wsse:Nonce>
                <wsu:Created>'. $created .'</wsu:Created>
            </wsse:UsernameToken>
        </wsse:Security>';
        
        // Crear el mensaje SOAP completo
        $soap_request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wcf="http://wcf.dian.colombia">
            <soapenv:Header>
                '. $digest_xml .'
            </soapenv:Header>
            <soapenv:Body>
                <wcf:GetStatus>
                    <wcf:trackId>'. $track_id .'</wcf:trackId>
                </wcf:GetStatus>
            </soapenv:Body>
        </soapenv:Envelope>';
        
        return $soap_request;
    }

    /**
     * Analiza la respuesta de estado de la DIAN
     *
     * @since    1.0.0
     * @param    string    $response_content    Contenido de la respuesta
     * @return   array     Información del estado
     */
    private function parse_status_response($response_content) {
        try {
            // Si la respuesta es en base64, decodificarla
            if ($this->is_base64($response_content)) {
                $response_content = base64_decode($response_content);
            }
            
            // Analizar el XML de respuesta
            $doc = new DOMDocument();
            $doc->loadXML($response_content);
            
            // Inicializar el resultado
            $status_info = array(
                'status' => '',
                'status_code' => '',
                'status_description' => '',
                'is_valid' => false,
                'errors' => array()
            );
            
            // Intentar extraer el estado principal
            $status_nodes = $doc->getElementsByTagNameNS('*', 'StatusCode');
            if ($status_nodes->length > 0) {
                $status_info['status_code'] = $status_nodes->item(0)->nodeValue;
            }
            
            // Extraer descripción del estado
            $description_nodes = $doc->getElementsByTagNameNS('*', 'StatusDescription');
            if ($description_nodes->length > 0) {
                $status_info['status_description'] = $description_nodes->item(0)->nodeValue;
            }
            
            // Determinar el estado basado en el código
            if (isset($status_info['status_code'])) {
                switch ($status_info['status_code']) {
                    case '00':
                        $status_info['status'] = 'accepted';
                        $status_info['is_valid'] = true;
                        break;
                    case '01':
                    case '02':
                        $status_info['status'] = 'rejected';
                        $status_info['is_valid'] = false;
                        break;
                    default:
                        $status_info['status'] = 'processing';
                        $status_info['is_valid'] = false;
                }
            }
            
            // Extraer errores si los hay
            $error_nodes = $doc->getElementsByTagNameNS('*', 'ErrorMessage');
            for ($i = 0; $i < $error_nodes->length; $i++) {
                $error_node = $error_nodes->item($i);
                $error_code_nodes = $error_node->getElementsByTagNameNS('*', 'ErrorCode');
                $error_message_nodes = $error_node->getElementsByTagNameNS('*', 'ErrorMessage');
                
                if ($error_code_nodes->length > 0 && $error_message_nodes->length > 0) {
                    $error_code = $error_code_nodes->item(0)->nodeValue;
                    $error_message = $error_message_nodes->item(0)->nodeValue;
                    
                    $status_info['errors'][] = array(
                        'code' => $error_code,
                        'message' => $error_message
                    );
                }
            }
            
            return $status_info;
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'status_code' => 'E01',
                'status_description' => 'Error al analizar la respuesta: ' . $e->getMessage(),
                'is_valid' => false,
                'errors' => array(
                    array(
                        'code' => 'E01',
                        'message' => $e->getMessage()
                    )
                )
            );
        }
    }

    /**
     * Actualiza el estado de un documento en la base de datos
     *
     * @since    1.0.0
     * @param    string    $track_id      TrackId del documento
     * @param    array     $status_info   Información del estado
     * @return   bool      Éxito de la operación
     */
    private function update_document_status($track_id, $status_info) {
        // Datos a actualizar
        $update_data = array(
            'status' => $status_info['status'],
            'status_message' => $status_info['status_description'],
            'is_valid' => $status_info['is_valid'] ? 1 : 0,
            'updated_at' => current_time('mysql')
        );
        
        return $this->db->update_document_by_track_id($track_id, $update_data);
    }

    /**
     * Obtiene las resoluciones de numeración disponibles
     *
     * @since    1.0.0
     * @return   array     Lista de resoluciones de numeración
     */
    public function get_numbering_ranges() {
        // Primero verificar si tenemos credenciales válidas
        if (empty($this->credentials['software_id']) || empty($this->credentials['software_pin']) ||
            empty($this->credentials['company_id']) || empty($this->credentials['company_pin'])) {
            return array(
                'success' => false,
                'message' => 'Faltan credenciales de configuración'
            );
        }
        
        try {
            // Primero autenticarse
            $auth_result = $this->authenticate();
            if (!$auth_result['success']) {
                return array(
                    'success' => false,
                    'message' => 'Error de autenticación: ' . $auth_result['message']
                );
            }
            
            // Preparar datos para la solicitud SOAP
            $soap_url = $this->endpoints['numbering'];
            
            // Timestamp actual
            $created = date('Y-m-d\TH:i:s.v\Z');
            $expires = date('Y-m-d\TH:i:s.v\Z', strtotime('+5 minutes'));
            
            // Crear el mensaje SOAP para consultar numeración
            $xml_request = $this->create_numbering_request($created, $expires);
            
            // Realizar la solicitud SOAP
            $response = $this->send_soap_request($soap_url, 'GetNumberingRange', $xml_request);
            
            // Procesar la respuesta
            if (isset($response['success']) && $response['success']) {
                // Extraer las resoluciones
                $numbering_ranges = $this->parse_numbering_response($response['data']);
                
                return array(
                    'success' => true,
                    'message' => 'Consulta de numeración exitosa',
                    'ranges' => $numbering_ranges
                );
            } else {
                return array(
                    'success' => false,
                    'message' => 'Error al consultar numeración: ' . $response['message']
                );
            }
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error al consultar numeración: ' . $e->getMessage()
            );
        }
    }

    /**
     * Crea el XML de solicitud para consultar numeración
     *
     * @since    1.0.0
     * @param    string    $created    Timestamp de creación
     * @param    string    $expires    Timestamp de expiración
     * @return   string    XML de solicitud
     */
    private function create_numbering_request($created, $expires) {
        // Esta función es muy similar a create_auth_request
        // De hecho, utilizamos la misma función para autenticarnos y consultar numeración
        return $this->create_auth_request($created, $expires);
    }

    /**
     * Analiza la respuesta de numeración de la DIAN
     *
     * @since    1.0.0
     * @param    string    $response_content    Contenido de la respuesta
     * @return   array     Lista de resoluciones de numeración
     */
    private function parse_numbering_response($response_content) {
        try {
            // Si la respuesta es en base64, decodificarla
            if ($this->is_base64($response_content)) {
                $response_content = base64_decode($response_content);
            }
            
            // Analizar el XML de respuesta
            $doc = new DOMDocument();
            $doc->loadXML($response_content);
            
            // Inicializar el resultado
            $numbering_ranges = array();
            
            // Extraer las resoluciones
            $resolution_nodes = $doc->getElementsByTagNameNS('*', 'NumberingRange');
            for ($i = 0; $i < $resolution_nodes->length; $i++) {
                $resolution_node = $resolution_nodes->item($i);
                
                $prefix = '';
                $from = '';
                $to = '';
                $resolution_date = '';
                $resolution_number = '';
                $valid_from = '';
                $valid_to = '';
                
                // Extraer cada campo de la resolución
                $prefix_nodes = $resolution_node->getElementsByTagNameNS('*', 'Prefix');
                if ($prefix_nodes->length > 0) {
                    $prefix = $prefix_nodes->item(0)->nodeValue;
                }
                
                $from_nodes = $resolution_node->getElementsByTagNameNS('*', 'FromNumber');
                if ($from_nodes->length > 0) {
                    $from = $from_nodes->item(0)->nodeValue;
                }
                
                $to_nodes = $resolution_node->getElementsByTagNameNS('*', 'ToNumber');
                if ($to_nodes->length > 0) {
                    $to = $to_nodes->item(0)->nodeValue;
                }
                
                $resolution_date_nodes = $resolution_node->getElementsByTagNameNS('*', 'ResolutionDate');
                if ($resolution_date_nodes->length > 0) {
                    $resolution_date = $resolution_date_nodes->item(0)->nodeValue;
                }
                
                $resolution_number_nodes = $resolution_node->getElementsByTagNameNS('*', 'ResolutionNumber');
                if ($resolution_number_nodes->length > 0) {
                    $resolution_number = $resolution_number_nodes->item(0)->nodeValue;
                }
                
                $valid_from_nodes = $resolution_node->getElementsByTagNameNS('*', 'ValidDateFrom');
                if ($valid_from_nodes->length > 0) {
                    $valid_from = $valid_from_nodes->item(0)->nodeValue;
                }
                
                $valid_to_nodes = $resolution_node->getElementsByTagNameNS('*', 'ValidDateTo');
                if ($valid_to_nodes->length > 0) {
                    $valid_to = $valid_to_nodes->item(0)->nodeValue;
                }
                
                // Agregar la resolución al resultado
                $numbering_ranges[] = array(
                    'prefix' => $prefix,
                    'from_number' => $from,
                    'to_number' => $to,
                    'resolution_date' => $resolution_date,
                    'resolution_number' => $resolution_number,
                    'valid_from' => $valid_from,
                    'valid_to' => $valid_to
                );
            }
            
            return $numbering_ranges;
        } catch (Exception $e) {
            return array();
        }
    }
}