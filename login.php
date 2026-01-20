<?php
session_start();
// Si el usuario ya está logueado, lo redirigimos a su panel correspondiente
if (isset($_SESSION['id_usuario'])) {
    if ($_SESSION['rol'] == 'administrador') {
        header('Location: dashboard.php');
    } else {
        header('Location: venta_formulario.php');
    }
    exit();
}
$error = isset($_GET['error']) ? 'Usuario o contraseña incorrectos.' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso al Sistema - PymeFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .login-container img { width: 150px; margin-bottom: 20px; }
        .login-container h1 { margin-bottom: 25px; color: #333; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn { padding: 12px 15px; border: none; border-radius: 5px; color: white; text-decoration: none; cursor: pointer; font-size: 1em; width: 100%; }
        .btn-login { background-color: #005f73; }
        .btn-back { display: inline-block; width: auto; margin-top: 20px; background-color: #6c757d; }
        .error-message { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="logo.png" alt="Logo">
        <h1>Acceso al Sistema</h1>
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="procesar_login.php" method="POST">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-login">Ingresar</button>
        </form>

        <a href="index.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
    </div>
</body>
</html>