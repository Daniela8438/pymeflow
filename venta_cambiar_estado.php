<?php
include 'verificar_sesion.php';
$pagina_activa = 'ventas';
include 'conexion.php';

if (!isset($_GET['id'])) {
    header("Location: historial_ventas.php");
    exit();
}

$id_pedido = $_GET['id'];

// CAMBIO: La consulta ahora también obtiene el 'estado_pago'
$stmt = $conexion->prepare("SELECT id, estado_pedido, estado_pago FROM pedidos WHERE id = ?");
$stmt->bind_param("i", $id_pedido);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    header("Location: historial_ventas.php");
    exit();
}

// Listas de posibles estados
$posibles_estados_pedido = ['Pendiente', 'En Proceso', 'Enviado', 'Completado', 'Cancelado'];
$posibles_estados_pago = ['Pendiente', 'Pagado', 'Cancelado'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Estado de Venta - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <style>
        .form-container { max-width: 600px; margin: auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .btn-submit { background-color: var(--color-primario); color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; }
        .btn-back { display: inline-block; margin-top: 15px; color: #6c757d; text-decoration: none; }
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
            <h1>Editar Estados de la Venta #<?php echo $id_pedido; ?></h1>
            <div class="form-container">
                <form action="guardar_estado_venta.php" method="POST">
                    <input type="hidden" name="id_pedido" value="<?php echo $id_pedido; ?>">
                    
                    <div class="form-group">
                        <label for="estado_pedido">Estado del Pedido</label>
                        <select name="estado_pedido" id="estado_pedido" required>
                            <?php foreach ($posibles_estados_pedido as $estado): ?>
                                <option value="<?php echo $estado; ?>" <?php if ($pedido['estado_pedido'] == $estado) echo 'selected'; ?>>
                                    <?php echo ucfirst($estado); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="estado_pago">Estado del Pago</label>
                        <select name="estado_pago" id="estado_pago" required>
                            <?php foreach ($posibles_estados_pago as $estado): ?>
                                <option value="<?php echo $estado; ?>" <?php if ($pedido['estado_pago'] == $estado) echo 'selected'; ?>>
                                    <?php echo ucfirst($estado); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">Actualizar Estados</button>
                </form>
                <a href="historial_ventas.php" class="btn-back">Cancelar y Volver al Historial</a>
            </div>
        </main>
    </div>
</body>
</html>