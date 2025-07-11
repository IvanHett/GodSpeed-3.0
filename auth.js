// Check if user is already logged in
async function checkAuth() {
    try {
        const response = await fetch('auth_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ action: 'check' })
        });
        const data = await response.json();
        
        if (data.loggedIn && window.location.pathname.includes('login.html')) {
            window.location.href = 'index.php';
        } else if (!data.loggedIn && !window.location.pathname.includes('login.html')) {
            window.location.href = 'login.html';
        }
    } catch (error) {
        console.error('Auth check failed:', error);
    }
}

// Handle login form submission
document.getElementById('login-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    try {
        const response = await fetch('auth_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'login',
                username: username,
                password: password
            })
        });
        
        const data = await response.json();
        if (data.success) {
            window.location.href = 'index.php';
        } else {
            alert('Invalid credentials!');
        }
    } catch (error) {
        console.error('Login failed:', error);
        alert('Login failed. Please try again.');
    }
});

// Check authentication on page load
window.onload = checkAuth; 