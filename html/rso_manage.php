<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.html");
  exit;
}
require __DIR__ . '/API/config.php';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Guest';

// Query RSOs that the user manages (role 'leader' or 'officer')
$query = "SELECT RSO.rso_id, RSO.name, RSO.is_active FROM RSO 
          JOIN RSO_Members ON RSO.rso_id = RSO_Members.rso_id 
          WHERE RSO_Members.user_id = ? AND RSO_Members.role IN ('leader','officer')";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$managedRSOs = [];
while ($row = $result->fetch_assoc()){
    $managedRSOs[] = $row;
}
$stmt->close();

// If user does not manage any RSO, redirect back.
if (empty($managedRSOs)) {
    header("Location: rso_user.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage RSO</title>
  <link rel="stylesheet" href="styles/events.css">
  <style>
    /* Reuse your global styles; add top-left and top-right positioning */
    .user-info {
      position: absolute;
      top: 10px;
      left: 10px;
      font-size: 18px;
      font-weight: bold;
      background: rgba(0,0,0,0.1);
      padding: 5px 10px;
      border-radius: 5px;
    }
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
    /* Simple layout for management sections */
    .section {
      margin: 20px auto;
      max-width: 800px;
      text-align: left;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    table, th, td {
      border: 1px solid #ccc;
    }
    th, td {
      padding: 8px;
      text-align: left;
    }
    button {
      background-color: #3561a9;
      color: white;
      padding: 5px 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }
    button:hover {
      background-color: #2b4d85;
    }
  </style>
  <script>
// Global variable to hold the current managed RSO id.
let currentRSO = <?php echo json_encode($managedRSOs[0]['rso_id']); ?>;
// Also store the user role in the RSO (leader/officer) for the current RSO.
// For simplicity, we assume if user is leader in any RSO, they have leader privileges.
// Otherwise, they are an officer.
let userPrivilege = 'officer'; 
<?php
  $isLeader = false;
  foreach ($managedRSOs as $rso) {
      if (stripos($rso['name'], 'leader') !== false) {
          $isLeader = true;
          break;
      }
  }
  echo "userPrivilege = " . ($isLeader ? "'leader'" : "'officer'") . ";";
?>

async function loadMembers() {
  const container = document.getElementById('members-container');
  container.textContent = 'Loading members...';
  try {
    let res = await fetch('API/rso_get_members.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ rso_id: currentRSO })
    });
    let data = await res.json();
    if (data.success) {
      container.innerHTML = '';
      if (data.members && data.members.length > 0) {
        let table = document.createElement('table');
        let header = document.createElement('tr');
        header.innerHTML = '<th>User ID</th><th>Username</th><th>Role</th><th>Actions</th>';
        table.appendChild(header);
        data.members.forEach(member => {
          let row = document.createElement('tr');
          row.innerHTML = `<td>${member.user_id}</td>
                           <td>${member.username}</td>
                           <td>${member.role}</td>
                           <td>
                              <button onclick="removeMember(${member.user_id})">Remove</button>
                              ${ userPrivilege === 'leader' ? `<button onclick="promoteMember(${member.user_id})">Promote to Officer</button>` : '' }
                           </td>`;
          table.appendChild(row);
        });
        container.appendChild(table);
      } else {
        container.textContent = 'No members found.';
      }
    } else {
      container.textContent = data.message;
    }
  } catch (err) {
    container.textContent = 'Error loading members: ' + err.message;
  }
}

async function removeMember(memberId) {
  if (!confirm("Are you sure you want to remove this member?")) return;
  try {
    let res = await fetch('API/rso_remove_member.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ rso_id: currentRSO, user_id: memberId })
    });
    let data = await res.json();
    alert(data.message);
    loadMembers();
  } catch (err) {
    alert('Error: ' + err.message);
  }
}

async function promoteMember(memberId) {
  try {
    let res = await fetch('API/rso_promote_member.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ rso_id: currentRSO, user_id: memberId })
    });
    let data = await res.json();
    alert(data.message);
    loadMembers();
  } catch (err) {
    alert('Error: ' + err.message);
  }
}

async function createEvent(event) {
  event.preventDefault();
  const title = document.getElementById('event-title').value.trim();
  const category = document.getElementById('event-category').value.trim();
  const details = document.getElementById('event-details').value.trim();
  const eventDate = document.getElementById('event-date').value.trim();
  const eventTime = document.getElementById('event-time').value.trim();
  const locationId = document.getElementById('location-id').value.trim();
  const contactPhone = document.getElementById('contact-phone').value.trim();
  const contactEmail = document.getElementById('contact-email').value.trim();
  const visibility = document.getElementById('event-visibility').value.trim();
  const messageEl = document.getElementById('event-msg');
  messageEl.textContent = '';
  
  if (!title || !category || !details || !eventDate || !eventTime || !locationId || !contactPhone || !contactEmail || !visibility) {
    messageEl.textContent = 'All fields are required.';
    return;
  }
  
  try {
    let res = await fetch('API/create_event.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        rso_id: currentRSO,
        name: title,
        event_category: category,
        description: details,
        event_date: eventDate,
        event_time: eventTime,
        location_id: locationId,
        contact_phone: contactPhone,
        contact_email: contactEmail,
        event_visibility: visibility
      })
    });
    let data = await res.json();
    messageEl.textContent = data.message;
    if (data.success) {
      document.getElementById('event-form').reset();
    }
  } catch (err) {
    messageEl.textContent = 'Error: ' + err.message;
  }
}

function changeManagedRSO(selectObj) {
  currentRSO = selectObj.value;
  loadMembers();
}

document.addEventListener('DOMContentLoaded', () => {
  loadMembers();
});

  </script>
</head>
<body>
  <!-- Top left user info -->
  <div class="user-info">
      <span>Hello, <?php echo htmlspecialchars($username); ?></span>
  </div>
  <!-- Top right links -->
  <div class="top-right-links">
      <a href="rso_user.php">Back to RSOs</a>
      <a href="events.html">Back to Events</a>
  </div>
  <h1>Manage Your RSOs</h1>
  
  <!-- If user manages multiple RSOs, allow selection -->
  <?php if(count($managedRSOs) > 1): ?>
  <div class="section">
    <label for="rso-select">Select RSO:</label>
    <select id="rso-select" onchange="changeManagedRSO(this)">
      <?php foreach($managedRSOs as $rso): ?>
        <option value="<?php echo $rso['rso_id']; ?>">
          <?php echo htmlspecialchars($rso['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  
  <!-- Section for managing members -->
  <div class="section">
      <h2>RSO Members</h2>
      <div id="members-container">Loading members...</div>
  </div>
  
  <!-- Section for creating new events -->
<div class="section">
  <h2>Create New Event</h2>
  <form id="event-form" onsubmit="createEvent(event)">
      <input type="text" id="event-title" placeholder="Event Title" required><br>
      <input type="text" id="event-category" placeholder="Event Category" required><br>
      <textarea id="event-details" placeholder="Event Details" required></textarea><br>
      <input type="date" id="event-date" required><br>
      <input type="time" id="event-time" required><br>
      <input type="number" id="location-id" placeholder="Location ID" required><br>
      <input type="text" id="contact-phone" placeholder="Contact Phone" required><br>
      <input type="email" id="contact-email" placeholder="Contact Email" required><br>
      <select id="event-visibility" required>
          <option value="">Select Visibility</option>
          <option value="public">Public</option>
          <option value="private">Private</option>
          <option value="RSO">RSO</option>
      </select><br>
      <button type="submit">Create Event</button>
      <p id="event-msg"></p>
  </form>
</div>
</body>
</html>
