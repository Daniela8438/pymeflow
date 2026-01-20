<?php
include 'verificar_sesion.php';
$pagina_activa = 'productos'; // VARIABLE PARA EL MENÚ ACTIVO
include 'conexion.php';

// CAMBIO: Nueva consulta para obtener el total de unidades en stock
$total_stock_res = $conexion->query("SELECT SUM(stock_actual) AS total_stock FROM productos");
$total_stock = $total_stock_res->fetch_assoc()['total_stock'] ?? 0;

$query = "SELECT id, nombre, descripcion, precio_venta, stock_actual FROM productos ORDER BY nombre ASC";
$resultado = $conexion->query($query);
$total_tipos_productos = $resultado->num_rows; // Obtenemos el total de productos únicos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .content-table { border-collapse: collapse; margin: 25px 0; font-size: 0.9em; width: 100%; border-radius: 8px 8px 0 0; overflow: hidden; box-shadow: 0 0 20px rgba(0, 0, 0, 0.15); }
        .content-table thead tr { background-color: var(--color-primario); color: #ffffff; text-align: left; font-weight: bold; }
        .content-table th, .content-table td { padding: 12px 15px; }
        .content-table tbody tr { border-bottom: 1px solid #dddddd; }
        .content-table tbody tr:nth-of-type(even) { background-color: #f3f3f3; }
        .content-table tbody tr:last-of-type { border-bottom: 2px solid var(--color-primario); }
        .btn { padding: 8px 12px; border: none; border-radius: 5px; color: white !important; text-decoration: none; cursor: pointer; font-size: 0.9em; margin-right: 5px; display: inline-block; }
        .btn-edit { background-color: #f0ad4e; }
        .btn-add { background-color: var(--color-secundario); color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 20px; }
        /* CAMBIO: Estilos para las nuevas tarjetas de información */
        .info-cards-container { display: flex; gap: 20px; margin-bottom: 20px; }
        .info-card { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); flex-grow: 1; text-align: center; }
        .info-card .number { font-size: 2em; font-weight: 700; color: var(--color-primario); }
        .info-card .title { font-size: 0.9em; color: #6c757d; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <header class="dashboard-header">
            <div class="user-info">
                Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>
            </div>
            <a href="logout.php" class="logout-button">Cerrar Sesión</a>
        </header>

        <main class="content">
            <h1>Gestión de Productos</h1>

            <div class="info-cards-container">
                <div class="info-card">
                    <div class="number"><?php echo $total_tipos_productos; ?></div>
                    <div class="title">Productos Únicos</div>
                </div>
                <div class="info-card">
                    <div class="number"><?php echo number_format($total_stock, 0, ',', '.'); ?></div>
                    <div class="title">Total de Unidades en Stock</div>
                </div>
            </div>

            <a href="producto_formulario.php" class="btn-add"><i class="fas fa-plus"></i> Agregar Nuevo Producto</a>
            <table class="content-table">
                <thead>
                    <tr>
                        <th>Nombre</th><th>Descripción</th><th>Precio Venta</th><th>Stock</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while($producto = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                                <td>$<?php echo number_format($producto['precio_venta'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($producto['stock_actual']); ?></td>
                                <td>
                                    <a href="producto_formulario.php?id=<?php echo $producto['id']; ?>" class="btn btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                    </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No hay productos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

</body>
</html>
<?php $conexion->close(); ?>