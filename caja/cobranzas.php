<?php
// caja/cobranzas.php - GESTIÓN PRECISA DE SALDOS
include '../config/db.php';
date_default_timezone_set('America/Lima');

$mensaje = ""; $tipo_mensaje = "";

// PROCESAR PAGO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] == 'pagar') {
    $id_deuda = $_POST['id_movimiento'];
    $monto_pago = floatval($_POST['monto_pago']);
    
    try {
        $pdo->beginTransaction();
        $deuda = $pdo->query("SELECT * FROM movimientos_caja WHERE id=$id_deuda")->fetch(PDO::FETCH_ASSOC);
        
        // 1. Ingreso de Caja (Ticket de cobro de hoy)
        $pdo->prepare("INSERT INTO movimientos_caja (fecha, id_tipo_ingreso, monto, concepto_detalle, id_parroquia, id_inquilino, metodo_pago, estado_pago, cliente_nombre) VALUES (NOW(), ?, ?, ?, ?, ?, 'Efectivo', 'Pagado', ?)")
            ->execute([$deuda['id_tipo_ingreso'], $monto_pago, "AMORTIZACIÓN #$id_deuda", $deuda['id_parroquia'], $deuda['id_inquilino'], $deuda['cliente_nombre']]);

        // 2. Actualizar Deuda Original
        $nuevo_pagado = $deuda['monto_pagado'] + $monto_pago;
        $estado = ($nuevo_pagado >= $deuda['monto'] - 0.1) ? 'Pagado' : 'Pendiente';
        $pdo->prepare("UPDATE movimientos_caja SET monto_pagado = ?, estado_pago = ? WHERE id = ?")->execute([$nuevo_pagado, $estado, $id_deuda]);
        
        $pdo->commit();
        $mensaje = "¡Cobro registrado!"; $tipo_mensaje = "success";
    } catch (Exception $e) { $pdo->rollBack(); $mensaje = "Error"; $tipo_mensaje = "danger"; }
}

// LISTA DEUDAS
$pendientes = [];
try {
    $sql = "SELECT m.*, (m.monto - m.monto_pagado) as saldo_real, p.nombre as p_nom, i.nombre as i_nom 
            FROM movimientos_caja m LEFT JOIN parroquias p ON m.id_parroquia=p.id LEFT JOIN inquilinos i ON m.id_inquilino=i.id 
            WHERE m.estado_pago = 'Pendiente' ORDER BY m.fecha ASC";
    $pendientes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cobranzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;800&family=Lora&display=swap" rel="stylesheet">
    <style>body{background:#fdfbf7;font-family:'Lora',serif;}.header{background:#2b2b2b;padding:20px;border-bottom:4px solid #c5a059;text-align:center;color:#fff;}.card-deuda{border-left:5px solid #d9534f;}</style>
</head>
<body>
<div class="header"><h2 style="font-family:'Cinzel'">Cobranzas</h2><a href="registrar_ingreso.php" class="text-white small">Volver</a></div>
<div class="container py-4">
    <?php if($mensaje): ?><div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div><?php endif; ?>
    
    <?php if(empty($pendientes)): ?><div class="text-center py-5 text-muted"><h3>No hay deudas pendientes</h3></div><?php else: ?>
    <div class="row">
        <?php foreach($pendientes as $p): ?>
        <div class="col-md-4 mb-3">
            <div class="card card-deuda p-3 shadow-sm">
                <div class="d-flex justify-content-between mb-2">
                    <small><?php echo date('d/m/Y', strtotime($p['fecha'])); ?></small>
                    <span class="badge bg-danger">DEUDA #<?php echo $p['id']; ?></span>
                </div>
                <h5 class="fw-bold"><?php echo $p['p_nom'] ?: ($p['i_nom'] ?: ($p['cliente_nombre'] ?: 'Particular')); ?></h5>
                <p class="small text-muted mb-2"><?php echo $p['concepto_detalle']; ?></p>
                
                <div class="bg-light p-2 rounded mb-3 small">
                    <div class="d-flex justify-content-between"><span>Total:</span> <strong>S/. <?php echo number_format($p['monto'], 2); ?></strong></div>
                    <div class="d-flex justify-content-between text-success"><span>A Cuenta:</span> <span>- S/. <?php echo number_format($p['monto_pagado'], 2); ?></span></div>
                    <div class="d-flex justify-content-between text-danger border-top mt-1 pt-1 fw-bold"><span>Saldo:</span> <span>S/. <?php echo number_format($p['saldo_real'], 2); ?></span></div>
                </div>

                <form method="POST" onsubmit="return confirm('¿Cobrar?');">
                    <input type="hidden" name="accion" value="pagar"><input type="hidden" name="id_movimiento" value="<?php echo $p['id']; ?>">
                    <div class="input-group">
                        <input type="number" name="monto_pago" class="form-control fw-bold text-success" value="<?php echo $p['saldo_real']; ?>" step="0.01" max="<?php echo $p['saldo_real']; ?>" required>
                        <button class="btn btn-success fw-bold">COBRAR</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</body>
</html>