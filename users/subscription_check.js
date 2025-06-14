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