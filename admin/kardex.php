<?php
require '../config/db.php';
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: ../index.php"); exit; }

$producto_id = $_GET['producto_id'] ?? '';
$productos = $pdo->query("SELECT id, nombre FROM productos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

$where = $producto_id ? "WHERE k.producto_id = ?" : "";
$params = $producto_id ? [$producto_id] : [];
$sql = "SELECT k.*, p.nombre as producto, p.es_granel FROM kardex k JOIN productos p ON k.producto_id = p.id $where ORDER BY k.id DESC LIMIT 50";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <div class="navbar">
        <span class="brand">KARDEX</span>
        <a href="dashboard.php" class="btn btn-dark" style="width:auto; padding:5px 15px; font-size:0.7rem;">VOLVER</a>
    </div>

    <div class="container">
        
        <form method="GET" style="margin-bottom:15px;">
            <select name="producto_id" onchange="this.form.submit()" style="margin:0;">
                <option value="">TODOS LOS PRODUCTOS</option>
                <?php foreach($productos as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $producto_id ? 'selected' : '' ?>>
                        <?= $p['nombre'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <table>
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>PRODUCTO</th>
                    <th>TIPO</th>
                    <th style="text-align:right;">CANTIDAD</th>
                    <th style="text-align:right;">SALDO</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($movimientos as $m): 
                    $esVenta = ($m['tipo'] == 'VENTA');
                    // Color y Signo para identificar sin necesidad de columna extra
                    $color = $esVenta ? '#ff4444' : '#10b981'; 
                    $signo = $esVenta ? '-' : '+';
                ?>
                <tr>
                    <td data-label="FECHA"><?= date('d/m H:i', strtotime($m['fecha'])) ?></td>
                    
                    <td data-label="PRODUCTO"><?= $m['producto'] ?></td>
                    
                    <td data-label="TIPO">
                        <?= $esVenta ? '<span class="badge-out">SALIDA</span>' : '<span class="badge-in">ENTRADA</span>' ?>
                    </td>
                    
                    <td data-label="CANTIDAD" style="color: <?= $color ?>;">
                        <?= $signo . number_format($m['cantidad'], $m['es_granel']?3:0) ?>
                    </td>
                    
                    <td data-label="SALDO">
                        <?= number_format($m['stock_saldo'], $m['es_granel']?3:0) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</body>
</html>