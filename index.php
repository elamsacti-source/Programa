<?php include 'includes/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 text-center">
        <h2>Bienvenido al Sistema Diocesano</h2>
        <p class="text-muted">Seleccione su perfil para ingresar:</p>
        
        <div class="d-grid gap-3 col-6 mx-auto mt-4">
            <a href="admin/index.php" class="btn btn-dark btn-lg">
                👨‍💼 Ingresar como Administrador
            </a>
            
            <a href="caja/registrar_ingreso.php" class="btn btn-success btn-lg">
                🛒 Ingresar como Cajero
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>