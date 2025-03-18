# API de Facturación Electrónica DIAN - Documentación

Esta documentación describe los endpoints disponibles en la API REST del plugin de Facturación Electrónica DIAN.

## Autenticación

Todas las solicitudes a la API deben incluir una API Key válida en el encabezado `X-API-Key`.

Ejemplo:

```
X-API-Key: ApiKeyGeneradaDesdeElPanel
```

## Endpoints

### Facturas

#### Generar Factura

Crea una nueva factura electrónica.

```
POST /wp-json/dian-api/v1/factura
```

**Parámetros de solicitud:**

```json
{
  "cliente_id": "CLIENTE001",
  "invoice_data": {
    "invoice_number": "FC0001",
    "prefix": "FC",
    "issue_date": "2023-08-31",
    "issue_time": "10:30:00",
    "due_date": "2023-09-30",
    "note": "Observaciones de la factura",
    "supplier": {
      "identification_number": "900123456",
      "name": "Empresa de Prueba S.A.S.",
      "party_type": "1",
      "tax_level_code": "O-23",
      "address": "Calle 123 # 45-67",
      "city": "11001",
      "city_name": "Bogotá",
      "postal_code": "110111",
      "department": "Bogotá D.C.",
      "department_code": "11",
      "country_code": "CO",
      "country_name": "Colombia"
    },
    "customer": {
      "identification_number": "1023456789",
      "name": "Cliente de Prueba",
      "party_type": "2",
      "id_type": "13",
      "tax_level_code": "R-99-PN",
      "address": "Carrera 45 # 67-89",
      "city": "11001",
      "city_name": "Bogotá",
      "postal_code": "110111",
      "department": "Bogotá D.C.",
      "department_code": "11",
      "country_code": "CO",
      "country_name": "Colombia"
    },
    "taxes": [
      {
        "tax_type": "01",
        "tax_amount": 19000.00,
        "taxable_amount": 100000.00,
        "percent": 19.00
      }
    ],
    "items": [
      {
        "description": "Producto de prueba 1",
        "code": "PRD001",
        "quantity": 2,
        "unit_code": "EA",
        "unit_price": 50000.00,
        "line_extension_amount": 100000.00,
        "taxes": [
          {
            "tax_type": "01",
            "tax_amount": 19000.00,
            "taxable_amount": 100000.00,
            "percent": 19.00
          }
        ]
      }
    ],
    "monetary_totals": {
      "line_extension_amount": 100000.00,
      "tax_exclusive_amount": 100000.00,
      "tax_inclusive_amount": 119000.00,
      "payable_amount": 119000.00
    }
  },
  "ambiente": "habilitacion",
  "generar_pdf": true
}
```

**Respuesta exitosa:**

```json
{
  "success": true,
  "message": "Factura generada correctamente",
  "factura": {
    "id": 123,
    "cliente_id": "CLIENTE001",
    "prefijo": "FC",
    "numero": "0001",
    "fecha_emision": "2023-08-31",
    "valor_total": 119000.00,
    "estado": "generado",
    "has_pdf": true
  }
}
```

#### Consultar Factura

Obtiene los detalles de una factura específica.

```
GET /wp-json/dian-api/v1/factura/{cliente_id}/{prefijo}/{numero}
```

**Parámetros de ruta:**
- `cliente_id`: ID del cliente (obligatorio)
- `prefijo`: Prefijo de la factura (puede estar vacío)
- `numero`: Número de la factura (obligatorio)

**Respuesta exitosa:**

```json
{
  "success": true,
  "factura": {
    "id": 123,
    "cliente_id": "CLIENTE001",
    "tipo_documento": "factura",
    "prefijo": "FC",
    "numero": "0001",
    "emisor_nit": "900123456",
    "emisor_razon_social": "Empresa de Prueba S.A.S.",
    "receptor_documento": "1023456789",
    "receptor_razon_social": "Cliente de Prueba",
    "fecha_emision": "2023-08-31 10:30:00",
    "fecha_vencimiento": "2023-09-30 10:30:00",
    "valor_sin_impuestos": 100000.00,
    "valor_impuestos": 19000.00,
    "valor_total": 119000.00,
    "moneda": "COP",
    "estado": "generado",
    "cufe": null,
    "track_id": null,
    "ambiente": "habilitacion",
    "items": [...],
    "observaciones": "Observaciones de la factura",
    "has_pdf": true,
    "has_xml": true
  }
}
```

#### Obtener PDF de Factura

Obtiene la representación gráfica (PDF) de una factura.

```
GET /wp-json/dian-api/v1/factura/pdf/{cliente_id}/{prefijo}/{numero}
```

**Parámetros de ruta:**
- `cliente_id`: ID del cliente (obligatorio)
- `prefijo`: Prefijo de la factura (puede estar vacío)
- `numero`: Número de la factura (obligatorio)

**Parámetros de consulta:**
- `format`: Formato de respuesta ('download' o 'base64')

**Respuesta exitosa (formato base64):**

