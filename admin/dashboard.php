<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: ../index.php"); exit; }

// Consultas Rápidas
$fechaHoy = date('Y-m-d');
$dineroHoy = $pdo->query("SELECT SUM(total) FROM ventas WHERE DATE(fecha) = '$fechaHoy'")->fetchColumn() ?: 0;
$alertaStock = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock <= 5")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard | SUARCORP</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .stat-val { font-size: 3rem; font-weight: 800; color: var(--gold-bright); line-height: 1; margin-top: 10px; }
        .stat-label { color: #888; font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase; }
        .menu-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px; }
        .menu-btn { 
            background: #111; border: 1px solid #333; border-radius: 12px; padding: 20px; 
            text-align: center; text-decoration: none; color: #fff; transition: 0.3s;
            display: flex; flex-direction: column; align-items: center; justify-content: center; height: 120px;
        }
        .menu-btn:active { border-color: var(--gold); background: #000; }
        .menu-icon { font-size: 2rem; margin-bottom: 10px; }
        .menu-text { font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; }
    </style>
</head>
<body>

    <div class="navbar">
        <span class="brand" style="font-size: 1.2rem;">SUARCORP</span>
        <a href="../index.php?logout=1" style="color: #666; text-decoration: none; font-size: 0.7rem; border: 1px solid #333; padding: 5px 12px; border-radius: 20px;">SALIR</a>
    </div>

    <div style="padding: 20px; max-width: 600px; margin: 0 auto;">
        
        <h2 style="margin-bottom: 5px; font-size: 1.5rem; color: #fff;">HOLA, <span style="color:var(--gold);"><?= htmlspecialchars($_SESSION['nombre']) ?></span></h2>
        <p style="color: #666; font-size: 0.8rem; margin-bottom: 30px;">PANEL DE CONTROL - <?= date('Y') ?></p>

        <div class="card" style="margin-bottom: 30px; text-align: center; border-color: var(--gold);">
            <div class="stat-label">VENTAS DEL DÍA</div>
            <div class="stat-val">S/ <?= number_format($dineroHoy, 2) ?></div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr; gap: 15px; margin-bottom: 30px;">
            <div class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 20px;">
                <div>
                    <div class="stat-label" style="color: #ef4444;">STOCK BAJO</div>
                    <div style="font-size: 0.7rem; color: #555;">PRODUCTOS CRÍTICOS</div>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: #fff;"><?= $alertaStock ?></div>
            </div>
        </div>

        <h3 style="font-size: 0.9rem; color: #fff; border-bottom: 1px solid #222; padding-bottom: 10px;">ACCESOS RÁPIDOS</h3>
        
        <div class="menu-grid">
            <a href="productos.php" class="menu-btn">
                <span class="menu-icon">📦</span>
                <span class="menu-text">PRODUCTOS</span>
            </a>
            <a href="kardex.php" class="menu-btn">
                <span class="menu-icon">📋</span>
                <span class="menu-text">KARDEX</span>
            </a>
            <a href="reporte_ventas.php" class="menu-btn">
                <span class="menu-icon">💰</span>
                <span class="menu-text">REPORTES</span>
            </a>
            <a href="../vendedor/pos.php" class="menu-btn" style="border-color: var(--gold); background: rgba(212,175,55,0.05);">
                <span class="menu-icon">🛒</span>
                <span class="menu-text" style="color: var(--gold);">IR A CAJA</span>
            </a>
        </div>

    </div>

</body>
</html>