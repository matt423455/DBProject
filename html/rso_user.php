<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.html");
  exit;
}
// Use the username from the session; if not set, default to 'Guest'
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>RSO Listings</title>
  <link rel="stylesheet" href="styles/events.css">
  <style>
    /* Top left user info is already styled in events.css (.user-info) */
    /* Top right links styling */
    .top-right-links {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    .top-right-links a,
    .top-right-links button {
        margin-left: 10px;
        color: #333;
        text-decoration: none;
        font-weight: bold;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }
    /* Modal styles */
    .modal {
      display: none; /* Hidden by default */
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 20px;
      border-radius: 8px;
      width: 80%;
      max-width: 500px;
      text-align: left;
      position: relative;
    }
    .close-modal {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 24px;
      font-weight: bold;
      color: #333;
      cursor: pointer;
    }
  </style>
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
              const approved = data.data.filter(rso => rso.is_active == 1 || rso.is_active == 0);
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
  
  async function submitRSORequest(event) {
      event.preventDefault();
      const name = document.getElementById('modal-rso-name').value.trim();
      const description = document.getElementById('modal-rso-description').value.trim();
      const universityId = document.getElementById('modal-university-id').value.trim();
      const messageEl = document.getElementById('modal-req-message');
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
          // If successful, close the modal after a short delay.
          if(data.success) {
            setTimeout(closeModal, 1500);
          }
      } catch (err) {
          messageEl.textContent = 'Error: ' + err.message;
      }
  }
  
  function openModal() {
      document.getElementById('request-modal').style.display = 'block';
  }
  
  function closeModal() {
      document.getElementById('request-modal').style.display = 'none';
  }
  
  document.addEventListener('DOMContentLoaded', loadRSOs);
  </script>
</head>
<body>
  <!-- Top left user info -->
  <div class="user-info">
      <span>Hello, <?php echo htmlspecialchars($username); ?></span>
  </div>
  <!-- Top right links -->
  <div class="top-right-links">
      <button onclick="openModal()">Request New RSO</button>
      <a href="events.html">Back to Events</a>
  </div>
  <h1>RSO Listings</h1>
  <div id="rso-list-container">Loading RSOs...</div>
  
  <!-- Modal for Request New RSO -->
  <div id="request-modal" class="modal">
      <div class="modal-content">
          <span class="close-modal" onclick="closeModal()">&times;</span>
          <h2>Request New RSO</h2>
          <form onsubmit="submitRSORequest(event)">
              <input type="text" id="modal-rso-name" placeholder="RSO Name" required><br>
              <textarea id="modal-rso-description" placeholder="Description" required></textarea><br>
              <input type="number" id="modal-university-id" placeholder="University ID" required><br>
              <button type="submit">Submit Request</button>
              <p id="modal-req-message"></p>
          </form>
      </div>
  </div>
</body>
</html>
