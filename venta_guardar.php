<?php
include 'verificar_sesion.php';
include 'conexion.php';

// Verificamos que se recibieron los datos esperados
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cliente']) && !empty($_POST['productos'])) {
    
    $id_cliente = $_POST['id_cliente'];
    $productos_venta = $_POST['productos'];
    $id_vendedor = $_SESSION['id_usuario'];
    $estado_pedido = $_POST['estado_pedido'];
    $estado_pago = $_POST['estado_pago'];
    $total_recalculado = 0;

    // Recalculamos el total en el servidor por seguridad
    foreach ($productos_venta as $prod) {
        $total_recalculado += $prod['cantidad'] * $prod['precio'];
    }

    // Iniciamos una transacción
    $conexion->begin_transaction();

    try {
        // 1. Insertar el pedido en la tabla `pedidos`
        $stmt_pedido = $conexion->prepare("INSERT INTO pedidos (id_cliente, id_vendedor, total, estado_pedido, estado_pago) VALUES (?, ?, ?, ?, ?)");
        $stmt_pedido->bind_param("iidss", $id_cliente, $id_vendedor, $total_recalculado, $estado_pedido, $estado_pago);
        $stmt_pedido->execute();
        
        $id_pedido = $conexion->insert_id;

        // Preparamos las consultas para los items y el stock
        $stmt_item = $conexion->prepare("INSERT INTO pedidos_items (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt_stock = $conexion->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");

        foreach ($productos_venta as $id_producto => $prod) {
            $cantidad = $prod['cantidad'];
            $precio = $prod['precio'];

            // 2. Insertar el item en la venta
            $stmt_item->bind_param("iiid", $id_pedido, $id_producto, $cantidad, $precio);
            $stmt_item->execute();

            // 3. Actualizar el stock (solo si el pedido no fue cancelado)
            if ($estado_pedido != 'cancelado') {
                 $stmt_stock->bind_param("ii", $cantidad, $id_producto);
                 $stmt_stock->execute();
            }
        }

        // Si todo funcionó, confirmamos los cambios
        $conexion->commit();

        // Redirigimos al historial de ventas (el nuevo archivo)
        header("Location: historial_ventas.php?exito=1");
        exit();

    } catch (mysqli_sql_exception $exception) {
        // Si algo falló, revertimos todos los cambios para no dejar datos corruptos
        $conexion->rollback();
        echo "Error al procesar la venta: " . $exception->getMessage();
    }
    
} else {
    // Si no se reciben los datos correctos
    header("Location: venta_formulario.php?error=datos_insuficientes");
    exit();
}
?>