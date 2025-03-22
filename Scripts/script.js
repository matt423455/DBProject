// script.js

function toggleForms() {
  const loginForm = document.getElementById("login-form");
  const registerForm = document.getElementById("register-form");
  loginForm.classList.toggle("hidden");
  registerForm.classList.toggle("hidden");
}

// Login function
async function loginUser() {
  const username = document.getElementById("login-username").value.trim();
  const password = document.getElementById("login-password").value.trim();
  const errorDiv = document.getElementById("login-error");

  errorDiv.textContent = ""; // clear any previous error

  if (!username || !password) {
    errorDiv.textContent = "Please enter username and password.";
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
      // In a real app, you'd redirect to a dashboard or something.
      alert("Login successful! Redirecting...");
      window.location.href = "dashboard.php"; // example redirect
    } else {
      errorDiv.textContent = data.message || "Login failed.";
    }
  } catch (err) {
    errorDiv.textContent = "Error connecting to server.";
  }
}

// Register function
async function registerUser() {
  const username = document.getElementById("reg-username").value.trim();
  const email = document.getElementById("reg-email").value.trim();
  const password = document.getElementById("reg-password").value.trim();
  const password2 = document.getElementById("reg-password2").value.trim();
  const uniId = document.getElementById("reg-university-id").value.trim();
  const errorDiv = document.getElementById("register-error");

  errorDiv.textContent = "";

  if (!username || !email || !password || !password2 || !uniId) {
    errorDiv.textContent = "All fields are required.";
    return;
  }
  if (password !== password2) {
    errorDiv.textContent = "Passwords do not match.";
    return;
  }

  try {
    let response = await fetch("API/register.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        username,
        email,
        password,
        university_id: uniId
      })
    });
    let data = await response.json();

    if (data.success) {
      alert("Registration successful! You can now log in.");
      toggleForms(); // show the login form
    } else {
      errorDiv.textContent = data.message || "Registration failed.";
    }
  } catch (err) {
    errorDiv.textContent = "Error connecting to server.";
  }
}
