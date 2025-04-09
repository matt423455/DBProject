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
async function filterEvents(events) {
    const filteredEvents = [];

    // Process events sequentially or concurrently
    for (let event of events) {
        const visibility = event.event_visibility;

        if (visibility === 'public') {
            filteredEvents.push(event);
        } else if (visibility === 'private') {
            // Make sure currentUser is available
            if (currentUser && currentUser.email && currentUser.email.toLowerCase().includes("ucf")) {
                filteredEvents.push(event);
            }
        } else if (visibility === 'RSO') {
            // RSO events: Check if the user is logged in and a member of the event's RSO
            if (!event.rso_id || !currentUser || !currentUser.user_id) {
                continue;
            }
            // Await the result of the membership check
            const isMember = await checkMembership(currentUser.user_id, event.rso_id);
            if (isMember) {
                filteredEvents.push(event);
            }
        }
    }

    return filteredEvents;
}

async function fetchCombinedEvents() {
    try {
        // Fetch events concurrently
        let [ourRes, ucfRes] = await Promise.all([
            fetch("API/event.php"),
            fetch("API/fetch_ucf_events.php")
        ]);
        let ourData = await ourRes.json();
        let ucfData = await ucfRes.json();

        let combinedEvents = [];
        if (ourData.success && Array.isArray(ourData.data)) {
            combinedEvents = combinedEvents.concat(ourData.data);
        }
        if (ucfData.success && Array.isArray(ucfData.data)) {
            combinedEvents = combinedEvents.concat(ucfData.data);
        }

        // Optional: sort events by date and time
        combinedEvents.sort((a, b) => {
            return new Date((a.event_date || '') + " " + (a.event_time || '')) -
                new Date((b.event_date || '') + " " + (b.event_time || ''));
        });

        // Ensure the currentUser is loaded first.
        if (!currentUser) {
            // Optionally, wait until fetchUserData is complete
            // You might want to call await fetchUserData() here if you refactor it to return a promise.
            console.error("Current user data not loaded.");
            return;
        }

        // Asynchronously filter events
        const validEvents = await filterEvents(combinedEvents);

        // Display events
        let container = document.getElementById("events-container");
        container.innerHTML = "";
        if (validEvents.length) {
            validEvents.forEach(event => {
                try {
                    const name = event.name || "Untitled Event";
                    const category = event.event_category || "General";
                    const description = event.description?.substring(0, 100) || "No description.";
                    const date = event.event_date || "Unknown Date";
                    const time = event.event_time || "Unknown Time";

                    let div = document.createElement("div");
                    div.classList.add("event");
                    div.innerHTML = `<h3>${name} (${category})</h3>
                                     <p>${description}...</p>
                                     <p><strong>Date:</strong> ${date} at ${time}</p>`;

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

document.addEventListener("DOMContentLoaded", async () => {
    // First fetch the user data and then combined events.
    await fetchUserData();
    await fetchCombinedEvents();
});
