<?php
// Iniciamos la sesión para poder guardar los datos del usuario.
session_start();

// Incluimos el archivo de conexión a la base de datos.
include 'conexion.php';

// Verificamos que los datos se hayan enviado por el método POST.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tomamos el identificador (puede ser email o usuario)
    $login_identifier = trim($_POST['usuario']); 
    $password = trim($_POST['password']);

    // Preparamos la consulta para buscar en la columna 'email' O en la columna 'usuario'.
    $stmt = $conexion->prepare("SELECT id, nombre_completo, password, rol FROM usuarios WHERE email = ? OR usuario = ? LIMIT 1");
    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conexion->error);
    }
    
    // Pasamos el identificador dos veces (uno para cada '?')
    $stmt->bind_param("ss", $login_identifier, $login_identifier);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Verificamos si se encontró un usuario.
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Verificamos si la contraseña coincide con la versión encriptada en la BD.
        if (password_verify($password, $usuario['password'])) {
            // ¡Contraseña correcta! Guardamos los datos importantes en la sesión.
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['nombre_usuario'] = $usuario['nombre_completo'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirigimos al usuario según su rol.
            if ($usuario['rol'] == 'administrador') {
                header("Location: dashboard.php");
            } else {
                header("Location: venta_formulario.php"); // Página principal para vendedores
            }
            exit(); // Es importante terminar el script después de una redirección.

        }
    }

    // Si el email no existe o la contraseña es incorrecta, redirigimos de vuelta al login con un error.
    header("Location: login.php?error=1");
    exit();

} else {
    // Si alguien intenta acceder a este archivo directamente, lo mandamos al login.
    header("Location: login.php");
    exit();
}
?>