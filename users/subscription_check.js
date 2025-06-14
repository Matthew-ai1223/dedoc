// Subscription check utility functions

// Check if user has an active subscription
async function checkSubscription() {
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = '../login.html';
        return false;
    }

    try {
        const response = await fetch('http://localhost:5000/api/payments/status', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (!response.ok) {
            throw new Error('Failed to fetch subscription status');
        }

        const data = await response.json();
        return data.subscription && data.subscription.status === 'active';
    } catch (error) {
        console.error('Error checking subscription:', error);
        return false;
    }
}

// Redirect non-subscribers to payment page
async function enforceSubscription() {
    const isSubscribed = await checkSubscription();
    if (!isSubscribed) {
        alert('Please subscribe to access this feature.');
        window.location.href = '../payment.html';
        return false;
    }
    return true;
}

// Update UI based on subscription status
async function updateSubscriptionUI() {
    const isSubscribed = await checkSubscription();
    const premiumElements = document.querySelectorAll('.premium-feature');
    
    premiumElements.forEach(element => {
        if (isSubscribed) {
            element.style.pointerEvents = 'auto';
            element.style.opacity = '1';
        } else {
            element.style.pointerEvents = 'none';
            element.style.opacity = '0.5';
        }
    });
}

// Handle payment status from URL parameters
function handlePaymentStatus() {
    const urlParams = new URLSearchParams(window.location.search);
    const paymentStatus = urlParams.get('payment');
    const error = urlParams.get('error');

    if (paymentStatus === 'success') {
        alert('Payment successful! Your subscription is now active.');
        // Remove the query parameters
        window.history.replaceState({}, document.title, window.location.pathname);
    } else if (error) {
        let errorMessage = 'Payment failed. ';
        switch (error) {
            case 'user_not_found':
                errorMessage += 'User not found.';
                break;
            case 'payment_failed':
                errorMessage += 'Transaction was not successful.';
                break;
            case 'verification_failed':
                errorMessage += 'Could not verify payment.';
                break;
            default:
                errorMessage += 'Please try again or contact support.';
        }
        alert(errorMessage);
        // Remove the query parameters
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

// Subscription check utility
function checkSubscriptionStatus(subscription) {
    if (!subscription || !subscription.endDate) {
        return {
            isActive: false,
            status: 'inactive',
            message: 'No active subscription'
        };
    }

    const now = new Date();
    const endDate = new Date(subscription.endDate);
    const timeLeft = endDate - now;

    // If subscription has expired
    if (timeLeft <= 0) {
        return {
            isActive: false,
            status: 'expired',
            message: 'Subscription has expired'
        };
    }

    // Calculate remaining time
    const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));

    // Check if subscription is about to expire
    if (timeLeft <= 24 * 60 * 60 * 1000) { // Less than 24 hours
        return {
            isActive: true,
            status: 'expiring-soon',
            message: `Subscription expires in ${hours}h ${minutes}m`,
            timeLeft: {
                days,
                hours,
                minutes
            }
        };
    }

    // Active subscription with more than 24 hours remaining
    return {
        isActive: true,
        status: 'active',
        message: `Subscription active - ${days} days remaining`,
        timeLeft: {
            days,
            hours,
            minutes
        }
    };
}

// Function to format time remaining
function formatTimeRemaining(timeLeft) {
    if (timeLeft.days > 0) {
        return `${timeLeft.days} days`;
    } else if (timeLeft.hours > 0) {
        return `${timeLeft.hours} hours`;
    } else {
        return `${timeLeft.minutes} minutes`;
    }
}

// Function to handle subscription expiration
function handleSubscriptionExpiration(subscription) {
    const status = checkSubscriptionStatus(subscription);
    
    if (!status.isActive) {
        // Redirect to payment page if not on payment page already
        if (!window.location.pathname.includes('payment.html')) {
            window.location.href = '/payment.html';
        }
        return false;
    }

    // Show warning if subscription is expiring soon
    if (status.status === 'expiring-soon') {
        showExpirationWarning(status.timeLeft);
    }

    return true;
}

// Function to show expiration warning
function showExpirationWarning(timeLeft) {
    const warningDiv = document.createElement('div');
    warningDiv.className = 'subscription-warning';
    warningDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #fff3cd;
        color: #856404;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 9999;
        animation: slideIn 0.5s ease;
    `;

    warningDiv.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Subscription Expiring Soon</strong><br>
                Time remaining: ${formatTimeRemaining(timeLeft)}
            </div>
            <button onclick="this.parentElement.parentElement.remove()" style="
                background: none;
                border: none;
                color: #856404;
                cursor: pointer;
                margin-left: 15px;
                font-size: 18px;
            ">&times;</button>
        </div>
    `;

    document.body.appendChild(warningDiv);
}

// Export functions for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        checkSubscriptionStatus,
        handleSubscriptionExpiration,
        showExpirationWarning
    };
} 