<?php
/**
 * Clase para la generación de documentos XML según especificaciones DIAN
 *
 * Esta clase se encarga de generar documentos XML para facturación electrónica
 * siguiendo el estándar UBL 2.1 requerido por la DIAN en Colombia.
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
 * Clase para la generación de documentos XML
 *
 * Implementa la lógica para crear documentos XML para facturación electrónica
 * de acuerdo a los requisitos de la DIAN.
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/includes
 * @author     Tu Nombre <email@example.com>
 */
class DIAN_API_XML_Generator {

    /**
     * Instancia de la clase de base de datos
     *
     * @since    1.0.0
     * @access   private
     * @var      DIAN_API_DB    $db    Instancia de la clase de base de datos
     */
    private $db;

    /**
     * Constructor de la clase
     *
     * @since    1.0.0
     * @param    DIAN_API_DB    $db    Instancia de la clase de base de datos
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Genera un documento XML de factura electrónica
     *
     * @since    1.0.0
     * @param    array    $invoice_data    Datos de la factura
     * @return   string   Documento XML generado
     */
    public function generate_invoice_xml($invoice_data) {
        // Validar los datos de entrada
        $validation = $this->validate_invoice_data($invoice_data);
        if (!$validation['is_valid']) {
            return array(
                'success' => false,
                'message' => 'Error de validación: ' . $validation['message']
            );
        }

        // Crear el documento XML
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // Crear el elemento raíz (Invoice)
        $invoice = $dom->createElement('Invoice');
        $dom->appendChild($invoice);

        // Añadir los namespaces requeridos por la DIAN
        $invoice->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $invoice->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
        $invoice->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $invoice->setAttribute('xmlns:sts', 'urn:dian:gov:co:facturaelectronica:Structures-2-1');
        $invoice->setAttribute('xmlns:xades', 'http://uri.etsi.org/01903/v1.3.2#');
        $invoice->setAttribute('xmlns:xades141', 'http://uri.etsi.org/01903/v1.4.1#');
        $invoice->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        // Añadir las extensiones UBL (para firma electrónica)
        $this->add_ubl_extensions($dom, $invoice);

        // Añadir los elementos obligatorios según UBL 2.1 y DIAN
        $this->add_ubl_version($dom, $invoice, '2.1');
        $this->add_customization_id($dom, $invoice, '10');
        $this->add_profile_id($dom, $invoice, 'DIAN 2.1');
        $this->add_id($dom, $invoice, $invoice_data['invoice_number']);
        $this->add_issue_date($dom, $invoice, $invoice_data['issue_date']);
        $this->add_issue_time($dom, $invoice, $invoice_data['issue_time']);
        $this->add_due_date($dom, $invoice, $invoice_data['due_date']);
        $this->add_invoice_type_code($dom, $invoice, '01'); // 01 = Factura de Venta
        $this->add_note($dom, $invoice, $invoice_data['note']);
        $this->add_currency_code($dom, $invoice, 'COP');
        
        // Información del emisor
        $this->add_accounting_supplier_party($dom, $invoice, $invoice_data['supplier']);
        
        // Información del receptor
        $this->add_accounting_customer_party($dom, $invoice, $invoice_data['customer']);
        
        // Información de impuestos
        $this->add_tax_information($dom, $invoice, $invoice_data['taxes']);
        
        // Líneas de factura (productos/servicios)
        $this->add_invoice_lines($dom, $invoice, $invoice_data['items']);
        
        // Totales legales
        $this->add_legal_monetary_total($dom, $invoice, $invoice_data['monetary_totals']);

        // Generar el XML como string
        $xml = $dom->saveXML();
        
        // Guardar el XML en la base de datos si es necesario
        if (isset($invoice_data['save_to_db']) && $invoice_data['save_to_db']) {
            $this->save_xml_to_db($invoice_data['invoice_number'], $xml);
        }
        
        return array(
            'success' => true,
            'xml' => $xml
        );
    }

