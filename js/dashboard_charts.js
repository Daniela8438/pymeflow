document.addEventListener('DOMContentLoaded', function () {

    // --- GRÁFICO DE VENTAS POR MES (LÍNEAS) ---
    const ctxVentas = document.getElementById('graficoVentas').getContext('2d');
    const graficoVentas = new Chart(ctxVentas, {
        type: 'line',
        data: {
            // Estos datos deberían venir de tu base de datos mediante una llamada a un PHP
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
            datasets: [{
                label: 'Ventas del Semestre',
                data: [12000, 19000, 15000, 25000, 22000, 30000],
                borderColor: 'rgba(0, 95, 115, 1)', // --color-primario
                backgroundColor: 'rgba(0, 95, 115, 0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Permite que el gráfico se ajuste al contenedor
            plugins: {
                legend: { labels: { font: { size: 14 } } }
            },
            scales: {
                y: { ticks: { font: { size: 12 } } },
                x: { ticks: { font: { size: 12 } } }
            }
        }
    });


    // --- GRÁFICO DE TOP 5 PRODUCTOS (TORTA/DONA) ---
    const ctxProductos = document.getElementById('graficoProductos').getContext('2d');
    const graficoProductos = new Chart(ctxProductos, {
        type: 'doughnut', // 'pie' para torta completa, 'doughnut' para dona
        data: {
            // Estos datos también deberían venir de la base de datos
            labels: ['Producto A', 'Producto B', 'Producto C', 'Producto D', 'Producto E'],
            datasets: [{
                label: 'Unidades Vendidas',
                data: [300, 150, 100, 80, 40],
                backgroundColor: [
                    '#005f73', '#0a9396', '#94d2bd', '#e9d8a6', '#ee9b00'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Clave para controlar el tamaño con el div
            plugins: {
                legend: {
                    position: 'top', // Posición de las etiquetas
                    labels: {
                        // Aumentamos el tamaño de la fuente de las etiquetas
                        font: {
                            size: 14 // <-- ¡TAMAÑO DE LETRA AJUSTADO!
                        },
                        boxWidth: 20,
                        padding: 20
                    }
                }
            }
        }
    });
});