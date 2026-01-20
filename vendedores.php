<?php
include 'verificar_sesion.php';
$pagina_activa = 'vendedores';
include 'conexion.php';

// CAMBIO: Se reemplaza 'nombre_usuario' por 'usuario' en la consulta SQL.
$query = "SELECT id, nombre_completo, usuario, rol, estado FROM usuarios ORDER BY nombre_completo ASC";
$resultado = $conexion->query($query);

if ($resultado === false) {
    die("Error al consultar los vendedores: " . $conexion->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Vendedores - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .content-table { border-collapse: collapse; margin: 25px 0; font-size: 0.9em; width: 100%; border-radius: 8px 8px 0 0; overflow: hidden; box-shadow: 0 0 20px rgba(0, 0, 0, 0.15); }
        .content-table thead tr { background-color: var(--color-primario); color: #ffffff; text-align: left; font-weight: bold; }
        .content-table th, .content-table td { padding: 12px 15px; }
        .content-table tbody tr { border-bottom: 1px solid #dddddd; }
        .btn { padding: 8px 12px; border: none; border-radius: 5px; color: white !important; text-decoration: none; cursor: pointer; font-size: 0.9em; margin-right: 5px; display: inline-block; }
        .btn-edit { background-color: #f0ad4e; }
        .btn-add { background-color: var(--color-secundario); color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 20px; }
        .status-badge { padding: 5px 10px; border-radius: 15px; color: white; font-size: 0.8em; }
        .status-activo { background-color: #28a745; }
        .status-inactivo { background-color: #6c757d; }
        .btn-desactivar { background-color: #dc3545; }
        .btn-activar { background-color: #28a745; }
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
            <h1>Gestión de Vendedores</h1>
            <a href="vendedor_formulario.php" class="btn-add"><i class="fas fa-plus"></i> Agregar Nuevo Vendedor</a>
            <table class="content-table">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($usuario = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($usuario['rol'])); ?></td>
                            <td>
                                <?php if ($usuario['estado'] == 'activo'): ?>
                                    <span class="status-badge status-activo">Activo</span>
                                <?php else: ?>
                                    <span class="status-badge status-inactivo">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="vendedor_formulario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                <?php if ($usuario['estado'] == 'activo'): ?>
                                    <a href="vendedor_estado.php?id=<?php echo $usuario['id']; ?>&accion=desactivar" class="btn btn-desactivar" title="Desactivar" onclick="return confirm('¿Seguro que quieres desactivar a este vendedor?');"><i class="fas fa-user-slash"></i></a>
                                <?php else: ?>
                                    <a href="vendedor_estado.php?id=<?php echo $usuario['id']; ?>&accion=activar" class="btn btn-activar" title="Activar"><i class="fas fa-user-check"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>