    /**
     * Valida los datos de la factura
     *
     * @since    1.0.0
     * @param    array    $invoice_data    Datos de la factura
     * @return   array    Resultado de la validación
     */
    private function validate_invoice_data($invoice_data) {
        // Campos requeridos básicos
        $required_fields = array(
            'invoice_number',
            'issue_date',
            'issue_time',
            'due_date',
            'supplier',
            'customer',
            'items',
            'monetary_totals'
        );

        foreach ($required_fields as $field) {
            if (!isset($invoice_data[$field]) || empty($invoice_data[$field])) {
                return array(
                    'is_valid' => false,
                    'message' => "El campo {$field} es requerido."
                );
            }
        }

        // Validación específica para el proveedor (emisor)
        $supplier_fields = array(
            'identification_number',
            'name',
            'tax_level_code',
            'address',
            'city',
            'postal_code',
            'department',
            'country_code'
        );

        foreach ($supplier_fields as $field) {
            if (!isset($invoice_data['supplier'][$field]) || empty($invoice_data['supplier'][$field])) {
                return array(
                    'is_valid' => false,
                    'message' => "El campo supplier.{$field} es requerido."
                );
            }
        }

        // Validación específica para el cliente (receptor)
        $customer_fields = array(
            'identification_number',
            'name',
            'address',
            'city',
            'postal_code',
            'department',
            'country_code'
        );

        foreach ($customer_fields as $field) {
            if (!isset($invoice_data['customer'][$field]) || empty($invoice_data['customer'][$field])) {
                return array(
                    'is_valid' => false,
                    'message' => "El campo customer.{$field} es requerido."
                );
            }
        }

        // Validación de items (al menos uno requerido)
        if (count($invoice_data['items']) < 1) {
            return array(
                'is_valid' => false,
                'message' => "Se requiere al menos un item en la factura."
            );
        }

        // Validación de totales monetarios
        $total_fields = array(
            'line_extension_amount',
            'tax_exclusive_amount',
            'tax_inclusive_amount',
            'payable_amount'
        );

        foreach ($total_fields as $field) {
            if (!isset($invoice_data['monetary_totals'][$field])) {
                return array(
                    'is_valid' => false,
                    'message' => "El campo monetary_totals.{$field} es requerido."
                );
            }
        }

        // Si llega hasta aquí, los datos son válidos
        return array(
            'is_valid' => true,
            'message' => 'Datos válidos'
        );
    }

    /**
     * Añade el elemento UBLExtensions para la firma electrónica
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom        Documento DOM
     * @param    DOMElement     $invoice    Elemento raíz de la factura
     */
    private function add_ubl_extensions($dom, $invoice) {
        $extensions = $dom->createElement('ext:UBLExtensions');
        $invoice->appendChild($extensions);
        
        $extension = $dom->createElement('ext:UBLExtension');
        $extensions->appendChild($extension);
        
        $ext_content = $dom->createElement('ext:ExtensionContent');
        $extension->appendChild($ext_content);
        
        // Espacio reservado para la firma digital
        // Este nodo será llenado posteriormente por el proceso de firma
        $signature = $dom->createElement('ds:Signature');
        $signature->setAttribute('Id', 'xmldsig');
        $ext_content->appendChild($signature);
    }

    /**
     * Añade la versión UBL
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom        Documento DOM
     * @param    DOMElement     $invoice    Elemento raíz de la factura
     * @param    string         $version    Versión UBL
     */
    private function add_ubl_version($dom, $invoice, $version) {
        $ubl_version = $dom->createElement('cbc:UBLVersionID', $version);
        $invoice->appendChild($ubl_version);
    }

    /**
     * Añade el ID de personalización
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom           Documento DOM
     * @param    DOMElement     $invoice       Elemento raíz de la factura
     * @param    string         $customization ID de personalización
     */
    private function add_customization_id($dom, $invoice, $customization) {
        $custom_id = $dom->createElement('cbc:CustomizationID', $customization);
        $invoice->appendChild($custom_id);
    }

