<?php
require_once 'app/config/config.php';
require_once 'app/config/Database.php';

$db = Database::getInstance()->getConnection();

// Nueva contraseña
$password = 'admin123456';
$hash = password_hash($password, PASSWORD_DEFAULT);

// Actualizar el usuario admin
$sql = "UPDATE usuarios SET password = ? WHERE email = 'admin@gmail.com'";
$stmt = $db->prepare($sql);
$stmt->execute([$hash]);

echo "Hash actualizado correctamente.<br>";
echo "Nuevo hash: " . $hash . "<br>";
echo "Ahora puedes iniciar sesión con admin@transporte.com y 123456";