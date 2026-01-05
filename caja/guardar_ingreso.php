<?php
// Habilitar reporte de errores para ver si algo falla
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config/db.php';

// 1. Recibir datos del formulario
$tipo_ingreso = $_POST['tipo_ingreso'] ?? null;
$monto        = $_POST['monto'] ?? 0;
$concepto     = $_POST['concepto'] ?? '';
$fecha_hoy    = date('Y-m-d H:i:s');

// Variables opcionales
$id_parroquia = $_POST['id_parroquia'] ?? null;
$id_producto  = $_POST['id_producto'] ?? null;
$cantidad     = $_POST['cantidad'] ?? 0;
$id_inquilino = $_POST['id_inquilino'] ?? null;
$detalle_per  = $_POST['detalle_periodo'] ?? '';

// Validación básica
if (!$tipo_ingreso || $monto <= 0) {
    die("Error: Faltan datos obligatorios o el monto es cero.");
}

// Completar concepto si está vacío
if (empty($concepto)) {
    if ($id_parroquia) $concepto = "Aporte de Parroquia";
    elseif ($id_inquilino) $concepto = "Pago de Alquiler - $detalle_per";
    elseif ($id_producto) $concepto = "Venta de Material";
    else $concepto = "Ingreso General";
}

try {
    // Iniciar transacción (Todo o nada)
    $pdo->beginTransaction();

    // 2. Insertar en la tabla principal de MOVIMIENTOS
    $sqlMov = "INSERT INTO movimientos_caja 
               (fecha, id_tipo_ingreso, monto, concepto_detalle, id_parroquia, id_inquilino) 
               VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sqlMov);
    // Nota: Si id_parroquia viene vacío (''), lo convertimos a NULL para la base de datos
    $stmt->execute([
        $fecha_hoy, 
        $tipo_ingreso, 
        $monto, 
        $concepto, 
        !empty($id_parroquia) ? $id_parroquia : null,
        !empty($id_inquilino) ? $id_inquilino : null
    ]);
    
    // Obtenemos el ID del recibo generado
    $id_movimiento = $pdo->lastInsertId();

    // 3. Si es VENTA (Tipo 2 o 3), descontar STOCK y guardar detalle
    if (($tipo_ingreso == 2 || $tipo_ingreso == 3) && $id_producto) {
        
        // A) Descontar del inventario
        $sqlStock = "UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?";
        $stmtStock = $pdo->prepare($sqlStock);
        $stmtStock->execute([$cantidad, $id_producto]);

        // B) Guardar detalle de venta (Opcional, pero recomendado)
        // Si no creaste la tabla 'detalle_venta_items', puedes borrar estas 3 lineas siguientes:
        // $sqlDetalle = "INSERT INTO detalle_venta_items (id_movimiento, id_producto, cantidad) VALUES (?, ?, ?)";
        // $stmtDet = $pdo->prepare($sqlDetalle);
        // $stmtDet->execute([$id_movimiento, $id_producto, $cantidad]);
    }

    // Confirmar cambios
    $pdo->commit();

    // 4. Redireccionar con mensaje de éxito (Diseño Huacho)
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&display=swap" rel="stylesheet">
        <style>body{background:#F9F7F2; font-family: "Cinzel", serif; text-align:center; padding-top:50px;}</style>
    </head>
    <body>
        <div class="container">
            <div class="card p-5 shadow border-0">
                <h1 class="text-success display-1">✝</h1>
                <h2 class="mb-4">Operación Registrada</h2>
                <p class="lead text-muted">El ingreso se ha guardado correctamente en los libros de la Diócesis.</p>
                <div class="mt-4">
                    <a href="registrar_ingreso.php" class="btn btn-dark btn-lg">Realizar otra operación</a>
                    <a href="../index.php" class="btn btn-outline-secondary btn-lg">Salir</a>
                </div>
            </div>
        </div>
    </body>
    </html>';

} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Error al guardar en base de datos: " . $e->getMessage();
}
?>