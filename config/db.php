<?php
// Credenciales extraídas de tu panel InfinityFree
$host = 'sql310.infinityfree.com';
$db   = 'if0_40786255_catedral';
$user = 'if0_40786255';
$pass = 'MesaIGS22a'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Si llegamos aquí, la conexión fue exitosa
} catch (PDOException $e) {
    // Si falla, mostrará el error en pantalla
    die("❌ Error de conexión a la Base de Datos: " . $e->getMessage());
}
?>