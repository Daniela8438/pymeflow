<?php
include 'verificar_sesion.php';
include 'conexion.php';

header('Content-Type: application/json');

$respuesta = [];

if (isset($_GET['term'])) {
    $busqueda = "%" . trim($_GET['term']) . "%";
    
    // Buscamos productos que tengan stock
    $stmt = $conexion->prepare("SELECT id, nombre, precio_venta, stock_actual FROM productos WHERE (nombre LIKE ?) AND stock_actual > 0 LIMIT 10");
    $stmt->bind_param("s", $busqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    while ($producto = $resultado->fetch_assoc()) {
        $respuesta[] = $producto;
    }
    $stmt->close();
}

echo json_encode($respuesta);
$conexion->close();
?>