// Fetch and display user data
async function fetchUserData() {
    try {
        let res = await fetch("API/user.php"); // Adjust API path if needed
        let data = await res.json();

        if (data.success) {
            document.getElementById("user-greeting").textContent = `Hello, ${data.user.username}`;
        }
    } catch (err) {
        console.error("Error fetching user data:", err);
    }
}
document.addEventListener("DOMContentLoaded", fetchUserData);

// Fetch events from the API and populate the container
async function fetchEvents() {
    try {
      // Adjust the URL if your API folder name is different (e.g., "API/events.php")
      let res = await fetch("API/event.php");
      let data = await res.json();
      let container = document.getElementById("events-container");
      container.innerHTML = "";
      if (data.success && data.data.length) {
        data.data.forEach(event => {
          // Create a clickable div for each event
          let div = document.createElement("div");
          div.classList.add("event");
          div.innerHTML = `<h3>${event.name} (${event.event_category})</h3>
                           <p>${event.description.substring(0, 100)}...</p>
                           <p><strong>Date:</strong> ${event.event_date} at ${event.event_time}</p>`;
          // When clicked, redirect to event_detail.html with the event_id in the query string
          div.addEventListener("click", () => {
            window.location.href = "event_detail.html?event_id=" + event.event_id;
          });
          container.appendChild(div);
        });
      } else {
        container.innerHTML = "No events found.";
      }
    } catch (err) {
      document.getElementById("events-container").innerHTML = "Error loading events: " + err.message;
    }
  }
fetchEvents();

async function fetchUCFEvents() {
    try {
        // Call the UCF events importer API
        let res = await fetch("API/fetch_ucf_events.php");
        let data = await res.json();
        let container = document.getElementById("events-container");
        container.innerHTML = "";

        // Display the message returned from the API
        if (data.success) {
            container.innerHTML = `<p>${data.message}</p>`;
        } else {
            container.innerHTML = `<p>Error fetching events: ${data.message}</p>`;
        }
    } catch (err) {
        document.getElementById("events-container").innerHTML = "Error loading events: " + err.message;
    }
}
document.addEventListener("DOMContentLoaded", fetchUCFEvents);

