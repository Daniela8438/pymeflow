<?php
include 'verificar_sesion.php';
$pagina_activa = 'ventas';
include 'conexion.php';

if (!isset($_GET['id'])) {
    header("Location: ventas.php");
    exit();
}
$id_pedido = $_GET['id'];

$stmt = $conexion->prepare("SELECT id, estado_pedido, estado_pago FROM pedidos WHERE id = ?");
$stmt->bind_param("i", $id_pedido);
$stmt->execute();
$resultado = $stmt->get_result();
$pedido = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Estado de Venta - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .form-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .form-buttons { margin-top: 20px; text-align: right; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; color: white; text-decoration: none; cursor: pointer; font-size: 1em; }
        .btn-save { background-color: var(--color-secundario); }
        .btn-cancel { background-color: #6c757d; }
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
            <h1>Editar Estado de Venta #<?php echo $id_pedido; ?></h1>
            <div class="form-container">
                <form action="venta_actualizar_estado.php" method="POST">
                    <input type="hidden" name="id_pedido" value="<?php echo $id_pedido; ?>">
                    <div class="form-group">
                        <label for="estado_pedido">Estado del Pedido</label>
                        <select id="estado_pedido" name="estado_pedido" required>
                            <option value="en_preparacion" <?php if($pedido['estado_pedido'] == 'en_preparacion') echo 'selected'; ?>>En Preparación</option>
                            <option value="completado" <?php if($pedido['estado_pedido'] == 'completado') echo 'selected'; ?>>Terminado / Entregado</option>
                            <option value="cancelado" <?php if($pedido['estado_pedido'] == 'cancelado') echo 'selected'; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="estado_pago">Estado del Pago</label>
                        <select id="estado_pago" name="estado_pago" required>
                            <option value="pendiente" <?php if($pedido['estado_pago'] == 'pendiente') echo 'selected'; ?>>Pendiente</option>
                            <option value="parcial" <?php if($pedido['estado_pago'] == 'parcial') echo 'selected'; ?>>Parcial</option>
                            <option value="pagado" <?php if($pedido['estado_pago'] == 'pagado') echo 'selected'; ?>>Pagado</option>
                            <option value="cancelado" <?php if($pedido['estado_pago'] == 'cancelado') echo 'selected'; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <a href="ventas.php" class="btn btn-cancel">Cancelar</a>
                        <button type="submit" class="btn btn-save">Actualizar Estados</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>