```json
{
  "success": true,
  "message": "PDF generado correctamente",
  "pdf_base64": "JVBERi0xLjcKJeLjz9MKNSAwIG9iago8PC9...",
  "filename": "Factura_FC0001.pdf"
}
```

**Respuesta exitosa (formato download):**
- Archivo PDF descargable directamente

#### Enviar Factura a la DIAN

Envía una factura a la DIAN para su validación.

```
POST /wp-json/dian-api/v1/factura/enviar/{cliente_id}/{prefijo}/{numero}
```

**Parámetros de ruta:**
- `cliente_id`: ID del cliente (obligatorio)
- `prefijo`: Prefijo de la factura (puede estar vacío)
- `numero`: Número de la factura (obligatorio)

**Parámetros de consulta:**
- `modo`: Modo de operación ('habilitacion' o 'produccion')

**Respuesta exitosa:**

```json
{
  "success": true,
  "message": "Factura enviada correctamente a la DIAN",
  "track_id": "12345678-1234-1234-1234-123456789012",
  "estado": "enviado"
}
```

#### Verificar Estado de Factura en la DIAN

Consulta el estado de una factura en la DIAN.

```
GET /wp-json/dian-api/v1/factura/estado/{track_id}
```

**Parámetros de ruta:**
- `track_id`: ID de seguimiento asignado por la DIAN (obligatorio)

**Parámetros de consulta:**
- `modo`: Modo de operación ('habilitacion' o 'produccion')

**Respuesta exitosa:**

```json
{
  "success": true,
  "message": "Estado consultado correctamente",
  "track_id": "12345678-1234-1234-1234-123456789012",
  "estado": "accepted",
  "codigo_estado": "00",
  "descripcion_estado": "Documento validado por la DIAN",
  "es_valido": true,
  "errores": []
}
```

### Resoluciones de Numeración

#### Obtener Resoluciones

Obtiene las resoluciones de numeración vigentes para un cliente.

```
GET /wp-json/dian-api/v1/resoluciones/{cliente_id}
```

**Parámetros de ruta:**
- `cliente_id`: ID del cliente (obligatorio)

**Parámetros de consulta:**
- `tipo_documento`: Tipo de documento ('factura', 'nota_credito', 'nota_debito')

**Respuesta exitosa:**

```json
{
  "success": true,
  "resoluciones": [
    {
      "id": 1,
      "cliente_id": "CLIENTE001",
      "prefijo": "FC",
      "desde_numero": "0001",
      "hasta_numero": "1000",
      "numero_resolucion": "18760000001",
      "fecha_resolucion": "2023-01-01",
      "fecha_desde": "2023-01-01",
      "fecha_hasta": "2024-01-01",
      "tipo_documento": "factura",
      "es_vigente": 1,
      "fecha_creacion": "2023-01-01 00:00:00",
      "fecha_actualizacion": null
    }
  ]
}
```

#### Crear Resolución

Crea una nueva resolución de numeración.

```
POST /wp-json/dian-api/v1/resoluciones
```

**Parámetros de solicitud:**

```json
{
  "cliente_id": "CLIENTE001",
  "prefijo": "FC",
  "desde_numero": "0001",
  "hasta_numero": "1000",
  "numero_resolucion": "18760000001",
  "fecha_resolucion": "2023-01-01",
  "fecha_desde": "2023-01-01",
  "fecha_hasta": "2024-01-01",
  "tipo_documento": "factura",
  "es_vigente": 1
}
```

**Respuesta exitosa:**

```json
{
  "success": true,
  "message": "Resolución guardada correctamente",
  "id": 1
}
```

### Clientes

#### Obtener Clientes

Obtiene la lista de clientes configurados.

```
GET /wp-json/dian-api/v1/clientes
```

**Respuesta exitosa:**

```json
{
  "success": true,
  "clientes": [
    {
      "cliente_id": "CLIENTE001",
      "id_software": "softwareid1",
      "modo_operacion": "habilitacion",
      "fecha_creacion": "2023-01-01 00:00:00"
    }
  ]
}
```

### Notas Crédito y Débito

#### Generar Nota Crédito

Crea una nueva nota crédito.

```
POST /wp-json/dian-api/v1/nota-credito
```

**Nota:** Esta funcionalidad aún no está implementada.

#### Generar Nota Débito

Crea una nueva nota débito.

```
POST /wp-json/dian-api/v1/nota-debito
```

**Nota:** Esta funcionalidad aún no está implementada.

## Códigos de Estado

La API utiliza los siguientes códigos de estado HTTP:

- `200 OK`: La solicitud se completó correctamente
- `400 Bad Request`: La solicitud contiene parámetros inválidos o faltantes
- `401 Unauthorized`: Falta la API Key o no es válida
- `404 Not Found`: El recurso solicitado no existe
- `500 Internal Server Error`: Error interno del servidor
- `501 Not Implemented`: La funcionalidad solicitada aún no está implementada

## Errores

En caso de error, la API devolverá un objeto con la siguiente estructura:

```json
{
  "code": "error_code",
  "message": "Descripción del error",
  "data": {
    "status": 400
  }
}
```