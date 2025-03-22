// scripts/script.js

async function loginUser() {
    const username = document.getElementById("login-username").value.trim();
    const password = document.getElementById("login-password").value.trim();
    const msgEl = document.getElementById("login-message");

    msgEl.textContent = "";

    if (!username || !password) {
        msgEl.textContent = "Please enter username and password.";
        return;
    }

    try {
        let response = await fetch("API/login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username, password })
        });

        let data = await response.json();

        if (data.success) {
            msgEl.textContent = "Login successful! (User ID: " + data.user_id + ")";
            // Optionally redirect or do something else, e.g.:
            // window.location.href = "dashboard.html";
        } else {
            msgEl.textContent = data.message || "Login failed.";
        }
    } catch (err) {
        msgEl.textContent = "Error connecting to server.";
    }
}

async function registerUser() {
    const username = document.getElementById("reg-username").value.trim();
    const email = document.getElementById("reg-email").value.trim();
    const password = document.getElementById("reg-password").value.trim();
    const university_id = document.getElementById("reg-university-id").value.trim();
    const msgEl = document.getElementById("register-message");

    msgEl.textContent = "";

    if (!username || !email || !password) {
        msgEl.textContent = "Please enter username, email, and password.";
        return;
    }

    try {
        let response = await fetch("API/register.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username, email, password, university_id })
        });

        let data = await response.json();

        if (data.success) {
            msgEl.textContent = "Registration successful! You can now log in.";
        } else {
            msgEl.textContent = data.message || "Registration failed.";
        }
    } catch (err) {
        msgEl.textContent = "Error connecting to server.";
    }
}
