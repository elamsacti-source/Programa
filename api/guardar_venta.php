<?php
ini_set('display_errors', 0);
header('Content-Type: application/json');
require '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) { 
    echo json_encode(['success'=>false, 'message'=>'Datos no recibidos']); 
    exit; 
}

try {
    $pdo->beginTransaction(); 

    // 1. Cabecera Venta
    $total = 0;
    foreach($input['items'] as $i) $total += $i['subtotal'];
    
    $stmt = $pdo->prepare("INSERT INTO ventas (total, usuario_id) VALUES (?, ?)");
    $stmt->execute([$total, $input['usuario_id']]);
    $venta_id = $pdo->lastInsertId();

    // 2. Detalles y Kardex
    foreach ($input['items'] as $item) {
        // Verificar stock actual
        $stmtProd = $pdo->prepare("SELECT costo, stock, nombre FROM productos WHERE id = ?");
        $stmtProd->execute([$item['id']]);
        $productoDB = $stmtProd->fetch();

        if(!$productoDB) throw new Exception("Producto ID {$item['id']} no encontrado");

        // Validar Stock (Opcional: Si quieres permitir negativos, borra este IF)
        /*
        if ($productoDB['stock'] < $item['cantidad']) {
            throw new Exception("Stock insuficiente para: " . $productoDB['nombre']);
        }
        */

        // Insertar Detalle
        $stmtDet = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_venta, costo_historico) VALUES (?, ?, ?, ?, ?)");
        $stmtDet->execute([$venta_id, $item['id'], $item['cantidad'], $item['precio'], $productoDB['costo']]);

        // Descontar Stock
        $nuevoStock = $productoDB['stock'] - $item['cantidad'];
        $pdo->prepare("UPDATE productos SET stock = ? WHERE id = ?")->execute([$nuevoStock, $item['id']]);

        // Escribir en KARDEX
        $stmtKardex = $pdo->prepare("INSERT INTO kardex (producto_id, tipo, cantidad, stock_saldo) VALUES (?, 'VENTA', ?, ?)");
        $stmtKardex->execute([$item['id'], $item['cantidad'], $nuevoStock]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>