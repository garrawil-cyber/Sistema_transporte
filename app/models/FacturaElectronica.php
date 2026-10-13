<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/facturacion.php';

use Greenter\See;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Ws\Services\SunatEndpoints;

class FacturaElectronica extends Model {
    protected $table = 'comprobantes_electronicos';
    private $see;

    public function __construct() {
        parent::__construct();
        $this->see = $this->initSee();
    }

    /**
     * Inicializar Greenter (See) - Adaptado a v5.x
     */
    private function initSee() {
        $see = new See();
        
        // 1. Configurar credenciales SOL (SUNAT)
        $see->setClaveSOL(
            FACTURACION_RUC,
            FACTURACION_SOL_USER,
            FACTURACION_SOL_PASS
        );
        
        // 2. Configurar certificado digital (solo si existe)
        if (file_exists(FACTURACION_CERT_PATH)) {
            $see->setCertificate(file_get_contents(FACTURACION_CERT_PATH));
        }
        
        // 3. Configurar endpoint (beta o producción)
        $endpoint = (FACTURACION_AMBIENTE === 'prod') 
            ? SunatEndpoints::FE_PRODUCCION 
            : SunatEndpoints::FE_BETA;
        $see->setService($endpoint);
        
        return $see;
    }

    /**
     * Crear objeto Company (empresa emisora)
     */
    private function getCompany() {
        return (new Company())
            ->setRuc(FACTURACION_RUC)
            ->setRazonSocial(FACTURACION_RAZON_SOCIAL)
            ->setNombreComercial(FACTURACION_NOMBRE_COMERCIAL)
            ->setAddress((new Address())
                ->setUbigueo(FACTURACION_UBIGEO)
                ->setDepartamento(FACTURACION_DEPARTAMENTO)
                ->setProvincia(FACTURACION_PROVINCIA)
                ->setDistrito(FACTURACION_DISTRITO)
                ->setUrbanizacion(FACTURACION_URBANIZACION)
                ->setDireccion(FACTURACION_DIRECCION));
    }

   /**
 * ============================================
 * EMITIR COMPROBANTE (con correlativo consistente)
 * ============================================
 * 
 * ✅ FIX CRÍTICO: Reutiliza el numero_comprobante generado por la venta.
 *    Antes había un doble incremento del correlativo, causando que
 *    ventas_pasajes.numero_comprobante y comprobantes_electronicos.numero_completo
 *    tuvieran números diferentes para la misma venta.
 */
public function emitir($venta_id, $tipo = 'BOLETA') {
    try {
        // 1. Obtener datos de la venta
        $venta = $this->getVentaData($venta_id);
        if (!$venta) {
            return ['success' => false, 'message' => 'Venta no encontrada'];
        }

        // 2. Verificar si ya existe comprobante
        $existente = $this->getByVenta($venta_id, $tipo);
        if ($existente) {
            return ['success' => false, 'message' => 'Ya existe un comprobante de este tipo para esta venta'];
        }

        // 3. ✅ FIX: Usar el número YA generado por la venta
        $numero_venta = $venta['numero_comprobante'] ?? null;

        if (empty($numero_venta) || !preg_match('/^([A-Z]{1})(\d{3})-(\d{1,8})$/', $numero_venta, $m)) {
            // Fallback: generar correlativo nuevo (caso raro)
            $serie = ($tipo === 'FACTURA') ? FACTURACION_SERIE_FACTURA : FACTURACION_SERIE_BOLETA;
            $correlativo = $this->siguienteCorrelativo($tipo, $serie);
            $numero_completo = $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);
            
            if (class_exists('Logger')) {
                try { Logger::info("Fallback correlativo: generado $numero_completo para venta $venta_id"); }
                catch (Exception $le) { error_log($le->getMessage()); }
            }
        } else {
            // ✅ Usar serie y correlativo de la venta
            $serie           = $m[1] . $m[2];
            $correlativo     = (int)$m[3];
            $numero_completo = $serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT);
        }

