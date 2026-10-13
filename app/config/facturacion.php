<?php
/**
 * ============================================================
 * CONFIGURACIÓN DE FACTURACIÓN ELECTRÓNICA - SUNAT
 * ============================================================
 */

// ============================================
// DATOS DE LA EMPRESA (EMISOR)
// ============================================
define('FACTURACION_RUC', '20561234567');
define('FACTURACION_RAZON_SOCIAL', 'MI CAUTIVO DE AYABACA');
define('FACTURACION_NOMBRE_COMERCIAL', 'MI CAUTIVO DE AYABACA');
define('FACTURACION_UBIGEO', '200101');
define('FACTURACION_DEPARTAMENTO', 'PIURA');
define('FACTURACION_PROVINCIA', 'AYABACA');
define('FACTURACION_DISTRITO', 'AYABACA');
define('FACTURACION_URBANIZACION', 'CENTRO');
define('FACTURACION_DIRECCION', 'AV. 28 DE JULIO NRO. 123');
define('FACTURACION_EMAIL', 'cautivo_ayabaca@transporte.com');
define('FACTURACION_TELEFONO', '(073) 914914644');

// ============================================
// CERTIFICADO DIGITAL
// ============================================
define('FACTURACION_CERT_PATH', dirname(__DIR__, 2) . '/certs/certificado.pem');
define('FACTURACION_PRIVATE_KEY_PATH', dirname(__DIR__, 2) . '/certs/private_key.pem');

// ============================================
// CREDENCIALES SOL (SUNAT)
// ⚠️ CAMBIAR POR TUS CREDENCIALES REALES
// ============================================
define('FACTURACION_SOL_USER', '20561234567MODDATOS');
define('FACTURACION_SOL_PASS', 'moddatos');

// ============================================
// AMBIENTE SUNAT (solo aplica si MODO_SIMULACION = false)
// 'beta' = pruebas | 'prod' = producción
// ============================================
define('FACTURACION_AMBIENTE', 'beta');

// URLs de SUNAT según ambiente
define('FACTURACION_URL_BETA', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService');
define('FACTURACION_URL_PROD', 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService');

// ============================================
// CONFIGURACIÓN DE SERIES Y CORRELATIVOS
// ============================================
define('FACTURACION_SERIE_FACTURA', 'F001');
define('FACTURACION_SERIE_BOLETA', 'B001');
define('FACTURACION_SERIE_NOTA_CREDITO', 'FC01');
define('FACTURACION_SERIE_NOTA_DEBITO', 'FD01');

// ============================================
// CONFIGURACIÓN DE IGV
// ============================================
define('FACTURACION_IGV_PORCENTAJE', 18.00);
define('FACTURACION_MONEDA', 'PEN');

// ============================================
// MODO SIMULACIÓN
// true  = No envía a SUNAT, genera XML local y guarda como SIMULADO
// false = Envía realmente a SUNAT (requiere certificado)
// ============================================
define('FACTURACION_MODO_SIMULACION', true);


