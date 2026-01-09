<?php
require '../config/db.php';
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: ../index.php"); exit; }

// Filtro de Fecha (Por defecto HOY)
$fecha = $_GET['fecha'] ?? date('Y-m-d');

// Consulta Detallada
$sql = "SELECT 
            v.fecha, 
            u.nombre as vendedor, 
            p.nombre as producto,
            d.cantidad, 
            d.precio_venta, 
            d.costo_historico,
            (d.cantidad * d.precio_venta) as subtotal,
            ((d.cantidad * d.precio_venta) - (d.cantidad * d.costo_historico)) as ganancia
        FROM ventas v
        JOIN detalle_ventas d ON v.id = d.venta_id
        JOIN productos p ON d.producto_id = p.id
        JOIN usuarios u ON v.usuario_id = u.id
        WHERE DATE(v.fecha) = ?
        ORDER BY v.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$fecha]);
$filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cálculos de Totales
$total_venta = array_sum(array_column($filas, 'subtotal'));
$total_ganancia = array_sum(array_column($filas, 'ganancia'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Financieros</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Ajustes específicos para las tarjetas de resumen */
        .summary-grid {
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
            margin-bottom: 20px;
        }
        .stat-card {
            background: #080808;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }
        .stat-label {
            color: #666; 
            font-size: 0.7rem; 
            letter-spacing: 1px; 
            text-transform: uppercase; 
            margin-bottom: 5px;
            font-weight: 700;
        }
        .stat-value {
            font-size: 1.6rem; 
            font-weight: 800; 
            color: #fff;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <span class="brand">FINANZAS</span>
        <a href="dashboard.php" class="btn btn-dark" style="width:auto; padding:5px 15px; font-size:0.7rem;">VOLVER</a>
    </div>

    <div class="container">
        
        <form method="GET" style="margin-bottom: 20px;">
            <label style="color:var(--gold); font-size:0.75rem; font-weight:700; margin-bottom:5px; display:block;">SELECCIONAR FECHA:</label>
            <input type="date" name="fecha" value="<?= $fecha ?>" onchange="this.form.submit()" style="margin:0;">
        </form>

        <div class="summary-grid">
            <div class="stat-card">
                <div class="stat-label">VENTA TOTAL</div>
                <div class="stat-value">S/ <?= number_format($total_venta, 2) ?></div>
            </div>
            
            <div class="stat-card" style="border-color: var(--gold);">
                <div class="stat-label" style="color:var(--gold);">GANANCIA NETA</div>
                <div class="stat-value" style="color:var(--gold);">S/ <?= number_format($total_ganancia, 2) ?></div>
            </div>
        </div>

        <h3 style="color:#fff; font-size:1rem; margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid #222;">
            DETALLE DE VENTAS (<?= count($filas) ?>)
        </h3>

        <table>
            <thead>
                <tr>
                    <th>HORA</th>
                    <th>PRODUCTO</th>
                    <th>CANTIDAD</th>
                    <th>TOTAL</th>
                    <th>MARGEN</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($filas) > 0): ?>
                    <?php foreach($filas as $f): ?>
                    <tr>
                        <td data-label="HORA" style="color:#666; font-size:0.8rem;">
                            <?= date('H:i', strtotime($f['fecha'])) ?>
                        </td>
                        
                        <td data-label="PRODUCTO" style="font-weight:700; color:#fff;">
                            <?= $f['producto'] ?>
                            <div style="font-size:0.65rem; color:#444; font-weight:400; text-transform:uppercase; margin-top:2px;">
                                Vend: <?= $f['vendedor'] ?>
                            </div>
                        </td>
                        
                        <td data-label="CANTIDAD">
                            <?= round($f['cantidad'], 2) ?>
                        </td>
                        
                        <td data-label="TOTAL" style="color:var(--gold); font-weight:800;">
                            S/ <?= number_format($f['subtotal'], 2) ?>
                        </td>
                        
                        <td data-label="MARGEN" style="color:#10b981; font-weight:700;">
                            +<?= number_format($f['ganancia'], 2) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:#555;">NO HUBO VENTAS ESTE DÍA</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</body>
</html>