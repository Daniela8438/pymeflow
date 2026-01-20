<?php
include 'verificar_sesion.php';
$pagina_activa = 'ventas';
include 'conexion.php';

// Obtener la lista de clientes para el dropdown
$clientes_resultado = $conexion->query("SELECT id, razon_social FROM clientes ORDER BY razon_social ASC");

// Obtener la lista de productos y guardarla en un array de PHP
$productos_array = [];
$productos_resultado = $conexion->query("SELECT id, nombre, precio_venta, stock_actual FROM productos WHERE stock_actual > 0 ORDER BY nombre ASC");
while ($producto = $productos_resultado->fetch_assoc()) {
    $productos_array[] = $producto;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nueva Venta - PymeFlow</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .form-section { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group select, .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .product-adder { display: flex; gap: 10px; align-items: flex-end; }
        .product-adder .form-group { flex-grow: 1; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; color: white; text-decoration: none; cursor: pointer; font-size: 1em; }
        .btn-add-product { background-color: #5cb85c; }
        .btn-delete-item { background-color: #d9534f; }
        .btn-save { background-color: var(--color-secundario); }
        .btn-cancel { background-color: #6c757d; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; }
        .total-section { text-align: right; margin-top: 20px; line-height: 1.5em; }
        .total-section span { display: block; }
        .total-section .grand-total { font-size: 1.5em; font-weight: bold; color: var(--color-primario); }
        .payment-options { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
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
            <h1>Registrar Nueva Venta</h1>
            <form action="venta_guardar.php" method="POST" id="ventaForm">
                <div class="form-section">
                    <h2>1. Datos del Cliente</h2>
                    <div class="form-group">
                        <label for="cliente">Seleccionar Cliente</label>
                        <select id="cliente" name="id_cliente" required>
                            <option value="" disabled selected>-- Elige un cliente --</option>
                            <?php 
                            mysqli_data_seek($clientes_resultado, 0);
                            while($cliente = $clientes_resultado->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['razon_social']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-section">
                    <h2>2. Productos</h2>
                    <div class="product-adder">
                        <div class="form-group" style="flex-basis: 60%;"><label for="producto">Seleccionar Producto</label><select id="producto"><option value="" data-stock="" disabled selected>-- Elige un producto --</option><?php foreach($productos_array as $producto): ?><option value="<?php echo $producto['id']; ?>" data-stock="<?php echo $producto['stock_actual']; ?>"><?php echo htmlspecialchars($producto['nombre']); ?></option><?php endforeach; ?></select></div>
                        <div class="form-group" style="flex-basis: 20%;"><label for="cantidad">Cantidad</label><input type="number" id="cantidad" min="1" value="1"></div>
                        <button type="button" id="btnAgregarProducto" class="btn btn-add-product"><i class="fas fa-plus"></i> Agregar</button>
                    </div>
                    <table class="items-table">
                        <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th><th>Acción</th></tr></thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
                <div class="form-section">
                    <h2>3. Pago y Cierre</h2>
                    <div class="payment-options">
                        <div class="form-group"><label for="estado_pedido">Estado del Pedido</label><select id="estado_pedido" name="estado_pedido" required><option value="en_preparacion">En Preparación</option><option value="completado">Terminado / Entregado</option><option value="cancelado">Cancelado</option></select></div>
                        <div class="form-group"><label for="estado_pago">Estado del Pago</label><select id="estado_pago" name="estado_pago" required><option value="pendiente">Pendiente</option><option value="parcial">Parcial</option><option value="pagado">Pagado</option><option value="cancelado">Cancelado</option></select></div>
                    </div>
                    <div class="total-section" id="totalSection">
                        <span>Subtotal: $0.00</span>
                        <span>IVA (21%): $0.00</span>
                        <span class="grand-total">Total: $0.00</span>
                    </div>
                    <div style="text-align: right; margin-top: 20px;">
                        <a href="historial_ventas.php" class="btn btn-cancel">Cancelar</a>
                        <button type="submit" class="btn btn-save">Guardar Venta</button>
                    </div>
                </div>
            </form>
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const productos = <?php echo json_encode(array_column($productos_array, null, 'id')); ?>;
    
    const btnAgregar = document.getElementById('btnAgregarProducto');
    const productoSelect = document.getElementById('producto');
    const cantidadInput = document.getElementById('cantidad');
    const itemsBody = document.getElementById('itemsBody');
    const totalSection = document.getElementById('totalSection');
    const ventaForm = document.getElementById('ventaForm');

    btnAgregar.addEventListener('click', function() {
        const productoId = productoSelect.value;
        const cantidad = parseInt(cantidadInput.value);

        if (!productoId) { alert('Por favor, selecciona un producto.'); return; }
        if (isNaN(cantidad) || cantidad <= 0) { alert('Por favor, introduce una cantidad válida.'); return; }

        const producto = productos[productoId];

        if (cantidad > producto.stock_actual) {
            alert(`Stock insuficiente. Solo quedan ${producto.stock_actual} unidades de ${producto.nombre}.`);
            return;
        }

        const subtotal = cantidad * producto.precio_venta;
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-id-producto', productoId);
        
        newRow.innerHTML = `
            <td>
                ${producto.nombre}
                <input type="hidden" name="productos[${productoId}][id]" value="${productoId}">
                <input type="hidden" name="productos[${productoId}][precio]" value="${producto.precio_venta}">
            </td>
            <td>
                ${cantidad}
                <input type="hidden" name="productos[${productoId}][cantidad]" value="${cantidad}">
            </td>
            <td>$${parseFloat(producto.precio_venta).toFixed(2)}</td>
            <td>$${subtotal.toFixed(2)}</td>
            <td><button type="button" class="btn btn-delete-item"><i class="fas fa-trash-alt"></i></button></td>
        `;

        itemsBody.appendChild(newRow);
        actualizarTotal();
        
        productoSelect.value = '';
        cantidadInput.value = '1';
    });

    itemsBody.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete-item')) {
            e.target.closest('tr').remove();
            actualizarTotal();
        }
    });

    function actualizarTotal() {
        let totalConIva = 0;
        const rows = itemsBody.querySelectorAll('tr');
        rows.forEach(row => {
            const precio = parseFloat(row.cells[2].textContent.replace('$', ''));
            const cantidad = parseInt(row.cells[1].textContent);
            totalConIva += precio * cantidad;
        });

        const totalNeto = totalConIva / 1.21;
        const montoIva = totalConIva - totalNeto;

        totalSection.innerHTML = `
            <span>Subtotal: $${totalNeto.toFixed(2)}</span>
            <span>IVA (21%): $${montoIva.toFixed(2)}</span>
            <span class="grand-total">Total: $${totalConIva.toFixed(2)}</span>
        `;
    }

    ventaForm.addEventListener('submit', function(e) {
        const rows = itemsBody.querySelectorAll('tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Debes agregar al menos un producto a la venta antes de guardar.');
        }
    });
});
</script>

</body>
</html>