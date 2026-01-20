<?php
include 'verificar_sesion.php';
$pagina_activa = 'productos';
include 'conexion.php';

// --- LÓGICA DE BÚSQUEDA ---
$busqueda = '';
$query_base = "SELECT id, nombre, descripcion, precio_venta, stock_actual FROM productos";
$params = [];
$types = '';

if (isset($_GET['busqueda']) && !empty(trim($_GET['busqueda']))) {
    $busqueda = trim($_GET['busqueda']);
    $termino_busqueda = "%" . $busqueda . "%";
    $query_base .= " WHERE nombre LIKE ? OR descripcion LIKE ?";
    $params[] = $termino_busqueda;
    $params[] = $termino_busqueda;
    $types = "ss";
}
$query_base .= " ORDER BY nombre ASC";

$stmt = $conexion->prepare($query_base);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

// --- TARJETAS DE INFORMACIÓN (solo para admin) ---
if ($_SESSION['rol'] == 'administrador') {
    $total_stock_res = $conexion->query("SELECT SUM(stock_actual) AS total_stock FROM productos");
    $total_stock = $total_stock_res->fetch_assoc()['total_stock'] ?? 0;
    $total_tipos_productos = $conexion->query("SELECT COUNT(id) AS total FROM productos")->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .content-table { border-collapse: collapse; margin: 25px 0; font-size: 0.9em; width: 100%; border-radius: 8px 8px 0 0; overflow: hidden; box-shadow: 0 0 20px rgba(0, 0, 0, 0.15); }
        .content-table thead tr { background-color: var(--color-primario); color: #ffffff; text-align: left; font-weight: bold; }
        .content-table th, .content-table td { padding: 12px 15px; }
        .btn { padding: 8px 12px; border: none; border-radius: 5px; color: white !important; text-decoration: none; display: inline-block; }
        .btn-edit { background-color: #f0ad4e; }
        .btn-add { background-color: var(--color-secundario); color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 20px; }
        .search-container { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; }
        .search-container input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .search-container button { padding: 10px 15px; border: none; background-color: var(--color-primario); color: white; border-radius: 5px; cursor: pointer; }
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
            <div class="user-info">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></div>
            <a href="logout.php" class="logout-button">Cerrar Sesión</a>
        </header>
        <main class="content">
            <h1><?php echo ($_SESSION['rol'] == 'administrador') ? 'Gestión de Productos' : 'Consulta de Productos'; ?></h1>

            <?php if ($_SESSION['rol'] == 'administrador'): ?>
                <div class="info-cards-container">
                    <div class="info-card"><div class="number"><?php echo $total_tipos_productos; ?></div><div class="title">Productos Únicos</div></div>
                    <div class="info-card"><div class="number"><?php echo number_format($total_stock, 0, ',', '.'); ?></div><div class="title">Total Unidades en Stock</div></div>
                </div>
            <?php endif; ?>

            <div class="search-container">
                <form action="productos.php" method="GET" style="display: flex; flex-grow: 1; gap: 10px;">
                    <input type="text" name="busqueda" placeholder="Buscar por Nombre o Descripción..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Buscar</button>
                </form>
            </div>
            
            <?php if ($_SESSION['rol'] == 'administrador'): ?>
                <a href="producto_formulario.php" class="btn-add"><i class="fas fa-plus"></i> Agregar Nuevo Producto</a>
            <?php endif; ?>

            <table class="content-table">
                <thead>
                    <tr>
                        <th>Nombre</th><th>Descripción</th><th>Precio Venta</th><th>Stock</th>
                        <?php if ($_SESSION['rol'] == 'administrador'): ?>
                            <th>Acciones</th>
                        <?php endif; ?>
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
                                <?php if ($_SESSION['rol'] == 'administrador'): ?>
                                    <td><a href="producto_formulario.php?id=<?php echo $producto['id']; ?>" class="btn btn-edit" title="Editar"><i class="fas fa-edit"></i></a></td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="<?php echo ($_SESSION['rol'] == 'administrador') ? '5' : '4'; ?>">No se encontraron productos.</td></tr>
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