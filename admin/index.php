<?php
// admin/index.php
include '../config/db.php';

// --- PEQUEÑO RESUMEN PARA EL DASHBOARD (Opcional) ---
// Contamos cuántos inquilinos activos hay
$total_inquilinos = 0;
$total_productos = 0;

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM inquilinos WHERE activo = 1");
    $total_inquilinos = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock_actual > 0");
    $total_productos = $stmt->fetchColumn();
} catch (Exception $e) {
    // Si falla, no pasa nada, mostramos 0
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
        /* --- ESTÉTICA GENERAL --- */
        :root {
            --color-vino: #5e1119;
            --color-oro: #c5a059;
            --color-papel: #fdfbf7;
            --color-texto: #1a1a1a;
        }

        body {
            background-color: var(--color-papel);
            font-family: 'Lora', serif;
            color: var(--color-texto);
            min-height: 100vh;
        }

        /* --- BARRA LATERAL (SIDEBAR) ESTILO COLUMNA --- */
        .sidebar {
            background: linear-gradient(180deg, #2b2b2b 0%, #1a1a1a 100%);
            min-height: 100vh;
            border-right: 4px solid var(--color-oro);
            color: #fff;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(197, 160, 89, 0.2);
        }

        .escudo {
            font-size: 3rem;
            color: var(--color-oro);
            margin-bottom: 10px;
            display: block;
        }

        .sidebar-title {
            font-family: 'Cinzel', serif;
            font-size: 1.1rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #fff;
        }

        .nav-link {
            font-family: 'Cinzel', serif;
            color: rgba(255,255,255,0.7);
            padding: 15px 25px;
            font-size: 0.9rem;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }

        .nav-link:hover {
            background-color: rgba(197, 160, 89, 0.1);
            color: var(--color-oro);
            border-left-color: var(--color-oro);
        }

        .nav-link.active {
            background-color: var(--color-vino);
            color: #fff;
            border-left-color: var(--color-oro);
        }

        .nav-link i { width: 25px; text-align: center; margin-right: 10px; }

        /* --- CONTENIDO PRINCIPAL --- */
        .main-content { padding: 40px; }

        .page-header {
            border-bottom: 2px solid #e0d0b0;
            padding-bottom: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-family: 'Cinzel', serif;
            color: var(--color-vino);
            font-weight: 800;
            font-size: 1.8rem;
            margin: 0;
        }

        /* --- TARJETAS DE ACCESO RÁPIDO (WIDGETS) --- */
        .stat-card {
            background: #fff;
            border: 1px solid #e0d0b0;
            padding: 25px;
            border-radius: 4px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            height: 100%;
            text-decoration: none;
            display: block;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(94, 17, 25, 0.1);
            border-color: var(--color-oro);
        }

        .stat-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 3rem;
            color: var(--color-oro);
            opacity: 0.2;
        }

        .stat-number {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            color: var(--color-vino);
            font-weight: 700;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
            display: block;
        }

        .action-link {
            font-size: 0.85rem;
            color: var(--color-vino);
            margin-top: 15px;
            display: inline-block;
            font-weight: 600;
        }

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
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <a href="inquilinos.php" class="nav-link">
                    <i class="fas fa-users"></i> Inquilinos
                </a>
                <a href="inventario.php" class="nav-link">
                    <i class="fas fa-boxes"></i> Inventario
                </a>
                <a href="#" class="nav-link text-muted">
                    <i class="fas fa-chart-line"></i> Reportes (Pronto)
                </a>
                <a href="../index.php" class="nav-link mt-5" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <i class="fas fa-sign-out-alt"></i> Salir al Menú
                </a>
            </nav>
        </div>

        <div class="col-md-9 col-lg-10 main-content">
            
            <div class="page-header">
                <h1 class="page-title">Panel de Control</h1>
                <span class="text-muted fst-italic">
                    <?php echo date('d \d\e F \d\e Y'); ?>
                </span>
            </div>

            <div class="row g-4 mb-5">
                
                <div class="col-md-4">
                    <a href="inquilinos.php" class="stat-card">
                        <i class="fas fa-building stat-icon"></i>
                        <span class="stat-number"><?php echo $total_inquilinos; ?></span>
                        <span class="stat-label">Inquilinos Activos</span>
                        <span class="action-link">Gestionar Contratos &rarr;</span>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="inventario.php" class="stat-card">
                        <i class="fas fa-wine-bottle stat-icon"></i>
                        <span class="stat-number"><?php echo $total_productos; ?></span>
                        <span class="stat-label">Items en Stock</span>
                        <span class="action-link">Ver Almacén &rarr;</span>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="#" class="stat-card" style="background-color: #f9f9f9; cursor: default;">
                        <i class="fas fa-cog stat-icon"></i>
                        <span class="stat-number">-</span>
                        <span class="stat-label">Configuración</span>
                        <span class="action-link text-muted">En desarrollo</span>
                    </a>
                </div>

            </div>

            <div class="card p-4" style="border: 1px solid #e0d0b0; border-left: 4px solid var(--color-vino);">
                <h4 style="font-family: 'Cinzel', serif; color: var(--color-vino); margin-bottom: 15px;">
                    <i class="fas fa-info-circle me-2"></i> Bienvenido, Administrador
                </h4>
                <p class="text-secondary mb-0">
                    Desde este panel puede gestionar los bienes de la diócesis. 
                    Seleccione <b>"Inquilinos"</b> en el menú lateral para registrar nuevas empresas que alquilan propiedades, 
                    o <b>"Inventario"</b> para dar de alta nuevos libros litúrgicos o insumos.
                </p>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>