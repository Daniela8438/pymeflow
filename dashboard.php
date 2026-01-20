<?php
include 'verificar_sesion.php';

// Verificamos el rol. Si no es administrador, lo sacamos.
if ($_SESSION['rol'] !== 'administrador') {
    header('Location: venta_formulario.php'); // Lo mandamos a la página de ventas
    exit();
}

$pagina_activa = 'dashboard';
include 'conexion.php';

// --- CONSULTAS PARA OBTENER LAS ESTADÍSTICAS ---

// 1. Tarjetas Principales
$total_clientes = $conexion->query("SELECT COUNT(id) AS total FROM clientes")->fetch_assoc()['total'];
$total_productos = $conexion->query("SELECT COUNT(id) AS total FROM productos")->fetch_assoc()['total'];
$ventas_totales_res = $conexion->query("SELECT SUM(total) AS total FROM pedidos WHERE estado_pedido = 'completado'");
$ventas_totales = $ventas_totales_res->fetch_assoc()['total'] ?? 0;

// 2. Mejor Vendedor (por monto total vendido)
$mejor_vendedor_res = $conexion->query("
    SELECT u.nombre_completo, SUM(p.total) AS total_vendido
    FROM pedidos p
    JOIN usuarios u ON p.id_vendedor = u.id
    WHERE p.estado_pedido = 'completado'
    GROUP BY p.id_vendedor
    ORDER BY total_vendido DESC
    LIMIT 1
")->fetch_assoc();
$mejor_vendedor = $mejor_vendedor_res ? $mejor_vendedor_res['nombre_completo'] : 'N/A';

// 3. Mejor Cliente (por monto total comprado)
$mejor_cliente_res = $conexion->query("
    SELECT c.razon_social, SUM(p.total) AS total_comprado
    FROM pedidos p
    JOIN clientes c ON p.id_cliente = c.id
    WHERE p.estado_pedido = 'completado'
    GROUP BY p.id_cliente
    ORDER BY total_comprado DESC
    LIMIT 1
")->fetch_assoc();
$mejor_cliente = $mejor_cliente_res ? $mejor_cliente_res['razon_social'] : 'N/A';

// 4. Producto Más Vendido (por cantidad)
$producto_mas_vendido_res = $conexion->query("
    SELECT pr.nombre, SUM(pi.cantidad) AS total_cantidad
    FROM pedidos_items pi
    JOIN productos pr ON pi.id_producto = pr.id
    GROUP BY pi.id_producto
    ORDER BY total_cantidad DESC
    LIMIT 1
")->fetch_assoc();
$producto_mas_vendido = $producto_mas_vendido_res ? $producto_mas_vendido_res['nombre'] : 'N/A';

// 5. Alertas de Stock Bajo (stock_actual <= stock_minimo)
$stock_bajo_res = $conexion->query("SELECT id, nombre, stock_actual, stock_minimo FROM productos WHERE stock_actual <= stock_minimo AND stock_minimo > 0 ORDER BY stock_actual ASC");

// 6. Alertas de Stock Sobrante (stock_actual > 20)
$stock_sobrante_res = $conexion->query("SELECT id, nombre, stock_actual FROM productos WHERE stock_actual > 20 ORDER BY stock_actual DESC");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .stats-container {
            display: grid;
            /* CAMBIO: Forzamos 3 columnas (dando 2 filas para 6 tarjetas) */
            grid-template-columns: repeat(3, 1fr); 
            gap: 20px; /* Espacio entre tarjetas */
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: #fff;
            /* CAMBIO: Tarjetas más compactas */
            padding: 15px; 
            border-radius: 8px;
            /* CAMBIO: Sombra más sutil */
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
            display: flex;
            align-items: center;
        }
        .stat-card .icon {
            /* CAMBIO: Icono más pequeño */
            font-size: 1.8em; 
            margin-right: 15px;
            padding: 10px;
            /* CAMBIO: Caja del icono más pequeña */
            width: 48px; 
            height: 48px; 
            border-radius: 50%;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
        }
        .stat-card .icon.icon-clients { background-color: #17a2b8; }
        .stat-card .icon.icon-products { background-color: #ffc107; }
        .stat-card .icon.icon-sales { background-color: #28a745; }
        .stat-card .icon.icon-seller { background-color: #6f42c1; }
        .stat-card .icon.icon-customer { background-color: #fd7e14; }
        .stat-card .icon.icon-star { background-color: #007bff; }
        
        .stat-card .info {
            flex: 1;
            min-width: 0; 
        }
        .stat-card .info .title {
            /* CAMBIO: Título más pequeño y más grueso */
            font-size: 0.8em; 
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600; 
        }
        .stat-card .info .number {
            /* CAMBIO: Número más pequeño */
            font-size: 1.4em; 
            font-weight: 700;
            color: #333;
            word-wrap: break-word;
            line-height: 1.2;
        }

        /* Estilos generales (sin cambios) */
        .widgets-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .widget { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .alert-table { width: 100%; border-collapse: collapse; }
        .alert-table thead th { padding: 12px 15px; text-align: left; }
        .alert-table tbody td { padding: 12px 15px; border-top: 1px solid #ddd; }
        .alert-table thead.low-stock th { background-color: #f8d7da; color: #721c24; }
        .alert-table thead.high-stock th { background-color: #cce5ff; color: #004085; }
        .chart-container { position: relative; height: 350px; width: 100%; }
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
            <h1>Panel Principal</h1>

            <div class="stats-container">
                <div class="stat-card"><div class="icon icon-clients"><i class="fas fa-users"></i></div><div class="info"><span class="title">Total Clientes</span><span class="number"><?php echo $total_clientes; ?></span></div></div>
                <div class="stat-card"><div class="icon icon-products"><i class="fas fa-box-open"></i></div><div class="info"><span class="title">Productos Únicos</span><span class="number"><?php echo $total_productos; ?></span></div></div>
                <div class="stat-card"><div class="icon icon-sales"><i class="fas fa-dollar-sign"></i></div><div class="info"><span class="title">Ventas Completadas</span><span class="number">$<?php echo number_format($ventas_totales, 2, ',', '.'); ?></span></div></div>
                <div class="stat-card"><div class="icon icon-seller"><i class="fas fa-trophy"></i></div><div class="info"><span class="title">Mejor Vendedor</span><span class="number"><?php echo htmlspecialchars($mejor_vendedor); ?></span></div></div>
                <div class="stat-card"><div class="icon icon-customer"><i class="fas fa-gem"></i></div><div class="info"><span class="title">Mejor Cliente</span><span class="number"><?php echo htmlspecialchars($mejor_cliente); ?></span></div></div>
                <div class="stat-card"><div class="icon icon-star"><i class="fas fa-star"></i></div><div class="info"><span class="title">Producto Estrella</span><span class="number"><?php echo htmlspecialchars($producto_mas_vendido); ?></span></div></div>
            </div>

            <div class="widgets-grid">
                <div class="widget">
                    <h2>Ventas por Mes</h2>
                    <div class="chart-container"><canvas id="graficoVentas"></canvas></div>
                </div>
                <div class="widget">
                    <h2>Top 5 Productos Vendidos</h2>
                    <div class="chart-container"><canvas id="graficoProductos"></canvas></div>
                </div>
            </div>

            <div class="widgets-grid" style="margin-top: 30px;">
                <div class="widget">
                    <h2><i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i> Alertas de Stock Bajo</h2>
                    <?php if ($stock_bajo_res->num_rows > 0): ?>
                        <table class="alert-table">
                            <thead class="low-stock"><tr><th>Producto</th><th>Stock Actual</th><th>Mínimo</th></tr></thead>
                            <tbody>
                                <?php while($producto = $stock_bajo_res->fetch_assoc()): ?>
                                <tr><td><?php echo htmlspecialchars($producto['nombre']); ?></td><td><?php echo htmlspecialchars($producto['stock_actual']); ?></td><td><?php echo htmlspecialchars($producto['stock_minimo']); ?></td></tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?><p>¡Excelente! No hay productos con stock bajo.</p><?php endif; ?>
                </div>
                <div class="widget">
                    <h2><i class="fas fa-archive" style="color: #004085;"></i> Alertas de Stock Sobrante (> 20 U.)</h2>
                    <?php if ($stock_sobrante_res->num_rows > 0): ?>
                        <table class="alert-table">
                            <thead class="high-stock"><tr><th>Producto</th><th>Stock Actual</th></tr></thead>
                            <tbody>
                                <?php while($producto = $stock_sobrante_res->fetch_assoc()): ?>
                                <tr><td><?php echo htmlspecialchars($producto['nombre']); ?></td><td><?php echo htmlspecialchars($producto['stock_actual']); ?></td></tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?><p>No hay productos con exceso de stock.</p><?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dashboard_charts.js"></script>
</body>
</html>