<?php
date_default_timezone_set('America/Lima');
include '../config/db.php';

// 1. AUTO-REPARACIÓN DE DB
$cols = [
    "ALTER TABLE movimientos_caja ADD COLUMN monto_pagado DECIMAL(10,2) DEFAULT 0.00", 
    "ALTER TABLE movimientos_caja ADD COLUMN estado_pago VARCHAR(20) DEFAULT 'Pagado'",
    "ALTER TABLE movimientos_caja ADD COLUMN cliente_nombre VARCHAR(150) DEFAULT NULL",
    "ALTER TABLE movimientos_caja ADD COLUMN archivo_comprobante VARCHAR(255) DEFAULT NULL" // Nueva columna para archivos
];
foreach($cols as $sql) { try { $pdo->exec($sql); } catch(Exception $e){} }

// 2. RECIBIR DATOS
$tipo_ingreso = $_POST['tipo_ingreso'];
$monto_total  = floatval($_POST['monto']);
$concepto     = $_POST['concepto'] ?? '';
$fecha_hoy    = date('Y-m-d H:i:s');

// PAGO
$metodo_pago  = $_POST['metodo_pago'];
$monto_inicial = ($metodo_pago === 'Credito') ? floatval($_POST['monto_amortizado']) : $monto_total;
$estado_pago = ($monto_inicial >= $monto_total - 0.10) ? 'Pagado' : 'Pendiente';

// DATOS OPERATIVOS
$id_parroquia = $_POST['id_parroquia'] ?? null;
$id_inquilino = $_POST['id_inquilino'] ?? null;
$id_producto  = $_POST['id_producto'] ?? null;
$cantidad     = $_POST['cantidad'] ?? 0;
$ticket_man   = $_POST['ticket_manual'] ?? '';

// CLIENTE
$tipo_cli = $_POST['tipo_cliente'] ?? '';
$id_par_v = $_POST['id_parroquia_venta'] ?? null;
$cli_ext  = $_POST['cliente_nombre'] ?? null;
$cliente_final = null;

if ($tipo_cli == 'parroquia' && $id_par_v) $id_parroquia = $id_par_v;
if ($tipo_cli == 'otro' && $cli_ext) $cliente_final = $cli_ext;

if (empty($concepto)) $concepto = $id_producto ? "Venta Mercadería" : "Ingreso Varios";