    /**
     * Añade el ID del perfil DIAN
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom        Documento DOM
     * @param    DOMElement     $invoice    Elemento raíz de la factura
     * @param    string         $profile    ID del perfil
     */
    private function add_profile_id($dom, $invoice, $profile) {
        $profile_id = $dom->createElement('cbc:ProfileID', $profile);
        $invoice->appendChild($profile_id);
    }

    /**
     * Añade el número de factura
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom             Documento DOM
     * @param    DOMElement     $invoice         Elemento raíz de la factura
     * @param    string         $invoice_number  Número de factura
     */
    private function add_id($dom, $invoice, $invoice_number) {
        $id = $dom->createElement('cbc:ID', $invoice_number);
        $invoice->appendChild($id);
    }

    /**
     * Añade la fecha de emisión
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom          Documento DOM
     * @param    DOMElement     $invoice      Elemento raíz de la factura
     * @param    string         $issue_date   Fecha de emisión (YYYY-MM-DD)
     */
    private function add_issue_date($dom, $invoice, $issue_date) {
        $date = $dom->createElement('cbc:IssueDate', $issue_date);
        $invoice->appendChild($date);
    }

    /**
     * Añade la hora de emisión
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom          Documento DOM
     * @param    DOMElement     $invoice      Elemento raíz de la factura
     * @param    string         $issue_time   Hora de emisión (HH:MM:SS)
     */
    private function add_issue_time($dom, $invoice, $issue_time) {
        $time = $dom->createElement('cbc:IssueTime', $issue_time);
        $invoice->appendChild($time);
    }

    /**
     * Añade la fecha de vencimiento
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom         Documento DOM
     * @param    DOMElement     $invoice     Elemento raíz de la factura
     * @param    string         $due_date    Fecha de vencimiento (YYYY-MM-DD)
     */
    private function add_due_date($dom, $invoice, $due_date) {
        $date = $dom->createElement('cbc:DueDate', $due_date);
        $invoice->appendChild($date);
    }

    /**
     * Añade el código de tipo de factura
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom             Documento DOM
     * @param    DOMElement     $invoice         Elemento raíz de la factura
     * @param    string         $invoice_type    Código de tipo de factura
     */
    private function add_invoice_type_code($dom, $invoice, $invoice_type) {
        $type = $dom->createElement('cbc:InvoiceTypeCode', $invoice_type);
        $invoice->appendChild($type);
    }

    /**
     * Añade una nota a la factura
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom        Documento DOM
     * @param    DOMElement     $invoice    Elemento raíz de la factura
     * @param    string         $note       Texto de la nota
     */
    private function add_note($dom, $invoice, $note) {
        if (!empty($note)) {
            $note_element = $dom->createElement('cbc:Note', $note);
            $invoice->appendChild($note_element);
        }
    }

    /**
     * Añade el código de moneda
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom            Documento DOM
     * @param    DOMElement     $invoice        Elemento raíz de la factura
     * @param    string         $currency_code  Código de moneda (ISO 4217)
     */
    private function add_currency_code($dom, $invoice, $currency_code) {
        $currency = $dom->createElement('cbc:DocumentCurrencyCode', $currency_code);
        $invoice->appendChild($currency);
    }

