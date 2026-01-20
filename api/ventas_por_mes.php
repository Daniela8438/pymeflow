<?php
header('Content-Type: application/json');
include '../conexion.php'; // Salimos un nivel para encontrar la conexión

$query = "SELECT MONTHNAME(fecha_creacion) as mes, SUM(total) as total_ventas 
          FROM pedidos 
          WHERE YEAR(fecha_creacion) = YEAR(CURDATE())
          GROUP BY MONTH(fecha_creacion) 
          ORDER BY MONTH(fecha_creacion)";

$resultado = $conexion->query($query);

$datos = [];
while($fila = $resultado->fetch_assoc()) {
    $datos[] = $fila;
}

echo json_encode($datos);
?>