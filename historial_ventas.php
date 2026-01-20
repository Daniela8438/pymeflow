<?php
include 'verificar_sesion.php';
$pagina_activa = 'ventas';
include 'conexion.php';

// (El resto de tu código PHP para la búsqueda se mantiene igual)
$busqueda = '';
$base_query = "SELECT p.id, p.fecha_creacion, c.razon_social AS nombre_cliente, u.nombre_completo AS nombre_vendedor, p.total, p.estado_pedido, p.estado_pago 
               FROM pedidos p
               JOIN clientes c ON p.id_cliente = c.id
               JOIN usuarios u ON p.id_vendedor = u.id";
$params = [];
$types = "";
if (isset($_GET['busqueda']) && !empty(trim($_GET['busqueda']))) {
    $busqueda = trim($_GET['busqueda']);
    $termino_busqueda = "%" . $busqueda . "%";
    $base_query .= " WHERE c.razon_social LIKE ? OR p.id = ?";
    $params[] = $termino_busqueda;
    $params[] = $busqueda;
    $types = "ss";
}
$base_query .= " ORDER BY p.fecha_creacion DESC";
$stmt = $conexion->prepare($base_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Ventas - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .content-table { border-collapse: collapse; margin: 25px 0; font-size: 0.9em; width: 100%; border-radius: 8px 8px 0 0; overflow: hidden; box-shadow: 0 0 20px rgba(0, 0, 0, 0.15); }
        .content-table thead tr { background-color: var(--color-primario); color: #ffffff; text-align: left; font-weight: bold; }
        .content-table th, .content-table td { padding: 12px 15px; }
        .btn-add { background-color: var(--color-secundario); color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 20px; }
        
        /* CAMBIO: Estilos para todas las etiquetas de estado */
        .status-badge { padding: 5px 10px; border-radius: 15px; color: white; font-size: 0.8em; font-weight: bold; text-align: center; }
        .status-pagado { background-color: #28a745; }
        .status-pendiente { background-color: #ffc107; color: #333; }
        .status-cancelado { background-color: #dc3545; }
        .status-completado { background-color: #007bff; }
        .status-en-proceso { background-color: #ffc107; color: #333; }
        .status-enviado { background-color: #17a2b8; }

        .btn { padding: 8px 12px; border-radius: 5px; color: white !important; text-decoration: none; font-size: 0.9em; margin-right: 5px; display: inline-block; }
        .btn-info { background-color: #17a2b8; }
        .btn-success { background-color: #28a745; }
        .btn-edit { background-color: #f0ad4e; }
        .search-container { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; }
        .search-container input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .search-container button { padding: 10px 15px; border: none; background-color: var(--color-primario); color: white; border-radius: 5px; cursor: pointer; }
        .search-container .btn-clear { background-color: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
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
            <h1>Historial de Ventas</h1>
            <div class="search-container">
                <form action="historial_ventas.php" method="GET" style="display: flex; flex-grow: 1; gap: 10px;">
                    <input type="text" name="busqueda" placeholder="Buscar por N° de Venta o Cliente..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Buscar</button>
                </form>
                <a href="historial_ventas.php" class="btn-clear">Mostrar Todas</a>
            </div>
            <a href="venta_formulario.php" class="btn-add"><i class="fas fa-plus"></i> Registrar Nueva Venta</a>
            
            <table class="content-table">
                <thead><tr><th>N° Venta</th><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th>Total</th><th>Estado Pedido</th><th>Estado Pago</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while($venta = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $venta['id']; ?></td>
                                <td><?php echo date("d/m/Y", strtotime($venta['fecha_creacion'])); ?></td>
                                <td><?php echo htmlspecialchars($venta['nombre_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($venta['nombre_vendedor']); ?></td>
                                <td>$<?php echo number_format($venta['total'], 2, ',', '.'); ?></td>
                                
                                <td>
                                    <?php
                                    $estado_pedido = strtolower($venta['estado_pedido']);
                                    $clase_pedido = 'status-pendiente'; // Clase por defecto
                                    if ($estado_pedido == 'completado') $clase_pedido = 'status-completado';
                                    if ($estado_pedido == 'en proceso') $clase_pedido = 'status-en-proceso';
                                    if ($estado_pedido == 'enviado') $clase_pedido = 'status-enviado';
                                    if ($estado_pedido == 'cancelado') $clase_pedido = 'status-cancelado';
                                    ?>
                                    <span class="status-badge <?php echo $clase_pedido; ?>"><?php echo htmlspecialchars(ucfirst($venta['estado_pedido'])); ?></span>
                                </td>

                                <td>
                                    <?php
                                    $estado_pago = strtolower($venta['estado_pago']);
                                    $clase_pago = 'status-pendiente'; // Clase por defecto
                                    if ($estado_pago == 'pagado') $clase_pago = 'status-pagado';
                                    if ($estado_pago == 'cancelado') $clase_pago = 'status-cancelado';
                                    ?>
                                    <span class="status-badge <?php echo $clase_pago; ?>"><?php echo htmlspecialchars(ucfirst($venta['estado_pago'])); ?></span>
                                </td>

                                <td>
                                    <a href="venta_detalle.php?id=<?php echo $venta['id']; ?>" title="Ver Detalle" class="btn btn-info"><i class="fas fa-eye"></i></a>
                                    <a href="venta_cambiar_estado.php?id=<?php echo $venta['id']; ?>" title="Editar Estados" class="btn btn-edit"><i class="fas fa-edit"></i></a>
                                    <a href="generar_factura.php?id=<?php echo $venta['id']; ?>" title="Generar Factura" class="btn btn-success" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8">No se encontraron ventas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>