    /**
     * Añade la información del proveedor (emisor)
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom        Documento DOM
     * @param    DOMElement     $invoice    Elemento raíz de la factura
     * @param    array          $supplier   Datos del proveedor
     */
    private function add_accounting_supplier_party($dom, $invoice, $supplier) {
        $supplier_party = $dom->createElement('cac:AccountingSupplierParty');
        $invoice->appendChild($supplier_party);
        
        // Tipo de organización (1 = Persona jurídica, 2 = Persona natural)
        $party_type = $dom->createElement('cbc:AdditionalAccountID', $supplier['party_type'] ?? '1');
        $supplier_party->appendChild($party_type);
        
        $party = $dom->createElement('cac:Party');
        $supplier_party->appendChild($party);
        
        // Información de identificación fiscal
        $party_tax_scheme = $dom->createElement('cac:PartyTaxScheme');
        $party->appendChild($party_tax_scheme);
        
        $registration_name = $dom->createElement('cbc:RegistrationName', $supplier['name']);
        $party_tax_scheme->appendChild($registration_name);
        
        $company_id = $dom->createElement('cbc:CompanyID', $supplier['identification_number']);
        $company_id->setAttribute('schemeAgencyID', '195');
        $company_id->setAttribute('schemeAgencyName', 'CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)');
        $company_id->setAttribute('schemeID', $this->calculate_check_digit($supplier['identification_number']));
        $company_id->setAttribute('schemeName', '31'); // 31 = NIT
        $party_tax_scheme->appendChild($company_id);
        
        $tax_level_code = $dom->createElement('cbc:TaxLevelCode', $supplier['tax_level_code']);
        $tax_level_code->setAttribute('listName', 'No obligado a registro');
        $party_tax_scheme->appendChild($tax_level_code);
        
        // Dirección fiscal
        $registration_address = $dom->createElement('cac:RegistrationAddress');
        $party_tax_scheme->appendChild($registration_address);
        
        $address_id = $dom->createElement('cbc:ID', $supplier['city']);
        $registration_address->appendChild($address_id);
        
        $city_name = $dom->createElement('cbc:CityName', $supplier['city_name'] ?? $supplier['city']);
        $registration_address->appendChild($city_name);
        
        $postal_zone = $dom->createElement('cbc:PostalZone', $supplier['postal_code']);
        $registration_address->appendChild($postal_zone);
        
        $department = $dom->createElement('cbc:CountrySubentity', $supplier['department']);
        $registration_address->appendChild($department);
        
        $department_code = $dom->createElement('cbc:CountrySubentityCode', $supplier['department_code'] ?? $supplier['department']);
        $registration_address->appendChild($department_code);
        
        $address_line = $dom->createElement('cac:AddressLine');
        $registration_address->appendChild($address_line);
        
        $line = $dom->createElement('cbc:Line', $supplier['address']);
        $address_line->appendChild($line);
        
        $country = $dom->createElement('cac:Country');
        $registration_address->appendChild($country);
        
        $country_code = $dom->createElement('cbc:IdentificationCode', $supplier['country_code']);
        $country->appendChild($country_code);
        
        $country_name = $dom->createElement('cbc:Name', $supplier['country_name'] ?? 'Colombia');
        $country_name->setAttribute('languageID', 'es');
        $country->appendChild($country_name);
        
        // Régimen fiscal
        $tax_scheme = $dom->createElement('cac:TaxScheme');
        $party_tax_scheme->appendChild($tax_scheme);
        
        $tax_scheme_id = $dom->createElement('cbc:ID', '01'); // 01 = IVA
        $tax_scheme->appendChild($tax_scheme_id);
        
        $tax_scheme_name = $dom->createElement('cbc:Name', 'IVA');
        $tax_scheme->appendChild($tax_scheme_name);
    }

