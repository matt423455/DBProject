async function loginUser(event) { 
    event.preventDefault(); // prevents refreshing of message

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

        // Parse the JSON response from the server
        let data = await response.json();

        // If login fails, data.success will be false and data.message contains the error
        if (!data.success) {
            msgEl.textContent = data.message || "Login failed.";
        } else {
            msgEl.textContent = "Login successful! (User ID: " + (data.user_id || "N/A") + ")";
            // Optionally redirect, e.g.:
            setTimeout(() => {
                window.location.href = "events.html";
            }, 1000);

            // window.location.href = "dashboard.html";
        }
    } catch (err) {
        // In case of a network or JSON parsing error, display the error message
        msgEl.textContent = "Error: " + err.message;
    }
}

async function fetchUniversities() {
    try {
        let res = await fetch("API/getUniversities.php");
        let data = await res.json();
        if (data.success && data.data) {
            let select = document.getElementById("reg-university-id");
            data.data.forEach(univ => {
                let option = document.createElement("option");
                option.value = univ.university_id;
                option.textContent = univ.name;
                select.appendChild(option);
            });
        }
    } catch (err) {
        console.error("Error fetching universities", err);
    }
}
document.addEventListener("DOMContentLoaded", fetchUniversities);

// Registration handler
async function registerUser(event) {
    event.preventDefault();
    const username = document.getElementById("reg-username").value.trim();
    const email = document.getElementById("reg-email").value.trim();
    const password = document.getElementById("reg-password").value.trim();
    const university_id = document.getElementById("reg-university-id").value.trim();
    const msgEl = document.getElementById("register-message");
    msgEl.textContent = "";

    if (!username || !email || !password || !university_id) {
        msgEl.textContent = "Please fill in all fields.";
        return;
    }

    try {
        let response = await fetch("API/register.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username, email, password, university_id })
        });
        let data = await response.json();
        msgEl.textContent = data.success ? "Registration successful!" : data.message;
    } catch (err) {
        msgEl.textContent = "Error: " + err.message;
    }
}
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("register-form").addEventListener("submit", registerUser);
});
