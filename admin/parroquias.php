<?php 
// admin/parroquias.php - MÓDULO DE GESTIÓN DE PARROQUIAS
// Se incluye dentro de index.php

$mensaje = "";
$tipo_mensaje = "";

// 1. AUTO-CREACIÓN DE TABLA PARROQUIAS
try {
    $sql = "CREATE TABLE IF NOT EXISTS parroquias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(150) NOT NULL,
        parroco VARCHAR(100),
        ubicacion VARCHAR(150),
        telefono VARCHAR(50),
        fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
} catch (Exception $e) {}

// 2. GUARDAR / ELIMINAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? 'guardar';

    if ($accion == 'eliminar') {
        $id = $_POST['id'] ?? 0;
        try {
            $pdo->prepare("DELETE FROM parroquias WHERE id = ?")->execute([$id]);
            echo "<script>window.location.href='index.php?ver=parroquias&status=deleted';</script>"; exit;
        } catch (Exception $e) { $mensaje = "Error al eliminar"; $tipo_mensaje = "danger"; }
    }
    elseif ($accion == 'guardar') {
        $nombre = $_POST['nombre'] ?? '';
        $parroco = $_POST['parroco'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $telefono = $_POST['telefono'] ?? '';

        if ($nombre) {
            try {
                $stmt = $pdo->prepare("INSERT INTO parroquias (nombre, parroco, ubicacion, telefono) VALUES (?,?,?,?)");
                $stmt->execute([$nombre, $parroco, $ubicacion, $telefono]);
                echo "<script>window.location.href='index.php?ver=parroquias&status=success';</script>"; exit;
            } catch (Exception $e) { $mensaje = "Error: " . $e->getMessage(); $tipo_mensaje = "danger"; }
        }
    }
}

// Mensajes
if (isset($_GET['status'])) {
    if($_GET['status']=='success'){$mensaje="¡Parroquia registrada!"; $tipo_mensaje="success";}
    if($_GET['status']=='deleted'){$mensaje="Parroquia eliminada."; $tipo_mensaje="warning";}
}

// 3. LISTAR
$lista = [];
try { $lista = $pdo->query("SELECT * FROM parroquias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC); } catch(Exception $e){}
?>

<?php if($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card card-custom h-100">
            <div class="card-header-custom text-center"><i class="fas fa-church me-2"></i> Nueva Parroquia</div>
            <div class="card-body p-4">
                <form action="index.php?ver=parroquias" method="POST">
                    <input type="hidden" name="accion" value="guardar">
                    <div class="mb-3">
                        <label class="form-label">Nombre Parroquia</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: San Bartolomé" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Párroco / Responsable</label>
                        <input type="text" name="parroco" class="form-control" placeholder="Ej: P. Juan Pérez">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ubicación / Zona</label>
                        <input type="text" name="ubicacion" class="form-control" placeholder="Ej: Huacho Centro">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" placeholder="Opcional">
                    </div>
                    <div class="d-grid mt-4"><button type="submit" class="btn btn-vino">Guardar Registro</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-custom h-100">
            <div class="card-header-custom"><i class="fas fa-list me-2"></i> Directorio</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead><tr><th class="ps-4">Nombre</th><th>Párroco</th><th>Ubicación</th><th></th></tr></thead>
                        <tbody>
                            <?php if(empty($lista)): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">No hay parroquias registradas.</td></tr>
                            <?php else: ?>
                                <?php foreach($lista as $p): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($p['parroco']); ?></td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($p['ubicacion']); ?></small></td>
                                    <td class="text-end pe-3">
                                        <form method="POST" onsubmit="return confirm('¿Eliminar esta parroquia?');" style="display:inline;">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-times"></i></button>
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