    /**
     * Añade la información del cliente (receptor)
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom        Documento DOM
     * @param    DOMElement     $invoice    Elemento raíz de la factura
     * @param    array          $customer   Datos del cliente
     */
    private function add_accounting_customer_party($dom, $invoice, $customer) {
        $customer_party = $dom->createElement('cac:AccountingCustomerParty');
        $invoice->appendChild($customer_party);
        
        // Tipo de organización (1 = Persona jurídica, 2 = Persona natural)
        $party_type = $dom->createElement('cbc:AdditionalAccountID', $customer['party_type'] ?? '1');
        $customer_party->appendChild($party_type);
        
        $party = $dom->createElement('cac:Party');
        $customer_party->appendChild($party);
        
        // Información de identificación fiscal
        $party_tax_scheme = $dom->createElement('cac:PartyTaxScheme');
        $party->appendChild($party_tax_scheme);
        
        $registration_name = $dom->createElement('cbc:RegistrationName', $customer['name']);
        $party_tax_scheme->appendChild($registration_name);
        
        $company_id = $dom->createElement('cbc:CompanyID', $customer['identification_number']);
        $company_id->setAttribute('schemeAgencyID', '195');
        $company_id->setAttribute('schemeAgencyName', 'CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)');
        
        // Solo se añade el dígito de verificación si es un NIT
        if (isset($customer['id_type']) && $customer['id_type'] == '31') {
            $company_id->setAttribute('schemeID', $this->calculate_check_digit($customer['identification_number']));
        }
        
        $company_id->setAttribute('schemeName', $customer['id_type'] ?? '13'); // 13 = Cédula, 31 = NIT
        $party_tax_scheme->appendChild($company_id);
        
        $tax_level_code = $dom->createElement('cbc:TaxLevelCode', $customer['tax_level_code'] ?? 'R-99-PN');
        $tax_level_code->setAttribute('listName', 'No obligado a registro');
        $party_tax_scheme->appendChild($tax_level_code);
        
        // Dirección fiscal
        $registration_address = $dom->createElement('cac:RegistrationAddress');
        $party_tax_scheme->appendChild($registration_address);
        
        $address_id = $dom->createElement('cbc:ID', $customer['city']);
        $registration_address->appendChild($address_id);
        
        $city_name = $dom->createElement('cbc:CityName', $customer['city_name'] ?? $customer['city']);
        $registration_address->appendChild($city_name);
        
        $postal_zone = $dom->createElement('cbc:PostalZone', $customer['postal_code']);
        $registration_address->appendChild($postal_zone);
        
        $department = $dom->createElement('cbc:CountrySubentity', $customer['department']);
        $registration_address->appendChild($department);
        
        $department_code = $dom->createElement('cbc:CountrySubentityCode', $customer['department_code'] ?? $customer['department']);
        $registration_address->appendChild($department_code);
        
        $address_line = $dom->createElement('cac:AddressLine');
        $registration_address->appendChild($address_line);
        
        $line = $dom->createElement('cbc:Line', $customer['address']);
        $address_line->appendChild($line);
        
        $country = $dom->createElement('cac:Country');
        $registration_address->appendChild($country);
        
        $country_code = $dom->createElement('cbc:IdentificationCode', $customer['country_code']);
        $country->appendChild($country_code);
        
        $country_name = $dom->createElement('cbc:Name', $customer['country_name'] ?? 'Colombia');
        $country_name->setAttribute('languageID', 'es');
        $country->appendChild($country_name);
        
        // Régimen fiscal
        $tax_scheme = $dom->createElement('cac:TaxScheme');
        $party_tax_scheme->appendChild($tax_scheme);
        
        $tax_scheme_id = $dom->createElement('cbc:ID', '01'); // 01 = IVA
        $tax_scheme->appendChild($tax_scheme_id);
        
        $tax_scheme_name = $dom->createElement('cbc:Name', 'IVA');
        $tax_scheme->appendChild($tax_scheme_name);
    }

