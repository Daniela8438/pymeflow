<?php
include 'verificar_sesion.php';
$pagina_activa = 'clientes';
include 'conexion.php';

if (!isset($_GET['id'])) {
    header("Location: clientes.php");
    exit();
}
$id_cliente = $_GET['id'];

// Obtener datos del cliente
$stmt_cliente = $conexion->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt_cliente->bind_param("i", $id_cliente);
$stmt_cliente->execute();
$cliente = $stmt_cliente->get_result()->fetch_assoc();

// Obtener historial de ventas del cliente
$stmt_ventas = $conexion->prepare("SELECT p.id, p.fecha_creacion, p.total, p.estado_pedido FROM pedidos p WHERE p.id_cliente = ? ORDER BY p.fecha_creacion DESC");
$stmt_ventas->bind_param("i", $id_cliente);
$stmt_ventas->execute();
$ventas = $stmt_ventas->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Cliente - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .content-table { border-collapse: collapse; margin: 25px 0; font-size: 0.9em; width: 100%; }
        .content-table thead tr { background-color: var(--color-primario); color: #ffffff; text-align: left; font-weight: bold; }
        .content-table th, .content-table td { padding: 12px 15px; border-bottom: 1px solid #ddd; }
        .btn-back { background-color: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
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
            <h1>Historial de Cliente</h1>
            <div class="card">
                <h2><?php echo htmlspecialchars($cliente['razon_social']); ?></h2>
                <p><strong>CUIT:</strong> <?php echo htmlspecialchars($cliente['cuit']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['telefono']); ?></p>
            </div>
            <div class="card">
                <h3>Compras Realizadas</h3>
                <table class="content-table">
                    <thead><tr><th>N° Venta</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead>
                    <tbody>
                        <?php if ($ventas->num_rows > 0): ?>
                            <?php while($venta = $ventas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $venta['id']; ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($venta['fecha_creacion'])); ?></td>
                                    <td>$<?php echo number_format($venta['total'], 2, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($venta['estado_pedido'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">Este cliente no tiene compras registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <a href="clientes.php" class="btn-back">Volver a Clientes</a>
        </main>
    </div>
</body>
</html>