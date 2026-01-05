<?php
// admin/reportes.php - REPORTE CONTABLE INTELIGENTE (CÁLCULO EXACTO)
include_once '../config/db.php';

// AUTO-REPARACIÓN
try { $pdo->exec("ALTER TABLE movimientos_caja ADD COLUMN monto_pagado DECIMAL(10,2) DEFAULT 0.00"); } catch (Exception $e) {}

$filtro_parroquia = $_GET['f_parroquia'] ?? '';
$tab_activa = $_GET['tab'] ?? 'general';

$movimientos = [];
$parroquias_lista = [];
$amortizaciones_por_padre = []; // Array para guardar cuánto se ha amortizado a cada deuda

// TOTALES FINALES
$caja_real_total = 0;
$deuda_real_total = 0;

try {
    $parroquias_lista = $pdo->query("SELECT * FROM parroquias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT m.*, 
            p.nombre as nombre_parroquia, 
            i.nombre as nombre_inquilino,
            prod.nombre as nombre_producto
            FROM movimientos_caja m 
            LEFT JOIN parroquias p ON m.id_parroquia = p.id 
            LEFT JOIN inquilinos i ON m.id_inquilino = i.id
            LEFT JOIN productos prod ON m.id_producto = prod.id
            WHERE 1=1 ";

    if ($tab_activa == 'parroquia' && $filtro_parroquia) $sql .= " AND m.id_parroquia = $filtro_parroquia ";
    if ($tab_activa == 'kardex') $sql .= " AND m.id_producto IS NOT NULL ";

    $sql .= " ORDER BY m.fecha DESC LIMIT 300";
    $movimientos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // PASO 1: DETECTAR AMORTIZACIONES (HIJOS) PARA RESTARLAS AL PADRE
    // Así evitamos duplicar el dinero en el reporte visual
    foreach($movimientos as $m) {
        // Buscamos tickets que digan "AMORTIZACIÓN DEUDA #ID"
        if (preg_match('/AMORTIZACIÓN DEUDA #(\d+)/', $m['concepto_detalle'], $matches)) {
            $id_padre = $matches[1];
            if (!isset($amortizaciones_por_padre[$id_padre])) $amortizaciones_por_padre[$id_padre] = 0;
            $amortizaciones_por_padre[$id_padre] += $m['monto'];
        }
    }

    // PASO 2: CALCULAR TOTALES REALES
    foreach($movimientos as $m) {
        $es_amortizacion = preg_match('/AMORTIZACIÓN DEUDA #(\d+)/', $m['concepto_detalle']);
        
        if ($es_amortizacion) {
            // Si es un ticket de pago, suma directo a la caja
            $caja_real_total += $m['monto'];
        } else {
            // Si es una venta/ingreso normal (o deuda padre)
            // Dinero Real = Lo que dice la BD que se pagó MENOS lo que vino de amortizaciones posteriores
            $acumulado_db = ($m['monto_pagado'] > 0) ? $m['monto_pagado'] : ($m['estado_pago']=='Pagado' ? $m['monto'] : 0);
            
            // Restamos las amortizaciones para obtener SOLO EL PAGO INICIAL
            $amortizado_despues = $amortizaciones_por_padre[$m['id']] ?? 0;
            $pago_inicial_real = $acumulado_db - $amortizado_despues;
            
            // Solo sumamos el inicial a la caja (porque las amortizaciones ya se sumaron arriba)
            // Corrección: Si el cálculo da negativo (por paginación), ponemos 0
            if ($pago_inicial_real < 0) $pago_inicial_real = 0;
            
            $caja_real_total += $pago_inicial_real;

            // Deuda Pendiente
            $saldo = $m['monto'] - $acumulado_db;
            if ($saldo > 0.1) $deuda_real_total += $saldo;
        }
    }

} catch (Exception $e) { echo "<div class='alert alert-danger'>".$e->getMessage()."</div>"; }
?>

<ul class="nav nav-tabs mb-4" style="border-bottom: 2px solid var(--color-oro);">
  <li class="nav-item"><a class="nav-link <?php echo $tab_activa=='general'?'active fw-bold':'text-muted'; ?>" href="index.php?ver=reportes&tab=general"><i class="fas fa-list me-2"></i> Movimientos</a></li>
  <li class="nav-item"><a class="nav-link <?php echo $tab_activa=='parroquia'?'active fw-bold':'text-muted'; ?>" href="index.php?ver=reportes&tab=parroquia"><i class="fas fa-church me-2"></i> Por Parroquia</a></li>
  <li class="nav-item"><a class="nav-link <?php echo $tab_activa=='kardex'?'active fw-bold':'text-muted'; ?>" href="index.php?ver=reportes&tab=kardex"><i class="fas fa-boxes me-2"></i> Kardex</a></li>
</ul>

<?php if($tab_activa == 'parroquia'): ?>
<div class="card p-3 mb-4 bg-light border-0">
    <form action="index.php" method="GET" class="row g-3 align-items-center">
        <input type="hidden" name="ver" value="reportes"><input type="hidden" name="tab" value="parroquia">
        <div class="col-auto fw-bold text-vino">Parroquia:</div>
        <div class="col-auto"><select name="f_parroquia" class="form-select"><option value="">-- Ver Todas --</option><?php foreach($parroquias_lista as $pl): ?><option value="<?php echo $pl['id']; ?>" <?php echo $filtro_parroquia==$pl['id']?'selected':''; ?>><?php echo htmlspecialchars($pl['nombre']); ?></option><?php endforeach; ?></select></div>
        <div class="col-auto"><button type="submit" class="btn btn-vino">Filtrar</button></div>
    </form>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="p-3 border rounded bg-white border-start border-5 border-success shadow-sm">
            <small class="text-muted text-uppercase fw-bold">Dinero Real en Caja (Pagado)</small>
            <div class="fs-3 fw-bold text-success">S/. <?php echo number_format($caja_real_total, 2); ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="p-3 border rounded bg-white border-start border-5 border-danger shadow-sm">
            <small class="text-muted text-uppercase fw-bold">Por Cobrar (Saldos)</small>
            <div class="fs-3 fw-bold text-danger">S/. <?php echo number_format($deuda_real_total, 2); ?></div>
        </div>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light" style="font-family: 'Cinzel', serif; font-size: 0.85rem;">
                    <tr>
                        <th class="ps-4">Fecha</th>
                        <th>Detalle / Cliente</th>
                        <th class="text-end text-muted">Total Op.</th>
                        <th class="text-end text-success" style="background:#f0fff4;">Ingreso Caja</th>
                        <th class="text-end text-danger pe-4">Deuda</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.9rem;">
                    <?php foreach($movimientos as $mov): 
                        // LÓGICA VISUAL POR FILA
                        $es_amortizacion = preg_match('/AMORTIZACIÓN DEUDA/', $mov['concepto_detalle']);
                        
                        // Si es amortización, su monto es puro ingreso
                        if ($es_amortizacion) {
                            $monto_op = $mov['monto'];
                            $en_caja  = $mov['monto'];
                            $deuda    = 0;
                            $clase_fila = "";
                            $badge = '<span class="badge bg-info text-dark"><i class="fas fa-level-up-alt"></i> ABONO</span>';
                        } else {
                            // Si es venta/deuda padre
                            $monto_op = $mov['monto'];
                            $acumulado = ($mov['monto_pagado'] > 0) ? $mov['monto_pagado'] : ($mov['estado_pago']=='Pagado' ? $mov['monto'] : 0);
                            
                            // Calculamos el inicial REAL restando amortizaciones
                            $amortizado = $amortizaciones_por_padre[$mov['id']] ?? 0;
                            $en_caja = $acumulado - $amortizado;
                            if($en_caja < 0) $en_caja = 0; // Ajuste por si acaso

                            $deuda = $monto_op - $acumulado;
                            
                            $clase_fila = ($deuda > 0.1) ? "table-warning" : "";
                            $badge = ($deuda > 0.1) ? '<span class="badge bg-danger">DEUDA</span>' : '<span class="badge bg-success">VENTA</span>';
                        }

                        // Nombre Cliente
                        $cliente = "Varios";
                        if($mov['nombre_parroquia']) $cliente = $mov['nombre_parroquia'];
                        elseif($mov['nombre_inquilino']) $cliente = $mov['nombre_inquilino'];
                        elseif($mov['cliente_nombre']) $cliente = $mov['cliente_nombre'];
                    ?>
                    <tr class="<?php echo $clase_fila; ?>">
                        <td class="ps-4 text-nowrap">
                            <?php echo date('d/m/Y', strtotime($mov['fecha'])); ?>
                            <br><small class="text-muted"><?php echo date('H:i', strtotime($mov['fecha'])); ?></small>
                        </td>
                        <td>
                            <strong class="text-vino"><?php echo htmlspecialchars($cliente); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($mov['concepto_detalle']); ?></small>
                            <?php echo $badge; ?>
                        </td>
                        
                        <td class="text-end text-muted">
                            <?php echo $es_amortizacion ? '-' : 'S/. '.number_format($monto_op, 2); ?>
                        </td>

                        <td class="text-end fw-bold text-success" style="background:#f0fff4;">
                            S/. <?php echo number_format($en_caja, 2); ?>
                        </td>

                        <td class="text-end pe-4">
                            <?php if($deuda > 0.1): ?>
                                <span class="badge bg-danger">Falta: S/. <?php echo number_format($deuda, 2); ?></span>
                            <?php elseif($es_amortizacion): ?>
                                -
                            <?php else: ?>
                                <i class="fas fa-check text-success"></i>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>