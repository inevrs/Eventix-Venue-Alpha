// Booking Date Calculator
function initBookingCalc(basePrice, addonsTotal) {
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const venueLabel = document.getElementById('venue-price-label');
    const venueDisplay = document.getElementById('venue-price-display');
    const grandDisplay = document.getElementById('grand-total-display');

    function calculateTotal() {
        if (startInput.value) {
            endInput.min = startInput.value;
        }

        if (startInput.value && endInput.value) {
            const start = new Date(startInput.value);
            const end = new Date(endInput.value);
            let days = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;

            if (days < 1) days = 1;

            const venueTotal = basePrice * days;
            const grandTotal = venueTotal + addonsTotal;

            venueLabel.innerText = `Venue (${days} day${days > 1 ? 's' : ''})`;
            venueDisplay.innerText = 'RM' + venueTotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            grandDisplay.innerText = 'RM' + grandTotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }
    }

    startInput.addEventListener('change', calculateTotal);
    endInput.addEventListener('change', calculateTotal);
}
