<?php
include 'verificar_sesion.php';
$pagina_activa = 'dashboard';
include 'conexion.php';

// --- CONSULTAS PARA OBTENER LAS ESTADÍSTICAS ---

// 1. Tarjetas Principales
$total_clientes = $conexion->query("SELECT COUNT(id) AS total FROM clientes")->fetch_assoc()['total'];
$total_productos = $conexion->query("SELECT COUNT(id) AS total FROM productos")->fetch_assoc()['total'];
$ventas_totales_res = $conexion->query("SELECT SUM(total) AS total FROM pedidos")->fetch_assoc()['total'];
$ventas_totales = $ventas_totales_res ?? 0;

// 2. Mejor Vendedor (por monto total vendido)
$mejor_vendedor_res = $conexion->query("
    SELECT u.nombre_completo, SUM(p.total) AS total_vendido
    FROM pedidos p
    JOIN usuarios u ON p.id_vendedor = u.id
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

// 5. Alertas de Stock Bajo
$stock_bajo_res = $conexion->query("SELECT id, nombre, stock_actual, stock_minimo FROM productos WHERE stock_actual <= stock_minimo ORDER BY stock_actual ASC");

// 6. Alertas de Stock Excedente (ej: stock actual es 5 veces mayor que el mínimo)
$multiplicador_stock_alto = 5;
$stock_alto_res = $conexion->query("SELECT id, nombre, stock_actual, stock_minimo FROM productos WHERE stock_actual >= (stock_minimo * $multiplicador_stock_alto) AND stock_minimo > 0 ORDER BY stock_actual DESC");

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
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); display: flex; align-items: center; }
        .stat-card .icon { font-size: 2.5em; margin-right: 20px; padding: 15px; border-radius: 50%; color: #fff; width: 65px; height: 65px; display: flex; justify-content: center; align-items: center; }
        .stat-card .icon.icon-clients { background-color: #17a2b8; }
        .stat-card .icon.icon-products { background-color: #ffc107; }
        .stat-card .icon.icon-sales { background-color: #28a745; }
        .stat-card .icon.icon-seller { background-color: #6f42c1; } /* Nuevo color */
        .stat-card .icon.icon-customer { background-color: #fd7e14; } /* Nuevo color */
        .stat-card .icon.icon-star { background-color: #007bff; } /* Nuevo color */
        .stat-card .info .title { font-size: 0.9em; color: #6c757d; text-transform: uppercase; }
        .stat-card .info .number { font-size: 1.8em; font-weight: 700; color: #333; }
        .widgets-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .widget { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .alert-table { width: 100%; border-collapse: collapse; }
        .alert-table thead th { padding: 12px 15px; text-align: left; }
        .alert-table tbody td { padding: 12px 15px; border-top: 1px solid #ddd; }
        .alert-table thead.low-stock th { background-color: #f8d7da; color: #721c24; }
        .alert-table thead.high-stock th { background-color: #cce5ff; color: #004085; } /* Nuevo color */
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
                <div class="stat-card">
                    <div class="icon icon-clients"><i class="fas fa-users"></i></div>
                    <div class="info"><span class="title">Total de Clientes</span><span class="number"><?php echo $total_clientes; ?></span></div>
                </div>
                <div class="stat-card">
                    <div class="icon icon-products"><i class="fas fa-box-open"></i></div>
                    <div class="info"><span class="title">Cantidad de Productos</span><span class="number"><?php echo $total_productos; ?></span></div>
                </div>
                <div class="stat-card">
                    <div class="icon icon-sales"><i class="fas fa-dollar-sign"></i></div>
                    <div class="info"><span class="title">Ventas Totales</span><span class="number">$<?php echo number_format($ventas_totales, 2, ',', '.'); ?></span></div>
                </div>
                <div class="stat-card">
                    <div class="icon icon-seller"><i class="fas fa-trophy"></i></div>
                    <div class="info"><span class="title">Mejor Vendedor</span><span class="number"><?php echo htmlspecialchars($mejor_vendedor); ?></span></div>
                </div>
                <div class="stat-card">
                    <div class="icon icon-customer"><i class="fas fa-gem"></i></div>
                    <div class="info"><span class="title">Mejor Cliente</span><span class="number"><?php echo htmlspecialchars($mejor_cliente); ?></span></div>
                </div>
                <div class="stat-card">
                    <div class="icon icon-star"><i class="fas fa-star"></i></div>
                    <div class="info"><span class="title">Producto Estrella</span><span class="number"><?php echo htmlspecialchars($producto_mas_vendido); ?></span></div>
                </div>
            </div>

            <div class="widgets-grid">
                <div class="widget">
                    <h2>Ventas por Mes</h2>
                    <canvas id="graficoVentas"></canvas>
                </div>
                <div class="widget">
                    <h2>Top 5 Productos Vendidos</h2>
                    <canvas id="graficoProductos"></canvas>
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
                    <h2><i class="fas fa-check-circle" style="color: #007bff;"></i> Alertas de Stock Excedente</h2>
                    <?php if ($stock_alto_res->num_rows > 0): ?>
                        <table class="alert-table">
                            <thead class="high-stock"><tr><th>Producto</th><th>Stock Actual</th><th>Mínimo</th></tr></thead>
                            <tbody>
                                <?php while($producto = $stock_alto_res->fetch_assoc()): ?>
                                <tr><td><?php echo htmlspecialchars($producto['nombre']); ?></td><td><?php echo htmlspecialchars($producto['stock_actual']); ?></td><td><?php echo htmlspecialchars($producto['stock_minimo']); ?></td></tr>
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