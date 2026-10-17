/**
 * PUB CampusMate — Course Registration Portal JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    const registrationForm = document.getElementById('registrationForm');
    const checkboxes = document.querySelectorAll('.course-section-check');
    const creditDisplay = document.getElementById('selectedCreditCount');
    const submitBtn = document.getElementById('btnSubmitRegistration');

    function updateSelectedCredits() {
        let total = 0.0;
        checkboxes.forEach(cb => {
            if (cb.checked) {
                total += parseFloat(cb.dataset.credit || 0);
            }
        });

        if (creditDisplay) {
            creditDisplay.textContent = total.toFixed(1);
        }

        if (submitBtn) {
            submitBtn.disabled = (total === 0);
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCredits);
    });

    updateSelectedCredits();

    if (registrationForm) {
        registrationForm.addEventListener('submit', (e) => {
            if (typeof Swal !== 'undefined') {
                e.preventDefault();
                Swal.fire({
                    title: 'Submit Course Registration?',
                    text: 'Your registration request will be forwarded to your Academic Advisor for formal review.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1e3a8a',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Submit Request'
                }).then((result) => {
                    if (result.isConfirmed) {
                        registrationForm.submit();
                    }
                });
            }
        });
    }
});
