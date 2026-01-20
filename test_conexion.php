<?php
// Forzamos a que PHP nos muestre todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Probando conexión a la base de datos...</h1>";

// --- Los mismos datos de tu archivo conexion.php ---
$host = "localhost";
$usuario = "root";
$password = "";
$db_nombre = "pymeflow_db";

// --- Intentamos la conexión ---
$conexion = new mysqli($host, $usuario, $password, $db_nombre);

// --- Verificamos la conexión ---
if ($conexion->connect_error) {
    // Si hay un error, el script se detiene y muestra el mensaje EXACTO del error.
    die("<p style='color: red; font-size: 1.2em;'><strong>Error de conexión:</strong> " . $conexion->connect_error . "</p>");
}

// Si llegamos hasta aquí, todo funcionó bien.
echo "<p style='color: green; font-size: 1.2em;'><strong>¡Conexión exitosa a la base de datos '" . $db_nombre . "'!</strong></p>";

$conexion->close();
?>