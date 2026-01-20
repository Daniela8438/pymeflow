<?php
include 'verificar_sesion.php';
include 'conexion.php';

header('Content-Type: application/json'); // Indicamos que la respuesta será en formato JSON

$respuesta = ['clientes' => []];

if (isset($_GET['term'])) {
    $busqueda = "%" . trim($_GET['term']) . "%";
    
    // Preparamos la consulta para buscar por razón social o CUIT
    $stmt = $conexion->prepare("SELECT id, razon_social, cuit FROM clientes WHERE razon_social LIKE ? OR cuit LIKE ? LIMIT 10");
    $stmt->bind_param("ss", $busqueda, $busqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    while ($cliente = $resultado->fetch_assoc()) {
        // Formateamos la respuesta para que sea fácil de mostrar
        $respuesta['clientes'][] = [
            'id' => $cliente['id'],
            'label' => $cliente['razon_social'] . ' (' . $cliente['cuit'] . ')', // Texto a mostrar en la lista
            'value' => $cliente['razon_social'] // Texto a poner en el campo de búsqueda
        ];
    }
    $stmt->close();
}

echo json_encode($respuesta['clientes']);
$conexion->close();
?>