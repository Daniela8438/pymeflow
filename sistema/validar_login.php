<?php
// 1. Iniciar la sesión
session_start();

// 2. Incluir el archivo de conexión a la base de datos
include 'conexion.php';

// 3. Verificar si se enviaron datos por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 4. Obtener el usuario y la contraseña del formulario
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // 5. Preparar la consulta para evitar inyecciones SQL
    $stmt = $conexion->prepare("SELECT id, nombre_completo, password, rol FROM usuarios WHERE usuario = ? AND estado = 'activo'");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // 6. Verificar si se encontró un usuario
    if ($resultado->num_rows === 1) {
        $user = $resultado->fetch_assoc();

        // 7. Verificar si la contraseña coincide con el hash guardado en la BD
        if (password_verify($password, $user['password'])) {
            // ¡Login correcto!
            
            // 8. Guardar los datos del usuario en la sesión
            $_SESSION['loggedin'] = true;
            $_SESSION['id_usuario'] = $user['id'];
            $_SESSION['nombre_usuario'] = $user['nombre_completo'];
            $_SESSION['rol_usuario'] = $user['rol'];
            
            // 9. Redirigir al panel principal
            header("Location: dashboard.php");
            exit();

        } else {
            // Contraseña incorrecta
            header("Location: login.php?error=1");
            exit();
        }
    } else {
        // Usuario no encontrado o inactivo
        header("Location: login.php?error=1");
        exit();
    }

    $stmt->close();
    $conexion->close();

} else {
    // Si alguien intenta acceder al archivo directamente, lo redirige al login
    header("Location: login.php");
    exit();
}
?>