// 3. SUBIDA DE ARCHIVO
$ruta_archivo = null;
if (isset($_FILES['archivo_recibo']) && $_FILES['archivo_recibo']['error'] == 0) {
    $dir = 'uploads/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    
    $ext = pathinfo($_FILES['archivo_recibo']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = 'recibo_' . time() . '.' . $ext;
    $ruta_destino = $dir . $nombre_archivo;
    
    if (move_uploaded_file($_FILES['archivo_recibo']['tmp_name'], $ruta_destino)) {
        $ruta_archivo = $ruta_destino;
    }
}

try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO movimientos_caja 
            (fecha, id_tipo_ingreso, monto, monto_pagado, concepto_detalle, id_parroquia, id_inquilino, id_producto, cantidad, metodo_pago, ticket_manual, cliente_nombre, estado_pago, archivo_comprobante) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $fecha_hoy, $tipo_ingreso, $monto_total, $monto_inicial, $concepto,
        !empty($id_parroquia)?$id_parroquia:null, !empty($id_inquilino)?$id_inquilino:null, !empty($id_producto)?$id_producto:null,
        $cantidad, $metodo_pago, $ticket_man, $cliente_final, $estado_pago, $ruta_archivo
    ]);
    
    $id_mov = $pdo->lastInsertId();

    if (($tipo_ingreso==2 || $tipo_ingreso==3) && $id_producto) {
        $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?")->execute([$cantidad, $id_producto]);
    }

    $pdo->commit();

    // 4. RECIBO DIGITAL MEJORADO
    $saldo = $monto_total - $monto_inicial;
    
    // Obtener nombres para el recibo
    $nombre_cliente = "General";
    if($cliente_final) $nombre_cliente = $cliente_final;
    elseif($id_parroquia) {
        $r = $pdo->query("SELECT nombre FROM parroquias WHERE id=$id_parroquia")->fetch();
        $nombre_cliente = $r['nombre'];
    } elseif($id_inquilino) {
        $r = $pdo->query("SELECT nombre FROM inquilinos WHERE id=$id_inquilino")->fetch();
        $nombre_cliente = $r['nombre'];
    }

    echo '<!DOCTYPE html><html lang="es"><head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Lora&display=swap" rel="stylesheet">
    <style>
        body{background:#eee;padding:30px;font-family:"Lora",serif;}
        .recibo{background:#fff;max-width:600px;margin:auto;padding:40px;border:1px solid #ddd;box-shadow:0 10px 30px rgba(0,0,0,0.1);position:relative;}
        .recibo::before{content:"";position:absolute;top:0;left:0;right:0;height:8px;background:linear-gradient(to right, #5e1119, #c5a059);}
        .sello{color:#c5a059;font-size:3rem;position:absolute;top:30px;right:40px;opacity:0.2;transform:rotate(-15deg);border:3px solid #c5a059;padding:10px;border-radius:50%;font-family:"Cinzel";}
        h2{font-family:"Cinzel";color:#5e1119;}
        .label{font-size:0.85rem;color:#888;text-transform:uppercase;letter-spacing:1px;}
        .valor{font-size:1.1rem;font-weight:bold;color:#333;}
        .total-box{background:#f9f9f9;padding:15px;border-left:5px solid #5e1119;margin-top:20px;}
    </style>
    </head>
    <body>
        <div class="recibo">
            <div class="sello">PAGADO</div>
            <div class="text-center mb-4">
                <div style="font-size:2rem;color:#c5a059;">✝</div>
                <h4 class="mb-0 text-uppercase">Obispado de Huacho</h4>
                <small class="text-muted">Tesorería Diocesana</small>
            </div>
            
            <div class="row mb-4 border-bottom pb-3">
                <div class="col-6">
                    <div class="label">Nro. Recibo</div>
                    <div class="valor">#'.str_pad($id_mov, 6, "0", STR_PAD_LEFT).'</div>
                </div>
                <div class="col-6 text-end">
                    <div class="label">Fecha Emisión</div>
                    <div class="valor">'.date('d/m/Y h:i A').'</div>
                </div>
            </div>

            <div class="mb-3">
                <div class="label">Recibido De:</div>
                <div class="valor">'.$nombre_cliente.'</div>
            </div>

            <div class="mb-3">
                <div class="label">Concepto:</div>
                <div class="valor">'.htmlspecialchars($concepto).'</div>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <div class="label">Forma de Pago</div>
                    <div class="valor">'.$metodo_pago.'</div>
                </div>
                <div class="col-6">
                    <div class="label">Ref. Externa</div>
                    <div class="valor">'.($ticket_man ? $ticket_man : '-').'</div>
                </div>
            </div>

            <div class="total-box">
                <div class="d-flex justify-content-between mb-1">
                    <span>Importe Total:</span>
                    <strong>S/. '.number_format($monto_total, 2).'</strong>
                </div>
                <div class="d-flex justify-content-between mb-1 text-success">
                    <span>A Cuenta (Pagado):</span>
                    <strong>S/. '.number_format($monto_inicial, 2).'</strong>
                </div>
                '.($saldo > 0 ? '<div class="d-flex justify-content-between text-danger border-top pt-2 mt-2"><span>Saldo Pendiente:</span><strong>S/. '.number_format($saldo, 2).'</strong></div>' : '').'
            </div>

            '.($ruta_archivo ? '<div class="mt-4 text-center"><a href="'.$ruta_archivo.'" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-paperclip"></i> Ver Comprobante Adjunto</a></div>' : '').'

            <div class="mt-5 text-center d-print-none">
                <button onclick="window.print()" class="btn btn-secondary me-2"><i class="fas fa-print"></i> Imprimir</button>
                <a href="registrar_ingreso.php" class="btn btn-dark">Nuevo Registro</a>
            </div>
        </div>
    </body></html>';

} catch (Exception $e) {
    $pdo->rollBack(); echo "Error: ".$e->getMessage();
}
?>