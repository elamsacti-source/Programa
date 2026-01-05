<?php 
// admin/inquilinos.php - CON MONTO DE ALQUILER
$mensaje = "";
$tipo_mensaje = "";

// 1. REPARACIÓN DB: Agregamos columna 'monto_alquiler'
try {
    $pdo->exec("ALTER TABLE inquilinos ADD COLUMN monto_alquiler DECIMAL(10,2) DEFAULT 0.00");
} catch (Exception $e) {}

// 2. GUARDAR / ELIMINAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? 'guardar';

    if ($accion == 'eliminar') {
        $id = $_POST['id'] ?? 0;
        $pdo->prepare("UPDATE inquilinos SET activo = 0 WHERE id = ?")->execute([$id]);
        echo "<script>window.location.href='index.php?ver=inquilinos&status=deleted';</script>"; exit;
    }
    elseif ($accion == 'guardar') {
        $nombre = $_POST['nombre'];
        $resp = $_POST['responsable'];
        $monto = $_POST['monto_alquiler'];
        $dia = $_POST['dia_pago'];
        $tel = $_POST['telefono'];

        if ($nombre) {
            $sql = "INSERT INTO inquilinos (nombre, responsable, monto_alquiler, dia_pago, telefono) VALUES (?,?,?,?,?)";
            $pdo->prepare($sql)->execute([$nombre, $resp, $monto, $dia, $tel]);
            echo "<script>window.location.href='index.php?ver=inquilinos&status=success';</script>"; exit;
        }
    }
}

// 3. LISTAR
$lista = [];
try { $lista = $pdo->query("SELECT * FROM inquilinos WHERE activo = 1 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC); } catch(Exception $e){}
?>

<?php if(isset($_GET['status']) && $_GET['status']=='success'): ?>
    <div class="alert alert-success alert-dismissible fade show">¡Inquilino guardado! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card card-custom h-100">
            <div class="card-header-custom text-center"><i class="fas fa-user-plus me-2"></i> Nuevo Inquilino</div>
            <div class="card-body p-4">
                <form action="index.php?ver=inquilinos" method="POST">
                    <input type="hidden" name="accion" value="guardar">
                    <div class="mb-3">
                        <label class="form-label">Institución / Empresa</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Responsable</label>
                        <input type="text" name="responsable" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto Alquiler (S/.)</label>
                        <input type="number" step="0.01" name="monto_alquiler" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Día de Pago</label>
                        <input type="text" name="dia_pago" class="form-control" placeholder="Ej: 5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                    <div class="d-grid mt-4"><button type="submit" class="btn btn-vino">Guardar</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-custom h-100">
            <div class="card-header-custom"><i class="fas fa-users me-2"></i> Inquilinos Activos</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead><tr><th>Nombre</th><th>Monto</th><th>Día Pago</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach($lista as $i): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($i['nombre']); ?></td>
                                <td>S/. <?php echo number_format($i['monto_alquiler'], 2); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo $i['dia_pago']; ?></span></td>
                                <td class="text-end pe-3">
                                    <form method="POST" onsubmit="return confirm('¿Dar de baja?');">
                                        <input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                                        <button class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-times"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>