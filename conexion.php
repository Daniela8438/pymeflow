<?php

// --- Datos de Conexión ---
$host = "localhost";
$usuario = "root";
$password = ""; // La contraseña está vacía por defecto en XAMPP
$db_nombre = "pymeflow_db";

// --- Crear la Conexión ---
$conexion = new mysqli($host, $usuario, $password, $db_nombre);

// --- Verificar la Conexión ---
if ($conexion->connect_error) {
    // Si hay un error, el script se detiene y muestra el mensaje de error.
    die("Error de conexión: " . $conexion->connect_error);
}

// Establecer el juego de caracteres a UTF-8 para evitar problemas con tildes y caracteres especiales
$conexion->set_charset("utf8mb4");

// Opcional: Descomenta la siguiente línea para probar si la conexión funciona.
// Si ves este mensaje, ¡todo está correcto!
// echo "¡Conexión exitosa a la base de datos!";

?>