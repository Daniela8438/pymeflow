document.addEventListener('DOMContentLoaded', function() {

    // --- Gráfico de Ventas por Mes (Gráfico de Barras) ---
    fetch('api/ventas_por_mes.php')
        .then(response => response.json())
        .then(data => {
            const etiquetas = data.map(item => item.mes);
            const valores = data.map(item => item.total_ventas);

            const ctxVentas = document.getElementById('graficoVentas').getContext('2d');
            new Chart(ctxVentas, {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [{
                        label: 'Ingresos por Mes',
                        data: valores,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: { scales: { y: { beginAtZero: true } } }
            });
        });

    // --- Gráfico de Top Productos (Gráfico de Torta/Dona) ---
    fetch('api/productos_mas_vendidos.php')
        .then(response => response.json())
        .then(data => {
            const etiquetas = data.map(item => item.nombre);
            const valores = data.map(item => item.total_cantidad);

            const ctxProductos = document.getElementById('graficoProductos').getContext('2d');
            new Chart(ctxProductos, {
                type: 'doughnut', // pie o doughnut
                data: {
                    labels: etiquetas,
                    datasets: [{
                        label: 'Cantidad Vendida',
                        data: valores,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ]
                    }]
                }
            });
        });
});