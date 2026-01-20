<?php
include 'verificar_sesion.php';
$pagina_activa = 'facturas'; // Para que se marque en el menú lateral
include 'conexion.php';

$busqueda = '';
$sql = "SELECT p.id, p.fecha_creacion, c.razon_social, u.nombre_completo AS vendedor, p.total, p.estado_pedido 
        FROM pedidos p 
        JOIN clientes c ON p.id_cliente = c.id
        JOIN usuarios u ON p.id_vendedor = u.id"; // Unimos con usuarios para obtener el nombre del vendedor

if (isset($_GET['busqueda']) && !empty(trim($_GET['busqueda']))) {
    $busqueda = trim($_GET['busqueda']);
    $termino_busqueda = "%" . $busqueda . "%"; 
    
    // Buscamos por N° de factura, cliente o vendedor
    $sql .= " WHERE c.razon_social LIKE ? OR p.id LIKE ? OR u.nombre_completo LIKE ?";
    $stmt = $conexion->prepare($sql . " ORDER BY p.fecha_creacion DESC");
    $stmt->bind_param("sss", $termino_busqueda, $termino_busqueda, $termino_busqueda);

} else {
    $stmt = $conexion->prepare($sql . " ORDER BY p.fecha_creacion DESC");
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
        .content-table tbody tr { border-bottom: 1px solid #dddddd; }
        
        .btn-accion { 
            color: white !important; 
            padding: 8px 12px; 
            border-radius: 5px; 
            text-decoration: none; 
            margin-right: 5px; 
            display: inline-block;
        }
        .btn-ver { background-color: #5bc0de; } /* Azul info */
        .btn-pdf { background-color: #d9534f; } /* Rojo PDF */
        
        .search-container { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; }
        .search-container input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .search-container button { padding: 10px 15px; border: none; background-color: var(--color-primario); color: white; border-radius: 5px; cursor: pointer; }
        .search-container .btn-clear { background-color: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
        
        .estado { padding: 5px 10px; border-radius: 15px; color: #fff; font-weight: bold; font-size: 0.85em; text-align: center; }
        .estado.pendiente { background-color: #f0ad4e; }
        .estado.completado { background-color: #5cb85c; }
        .estado.cancelado { background-color: #d9534f; }
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
                <form action="facturas.php" method="GET" style="display: flex; flex-grow: 1; gap: 10px;">
                    <input type="text" name="busqueda" placeholder="Buscar por N° Venta, Cliente o Vendedor..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Buscar</button>
                </form>
                <a href="facturas.php" class="btn-clear">Mostrar Todos</a>
            </div>
            
            <table class="content-table">
                <thead>
                    <tr>
                        <th>N° Venta</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while($factura = $resultado->fetch_assoc()): ?>
                            <?php
                                // --- INICIO DE LA CORRECCIÓN ---
                                // 1. Obtenemos el estado. Si está vacío o es NULL, lo forzamos a 'pendiente'.
                                $estado_texto = !empty($factura['estado_pedido']) ? $factura['estado_pedido'] : 'pendiente';
                                
                                // 2. Limpiamos el texto y lo preparamos para la clase CSS
                                $clase_estado = strtolower(htmlspecialchars($estado_texto));
                                
                                // 3. Preparamos el texto para mostrarlo (primera letra en mayúscula)
                                $estado_mostrar = htmlspecialchars(ucfirst($estado_texto));
                                // --- FIN DE LA CORRECCIÓN ---
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($factura['id']); ?></td>
                                <td><?php echo date("d/m/Y", strtotime($factura['fecha_creacion'])); ?></td>
                                <td><?php echo htmlspecialchars($factura['razon_social']); ?></td>
                                <td><?php echo htmlspecialchars($factura['vendedor']); ?></td>
                                <td>$<?php echo number_format($factura['total'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="estado <?php echo $clase_estado; ?>">
                                        <?php echo $estado_mostrar; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="generar_factura.php?id=<?php echo $factura['id']; ?>" class="btn-accion btn-ver" title="Ver" target="_blank"><i class="fas fa-eye"></i></a>
                                    <a href="generar_factura.php?id=<?php echo $factura['id']; ?>&modo=descargar" class="btn-accion btn-pdf" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No se encontraron ventas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
<?php
$stmt->close();
$conexion->close();
?>