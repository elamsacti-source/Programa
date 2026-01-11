<?php
require '../config/db.php';
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: ../index.php"); exit; }

$mensaje = "";
$tab_activa = "list"; // Pestaña por defecto

// LÓGICA DE GUARDADO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = $_POST['codigo']; $nombre = $_POST['nombre']; $es_granel = isset($_POST['es_granel']) ? 1 : 0;
    $costo = $_POST['costo']; $precio = $_POST['precio']; $stock = $_POST['stock'];

    try {
        $pdo->prepare("INSERT INTO productos (codigo, nombre, es_granel, costo, precio, stock) VALUES (?,?,?,?,?,?)")
            ->execute([$codigo, $nombre, $es_granel, $costo, $precio, $stock]);
        
        $pid = $pdo->lastInsertId();
        $pdo->query("INSERT INTO kardex (producto_id, tipo, cantidad, stock_saldo) VALUES ($pid, 'INICIAL', $stock, $stock)");
        
        $mensaje = "✅ PRODUCTO REGISTRADO CON ÉXITO";
        $tab_activa = "form"; 
    } catch (Exception $e) {
        $mensaje = "❌ ERROR: CÓDIGO DUPLICADO O DATOS INVÁLIDOS";
        $tab_activa = "form";
    }
}

// LÓGICA DE LISTADO
$busqueda = $_GET['q'] ?? '';
$pagina = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
$por_pagina = 20; 
$inicio = ($pagina > 1) ? ($pagina * $por_pagina) - $por_pagina : 0;

$where = ""; $params = [];
if ($busqueda != '') {
    $where = "WHERE nombre LIKE ? OR codigo LIKE ?";
    $params = ["%$busqueda%", "%$busqueda%"];
}

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM productos $where");
$stmtTotal->execute($params);
$total_registros = $stmtTotal->fetchColumn();
$total_paginas = ceil($total_registros / $por_pagina);

