// events-script.js

// Fetch and display user data
let currentUser;
async function fetchUserData() {
    try {
        let res = await fetch("API/user.php"); // Adjust API path if needed
        let data = await res.json();

        currentUser = data;

        if (data.success) {
            document.getElementById("user-greeting").textContent = `Hello, ${data.user.username}`;
        }
    } catch (err) {
        console.error("Error fetching user data:", err);
    }
}
document.addEventListener("DOMContentLoaded", fetchUserData);

async function checkMembership(userId, rsoId) {
    try {
        const response = await fetch('checkMembership.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `user_id=${userId}&rso_id=${rsoId}`
        });
        const result = await response.json();
        return result.in_rso;
    } catch (error) {
        console.error('Error checking membership:', error);
        return false;
    }
}

// Fetch and combine our events and UCF events from two API endpoints
async function fetchCombinedEvents() {
    try {
        // Use Promise.all to fetch events from both endpoints concurrently
        let [ourRes, ucfRes] = await Promise.all([
            fetch("API/event.php"),
            fetch("API/fetch_ucf_events.php")  // Make sure this endpoint returns event data in JSON format
        ]);
        let ourData = await ourRes.json();
        let ucfData = await ucfRes.json();

        let combinedEvents = [];
        if (ourData.success && ourData.data && Array.isArray(ourData.data)) {
            combinedEvents = combinedEvents.concat(ourData.data);
        }
        if (ucfData.success && ucfData.data && Array.isArray(ucfData.data)) {
            combinedEvents = combinedEvents.concat(ucfData.data);
        }

        // Optionally sort events by date and time (adjust date/time formatting as needed)
        combinedEvents.sort((a, b) => {
            return new Date((a.event_date || '') + " " + (a.event_time || '')) - new Date((b.event_date || '') + " " + (b.event_time || ''));
        });

        combinedEvents = combinedEvents.filter(event => {
            const visibility = event.event_visibility;
            if (visibility === 'public') {
                return true;
            } else if (visibility === 'private') {
                return currentUser.email &&
                    currentUser.email.toLowerCase().includes("ucf");
            } else if (visibility === 'RSO') {
                // For RSO events, we need to check:
                // 1. The event has a valid rso_id.
                // 2. The current user is logged in (has a user_id).
                // 3. The user is a member of the RSO (their rso_ids array includes event.rso_id).
                if (!event.rso_id || !currentUser.user_id) {
                    return false;
                }

                return (checkMembership(currentUser.user_id, event.rso_id))
            }
            return false;
        });

        let container = document.getElementById("events-container");
        container.innerHTML = "";
        if (combinedEvents.length) {
            combinedEvents.forEach(event => {
                try {
                    // Add fallbacks so nothing crashes
                    const name = event.name || "Untitled Event";
                    const category = event.event_category || "General";
                    const description = event.description?.substring(0, 100) || "No description.";
                    const date = event.event_date || "Unknown Date";
                    const time = event.event_time || "Unknown Time";

                    // Create a clickable div for each event
                    let div = document.createElement("div");
                    div.classList.add("event");
                    div.innerHTML = `<h3>${name} (${category})</h3>
                        <p>${description}...</p>
                        <p><strong>Date:</strong> ${date} at ${time}</p>`;

                    // If an event_id exists, add a click listener to redirect to details page
                    if (event.event_id) {
                        div.addEventListener("click", () => {
                            window.location.href = "event_detail.html?event_id=" + event.event_id;
                        });
                    }
                    container.appendChild(div);
                } catch (err) {
                    console.error("Error rendering event:", err);
                }
            });
        } else {
            container.innerHTML = "No events found.";
        }
    } catch (err) {
        document.getElementById("events-container").innerHTML = "Error loading events: " + err.message;
    }
}
document.addEventListener("DOMContentLoaded", fetchCombinedEvents);
