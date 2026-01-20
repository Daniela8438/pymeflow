<?php
header('Content-Type: application/json');
include '../conexion.php';

$query = "SELECT pr.nombre, SUM(pi.cantidad) AS total_cantidad
          FROM pedidos_items pi
          JOIN productos pr ON pi.id_producto = pr.id
          GROUP BY pi.id_producto
          ORDER BY total_cantidad DESC
          LIMIT 5"; // Limitamos a los 5 productos más vendidos

$resultado = $conexion->query($query);

$datos = [];
while($fila = $resultado->fetch_assoc()) {
    $datos[] = $fila;
}

echo json_encode($datos);
?>