<?php
/**
 * Clase para manejar la capa de base de datos
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
 * Clase para manejar la capa de base de datos
 *
 * Esta clase maneja todas las operaciones de base de datos necesarias
 * para el funcionamiento del plugin de facturación electrónica DIAN.
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/includes
 * @author     Tu Nombre <email@example.com>
 */
class DIAN_API_DB {
    
    /**
     * Constructor
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Nada que inicializar aquí
    }
    
    /**
     * Crear tablas en la base de datos
     *
     * @since    1.0.0
     * @return   void
     */
    public function crear_tablas() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabla de API Keys
        $tabla_api_keys = $wpdb->prefix . 'dian_api_keys';
        $sql_api_keys = "CREATE TABLE IF NOT EXISTS $tabla_api_keys (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nombre varchar(100) NOT NULL,
            api_key varchar(64) NOT NULL,
            api_secret varchar(64) NOT NULL,
            estado varchar(20) NOT NULL DEFAULT 'activo',
            usuario_id bigint(20) NULL,
            permisos varchar(255) NULL,
            fecha_creacion datetime NOT NULL,
            fecha_actualizacion datetime NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY api_key (api_key)
        ) $charset_collate;";
        
        // Tabla de configuración
        $tabla_configuracion = $wpdb->prefix . 'dian_configuracion';
        $sql_configuracion = "CREATE TABLE IF NOT EXISTS $tabla_configuracion (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cliente_id varchar(100) NOT NULL,
            certificado_ruta text NOT NULL,
            certificado_clave varchar(255) NOT NULL,
            id_software varchar(100) NOT NULL,
            software_pin varchar(100) NOT NULL,
            tecnologia_firma varchar(20) NOT NULL DEFAULT 'sha1',
            url_ws_validacion text NOT NULL,
            url_ws_produccion text NOT NULL,
            modo_operacion varchar(20) NOT NULL DEFAULT 'habilitacion',
            test_set_id varchar(100) NULL,
            fecha_creacion datetime NOT NULL,
            fecha_actualizacion datetime NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY cliente_id (cliente_id)
        ) $charset_collate;";
        
        // Tabla de documentos electrónicos (facturas, notas crédito, notas débito)
        $tabla_documentos = $wpdb->prefix . 'dian_documentos';
        $sql_documentos = "CREATE TABLE IF NOT EXISTS $tabla_documentos (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cliente_id varchar(100) NOT NULL,
            tipo_documento varchar(20) NOT NULL,
            prefijo varchar(20) NULL,
            numero varchar(20) NOT NULL,
            emisor_nit varchar(20) NOT NULL,
            emisor_razon_social varchar(255) NOT NULL,
            receptor_documento varchar(20) NOT NULL,
            receptor_razon_social varchar(255) NOT NULL,
            fecha_emision datetime NOT NULL,
            fecha_vencimiento datetime NOT NULL,
            valor_sin_impuestos decimal(12,2) NOT NULL,
            valor_impuestos decimal(12,2) NOT NULL,
            valor_total decimal(12,2) NOT NULL,
            moneda varchar(3) NOT NULL DEFAULT 'COP',
            estado varchar(50) NOT NULL,
            cufe varchar(96) NULL,
            qr_data text NULL,
            cude varchar(96) NULL,
            archivo_xml text NULL,
            archivo_pdf text NULL,
            track_id varchar(100) NULL,
            respuesta_dian longtext NULL,
            error_dian text NULL,
            ambiente varchar(20) NOT NULL DEFAULT 'habilitacion',
            fecha_creacion datetime NOT NULL,
            fecha_actualizacion datetime NULL,
            PRIMARY KEY  (id),
            KEY track_id (track_id),
            UNIQUE KEY prefijo_numero (cliente_id, tipo_documento, prefijo, numero)
        ) $charset_collate;";
        
        // Tabla de logs para comunicación con DIAN
        $tabla_logs = $wpdb->prefix . 'dian_logs';
        $sql_logs = "CREATE TABLE IF NOT EXISTS $tabla_logs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cliente_id varchar(100) NOT NULL,
            accion varchar(100) NOT NULL,
            peticion longtext NOT NULL,
            respuesta longtext NOT NULL,
            codigo_http int(5) NOT NULL,
            fecha_creacion datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY cliente_id (cliente_id)
        ) $charset_collate;";
        
        // Tabla para información de autenticación
        $tabla_auth_info = $wpdb->prefix . 'dian_auth_info';
        $sql_auth_info = "CREATE TABLE IF NOT EXISTS $tabla_auth_info (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cliente_id varchar(100) NOT NULL,
            datos_auth text NOT NULL,
            fecha_creacion datetime NOT NULL,
            fecha_expiracion datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY cliente_id (cliente_id)
        ) $charset_collate;";
        
        // Tabla para resoluciones de numeración
        $tabla_resoluciones = $wpdb->prefix . 'dian_resoluciones';
        $sql_resoluciones = "CREATE TABLE IF NOT EXISTS $tabla_resoluciones (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cliente_id varchar(100) NOT NULL,
            prefijo varchar(20) NULL,
            desde_numero varchar(20) NOT NULL,
            hasta_numero varchar(20) NOT NULL,
            numero_resolucion varchar(50) NOT NULL,
            fecha_resolucion date NOT NULL,
            fecha_desde date NOT NULL,
            fecha_hasta date NOT NULL,
            tipo_documento varchar(20) NOT NULL DEFAULT 'factura',
            es_vigente tinyint(1) NOT NULL DEFAULT 1,
            fecha_creacion datetime NOT NULL,
            fecha_actualizacion datetime NULL,
            PRIMARY KEY  (id),
            KEY cliente_prefijo (cliente_id, prefijo)
        ) $charset_collate;";
        
        // Crear tablas
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_api_keys);
        dbDelta($sql_configuracion);
        dbDelta($sql_documentos);
        dbDelta($sql_logs);
        dbDelta($sql_auth_info);
        dbDelta($sql_resoluciones);
    }
    
    /**
     * Obtener el nombre de una tabla específica
     *
     * @since    1.0.0
     * @param    string    $nombre    Nombre de la tabla sin prefijo
     * @return   string    Nombre completo de la tabla con prefijo
     */
    public function obtener_nombre_tabla($nombre) {
        global $wpdb;
        return $wpdb->prefix . 'dian_' . $nombre;
    }
    
    /**
     * Verificar si existe un cliente con el ID especificado
     *
     * @since    1.0.0
     * @param    string    $cliente_id    ID del cliente
     * @return   boolean   True si existe, false en caso contrario
     */
    public function existe_cliente($cliente_id) {
        global $wpdb;
        $tabla_configuracion = $wpdb->prefix . 'dian_configuracion';
        
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $tabla_configuracion WHERE cliente_id = %s",
                $cliente_id
            )
        );
        
        return intval($result) > 0;
    }
    
    /**
     * Obtener configuración para un cliente
     *
     * @since    1.0.0
     * @param    string    $cliente_id    ID del cliente
     * @return   array     Datos de configuración o null si no existe
     */
    public function obtener_configuracion($cliente_id) {
        global $wpdb;
        $tabla_configuracion = $wpdb->prefix . 'dian_configuracion';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tabla_configuracion WHERE cliente_id = %s",
                $cliente_id
            ),
            ARRAY_A
        );
    }
    
    /**
     * Guardar configuración para un cliente
     *
     * @since    1.0.0
     * @param    array     $datos    Datos de configuración
     * @return   boolean   True si se guardó correctamente, false en caso contrario
     */
    public function guardar_configuracion($datos) {
        global $wpdb;
        $tabla_configuracion = $wpdb->prefix . 'dian_configuracion';
        
        // Validar datos mínimos necesarios
        if (empty($datos['cliente_id']) || empty($datos['id_software'])) {
            return false;
        }
        
        // Verificar si ya existe el cliente
        $existe = $this->existe_cliente($datos['cliente_id']);
        
        if ($existe) {
            // Actualizar configuración existente
            $resultado = $wpdb->update(
                $tabla_configuracion,
                array(
                    'certificado_ruta' => $datos['certificado_ruta'],
                    'certificado_clave' => $datos['certificado_clave'],
                    'id_software' => $datos['id_software'],
                    'software_pin' => $datos['software_pin'],
                    'tecnologia_firma' => isset($datos['tecnologia_firma']) ? $datos['tecnologia_firma'] : 'sha1',
                    'url_ws_validacion' => isset($datos['url_ws_validacion']) ? $datos['url_ws_validacion'] : '',
                    'url_ws_produccion' => isset($datos['url_ws_produccion']) ? $datos['url_ws_produccion'] : '',
                    'modo_operacion' => isset($datos['modo_operacion']) ? $datos['modo_operacion'] : 'habilitacion',
                    'test_set_id' => isset($datos['test_set_id']) ? $datos['test_set_id'] : null,
                    'fecha_actualizacion' => current_time('mysql')
                ),
                array('cliente_id' => $datos['cliente_id'])
            );
            
            return $resultado !== false;
        } else {
            // Crear nueva configuración
            $resultado = $wpdb->insert(
                $tabla_configuracion,
                array(
                    'cliente_id' => $datos['cliente_id'],
                    'certificado_ruta' => $datos['certificado_ruta'],
                    'certificado_clave' => $datos['certificado_clave'],
                    'id_software' => $datos['id_software'],
                    'software_pin' => $datos['software_pin'],
                    'tecnologia_firma' => isset($datos['tecnologia_firma']) ? $datos['tecnologia_firma'] : 'sha1',
                    'url_ws_validacion' => isset($datos['url_ws_validacion']) ? $datos['url_ws_validacion'] : '',
                    'url_ws_produccion' => isset($datos['url_ws_produccion']) ? $datos['url_ws_produccion'] : '',
                    'modo_operacion' => isset($datos['modo_operacion']) ? $datos['modo_operacion'] : 'habilitacion',
                    'test_set_id' => isset($datos['test_set_id']) ? $datos['test_set_id'] : null,
                    'fecha_creacion' => current_time('mysql')
                )
            );
            
            return $resultado !== false;
        }
    }
    
    /**
     * Crear API Key para un cliente
     *
     * @since    1.0.0
     * @param    array     $datos    Datos de la API Key
     * @return   array|boolean    Datos de la API Key creada o false en caso de error
     */
    public function crear_api_key($datos) {
        global $wpdb;
        $tabla_api_keys = $wpdb->prefix . 'dian_api_keys';
        
        // Validar datos mínimos necesarios
        if (empty($datos['nombre'])) {
            return false;
        }
        
        // Generar claves seguras
        $api_key = wp_generate_password(32, false);
        $api_secret = wp_generate_password(64, true);
        
        // Crear nueva API Key
        $resultado = $wpdb->insert(
            $tabla_api_keys,
            array(
                'nombre' => $datos['nombre'],
                'api_key' => $api_key,
                'api_secret' => $api_secret,
                'estado' => 'activo',
                'usuario_id' => isset($datos['usuario_id']) ? $datos['usuario_id'] : null,
                'permisos' => isset($datos['permisos']) ? $datos['permisos'] : null,
                'fecha_creacion' => current_time('mysql')
            )
        );
        
        if ($resultado) {
            return array(
                'id' => $wpdb->insert_id,
                'api_key' => $api_key,
                'api_secret' => $api_secret
            );
        }
        
        return false;
    }
    
    /**
     * Verificar si existe API Key
     *
     * @since    1.0.0
     * @param    string    $api_key    API Key a verificar
     * @return   array     Datos de la API Key o null si no existe
     */
    public function obtener_api_key($api_key) {
        global $wpdb;
        $tabla_api_keys = $wpdb->prefix . 'dian_api_keys';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tabla_api_keys WHERE api_key = %s",
                $api_key
            ),
            ARRAY_A
        );
    }
    
    /**
     * Actualizar estado de API Key
     *
     * @since    1.0.0
     * @param    int       $id        ID de la API Key
     * @param    string    $estado    Nuevo estado ('activo' o 'inactivo')
     * @return   boolean   True si se actualizó correctamente, false en caso contrario
     */
    public function actualizar_estado_api_key($id, $estado) {
        global $wpdb;
        $tabla_api_keys = $wpdb->prefix . 'dian_api_keys';
        
        $resultado = $wpdb->update(
            $tabla_api_keys,
            array(
                'estado' => $estado,
                'fecha_actualizacion' => current_time('mysql')
            ),
            array('id' => $id)
        );
        
        return $resultado !== false;
    }
    
    /**
     * Listar API Keys
     *
     * @since    1.0.0
     * @param    array     $filtros    Filtros opcionales
     * @return   array     Lista de API Keys
     */
    public function listar_api_keys($filtros = array()) {
        global $wpdb;
        
        $tabla_api_keys = $wpdb->prefix . 'dian_api_keys';
        
        $sql = "SELECT * FROM $tabla_api_keys";
        $where = array();
        $params = array();
        
        if (isset($filtros['estado'])) {
            $where[] = "estado = %s";
            $params[] = $filtros['estado'];
        }
        
        if (isset($filtros['usuario_id'])) {
            $where[] = "usuario_id = %d";
            $params[] = $filtros['usuario_id'];
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY fecha_creacion DESC";
        
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }
        
        $resultados = $wpdb->get_results($sql, ARRAY_A);
        
        // Si no hay resultados, devolver array vacío
        if (!$resultados) {
            return array();
        }
        
        return $resultados;
    }
    
    /**
     * Registrar log de comunicación con DIAN
     *
     * @since    1.0.0
     * @param    array     $datos_log    Datos del log
     * @return   int|boolean    ID del log insertado o false en caso de error
     */
    public function registrar_log($datos_log) {
        global $wpdb;
        $tabla_logs = $wpdb->prefix . 'dian_logs';
        
        // Validar datos mínimos necesarios
        if (empty($datos_log['cliente_id']) || empty($datos_log['accion'])) {
            return false;
        }
        
        $resultado = $wpdb->insert(
            $tabla_logs,
            array(
                'cliente_id' => $datos_log['cliente_id'],
                'accion' => $datos_log['accion'],
                'peticion' => isset($datos_log['peticion']) ? $datos_log['peticion'] : '',
                'respuesta' => isset($datos_log['respuesta']) ? $datos_log['respuesta'] : '',
                'codigo_http' => isset($datos_log['codigo_http']) ? $datos_log['codigo_http'] : 0,
                'fecha_creacion' => current_time('mysql')
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s'
            )
        );
        
        if ($resultado) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Obtener logs de un cliente
     *
     * @since    1.0.0
     * @param    string    $cliente_id    ID del cliente
     * @param    int       $limite        Cantidad de registros a obtener
     * @param    int       $offset        Desplazamiento (para paginación)
     * @return   array     Lista de logs
     */
    public function obtener_logs($cliente_id, $limite = 50, $offset = 0) {
        global $wpdb;
        
        $tabla_logs = $wpdb->prefix . 'dian_logs';
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $tabla_logs WHERE cliente_id = %s ORDER BY fecha_creacion DESC LIMIT %d OFFSET %d",
            $cliente_id,
            $limite,
            $offset
        );
        
        $resultados = $wpdb->get_results($sql, ARRAY_A);
        
        // Si no hay resultados, devolver array vacío
        if (!$resultados) {
            return array();
        }
        
        return $resultados;
    }
    
    /**
     * Guardar información de autenticación
     *
     * @since    1.0.0
     * @param    array     $datos_auth    Datos de autenticación
     * @return   int|boolean    ID del registro insertado o false en caso de error
     */
    public function guardar_info_autenticacion($datos_auth) {
        global $wpdb;
        $tabla_auth_info = $wpdb->prefix . 'dian_auth_info';
        
        // Validar datos mínimos necesarios
        if (empty($datos_auth['cliente_id']) || empty($datos_auth['datos_auth'])) {
            return false;
        }
        
        // Fecha de expiración (8 horas por defecto)
        $fecha_expiracion = isset($datos_auth['fecha_expiracion']) 
            ? $datos_auth['fecha_expiracion'] 
            : date('Y-m-d H:i:s', strtotime('+8 hours'));
        
        $resultado = $wpdb->insert(
            $tabla_auth_info,
            array(
                'cliente_id' => $datos_auth['cliente_id'],
                'datos_auth' => is_array($datos_auth['datos_auth']) ? json_encode($datos_auth['datos_auth']) : $datos_auth['datos_auth'],
                'fecha_creacion' => current_time('mysql'),
                'fecha_expiracion' => $fecha_expiracion
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
        
        if ($resultado) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Obtener información de autenticación válida
     *
     * @since    1.0.0
     * @param    string    $cliente_id    ID del cliente
     * @return   array     Datos de autenticación o null si no existe o expiró
     */
    public function obtener_info_autenticacion($cliente_id) {
        global $wpdb;
        $tabla_auth_info = $wpdb->prefix . 'dian_auth_info';
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $tabla_auth_info WHERE cliente_id = %s AND fecha_expiracion > %s ORDER BY fecha_creacion DESC LIMIT 1",
            $cliente_id,
            current_time('mysql')
        );
        
        $resultado = $wpdb->get_row($sql, ARRAY_A);
        
        if ($resultado) {
            // Decodificar datos de autenticación si están en formato JSON
            if (is_string($resultado['datos_auth']) && $this->es_json($resultado['datos_auth'])) {
                $resultado['datos_auth'] = json_decode($resultado['datos_auth'], true);
            }
        }
        
        return $resultado;
    }
    
    /**
     * Verificar si una cadena es JSON válido
     *
     * @since    1.0.0
     * @param    string    $string    Cadena a verificar
     * @return   boolean   True si es JSON válido, false en caso contrario
     */
    private function es_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    
    /**
     * Guardar documento electrónico
     *
     * @since    1.0.0
     * @param    array     $datos_documento    Datos del documento
     * @return   int|boolean    ID del documento insertado o false en caso de error
     */
    public function guardar_documento($datos_documento) {
        global $wpdb;
        $tabla_documentos = $wpdb->prefix . 'dian_documentos';
        
        // Validar datos mínimos necesarios
        $campos_requeridos = array(
            'cliente_id', 'tipo_documento', 'numero', 'emisor_nit', 
            'emisor_razon_social', 'receptor_documento', 'receptor_razon_social',
            'fecha_emision', 'fecha_vencimiento', 'valor_sin_impuestos',
            'valor_impuestos', 'valor_total'
        );
        
        foreach ($campos_requeridos as $campo) {
            if (empty($datos_documento[$campo])) {
                return false;
            }
        }
        
        // Verificar si ya existe el documento
        $existe = $this->existe_documento(
            $datos_documento['cliente_id'],
            $datos_documento['tipo_documento'],
            $datos_documento['prefijo'] ?? '',
            $datos_documento['numero']
        );
        
        $fecha_actual = current_time('mysql');
        
        if ($existe) {
            // Actualizar documento existente
            $datos_actualizar = array(
                'emisor_razon_social' => $datos_documento['emisor_razon_social'],
                'receptor_razon_social' => $datos_documento['receptor_razon_social'],
                'fecha_emision' => $datos_documento['fecha_emision'],
                'fecha_vencimiento' => $datos_documento['fecha_vencimiento'],
                'valor_sin_impuestos' => $datos_documento['valor_sin_impuestos'],
                'valor_impuestos' => $datos_documento['valor_impuestos'],
                'valor_total' => $datos_documento['valor_total'],
                'moneda' => isset($datos_documento['moneda']) ? $datos_documento['moneda'] : 'COP',
                'estado' => $datos_documento['estado'],
                'fecha_actualizacion' => $fecha_actual
            );
            
            // Campos opcionales
            $campos_opcionales = array(
                'cufe', 'qr_data', 'cude', 'archivo_xml', 'archivo_pdf', 
                'track_id', 'respuesta_dian', 'error_dian', 'ambiente'
            );
            
            foreach ($campos_opcionales as $campo) {
                if (isset($datos_documento[$campo])) {
                    $datos_actualizar[$campo] = $datos_documento[$campo];
                }
            }
            
            $resultado = $wpdb->update(
                $tabla_documentos,
                $datos_actualizar,
                array(
                    'cliente_id' => $datos_documento['cliente_id'],
                    'tipo_documento' => $datos_documento['tipo_documento'],
                    'prefijo' => $datos_documento['prefijo'] ?? '',
                    'numero' => $datos_documento['numero']
                )
            );
            
            if ($resultado !== false) {
                // Obtener el ID del documento actualizado
                $id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $tabla_documentos WHERE cliente_id = %s AND tipo_documento = %s AND prefijo = %s AND numero = %s",
                    $datos_documento['cliente_id'],
                    $datos_documento['tipo_documento'],
                    $datos_documento['prefijo'] ?? '',
                    $datos_documento['numero']
                ));
                
                return $id;
            }
            
            return false;
        } else {
            // Crear nuevo documento
            $datos_insertar = array(
                'cliente_id' => $datos_documento['cliente_id'],
                'tipo_documento' => $datos_documento['tipo_documento'],
                'prefijo' => $datos_documento['prefijo'] ?? '',
                'numero' => $datos_documento['numero'],
                'emisor_nit' => $datos_documento['emisor_nit'],
                'emisor_razon_social' => $datos_documento['emisor_razon_social'],
                'receptor_documento' => $datos_documento['receptor_documento'],
                'receptor_razon_social' => $datos_documento['receptor_razon_social'],
                'fecha_emision' => $datos_documento['fecha_emision'],
                'fecha_vencimiento' => $datos_documento['fecha_vencimiento'],
                'valor_sin_impuestos' => $datos_documento['valor_sin_impuestos'],
                'valor_impuestos' => $datos_documento['valor_impuestos'],
                'valor_total' => $datos_documento['valor_total'],
                'moneda' => isset($datos_documento['moneda']) ? $datos_documento['moneda'] : 'COP',
                'estado' => $datos_documento['estado'],
                'cufe' => isset($datos_documento['cufe']) ? $datos_documento['cufe'] : null,
                'qr_data' => isset($datos_documento['qr_data']) ? $datos_documento['qr_data'] : null,
                'cude' => isset($datos_documento['cude']) ? $datos_documento['cude'] : null,
                'archivo_xml' => isset($datos_documento['archivo_xml']) ? $datos_documento['archivo_xml'] : null,
                'archivo_pdf' => isset($datos_documento['archivo_pdf']) ? $datos_documento['archivo_pdf'] : null,
                'track_id' => isset($datos_documento['track_id']) ? $datos_documento['track_id'] : null,
                'respuesta_dian' => isset($datos_documento['respuesta_dian']) ? $datos_documento['respuesta_dian'] : null,
                'error_dian' => isset($datos_documento['error_dian']) ? $datos_documento['error_dian'] : null,
                'ambiente' => isset($datos_documento['ambiente']) ? $datos_documento['ambiente'] : 'habilitacion',
                'fecha_creacion' => $fecha_actual,
                'fecha_actualizacion' => $fecha_actual
            );
            
            $resultado = $wpdb->insert($tabla_documentos, $datos_insertar);
            
            if ($resultado) {
                return $wpdb->insert_id;
            }
            
            return false;
        }
    }
    
    /**
     * Verificar si ya existe un documento
     *
     * @since    1.0.0
     * @param    string    $cliente_id        ID del cliente
     * @param    string    $tipo_documento    Tipo de documento
     * @param    string    $prefijo           Prefijo del documento
     * @param    string    $numero            Número del documento
     * @return   boolean   True si existe, false en caso contrario
     */
    public function existe_documento($cliente_id, $tipo_documento, $prefijo, $numero) {
        global $wpdb;
        $tabla_documentos = $wpdb->prefix . 'dian_documentos';
        
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $tabla_documentos WHERE cliente_id = %s AND tipo_documento = %s AND prefijo = %s AND numero = %s",
                $cliente_id,
                $tipo_documento,
                $prefijo,
                $numero
            )
        );
        
        return intval($result) > 0;
    }
    
    /**
     * Obtener documento por ID
     *
     * @since    1.0.0
     * @param    int       $id    ID del documento
     * @return   array     Datos del documento o null si no existe
     */
    public function obtener_documento_por_id($id) {
        global $wpdb;
        $tabla_documentos = $wpdb->prefix . 'dian_documentos';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tabla_documentos WHERE id = %d",
                $id
            ),
            ARRAY_A
        );
    }
    
    /**
     * Obtener documento por número
     *
     * @since    1.0.0
     * @param    string    $cliente_id        ID del cliente
     * @param    string    $tipo_documento    Tipo de documento
     * @param    string    $prefijo           Prefijo del documento
     * @param    string    $numero            Número del documento
     * @return   array     Datos del documento o null si no existe
     */
    public function obtener_documento_por_numero($cliente_id, $tipo_documento, $prefijo, $numero) {
        global $wpdb;
        $tabla_documentos = $wpdb->prefix . 'dian_documentos';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tabla_documentos WHERE cliente_id = %s AND tipo_documento = %s AND prefijo = %s AND numero = %s",
                $cliente_id,
                $tipo_documento,
                $prefijo,
                $numero
            ),
            ARRAY_A
        );
    }
    
    /**
     * Obtener documento por TrackID
     *
     * @since    1.0.0
     * @param    string    $track_id    TrackID asignado por la DIAN
     * @return   array     Datos del documento o null si no existe
     */
    public function obtener_documento_por_track_id($track_id) {
        global $wpdb;
        $tabla_documentos = $wpdb->prefix . 'dian_documentos';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tabla_documentos WHERE track_id = %s",
                $track_id
            ),
            ARRAY_A
        );
    }
    
    /**
     * Actualizar estado de un documento por TrackID
     *
     * @since    1.0.0
     * @param    string    $track_id          TrackID del documento
     * @param    array     $datos_actualizacion    Datos a actualizar
     * @return   boolean   True si se actualizó correctamente, false en caso contrario
     */
    public function actualizar_documento_por_track_id($track_id, $datos_actualizacion) {
        global $wpdb;
        $tabla_documentos = $wpdb->prefix . 'dian_documentos';
        
        $datos = array(
            'fecha_actualizacion' => current_time('mysql')
        );
        
        $campos_permitidos = array(
            'estado', 'cufe', 'qr_data', 'cude', 'archivo_xml', 
            'archivo_pdf', 'respuesta_dian', 'error_dian'
        );
        
        foreach ($campos_permitidos as $campo) {
            if (isset($datos_actualizacion[$campo])) {
                $datos[$campo] = $datos_actualizacion[$campo];
            }
        }
        
        $resultado = $wpdb->update(
            $tabla_documentos,
            $datos,
            array('track_id' => $track_id)
        );
        
        return $resultado !== false;
    }
    
    /**
     * Listar documentos
     *
     * @since    1.0.0
     * @param    string    $cliente_id    ID del cliente
     * @param    array     $filtros       Filtros opcionales
     * @param    int       $limite        Cantidad de registros a obtener
     * @param    int       $offset        Desplazamiento (para paginación)
     * @return   array     Lista de documentos
     */
    public function listar_documentos($cliente_id, $filtros = array(), $limite = 50, $offset = 0) {
        global $wpdb;
        
        $tabla_documentos = $wpdb->prefix . 'dian_documentos';
        
        $sql = "SELECT * FROM $tabla_documentos WHERE cliente_id = %s";
        $params = array($cliente_id);
        
        // Aplicar filtros
        if (isset($filtros['tipo_documento'])) {
            $sql .= " AND tipo_documento = %s";
            $params[] = $filtros['tipo_documento'];
        }
        
        if (isset($filtros['estado'])) {
            $sql .= " AND estado = %s";
            $params[] = $filtros['estado'];
        }
        
        if (isset($filtros['ambiente'])) {
            $sql .= " AND ambiente = %s";
            $params[] = $filtros['ambiente'];
        }
        
        if (isset($filtros['fecha_desde']) && isset($filtros['fecha_hasta'])) {
            $sql .= " AND fecha_emision BETWEEN %s AND %s";
            $params[] = $filtros['fecha_desde'];
            $params[] = $filtros['fecha_hasta'];
        }
        
        // Ordenamiento
        $sql .= " ORDER BY fecha_emision DESC";
        
        // Límite y offset
        $sql .= " LIMIT %d OFFSET %d";
        $params[] = $limite;
        $params[] = $offset;
        
        $sql_preparada = $wpdb->prepare($sql, $params);
        
        $resultados = $wpdb->get_results($sql_preparada, ARRAY_A);
        
        // Si no hay resultados, devolver array vacío
        if (!$resultados) {
            return array();
        }
        
        return $resultados;
    }
    
    /**
     * Guardar resolución de numeración
     *
     * @since    1.0.0
     * @param    array     $datos_resolucion    Datos de la resolución
     * @return   int|boolean    ID de la resolución insertada o false en caso de error
     */
    public function guardar_resolucion($datos_resolucion) {
        global $wpdb;
        $tabla_resoluciones = $wpdb->prefix . 'dian_resoluciones';
        
        // Validar datos mínimos necesarios
        $campos_requeridos = array(
            'cliente_id', 'desde_numero', 'hasta_numero', 'numero_resolucion',
            'fecha_resolucion', 'fecha_desde', 'fecha_hasta'
        );
        
        foreach ($campos_requeridos as $campo) {
            if (empty($datos_resolucion[$campo])) {
                return false;
            }
        }
        
        // Verificar si ya existe la resolución
        $id_existente = $this->existe_resolucion(
            $datos_resolucion['cliente_id'],
            $datos_resolucion['prefijo'] ?? '',
            $datos_resolucion['numero_resolucion']
        );
        
        $fecha_actual = current_time('mysql');
        
        if ($id_existente) {
            // Actualizar resolución existente
            $datos_actualizar = array(
                'desde_numero' => $datos_resolucion['desde_numero'],
                'hasta_numero' => $datos_resolucion['hasta_numero'],
                'fecha_resolucion' => $datos_resolucion['fecha_resolucion'],
                'fecha_desde' => $datos_resolucion['fecha_desde'],
                'fecha_hasta' => $datos_resolucion['fecha_hasta'],
                'tipo_documento' => isset($datos_resolucion['tipo_documento']) ? $datos_resolucion['tipo_documento'] : 'factura',
                'es_vigente' => isset($datos_resolucion['es_vigente']) ? $datos_resolucion['es_vigente'] : 1,
                'fecha_actualizacion' => $fecha_actual
            );
            
            $resultado = $wpdb->update(
                $tabla_resoluciones,
                $datos_actualizar,
                array('id' => $id_existente)
            );
            
            return $resultado !== false ? $id_existente : false;
        } else {
            // Crear nueva resolución
            $datos_insertar = array(
                'cliente_id' => $datos_resolucion['cliente_id'],
                'prefijo' => isset($datos_resolucion['prefijo']) ? $datos_resolucion['prefijo'] : '',
                'desde_numero' => $datos_resolucion['desde_numero'],
                'hasta_numero' => $datos_resolucion['hasta_numero'],
                'numero_resolucion' => $datos_resolucion['numero_resolucion'],
                'fecha_resolucion' => $datos_resolucion['fecha_resolucion'],
                'fecha_desde' => $datos_resolucion['fecha_desde'],
                'fecha_hasta' => $datos_resolucion['fecha_hasta'],
                'tipo_documento' => isset($datos_resolucion['tipo_documento']) ? $datos_resolucion['tipo_documento'] : 'factura',
                'es_vigente' => isset($datos_resolucion['es_vigente']) ? $datos_resolucion['es_vigente'] : 1,
                'fecha_creacion' => $fecha_actual,
                'fecha_actualizacion' => $fecha_actual
            );
            
            $resultado = $wpdb->insert($tabla_resoluciones, $datos_insertar);
            
            return $resultado ? $wpdb->insert_id : false;
        }
    }
    
    /**
     * Verificar si ya existe una resolución
     *
     * @since    1.0.0
     * @param    string    $cliente_id          ID del cliente
     * @param    string    $prefijo             Prefijo de la resolución
     * @param    string    $numero_resolucion   Número de resolución
     * @return   int|false ID de la resolución si existe, false en caso contrario
     */
    public function existe_resolucion($cliente_id, $prefijo, $numero_resolucion) {
        global $wpdb;
        $tabla_resoluciones = $wpdb->prefix . 'dian_resoluciones';
        
        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $tabla_resoluciones WHERE cliente_id = %s AND prefijo = %s AND numero_resolucion = %s",
                $cliente_id,
                $prefijo,
                $numero_resolucion
            )
        );
    }
    
    /**
     * Obtener resoluciones de numeración vigentes
     *
     * @since    1.0.0
     * @param    string    $cliente_id       ID del cliente
     * @param    string    $tipo_documento   Tipo de documento (opcional)
     * @return   array     Lista de resoluciones vigentes
     */
    public function obtener_resoluciones_vigentes($cliente_id, $tipo_documento = null) {
        global $wpdb;
        
        $tabla_resoluciones = $wpdb->prefix . 'dian_resoluciones';
        
        $sql = "SELECT * FROM $tabla_resoluciones 
                WHERE cliente_id = %s 
                AND es_vigente = 1 
                AND fecha_desde <= %s 
                AND fecha_hasta >= %s";
        
        $params = array(
            $cliente_id,
            current_time('mysql'),
            current_time('mysql')
        );
        
        if ($tipo_documento) {
            $sql .= " AND tipo_documento = %s";
            $params[] = $tipo_documento;
        }
        
        $sql .= " ORDER BY fecha_desde DESC";
        
        $sql_preparada = $wpdb->prepare($sql, $params);
        
        $resultados = $wpdb->get_results($sql_preparada, ARRAY_A);
        
        // Si no hay resultados, devolver array vacío
        if (!$resultados) {
            return array();
        }
        
        return $resultados;
    }
    
    /**
     * Listar resoluciones de numeración
     *
     * @since    1.0.0
     * @param    string    $cliente_id    ID del cliente
     * @return   array     Lista de resoluciones
     */
    public function listar_resoluciones($cliente_id) {
        global $wpdb;
        
        $tabla_resoluciones = $wpdb->prefix . 'dian_resoluciones';
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $tabla_resoluciones WHERE cliente_id = %s ORDER BY fecha_desde DESC",
            $cliente_id
        );
        
        $resultados = $wpdb->get_results($sql, ARRAY_A);
        
        // Si no hay resultados, devolver array vacío
        if (!$resultados) {
            return array();
        }
        
        return $resultados;
    }

    /**
     * Listar clientes configurados
     *
     * @since    1.0.0
     * @return   array    Lista de clientes
     */
    public function listar_clientes() {
        global $wpdb;
        
        $tabla_configuracion = $wpdb->prefix . 'dian_configuracion';
        
        // Consultar clientes
        $clientes = $wpdb->get_results(
            "SELECT cliente_id, id_software, modo_operacion, fecha_creacion 
            FROM {$tabla_configuracion} 
            ORDER BY fecha_creacion DESC",
            ARRAY_A
        );
        
        // Si no hay resultados, devolver array vacío
        if (!$clientes) {
            return array();
        }
        
        return $clientes;
    }
}