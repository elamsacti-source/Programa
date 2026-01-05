<?php 
// 1. INCLUIR CONEXIÓN A BASE DE DATOS
include '../config/db.php'; 

// 2. CONSULTAS DE DATOS (Misma lógica, solo cambia el diseño visual)
$parroquias = [];
$insumos = [];
$libros = [];
$inquilinos = [];

try {
    $stmt = $pdo->query("SELECT id, nombre FROM parroquias ORDER BY nombre ASC");
    $parroquias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT id, nombre, stock_actual FROM productos WHERE categoria = 2 AND stock_actual > 0 ORDER BY nombre ASC");
    $insumos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT id, nombre, stock_actual FROM productos WHERE categoria = 3 AND stock_actual > 0 ORDER BY nombre ASC");
    $libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT id, nombre FROM inquilinos ORDER BY nombre ASC");
    $inquilinos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Manejo silencioso de errores para no romper la estética
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tesorería - Diócesis de Huacho</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;800&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <style>
        /* --- 1. ESTÉTICA ECLESIÁSTICA --- */
        :root {
            --color-vino: #5e1119;      /* Rojo litúrgico oscuro */
            --color-oro: #c5a059;       /* Dorado antiguo */
            --color-negro: #1a1a1a;     /* Texto principal */
            --color-papel: #fdfbf7;     /* Fondo crema suave (tipo papel) */
            --color-blanco: #ffffff;
        }

        body {
            background-color: var(--color-papel);
            font-family: 'Lora', serif; /* Tipografía de libro */
            color: var(--color-negro);
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c5a059' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); /* Patrón sutil de cruces */
        }

        /* --- 2. CABEZAL SOLEMNE --- */
        header {
            background: linear-gradient(to bottom, #2b2b2b, #1a1a1a);
            border-bottom: 5px solid var(--color-oro);
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            margin-bottom: 40px;
        }

        .header-title {
            font-family: 'Cinzel', serif;
            color: #fff;
            font-size: 2rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 800;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .header-subtitle {
            color: var(--color-oro);
            font-family: 'Cinzel', serif;
            font-size: 0.9rem;
            letter-spacing: 2px;
            margin-top: 10px;
            text-transform: uppercase;
            border-top: 1px solid rgba(197, 160, 89, 0.3);
            display: inline-block;
            padding-top: 10px;
        }

        .cruz-central {
            font-size: 2.5rem;
            color: var(--color-oro);
            display: block;
            margin-bottom: 10px;
        }

        /* --- 3. MENÚ DE TARJETAS (ESTILO CLÁSICO) --- */
        .section-title {
            text-align: center;
            font-family: 'Cinzel', serif;
            color: var(--color-vino);
            margin-bottom: 30px;
            font-weight: 600;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
            padding-bottom: 10px;
        }
        
        .section-title::after {
            content: "☧"; /* Símbolo Chi-Rho o línea decorativa */
            display: block;
            font-size: 1.5rem;
            color: var(--color-oro);
            margin-top: 5px;
            line-height: 1;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            max-width: 1100px;
            margin: 0 auto 50px auto;
            padding: 0 20px;
        }

        .card-menu {
            background-color: #fff;
            border: 1px solid #e0d0b0; /* Borde crema oscuro */
            border-radius: 4px; /* Bordes menos redondeados, más serios */
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
            /* Efecto de "doble borde" clásico con sombra interna */
            box-shadow: inset 0 0 0 4px var(--color-papel), 0 4px 6px rgba(0,0,0,0.05);
        }

        .card-menu:hover {
            border-color: var(--color-oro);
            transform: translateY(-5px);
            box-shadow: inset 0 0 0 2px var(--color-oro), 0 15px 30px rgba(94, 17, 25, 0.15);
        }

        .card-menu.active {
            background-color: #fffbf0;
            border-color: var(--color-vino);
            box-shadow: inset 0 0 0 2px var(--color-vino);
        }

        .card-icon {
            font-size: 2.5rem;
            color: var(--color-vino); /* Iconos color vino */
            margin-bottom: 15px;
            display: block;
            transition: color 0.3s;
        }
        
        .card-menu:hover .card-icon {
            color: var(--color-oro);
        }

        .card-menu h3 {
            font-family: 'Cinzel', serif;
            font-size: 1rem;
            color: #333;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.5px;
        }

        /* --- 4. FORMULARIO (ESTILO DOCUMENTO OFICIAL) --- */
        #cardFormulario {
            max-width: 900px;
            margin: 0 auto 50px auto;
            border: none;
            background: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            position: relative;
            border-top: 5px solid var(--color-vino); /* Tope color vino */
        }
        
        /* Borde decorativo */
        #cardFormulario::before {
            content: '';
            position: absolute;
            top: 5px; left: 5px; right: 5px; bottom: 5px;
            border: 1px solid #e0d0b0;
            pointer-events: none;
            z-index: 0;
        }

        .form-header {
            background-color: #fff;
            padding: 30px 40px 10px 40px;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .form-title-text {
            font-family: 'Cinzel', serif;
            color: var(--color-vino);
            font-size: 1.5rem;
            font-weight: 700;
            border-bottom: 2px solid var(--color-oro);
            display: inline-block;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .card-body {
            position: relative;
            z-index: 1;
            padding: 30px 50px;
        }

        /* Inputs estilizados clásicos */
        .form-label-custom {
            font-family: 'Cinzel', serif;
            font-size: 0.85rem;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        .form-control, .form-select {
            border: 1px solid #ccc;
            border-radius: 2px; /* Cuadrados */
            padding: 10px 12px;
            font-family: 'Lora', serif;
            background-color: #fcfcfc;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--color-vino);
            box-shadow: none;
            background-color: #fff;
        }

        .btn-guardar {
            background-color: var(--color-vino);
            color: var(--color-oro);
            font-family: 'Cinzel', serif;
            font-weight: 700;
            padding: 15px 40px;
            border: 1px solid var(--color-vino);
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-guardar:hover {
            background-color: #4a0d13;
            color: #fff;
            border-color: #4a0d13;
        }

        /* Pie de página */
        footer {
            text-align: center;
            padding: 30px;
            color: #888;
            font-size: 0.8rem;
            border-top: 1px solid rgba(0,0,0,0.05);
            background: #fff;
        }

    </style>
</head>
<body>

    <header>
        <span class="cruz-central">✝</span>
        <h1 class="header-title">Obispado de Huacho</h1>
        <div class="header-subtitle">Administración de Bienes Eclesiásticos</div>
    </header>

    <div class="container pb-5">
        
        <h2 class="section-title">Registro de Ingresos</h2>
        
        <div class="menu-grid">
            
            <div class="card-menu" onclick="seleccionarOpcion(1, this)">
                <i class="fas fa-building card-icon"></i>
                <h3>Alquileres</h3>
            </div>

            <div class="card-menu" onclick="seleccionarOpcion(2, this)">
                <i class="fas fa-wine-glass-alt card-icon"></i>
                <h3>Insumos Misa</h3>
            </div>

            <div class="card-menu" onclick="seleccionarOpcion(3, this)">
                <i class="fas fa-bible card-icon"></i>
                <h3>Libros Litúrgicos</h3>
            </div>

            <div class="card-menu" onclick="seleccionarOpcion(4, this)">
                <i class="fas fa-scroll card-icon"></i>
                <h3>Archivo Diocesano</h3>
            </div>

            <div class="card-menu" onclick="seleccionarOpcion(5, this)">
                <i class="fas fa-stamp card-icon"></i>
                <h3>Trámites Obispado</h3>
            </div>

            <div class="card-menu" onclick="seleccionarOpcion(6, this)">
                <i class="fas fa-church card-icon"></i>
                <h3>Aporte Parroquia</h3>
            </div>

            <div class="card-menu" style="grid-column: span 1;" onclick="seleccionarOpcion(7, this)">
                <i class="fas fa-hand-holding-heart card-icon"></i>
                <h3>Colectas</h3>
            </div>
            
            <a href="../index.php" class="card-menu" style="text-decoration:none;">
                <i class="fas fa-sign-out-alt card-icon" style="color: #666;"></i>
                <h3 style="color:#666;">Salir</h3>
            </a>

        </div>

        <div class="card d-none" id="cardFormulario">
            
            <div class="form-header">
                <div class="form-title-text" id="tituloFormulario">Detalle de la Operación</div>
            </div>
            
            <div class="card-body">
                <form action="guardar_ingreso.php" method="POST">
                    
                    <input type="hidden" name="tipo_ingreso" id="inputTipoIngreso" required>

                    <div class="mb-4 d-none bloque-dinamico" id="bloqueAlquiler">
                        <div class="row g-4">
                            <div class="col-md-8">
                                <label class="form-label-custom">Inquilino / Arrendatario</label>
                                <select name="id_inquilino" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach($inquilinos as $i): ?>
                                        <option value="<?php echo $i['id']; ?>"><?php echo htmlspecialchars($i['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Periodo</label>
                                <input type="text" name="detalle_periodo" class="form-control" placeholder="Ej: Enero 2026">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 d-none bloque-dinamico" id="bloqueInsumos">
                        <div class="row g-4">
                            <div class="col-md-9">
                                <label class="form-label-custom">Especie / Insumo</label>
                                <select name="id_producto_insumo" class="form-select" onchange="actualizarInputProducto(this)">
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach($insumos as $ins): ?>
                                        <option value="<?php echo $ins['id']; ?>">
                                            <?php echo htmlspecialchars($ins['nombre']) . " (Disp: " . $ins['stock_actual'] . ")"; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-custom">Cantidad</label>
                                <input type="number" name="cantidad_insumo" class="form-control" value="1" min="1">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 d-none bloque-dinamico" id="bloqueLibros">
                        <div class="row g-4">
                            <div class="col-md-9">
                                <label class="form-label-custom">Libro Litúrgico</label>
                                <select name="id_producto_libro" class="form-select" onchange="actualizarInputProducto(this)">
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach($libros as $lib): ?>
                                        <option value="<?php echo $lib['id']; ?>">
                                            <?php echo htmlspecialchars($lib['nombre']) . " (Disp: " . $lib['stock_actual'] . ")"; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-custom">Cantidad</label>
                                <input type="number" name="cantidad_libro" class="form-control" value="1" min="1">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="id_producto" id="inputProductoGeneral">
                    <input type="hidden" name="cantidad" id="inputCantidadGeneral">

                    <div class="mb-4 d-none bloque-dinamico" id="bloqueTramite">
                        <label class="form-label-custom">Solicitante / Institución</label>
                        <input type="text" name="solicitante" class="form-control" placeholder="Nombre completo">
                    </div>

                    <div class="mb-4 d-none bloque-dinamico" id="bloqueParroquia">
                        <label class="form-label-custom">Parroquia</label>
                        <select name="id_parroquia" class="form-select">
                            <option value="">-- Seleccionar Parroquia --</option>
                            <?php foreach($parroquias as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4 d-none bloque-dinamico" id="bloqueColecta">
                        <label class="form-label-custom">Destino / Tipo</label>
                        <select name="subtipo_colecta" class="form-select">
                            <option value="Ordinaria">Ordinaria (Misa / Cepillo)</option>
                            <option value="Imperada">Imperada (Diócesis / Cáritas)</option>
                            <option value="Misiones">Misiones (Domund / OMP)</option>
                            <option value="Otras">Otras Donaciones</option>
                        </select>
                    </div>

                    <hr style="border-top: 1px solid var(--color-oro); opacity: 0.3; margin: 30px 0;">

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label-custom">Fecha Registro</label>
                            <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Monto Total (S/.)</label>
                            <input type="number" step="0.01" name="monto" class="form-control" style="font-weight: bold; color: var(--color-vino);" placeholder="0.00" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Concepto / Glosa</label>
                            <textarea name="concepto" class="form-control" rows="2" placeholder="Describa el detalle de la operación..."></textarea>
                        </div>
                    </div>

                    <div class="mt-5">
                        <button type="submit" class="btn btn-guardar">
                            <i class="fas fa-save me-2"></i> Registrar en Libros
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>

    <footer>
        <div style="font-family: 'Cinzel', serif; color: var(--color-vino);">PAX ET BONUM</div>
        <div class="mt-2">&copy; <?php echo date('Y'); ?> Diócesis de Huacho - Tesorería</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function seleccionarOpcion(tipo, elementoCard) {
        // Estilos de selección
        document.querySelectorAll('.card-menu').forEach(c => c.classList.remove('active'));
        elementoCard.classList.add('active');

        // Mostrar formulario
        const formulario = document.getElementById('cardFormulario');
        formulario.classList.remove('d-none');
        
        setTimeout(() => {
            formulario.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);

        // Lógica de campos
        document.getElementById('inputTipoIngreso').value = tipo;
        document.querySelectorAll('.bloque-dinamico').forEach(b => b.classList.add('d-none'));

        let titulos = {
            1: "Cobro de Alquiler",
            2: "Venta de Insumos",
            3: "Venta de Libros",
            4: "Trámite de Archivo",
            5: "Trámite de Curia",
            6: "Aporte Parroquial",
            7: "Registro de Colecta"
        };
        document.getElementById('tituloFormulario').innerText = titulos[tipo] || "Operación";

        if (tipo == 1) document.getElementById('bloqueAlquiler').classList.remove('d-none');
        else if (tipo == 2) document.getElementById('bloqueInsumos').classList.remove('d-none');
        else if (tipo == 3) document.getElementById('bloqueLibros').classList.remove('d-none');
        else if (tipo == 4 || tipo == 5) document.getElementById('bloqueTramite').classList.remove('d-none');
        else if (tipo == 6) document.getElementById('bloqueParroquia').classList.remove('d-none');
        else if (tipo == 7) document.getElementById('bloqueColecta').classList.remove('d-none');
    }

    // Unificar inputs para el backend
    document.querySelector('form').addEventListener('submit', function() {
        const tipo = document.getElementById('inputTipoIngreso').value;
        const inputProd = document.getElementById('inputProductoGeneral');
        const inputCant = document.getElementById('inputCantidadGeneral');

        if (tipo == 2) {
            inputProd.value = document.querySelector('select[name="id_producto_insumo"]').value;
            inputCant.value = document.querySelector('input[name="cantidad_insumo"]').value;
        } else if (tipo == 3) {
            inputProd.value = document.querySelector('select[name="id_producto_libro"]').value;
            inputCant.value = document.querySelector('input[name="cantidad_libro"]').value;
        }
    });
    </script>

</body>
</html>