<?php 
// include '../config/db.php'; 
// include '../includes/header.php'; 

// --- DATOS DE PRUEBA (INTACTOS DE TU CÓDIGO) ---
$parroquias = [['id'=>1, 'nombre'=>'San Pedro'], ['id'=>2, 'nombre'=>'Catedral']];
$insumos = [['id'=>1, 'nombre'=>'Vino Misa', 'stock_actual'=>50], ['id'=>2, 'nombre'=>'Hostias Paq. x500', 'stock_actual'=>20]];
$libros = [['id'=>1, 'nombre'=>'Misal Romano', 'stock_actual'=>5], ['id'=>2, 'nombre'=>'Leccionario I', 'stock_actual'=>8]];
$inquilinos = [['id'=>1, 'nombre'=>'Juan Pérez'], ['id'=>2, 'nombre'=>'Asoc. Cultural']];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Ingreso - Obispado de Huacho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* --- 1. ESTILO GENERAL LIMPIO --- */
        :root {
            --color-azul: #2c3e50;
            --color-dorado: #d4af37;
            --color-fondo: #f4f6f9;
        }

        body {
            background-color: var(--color-fondo);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        /* --- 2. CABEZAL INSTITUCIONAL (Tu diseño favorito) --- */
        header {
            background-color: var(--color-azul);
            color: #ffffff;
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 4px solid var(--color-dorado);
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }

        header h1 {
            font-size: 1.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
            font-weight: 600;
        }

        header p {
            margin: 5px 0 0 0;
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 300;
        }

        /* --- 3. GRID DE TARJETAS (MODERNO) --- */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card-menu {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 25px 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        /* Efecto Hover */
        .card-menu:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.1);
            border-bottom: 3px solid var(--color-dorado);
        }

        /* Estado Activo (Cuando se selecciona) */
        .card-menu.active {
            background-color: #eaf2f8;
            border: 2px solid var(--color-azul);
        }

        .card-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .card-menu h3 {
            font-size: 1.1rem;
            color: var(--color-azul);
            font-weight: 600;
            margin: 0;
        }

        /* Tarjeta especial ancha para Colectas (Opcional, para destacar) */
        .card-wide {
            grid-column: span 2; /* Ocupa 2 espacios si hay sitio */
            background: linear-gradient(to right, #fff, #fbfbfb);
        }
        @media (max-width: 768px) { .card-wide { grid-column: span 1; } }


        /* --- 4. FORMULARIO DESPLEGABLE --- */
        #cardFormulario {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.08);
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-header {
            background-color: var(--color-azul);
            color: white;
            padding: 15px 25px;
            border-bottom: 3px solid var(--color-dorado);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-label-custom {
            font-weight: 600;
            color: var(--color-azul);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .form-control, .form-select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ced4da;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--color-dorado);
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
        }

        .btn-guardar {
            background-color: var(--color-azul);
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .btn-guardar:hover {
            background-color: #1a252f;
        }

    </style>
</head>
<body>

    <header>
        <h1>OBISPADO DE HUACHO</h1>
        <p>Sistema de Gestión de Ingresos</p>
    </header>

    <div class="container pb-5">
        
        <h4 class="text-center mb-4 text-secondary">Seleccione el Tipo de Ingreso</h4>
        
        <div class="menu-grid">
            
            <div class="card-menu" onclick="seleccionarOpcion(1, this)">
                <span class="card-icon">🏢</span>
                <h3>Alquileres</h3>
            </div>

            <div class="card-menu" onclick="seleccionarOpcion(2, this)">
                <span class="card-icon">🍷</span>
                <h3>Venta Insumos</h3>
            </div>

            <div class="card-menu" onclick="seleccionarOpcion(3, this)">
                <span class="card-icon">📖</span>
                <h3>Libros Litúrgicos</h3>
            </div>

            <div class="card-menu" onclick="seleccionarOpcion(4, this)">
                <span class="card-icon">📜</span>
                <h3>Archivo Diocesano</h3>
            </div>

            <div class="card-menu" onclick="seleccionarOpcion(5, this)">
                <span class="card-icon">⚖️</span>
                <h3>Trámites Curia</h3>
            </div>

            <div class="card-menu" onclick="seleccionarOpcion(6, this)">
                <span class="card-icon">⛪</span>
                <h3>Aporte Parroquia</h3>
            </div>

            <div class="card-menu card-wide" onclick="seleccionarOpcion(7, this)">
                <span class="card-icon">🤲</span>
                <h3>Colectas Generales</h3>
                <small class="text-muted">Ordinarias, Misiones y Donaciones</small>
            </div>

        </div>

        <div class="card d-none mb-5" id="cardFormulario">
            
            <div class="form-header">
                <h4 class="mb-0 fs-5" id="tituloFormulario">Detalle de la Operación</h4>
                <small class="text-white-50">Complete los campos requeridos</small>
            </div>
            
            <div class="card-body p-4 bg-white">
                <form action="guardar_ingreso.php" method="POST">
                    
                    <input type="hidden" name="tipo_ingreso" id="inputTipoIngreso" required>

                    <div class="mb-4 d-none bloque-dinamico" id="bloqueAlquiler">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label-custom">Inquilino</label>
                                <select name="id_inquilino" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach($inquilinos as $i): ?>
                                        <option value="<?php echo $i['id']; ?>"><?php echo $i['nombre']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Periodo (Mes/Año)</label>
                                <input type="text" name="periodo_alquiler" class="form-control" placeholder="Ej: Enero 2026">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 d-none bloque-dinamico" id="bloqueInsumos">
                        <div class="row g-3">
                            <div class="col-md-9">
                                <label class="form-label-custom">Producto (Insumo)</label>
                                <select name="id_insumo" class="form-select">
                                    <option value="">-- Seleccionar Insumo --</option>
                                    <?php foreach($insumos as $ins): ?>
                                        <option value="<?php echo $ins['id']; ?>">
                                            <?php echo $ins['nombre'] . " (Stock: " . $ins['stock_actual'] . ")"; ?>
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
                        <div class="row g-3">
                            <div class="col-md-9">
                                <label class="form-label-custom">Libro Litúrgico</label>
                                <select name="id_libro" class="form-select">
                                    <option value="">-- Seleccionar Libro --</option>
                                    <?php foreach($libros as $lib): ?>
                                        <option value="<?php echo $lib['id']; ?>">
                                            <?php echo $lib['nombre'] . " (Stock: " . $lib['stock_actual'] . ")"; ?>
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

                    <div class="mb-4 d-none bloque-dinamico" id="bloqueTramite">
                        <label class="form-label-custom">Solicitante / Institución</label>
                        <input type="text" name="solicitante" class="form-control" placeholder="Nombre de quien realiza el trámite">
                    </div>

                    <div class="mb-4 d-none bloque-dinamico" id="bloqueParroquia">
                        <label class="form-label-custom">Parroquia Aportante</label>
                        <select name="id_parroquia" class="form-select">
                            <option value="">-- Seleccionar Parroquia --</option>
                            <?php foreach($parroquias as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo $p['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4 d-none bloque-dinamico" id="bloqueColecta">
                        <label class="form-label-custom">Tipo de Colecta</label>
                        <select name="subtipo_colecta" class="form-select">
                            <option value="Ordinaria">Ordinaria (Misa / Cepillo)</option>
                            <option value="Imperada">Imperada (Diócesis / Cáritas)</option>
                            <option value="Misiones">Misiones (Domund / OMP)</option>
                            <option value="Otras">Otras Donaciones</option>
                        </select>
                    </div>

                    <hr class="text-muted">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label-custom">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Monto (S/.)</label>
                            <div class="input-group">
                                <span class="input-group-text">S/.</span>
                                <input type="number" step="0.01" name="monto" class="form-control fw-bold text-end" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Observación / Concepto</label>
                            <textarea name="concepto" class="form-control" rows="2" placeholder="Detalles..."></textarea>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-guardar">
                            <i class="fas fa-save me-2"></i> Guardar Operación
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // TU LÓGICA DE JAVASCRIPT INTACTA
    function seleccionarOpcion(tipo, elementoCard) {
        
        // 1. Efecto visual de selección (marcar el borde azul)
        document.querySelectorAll('.card-menu').forEach(c => c.classList.remove('active'));
        elementoCard.classList.add('active');

        // 2. Mostrar formulario con animación
        const formulario = document.getElementById('cardFormulario');
        formulario.classList.remove('d-none');
        
        // Scroll suave
        setTimeout(() => {
            formulario.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);

        // 3. Asignar valor al hidden
        document.getElementById('inputTipoIngreso').value = tipo;
        
        // 4. Ocultar todos los bloques dinámicos primero
        document.querySelectorAll('.bloque-dinamico').forEach(b => b.classList.add('d-none'));

        // 5. Títulos dinámicos
        let titulos = {
            1: "Cobro de Alquileres",
            2: "Venta de Insumos",
            3: "Venta de Libros",
            4: "Archivo Diocesano",
            5: "Trámites Curia",
            6: "Aportes Parroquiales",
            7: "Registro de Colectas"
        };
        document.getElementById('tituloFormulario').innerText = titulos[tipo];

        // 6. Mostrar el bloque específico
        if (tipo == 1) document.getElementById('bloqueAlquiler').classList.remove('d-none');
        else if (tipo == 2) document.getElementById('bloqueInsumos').classList.remove('d-none');
        else if (tipo == 3) document.getElementById('bloqueLibros').classList.remove('d-none');
        else if (tipo == 4 || tipo == 5) document.getElementById('bloqueTramite').classList.remove('d-none');
        else if (tipo == 6) document.getElementById('bloqueParroquia').classList.remove('d-none');
        else if (tipo == 7) document.getElementById('bloqueColecta').classList.remove('d-none');
    }
    </script>

</body>
</html>