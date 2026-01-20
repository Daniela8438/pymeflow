<?php
include 'verificar_sesion.php';
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recibimos los datos del formulario
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio_venta = $_POST['precio_venta'];
    $precio_costo = $_POST['precio_costo'] ?: null; // Si está vacío, guarda NULL
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];

    // Validación simple en el servidor
    if (empty($nombre) || !is_numeric($precio_venta) || !is_numeric($stock_actual) || !is_numeric($stock_minimo)) {
        // Redirigir con error si faltan datos clave
        header("Location: producto_formulario.php?error=1");
        exit();
    }
    
    if (!empty($id)) {
        // Actualización
        $stmt = $conexion->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio_venta = ?, precio_costo = ?, stock_actual = ?, stock_minimo = ? WHERE id = ?");
        // "ssddiii" significa string, string, double, double, integer, integer, integer
        $stmt->bind_param("ssddiii", $nombre, $descripcion, $precio_venta, $precio_costo, $stock_actual, $stock_minimo, $id);
    } else {
        // Inserción
        $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, precio_venta, precio_costo, stock_actual, stock_minimo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddii", $nombre, $descripcion, $precio_venta, $precio_costo, $stock_actual, $stock_minimo);
    }
    
    if ($stmt->execute()) {
        header("Location: productos.php"); // Redirigimos a la lista si todo sale bien
    } else {
        echo "Error al guardar el producto: " . $stmt->error;
    }
    
    $stmt->close();
    $conexion->close();

} else {
    header("Location: productos.php");
}
?>