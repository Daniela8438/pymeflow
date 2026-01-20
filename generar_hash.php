<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador de Hashes de Contraseña</title>
    <style>
        body { font-family: sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 500px; }
        input[type="text"] { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { padding: 10px 15px; border: none; background-color: #005f73; color: white; border-radius: 5px; cursor: pointer; }
        .resultado { margin-top: 20px; background-color: #e9ecef; padding: 15px; border-radius: 5px; word-wrap: break-word; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Generador de Hashes de Contraseña</h1>
        <form method="POST">
            <label for="password">Ingresa la contraseña para encriptar:</label>
            <input type="text" name="password" id="password" required>
            <button type="submit">Generar Hash</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['password'])) {
            $password = $_POST['password'];
            // Generamos el hash seguro
            $hash = password_hash($password, PASSWORD_DEFAULT);
            echo '<div class="resultado"><strong>Contraseña:</strong> ' . htmlspecialchars($password) . '<br><strong>Hash generado:</strong><br>' . $hash . '</div>';
        }
        ?>
    </div>
</body>
</html>