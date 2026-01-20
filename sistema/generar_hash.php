<?php
// Archivo para generar un hash compatible

$password_para_encriptar = 'admin123';

// Usamos el motor de tu PHP para crear el hash
$nuevo_hash = password_hash($password_para_encriptar, PASSWORD_DEFAULT);

echo "<h1>Nuevo Hash Generado</h1>";
echo "<p>Tu instalación de PHP ha generado el siguiente hash para la contraseña 'admin123'. Cópialo completo:</p>";
// Mostramos el hash en un formato fácil de copiar
echo "<pre style='background:#eee; padding:10px; border:1px solid #ccc; font-size:1.2em;'>" . htmlspecialchars($nuevo_hash) . "</pre>";
?>