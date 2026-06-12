<style>
.auth-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.3s;
}

.auth-modal-overlay.active {
    display: flex;
    opacity: 1;
}

.auth-modal {
    background: var(--white);
    width: 100%;
    max-width: 440px;
    border-radius: 16px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.15);
    padding: 32px;
    transform: scale(0.95);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
}

.auth-modal-overlay.active .auth-modal {
    transform: scale(1);
}

.auth-modal-close {
    position: absolute;
    top: 16px; right: 16px;
    background: transparent;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-muted);
}

.auth-modal h2 {
    font-family: 'Playfair Display', serif;
    color: var(--pink-dark);
    font-size: 28px;
    margin-bottom: 8px;
}

.auth-modal p {
    color: var(--text-muted);
    font-size: 14px;
    margin-bottom: 24px;
}

#auth-error {
    display: none;
    background: #fff0f0;
    color: #e53935;
    padding: 12px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 16px;
}
</style>

<div class="auth-modal-overlay" id="authModalOverlay">
    <div class="auth-modal">
        <button class="auth-modal-close" onclick="closeAuthModal()">×</button>
        <h2>Welcome Back</h2>
        <p>Log in to book your favorite venues instantly.</p>
        
        <div id="auth-error"></div>

        <form id="authLoginForm" onsubmit="handleAuthLogin(event)">
            <input type="hidden" id="pendingVenueId" name="venue_id" value="">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="authEmail" name="email" required style="width:100%">
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label>Password</label>
                <input type="password" id="authPassword" name="password" required style="width:100%">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%" id="authLoginBtn">Log in</button>
        </form>

        <p style="text-align:center; margin-top: 24px; font-size: 14px;">
            Don't have an account? <a href="/eventix/register.php" style="color:var(--pink-main);font-weight:600">Sign up</a>
        </p>
    </div>
</div>

<script>
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
                // Redirect directly to booking!
                const addons = window.pendingAddons ? '&addons=' + window.pendingAddons : '';
                window.location.href = `/eventix/customer/book.php?id=${venueId}${addons}`;
            } else {
                // Refresh or go to dashboard
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
</script>
