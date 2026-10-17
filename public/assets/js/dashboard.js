/**
 * PUB CampusMate — Dashboard JavaScript & Chart.js Visualizations
 */

document.addEventListener('DOMContentLoaded', () => {
    // Academic Progress CGPA & Credit Chart (if canvas present)
    const progressCanvas = document.getElementById('academicProgressChart');
    if (progressCanvas && typeof Chart !== 'undefined') {
        new Chart(progressCanvas, {
            type: 'line',
            data: {
                labels: ['Semester 1', 'Semester 2', 'Summer 26 (Current)'],
                datasets: [{
                    label: 'CGPA Progression',
                    data: [3.50, 3.65, 3.65],
                    borderColor: '#1e3a8a',
                    backgroundColor: 'rgba(30, 58, 138, 0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointBackgroundColor: '#1e3a8a',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 2.0,
                        max: 4.0,
                        ticks: { stepSize: 0.5 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
