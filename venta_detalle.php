<?php
include 'verificar_sesion.php';
$pagina_activa = 'ventas';
include 'conexion.php';

if (!isset($_GET['id'])) {
    header("Location: historial_ventas.php"); // Redirigimos al historial si no hay ID
    exit();
}
$id_pedido = $_GET['id'];

// Consulta para los datos principales del pedido
$stmt_pedido = $conexion->prepare("SELECT p.*, c.razon_social, c.cuit, u.nombre_completo AS vendedor FROM pedidos p JOIN clientes c ON p.id_cliente = c.id JOIN usuarios u ON p.id_vendedor = u.id WHERE p.id = ?");
$stmt_pedido->bind_param("i", $id_pedido);
$stmt_pedido->execute();
$pedido = $stmt_pedido->get_result()->fetch_assoc();

// Consulta para los productos del pedido
$stmt_items = $conexion->prepare("SELECT pi.*, pr.nombre AS nombre_producto FROM pedidos_items pi JOIN productos pr ON pi.id_producto = pr.id WHERE pi.id_pedido = ?");
$stmt_items->bind_param("i", $id_pedido);
$stmt_items->execute();
$items_resultado = $stmt_items->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Venta #<?php echo $id_pedido; ?> - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); font-size: 16px; line-height: 24px; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #555; background: #fff; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        .invoice-box table td { padding: 5px; vertical-align: top; }
        .invoice-box table tr.top table td.title { font-size: 45px; line-height: 45px; color: #333; }
        .invoice-box table tr.information table td { padding-bottom: 40px; }
        .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
        .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; }
        .btn-back { display: inline-block; margin-top: 20px; background-color: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <header class="dashboard-header">
            <div class="user-info">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></div>
            <a href="logout.php" class="logout-button">Cerrar Sesión</a>
        </header>
        <main class="content">
            <h1>Detalle de Venta</h1>
            <div class="invoice-box">
                <table>
                    <tr class="top">
                        <td colspan="4">
                            <table>
                                <tr>
                                    <td class="title"><img src="img/logo.png" style="width:100%; max-width:150px;"></td>
                                    <td>Venta #: <?php echo htmlspecialchars($pedido['id']); ?><br>Creada: <?php echo date("d/m/Y", strtotime($pedido['fecha_creacion'])); ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr class="information">
                        <td colspan="4">
                            <table>
                                <tr>
                                    <td><strong>Cliente:</strong><br><?php echo htmlspecialchars($pedido['razon_social']); ?><br>CUIT: <?php echo htmlspecialchars($pedido['cuit']); ?></td>
                                    <td><strong>Vendido por:</strong><br><?php echo htmlspecialchars($pedido['vendedor']); ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr class="heading"><td>Producto</td><td style="text-align: center;">Cantidad</td><td style="text-align: right;">Precio Unit.</td><td style="text-align: right;">Subtotal</td></tr>
                    <?php while($item = $items_resultado->fetch_assoc()): ?>
                    <tr class="item">
                        <td><?php echo htmlspecialchars($item['nombre_producto']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($item['cantidad']); ?></td>
                        <td style="text-align: right;">$<?php echo number_format($item['precio_unitario'], 2, ',', '.'); ?></td>
                        <td style="text-align: right;">$<?php echo number_format($item['cantidad'] * $item['precio_unitario'], 2, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <tr class="total">
                        <td colspan="3"></td>
                        <td style="text-align: right;"><strong>Total: $<?php echo number_format($pedido['total'], 2, ',', '.'); ?></strong></td>
                    </tr>
                </table>
                <a href="historial_ventas.php" class="btn-back">Volver al Historial</a>
            </div>
        </main>
    </div>
</body>
</html>