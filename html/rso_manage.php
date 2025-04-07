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
  <title>Manage Your RSOs</title>
  <link rel="stylesheet" href="styles/events.css">
  <style>
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
    .section {
      margin: 20px auto;
      max-width: 800px;
      text-align: left;
    }
  </style>
  <script>
    // Global variable for current RSO from drop-down.
    let currentRSO = <?php echo json_encode($managedRSOs[0]['rso_id']); ?>;
    
    // Example privilege check (update your logic as needed)
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

    async function fetchLocations(){
       try {
            let res = await fetch("API/getLocations.php");
            let data = await res.json();
            if (data.success && data.data) {
                 let select = document.getElementById("location-id");
                 data.data.forEach(loc => {
                    let option = document.createElement("option");
                    option.value = loc.location_id;
                    option.textContent = loc.name;
                    select.appendChild(option);
                 });
            }
       } catch(err) {
             console.error("Error fetching locations", err);
       }
    }
    document.addEventListener("DOMContentLoaded", fetchLocations);

    
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
      // Optionally reload members for the new RSO
    }
    
    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('event-form').addEventListener('submit', createEvent);
    });
  </script>
</head>
<body>
  <div class="user-info">
      <span>Hello, <?php echo htmlspecialchars($username); ?></span>
  </div>
  <div class="top-right-links">
      <a href="rso_user.php">Back to RSOs</a>
      <a href="events.html">Back to Events</a>
  </div>
  <h1>Manage Your RSOs</h1>
  
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
  
  <!-- Section for managing members (omitted here for brevity) -->
  <div class="section">
      <h2>Create New Event</h2>
      <form id="event-form">
          <input type="text" id="event-title" placeholder="Event Title" required><br>
          <input type="text" id="event-category" placeholder="Event Category" required><br>
          <textarea id="event-details" placeholder="Event Details" required></textarea><br>
          <input type="date" id="event-date" required><br>
          <input type="time" id="event-time" required><br>
          <label for="location-id">Select Location:</label>
          <select id="location-id" required>
              <option value="">-- Select Location --</option>
          </select><br>
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
