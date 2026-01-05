<?php
// admin/cobranzas.php - GESTIÓN DE DEUDAS
// Se incluye dentro de index.php

$mensaje = "";
$tipo_mensaje = "";

// 1. PROCESAR PAGO (Cobrar Deuda)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'cobrar') {
    $id_movimiento = $_POST['id_movimiento'];
    
    try {
        $sql = "UPDATE movimientos_caja SET estado_pago = 'Pagado', metodo_pago = CONCAT(metodo_pago, ' (Cobrado)') WHERE id = ?";
        $pdo->prepare($sql)->execute([$id_movimiento]);
        
        $mensaje = "¡Deuda cobrada exitosamente!";
        $tipo_mensaje = "success";
    } catch (Exception $e) {
        $mensaje = "Error al procesar cobro: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// 2. CONSULTAR DEUDAS PENDIENTES
$deudas = [];
try {
    // Consulta inteligente para saber quién debe
    $sql = "SELECT m.*, 
            p.nombre as nombre_parroquia, 
            i.nombre as nombre_inquilino
            FROM movimientos_caja m 
            LEFT JOIN parroquias p ON m.id_parroquia = p.id 
            LEFT JOIN inquilinos i ON m.id_inquilino = i.id
            WHERE m.estado_pago = 'Pendiente'
            ORDER BY m.fecha ASC"; // Las más antiguas primero
            
    $deudas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>

<?php if($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
        <?php echo $mensaje; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-custom">
            <div class="card-header-custom bg-danger text-white" style="background: #a94442 !important;">
                <i class="fas fa-exclamation-circle me-2"></i> Listado de Pagos Pendientes
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Fecha Emisión</th>
                                <th>Deudor (Cliente)</th>
                                <th>Concepto</th>
                                <th>Monto Deuda</th>
                                <th class="text-end pe-4">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($deudas)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i><br>
                                        <span class="text-muted">¡Excelente! No hay deudas pendientes.</span>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($deudas as $d): ?>
                                <tr>
                                    <td class="ps-4">
                                        <?php echo date('d/m/Y', strtotime($d['fecha'])); ?>
                                        <br><small class="text-muted"><?php echo date('h:i A', strtotime($d['fecha'])); ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                            // Lógica para mostrar nombre del deudor
                                            if($d['nombre_parroquia']) echo '<i class="fas fa-church text-secondary me-1"></i> <strong>'.$d['nombre_parroquia'].'</strong>';
                                            elseif($d['nombre_inquilino']) echo '<i class="fas fa-building text-secondary me-1"></i> <strong>'.$d['nombre_inquilino'].'</strong>';
                                            elseif(!empty($d['cliente_nombre'])) echo '<i class="fas fa-user text-secondary me-1"></i> <strong>'.$d['cliente_nombre'].'</strong>';
                                            else echo '<span class="text-muted">Particular</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($d['concepto_detalle']); ?>
                                        <span class="badge bg-warning text-dark ms-2">Pendiente</span>
                                    </td>
                                    <td class="fw-bold text-danger fs-5">
                                        S/. <?php echo number_format($d['monto'], 2); ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form method="POST" onsubmit="return confirm('¿Confirma que ha recibido el pago de S/. <?php echo $d['monto']; ?>?');">
                                            <input type="hidden" name="accion" value="cobrar">
                                            <input type="hidden" name="id_movimiento" value="<?php echo $d['id']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm text-white fw-bold">
                                                <i class="fas fa-hand-holding-usd me-1"></i> REGISTRAR COBRO
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>