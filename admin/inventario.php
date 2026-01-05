<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <h3>🛠️ Gestión de Inventario (Admin)</h3>
        <p>Registre aquí los Libros Litúrgicos o Insumos disponibles para venta.</p>
        <hr>
    </div>

    <div class="col-md-6">
        <div class="card p-4">
            <h5 class="card-title">Nuevo Producto</h5>
            <form action="procesar_producto.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Nombre del Producto</label>
                    <input type="text" class="form-control" placeholder="Ej. Misal Romano, Vino de Misa" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Categoría</label>
                    <select class="form-select">
                        <option value="2">Venta de Insumos</option>
                        <option value="3">Venta de Libros Litúrgicos</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Precio Unitario (S/.)</label>
                        <input type="number" step="0.01" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stock Inicial</label>
                        <input type="number" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Guardar Producto</button>
            </form>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="alert alert-info">
            <strong>Nota para el Admin:</strong> <br>
            Los alquileres y Parroquias se configuran en otros módulos. Aquí solo gestionamos bienes tangibles.
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>