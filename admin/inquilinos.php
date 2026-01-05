<?php 
include '../config/db.php'; 

// Obtener lista de inquilinos para mostrar en la tabla de abajo
$stmt = $pdo->query("SELECT * FROM inquilinos WHERE activo = 1 ORDER BY nombre ASC");
$lista_inquilinos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inquilinos - Obispado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;800&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-vino: #5e1119;
            --color-oro: #c5a059;
            --color-papel: #fdfbf7;
        }
        body { background-color: var(--color-papel); font-family: 'Lora', serif; }
        
        .header-admin {
            background: linear-gradient(to right, #2b2b2b, #4a0d13);
            color: #fff; padding: 20px; border-bottom: 4px solid var(--color-oro);
            margin-bottom: 30px; text-align: center;
        }
        
        .card-form {
            border: 1px solid var(--color-oro);
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .form-label { font-family: 'Cinzel', serif; color: var(--color-vino); font-weight: 700; font-size: 0.9rem; }
        .btn-vino { background-color: var(--color-vino); color: #fff; font-family: 'Cinzel', serif; border: none; }
        .btn-vino:hover { background-color: #3b090e; color: #d4af37; }

        table { font-size: 0.95rem; }
        thead { background-color: var(--color-vino); color: #fff; font-family: 'Cinzel', serif; }
    </style>
</head>
<body>

    <div class="header-admin">
        <h2 style="font-family: 'Cinzel', serif; margin: 0;">Gestión de Propiedades y Alquileres</h2>
        <small style="color: var(--color-oro);">Administración de Inquilinos y Contratos</small>
    </div>

    <div class="container pb-5">
        <div class="row">
            
            <div class="col-md-4 mb-4">
                <div class="card card-form p-4">
                    <h5 class="text-center mb-4" style="font-family: 'Cinzel', serif; color: var(--color-vino);">
                        <i class="fas fa-plus-circle me-2"></i> Nuevo Inquilino
                    </h5>
                    
                    <form action="guardar_inquilino.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Empresa / Institución</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Librería San Pablo" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Responsable de Pago</label>
                            <input type="text" name="responsable" class="form-control" placeholder="Ej: Hna. María González" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fecha / Día de Pago</label>
                            <input type="text" name="dia_pago" class="form-control" placeholder="Ej: Día 5 de cada mes">
                            <small class="text-muted" style="font-size: 0.8rem;">Referencia para el cobro.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono / Contacto</label>
                            <input type="text" name="telefono" class="form-control" placeholder="Opcional">
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-vino py-2">Guardar Registro</button>
                        </div>
                    </form>
                </div>
                
                <div class="text-center mt-3">
                    <a href="index.php" class="text-muted" style="text-decoration: none;">&larr; Volver al Panel Admin</a>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-form p-4">
                    <h5 class="mb-4" style="font-family: 'Cinzel', serif; color: var(--color-vino);">
                        <i class="fas fa-list me-2"></i> Inquilinos Activos
                    </h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th>Responsable</th>
                                    <th>Día Pago</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($lista_inquilinos)): ?>
                                    <tr><td colspan="4" class="text-center py-3">No hay inquilinos registrados.</td></tr>
                                <?php else: ?>
                                    <?php foreach($lista_inquilinos as $inq): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?php echo $inq['nombre']; ?></td>
                                        <td><?php echo $inq['responsable']; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo $inq['dia_pago']; ?></span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-danger" title="Dar de baja"><i class="fas fa-times"></i></a>
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

</body>
</html>