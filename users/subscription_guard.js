// users/subscription_guard.js
// Requires SweetAlert2: <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

// Get token from localStorage
function getToken() {
    return localStorage.getItem('token');
}

// Fetch subscription status from backend
async function fetchSubscriptionStatus() {
    const token = getToken();
    if (!token) return null;

    try {
        const response = await fetch('https://terrific-presence-production.up.railway.app/api/subscription/status', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Cache-Control': 'no-cache'
            }
        });
        
        if (!response.ok) {
            console.error('Failed to fetch subscription status:', response.status);
            return null;
        }
        
        const data = await response.json();
        return data.status === 'success' ? data.subscription : null;
    } catch (error) {
        console.error('Error fetching subscription status:', error);
        return null;
    }
}

// Check if user has access to the current page
async function checkPageAccess(pageName = null) {
    const token = getToken();
    if (!token) return false;

    // Get current page name (without .html)
    const currentPage = pageName || window.location.pathname.split('/').pop();
    if (!currentPage) return false;

    try {
        const response = await fetch(`https://terrific-presence-production.up.railway.app/api/subscription/check-access?page=${currentPage}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            console.error('Failed to check page access:', response.status);
            return false;
        }
        
        const data = await response.json();
        return data.status === 'success' && data.hasAccess;
    } catch (error) {
        console.error('Error checking page access:', error);
        return false;
    }
}

// Main function to enforce subscription and access
async function enforceSubscriptionOrRedirect() {
    const token = getToken();
    if (!token) {
        window.location.href = '/login.html';
        return false;
    }

    const subscription = await fetchSubscriptionStatus();
    if (!subscription || subscription.status !== 'active') {
        await showSubscriptionRequired();
        return false;
    }

    // Check if subscription expired
    const now = new Date();
    const endDate = new Date(subscription.subscriptionEnd);
    if (endDate <= now) {
        await showSubscriptionExpired();
        return false;
    }

    // Check page access
    const hasAccess = await checkPageAccess();
    if (!hasAccess) {
        await showAccessDenied();
        return false;
    }

    // If all checks pass, update UI
    updateSubscriptionUI(subscription);
    return true;
}

// UI helpers using SweetAlert2
async function showSubscriptionRequired() {
    await Swal.fire({
        icon: 'warning',
        title: 'Subscription Required',
        text: 'You need an active subscription to access this page.',
        confirmButtonText: 'Subscribe Now',
        allowOutsideClick: false
    });
    window.location.href = '../payment.html';
}

async function showSubscriptionExpired() {
    await Swal.fire({
        icon: 'error',
        title: 'Subscription Expired',
        text: 'Your subscription has expired. Please renew to continue accessing this page.',
        confirmButtonText: 'Renew Subscription',
        allowOutsideClick: false
    });
    window.location.href = '../payment.html';
}

async function showAccessDenied() {
    await Swal.fire({
        icon: 'error',
        title: 'Access Denied',
        text: 'Your current subscription plan does not include access to this page.',
        confirmButtonText: 'Upgrade Plan',
        allowOutsideClick: false
    });
    window.location.href = '../payment.html';
}

// Update subscription info UI
function updateSubscriptionUI(subscription) {
    const subscriptionInfo = document.getElementById('subscriptionInfo');
    if (!subscriptionInfo) return;

    let startDate = 'N/A', endDate = 'N/A', timeRemainingText = '';
    try {
        if (subscription.subscriptionStart) {
            const start = new Date(subscription.subscriptionStart);
            if (!isNaN(start.getTime())) {
                startDate = start.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            }
        }
        if (subscription.subscriptionEnd) {
            const end = new Date(subscription.subscriptionEnd);
            if (!isNaN(end.getTime())) {
                endDate = end.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                // Calculate time remaining
                const now = new Date();
                const timeRemaining = end - now;
                if (timeRemaining > 0) {
                    const days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                    if (days > 0) timeRemainingText = `${days} days remaining`;
                    else if (hours > 0) timeRemainingText = `${hours} hours remaining`;
                    else timeRemainingText = `${minutes} minutes remaining`;
                }
            }
        }
    } catch (error) {
        console.error('Error formatting dates:', error);
    }

    subscriptionInfo.innerHTML = `
        <div class="subscription-status active">
            <i class="fas fa-check-circle"></i>
            <span>Active Subscription</span>
        </div>
        <div class="subscription-details">
            <p><strong>Plan:</strong> ${subscription.plan ? subscription.plan.charAt(0).toUpperCase() + subscription.plan.slice(1) : 'Free'}</p>
            <p><strong>Start Date:</strong> ${startDate}</p>
            <p><strong>Expiry Date:</strong> ${endDate}</p>
            ${timeRemainingText ? `<p><strong>Time Remaining:</strong> ${timeRemainingText}</p>` : ''}
            <p><strong>Status:</strong> ${subscription.status ? subscription.status.charAt(0).toUpperCase() + subscription.status.slice(1) : 'Inactive'}</p>
            <p><strong>Access:</strong> All Premium Features</p>
        </div>
    `;
}

// Export for use in HTML pages
window.enforceSubscriptionOrRedirect = enforceSubscriptionOrRedirect;
window.updateSubscriptionUI = updateSubscriptionUI; 