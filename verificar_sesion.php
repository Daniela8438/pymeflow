<?php
// 1. INICIAR LA SESIÓN.
// Esta es la línea clave que faltaba. Debe ser lo PRIMERO en el archivo.
session_start();

// 2. VERIFICAR SI EL USUARIO ESTÁ LOGUEADO.
// Si no existe la variable de sesión 'id_usuario', significa que no ha iniciado sesión.
if (!isset($_SESSION['id_usuario'])) {
    
    // 3. REDIRIGIR AL LOGIN.
    // Lo enviamos de vuelta a la página de login para que ingrese.
    header("Location: login.php");
    
    // 4. DETENER EL SCRIPT.
    // Es importante detener la ejecución del script después de una redirección.
    exit();
}
?>