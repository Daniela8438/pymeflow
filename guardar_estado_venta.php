<?php
include 'verificar_sesion.php';
include 'conexion.php';

// Verificamos que los datos lleguen por el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // CAMBIO: Verificamos que recibimos ambos estados
    if (isset($_POST['id_pedido']) && isset($_POST['estado_pedido']) && isset($_POST['estado_pago'])) {
        
        $id_pedido = $_POST['id_pedido'];
        $nuevo_estado_pedido = $_POST['estado_pedido'];
        $nuevo_estado_pago = $_POST['estado_pago'];

        // CAMBIO: La consulta UPDATE ahora actualiza las dos columnas
        $stmt = $conexion->prepare("UPDATE pedidos SET estado_pedido = ?, estado_pago = ? WHERE id = ?");
        // CAMBIO: bind_param ahora tiene tres variables ('s' para string, 'i' para integer)
        $stmt->bind_param("ssi", $nuevo_estado_pedido, $nuevo_estado_pago, $id_pedido);

        if ($stmt->execute()) {
            // Si todo sale bien, redirigimos al historial
            header("Location: historial_ventas.php?exito=actualizado");
            exit();
        } else {
            echo "Error al actualizar los estados: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Faltan datos para actualizar los estados.";
    }
} else {
    // Si alguien intenta acceder a este archivo directamente, lo redirigimos
    header("Location: historial_ventas.php");
    exit();
}
$conexion->close();
?>