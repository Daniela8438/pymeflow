<?php
include 'verificar_sesion.php';
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pedido = $_POST['id_pedido'];
    $estado_pedido = $_POST['estado_pedido'];
    $estado_pago = $_POST['estado_pago'];

    $stmt = $conexion->prepare("UPDATE pedidos SET estado_pedido = ?, estado_pago = ? WHERE id = ?");
    $stmt->bind_param("ssi", $estado_pedido, $estado_pago, $id_pedido);

    if ($stmt->execute()) {
        header("Location: ventas.php?actualizado=1");
    } else {
        echo "Error al actualizar los estados.";
    }
    $stmt->close();
} else {
    header("Location: ventas.php");
}
?>