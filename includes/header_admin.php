<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Royal Licorería - Admin</title>
    
    <link rel="stylesheet" href="/assets/css/estilos.css?v=<?php echo time(); ?>">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="mobile-nav-toggle">
    <div class="mobile-logo">
        ROYAL <i class="fa-solid fa-wine-bottle" style="color:var(--royal-gold); margin-left: 5px;"></i>
    </div>
    <button class="hamburger" onclick="toggleMenu()">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>

<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<nav class="sidebar" id="sidebar">
    <div class="brand">
        <h2>ROYAL <i class="fa-solid fa-wine-bottle"></i></h2>
    </div>
    
    <div class="menu">
        <a href="/modulos/admin/dashboard.php">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
        </a>
        
        <a href="/modulos/admin/productos_nuevo.php">
            <i class="fa-solid fa-plus-circle"></i> Nuevo Producto
        </a>
        
        <a href="/modulos/admin/productos_lista.php">
            <i class="fa-solid fa-boxes-stacked"></i> Inventario Global
        </a>
        
        <a href="/modulos/admin/crear_combo.php">
            <i class="fa-solid fa-gift"></i> Crear Pack
        </a>

        <a href="/modulos/admin/transferencias.php">
            <i class="fa-solid fa-truck-moving"></i> Transferencias
        </a>

        <a href="/modulos/admin/sedes.php">
            <i class="fa-solid fa-store"></i> Gestionar Sedes
        </a>

        <a href="/modulos/admin/kardex.php">
            <i class="fa-solid fa-list-check"></i> Kardex
        </a>
        
        <br>
        
        <a href="/logout.php" style="color: #ef5350; border: 1px solid #ef5350;">
            <i class="fa-solid fa-power-off"></i> Cerrar Sesión
        </a>
    </div>
</nav>

<main class="main-content">

<script>
    function toggleMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
</script>