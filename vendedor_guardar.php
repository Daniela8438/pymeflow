<?php
include 'verificar_sesion.php';
include 'conexion.php';

// Solo administradores pueden ejecutar este script
if ($_SESSION['rol_usuario'] !== 'administrador') {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id = $_POST['id'];
    $nombre_completo = trim($_POST['nombre_completo']);
    $usuario = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // No usamos trim() por si la contraseña tiene espacios intencionales
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];

    // Lógica para INSERTAR un nuevo usuario
    if (empty($id)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (nombre_completo, usuario, email, password, rol, estado, fecha_alta) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssss", $nombre_completo, $usuario, $email, $password_hash, $rol, $estado);
    } 
    // Lógica para ACTUALIZAR un usuario existente
    else {
        if (!empty($password)) {
            // Si se proveyó una nueva contraseña, la actualizamos
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nombre_completo = ?, usuario = ?, email = ?, password = ?, rol = ?, estado = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ssssssi", $nombre_completo, $usuario, $email, $password_hash, $rol, $estado, $id);
        } else {
            // Si la contraseña está vacía, no la actualizamos
            $sql = "UPDATE usuarios SET nombre_completo = ?, usuario = ?, email = ?, rol = ?, estado = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sssssi", $nombre_completo, $usuario, $email, $rol, $estado, $id);
        }
    }
    
    // Ejecutamos la consulta y manejamos errores de duplicados
    try {
        if ($stmt->execute()) {
            header("Location: vendedores.php");
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) { // Error de entrada duplicada
            $error_url = "vendedor_formulario.php?id=" . $id;
            if (strpos($e->getMessage(), 'usuario')) {
                $error_url .= "&error=usuario_duplicado";
            } elseif (strpos($e->getMessage(), 'email')) {
                $error_url .= "&error=email_duplicado";
            }
            header("Location: " . $error_url);
        } else {
            echo "Error al guardar el usuario: " . $e->getMessage();
        }
    }
    
    $stmt->close();
    $conexion->close();

} else {
    header("Location: vendedores.php");
}
?>