        // 4. Crear cliente
        $client = (new Client())
            ->setTipoDoc($venta['tipo_documento'] === 'DNI' ? '1' : '6')
            ->setNumDoc($venta['numero_documento'])
            ->setRznSocial($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']);
        
        if (!empty($venta['email']))     $client->setEmail($venta['email']);
        if (!empty($venta['direccion'])) $client->setAddress($venta['direccion']);

        // 5. Calcular totales
        $total    = (float)$venta['precio_pagado'];
        $subtotal = $total / (1 + (FACTURACION_IGV_PORCENTAJE / 100));
        $igv      = $total - $subtotal;

        // 6. Crear comprobante
        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($tipo === 'FACTURA' ? '01' : '03')
            ->setSerie($serie)
            ->setCorrelativo($correlativo)
            ->setFechaEmision(new \DateTime())
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda(FACTURACION_MONEDA)
            ->setCompany($this->getCompany())
            ->setClient($client)
            ->setMtoOperGravadas(round($subtotal, 2))
            ->setMtoIGV(round($igv, 2))
            ->setTotalImpuestos(round($igv, 2))
            ->setValorVenta(round($subtotal, 2))
            ->setSubTotal(round($total, 2))
            ->setMtoImpVenta(round($total, 2));

        // 7. Detalle
        $detail = (new SaleDetail())
            ->setCodProducto('P001')
            ->setUnidad('NIU')
            ->setCantidad(1)
            ->setDescripcion('Servicio de transporte: ' . $venta['origen'] . ' → ' . $venta['destino'] . ' - Asiento ' . $venta['numero_asiento'])
            ->setMtoBaseIgv(round($subtotal, 2))
            ->setPorcentajeIgv(FACTURACION_IGV_PORCENTAJE)
            ->setIgv(round($igv, 2))
            ->setTipAfeIgv('10')
            ->setTotalImpuestos(round($igv, 2))
            ->setMtoValorVenta(round($subtotal, 2))
            ->setMtoValorUnitario(round($subtotal, 2))
            ->setMtoPrecioUnitario(round($total, 2));

        $invoice->setDetails([$detail]);

        // 8. Leyenda
        $legend = (new Legend())
            ->setCode('1000')
            ->setValue('SON ' . $this->numeroALetras($total) . ' SOLES');
        $invoice->setLegends([$legend]);

        // ============================================================
        // 9. MODO SIMULACIÓN o REAL
        // ============================================================
        $modo_simulacion = defined('FACTURACION_MODO_SIMULACION') && FACTURACION_MODO_SIMULACION;
        $xml = null;
        $cdr = null;
        $cdr_code = null;
        $cdr_description = null;
        $estado = 'PENDIENTE';

        if ($modo_simulacion) {
            try {
                if (file_exists(FACTURACION_CERT_PATH)) {
                    try {
                        $xml = $this->see->getXmlSigned($invoice);
                    } catch (Exception $e) {
                        $xml = $this->generarXmlSinFirma($invoice);
                    }
                } else {
                    $xml = $this->generarXmlSinFirma($invoice);
                }
                
                $cdr_code = 'SIMULADO';
                $cdr_description = 'Comprobante generado en MODO SIMULACIÓN. No fue enviado a SUNAT.';
                $estado = 'SIMULADO';
                
                if (class_exists('Logger')) {
                    try { Logger::info('Comprobante simulado', ['venta_id' => $venta_id, 'numero' => $numero_completo]); }
                    catch (Exception $le) { error_log($le->getMessage()); }
                }
                
            } catch (Exception $e) {
                $cdr_description = 'Error en simulación: ' . $e->getMessage();
                $estado = 'RECHAZADO';
            }
        } else {
            try {
                $xml = $this->see->getXmlSigned($invoice);
                $result = $this->see->send($invoice);

                if ($result->isSuccess()) {
                    $cdr = $result->getCdrResponse();
                    $cdr_code = $cdr->getCode();
                    $cdr_description = $cdr->getDescription();
                    $estado = ($cdr_code === '0') ? 'ACEPTADO' : 'RECHAZADO';
                } else {
                    $cdr_description = $result->getError()->getMessage();
                    $estado = 'RECHAZADO';
                }
            } catch (Exception $e) {
                $cdr_description = 'Error al enviar a SUNAT: ' . $e->getMessage();
                $estado = 'RECHAZADO';
                
                if (class_exists('Logger')) {
                    try { Logger::error('Error SUNAT: ' . $e->getMessage()); }
                    catch (Exception $le) { error_log($le->getMessage()); }
                }
            }
        }

        // 10. Guardar en BD
        $data = [
            'venta_id'           => $venta_id,
            'tipo'               => $tipo,
            'serie'              => $serie,
            'correlativo'        => str_pad($correlativo, 8, '0', STR_PAD_LEFT),
            'numero_completo'    => $numero_completo,
            'cliente_tipo_doc'   => $venta['tipo_documento'],
            'cliente_numero_doc' => $venta['numero_documento'],
            'cliente_nombre'     => $venta['cliente_nombre'] . ' ' . $venta['cliente_apellido'],
            'subtotal'           => round($subtotal, 2),
            'igv'                => round($igv, 2),
            'total'              => round($total, 2),
            'moneda'             => FACTURACION_MONEDA,
            'xml'                => $xml,
            'cdr'                => $cdr ? json_encode($cdr) : null,
            'cdr_code'           => $cdr_code,
            'cdr_description'    => $cdr_description,
            'estado'             => $estado,
            'fecha_envio'        => date('Y-m-d H:i:s'),
            'usuario_id'         => $_SESSION['user_id'] ?? 1
        ];

        $this->create($data);

        // Mensaje según el modo
        if ($modo_simulacion) {
            $message = '🧪 SIMULACIÓN: Comprobante ' . $numero_completo . ' generado (no enviado a SUNAT)';
            $success = true;
        } else {
            $message = $estado === 'ACEPTADO' 
                ? '✅ Comprobante emitido correctamente' 
                : '❌ Error: ' . $cdr_description;
            $success = ($estado === 'ACEPTADO');
        }

        return [
            'success'         => $success,
            'message'         => $message,
            'numero_completo' => $numero_completo,
            'estado'          => $estado,
            'cdr_code'        => $cdr_code,
            'cdr_description' => $cdr_description,
            'modo_simulacion' => $modo_simulacion
        ];

    } catch (Exception $e) {
        if (class_exists('Logger')) {
            try { Logger::error('Error al emitir comprobante: ' . $e->getMessage()); }
            catch (Exception $le) { error_log($le->getMessage()); }
        }
        
        return [
            'success' => false,
            'message' => 'Error al emitir: ' . $e->getMessage()
        ];
    }
}

