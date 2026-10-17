/**
 * PUB CampusMate — Timetable JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // Print Timetable Helper
    const printBtn = document.getElementById('btnPrintTimetable');
    if (printBtn) {
        printBtn.addEventListener('click', () => {
            window.print();
        });
    }
});
