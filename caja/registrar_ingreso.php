<?php 
// caja/registrar_ingreso.php - CON SUBIDA DE ARCHIVOS
include '../config/db.php'; 

// Carga de datos
$parroquias = []; $insumos = []; $libros = []; $inquilinos = [];
try { $parroquias = $pdo->query("SELECT id, nombre FROM parroquias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
try { $insumos = $pdo->query("SELECT id, nombre, stock_actual, precio FROM productos WHERE categoria = 2 AND stock_actual > 0 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
try { $libros = $pdo->query("SELECT id, nombre, stock_actual, precio FROM productos WHERE categoria = 3 AND stock_actual > 0 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
try { $inquilinos = $pdo->query("SELECT id, nombre, monto_alquiler FROM inquilinos WHERE activo = 1 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja - Diócesis de Huacho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;800&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <style>
        :root { --color-vino: #5e1119; --color-oro: #c5a059; --color-negro: #1a1a1a; --color-papel: #fdfbf7; }
        body { background-color: var(--color-papel); font-family: 'Lora', serif; background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c5a059' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
        header { background: linear-gradient(to bottom, #2b2b2b, #1a1a1a); border-bottom: 5px solid var(--color-oro); padding: 2rem 0; text-align: center; margin-bottom: 30px; }
        .header-title { font-family: 'Cinzel', serif; color: #fff; font-size: 2rem; font-weight: 800; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; max-width: 1100px; margin: 0 auto 40px auto; padding: 0 20px; }
        .card-menu { background: #fff; border: 1px solid #e0d0b0; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s; }
        .card-menu:hover, .card-menu.active { border-color: var(--color-vino); background: #fffbf0; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(94,17,25,0.1); }
        .card-icon { font-size: 2rem; color: var(--color-vino); margin-bottom: 10px; display: block; }
        .card-menu h3 { font-family: 'Cinzel', serif; font-size: 0.9rem; margin: 0; font-weight: 700; text-transform: uppercase; }
        
        #cardFormulario { max-width: 800px; margin: 0 auto 50px auto; border-top: 5px solid var(--color-vino); background: #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .form-label-custom { font-family: 'Cinzel', serif; font-size: 0.8rem; font-weight: 700; color: #555; text-transform: uppercase; margin-bottom: 5px; display: block; }
        .btn-guardar { background: var(--color-vino); color: var(--color-oro); font-family: 'Cinzel', serif; padding: 15px; width: 100%; border: none; font-weight: 700; }
        .btn-check:checked + .btn-outline-vino { background-color: var(--color-vino); color: #fff; border-color: var(--color-vino); }
        .btn-outline-vino { color: var(--color-vino); border-color: var(--color-vino); }
    </style>
</head>
<body>

    <header>
        <span style="font-size: 2rem; color: var(--color-oro);">✝</span>
        <h1 class="header-title">Tesorería Diocesana</h1>
    </header>

    <div class="container pb-5">
        
        <div class="menu-grid">
            <div class="card-menu" onclick="seleccionarOpcion(1, this)"><i class="fas fa-building card-icon"></i><h3>Alquileres</h3></div>
            <div class="card-menu" onclick="seleccionarOpcion(2, this)"><i class="fas fa-wine-glass-alt card-icon"></i><h3>Insumos</h3></div>
            <div class="card-menu" onclick="seleccionarOpcion(3, this)"><i class="fas fa-bible card-icon"></i><h3>Libros</h3></div>
            <div class="card-menu" onclick="seleccionarOpcion(4, this)"><i class="fas fa-scroll card-icon"></i><h3>Archivo</h3></div>
            <div class="card-menu" onclick="seleccionarOpcion(5, this)"><i class="fas fa-stamp card-icon"></i><h3>Trámites</h3></div>
            <div class="card-menu" onclick="seleccionarOpcion(6, this)"><i class="fas fa-church card-icon"></i><h3>Aporte Parroquia</h3></div>
            <div class="card-menu" onclick="seleccionarOpcion(7, this)"><i class="fas fa-hand-holding-heart card-icon"></i><h3>Colectas</h3></div>
            <a href="cobranzas.php" class="card-menu" style="border-color: #d9534f;"><i class="fas fa-money-bill-wave card-icon text-danger"></i><h3 class="text-danger">Cobrar Deudas</h3></a>
            <a href="../index.php" class="card-menu" style="text-decoration:none;"><i class="fas fa-sign-out-alt card-icon" style="color: #666;"></i><h3 style="color:#666;">Salir</h3></a>
        </div>

        <div class="card d-none p-4" id="cardFormulario">
            <h4 class="text-center mb-4" style="font-family: 'Cinzel', serif; color: var(--color-vino);" id="tituloFormulario">Registro</h4>
            
            <form action="guardar_ingreso.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tipo_ingreso" id="inputTipoIngreso">
                
                <div class="mb-3 d-none bloque-dinamico" id="bloqueAlquiler">
                    <label class="form-label-custom">Inquilino</label>
                    <select name="id_inquilino" class="form-select" onchange="actualizarPrecio(this)"><option value="" data-precio="0">-- Seleccionar --</option><?php foreach($inquilinos as $i): ?><option value="<?php echo $i['id']; ?>" data-precio="<?php echo $i['monto_alquiler']; ?>"><?php echo htmlspecialchars($i['nombre']); ?></option><?php endforeach; ?></select>
                    <div class="mt-2"><input type="text" name="detalle_periodo" class="form-control form-control-sm" placeholder="Periodo (Ej: Enero 2026)"></div>
                </div>

                <div class="mb-3 d-none bloque-dinamico" id="bloqueInsumos">
                    <div class="row"><div class="col-9"><label class="form-label-custom">Producto</label><select name="id_producto_insumo" class="form-select" onchange="actualizarPrecio(this, 'cantidad_insumo')"><option value="" data-precio="0">-- Seleccionar --</option><?php foreach($insumos as $p): ?><option value="<?php echo $p['id']; ?>" data-precio="<?php echo $p['precio']; ?>"><?php echo htmlspecialchars($p['nombre']); ?> (S/. <?php echo $p['precio']; ?>)</option><?php endforeach; ?></select></div><div class="col-3"><label class="form-label-custom">Cant.</label><input type="number" id="cantidad_insumo" name="cantidad_insumo" class="form-control" value="1" min="1" onchange="recalcularTotal()"></div></div>
                </div>

                <div class="mb-3 d-none bloque-dinamico" id="bloqueLibros">
                    <div class="row"><div class="col-9"><label class="form-label-custom">Libro</label><select name="id_producto_libro" class="form-select" onchange="actualizarPrecio(this, 'cantidad_libro')"><option value="" data-precio="0">-- Seleccionar --</option><?php foreach($libros as $p): ?><option value="<?php echo $p['id']; ?>" data-precio="<?php echo $p['precio']; ?>"><?php echo htmlspecialchars($p['nombre']); ?> (S/. <?php echo $p['precio']; ?>)</option><?php endforeach; ?></select></div><div class="col-3"><label class="form-label-custom">Cant.</label><input type="number" id="cantidad_libro" name="cantidad_libro" class="form-control" value="1" min="1" onchange="recalcularTotal()"></div></div>
                </div>

                <div class="mb-4 d-none bloque-dinamico p-3 border rounded bg-light" id="bloqueCliente">
                    <label class="form-label-custom text-vino mb-2">Cliente</label>
                    <div class="btn-group w-100 mb-2">
                        <input type="radio" class="btn-check" name="tipo_cliente" id="btnParroquia" value="parroquia" onclick="toggleCliente('parroquia')" checked><label class="btn btn-outline-vino" for="btnParroquia">Parroquia</label>
                        <input type="radio" class="btn-check" name="tipo_cliente" id="btnOtro" value="otro" onclick="toggleCliente('otro')"><label class="btn btn-outline-vino" for="btnOtro">Particular</label>
                    </div>
                    <div id="selectParroquiaDiv"><select name="id_parroquia_venta" class="form-select"><option value="">-- Seleccionar Parroquia --</option><?php foreach($parroquias as $p): echo "<option value='".$p['id']."'>".$p['nombre']."</option>"; endforeach; ?></select></div>
                    <div id="inputClienteDiv" class="d-none"><input type="text" name="cliente_nombre" class="form-control" placeholder="Nombre del cliente"></div>
                </div>

                <div class="mb-3 d-none bloque-dinamico" id="bloqueTramite"><label class="form-label-custom">Solicitante</label><input type="text" name="solicitante" class="form-control"></div>
                <div class="mb-3 d-none bloque-dinamico" id="bloqueParroquia"><label class="form-label-custom">Parroquia</label><select name="id_parroquia" class="form-select"><option value="">-- Seleccionar --</option><?php foreach($parroquias as $p): echo "<option value='".$p['id']."'>".$p['nombre']."</option>"; endforeach; ?></select></div>
                <div class="mb-3 d-none bloque-dinamico" id="bloqueColecta"><label class="form-label-custom">Tipo</label><select name="subtipo_colecta" class="form-select"><option value="Ordinaria">Ordinaria</option><option value="Imperada">Imperada</option><option value="Misiones">Misiones</option></select></div>

                <hr>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">Método de Pago</label>
                        <select name="metodo_pago" id="selectMetodoPago" class="form-select" onchange="toggleCredito()" required>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Yape">Yape</option>
                            <option value="Plin">Plin</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Credito" style="color:#d9534f; font-weight:bold;">Crédito (Pago Parcial)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Nro. Ticket (Ref)</label>
                        <input type="text" name="ticket_manual" class="form-control" placeholder="Opcional">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom"><i class="fas fa-paperclip"></i> Adjuntar (JPG/PDF)</label>
                        <input type="file" name="archivo_recibo" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom text-center">Total Venta (S/.)</label>
                        <input type="number" step="0.01" id="montoTotal" name="monto" class="form-control text-center fs-4 fw-bold text-vino" readonly required>
                        <div class="form-check mt-1 d-flex justify-content-center">
                             <input class="form-check-input me-2" type="checkbox" id="checkManual" onchange="toggleManualPrice()">
                             <label class="form-check-label small text-muted" for="checkManual">Editar precio</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6 d-none" id="divPagoInicial">
                        <label class="form-label-custom text-center text-success">Pago Inicial (S/.)</label>
                        <input type="number" step="0.01" id="montoInicial" name="monto_amortizado" class="form-control text-center fs-4 fw-bold text-success border-success" value="0.00">
                        <small class="text-danger d-block text-center mt-1 fw-bold" id="labelDeudaRestante">Saldo: S/. 0.00</small>
                    </div>

                    <div class="col-md-12"><label class="form-label-custom">Detalle / Glosa</label><textarea name="concepto" class="form-control" rows="2"></textarea></div>
                </div>
                
                <input type="hidden" name="id_producto" id="inputProductoGeneral"><input type="hidden" name="cantidad" id="inputCantidadGeneral">
                <div class="mt-4"><button type="submit" class="btn btn-guardar"><i class="fas fa-save me-2"></i> Registrar</button></div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let precioUnitarioActual=0; let idCantidadActual=null;

        function seleccionarOpcion(tipo, card) {
            document.querySelectorAll('.card-menu').forEach(c => c.classList.remove('active')); card.classList.add('active');
            document.getElementById('cardFormulario').classList.remove('d-none'); document.getElementById('cardFormulario').scrollIntoView({ behavior: 'smooth' });
            document.getElementById('inputTipoIngreso').value = tipo;
            document.querySelectorAll('.bloque-dinamico').forEach(b => b.classList.add('d-none'));
            document.getElementById('montoTotal').value=''; precioUnitarioActual=0; idCantidadActual=null;
            document.getElementById('selectMetodoPago').value = 'Efectivo'; toggleCredito();

            if(tipo==1) document.getElementById('bloqueAlquiler').classList.remove('d-none');
            else if(tipo==2) { document.getElementById('bloqueInsumos').classList.remove('d-none'); document.getElementById('bloqueCliente').classList.remove('d-none'); }
            else if(tipo==3) { document.getElementById('bloqueLibros').classList.remove('d-none'); document.getElementById('bloqueCliente').classList.remove('d-none'); }
            else if(tipo==4||tipo==5) document.getElementById('bloqueTramite').classList.remove('d-none');
            else if(tipo==6) document.getElementById('bloqueParroquia').classList.remove('d-none');
            else if(tipo==7) document.getElementById('bloqueColecta').classList.remove('d-none');
        }

        function toggleCliente(tipo) {
            if(tipo==='parroquia') { document.getElementById('selectParroquiaDiv').classList.remove('d-none'); document.getElementById('inputClienteDiv').classList.add('d-none'); }
            else { document.getElementById('selectParroquiaDiv').classList.add('d-none'); document.getElementById('inputClienteDiv').classList.remove('d-none'); }
        }

        function toggleManualPrice() { document.getElementById('montoTotal').readOnly = !document.getElementById('checkManual').checked; }

        function actualizarPrecio(select, idCant=null) {
            precioUnitarioActual = parseFloat(select.options[select.selectedIndex].getAttribute('data-precio'))||0;
            idCantidadActual = idCant;
            recalcularTotal();
        }

        function recalcularTotal() {
            let total = precioUnitarioActual;
            if(idCantidadActual) total *= (parseInt(document.getElementById(idCantidadActual).value)||1);
            document.getElementById('montoTotal').value = total > 0 ? total.toFixed(2) : '';
            actualizarDeuda(); 
        }

        function toggleCredito() {
            const metodo = document.getElementById('selectMetodoPago').value;
            const divInicial = document.getElementById('divPagoInicial');
            if (metodo === 'Credito') { divInicial.classList.remove('d-none'); actualizarDeuda(); } 
            else { divInicial.classList.add('d-none'); }
        }

        document.getElementById('montoInicial').addEventListener('input', actualizarDeuda);
        document.getElementById('montoTotal').addEventListener('input', actualizarDeuda);

        function actualizarDeuda() {
            const total = parseFloat(document.getElementById('montoTotal').value) || 0;
            const inicial = parseFloat(document.getElementById('montoInicial').value) || 0;
            const resta = total - inicial;
            const label = document.getElementById('labelDeudaRestante');
            if(resta > 0) label.innerText = "Saldo: S/. " + resta.toFixed(2);
            else label.innerText = "Pago Completo";
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const tipo = document.getElementById('inputTipoIngreso').value;
            const metodo = document.getElementById('selectMetodoPago').value;
            if(metodo === 'Credito') {
                const total = parseFloat(document.getElementById('montoTotal').value) || 0;
                const inicial = parseFloat(document.getElementById('montoInicial').value) || 0;
                if(inicial > total) { alert("¡Error! El adelanto no puede ser mayor al total."); e.preventDefault(); return; }
            }
            if(tipo==2) { document.getElementById('inputProductoGeneral').value=document.querySelector('select[name="id_producto_insumo"]').value; document.getElementById('inputCantidadGeneral').value=document.getElementById('cantidad_insumo').value; }
            else if(tipo==3) { document.getElementById('inputProductoGeneral').value=document.querySelector('select[name="id_producto_libro"]').value; document.getElementById('inputCantidadGeneral').value=document.getElementById('cantidad_libro').value; }
        });
    </script>
</body>
</html>