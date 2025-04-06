<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: events.php");
    exit;
}
$user_role = $_SESSION['role'];
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
        <a class="leave-admin" href="events.php">Leave Admin Panel</a>
        <h1>Super Admin Panel</h1>
        
        <!-- Only super admins can approve RSO requests -->
        <section id="approve-rso">
            <h2>Approve RSO Requests</h2>
            <div id="pending-rso-container">Loading pending RSOs...</div>
        </section>
        
        <!-- Other sections available to both admins and super admins -->
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

        <section id="list-rsos">
            <h2>List of RSOs</h2>
            <div id="rso-list-container">Loading RSOs...</div>
        </section>
    </div>
    <script>
    async function loadPendingRSOs() {
        const container = document.getElementById('pending-rso-container');
        container.textContent = 'Loading pending RSOs...';
        try {
            let res = await fetch('API/list_rso.php');
            let data = await res.json();
            if (data.success && data.data.length) {
                // Filter pending RSOs (status == 2)
                const pending = data.data.filter(rso => rso.status == 2);
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
