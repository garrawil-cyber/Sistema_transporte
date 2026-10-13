<?php
require_once __DIR__ . '/app/core/Env.php';
Env::load(__DIR__ . '/.env');

echo "MAIL_USERNAME: " . Env::get('MAIL_USERNAME') . "\n";
echo "MAIL_HOST: " . Env::get('MAIL_HOST') . "\n";
echo "MAIL_PORT: " . Env::get('MAIL_PORT') . "\n";
echo "MODO_SIMULACION: " . var_export(Env::get('FACTURACION_MODO_SIMULACION'), true) . "\n";