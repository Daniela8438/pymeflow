<?php
// Inicia la sesión para poder acceder a sus variables
session_start();

// Elimina todas las variables de la sesión
$_SESSION = array();

// Destruye la sesión por completo
session_destroy();

// Redirige al usuario a la página de login
header("Location: login.php");
exit;
?>