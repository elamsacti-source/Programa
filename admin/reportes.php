<?php
// admin/reportes.php - REPORTE GERENCIAL CON ESTADO DE DEUDAS

// 1. REPARACIÓN DB (Aseguramos columnas)
try {
    $pdo->exec("ALTER TABLE movimientos_caja ADD COLUMN estado_pago VARCHAR(20) DEFAULT 'Pagado'");
} catch (Exception $e) {}

// FILTROS
$filtro_parroquia = $_GET['f_parroquia'] ?? '';
$tab_activa = $_GET['tab'] ?? 'general';

// CONSULTAS
$movimientos = [];
$parroquias_lista = [];
$total_recaudado = 0; // Dinero real
$total_pendiente = 0; // Deudas

try {
    $parroquias_lista = $pdo->query("SELECT * FROM parroquias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

    // CONSULTA MAESTRA
    $sql = "SELECT m.*, 
            p.nombre as nombre_parroquia, 
            i.nombre as nombre_inquilino,
            prod.nombre as nombre_producto
            FROM movimientos_caja m 
            LEFT JOIN parroquias p ON m.id_parroquia = p.id 
            LEFT JOIN inquilinos i ON m.id_inquilino = i.id
            LEFT JOIN productos prod ON m.id_producto = prod.id
            WHERE 1=1 ";

    if ($tab_activa == 'parroquia' && $filtro_parroquia) {
        $sql .= " AND m.id_parroquia = $filtro_parroquia ";
    }
    
    if ($tab_activa == 'kardex') {
        $sql .= " AND m.id_producto IS NOT NULL ";
    }

    $sql .= " ORDER BY m.fecha DESC LIMIT 200"; // Últimos 200 movimientos
    
    $movimientos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // CALCULAR TOTALES SEPARADOS
    foreach($movimientos as $m) {
        if ($m['estado_pago'] == 'Pendiente') {
            $total_pendiente += $m['monto'];
        } else {
            $total_recaudado += $m['monto'];
        }
    }

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: ".$e->getMessage()."</div>";
}
?>

<ul class="nav nav-tabs mb-4" style="border-bottom: 2px solid var(--color-oro);">
  <li class="nav-item">
    <a class="nav-link <?php echo $tab_activa=='general'?'active fw-bold':'text-muted'; ?>" href="index.php?ver=reportes&tab=general">
        <i class="fas fa-list me-2"></i> Movimientos
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo $tab_activa=='parroquia'?'active fw-bold':'text-muted'; ?>" href="index.php?ver=reportes&tab=parroquia">
        <i class="fas fa-church me-2"></i> Por Parroquia
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo $tab_activa=='kardex'?'active fw-bold':'text-muted'; ?>" href="index.php?ver=reportes&tab=kardex">
        <i class="fas fa-boxes me-2"></i> Kardex Ventas
    </a>
  </li>
</ul>

<?php if($tab_activa == 'parroquia'): ?>
<div class="card p-3 mb-4 bg-light border-0">
    <form action="index.php" method="GET" class="row g-3 align-items-center">
        <input type="hidden" name="ver" value="reportes">
        <input type="hidden" name="tab" value="parroquia">
        <div class="col-auto"><label class="fw-bold text-vino">Parroquia:</label></div>
        <div class="col-auto">
            <select name="f_parroquia" class="form-select">
                <option value="">-- Ver Todas --</option>
                <?php foreach($parroquias_lista as $pl): ?>
                    <option value="<?php echo $pl['id']; ?>" <?php echo $filtro_parroquia==$pl['id']?'selected':''; ?>>
                        <?php echo htmlspecialchars($pl['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto"><button type="submit" class="btn btn-vino">Filtrar</button></div>
    </form>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="p-3 border rounded bg-white border-start border-5 border-success shadow-sm">
            <small class="text-muted text-uppercase fw-bold">Ingresos Reales (Caja)</small>
            <div class="fs-3 fw-bold text-success">S/. <?php echo number_format($total_recaudado, 2); ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="p-3 border rounded bg-white border-start border-5 border-danger shadow-sm">
            <small class="text-muted text-uppercase fw-bold">Por Cobrar (Deudas)</small>
            <div class="fs-3 fw-bold text-danger">S/. <?php echo number_format($total_pendiente, 2); ?></div>
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
                        <th>Detalle</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Monto</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.9rem;">
                    <?php foreach($movimientos as $mov): ?>
                    <tr class="<?php echo $mov['estado_pago']=='Pendiente' ? 'table-warning' : ''; ?>">
                        <td class="ps-4">
                            <?php echo date('d/m/Y', strtotime($mov['fecha'])); ?>
                            <br><small class="text-muted"><?php echo date('h:i A', strtotime($mov['fecha'])); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($mov['concepto_detalle']); ?>
                            <?php if($tab_activa=='kardex' && $mov['nombre_producto']): ?>
                                <br><span class="badge bg-secondary"><?php echo $mov['cantidad']; ?> x <?php echo $mov['nombre_producto']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                                if($mov['nombre_parroquia']) echo '<i class="fas fa-church text-secondary"></i> '.$mov['nombre_parroquia'];
                                elseif($mov['nombre_inquilino']) echo '<i class="fas fa-building text-secondary"></i> '.$mov['nombre_inquilino'];
                                elseif($mov['cliente_nombre']) echo '<i class="fas fa-user text-secondary"></i> '.$mov['cliente_nombre'];
                                else echo 'General';
                            ?>
                        </td>
                        <td>
                            <?php if($mov['estado_pago']=='Pendiente'): ?>
                                <span class="badge bg-danger">PENDIENTE</span>
                            <?php else: ?>
                                <span class="badge bg-success">PAGADO</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4 fw-bold">
                            S/. <?php echo number_format($mov['monto'], 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>