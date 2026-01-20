<?php
include 'verificar_sesion.php';
$pagina_activa = 'clientes';
include 'conexion.php';

$busqueda = '';

if (isset($_GET['busqueda']) && !empty(trim($_GET['busqueda']))) {
    $busqueda = trim($_GET['busqueda']);
    $termino_busqueda = "%" . $busqueda . "%"; 
    
    $stmt = $conexion->prepare("SELECT id, razon_social, cuit, email, telefono FROM clientes WHERE razon_social LIKE ? OR cuit LIKE ? OR email LIKE ? ORDER BY razon_social ASC");
    $stmt->bind_param("sss", $termino_busqueda, $termino_busqueda, $termino_busqueda);

} else {
    $stmt = $conexion->prepare("SELECT id, razon_social, cuit, email, telefono FROM clientes ORDER BY razon_social ASC");
}

$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .content-table { border-collapse: collapse; margin: 25px 0; font-size: 0.9em; width: 100%; border-radius: 8px 8px 0 0; overflow: hidden; box-shadow: 0 0 20px rgba(0, 0, 0, 0.15); }
        .content-table thead tr { background-color: var(--color-primario); color: #ffffff; text-align: left; font-weight: bold; }
        .content-table th, .content-table td { padding: 12px 15px; }
        .content-table tbody tr { border-bottom: 1px solid #dddddd; }
        .btn { padding: 8px 12px; border: none; border-radius: 5px; color: white !important; text-decoration: none; cursor: pointer; font-size: 0.9em; margin-right: 5px; display: inline-block; }
        .btn-edit { background-color: #f0ad4e; }
        .btn-history { background-color: #5bc0de; }
        .btn-add { background-color: var(--color-secundario); color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 20px; }
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
            <h1>Gestión de Clientes</h1>
            <div class="search-container">
                <form action="clientes.php" method="GET" style="display: flex; flex-grow: 1; gap: 10px;">
                    <input type="text" name="busqueda" placeholder="Buscar por Nombre, CUIT o Email..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Buscar</button>
                </form>
                <a href="clientes.php" class="btn-clear">Mostrar Todos</a>
            </div>
            <a href="cliente_formulario.php" class="btn-add"><i class="fas fa-plus"></i> Agregar Nuevo Cliente</a>
            <table class="content-table">
                <thead><tr><th>Razón Social</th><th>CUIT</th><th>Email</th><th>Teléfono</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while($cliente = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['razon_social']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cuit']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                <td>
                                    <a href="cliente_historial.php?id=<?php echo $cliente['id']; ?>" class="btn btn-history" title="Ver Historial"><i class="fas fa-history"></i></a>
                                    <a href="cliente_formulario.php?id=<?php echo $cliente['id']; ?>" class="btn btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                    </td>
                            </tr>
                        <?php endwhile; ?>
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