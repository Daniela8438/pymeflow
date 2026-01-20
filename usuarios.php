<?php
include 'verificar_sesion.php';
// Solo los administradores pueden gestionar usuarios
if ($_SESSION['rol'] !== 'administrador') {
    header('Location: dashboard.php'); // O a la página principal
    exit();
}
$pagina_activa = 'usuarios';
include 'conexion.php';

$query = "SELECT id, nombre_completo, email, usuario, rol, estado FROM usuarios ORDER BY nombre_completo ASC";
$resultado = $conexion->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - PymeFlow</title>
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
            <h1>Gestión de Usuarios</h1>
            <a href="usuario_formulario.php" class="btn-add"><i class="fas fa-plus"></i> Agregar Nuevo Usuario</a>
            <table class="content-table">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while($usuario = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($usuario['rol'])); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($usuario['estado'])); ?></td>
                                <td>
                                    <a href="usuario_formulario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
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
<?php $conexion->close(); ?>