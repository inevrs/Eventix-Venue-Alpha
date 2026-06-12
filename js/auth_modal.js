// Auth Modal Logic
function openAuthModal(venueId = null) {
    document.getElementById('authModalOverlay').classList.add('active');
    if (venueId) {
        document.getElementById('pendingVenueId').value = venueId;
    }
}

function closeAuthModal() {
    document.getElementById('authModalOverlay').classList.remove('active');
    document.getElementById('auth-error').style.display = 'none';
    document.getElementById('pendingVenueId').value = '';
}

async function handleAuthLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('authLoginBtn');
    const err = document.getElementById('auth-error');

    btn.disabled = true;
    btn.textContent = 'Logging in...';
    err.style.display = 'none';

    const formData = new FormData();
    formData.append('email', document.getElementById('authEmail').value);
    formData.append('password', document.getElementById('authPassword').value);

    try {
        const res = await fetch('/eventix/api_login.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            const venueId = document.getElementById('pendingVenueId').value;
            if (venueId && data.role === 'customer') {
                const addons = window.pendingAddons ? '&addons=' + window.pendingAddons : '';
                window.location.href = `/eventix/customer/book.php?id=${venueId}${addons}`;
            } else {
                window.location.href = data.redirect || window.location.href;
            }
        } else {
            err.textContent = data.error;
            err.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Log in';
        }
    } catch (e) {
        err.textContent = 'Network error. Please try again.';
        err.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Log in';
    }
}
