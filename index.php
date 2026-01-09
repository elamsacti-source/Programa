<?php
session_start();
require 'config/db.php';
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['usuario'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre = ? LIMIT 1");
    $stmt->execute([$nombre]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && $usuario['password'] === $password) {
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];
        header("Location: " . ($usuario['rol'] === 'admin' ? "admin/dashboard.php" : "vendedor/pos.php"));
        exit;
    } else { $error = "ACCESO DENEGADO"; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login SUARCORP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="login-container">
        <div class="card login-box">
            
            <img src="logo.png" alt="Logo" style="height: 70px; margin-bottom: 20px; filter: drop-shadow(0 0 10px rgba(255,215,0,0.3));" onerror="this.style.display='none'; document.getElementById('txt-brand').style.display='block';">
            
            <h1 id="txt-brand" class="shimmer" style="display:none; font-size: 2.2rem; margin-bottom: 5px;">SUARCORP</h1>
            
            <p style="color: #888; font-size: 0.75rem; letter-spacing: 3px; margin-bottom: 40px; text-transform: uppercase; font-weight: 300;">
                SISTEMA DE VENTAS
            </p>

            <?php if($error): ?>
                <div style="color: #ff4444; border: 1px solid #ff4444; padding: 10px; margin-bottom: 20px; font-size: 0.8rem; background: rgba(255, 68, 68, 0.1);">
                    ⚠ <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                
                <label>USUARIO</label>
                <input type="text" name="usuario" placeholder="INGRESE SU ID" required autocomplete="off">
                
                <label>CONTRASEÑA</label>
                <input type="password" name="password" placeholder="••••••••" required>

                <button type="submit" class="btn btn-primary" style="margin-top: 15px;">
                    INICIAR SESIÓN
                </button>
            </form>

            <div style="margin-top: 40px; border-top: 1px solid #222; padding-top: 20px;">
                <p style="color: #555; font-size: 0.7rem; letter-spacing: 1px;">SUARCORP ELITE EDITION - 2026</p>
            </div>
        </div>
    </div>

</body>
</html>