$stmt = $pdo->prepare("SELECT * FROM productos $where ORDER BY id DESC LIMIT $inicio, $por_pagina");
$stmt->execute($params);
$lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <style>
        .tabs-nav { display: flex; border-bottom: 1px solid #333; margin-bottom: 20px; }
        .tab-btn {
            flex: 1; padding: 15px; background: #000; border: none; 
            color: #666; font-weight: 700; font-size: 0.9rem; cursor: pointer;
            border-bottom: 3px solid transparent; transition: 0.3s;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .tab-btn.active { color: var(--gold); border-bottom-color: var(--gold); background: #080808; }
        .view-section { display: none; animation: fadeIn 0.3s ease; }
        .view-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .paginador { display: flex; gap: 5px; justify-content: center; margin-top: 30px; }
        .pag-link { padding: 10px 15px; border: 1px solid #333; color: #fff; text-decoration: none; border-radius: 6px; background: #111; }
        .pag-link.active { background: var(--gold); color: #000; border-color: var(--gold); font-weight: 800; }
        
        /* Estilo para el lector de cámara */
        #reader { width: 100%; max-width: 400px; margin: 0 auto; display:none; border: 2px solid var(--gold); border-radius: 8px; margin-bottom: 15px; }
    </style>
</head>
<body>

    <div class="navbar">
        <span class="brand">GESTIÓN</span>
        <a href="dashboard.php" class="btn btn-dark" style="width:auto; padding:5px 15px; font-size:0.7rem;">VOLVER</a>
    </div>

    <div class="tabs-nav">
        <button class="tab-btn <?= $tab_activa == 'list' ? 'active' : '' ?>" onclick="switchTab('list')" id="btn-list">📋 LISTADO</button>
        <button class="tab-btn <?= $tab_activa == 'form' ? 'active' : '' ?>" onclick="switchTab('form')" id="btn-form">➕ NUEVO</button>
    </div>

    <div class="container">

        <div id="view-list" class="view-section <?= $tab_activa == 'list' ? 'active' : '' ?>">
            <form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">
                <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>" placeholder="🔍 BUSCAR PRODUCTO..." style="margin:0;">
                <button type="submit" class="btn btn-dark" style="width:auto;">IR</button>
            </form>

            <h3 style="color:var(--gold); font-size:0.9rem; margin-bottom:15px; border-left:3px solid var(--gold); padding-left:10px;">
                INVENTARIO ACTUAL (<?= $total_registros ?>)
            </h3>

            <table>
                <thead>
                    <tr>
                        <th>CÓDIGO</th>
                        <th>PRODUCTO</th>
                        <th>TIPO</th>
                        <th>PRECIO</th>
                        <th>STOCK</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($lista) > 0): ?>
                        <?php foreach($lista as $p): ?>
                        <tr>
                            <td data-label="CÓDIGO"><?= $p['codigo'] ?></td>
                            <td data-label="PRODUCTO"><?= $p['nombre'] ?></td>
                            <td data-label="TIPO"><?= $p['es_granel'] ? '<span class="mini-badge mb-gold">GRANEL</span>' : '<span class="mini-badge mb-gray">UNIDAD</span>' ?></td>
                            <td data-label="PRECIO">S/ <?= number_format($p['precio'], 2) ?></td>
                            <td data-label="STOCK"><?= number_format($p['stock'], $p['es_granel']?3:0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:#666;">NO SE ENCONTRARON RESULTADOS</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if($total_paginas > 1): ?>
            <div class="paginador">
                <?php for($i=1; $i<=$total_paginas; $i++): ?>
                    <a href="?pag=<?= $i ?>&q=<?= $busqueda ?>" class="pag-link <?= $i == $pagina ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>

        <div id="view-form" class="view-section <?= $tab_activa == 'form' ? 'active' : '' ?>">
            
            <?php if($mensaje): ?>
                <div style="text-align:center; padding:15px; border:1px solid var(--gold); border-radius:8px; margin-bottom:20px; color:var(--gold); font-weight:700;">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <h3 style="color:#fff; text-align:center; margin-bottom:20px;">REGISTRAR ÍTEM</h3>

                <div id="reader"></div>
                <div id="camera-controls" style="text-align:center; margin-bottom:15px; display:none;">
                    <button type="button" onclick="stopCamera()" class="btn btn-dark" style="background:#b91c1c; border-color:#b91c1c;">CERRAR CÁMARA</button>
                </div>
                
                <form method="POST">
                    <label style="color:#666; font-size:0.7rem; margin-bottom:5px; display:block;">CÓDIGO DE BARRAS / ID</label>
                    
                    <div style="display:flex; gap:10px; margin-bottom:15px;">
                        <input type="text" name="codigo" id="txt-codigo" placeholder="ESCANEA O ESCRIBE..." required autofocus 
                               onkeydown="if(event.key==='Enter'){event.preventDefault(); document.getElementById('txt-nombre').focus();}" 
                               style="margin-bottom:0;">
                        
                        <button type="button" onclick="startCamera()" class="btn btn-dark" style="width:auto; padding:0 20px; font-size:1.2rem;">📷</button>
                    </div>
                    
                    <label style="color:#666; font-size:0.7rem; margin-bottom:5px; display:block;">NOMBRE DEL PRODUCTO</label>
                    <input type="text" name="nombre" id="txt-nombre" placeholder="Ej: Arroz Costeño 1kg" required>
                    
                    <div style="background:#080808; padding:10px; border-radius:6px; margin-bottom:15px; display:flex; align-items:center; gap:10px; border:1px solid #333;">
                        <input type="checkbox" name="es_granel" style="width:20px; margin:0;">
                        <span style="color:#fff; font-weight:700; font-size:0.9rem;">¿ES VENTA A GRANEL (PESO)?</span>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div>
                            <label style="color:#666; font-size:0.7rem;">COSTO (S/)</label>
                            <input type="number" step="0.01" name="costo" placeholder="0.00" required>
                        </div>
                        <div>
                            <label style="color:var(--gold); font-size:0.7rem;">PRECIO VENTA (S/)</label>
                            <input type="number" step="0.01" name="precio" placeholder="0.00" style="border-color:var(--gold);" required>
                        </div>
                    </div>

                    <label style="color:#666; font-size:0.7rem; margin-bottom:5px; display:block;">STOCK INICIAL</label>
                    <input type="number" step="0.001" name="stock" placeholder="0" required>
                    
                    <button type="submit" class="btn btn-primary" style="margin-top:10px;">GUARDAR PRODUCTO</button>
                </form>
            </div>
        </div>

    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
            document.getElementById('btn-' + tab).classList.add('active');
            document.getElementById('view-' + tab).classList.add('active');
            if(tab === 'form') { setTimeout(() => document.getElementById('txt-codigo').focus(), 100); }
        }

        /* LÓGICA DE LA CÁMARA CON HTML5-QRCODE */
        let html5QrCode;

        function startCamera() {
            const reader = document.getElementById('reader');
            const controls = document.getElementById('camera-controls');
            
            reader.style.display = 'block';
            controls.style.display = 'block';

            html5QrCode = new Html5Qrcode("reader");
            
            // Configuración: Cámara trasera (environment), fps 10
            const config = { fps: 10, qrbox: { width: 250, height: 150 } };
            
            html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
            .catch(err => {
                alert("Error al iniciar cámara: " + err);
                stopCamera();
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Poner texto en el input
            document.getElementById('txt-codigo').value = decodedText;
            
            // Detener cámara
            stopCamera();
            
            // Pasar foco al nombre
            document.getElementById('txt-nombre').focus();
            
            // Opcional: Feedback sonoro
            // let audio = new Audio('../beep.mp3'); audio.play(); 
        }

        function stopCamera() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    document.getElementById('reader').style.display = 'none';
                    document.getElementById('camera-controls').style.display = 'none';
                    html5QrCode.clear();
                }).catch(err => console.log(err));
            } else {
                document.getElementById('reader').style.display = 'none';
                document.getElementById('camera-controls').style.display = 'none';
            }
        }
    </script>
</body>
</html>