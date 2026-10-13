<?php
require_once __DIR__ . '/../vendor/autoload.php';

echo "<h1>Verificación de Greenter 5.x</h1>";

$clases = [
    'Greenter\See',
    'Greenter\Model\Company\Company',
    'Greenter\Model\Company\Address',
    'Greenter\Model\Client\Client',
    'Greenter\Model\Sale\Invoice',
    'Greenter\Model\Sale\SaleDetail',
    'Greenter\Model\Sale\Legend',
    'Greenter\Model\Sale\FormaPagos\FormaPagoContado',
    'Greenter\Ws\Services\SunatEndpoints'
];

foreach ($clases as $clase) {
    if (class_exists($clase)) {
        echo "✅ <strong>{$clase}</strong><br>";
    } else {
        echo "❌ <strong>{$clase}</strong> - NO ENCONTRADA<br>";
    }
}

// Verificar métodos de See
echo "<hr><h2>Métodos disponibles en Greenter\See:</h2>";
$metodos = get_class_methods('Greenter\See');
foreach ($metodos as $m) {
    if (strpos($m, 'set') === 0 || strpos($m, 'get') === 0) {
        echo "• <code>{$m}()</code><br>";
    }
}