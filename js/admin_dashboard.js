// Admin Dashboard Chart
function initAdminChart(labels, data) {
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Number of Bookings',
                data: data,
                backgroundColor: [
                    'rgba(233, 30, 99, 0.7)',  // Pink (Confirmed)
                    'rgba(255, 193, 7, 0.7)',  // Yellow (Pending)
                    'rgba(158, 158, 158, 0.7)' // Grey (Cancelled)
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}
