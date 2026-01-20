<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'verificar_sesion.php';
require 'vendor/autoload.php'; // Cargar Composer
include 'conexion.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- 1. DEFINIR DATOS DEL EMISOR (NUESTRA EMPRESA) ---
define('EMISOR_RAZON_SOCIAL', 'PymeFlow S.A.');
define('EMISOR_DIRECCION', 'Av. Belgrano 637, C1029 Cdad. Autónoma de Buenos Aires');
define('EMISOR_CUIT', '30-12345678-9');
define('EMISOR_CONDICION_IVA', 'Responsable Inscripto'); // <-- CLAVE: Somos RI

// Verificar ID de pedido
if (!isset($_GET['id'])) {
    die("Error: No se proporcionó un ID de venta.");
}
$id_pedido = $_GET['id'];

// --- 2. OBTENER DATOS DEL RECEPTOR (CLIENTE) Y PEDIDO ---
$stmt_pedido = $conexion->prepare("
    SELECT p.*, c.razon_social, c.cuit, c.direccion, c.condicion_iva, u.nombre_completo AS vendedor 
    FROM pedidos p 
    JOIN clientes c ON p.id_cliente = c.id 
    JOIN usuarios u ON p.id_vendedor = u.id 
    WHERE p.id = ?");
$stmt_pedido->bind_param("i", $id_pedido);
$stmt_pedido->execute();
$pedido_resultado = $stmt_pedido->get_result();

if ($pedido_resultado->num_rows === 0) {
    die("Error: No se encontró la venta.");
}
$pedido = $pedido_resultado->fetch_assoc();

// --- ¡¡CORRECCIÓN APLICADA AQUÍ!! ---
// Convertimos a minúsculas y quitamos espacios para una comparación segura
$cliente_condicion_iva = strtolower(trim($pedido['condicion_iva']));

// --- 3. DETERMINAR EL TIPO DE FACTURA (LÓGICA A/B/C) ---
$tipo_factura = '';

if (EMISOR_CONDICION_IVA == 'Responsable Inscripto') {
    // --- ¡¡CORRECCIÓN APLICADA AQUÍ!! ---
    // Comparamos en minúsculas
    if ($cliente_condicion_iva == 'responsable inscripto') {
        $tipo_factura = 'A';
    } else {
        // Para Monotributista, Consumidor Final o Exento
        $tipo_factura = 'B';
    }
} else {
    // Si fuéramos Monotributistas, siempre sería 'C'
    $tipo_factura = 'C';
}

// Obtener los productos del pedido
$stmt_items = $conexion->prepare("SELECT pi.*, pr.nombre AS nombre_producto FROM pedidos_items pi JOIN productos pr ON pi.id_producto = pr.id WHERE pi.id_pedido = ?");
$stmt_items->bind_param("i", $id_pedido);
$stmt_items->execute();
$items_resultado = $stmt_items->get_result();

// --- 4. CÁLCULOS DE IMPUESTOS (DESGLOSAR IVA) ---
$total_final_con_iva = $pedido['total'];
$subtotal_neto = $total_final_con_iva / 1.21;
$monto_iva_21 = $total_final_con_iva - $subtotal_neto;

// --- 5. CONSTRUIR EL HTML DE LA FACTURA ---
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura ' . $tipo_factura . ' N°' . $pedido['id'] . '</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header-container { border: 2px solid #000; padding: 10px; }
        .header-left { float: left; width: 48%; }
        .header-right { float: right; width: 48%; text-align: center; }
        .tipo-factura { border: 2px solid #000; font-size: 40px; font-weight: bold; width: 50px; height: 50px; line-height: 50px; margin: 10px auto; }
        .cliente-info { border: 1px solid #aaa; padding: 15px; margin-top: 20px; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th, .items-table td { border: 1px solid #aaa; padding: 8px; }
        .items-table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total-section { margin-top: 20px; float: right; width: 40%; }
        .total-section table { width: 100%; border-collapse: collapse; }
        .total-section table td { padding: 5px 8px; }
        .grand-total { font-weight: bold; font-size: 1.2em; border-top: 2px solid #000; }
        .afip-info { text-align: center; font-size: 9px; margin-top: 50px; }
        .clearfix { clear: both; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <div class="header-left">
                <h2>' . EMISOR_RAZON_SOCIAL . '</h2>
                <p>
                    <strong>Razón Social:</strong> ' . EMISOR_RAZON_SOCIAL . '<br>
                    <strong>Dirección:</strong> ' . EMISOR_DIRECCION . '<br>
                    <strong>Condición IVA:</strong> ' . EMISOR_CONDICION_IVA . '
                </p>
            </div>
            <div class="header-right">
                <div class="tipo-factura">' . $tipo_factura . '</div>
                <h2>FACTURA</h2>
                <p>
                    <strong>N° Comprobante:</strong> ' . str_pad($pedido['id'], 8, "0", STR_PAD_LEFT) . '<br>
                    <strong>Fecha de Emisión:</strong> ' . date("d/m/Y", strtotime($pedido['fecha_creacion'])) . '<br>
                    <strong>CUIT:</strong> ' . EMISOR_CUIT . '
                </p>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class.cliente-info">
            <strong>Señor(es):</strong> ' . htmlspecialchars($pedido['razon_social']) . '<br>
            <strong>Dirección:</strong> ' . htmlspecialchars($pedido['direccion']) . '<br>
            <strong>CUIT:</strong> ' . htmlspecialchars($pedido['cuit']) . '<br>
            <strong>Condición IVA:</strong> ' . htmlspecialchars($pedido['condicion_iva']) . '
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio Unit. (c/IVA)</th>';
                    
// --- LÓGICA DE COLUMNAS (Factura A desglosa, B y C no) ---
if ($tipo_factura == 'A') {
    $html .= '  <th class="text-right">Precio Unit. (Neto)</th>
                <th class="text-right">Subtotal Neto</th>
                <th class="text-right">Subtotal IVA (21%)</th>';
}
$html .= '      <th class="text-right">Subtotal Final</th>
            </tr>
        </thead>
        <tbody>';
            
while ($item = $items_resultado->fetch_assoc()) {
    $precio_final_item = $item['precio_unitario'];
    $cantidad = $item['cantidad'];
    
    // Desglosamos el IVA para este item
    $precio_neto_item = $precio_final_item / 1.21;
    $monto_iva_item = $precio_final_item - $precio_neto_item;
    
    $subtotal_final_item = $precio_final_item * $cantidad;
    $subtotal_neto_item = $precio_neto_item * $cantidad;
    $subtotal_iva_item = $monto_iva_item * $cantidad;

    $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['nombre_producto']) . '</td>
                    <td class="text-right">' . number_format($cantidad, 2, ',', '.') . '</td>
                    <td class="text-right">$' . number_format($precio_final_item, 2, ',', '.') . '</td>';
if ($tipo_factura == 'A') {
    $html .= '      <td class="text-right">$' . number_format($precio_neto_item, 2, ',', '.') . '</td>
                    <td class="text-right">$' . number_format($subtotal_neto_item, 2, ',', '.') . '</td>
                    <td class="text-right">$' . number_format($subtotal_iva_item, 2, ',', '.') . '</td>';
}
$html .= '          <td class="text-right">$' . number_format($subtotal_final_item, 2, ',', '.') . '</td>
                </tr>';
}
$html .= '
            </tbody>
        </table>

        <div class="total-section">
            <table>';
// --- LÓGICA DE TOTALES (Factura A desglosa) ---
if ($tipo_factura == 'A') {
    $html .= '  <tr>
                    <td>Subtotal Neto Gravado:</td>
                    <td class="text-right">$' . number_format($subtotal_neto, 2, ',', '.') . '</td>
                </tr>
                <tr>
                    <td>IVA (21%):</td>
                    <td class="text-right">$' . number_format($monto_iva_21, 2, ',', '.') . '</td>
                </tr>';
}
$html .= '      <tr class="grand-total">
                    <td>Total:</td>
                    <td class="text-right">$' . number_format($total_final_con_iva, 2, ',', '.') . '</td>
                </tr>
            </table>
        </div>
        
        <div class="clearfix"></div>
        
        <div class="afip-info">
            <p><strong>Comprobante sin validez fiscal (documento no oficial).</strong><br>
            Esta es una simulación de Factura ' . $tipo_factura . '.</p>
        </div>
    </div>
</body>
</html>';

// --- 6. GENERAR EL PDF ---
$options = new Options();
$options->set('isRemoteEnabled', TRUE);
$options->set('defaultFont', 'DejaVu Sans'); // Para que funcionen tildes y símbolos

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$modo_descarga = isset($_GET['modo']) && $_GET['modo'] == 'descargar';

$dompdf->stream(
    "factura_" . $tipo_factura . "_N" . $pedido['id'] . ".pdf", 
    array("Attachment" => $modo_descarga) // true = Descargar, false = Ver en pantalla
);
?>