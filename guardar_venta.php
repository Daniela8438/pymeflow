<?php
include 'verificar_sesion.php';
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Datos de la venta
    $id_cliente = $_POST['id_cliente'];
    $total_venta = $_POST['total_venta'];
    $items_json = $_POST['items_venta'];
    
    // Obtenemos el ID del vendedor que está logueado
    $id_vendedor = $_SESSION['id_usuario']; 
    
    // Decodificamos los productos del carrito
    $items = json_decode($items_json, true);

    if (empty($id_cliente) || empty($items) || $total_venta <= 0) {
        die("Error: Faltan datos para registrar la venta.");
    }

    // Iniciamos una transacción para asegurar que todo se guarde correctamente
    $conexion->begin_transaction();

    try {
        // 1. Insertar el pedido principal en la tabla 'pedidos'
        $stmt_pedido = $conexion->prepare("INSERT INTO pedidos (id_cliente, id_vendedor, total, estado_pedido, fecha_creacion) VALUES (?, ?, ?, 'completado', NOW())");
        $stmt_pedido->bind_param("iid", $id_cliente, $id_vendedor, $total_venta);
        $stmt_pedido->execute();
        
        // Obtenemos el ID del pedido que acabamos de insertar
        $id_pedido_nuevo = $conexion->insert_id;
        
        // 2. Insertar cada item del pedido en 'pedidos_items' y actualizar stock
        $stmt_item = $conexion->prepare("INSERT INTO pedidos_items (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt_stock = $conexion->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
        
        foreach ($items as $item) {
            $id_producto = $item['id'];
            $cantidad = $item['cantidad'];
            $precio = $item['precio'];
            
            // Insertar el item
            $stmt_item->bind_param("iiid", $id_pedido_nuevo, $id_producto, $cantidad, $precio);
            $stmt_item->execute();
            
            // Actualizar el stock
            $stmt_stock->bind_param("ii", $cantidad, $id_producto);
            $stmt_stock->execute();
        }

        // 3. Si todo salió bien, confirmamos la transacción
        $conexion->commit();
        
        // Redirigimos al historial de facturas
        header("Location: facturas.php?exito=1");
        exit();

    } catch (mysqli_sql_exception $e) {
        // Si algo falla, revertimos todos los cambios
        $conexion->rollback();
        die("Error al guardar la venta: " . $e->getMessage());
    }

} else {
    header("Location: venta_formulario.php");
    exit();
}
?>