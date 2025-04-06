<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.html");
  exit;
}
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>RSO Listings</title>
  <link rel="stylesheet" href="styles/events.css">
  <script>
  async function loadRSOs() {
      const container = document.getElementById('rso-list-container');
      container.textContent = 'Loading RSOs...';
      try {
          let res = await fetch('API/list_rso.php');
          let data = await res.json();
          console.log("RSO data from API:", data);
          if (data.success && data.data.length) {
              // Only approved RSOs (is_active = 1)
              const approved = data.data.filter(rso => rso.is_active == 1);
              console.log("Approved RSOs:", approved);
              container.innerHTML = '';
              if (approved.length > 0) {
                  approved.forEach(rso => {
                      const div = document.createElement('div');
                      div.classList.add('event');
                      div.innerHTML = `<h3 class="event-title">${rso.name}</h3>
                                        <p class="event-description">${rso.description}</p>
                                        <p><strong>University ID:</strong> ${rso.university_id}</p>
                                        <p><strong>Created By:</strong> ${rso.created_by}</p>
                                        <button onclick="joinRSO(${rso.rso_id})">Join RSO</button>
                                        <button onclick="leaveRSO(${rso.rso_id})">Leave RSO</button>`;
                      container.appendChild(div);
                  });
              } else {
                  container.textContent = 'No approved RSOs found.';
              }
          } else {
              container.textContent = 'No RSOs found.';
          }
      } catch (err) {
          container.textContent = 'Error loading RSOs: ' + err.message;
      }
  }
  
  async function joinRSO(rso_id) {
      try {
          let res = await fetch('API/join_rso.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ rso_id })
          });
          let data = await res.json();
          alert(data.message);
          loadRSOs();
      } catch (err) {
          alert('Error: ' + err.message);
      }
  }
  
  async function leaveRSO(rso_id) {
      try {
          let res = await fetch('API/leave_rso.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ rso_id })
          });
          let data = await res.json();
          alert(data.message);
          loadRSOs();
      } catch (err) {
          alert('Error: ' + err.message);
      }
  }
  
  document.addEventListener('DOMContentLoaded', loadRSOs);
  </script>
  <style>
    /* Inline styles for top-right links */
    .top-right-links {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    .top-right-links a {
        margin-left: 10px;
        color: #333;
        text-decoration: none;
        font-weight: bold;
    }
  </style>
</head>
<body>
  <!-- User info positioned at top left -->
  <div class="user-info">
      <span>Hello, <?php echo htmlspecialchars($username); ?></span>
  </div>
  <!-- Top right links -->
  <div class="top-right-links">
      <a href="request_rso_page.php">Request New RSO</a>
      <a href="events.html">Back to Events</a>
  </div>
  <h1>RSO Listings</h1>
  <div id="rso-list-container">Loading RSOs...</div>
</body>
</html>
