<?php
// admin/reparar.php - REINICIO TOTAL DE LA TABLA MOVIMIENTOS
// Úsalo para corregir el error 1075 y limpiar la estructura.

include '../config/db.php';

echo "<h1>🛠️ Reinicio de Tabla 'movimientos_caja'</h1><hr>";

try {
    // 1. ELIMINAR LA TABLA CORRUPTA
    // Esto borra la tabla antigua que está dando problemas de índices
    $pdo->exec("DROP TABLE IF EXISTS movimientos_caja");
    echo "<p style='color:red;'>🗑️ Tabla antigua eliminada correctamente.</p>";

    // 2. CREAR LA TABLA NUEVA (CON TODAS LAS COLUMNAS CORRECTAS)
    // Incluye ID, Clientes, Deudas, Tickets, etc.
    $sql = "CREATE TABLE movimientos_caja (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha DATETIME,
        id_tipo_ingreso INT,
        monto DECIMAL(10,2) DEFAULT 0.00,
        concepto_detalle TEXT,
        id_parroquia INT NULL,
        id_inquilino INT NULL,
        id_producto INT NULL,
        cantidad INT DEFAULT 0,
        
        -- Nuevas columnas para la gestión avanzada
        metodo_pago VARCHAR(50) DEFAULT 'Efectivo',
        ticket_manual VARCHAR(50) DEFAULT NULL,
        cliente_nombre VARCHAR(150) DEFAULT NULL,
        estado_pago VARCHAR(20) DEFAULT 'Pagado',
        monto_pagado DECIMAL(10,2) DEFAULT 0.00
    )";
    
    $pdo->exec($sql);
    echo "<h2 style='color:green;'>✅ ÉXITO: Tabla creada desde cero con la estructura perfecta.</h2>";
    echo "<p>El sistema ahora tiene soporte para:</p>";
    echo "<ul>
            <li>Identificador único (ID) para cada recibo.</li>
            <li>Control de Pagos Parciales y Deudas.</li>
            <li>Clientes Externos y Parroquias.</li>
            <li>Tickets Manuales.</li>
          </ul>";

    echo "<hr>";
    echo "<a href='../caja/registrar_ingreso.php' style='background:#5e1119; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>&rarr; Ir a Caja y Probar</a>";

} catch (PDOException $e) {
    echo "<h3 style='color:red;'>❌ Error: " . $e->getMessage() . "</h3>";
}
?>