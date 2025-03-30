<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Factura Electrónica <?php echo $datos_factura['prefijo'] . $datos_factura['numero']; ?></title>
    <style type="text/css">
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        .document-title {
            font-size: 18pt;
            font-weight: bold;
            color: <?php echo $datos_factura['pdf_primary_color']; ?>;
            margin-bottom: 10px;
            text-align: center;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
        }
        .header-col {
            width: 33%;
            vertical-align: top;
        }
        .logo {
            max-width: 200px;
            max-height: 100px;
        }
        .company-info {
            font-size: 9pt;
        }
        .document-info {
            font-size: 9pt;
            border: 1px solid <?php echo $datos_factura['pdf_primary_color']; ?>;
            padding: 10px;
            border-radius: 5px;
        }
        .document-info-title {
            font-weight: bold;
            color: <?php echo $datos_factura['pdf_primary_color']; ?>;
            margin-bottom: 5px;
        }
        .customer-info {
            margin-bottom: 20px;
            font-size: 9pt;
        }
        .section-title {
            font-weight: bold;
            color: <?php echo $datos_factura['pdf_primary_color']; ?>;
            margin-bottom: 5px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.items th {
            background-color: <?php echo $datos_factura['pdf_primary_color']; ?>;
            color: white;
            padding: 5px;
            text-align: left;
            font-size: 9pt;
        }
        table.items td {
            padding: 5px;
            font-size: 9pt;
            border-bottom: 1px solid #ddd;
        }
        .tax-summary {
            margin-bottom: 20px;
        }
        .payment-info {
            margin-bottom: 20px;
            font-size: 9pt;
        }
        .qr-code {
            text-align: center;
            margin-top: 20px;
        }
        .qr-code img {
            max-width: 100px;
            max-height: 100px;
        }
        .totals {
            width: 100%;
            font-size: 9pt;
        }
        .totals td {
            padding: 3px;
        }
        .total-title {
            text-align: right;
            font-weight: bold;
        }
        .total-value {
            text-align: right;
            font-weight: bold;
        }
        .grand-total {
            font-size: 11pt;
            color: <?php echo $datos_factura['pdf_primary_color']; ?>;
        }
        .footer {
            margin-top: 20px;
            font-size: 8pt;
            text-align: center;
            color: #777;
        }
        .cufe-info {
            margin-top: 20px;
            font-size: 8pt;
        }
        .notes {
            margin-top: 20px;
            font-size: 9pt;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="document-title">FACTURA ELECTRÓNICA DE VENTA</div>
    
    <table class="header">
        <tr>
            <td class="header-col">
                <?php if (!empty($datos_factura['company_logo'])): ?>
                <img src="<?php echo $datos_factura['company_logo']; ?>" class="logo" />
                <?php endif; ?>
                <div class="company-info">
                    <h3><?php echo htmlspecialchars($datos_factura['company_name'] ?: $datos_factura['emisor_razon_social']); ?></h3>
                    <p>NIT: <?php echo htmlspecialchars($datos_factura['company_nit'] ?: $datos_factura['emisor_nit']); ?></p>
                    <p><?php echo htmlspecialchars($datos_factura['company_address']); ?></p>
                    <p>Teléfono: <?php echo htmlspecialchars($datos_factura['company_phone']); ?></p>
                    <p>Email: <?php echo htmlspecialchars($datos_factura['company_email']); ?></p>
                    <p>Web: <?php echo htmlspecialchars($datos_factura['company_website']); ?></p>
                </div>
            </td>
            <td class="header-col">
                <div class="document-info">
                    <div class="document-info-title">FACTURA ELECTRÓNICA</div>
                    <p><strong>No.:</strong> <?php echo htmlspecialchars($datos_factura['prefijo'] . $datos_factura['numero']); ?></p>
                    <p><strong>Fecha de emisión:</strong> <?php echo date('d/m/Y', strtotime($datos_factura['fecha_emision'])); ?></p>
                    <p><strong>Hora:</strong> <?php echo date('H:i:s', strtotime($datos_factura['fecha_emision'])); ?></p>
                    <p><strong>Fecha de vencimiento:</strong> <?php echo date('d/m/Y', strtotime($datos_factura['fecha_vencimiento'])); ?></p>
                </div>
            </td>
            <td class="header-col">
            <div class="qr-code">
                <?php if (!empty($datos_factura['qr_code']) && file_exists($datos_factura['qr_code'])): ?>
                    <img src="<?php echo $datos_factura['qr_code']; ?>" alt="Código QR de verificación" width="100" height="100" />
                    <p>Escanea para verificar</p>
                <?php else: ?>
                    <p>QR no disponible</p>
                <?php endif; ?>
            </div>
            </td>
        </tr>
    </table>
    
    <div class="customer-info">
        <div class="section-title">CLIENTE</div>
        <table width="100%">
            <tr>
                <td width="60%">
                    <p><strong>Razón Social:</strong> <?php echo htmlspecialchars($datos_factura['receptor_razon_social']); ?></p>
                    <p><strong>NIT/CC:</strong> <?php echo htmlspecialchars($datos_factura['receptor_documento']); ?></p>
                </td>
                <td width="40%">
                    <p><strong>Teléfono:</strong> <?php echo isset($datos_factura['receptor_telefono']) ? htmlspecialchars($datos_factura['receptor_telefono']) : ''; ?></p>
                    <p><strong>Email:</strong> <?php echo isset($datos_factura['receptor_email']) ? htmlspecialchars($datos_factura['receptor_email']) : ''; ?></p>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="section-title">DETALLE</div>
    <table class="items">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="10%">Código</th>
                <th width="40%">Descripción</th>
                <th width="10%">Cantidad</th>
                <th width="15%">Valor Unitario</th>
                <th width="10%">% IVA</th>
                <th width="10%">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Simular líneas de factura si no están disponibles
            $items = isset($datos_factura['items']) ? json_decode($datos_factura['items'], true) : [];
            if (empty($items)) {
                // Crear un item por defecto basado en totales
                $items = [
                    [
                        'codigo' => '001',
                        'descripcion' => 'Productos/Servicios según detalle',
                        'cantidad' => 1,
                        'valor_unitario' => $datos_factura['valor_sin_impuestos'],
                        'iva_porcentaje' => $datos_factura['valor_impuestos'] > 0 ? 
                            round(($datos_factura['valor_impuestos'] / $datos_factura['valor_sin_impuestos']) * 100) : 0,
                        'total' => $datos_factura['valor_sin_impuestos']
                    ]
                ];
            }
            
            foreach ($items as $index => $item): 
            ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td><?php echo htmlspecialchars($item['codigo'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                <td align="right"><?php echo number_format($item['cantidad'], 2); ?></td>
                <td align="right"><?php echo number_format($item['valor_unitario'], 2); ?></td>
                <td align="right"><?php echo number_format($item['iva_porcentaje'] ?? 0, 2); ?>%</td>
                <td align="right"><?php echo number_format($item['total'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <table class="totals" cellspacing="0">
        <tr>
            <td width="70%"></td>
            <td class="total-title" width="20%">Subtotal:</td>
            <td class="total-value" width="10%"><?php echo number_format($datos_factura['valor_sin_impuestos'], 2); ?></td>
        </tr>
        <tr>
            <td></td>
            <td class="total-title">IVA (19%):</td>
            <td class="total-value"><?php echo number_format($datos_factura['valor_impuestos'], 2); ?></td>
        </tr>
        <tr>
            <td></td>
            <td class="total-title grand-total">TOTAL A PAGAR:</td>
            <td class="total-value grand-total"><?php echo number_format($datos_factura['valor_total'], 2); ?></td>
        </tr>
    </table>

    <div class="payment-info">
        <div class="section-title">INFORMACIÓN DE PAGO</div>
        <p><strong>Forma de pago:</strong> <?php echo isset($datos_factura['forma_pago']) ? htmlspecialchars($datos_factura['forma_pago']) : 'Contado'; ?></p>
        <p><strong>Medio de pago:</strong> <?php echo isset($datos_factura['medio_pago']) ? htmlspecialchars($datos_factura['medio_pago']) : 'Efectivo'; ?></p>
    </div>
    
    <div class="cufe-info">
        <div class="section-title">INFORMACIÓN TÉCNICA</div>
        <?php if (!empty($datos_factura['cufe'])): ?>
            <p><strong>CUFE:</strong> <?php echo htmlspecialchars($datos_factura['cufe']); ?></p>
        <?php elseif (!empty($datos_factura['track_id'])): ?>
            <p><strong>TrackID:</strong> <?php echo htmlspecialchars($datos_factura['track_id']); ?></p>
        <?php else: ?>
            <p><strong>CUFE:</strong> Pendiente de validación DIAN</p>
        <?php endif; ?>
        
        <p><strong>Ambiente:</strong> <?php echo htmlspecialchars(ucfirst($datos_factura['ambiente'])); ?></p>
        
        <p><strong>Estado:</strong> 
            <span style="font-weight: bold; color: <?php echo ($datos_factura['estado'] === 'aceptado') ? 'green' : (($datos_factura['estado'] === 'rechazado') ? 'red' : 'orange'); ?>">
                <?php echo htmlspecialchars(ucfirst($datos_factura['estado'])); ?>
            </span>
        </p>
        
        <?php if (!empty($datos_factura['descripcion_estado'])): ?>
            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($datos_factura['descripcion_estado']); ?></p>
        <?php endif; ?>
        
        <?php if (!empty($datos_factura['fecha_validacion_dian'])): ?>
            <p><strong>Fecha validación:</strong> <?php echo date('d/m/Y H:i:s', strtotime($datos_factura['fecha_validacion_dian'])); ?></p>
        <?php endif; ?>
    </div>
    
    <?php if (isset($datos_factura['observaciones']) && !empty($datos_factura['observaciones'])): ?>
    <div class="notes">
        <div class="section-title">OBSERVACIONES</div>
        <p><?php echo nl2br(htmlspecialchars($datos_factura['observaciones'])); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Esta factura electrónica de venta ha sido validada por la DIAN</p>
        <p>Representación gráfica de la factura electrónica de venta</p>
        <p><?php echo htmlspecialchars($datos_factura['company_name'] ?: $datos_factura['emisor_razon_social']); ?> - NIT: <?php echo htmlspecialchars($datos_factura['company_nit'] ?: $datos_factura['emisor_nit']); ?></p>
    </div>
</body>
</html>