<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','super_admin'])) {
    header("Location: events.php");
    exit;
}
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Panel</title>
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
        <h1>Admin Panel</h1>
        
        <?php if($user_role === 'super_admin'): ?>
        <!-- Super Admin functionalities only -->
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

            <section id="create-rso">
                <h2>Create RSO</h2>
                <form id="create-rso-form">
                    <label for="rso-name">RSO Name:</label>
                    <input type="text" id="rso-name" name="name" required>
                    <br>
                    <label for="rso-description">Description:</label>
                    <textarea id="rso-description" name="description" required></textarea>
                    <br>
                    <label for="university-id">University ID:</label>
                    <input type="number" id="university-id" name="university_id" required>
                    <br>
                    <button type="submit">Create RSO</button>
                </form>
                <p id="rso-message"></p>
            </section>
        <?php endif; ?>

        <!-- These sections are available to both admins and super_admins -->
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
</body>
</html>