    /**
     * Añade la información de impuestos
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom        Documento DOM
     * @param    DOMElement     $invoice    Elemento raíz de la factura
     * @param    array          $taxes      Datos de impuestos
     */
    private function add_tax_information($dom, $invoice, $taxes) {
        // Agrupamos los impuestos por tipo
        $tax_groups = array();
        foreach ($taxes as $tax) {
            $tax_type = $tax['tax_type'];
            if (!isset($tax_groups[$tax_type])) {
                $tax_groups[$tax_type] = array(
                    'tax_amount' => 0,
                    'taxable_amount' => 0,
                    'percent' => $tax['percent'],
                    'details' => array()
                );
            }
            $tax_groups[$tax_type]['tax_amount'] += $tax['tax_amount'];
            $tax_groups[$tax_type]['taxable_amount'] += $tax['taxable_amount'];
            $tax_groups[$tax_type]['details'][] = $tax;
        }
        
        // Añadimos cada grupo de impuestos al XML
        foreach ($tax_groups as $tax_type => $group) {
            $tax_total = $dom->createElement('cac:TaxTotal');
            $invoice->appendChild($tax_total);
            
            $tax_amount = $dom->createElement('cbc:TaxAmount', number_format($group['tax_amount'], 2, '.', ''));
            $tax_amount->setAttribute('currencyID', 'COP');
            $tax_total->appendChild($tax_amount);
            
            $tax_subtotal = $dom->createElement('cac:TaxSubtotal');
            $tax_total->appendChild($tax_subtotal);
            
            $taxable_amount = $dom->createElement('cbc:TaxableAmount', number_format($group['taxable_amount'], 2, '.', ''));
            $taxable_amount->setAttribute('currencyID', 'COP');
            $tax_subtotal->appendChild($taxable_amount);
            
            $subtotal_tax_amount = $dom->createElement('cbc:TaxAmount', number_format($group['tax_amount'], 2, '.', ''));
            $subtotal_tax_amount->setAttribute('currencyID', 'COP');
            $tax_subtotal->appendChild($subtotal_tax_amount);
            
            $percent = $dom->createElement('cbc:Percent', $group['percent']);
            $tax_subtotal->appendChild($percent);
            
            $tax_category = $dom->createElement('cac:TaxCategory');
            $tax_subtotal->appendChild($tax_category);
            
            $tax_scheme = $dom->createElement('cac:TaxScheme');
            $tax_category->appendChild($tax_scheme);
            
            $tax_id = $dom->createElement('cbc:ID', $tax_type);
            $tax_scheme->appendChild($tax_id);
            
            // Mapeo de códigos de impuestos a nombres
            $tax_names = array(
                '01' => 'IVA',
                '02' => 'IC',
                '03' => 'ICA',
                '04' => 'INC',
                '05' => 'ReteIVA',
                '06' => 'ReteFuente',
                '07' => 'ReteICA'
            );
            
            $tax_name = $dom->createElement('cbc:Name', $tax_names[$tax_type] ?? 'Otro');
            $tax_scheme->appendChild($tax_name);
        }
    }