/**
 * ============================================
 * Generar XML sin firma (para simulación)
 * ============================================
 */
private function generarXmlSinFirma($invoice) {
    try {
        // Si Greenter tiene un método para generar XML sin firmar
        $xmlBuilder = new \Greenter\Xml\Builder\InvoiceBuilder();
        return $xmlBuilder->build($invoice);
    } catch (Exception $e) {
        // Si falla, generar XML de prueba simple
        return $this->generarXmlPrueba($invoice);
    }
}

/**
 * ============================================
 * Generar XML de prueba (fallback)
 * ============================================
 */
private function generarXmlPrueba($invoice) {
    $company = $invoice->getCompany();
    $client = $invoice->getClient();
    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<!-- XML GENERADO EN MODO SIMULACIÓN -->' . "\n";
    $xml .= '<!-- Este XML NO ha sido enviado a SUNAT -->' . "\n";
    $xml .= '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">' . "\n";
    $xml .= '  <ID>' . htmlspecialchars($invoice->getSerie() . '-' . $invoice->getCorrelativo()) . '</ID>' . "\n";
    $xml .= '  <IssueDate>' . date('Y-m-d') . '</IssueDate>' . "\n";
    $xml .= '  <InvoiceTypeCode>' . $invoice->getTipoDoc() . '</InvoiceTypeCode>' . "\n";
    $xml .= '  <DocumentCurrencyCode>' . $invoice->getTipoMoneda() . '</DocumentCurrencyCode>' . "\n";
    $xml .= '  <AccountingSupplierParty>' . "\n";
    $xml .= '    <Party>' . "\n";
    $xml .= '      <PartyIdentification><ID>' . $company->getRuc() . '</ID></PartyIdentification>' . "\n";
    $xml .= '      <PartyName><Name>' . htmlspecialchars($company->getRazonSocial()) . '</Name></PartyName>' . "\n";
    $xml .= '    </Party>' . "\n";
    $xml .= '  </AccountingSupplierParty>' . "\n";
    $xml .= '  <AccountingCustomerParty>' . "\n";
    $xml .= '    <Party>' . "\n";
    $xml .= '      <PartyIdentification><ID>' . $client->getNumDoc() . '</ID></PartyIdentification>' . "\n";
    $xml .= '      <PartyName><Name>' . htmlspecialchars($client->getRznSocial()) . '</Name></PartyName>' . "\n";
    $xml .= '    </Party>' . "\n";
    $xml .= '  </AccountingCustomerParty>' . "\n";
    $xml .= '  <LegalMonetaryTotal>' . "\n";
    $xml .= '    <LineExtensionAmount>' . $invoice->getMtoOperGravadas() . '</LineExtensionAmount>' . "\n";
    $xml .= '    <TaxInclusiveAmount>' . $invoice->getMtoImpVenta() . '</TaxInclusiveAmount>' . "\n";
    $xml .= '    <PayableAmount>' . $invoice->getMtoImpVenta() . '</PayableAmount>' . "\n";
    $xml .= '  </LegalMonetaryTotal>' . "\n";
    $xml .= '</Invoice>';
    
    return $xml;
}

    /**
     * Obtener datos de la venta
     */
    private function getVentaData($venta_id) {
        $sql = "SELECT vp.*, 
                       c.tipo_documento, c.numero_documento, 
                       c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                       c.telefono, c.email, c.direccion,
                       r.origen, r.destino,
                       a.numero_asiento,
                       b.placa
                FROM ventas_pasajes vp
                JOIN clientes c ON vp.cliente_id = c.id
                JOIN viajes v ON vp.viaje_id = v.id
                JOIN rutas r ON v.ruta_id = r.id
                JOIN asientos a ON vp.asiento_id = a.id
                JOIN buses b ON v.bus_id = b.id
                WHERE vp.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$venta_id]);
        return $stmt->fetch();
    }

    /**
     * Siguiente correlativo
     */
    private function siguienteCorrelativo($tipo, $serie) {
        try {
            $this->db->beginTransaction();
            
            $sql = "SELECT ultimo_correlativo FROM facturacion_correlativos 
                    WHERE tipo = ? AND serie = ? FOR UPDATE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tipo, $serie]);
            $row = $stmt->fetch();
            
            $nuevo = ($row['ultimo_correlativo'] ?? 0) + 1;
            
            if ($row) {
                $sql = "UPDATE facturacion_correlativos SET ultimo_correlativo = ? 
                        WHERE tipo = ? AND serie = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$nuevo, $tipo, $serie]);
            } else {
                $sql = "INSERT INTO facturacion_correlativos (tipo, serie, ultimo_correlativo) 
                        VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$tipo, $serie, $nuevo]);
            }
            
            $this->db->commit();
            return $nuevo;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Obtener comprobante por venta
     */
    public function getByVenta($venta_id, $tipo = null) {
        $sql = "SELECT * FROM comprobantes_electronicos WHERE venta_id = ?";
        $params = [$venta_id];
        
        if ($tipo) {
            $sql .= " AND tipo = ?";
            $params[] = $tipo;
        }
        
        $sql .= " AND estado != 'ANULADO' ORDER BY id DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Obtener todos los comprobantes de una venta
     */
    public function getByVentaTodos($venta_id) {
        $sql = "SELECT * FROM comprobantes_electronicos 
                WHERE venta_id = ? ORDER BY fecha_emision DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$venta_id]);
        return $stmt->fetchAll();
    }

    /**
     * Listar comprobantes paginados
     */
    public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
        $sql = "SELECT ce.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM comprobantes_electronicos ce
                JOIN usuarios u ON ce.usuario_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (ce.numero_completo LIKE ? OR ce.cliente_nombre LIKE ? OR ce.cliente_numero_doc LIKE ?)";
            $buscar = "%{$filtros['buscar']}%";
            $params = array_merge($params, [$buscar, $buscar, $buscar]);
        }
        if (!empty($filtros['tipo'])) {
            $sql .= " AND ce.tipo = ?";
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND ce.estado = ?";
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(ce.fecha_emision) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(ce.fecha_emision) <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        
        $sql .= " ORDER BY ce.fecha_emision DESC";
        
        return $this->paginate($sql, $params, $pagina, $por_pagina);
    }

    /**
     * Resumen
     */
    public function getResumen($filtros = []) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN tipo = 'FACTURA' THEN 1 ELSE 0 END) as facturas,
                    SUM(CASE WHEN tipo = 'BOLETA' THEN 1 ELSE 0 END) as boletas,
                    SUM(CASE WHEN estado = 'ACEPTADO' THEN 1 ELSE 0 END) as aceptados,
                    SUM(CASE WHEN estado = 'RECHAZADO' THEN 1 ELSE 0 END) as rechazados,
                    COALESCE(SUM(total), 0) as monto_total
                FROM comprobantes_electronicos
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(fecha_emision) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(fecha_emision) <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Número a letras
     */
    private function numeroALetras($numero) {
        $entero = floor($numero);
        $decimales = round(($numero - $entero) * 100);
        $letras = $this->enteroALetras($entero);
        return strtoupper($letras . ' CON ' . str_pad($decimales, 2, '0', STR_PAD_LEFT) . '/100');
    }

    private function enteroALetras($numero) {
        $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
        
        if ($numero == 0) return 'CERO';
        if ($numero == 100) return 'CIEN';
        
        $letras = '';
        
        if ($numero >= 1000) {
            $miles = floor($numero / 1000);
            $letras .= ($miles == 1) ? 'MIL ' : $this->enteroALetras($miles) . ' MIL ';
            $numero %= 1000;
        }
        
        if ($numero >= 100) {
            $letras .= $centenas[floor($numero / 100)] . ' ';
            $numero %= 100;
        }
        
        if ($numero >= 20) {
            $letras .= $decenas[floor($numero / 10)];
            if ($numero % 10 > 0) {
                $letras .= ' Y ' . $unidades[$numero % 10];
            }
        } elseif ($numero >= 10) {
            $letras .= $especiales[$numero - 10];
        } elseif ($numero > 0) {
            $letras .= $unidades[$numero];
        }
        
        return trim($letras);
    }

    /**
     * Descargar XML
     */
    public function descargarXML($id) {
        $comprobante = $this->find($id);
        if (!$comprobante || empty($comprobante['xml'])) {
            return false;
        }
        
        $nombre = $comprobante['cliente_numero_doc'] . '-' . $comprobante['tipo'] . '-' . 
                  $comprobante['numero_completo'] . '.xml';
        
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        echo $comprobante['xml'];
        exit;
    }
}