<?php
include 'verificar_sesion.php';

// Solo los administradores pueden estar aquí
if ($_SESSION['rol'] !== 'administrador') {
    header('Location: dashboard.php');
    exit();
}

$pagina_activa = 'usuarios';
include 'conexion.php';

$error_message = '';
$usuario = [
    'id' => '',
    'nombre_completo' => '',
    'email' => '',
    'usuario' => '',
    'rol' => 'vendedor',
    'estado' => 'activo'
];
$titulo_pagina = "Agregar Nuevo Usuario";
$es_edicion = false;

// MODO EDICIÓN: Si recibimos un ID, cargamos los datos del usuario
if (isset($_GET['id'])) {
    $titulo_pagina = "Editar Usuario";
    $es_edicion = true;
    $id_usuario = $_GET['id'];
    
    $stmt_fetch = $conexion->prepare("SELECT id, nombre_completo, email, usuario, rol, estado FROM usuarios WHERE id = ?");
    $stmt_fetch->bind_param("i", $id_usuario);
    $stmt_fetch->execute();
    $resultado = $stmt_fetch->get_result();
    
    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
    }
    $stmt_fetch->close();
}

// MODO GUARDAR: Si se envía el formulario (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre_completo = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $usuario_login = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];

    // Validaciones
    if (empty($nombre_completo) || empty($email) || empty($usuario_login) || empty($rol) || empty($estado)) {
        $error_message = 'Todos los campos (excepto contraseña en edición) son obligatorios.';
    } else {
        try {
            if (empty($id)) {
                // --- CREAR NUEVO USUARIO ---
                if (empty($password)) {
                    $error_message = 'La contraseña es obligatoria para usuarios nuevos.';
                } else {
                    // Encriptamos la contraseña nueva
                    $hash_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre_completo, email, usuario, password, rol, estado, fecha_alta) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssssss", $nombre_completo, $email, $usuario_login, $hash_password, $rol, $estado);
                }
            } else {
                // --- ACTUALIZAR USUARIO EXISTENTE ---
                if (!empty($password)) {
                    // Si el usuario escribió una nueva contraseña, la actualizamos
                    $hash_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conexion->prepare("UPDATE usuarios SET nombre_completo = ?, email = ?, usuario = ?, password = ?, rol = ?, estado = ? WHERE id = ?");
                    $stmt->bind_param("ssssssi", $nombre_completo, $email, $usuario_login, $hash_password, $rol, $estado, $id);
                } else {
                    // Si dejó la contraseña en blanco, NO la actualizamos
                    $stmt = $conexion->prepare("UPDATE usuarios SET nombre_completo = ?, email = ?, usuario = ?, rol = ?, estado = ? WHERE id = ?");
                    $stmt->bind_param("sssssi", $nombre_completo, $email, $usuario_login, $rol, $estado, $id);
                }
            }

            if (empty($error_message)) {
                $stmt->execute();
                $stmt->close();
                header("Location: usuarios.php");
                exit();
            }

        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                // Error de 'Duplicate entry'
                $error_message = 'Error: Ya existe un usuario con ese Email o Nombre de Usuario.';
            } else {
                $error_message = 'Error al guardar el usuario: ' . $e->getMessage();
            }
        }
    }
    
    // Si hay error, poblamos el array 'usuario' para que no se borren los campos
    $usuario = compact('id', 'nombre_completo', 'email', 'usuario', 'rol', 'estado');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo_pagina; ?> - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .form-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .form-buttons { margin-top: 20px; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; color: white !important; text-decoration: none; cursor: pointer; font-size: 1em; }
        .btn-save { background-color: var(--color-secundario); }
        .btn-cancel { background-color: #6c757d; }
        .error-message { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .password-note { font-size: 0.85em; color: #6c757d; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <header class="dashboard-header">
            <div class="user-info">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></div>
            <a href="logout.php" class="logout-button">Cerrar Sesión</a>
        </header>

        <main class="content">
            <h1><?php echo $titulo_pagina; ?></h1>
            <div class="form-container">
                <?php if (!empty($error_message)) { echo '<div class="error-message">' . htmlspecialchars($error_message) . '</div>'; } ?>
                
                <form action="usuario_formulario.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id']); ?>">
                    
                    <div class="form-group">
                        <label for="nombre_completo">Nombre Completo</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="usuario">Nombre de Usuario (para login)</label>
                        <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password">
                        <?php if ($es_edicion): ?>
                            <small class="password-note">Dejar en blanco para no cambiar la contraseña.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select id="rol" name="rol" required>
                            <option value="vendedor" <?php echo ($usuario['rol'] == 'vendedor') ? 'selected' : ''; ?>>Vendedor</option>
                            <option value="administrador" <?php echo ($usuario['rol'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="activo" <?php echo ($usuario['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo ($usuario['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-save">Guardar</button>
                        <a href="usuarios.php" class="btn btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>