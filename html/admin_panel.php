<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: events.html");
    exit;
}
$user_role = $_SESSION['role'];
$username  = $_SESSION['username'] ?? 'Super Admin';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Super Admin Panel</title>
    <link rel="stylesheet" href="styles/admin.css">
    <script src="scripts/admin-script.js" defer></script>
    <style>
        .leave-admin {
            position: absolute;
            top: 10px;
            left: 10px;
            text-decoration: none;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <a class="leave-admin" href="events.html">Leave Admin Panel</a>
        <h1>Super Admin Panel</h1>
        
        <!-- Update User Role Section (Super Admin Only) -->
        <section id="user-management">
            <h2>User Role Management</h2>
            <form id="update-user-role-form">
                <label for="user-id">User ID:</label>
                <input type="number" id="user-id" name="user_id" required>
                <br>
                <label for="new-role">New Role:</label>
                <select id="new-role" name="new_role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
                <br>
                <button type="submit">Update Role</button>
            </form>
            <p id="user-role-message"></p>
        </section>

        <!-- Approve RSO Requests Section (Super Admin Only) -->
        <section id="approve-rso">
            <h2>Approve RSO Requests</h2>
            <div id="pending-rso-container">Loading pending RSOs...</div>
        </section>

        <section id="pending-events">
            <h2>Pending Events</h2>
            <div id="pending-events-container">Loading pending events...</div>
        </section>
        
        <!-- Delete Event Section (Admins & Super Admins) -->
        <section id="delete-event">
            <h2>Delete Event</h2>
            <form id="delete-event-form">
                <label for="event-id">Event ID:</label>
                <input type="number" id="event-id" name="event_id" required>
                <br>
                <button type="submit">Delete Event</button>
            </form>
            <p id="event-message"></p>
        </section>

        <!-- Manage RSO Membership Section (Admins & Super Admins) -->
        <section id="rso-membership">
            <h2>Manage RSO Membership</h2>
            <!-- Add User to RSO -->
            <form id="add-user-to-rso-form">
                <label for="add-rso-id">RSO ID:</label>
                <input type="number" id="add-rso-id" name="rso_id" required>
                <br>
                <label for="add-user-id">User ID:</label>
                <input type="number" id="add-user-id" name="user_id" required>
                <br>
                <button type="submit">Add User to RSO</button>
            </form>
            <p id="add-rso-message"></p>
            <!-- Remove User from RSO -->
            <form id="remove-user-from-rso-form">
                <label for="remove-rso-id">RSO ID:</label>
                <input type="number" id="remove-rso-id" name="rso_id" required>
                <br>
                <label for="remove-user-id">User ID:</label>
                <input type="number" id="remove-user-id" name="user_id" required>
                <br>
                <button type="submit">Remove User from RSO</button>
            </form>
            <p id="remove-rso-message"></p>
        </section>

        <!-- List All RSOs Section (Admins & Super Admins) -->
        <section id="list-rsos">
            <h2>List of RSOs</h2>
            <div id="rso-list-container">Loading RSOs...</div>
        </section>
    </div>
    <!-- Load pending RSOs (for the Approve RSO section) -->
    <script>
        async function loadPendingRSOs() {
            const container = document.getElementById('pending-rso-container');
            container.textContent = 'Loading pending RSOs...';
            try {
                let res = await fetch('API/list_rso.php');
                let data = await res.json();
                if (data.success && data.data.length) {
                    // Filter pending RSOs (status == 2)
                    const pending = data.data.filter(rso => rso.is_active == 2);
                    container.innerHTML = '';
                    pending.forEach(rso => {
                        const div = document.createElement('div');
                        div.classList.add('event');
                        div.innerHTML = `<h3 class="event-title">${rso.name}</h3>
                                         <p class="event-description">${rso.description}</p>
                                         <p><strong>University ID:</strong> ${rso.university_id}</p>
                                         <p><strong>Requested By:</strong> ${rso.created_by}</p>
                                         <button onclick="approveRSO(${rso.rso_id})">Approve</button>`;
                        container.appendChild(div);
                    });
                } else {
                    container.textContent = 'No pending RSOs.';
                }
            } catch (err) {
                container.textContent = 'Error loading pending RSOs: ' + err.message;
            }
        }

          async function loadPendingEvents() {
    const container = document.getElementById('pending-events-container');
    container.textContent = 'Loading pending events...';
    try {
      let res = await fetch('API/list_event.php');
      let data = await res.json();
      if (data.success && data.data.length) {
        // Filter pending events (approved == 0)
        const pending = data.data.filter(event => event.approved == 0);
        container.innerHTML = '';
        if (pending.length > 0) {
          pending.forEach(event => {
            const div = document.createElement('div');
            div.classList.add('event');
            div.innerHTML = `<h3 class="event-title">${event.name}</h3>
                             <p class="event-description">${event.description}</p>
                             <p><strong>Category:</strong> ${event.event_category}</p>
                             <p><strong>Date:</strong> ${event.event_date} at ${event.event_time}</p>
                             <p><strong>Visibility:</strong> ${event.event_visibility}</p>
                             <button onclick="approveEvent(${event.event_id})">Approve</button>`;
            container.appendChild(div);
          });
        } else {
          container.textContent = 'No pending events found.';
        }
      } else {
        container.textContent = 'No events found.';
      }
    } catch (err) {
      container.textContent = 'Error loading events: ' + err.message;
    }
  }
  
  async function approveEvent(event_id) {
    try {
      let res = await fetch('API/approve_event.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ event_id })
      });
      let data = await res.json();
      alert(data.message);
      loadPendingEvents();
    } catch (err) {
      alert('Error: ' + err.message);
    }
  }
  
  document.addEventListener('DOMContentLoaded', loadPendingEvents);
        async function approveRSO(rso_id) {
            try {
                let res = await fetch('API/approve_rso.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ rso_id })
                });
                let data = await res.json();
                alert(data.message);
                loadPendingRSOs();
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }
        document.addEventListener('DOMContentLoaded', loadPendingRSOs);
    </script>
</body>
</html>
