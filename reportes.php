<?php
include 'verificar_sesion.php';
$pagina_activa = 'reportes';
include 'conexion.php';

// Solo administradores pueden acceder
if ($_SESSION['rol_usuario'] !== 'administrador') {
    header("Location: dashboard.php");
    exit();
}

$ventas = [];
$total_ventas = 0;
$fecha_inicio = '';
$fecha_fin = '';

// Si el formulario fue enviado, procesamos la búsqueda
if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])) {
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];

    // Aseguramos que las fechas sean válidas
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        // Agregamos la hora al final del día para incluir todas las ventas de esa fecha
        $fecha_fin_completa = $fecha_fin . ' 23:59:59';

        $query = "SELECT p.id, p.fecha_creacion, c.razon_social AS nombre_cliente, u.nombre_completo AS nombre_vendedor, p.total
                  FROM pedidos p
                  JOIN clientes c ON p.id_cliente = c.id
                  JOIN usuarios u ON p.id_vendedor = u.id
                  WHERE p.fecha_creacion BETWEEN ? AND ?
                  ORDER BY p.fecha_creacion DESC";
        
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin_completa);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        while ($venta = $resultado->fetch_assoc()) {
            $ventas[] = $venta;
            $total_ventas += $venta['total'];
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .form-container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; display: flex; gap: 20px; align-items: flex-end; }
        .form-group { flex-grow: 1; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; color: white; text-decoration: none; cursor: pointer; font-size: 1em; }
        .btn-generate { background-color: var(--color-secundario); }
        .content-table { border-collapse: collapse; margin: 25px 0; font-size: 0.9em; width: 100%; box-shadow: 0 0 20px rgba(0, 0, 0, 0.15); }
        .content-table thead tr { background-color: var(--color-primario); color: #ffffff; text-align: left; font-weight: bold; }
        .content-table th, .content-table td { padding: 12px 15px; border-bottom: 1px solid #ddd; }
        .total-row td { font-weight: bold; font-size: 1.2em; border-top: 2px solid var(--color-primario); }
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
            <h1>Reporte de Ventas por Fecha</h1>

            <div class="form-container">
                <form action="reportes.php" method="GET" style="display: flex; width: 100%; gap: 20px; align-items: flex-end;">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin">Fecha de Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-generate">Generar Reporte</button>
                </form>
            </div>

            <?php if (isset($_GET['fecha_inicio'])): ?>
            <div class="widget">
                <h2>Resultados del Período</h2>
                <table class="content-table">
                    <thead>
                        <tr><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th style="text-align: right;">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($ventas) > 0): ?>
                            <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?php echo date("d/m/Y H:i", strtotime($venta['fecha_creacion'])); ?></td>
                                <td><?php echo htmlspecialchars($venta['nombre_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($venta['nombre_vendedor']); ?></td>
                                <td style="text-align: right;">$<?php echo number_format($venta['total'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="3" style="text-align: right;">Total del Período:</td>
                                <td style="text-align: right;">$<?php echo number_format($total_ventas, 2, ',', '.'); ?></td>
                            </tr>
                        <?php else: ?>
                            <tr><td colspan="4">No se encontraron ventas en el período seleccionado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>