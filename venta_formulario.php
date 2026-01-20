<?php
include 'verificar_sesion.php';

// --- ¡NUEVA VALIDACIÓN DE ROL! ---
// Si el usuario NO es un vendedor, lo sacamos de esta página.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'vendedor') {
    // Lo redirigimos a una página que sí pueda ver
    // (el dashboard para el admin, o al login si algo salió mal)
    header('Location: dashboard.php'); 
    exit();
}
// --- FIN DE LA VALIDACIÓN ---

$pagina_activa = 'ventas'; // Para el menú
include 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Venta - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .form-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .grid-2 { display: grid; grid-template-columns: 3fr 1fr; gap: 20px; }
        #resultados_cliente, #resultados_producto { border: 1px solid #ccc; border-radius: 5px; max-height: 150px; overflow-y: auto; background: #fafafa; }
        #resultados_cliente div, #resultados_producto div { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; }
        #resultados_cliente div:hover, #resultados_producto div:hover { background-color: #f0f0f0; }
        #resultados_cliente div:last-child, #resultados_producto div:last-child { border-bottom: none; }
        .content-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .content-table th, .content-table td { padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; }
        .content-table thead tr { background-color: var(--color-primario); color: #fff; }
        .content-table td:nth-child(2), .content-table td:nth-child(3), .content-table td:nth-child(4) { text-align: right; }
        .content-table th:nth-child(2), .content-table th:nth-child(3), .content-table th:nth-child(4) { text-align: right; }
        .total-section { margin-top: 20px; text-align: right; font-size: 1.5em; font-weight: bold; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; color: white !important; text-decoration: none; cursor: pointer; }
        .btn-save { background-color: var(--color-secundario); }
        .btn-delete { background-color: #d9534f; }
        .btn-add { background-color: #0a9396; }
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
            <h1>Nueva Venta</h1>
            
            <form id="formVenta" action="guardar_venta.php" method="POST" class="form-container">
                
                <div class="form-group">
                    <label for="cliente_busqueda">Buscar Cliente (por Razón Social o CUIT)</label>
                    <input type="text" id="cliente_busqueda" placeholder="Escribe para buscar..." autocomplete="off">
                    <div id="resultados_cliente"></div>
                    <input type="hidden" id="id_cliente" name="id_cliente" required>
                </div>
                
                <hr style="margin: 30px 0;">

                <h3>Agregar Productos</h3>
                <div class="grid-2">
                    <div class="form-group">
                        <label for="producto_busqueda">Buscar Producto</label>
                        <input type="text" id="producto_busqueda" placeholder="Buscar por nombre..." autocomplete="off">
                        <div id="resultados_producto"></div>
                    </div>
                    <div class="form-group">
                        <label for="cantidad_producto">Cantidad</label>
                        <input type="number" id="cantidad_producto" value="1" min="1">
                        <button type="button" id="btnAgregarProducto" class="btn btn-add" style="margin-top: 10px; width: 100%;">Agregar</button>
                    </div>
                </div>

                <h3>Productos en la Venta</h3>
                <table class="content-table">
                    <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th><th>Acción</th></tr></thead>
                    <tbody id="carrito_tbody">
                        </tbody>
                </table>
                
                <div class="total-section">
                    Total: $<span id="totalVenta">0.00</span>
                    <input type="hidden" id="total_venta_hidden" name="total_venta">
                </div>

                <input type="hidden" name="items_venta" id="items_venta">
                
                <div style="text-align: right; margin-top: 30px;">
                    <button type="submit" class="btn btn-save" style="font-size: 1.1em;">Finalizar y Guardar Venta</button>
                </div>
            </form>
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let carrito = [];
    let clienteSeleccionado = null;
    let productoSeleccionado = null;

    // --- BÚSQUEDA DE CLIENTES ---
    const clienteBusqueda = document.getElementById('cliente_busqueda');
    const resultadosCliente = document.getElementById('resultados_cliente');
    const idCliente = document.getElementById('id_cliente');

    clienteBusqueda.addEventListener('keyup', function() {
        let term = this.value;
        idCliente.value = ''; // Limpiamos el ID si el usuario escribe de nuevo
        if (term.length < 2) {
            resultadosCliente.innerHTML = '';
            return;
        }
        fetch(`buscar_clientes.php?term=${term}`)
            .then(response => response.json())
            .then(data => {
                resultadosCliente.innerHTML = '';
                data.forEach(cliente => {
                    let div = document.createElement('div');
                    div.textContent = cliente.label; // Ej: "Juan Perez (20-12345678-9)"
                    div.dataset.id = cliente.id;
                    div.addEventListener('click', function() {
                        clienteBusqueda.value = this.textContent;
                        idCliente.value = this.dataset.id;
                        clienteSeleccionado = {id: cliente.id, label: cliente.label};
                        resultadosCliente.innerHTML = ''; // Ocultamos resultados
                    });
                    resultadosCliente.appendChild(div);
                });
            });
    });

    // --- BÚSQUEDA DE PRODUCTOS ---
    const productoBusqueda = document.getElementById('producto_busqueda');
    const resultadosProducto = document.getElementById('resultados_producto');

    productoBusqueda.addEventListener('keyup', function() {
        let term = this.value;
        if (term.length < 2) {
            resultadosProducto.innerHTML = '';
            return;
        }
        // Este archivo 'buscar_productos.php' es necesario. Te lo doy a continuación.
        fetch(`buscar_productos.php?term=${term}`)
            .then(response => response.json())
            .then(data => {
                resultadosProducto.innerHTML = '';
                data.forEach(producto => {
                    let div = document.createElement('div');
                    div.textContent = `${producto.nombre} ($${producto.precio_venta}) - Stock: ${producto.stock_actual}`;
                    div.addEventListener('click', function() {
                        productoBusqueda.value = producto.nombre;
                        productoSeleccionado = producto; // Guardamos el objeto producto completo
                        resultadosProducto.innerHTML = '';
                    });
                    resultadosProducto.appendChild(div);
                });
            });
    });

    // --- AGREGAR PRODUCTO AL CARRITO ---
    document.getElementById('btnAgregarProducto').addEventListener('click', function() {
        let cantidad = parseInt(document.getElementById('cantidad_producto').value);
        if (productoSeleccionado && cantidad > 0) {
            // Verificamos si el producto ya está en el carrito
            let itemExistente = carrito.find(item => item.id === productoSeleccionado.id);
            if (itemExistente) {
                itemExistente.cantidad += cantidad;
            } else {
                carrito.push({
                    id: productoSeleccionado.id,
                    nombre: productoSeleccionado.nombre,
                    precio: parseFloat(productoSeleccionado.precio_venta),
                    cantidad: cantidad
                });
            }
            productoSeleccionado = null; // Reseteamos el producto seleccionado
            document.getElementById('producto_busqueda').value = '';
            document.getElementById('cantidad_producto').value = 1;
            actualizarCarrito();
        } else {
            alert('Por favor, seleccione un producto y asegúrese de que la cantidad sea válida.');
        }
    });

    // --- ACTUALIZAR VISTA DEL CARRITO ---
    function actualizarCarrito() {
        let tbody = document.getElementById('carrito_tbody');
        let total = 0;
        tbody.innerHTML = ''; // Limpiamos la tabla

        carrito.forEach((item, index) => {
            let subtotal = item.cantidad * item.precio;
            total += subtotal;
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.nombre}</td>
                <td style="text-align: right;">${item.cantidad}</td>
                <td style="text-align: right;">$${item.precio.toFixed(2)}</td>
                <td style="text-align: right;">$${subtotal.toFixed(2)}</td>
                <td style="text-align: center;"><button type="button" class="btn btn-delete btn-eliminar" data-index="${index}">Quitar</button></td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('totalVenta').textContent = total.toFixed(2);
        document.getElementById('total_venta_hidden').value = total;
        
        // Actualizar el campo oculto para el formulario con el JSON del carrito
        document.getElementById('items_venta').value = JSON.stringify(carrito);

        // Añadir listeners a los botones de eliminar
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', function() {
                let index = parseInt(this.dataset.index);
                carrito.splice(index, 1); // Quitar del array
                actualizarCarrito();
            });
        });
    }

    // --- VALIDACIÓN ANTES DE ENVIAR ---
    document.getElementById('formVenta').addEventListener('submit', function(e) {
        if (!idCliente.value) {
            e.preventDefault();
            alert('Debe seleccionar un cliente.');
            return;
        }
        if (carrito.length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un producto a la venta.');
            return;
        }
        // Si todo está bien, el formulario se envía
    });
});
</script>
</body>
</html>