<?php
// admin/index.php - VERSIÓN FINAL CON COBRANZAS
include_once '../config/db.php';

$vista = $_GET['ver'] ?? 'inicio';

// Datos Dashboard
$total_inquilinos = 0; $total_productos = 0; $total_parroquias = 0; $total_deuda = 0;

if ($vista == 'inicio') {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM inquilinos WHERE activo = 1");
        $total_inquilinos = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock_actual > 0");
        $total_productos = $stmt->fetchColumn();
        
        try { $total_parroquias = $pdo->query("SELECT COUNT(*) FROM parroquias")->fetchColumn(); } catch(Exception $e){}
        
        // Calcular Deuda Total (Suma de montos pendientes)
        try { 
            $stmt = $pdo->query("SELECT SUM(monto) FROM movimientos_caja WHERE estado_pago = 'Pendiente'");
            $total_deuda = $stmt->fetchColumn() ?: 0;
        } catch(Exception $e){}
        
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Diócesis</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;800&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <style>
        :root { --color-vino: #5e1119; --color-oro: #c5a059; --color-papel: #fdfbf7; --color-texto: #1a1a1a; }
        body { background-color: var(--color-papel); font-family: 'Lora', serif; color: var(--color-texto); min-height: 100vh; }

        /* SIDEBAR */
        .sidebar { background: linear-gradient(180deg, #2b2b2b 0%, #1a1a1a 100%); min-height: 100vh; border-right: 4px solid var(--color-oro); color: #fff; }
        .sidebar-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid rgba(197, 160, 89, 0.2); }
        .escudo { font-size: 3rem; color: var(--color-oro); display: block; margin-bottom: 10px; }
        .sidebar-title { font-family: 'Cinzel', serif; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px;}
        
        .nav-link { font-family: 'Cinzel', serif; color: rgba(255,255,255,0.7); padding: 15px 25px; border-left: 4px solid transparent; transition: all 0.3s; }
        .nav-link:hover { background-color: rgba(197, 160, 89, 0.1); color: var(--color-oro); border-left-color: var(--color-oro); }
        .nav-link.active { background-color: var(--color-vino); color: #fff; border-left-color: var(--color-oro); }
        .nav-link i { width: 25px; margin-right: 10px; text-align: center; }

        /* CONTENIDO */
        .main-content { padding: 40px; }
        .page-header { border-bottom: 2px solid #e0d0b0; padding-bottom: 15px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .page-title { font-family: 'Cinzel', serif; color: var(--color-vino); font-weight: 800; font-size: 1.8rem; margin: 0; }
        .page-subtitle { color: var(--color-oro); font-family: 'Cinzel', serif; font-weight: 600; text-transform: uppercase; font-size: 0.9rem; }

        /* COMPONENTES */
        .card-custom { background: #fff; border: 1px solid #e0d0b0; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-radius: 4px; }
        .card-header-custom { background: var(--color-vino); color: #fff; padding: 15px; font-family: 'Cinzel', serif; font-size: 1rem; border-bottom: 3px solid var(--color-oro); }
        .form-label { font-family: 'Cinzel', serif; color: var(--color-vino); font-weight: 700; font-size: 0.85rem; text-transform: uppercase; }
        .form-control, .form-select { border-radius: 2px; border: 1px solid #ccc; padding: 10px; }
        .form-control:focus, .form-select:focus { border-color: var(--color-vino); box-shadow: 0 0 0 0.2rem rgba(94, 17, 25, 0.1); }
        .btn-vino { background-color: var(--color-vino); color: #fff; font-family: 'Cinzel', serif; border: none; padding: 10px 20px; transition: 0.3s; }
        .btn-vino:hover { background-color: #3b090e; color: #d4af37; }
        .table-custom thead { background-color: #f0ebe0; color: var(--color-vino); font-family: 'Cinzel', serif; }
        .table-custom th { border-bottom: 2px solid var(--color-oro) !important; font-weight: 700; }
        
        /* WIDGETS */
        .stat-card { background: #fff; border: 1px solid #e0d0b0; padding: 25px; display: block; text-decoration: none; position: relative; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); border-color: var(--color-oro); box-shadow: 0 10px 20px rgba(94, 17, 25, 0.1); }
        .stat-number { font-family: 'Cinzel', serif; font-size: 2.5rem; color: var(--color-vino); font-weight: 700; display: block; }
        .stat-label { color: #666; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar d-none d-md-block">
            <div class="sidebar-header">
                <span class="escudo">✝</span>
                <div class="sidebar-title">Administración Diocesana</div>
            </div>
            <nav class="nav flex-column mt-4">
                <a href="index.php?ver=inicio" class="nav-link <?php echo $vista=='inicio'?'active':''; ?>"><i class="fas fa-home"></i> Inicio</a>
                <a href="index.php?ver=cobranzas" class="nav-link <?php echo $vista=='cobranzas'?'active':''; ?>"><i class="fas fa-hand-holding-usd"></i> Cobranzas</a>
                <a href="index.php?ver=inquilinos" class="nav-link <?php echo $vista=='inquilinos'?'active':''; ?>"><i class="fas fa-users"></i> Inquilinos</a>
                <a href="index.php?ver=parroquias" class="nav-link <?php echo $vista=='parroquias'?'active':''; ?>"><i class="fas fa-church"></i> Parroquias</a>
                <a href="index.php?ver=inventario" class="nav-link <?php echo $vista=='inventario'?'active':''; ?>"><i class="fas fa-boxes"></i> Inventario</a>
                <a href="index.php?ver=reportes" class="nav-link <?php echo $vista=='reportes'?'active':''; ?>"><i class="fas fa-chart-line"></i> Reportes</a>
                <a href="../index.php" class="nav-link mt-5"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </nav>
        </div>

        <div class="col-md-9 col-lg-10 main-content">
            <?php if ($vista == 'inicio'): ?>
                <div class="page-header">
                    <div><h1 class="page-title">Panel de Control</h1><span class="page-subtitle"><?php echo date('d \d\e F \d\e Y'); ?></span></div>
                </div>
                <div class="row g-4">
                    <div class="col-md-3">
                        <a href="index.php?ver=cobranzas" class="stat-card" style="border-left: 5px solid #d9534f;">
                            <span class="stat-number text-danger">S/. <?php echo number_format($total_deuda, 2); ?></span>
                            <span class="stat-label text-danger">Por Cobrar</span>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="index.php?ver=parroquias" class="stat-card">
                            <span class="stat-number"><?php echo $total_parroquias; ?></span><span class="stat-label">Parroquias</span>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="index.php?ver=inquilinos" class="stat-card">
                            <span class="stat-number"><?php echo $total_inquilinos; ?></span><span class="stat-label">Inquilinos</span>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="index.php?ver=inventario" class="stat-card">
                            <span class="stat-number"><?php echo $total_productos; ?></span><span class="stat-label">Stock Items</span>
                        </a>
                    </div>
                </div>

            <?php elseif ($vista == 'cobranzas'): ?>
                <div class="page-header">
                    <div><h1 class="page-title">Cuentas por Cobrar</h1><span class="page-subtitle">Gestión de Deudas y Créditos</span></div>
                </div>
                <?php include 'cobranzas.php'; ?>

            <?php elseif ($vista == 'inquilinos'): ?>
                <div class="page-header">
                    <div><h1 class="page-title">Inquilinos</h1><span class="page-subtitle">Gestión de Propiedades</span></div>
                </div>
                <?php include 'inquilinos.php'; ?>

            <?php elseif ($vista == 'parroquias'): ?>
                <div class="page-header">
                    <div><h1 class="page-title">Parroquias</h1><span class="page-subtitle">Directorio Diocesano</span></div>
                </div>
                <?php include 'parroquias.php'; ?>

            <?php elseif ($vista == 'inventario'): ?>
                <div class="page-header">
                    <div><h1 class="page-title">Inventario</h1><span class="page-subtitle">Libros e Insumos</span></div>
                </div>
                <?php include 'inventario.php'; ?>

            <?php elseif ($vista == 'reportes'): ?>
                <div class="page-header">
                    <div><h1 class="page-title">Reportes</h1><span class="page-subtitle">Historial y Estados de Cuenta</span></div>
                </div>
                <?php include 'reportes.php'; ?>

            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>