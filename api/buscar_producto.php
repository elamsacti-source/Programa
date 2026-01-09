<?php
// API SIEMPRE debe devolver JSON, silenciamos errores HTML
ini_set('display_errors', 0);
header('Content-Type: application/json');

require '../config/db.php';

$q = $_GET['q'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE nombre LIKE ? OR codigo = ? LIMIT 10");
    $stmt->execute(["%$q%", $q]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultados);
} catch (Exception $e) {
    echo json_encode([]);
}
?>