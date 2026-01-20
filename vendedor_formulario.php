<?php
include 'verificar_sesion.php';
$pagina_activa = 'vendedores';
include 'conexion.php';

// Solo administradores pueden acceder
if ($_SESSION['rol_usuario'] !== 'administrador') {
    header("Location: dashboard.php");
    exit();
}

// Inicializamos variables
$usuario = [
    'id' => '', 'nombre_completo' => '', 'dni' => '', 'email' => '', 'telefono' => '',
    'usuario' => '', 'rol' => 'vendedor', 'estado' => 'activo'
];
$titulo_pagina = "Agregar Nuevo Usuario";

// Lógica para modo EDICIÓN
if (isset($_GET['id'])) {
    $titulo_pagina = "Editar Usuario";
    $id_usuario = $_GET['id'];
    
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
    }
    $stmt->close();
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
        .form-buttons { margin-top: 20px; text-align: right; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; color: white; text-decoration: none; cursor: pointer; font-size: 1em; }
        .btn-save { background-color: var(--color-secundario); }
        .btn-cancel { background-color: #6c757d; }
        .error-message { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 15px; }
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
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message">
                        <?php 
                            if ($_GET['error'] == 'usuario_duplicado') echo "Error: El nombre de usuario ya existe.";
                            if ($_GET['error'] == 'email_duplicado') echo "Error: El email ya está registrado.";
                        ?>
                    </div>
                <?php endif; ?>
                <form action="vendedor_guardar.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id']); ?>">
                    <div class="form-group">
                        <label for="nombre_completo">Nombre Completo</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="usuario">Nombre de Usuario</label>
                        <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" <?php if (empty($usuario['id'])) echo 'required'; ?>>
                        <?php if (!empty($usuario['id'])): ?>
                            <small>Dejar en blanco para no cambiar la contraseña.</small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select id="rol" name="rol" required>
                            <option value="vendedor" <?php if ($usuario['rol'] == 'vendedor') echo 'selected'; ?>>Vendedor</option>
                            <option value="administrador" <?php if ($usuario['rol'] == 'administrador') echo 'selected'; ?>>Administrador</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="activo" <?php if ($usuario['estado'] == 'activo') echo 'selected'; ?>>Activo</option>
                            <option value="inactivo" <?php if ($usuario['estado'] == 'inactivo') echo 'selected'; ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <a href="vendedores.php" class="btn btn-cancel">Cancelar</a>
                        <button type="submit" class="btn btn-save">Guardar Usuario</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>