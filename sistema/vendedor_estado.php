<?php
include 'verificar_sesion.php';
include 'conexion.php';

// --- VERIFICACIONES DE SEGURIDAD ---
if ($_SESSION['rol_usuario'] !== 'administrador') {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['accion'])) {
    header("Location: vendedores.php");
    exit();
}

$id_usuario = $_GET['id'];
$accion = $_GET['accion'];

// Un administrador no puede desactivarse a sí mismo
if ($id_usuario == $_SESSION['id_usuario']) {
    header("Location: vendedores.php?error=auto_accion");
    exit();
}

// --- LÓGICA DE ACTIVAR/DESACTIVAR ---

// Determinamos el nuevo estado (si la acción es 'desactivar', el nuevo estado es 0, si no, es 1)
$nuevo_estado = ($accion == 'desactivar') ? 0 : 1;

// Usamos una sentencia UPDATE en lugar de DELETE
$stmt = $conexion->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
$stmt->bind_param("ii", $nuevo_estado, $id_usuario);

if ($stmt->execute()) {
    // Si la actualización es exitosa, redirigimos a la lista de vendedores
    header("Location: vendedores.php?exito=estado_actualizado");
    exit();
} else {
    // Si hay un error, lo mostramos (en un entorno de producción, registraríamos el error)
    echo "Error al cambiar el estado del usuario: " . $stmt->error;
}

$stmt->close();
$conexion->close();
?>