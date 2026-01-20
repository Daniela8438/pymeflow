<div class="sidebar">
    <div class="logo">
        <img src="img/logo.png" alt="Logo PymeFlow">
    </div>
    <nav>
        <a href="dashboard.php" class="<?php if ($pagina_activa == 'dashboard') echo 'active'; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="clientes.php" class="<?php if ($pagina_activa == 'clientes') echo 'active'; ?>"><i class="fas fa-users"></i> Clientes</a>
        <a href="productos.php" class="<?php if ($pagina_activa == 'productos') echo 'active'; ?>"><i class="fas fa-box-open"></i> Productos</a>
        
        <a href="historial_ventas.php" class="<?php if ($pagina_activa == 'ventas') echo 'active'; ?>"><i class="fas fa-shopping-cart"></i> Ventas</a>
        
        <?php if ($_SESSION['rol_usuario'] == 'administrador'): ?>
            <a href="vendedores.php" class="<?php if ($pagina_activa == 'vendedores') echo 'active'; ?>"><i class="fas fa-user-tie"></i> Vendedores</a>
            <a href="reportes.php" class="<?php if ($pagina_activa == 'reportes') echo 'active'; ?>"><i class="fas fa-chart-line"></i> Reportes</a>
        <?php endif; ?>
    </nav>
</div>