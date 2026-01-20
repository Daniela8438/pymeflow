<?php
include 'verificar_sesion.php';
$pagina_activa = 'productos'; // VARIABLE PARA EL MENÚ ACTIVO
include 'conexion.php';

// ... (El resto del código PHP de este archivo no cambia) ...
$producto = [ 'id' => '', 'nombre' => '', 'descripcion' => '', 'precio_venta' => '', 'precio_costo' => '', 'stock_actual' => '', 'stock_minimo' => '', 'foto_url' => '' ];
$titulo_pagina = "Agregar Nuevo Producto";
if (isset($_GET['id'])) {
    $titulo_pagina = "Editar Producto";
    $id_producto = $_GET['id'];
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows > 0) { $producto = $resultado->fetch_assoc(); }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .form-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .form-buttons { margin-top: 20px; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; color: white; text-decoration: none; cursor: pointer; font-size: 1em; }
        .btn-save { background-color: var(--color-secundario); }
        .btn-cancel { background-color: #6c757d; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; // INCLUIMOS EL MENÚ LATERAL REUTILIZABLE ?>

    <div class="main-content">
        <header class="dashboard-header">
            <div class="user-info">
                Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>
            </div>
            <a href="logout.php" class="logout-button">Cerrar Sesión</a>
        </header>

        <main class="content">
            <h1><?php echo $titulo_pagina; ?></h1>
            <div class="form-container">
                <form action="producto_guardar.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($producto['id']); ?>">
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="precio_venta">Precio de Venta</label>
                        <input type="number" step="0.01" id="precio_venta" name="precio_venta" value="<?php echo htmlspecialchars($producto['precio_venta']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="precio_costo">Precio de Costo</label>
                        <input type="number" step="0.01" id="precio_costo" name="precio_costo" value="<?php echo htmlspecialchars($producto['precio_costo']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="stock_actual">Stock Actual</label>
                        <input type="number" id="stock_actual" name="stock_actual" value="<?php echo htmlspecialchars($producto['stock_actual']); ?>" required>
                    </div>
                     <div class="form-group">
                        <label for="stock_minimo">Stock Mínimo</label>
                        <input type="number" id="stock_minimo" name="stock_minimo" value="<?php echo htmlspecialchars($producto['stock_minimo']); ?>" required>
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-save">Guardar Producto</button>
                        <a href="productos.php" class="btn btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>