    /**
     * Añade las líneas de factura (productos/servicios)
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom        Documento DOM
     * @param    DOMElement     $invoice    Elemento raíz de la factura
     * @param    array          $items      Líneas de factura
     */
    private function add_invoice_lines($dom, $invoice, $items) {
        $line_number = 1;
        
        foreach ($items as $item) {
            $invoice_line = $dom->createElement('cac:InvoiceLine');
            $invoice->appendChild($invoice_line);
            
            $id = $dom->createElement('cbc:ID', $line_number);
            $invoice_line->appendChild($id);
            
            $invoiced_quantity = $dom->createElement('cbc:InvoicedQuantity', $item['quantity']);
            $invoiced_quantity->setAttribute('unitCode', $item['unit_code'] ?? 'EA'); // EA = Unidad
            $invoice_line->appendChild($invoiced_quantity);
            
            $line_extension_amount = $dom->createElement('cbc:LineExtensionAmount', number_format($item['line_extension_amount'], 2, '.', ''));
            $line_extension_amount->setAttribute('currencyID', 'COP');
            $invoice_line->appendChild($line_extension_amount);
            
            // Información de impuestos por línea
            if (isset($item['taxes']) && is_array($item['taxes'])) {
                foreach ($item['taxes'] as $tax) {
                    $tax_total = $dom->createElement('cac:TaxTotal');
                    $invoice_line->appendChild($tax_total);
                    
                    $tax_amount = $dom->createElement('cbc:TaxAmount', number_format($tax['tax_amount'], 2, '.', ''));
                    $tax_amount->setAttribute('currencyID', 'COP');
                    $tax_total->appendChild($tax_amount);
                    
                    $tax_subtotal = $dom->createElement('cac:TaxSubtotal');
                    $tax_total->appendChild($tax_subtotal);
                    
                    $taxable_amount = $dom->createElement('cbc:TaxableAmount', number_format($tax['taxable_amount'], 2, '.', ''));
                    $taxable_amount->setAttribute('currencyID', 'COP');
                    $tax_subtotal->appendChild($taxable_amount);
                    
                    $subtotal_tax_amount = $dom->createElement('cbc:TaxAmount', number_format($tax['tax_amount'], 2, '.', ''));
                    $subtotal_tax_amount->setAttribute('currencyID', 'COP');
                    $tax_subtotal->appendChild($subtotal_tax_amount);
                    
                    $percent = $dom->createElement('cbc:Percent', $tax['percent']);
                    $tax_subtotal->appendChild($percent);
                    
                    $tax_category = $dom->createElement('cac:TaxCategory');
                    $tax_subtotal->appendChild($tax_category);
                    
                    $tax_exemption_reason = $dom->createElement('cbc:TaxExemptionReasonCode', $tax['exemption_code'] ?? 'TAXEX-CO-01');
                    $tax_category->appendChild($tax_exemption_reason);
                    
                    $tax_scheme = $dom->createElement('cac:TaxScheme');
                    $tax_category->appendChild($tax_scheme);
                    
                    $tax_id = $dom->createElement('cbc:ID', $tax['tax_type']);
                    $tax_scheme->appendChild($tax_id);
                    
                    // Mapeo de códigos de impuestos a nombres
                    $tax_names = array(
                        '01' => 'IVA',
                        '02' => 'IC',
                        '03' => 'ICA',
                        '04' => 'INC',
                        '05' => 'ReteIVA',
                        '06' => 'ReteFuente',
                        '07' => 'ReteICA'
                    );
                    
                    $tax_name = $dom->createElement('cbc:Name', $tax_names[$tax['tax_type']] ?? 'Otro');
                    $tax_scheme->appendChild($tax_name);
                }
            }
            
            // Información del artículo
            $item_element = $dom->createElement('cac:Item');
            $invoice_line->appendChild($item_element);
            
            $description = $dom->createElement('cbc:Description', $item['description']);
            $item_element->appendChild($description);
            
            if (isset($item['code'])) {
                $sellers_item_id = $dom->createElement('cac:SellersItemIdentification');
                $item_element->appendChild($sellers_item_id);
                
                $id = $dom->createElement('cbc:ID', $item['code']);
                $sellers_item_id->appendChild($id);
            }
            
            // Precios
            $price = $dom->createElement('cac:Price');
            $invoice_line->appendChild($price);
            
            $price_amount = $dom->createElement('cbc:PriceAmount', number_format($item['unit_price'], 2, '.', ''));
            $price_amount->setAttribute('currencyID', 'COP');
            $price->appendChild($price_amount);
            
            $base_quantity = $dom->createElement('cbc:BaseQuantity', '1');
            $base_quantity->setAttribute('unitCode', $item['unit_code'] ?? 'EA');
            $price->appendChild($base_quantity);
            
            $line_number++;
        }
    }

