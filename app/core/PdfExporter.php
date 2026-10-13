<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfExporter {
    private $dompdf;
    private $razon_social;
    private $ruc;
    private $direccion;
    private $telefono;
    private $email;
    private $logo_base64;

    public function __construct() {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', __DIR__ . '/../..');

        $this->dompdf = new Dompdf($options);

        // Datos de la empresa
        $this->razon_social = defined('FACTURACION_RAZON_SOCIAL') ? FACTURACION_RAZON_SOCIAL : 'MI EMPRESA SAC';
        $this->ruc = defined('FACTURACION_RUC') ? FACTURACION_RUC : '20561234567';
        $this->direccion = defined('FACTURACION_DIRECCION') ? FACTURACION_DIRECCION : 'Av. Principal 123';
        $this->telefono = defined('FACTURACION_TELEFONO') ? FACTURACION_TELEFONO : '(01) 555-5555';
        $this->email = defined('FACTURACION_EMAIL') ? FACTURACION_EMAIL : 'info@empresa.com';

        // ✅ Cargar logo en base64 (funciona siempre, sin depender de URL)
        $this->logo_base64 = $this->cargarLogoBase64();
    }

    /**
     * ✅ Cargar logo como base64 (robusto, sin depender del servidor web)
     */
    private function cargarLogoBase64() {
        $posibles_rutas = [
            __DIR__ . '/../../public/assets/img/logo.jpg',
            __DIR__ . '/../../public/assets/img/logo.png',
            __DIR__ . '/../../public/assets/img/logo.jpeg',
        ];

        foreach ($posibles_rutas as $ruta) {
            if (file_exists($ruta)) {
                $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
                $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($ruta));
            }
        }
        return '';
    }

    /**
     * Generar PDF con contenido HTML
     */
    public function generar($titulo, $contenido_html, $filtros = [], $orientacion = 'portrait') {
        $html = $this->construirHTML($titulo, $contenido_html, $filtros);

        $this->dompdf->loadHtml($html, 'UTF-8');
        $this->dompdf->setPaper('A4', $orientacion);
        $this->dompdf->render();

        $nombre = 'reporte_' . strtolower(str_replace(' ', '_', $titulo)) . '_' . date('Ymd_His') . '.pdf';
        $this->dompdf->stream($nombre, ['Attachment' => true]);
    }

    /**
     * Construir el HTML completo con encabezado y pie de página
     */
    private function construirHTML($titulo, $contenido, $filtros) {
        $fecha_generacion = date('d/m/Y H:i:s');
        $usuario = $_SESSION['nombre'] ?? 'Sistema';

        // Filtros en string
        $filtros_html = '';
        if (!empty($filtros)) {
            $filtros_html = '<div class="filtros">';
            foreach ($filtros as $key => $value) {
                if (!empty($value)) {
                    $filtros_html .= '<span class="filtro-item"><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ' . htmlspecialchars($value) . '</span>';
                }
            }
            $filtros_html .= '</div>';
        }

        // ✅ Logo (base64 o vacío)
        $logo_html = '';
        if (!empty($this->logo_base64)) {
            $logo_html = '<img src="' . $this->logo_base64 . '" style="max-height: 60px; max-width: 100%;">';
        } else {
            $logo_html = '<div style="font-size: 30pt; color: #0d6efd;">🏢</div>';
        }

        return '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>' . $titulo . '</title>
            <style>
                @page {
                    margin: 100px 40px 80px 40px;
                }
                * {
                    font-family: Helvetica, Arial, sans-serif;
                }
                body {
                    font-size: 10pt;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }

                /* Encabezado */
                header {
                    position: fixed;
                    top: -80px;
                    left: 0;
                    right: 0;
                    height: 70px;
                    border-bottom: 2px solid #0d6efd;
                    padding-bottom: 5px;
                }
                .header-empresa {
                    float: left;
                    width: 70%;
                }
                .header-empresa h2 {
                    color: #0d6efd;
                    margin: 0;
                    font-size: 14pt;
                }
                .header-empresa p {
                    margin: 2px 0;
                    font-size: 8pt;
                    color: #666;
                }
                .header-logo {
                    float: right;
                    text-align: right;
                    width: 30%;
                }

                /* Título del reporte */
                .titulo-reporte {
                    text-align: center;
                    background: #0d6efd;
                    color: white;
                    padding: 8px 15px;
                    margin: 15px 0;
                    font-size: 13pt;
                    font-weight: bold;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }

                /* Filtros */
                .filtros {
                    background: #f8f9fa;
                    border-left: 4px solid #0d6efd;
                    padding: 8px 12px;
                    margin-bottom: 15px;
                    font-size: 9pt;
                }
                .filtro-item {
                    display: inline-block;
                    margin-right: 15px;
                    color: #495057;
                }

                /* Info de generación */
                .info-generacion {
                    text-align: right;
                    font-size: 8pt;
                    color: #6c757d;
                    margin-bottom: 15px;
                }

                /* Tablas */
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                    font-size: 9pt;
                }
                table thead {
                    background: #212529;
                    color: white;
                }
                table th {
                    padding: 7px 6px;
                    text-align: left;
                    font-weight: bold;
                    font-size: 9pt;
                    border: 1px solid #dee2e6;
                }
                table td {
                    padding: 5px 6px;
                    border: 1px solid #dee2e6;
                }
                table tbody tr:nth-child(even) {
                    background: #f8f9fa;
                }
                table tfoot {
                    background: #212529;
                    color: white;
                    font-weight: bold;
                }
                table tfoot th {
                    padding: 7px 6px;
                    border: 1px solid #dee2e6;
                }

                /* Resumen / Cards */
                .resumen-container {
                    margin-bottom: 15px;
                    width: 100%;
                }
                .resumen-item {
                    display: inline-block;
                    width: 23%;
                    margin-right: 1%;
                    padding: 10px;
                    background: #f8f9fa;
                    border-left: 4px solid #0d6efd;
                    text-align: center;
                    vertical-align: top;
                }
                .resumen-item h4 {
                    margin: 0;
                    font-size: 8pt;
                    color: #6c757d;
                    text-transform: uppercase;
                }
                .resumen-item .valor {
                    font-size: 13pt;
                    font-weight: bold;
                    color: #0d6efd;
                    margin: 5px 0;
                }
                .resumen-item.success { border-color: #198754; }
                .resumen-item.success .valor { color: #198754; }
                .resumen-item.warning { border-color: #ffc107; }
                .resumen-item.warning .valor { color: #ffc107; }
                .resumen-item.info { border-color: #0dcaf0; }
                .resumen-item.info .valor { color: #0dcaf0; }
                .resumen-item.danger { border-color: #dc3545; }
                .resumen-item.danger .valor { color: #dc3545; }

                /* Badges */
                .badge {
                    display: inline-block;
                    padding: 2px 6px;
                    font-size: 8pt;
                    border-radius: 3px;
                    background: #6c757d;
                    color: white;
                }
                .badge-success { background: #198754; }
                .badge-danger { background: #dc3545; }
                .badge-warning { background: #ffc107; color: #000; }
                .badge-info { background: #0dcaf0; color: #000; }
                .badge-primary { background: #0d6efd; }
                .badge-secondary { background: #6c757d; }

                /* Firmas */
                .firmas {
                    margin-top: 40px;
                    width: 100%;
                }
                .firma {
                    display: inline-block;
                    width: 30%;
                    text-align: center;
                    margin: 0 1.5%;
                    vertical-align: top;
                }
                .firma-linea {
                    border-top: 1px solid #333;
                    margin: 35px auto 5px;
                    width: 90%;
                }
                .firma p {
                    margin: 2px 0;
                    font-size: 9pt;
                }

                /* Pie de página */
                footer {
                    position: fixed;
                    bottom: -60px;
                    left: 0;
                    right: 0;
                    height: 50px;
                    border-top: 1px solid #dee2e6;
                    padding-top: 5px;
                    font-size: 8pt;
                    color: #6c757d;
                    text-align: center;
                }
                .footer-empresa {
                    text-align: left;
                    display: inline-block;
                    width: 70%;
                }
                .footer-pagina {
                    text-align: right;
                    display: inline-block;
                    width: 25%;
                }
                .pagina-numero:after {
                    content: counter(page);
                }

                /* Utilidades */
                .text-center { text-align: center; }
                .text-end { text-align: right; }
                .text-start { text-align: left; }
                .fw-bold { font-weight: bold; }
                .text-success { color: #198754; }
                .text-danger { color: #dc3545; }
                .text-muted { color: #6c757d; }
                .small { font-size: 8pt; }
                .mb-0 { margin-bottom: 0; }
                .mt-3 { margin-top: 15px; }
                .page-break { page-break-before: always; }
            </style>
        </head>
        <body>
            <!-- ENCABEZADO -->
            <header>
                <div class="header-empresa">
                    <h2>' . $this->razon_social . '</h2>
                    <p><strong>RUC:</strong> ' . $this->ruc . '</p>
                    <p><strong>Dirección:</strong> ' . $this->direccion . '</p>
                    <p><strong>Teléfono:</strong> ' . $this->telefono . ' | <strong>Email:</strong> ' . $this->email . '</p>
                </div>
                <div class="header-logo">
                    ' . $logo_html . '
                </div>
            </header>

            <!-- PIE DE PÁGINA -->
            <footer>
                <div class="footer-empresa">
                    ' . $this->razon_social . ' - Reporte generado automáticamente
                </div>
                <div class="footer-pagina">
                    Página <span class="pagina-numero"></span>
                </div>
            </footer>

            <!-- CONTENIDO -->
            <main>
                <div class="titulo-reporte">' . $titulo . '</div>
                ' . $filtros_html . '
                <div class="info-generacion">
                    <strong>Generado por:</strong> ' . $usuario . ' | <strong>Fecha:</strong> ' . $fecha_generacion . '
                </div>
                ' . $contenido . '
            </main>
        </body>
        </html>
        ';
    }
}
