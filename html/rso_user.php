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
          if (data.success && data.data.length) {
              // Only approved RSOs (status = 1)
              const approved = data.data.filter(rso => rso.status == 1);
              container.innerHTML = '';
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
  
  async function requestRSO(event) {
      event.preventDefault();
      const name = document.getElementById('req-rso-name').value.trim();
      const description = document.getElementById('req-rso-description').value.trim();
      const universityId = document.getElementById('req-university-id').value.trim();
      const messageEl = document.getElementById('req-rso-message');
      messageEl.textContent = '';
      if (!name || !description || !universityId) {
          messageEl.textContent = 'All fields are required.';
          return;
      }
      try {
          let res = await fetch('API/request_rso.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ name, description, university_id: universityId })
          });
          let data = await res.json();
          messageEl.textContent = data.message;
          loadRSOs();
      } catch (err) {
          messageEl.textContent = 'Error: ' + err.message;
      }
  }
  
  document.addEventListener('DOMContentLoaded', loadRSOs);
  </script>
</head>
<body>
  <div class="user-info"><span>Hello, <?php echo htmlspecialchars($username); ?></span></div>
  <h1>RSO Listings</h1>
  <div id="rso-list-container">Loading RSOs...</div>
  <h2>Request New RSO</h2>
  <form onsubmit="requestRSO(event)">
      <input type="text" id="req-rso-name" placeholder="RSO Name" required><br>
      <textarea id="req-rso-description" placeholder="Description" required></textarea><br>
      <input type="number" id="req-university-id" placeholder="University ID" required><br>
      <button type="submit">Request RSO</button>
      <p id="req-rso-message"></p>
  </form>
  <a href="events.php">Back to Events</a>
</body>
</html>
