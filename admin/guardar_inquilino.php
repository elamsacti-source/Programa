<?php
include '../config/db.php';

$nombre      = $_POST['nombre'] ?? '';
$responsable = $_POST['responsable'] ?? '';
$dia_pago    = $_POST['dia_pago'] ?? '';
$telefono    = $_POST['telefono'] ?? '';

if ($nombre && $responsable) {
    try {
        $sql = "INSERT INTO inquilinos (nombre, responsable, dia_pago, telefono) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $responsable, $dia_pago, $telefono]);
        
        // Redirigir con éxito
        header("Location: inquilinos.php?status=success");
    } catch (PDOException $e) {
        die("Error al guardar: " . $e->getMessage());
    }
} else {
    // Faltan datos
    header("Location: inquilinos.php?status=error");
}
?>