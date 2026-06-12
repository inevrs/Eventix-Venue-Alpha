// Manager Dashboard Chart
function initManagerChart(labels, data) {
    const ctx = document.getElementById('venueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Bookings',
                data: data,
                backgroundColor: 'rgba(233, 30, 99, 0.7)',
                borderColor: 'rgba(233, 30, 99, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}
