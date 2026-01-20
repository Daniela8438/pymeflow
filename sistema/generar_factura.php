<?php // <-- ASEGÚRATE DE QUE TU ARCHIVO EMPIECE EXACTAMENTE CON ESTA LÍNEA

// Incluimos la seguridad de la sesión
include 'verificar_sesion.php';

// Cargar el autoloader de Composer
require 'vendor/autoload.php';

// Usar las clases de Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

// Incluimos la conexión a la base de datos
include 'conexion.php';

// Verificamos que se reciba un ID de pedido
if (!isset($_GET['id'])) {
    die("Error: No se proporcionó un ID de venta.");
}
$id_pedido = $_GET['id'];

// --- OBTENER DATOS DE LA BASE DE DATOS ---

// Consulta para los datos principales del pedido
$stmt_pedido = $conexion->prepare("SELECT p.*, c.razon_social, c.cuit, c.direccion, u.nombre_completo AS vendedor FROM pedidos p JOIN clientes c ON p.id_cliente = c.id JOIN usuarios u ON p.id_vendedor = u.id WHERE p.id = ?");
$stmt_pedido->bind_param("i", $id_pedido);
$stmt_pedido->execute();
$pedido_resultado = $stmt_pedido->get_result();

if ($pedido_resultado->num_rows === 0) {
    die("Error: No se encontró la venta con el ID proporcionado.");
}
$pedido = $pedido_resultado->fetch_assoc();

// Consulta para los productos del pedido
$stmt_items = $conexion->prepare("SELECT pi.*, pr.nombre AS nombre_producto FROM pedidos_items pi JOIN productos pr ON pi.id_producto = pr.id WHERE pi.id_pedido = ?");
$stmt_items->bind_param("i", $id_pedido);
$stmt_items->execute();
$items_resultado = $stmt_items->get_result();

// --- CONSTRUIR EL HTML DE LA FACTURA (VERSIÓN CON IVA) ---
$total_pedido = $pedido['total'];
$neto_gravado = $total_pedido / 1.21;
$monto_iva = $total_pedido - $neto_gravado;

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura N°' . htmlspecialchars($pedido['id']) . '</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; }
        .header h1 { margin: 0; }
        .invoice-details { margin: 20px 0; }
        .invoice-details table { width: 100%; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; }
        .items-table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total-section { margin-top: 20px; }
        .total-section table { width: 50%; float: right; }
        .total-section table td { padding: 5px 8px; }
        .grand-total { font-weight: bold; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>Factura</h1><p>PymeFlow S.A.</p></div>
        <div class="invoice-details">
            <table>
                <tr>
                    <td>
                        <strong>Factura a:</strong><br>
                        ' . htmlspecialchars($pedido['razon_social']) . '<br>
                        Dirección: ' . htmlspecialchars($pedido['direccion']) . '<br>
                        CUIT: ' . htmlspecialchars($pedido['cuit']) . '
                    </td>
                    <td class="text-right">
                        <strong>Factura N°:</strong> ' . htmlspecialchars($pedido['id']) . '<br>
                        <strong>Fecha:</strong> ' . date("d/m/Y", strtotime($pedido['fecha_creacion'])) . '
                    </td>
                </tr>
            </table>
        </div>
        <table class="items-table">
            <thead>
                <tr><th>Producto</th><th class="text-right">Cantidad</th><th class="text-right">Precio Unit. (IVA Incl.)</th><th class="text-right">Subtotal</th></tr>
            </thead>
            <tbody>';
            
while ($item = $items_resultado->fetch_assoc()) {
    $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['nombre_producto']) . '</td>
                    <td class="text-right">' . htmlspecialchars($item['cantidad']) . '</td>
                    <td class="text-right">$' . number_format($item['precio_unitario'], 2, ',', '.') . '</td>
                    <td class="text-right">$' . number_format($item['cantidad'] * $item['precio_unitario'], 2, ',', '.') . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
        <div class="total-section">
            <table>
                <tr><td>Subtotal Neto:</td><td class="text-right">$' . number_format($neto_gravado, 2, ',', '.') . '</td></tr>
                <tr><td>IVA (21%):</td><td class="text-right">$' . number_format($monto_iva, 2, ',', '.') . '</td></tr>
                <tr class="grand-total"><td>Total:</td><td class="text-right">$' . number_format($total_pedido, 2, ',', '.') . '</td></tr>
            </table>
        </div>
    </div>
</body>
</html>';

// --- GENERAR EL PDF CON DOMPDF ---
$options = new Options();
$options->set('isRemoteEnabled', TRUE);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("factura_N" . $pedido['id'] . ".pdf");
?>