    /**
     * Añade los totales legales de la factura
     *
     * @since    1.0.0
     * @param    DOMDocument    $dom                Documento DOM
     * @param    DOMElement     $invoice            Elemento raíz de la factura
     * @param    array          $monetary_totals    Totales monetarios
     */
    private function add_legal_monetary_total($dom, $invoice, $monetary_totals) {
        $legal_monetary_total = $dom->createElement('cac:LegalMonetaryTotal');
        $invoice->appendChild($legal_monetary_total);
        
        $line_extension_amount = $dom->createElement('cbc:LineExtensionAmount', number_format($monetary_totals['line_extension_amount'], 2, '.', ''));
        $line_extension_amount->setAttribute('currencyID', 'COP');
        $legal_monetary_total->appendChild($line_extension_amount);
        
        $tax_exclusive_amount = $dom->createElement('cbc:TaxExclusiveAmount', number_format($monetary_totals['tax_exclusive_amount'], 2, '.', ''));
        $tax_exclusive_amount->setAttribute('currencyID', 'COP');
        $legal_monetary_total->appendChild($tax_exclusive_amount);
        
        $tax_inclusive_amount = $dom->createElement('cbc:TaxInclusiveAmount', number_format($monetary_totals['tax_inclusive_amount'], 2, '.', ''));
        $tax_inclusive_amount->setAttribute('currencyID', 'COP');
        $legal_monetary_total->appendChild($tax_inclusive_amount);
        
        if (isset($monetary_totals['allowance_total_amount'])) {
            $allowance_total_amount = $dom->createElement('cbc:AllowanceTotalAmount', number_format($monetary_totals['allowance_total_amount'], 2, '.', ''));
            $allowance_total_amount->setAttribute('currencyID', 'COP');
            $legal_monetary_total->appendChild($allowance_total_amount);
        }
        
        if (isset($monetary_totals['charge_total_amount'])) {
            $charge_total_amount = $dom->createElement('cbc:ChargeTotalAmount', number_format($monetary_totals['charge_total_amount'], 2, '.', ''));
            $charge_total_amount->setAttribute('currencyID', 'COP');
            $legal_monetary_total->appendChild($charge_total_amount);
        }
        
        if (isset($monetary_totals['prepaid_amount'])) {
            $prepaid_amount = $dom->createElement('cbc:PrepaidAmount', number_format($monetary_totals['prepaid_amount'], 2, '.', ''));
            $prepaid_amount->setAttribute('currencyID', 'COP');
            $legal_monetary_total->appendChild($prepaid_amount);
        }
        
        $payable_amount = $dom->createElement('cbc:PayableAmount', number_format($monetary_totals['payable_amount'], 2, '.', ''));
        $payable_amount->setAttribute('currencyID', 'COP');
        $legal_monetary_total->appendChild($payable_amount);
    }

    /**
     * Calcula el dígito de verificación para NIT
     *
     * @since    1.0.0
     * @param    string    $nit    Número de NIT
     * @return   string    Dígito de verificación
     */
    private function calculate_check_digit($nit) {
        // Eliminar guiones y espacios
        $nit = preg_replace('/[^0-9]/', '', $nit);
        
        // Factores de multiplicación según algoritmo DIAN
        $factors = array(3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71);
        
        // Invertir el NIT para operar de derecha a izquierda
        $nit_reverse = strrev($nit);
        
        $sum = 0;
        for ($i = 0; $i < strlen($nit_reverse); $i++) {
            $sum += intval($nit_reverse[$i]) * $factors[$i];
        }
        
        $check_digit = $sum % 11;
        if ($check_digit > 1) {
            $check_digit = 11 - $check_digit;
        }
        
        return (string) $check_digit;
    }

    /**
     * Guarda el XML generado en la base de datos
     *
     * @since    1.0.0
     * @param    string    $invoice_number    Número de factura
     * @param    string    $xml               Contenido XML
     * @return   bool      Resultado de la operación
     */
    private function save_xml_to_db($invoice_number, $xml) {
        return $this->db->insert_document(
            array(
                'document_number' => $invoice_number,
                'document_type' => 'invoice',
                'content' => $xml,
                'status' => 'generated',
                'created_at' => current_time('mysql')
            )
        );
    }

    /**
     * Genera un documento XML de nota crédito
     *
     * @since    1.0.0
     * @param    array    $credit_note_data    Datos de la nota crédito
     * @return   array    Resultado con el XML generado
     */
    public function generate_credit_note_xml($credit_note_data) {
        // Implementación similar a generate_invoice_xml pero adaptada para notas crédito
        // TODO: Implementar cuando se requiera
        return array(
            'success' => false,
            'message' => 'Método no implementado'
        );
    }

    /**
     * Genera un documento XML de nota débito
     *
     * @since    1.0.0
     * @param    array    $debit_note_data    Datos de la nota débito
     * @return   array    Resultado con el XML generado
     */
    public function generate_debit_note_xml($debit_note_data) {
        // Implementación similar a generate_invoice_xml pero adaptada para notas débito
        // TODO: Implementar cuando se requiera
        return array(
            'success' => false,
            'message' => 'Método no implementado'
        );
    }
}