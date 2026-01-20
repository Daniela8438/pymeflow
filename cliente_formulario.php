<?php
include 'verificar_sesion.php';
$pagina_activa = 'clientes'; // Para que el menú lateral se marque como activo
include 'conexion.php';

$error_message = '';
$cliente = [
    'id' => '',
    'razon_social' => '',
    'cuit' => '',
    'condicion_iva' => 'Consumidor Final', // Valor por defecto
    'email' => '',
    'telefono' => '',
    'direccion' => ''
];
$titulo_pagina = "Agregar Nuevo Cliente";

// --- LÓGICA 1: CARGAR DATOS PARA EDITAR (SI VIENE UN ID POR GET) ---
if (isset($_GET['id'])) {
    $titulo_pagina = "Editar Cliente";
    $id_cliente = $_GET['id'];
    
    $stmt_fetch = $conexion->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt_fetch->bind_param("i", $id_cliente);
    $stmt_fetch->execute();
    $resultado = $stmt_fetch->get_result();
    
    if ($resultado->num_rows > 0) {
        $cliente = $resultado->fetch_assoc();
    }
    $stmt_fetch->close();
}

// --- LÓGICA 2: GUARDAR DATOS (SI VIENE POR POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibimos los datos del formulario
    $id = $_POST['id'];
    $razon_social = trim($_POST['razon_social']);
    $cuit = trim($_POST['cuit']);
    $condicion_iva = trim($_POST['condicion_iva']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    // Volvemos a llenar el array 'cliente' para que los campos no se borren si hay un error
    $cliente = compact('id', 'razon_social', 'cuit', 'condicion_iva', 'email', 'telefono', 'direccion');

    // Validación simple
    if (empty($razon_social) || empty($cuit) || empty($condicion_iva) || empty($email) || empty($telefono) || empty($direccion)) {
        $error_message = 'Todos los campos son obligatorios.';
    } else {
        // Si la validación pasa, intentamos guardar en la BD
        try {
            if (!empty($id)) {
                // Es una ACTUALIZACIÓN (UPDATE)
                $stmt = $conexion->prepare("UPDATE clientes SET razon_social = ?, cuit = ?, condicion_iva = ?, email = ?, telefono = ?, direccion = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $razon_social, $cuit, $condicion_iva, $email, $telefono, $direccion, $id);
            } else {
                // Es uno NUEVO (INSERT)
                $stmt = $conexion->prepare("INSERT INTO clientes (razon_social, cuit, condicion_iva, email, telefono, direccion) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $razon_social, $cuit, $condicion_iva, $email, $telefono, $direccion);
            }
            
            $stmt->execute();
            $stmt->close();
            
            // Si todo salió bien, redirigimos a la lista de clientes
            header("Location: clientes.php");
            exit();

        } catch (mysqli_sql_exception $e) {
            // Capturamos cualquier error de la base de datos
            if ($e->getCode() === 1062) {
                // Error 1062 = Entrada duplicada (generalmente por el CUIT)
                $error_message = 'Error: Ya existe un cliente con ese CUIT.';
            } else {
                // Mostramos cualquier otro error
                $error_message = 'Error al guardar el cliente: ' . $e->getMessage();
            }
        }
    }
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
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .form-buttons { margin-top: 20px; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; color: white !important; text-decoration: none; cursor: pointer; font-size: 1em; }
        .btn-save { background-color: var(--color-secundario); }
        .btn-cancel { background-color: #6c757d; }
        .error-message { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 15px; text-align: center; }
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
            <h1><?php echo $titulo_pagina; ?></h1>
            <div class="form-container">
                
                <?php if (!empty($error_message)) { echo '<div class="error-message">' . htmlspecialchars($error_message) . '</div>'; } ?>
                
                <form action="cliente_formulario.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($cliente['id']); ?>">
                    
                    <div class="form-group">
                        <label for="razon_social">Razón Social o Apellido y Nombre</label>
                        <input type="text" id="razon_social" name="razon_social" value="<?php echo htmlspecialchars($cliente['razon_social']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cuit">CUIT</label>
                        <input type="text" id="cuit" name="cuit" value="<?php echo htmlspecialchars($cliente['cuit']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="condicion_iva">Condición frente al IVA</label>
                        <select id="condicion_iva" name="condicion_iva" required>
                            <option value="Responsable Inscripto" <?php echo ($cliente['condicion_iva'] == 'Responsable Inscripto') ? 'selected' : ''; ?>>
                                Responsable Inscripto
                            </option>
                            <option value="Monotributista" <?php echo ($cliente['condicion_iva'] == 'Monotributista') ? 'selected' : ''; ?>>
                                Monotributista
                            </option>
                            <option value="Consumidor Final" <?php echo ($cliente['condicion_iva'] == 'Consumidor Final') ? 'selected' : ''; ?>>
                                Consumidor Final
                            </option>
                            <option value="Exento" <?php echo ($cliente['condicion_iva'] == 'Exento') ? 'selected' : ''; ?>>
                                Exento
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <textarea id="direccion" name="direccion" rows="3" required><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-save">Guardar</button>
                        <a href="clientes.php" class="btn btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>