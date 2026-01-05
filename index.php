<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Diocesano - Obispado de Huacho</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;900&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <style>
        /* --- IDENTIDAD VISUAL ECLESIÁSTICA --- */
        :root {
            --color-vino: #5e1119;      /* Rojo litúrgico oscuro */
            --color-oro: #c5a059;       /* Dorado antiguo */
            --color-negro: #1a1a1a;     /* Texto principal */
            --color-papel: #fdfbf7;     /* Fondo crema suave */
        }

        body {
            background-color: var(--color-papel);
            font-family: 'Lora', serif;
            color: var(--color-negro);
            /* Patrón de fondo muy sutil */
            background-image: radial-gradient(#c5a059 0.5px, transparent 0.5px), radial-gradient(#c5a059 0.5px, var(--color-papel) 0.5px);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .main-container {
            background: #ffffff;
            border: 1px solid rgba(197, 160, 89, 0.3);
            box-shadow: 0 10px 40px rgba(94, 17, 25, 0.1);
            max-width: 900px;
            width: 95%;
            padding: 3rem;
            text-align: center;
            position: relative;
            /* Marco doble estilo diploma */
            outline: 4px double var(--color-papel);
            outline-offset: -10px;
        }

        /* Borde superior decorativo */
        .main-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--color-vino), var(--color-oro), var(--color-vino));
        }

        .header-symbol {
            font-size: 3.5rem;
            color: var(--color-oro);
            margin-bottom: 1rem;
            display: block;
            text-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        h1 {
            font-family: 'Cinzel', serif;
            font-weight: 900;
            color: var(--color-vino);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        p.subtitle {
            font-family: 'Cinzel', serif;
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 3rem;
            border-bottom: 1px solid var(--color-oro);
            display: inline-block;
            padding-bottom: 10px;
        }

        /* --- TARJETAS DE ACCESO --- */
        .access-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .access-card {
            display: block;
            text-decoration: none;
            background: #fcfcfc;
            border: 1px solid #e0e0e0;
            padding: 2.5rem 1.5rem;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            group-hover: text-decoration-none;
        }

        .access-card:hover {
            transform: translateY(-5px);
            border-color: var(--color-oro);
            background: #fff;
            box-shadow: 0 15px 30px rgba(197, 160, 89, 0.15);
        }

        /* Icono dentro de la tarjeta */
        .access-icon {
            font-size: 3rem;
            color: var(--color-negro);
            margin-bottom: 1.5rem;
            transition: color 0.3s;
        }

        .access-card:hover .access-icon {
            color: var(--color-vino);
        }

        .access-title {
            font-family: 'Cinzel', serif;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--color-vino);
            margin-bottom: 0.5rem;
            display: block;
        }

        .access-desc {
            font-family: 'Lora', serif;
            font-size: 0.9rem;
            color: #777;
            font-style: italic;
        }

        /* Línea dorada al hacer hover */
        .access-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; width: 100%;
            height: 3px;
            background: var(--color-oro);
            transform: scaleX(0);
            transition: transform 0.4s ease;
            transform-origin: center;
        }

        .access-card:hover::after {
            transform: scaleX(1);
        }

        footer {
            margin-top: 3rem;
            font-size: 0.8rem;
            color: #999;
        }

    </style>
</head>
<body>

    <div class="main-container">
        
        <div class="header-symbol">✝</div>
        <h1>Diócesis de Huacho</h1>
        <p class="subtitle">Portal de Gestión Eclesiástica</p>

        <div class="access-grid">
            
            <a href="admin/index.php" class="access-card">
                <i class="fas fa-user-tie access-icon"></i>
                <span class="access-title">Administración</span>
                <span class="access-desc">Gestión de inventarios, usuarios y configuración global.</span>
            </a>

            <a href="caja/registrar_ingreso.php" class="access-card">
                <i class="fas fa-hand-holding-usd access-icon"></i>
                <span class="access-title">Tesorería y Caja</span>
                <span class="access-desc">Registro de ingresos, alquileres, colectas y ventas.</span>
            </a>

        </div>

        <footer>
            &copy; <?php echo date('Y'); ?> Obispado de Huacho. <br>
            <span style="font-family: 'Cinzel', serif; color: var(--color-oro);">Ad Maiorem Dei Gloriam</span>
        </footer>

    </div>

</body>
</html>