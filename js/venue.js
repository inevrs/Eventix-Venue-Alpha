// Venue Detail Page: Carousel + Addon Selection
let current = 0;
let selectedAddons = {};

function initCarousel(totalSlides) {
    window._totalSlides = totalSlides;
}

function updateCarousel() {
    if (!document.getElementById('slides')) return;
    document.getElementById('slides').style.transform = `translateX(-${current * 100}%)`;
    document.querySelectorAll('.carousel-dot').forEach((d, i) => d.classList.toggle('active', i === current));
}

function changeSlide(dir) {
    current = (current + dir + window._totalSlides) % window._totalSlides;
    updateCarousel();
}

function goToSlide(i) {
    current = i;
    updateCarousel();
}

function toggleAddon(id, price, name) {
    const card = document.getElementById('addon-' + id);
    if (selectedAddons[id]) {
        delete selectedAddons[id];
        card.classList.remove('selected');
    } else {
        selectedAddons[id] = { price, name };
        card.classList.add('selected');
    }
    updateTotal();
}

function updateTotal() {
    let addonsTotal = 0;
    let summaryHtml = '';
    let addonIds = [];

    for (const [id, addon] of Object.entries(selectedAddons)) {
        addonsTotal += addon.price;
        addonIds.push(id);
        summaryHtml += `<div class="total-row"><span>${addon.name}</span><span>+RM${addon.price.toFixed(2)}</span></div>`;
    }

    document.getElementById('addon-summary').innerHTML = summaryHtml;
    document.getElementById('grand-total').textContent = 'RM' + (window._venuePrice + addonsTotal).toFixed(2);

    const addonInput = document.getElementById('selected-addons-input');
    if (addonInput) addonInput.value = addonIds.join(',');

    // Save for auth modal redirect
    window.pendingAddons = addonIds.join(',');
}
