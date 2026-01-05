<?php 
// admin/inventario.php - VERSIÓN CON REPARACIÓN DE TIPOS Y ELIMINACIÓN
$mensaje = "";
$tipo_mensaje = "";

// 1. REPARACIÓN AGRESIVA DE BASE DE DATOS
try {
    // A) Aseguramos que la tabla exista
    $pdo->exec("CREATE TABLE IF NOT EXISTS productos (id INT AUTO_INCREMENT PRIMARY KEY)");
    
    // B) Forzamos que la columna 'categoria' sea INT (Entero). 
    // Si estaba como texto o mal definida, esto lo arregla.
    $pdo->exec("ALTER TABLE productos MODIFY COLUMN categoria INT NOT NULL DEFAULT 2");
    
    // C) Agregamos columnas faltantes si no existen
    $pdo->exec("ALTER TABLE productos ADD COLUMN nombre VARCHAR(150) NOT NULL");
    $pdo->exec("ALTER TABLE productos ADD COLUMN precio DECIMAL(10,2) DEFAULT 0.00");
    $pdo->exec("ALTER TABLE productos ADD COLUMN stock_actual INT DEFAULT 0");
    $pdo->exec("ALTER TABLE productos ADD COLUMN fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP");

    // D) CORRECCIÓN DE DATOS: Convertimos los '0' (Errores) en '3' (Libros Litúrgicos)
    // Asumimos que los que fallaron eran libros nuevos.
    $pdo->exec("UPDATE productos SET categoria = 3 WHERE categoria = 0");
    
} catch (Exception $e) {
    // Si falla algo técnico, lo ignoramos para no bloquear la pantalla,
    // pero la corrección principal debería funcionar.
}

// 2. LÓGICA: GUARDAR O ELIMINAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? 'guardar';

    // ELIMINAR PRODUCTO
    if ($accion == 'eliminar') {
        $id_borrar = $_POST['id'] ?? 0;
        if ($id_borrar) {
            $pdo->prepare("DELETE FROM productos WHERE id = ?")->execute([$id_borrar]);
            echo "<script>window.location.href='index.php?ver=inventario&status=deleted';</script>";
            exit;
        }
    }
    // GUARDAR PRODUCTO
    elseif ($accion == 'guardar') {
        $nombre    = $_POST['nombre'] ?? '';
        $categoria = $_POST['categoria'] ?? 2;
        $precio    = $_POST['precio'] ?? 0;
        $stock     = $_POST['stock'] ?? 0;

        if ($nombre) {
            try {
                $sql = "INSERT INTO productos (nombre, categoria, precio, stock_actual) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $categoria, $precio, $stock]);
                echo "<script>window.location.href='index.php?ver=inventario&status=success';</script>";
                exit;
            } catch (PDOException $e) {
                $mensaje = "Error: " . $e->getMessage();
                $tipo_mensaje = "danger";
            }
        }
    }
}

// 3. MENSAJES
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') { $mensaje = "¡Producto guardado!"; $tipo_mensaje = "success"; }
    if ($_GET['status'] == 'deleted') { $mensaje = "Producto eliminado."; $tipo_mensaje = "warning"; }
}

// 4. LISTAR
$lista_productos = [];
try {
    $lista_productos = $pdo->query("SELECT * FROM productos ORDER BY categoria ASC, nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>

<?php if($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
        <?php if($tipo_mensaje=='success') echo '<i class="fas fa-check-circle me-2"></i>'; ?>
        <?php if($tipo_mensaje=='warning') echo '<i class="fas fa-trash-alt me-2"></i>'; ?>
        <?php echo $mensaje; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    
    <div class="col-md-4 mb-4">
        <div class="card card-custom h-100">
            <div class="card-header-custom text-center">
                <i class="fas fa-plus-circle me-2"></i> Nuevo Item
            </div>
            <div class="card-body p-4">
                <form action="index.php?ver=inventario" method="POST" autocomplete="off">
                    <input type="hidden" name="accion" value="guardar">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre del Producto</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Vino de Misa, Misal..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" class="form-select">
                            <option value="2">Insumos (Velas, Formas, Vino)</option>
                            <option value="3">Libros Litúrgicos</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Precio (S/.)</label>
                            <input type="number" step="0.01" name="precio" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock Inicial</label>
                            <input type="number" name="stock" class="form-control" value="0" min="0">
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-vino">
                            <i class="fas fa-save me-2"></i> Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-custom h-100">
            <div class="card-header-custom">
                <i class="fas fa-boxes me-2"></i> Stock Actual
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Producto</th>
                                <th>Categoría</th>
                                <th class="text-center">Stock</th>
                                <th class="text-end">Precio</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($lista_productos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-box-open fa-2x mb-3 text-secondary"></i><br>
                                        El inventario está vacío.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($lista_productos as $prod): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        <?php echo htmlspecialchars($prod['nombre']); ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $cat = (int)$prod['categoria'];
                                            if($cat === 2) echo '<span class="badge bg-light text-dark border">Insumo</span>';
                                            elseif($cat === 3) echo '<span class="badge bg-warning text-dark border">Libro</span>';
                                            else echo '<span class="badge bg-secondary">Otro (' . $cat . ')</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($prod['stock_actual'] <= 5): ?>
                                            <span class="badge bg-danger"><?php echo $prod['stock_actual']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo $prod['stock_actual']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        S/. <?php echo number_format($prod['precio'], 2); ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <form action="index.php?ver=inventario" method="POST" onsubmit="return confirm('¿Borrar este producto?');" style="display:inline;">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>