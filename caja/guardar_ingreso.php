<?php
date_default_timezone_set('America/Lima');
include '../config/db.php';

// AUTO-REPARACIÓN (Por si acaso)
$cols = ["ALTER TABLE movimientos_caja ADD COLUMN monto_pagado DECIMAL(10,2) DEFAULT 0.00", 
         "ALTER TABLE movimientos_caja ADD COLUMN estado_pago VARCHAR(20) DEFAULT 'Pagado'",
         "ALTER TABLE movimientos_caja ADD COLUMN cliente_nombre VARCHAR(150) DEFAULT NULL"];
foreach($cols as $sql) { try { $pdo->exec($sql); } catch(Exception $e){} }

// DATOS
$tipo_ingreso = $_POST['tipo_ingreso'];
$monto_total  = floatval($_POST['monto']);
$concepto     = $_POST['concepto'] ?? '';
$fecha_hoy    = date('Y-m-d H:i:s');

// PAGO (LÓGICA CRÍTICA: Credito sin tilde)
$metodo_pago  = $_POST['metodo_pago']; // Recibe 'Credito' o 'Efectivo'
// Si es Credito, toma el adelanto. Si es Efectivo, toma el total.
$monto_inicial = ($metodo_pago === 'Credito') ? floatval($_POST['monto_amortizado']) : $monto_total;

// ESTADO
$estado_pago = ($monto_inicial >= $monto_total - 0.10) ? 'Pagado' : 'Pendiente';

// RESTO DE DATOS
$id_parroquia = $_POST['id_parroquia'] ?? null;
$id_inquilino = $_POST['id_inquilino'] ?? null;
$id_producto  = $_POST['id_producto'] ?? null;
$cantidad     = $_POST['cantidad'] ?? 0;
$ticket_man   = $_POST['ticket_manual'] ?? '';

$tipo_cli = $_POST['tipo_cliente'] ?? '';
$id_par_v = $_POST['id_parroquia_venta'] ?? null;
$cli_ext  = $_POST['cliente_nombre'] ?? null;
$cliente_final = null;

if ($tipo_cli == 'parroquia' && $id_par_v) $id_parroquia = $id_par_v;
if ($tipo_cli == 'otro' && $cli_ext) $cliente_final = $cli_ext;

if (empty($concepto)) $concepto = $id_producto ? "Venta Mercadería" : "Ingreso Varios";

try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO movimientos_caja 
            (fecha, id_tipo_ingreso, monto, monto_pagado, concepto_detalle, id_parroquia, id_inquilino, id_producto, cantidad, metodo_pago, ticket_manual, cliente_nombre, estado_pago) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $fecha_hoy, $tipo_ingreso, $monto_total, $monto_inicial, $concepto,
        !empty($id_parroquia)?$id_parroquia:null, !empty($id_inquilino)?$id_inquilino:null, !empty($id_producto)?$id_producto:null,
        $cantidad, $metodo_pago, $ticket_man, $cliente_final, $estado_pago
    ]);
    
    $id_mov = $pdo->lastInsertId();

    if (($tipo_ingreso==2 || $tipo_ingreso==3) && $id_producto) {
        $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?")->execute([$cantidad, $id_producto]);
    }

    $pdo->commit();

    $saldo = $monto_total - $monto_inicial;
    $msg_estado = ($estado_pago == 'Pagado') ? '<h2 class="text-success">¡Venta Exitosa!</h2>' : '<h2 class="text-warning">Registrado con Deuda</h2>';
    $msg_saldo  = ($saldo > 0) ? "<div class='alert alert-danger'><strong>Adelanto:</strong> S/. $monto_inicial <br> <strong>Saldo Pendiente:</strong> S/. ".number_format($saldo,2)."</div>" : "";

    echo '<!DOCTYPE html><html lang="es"><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#F9F7F2;text-align:center;padding-top:50px;}</style></head>
    <body><div class="container"><div class="card p-5 shadow" style="max-width:500px;margin:auto;">
        '.$msg_estado.'
        <p class="lead">Recibo #'.$id_mov.' | Total: S/. '.number_format($monto_total, 2).'</p>
        '.$msg_saldo.'
        <a href="registrar_ingreso.php" class="btn btn-dark w-100 mt-3">Volver</a>
    </div></div></body></html>';

} catch (Exception $e) {
    $pdo->rollBack(); echo "Error: ".$e->getMessage();
}
?>