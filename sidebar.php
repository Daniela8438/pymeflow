<?php
// La sesión ya está iniciada por 'verificar_sesion.php'
// La variable $pagina_activa se define en cada página
?>
<div class="sidebar">
    <div class="logo">
        <img src="logo.png" alt="PymeFlow Logo" style="width: 150px; margin-bottom: 10px;">
    </div>
    <nav>
        <?php // --- ENLACES DEL ADMINISTRADOR --- ?>
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'administrador'): ?>
            
            <a href="dashboard.php" class="<?php echo ($pagina_activa == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            
            <a href="facturas.php" class="<?php echo ($pagina_activa == 'facturas') ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice"></i> Historial de Facturas
            </a>
            
            <a href="productos.php" class="<?php echo ($pagina_activa == 'productos') ? 'active' : ''; ?>">
                <i class="fas fa-boxes"></i> Productos
            </a>
            
            <a href="clientes.php" class="<?php echo ($pagina_activa == 'clientes') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Clientes
            </a>
            
            <a href="usuarios.php" class="<?php echo ($pagina_activa == 'usuarios') ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i> Usuarios
            </a>
            
        <?php endif; ?>
        
        <?php // --- ENLACES DEL VENDEDOR --- ?>
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'vendedor'): ?>
            
            <a href="venta_formulario.php" class="<?php echo ($pagina_activa == 'ventas') ? 'active' : ''; ?>">
                <i class="fas fa-cash-register"></i> Nueva Venta
            </a>
            
            <a href="facturas.php" class="<?php echo ($pagina_activa == 'facturas') ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice"></i> Historial de Facturas
            </a>
            
            <a href="productos.php" class="<?php echo ($pagina_activa == 'productos') ? 'active' : ''; ?>">
                <i class="fas fa-boxes"></i> Consultar Productos
            </a>

        <?php endif; ?>
    </nav>
</div>