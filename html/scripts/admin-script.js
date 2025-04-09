// scripts/admin-script.js

document.addEventListener('DOMContentLoaded', () => {
    // Update user role form submission
    const updateUserRoleForm = document.getElementById('update-user-role-form');
    updateUserRoleForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const userId = document.getElementById('user-id').value.trim();
        const newRole = document.getElementById('new-role').value;
        const messageEl = document.getElementById('user-role-message');
        messageEl.textContent = '';

        try {
            const res = await fetch('API/update_user_role.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, new_role: newRole })
            });
            const data = await res.json();
            messageEl.textContent = data.message;
        } catch (err) {
            messageEl.textContent = 'Error: ' + err.message;
        }
    });

    // Delete event form submission
    const deleteEventForm = document.getElementById('delete-event-form');
    deleteEventForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const eventId = document.getElementById('event-id').value.trim();
        const messageEl = document.getElementById('event-message');
        messageEl.textContent = '';

        try {
            const res = await fetch('API/delete_event.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ event_id: eventId })
            });
            const data = await res.json();
            messageEl.textContent = data.message;
        } catch (err) {
            messageEl.textContent = 'Error: ' + err.message;
        }
    });

    // Add user to RSO form submission
    const addUserToRsoForm = document.getElementById('add-user-to-rso-form');
    addUserToRsoForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const rsoId = document.getElementById('add-rso-id').value.trim();
        const userId = document.getElementById('add-user-id').value.trim();
        const messageEl = document.getElementById('add-rso-message');
        messageEl.textContent = '';

        try {
            const res = await fetch('API/add_user_to_rso.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ rso_id: rsoId, user_id: userId })
            });
            const data = await res.json();
            messageEl.textContent = data.message;
        } catch (err) {
            messageEl.textContent = 'Error: ' + err.message;
        }
    });

    // Remove user from RSO form submission
    const removeUserFromRsoForm = document.getElementById('remove-user-from-rso-form');
    removeUserFromRsoForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const rsoId = document.getElementById('remove-rso-id').value.trim();
        const userId = document.getElementById('remove-user-id').value.trim();
        const messageEl = document.getElementById('remove-rso-message');
        messageEl.textContent = '';

        try {
            const res = await fetch('API/remove_user_from_rso.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ rso_id: rsoId, user_id: userId })
            });
            const data = await res.json();
            messageEl.textContent = data.message;
        } catch (err) {
            messageEl.textContent = 'Error: ' + err.message;
        }
    });

    // Load RSOs list on page load
    async function loadRSOs() {
        const container = document.getElementById('rso-list-container');
        container.textContent = 'Loading RSOs...';
        try {
            const res = await fetch('API/list_rso.php');
            const data = await res.json();
            if (data.success && data.data.length) {
                container.innerHTML = '';
                data.data.forEach(rso => {
                    const div = document.createElement('div');
                    div.classList.add('event'); // using the same style as events
                    div.innerHTML = `<h3 class="event-title">${rso.name}</h3>
                                     <p class="event-description">${rso.description}</p>
                                     <p><strong>University ID:</strong> ${rso.university_id}</p>
                                     <p><strong>Created By:</strong> ${rso.created_by}</p>`;
                    container.appendChild(div);
                });
            } else {
                container.textContent = 'No RSOs found.';
            }
        } catch (err) {
            container.textContent = 'Error loading RSOs: ' + err.message;
        }
    }
    loadRSOs();

    // RSO creation form handling with member enforcement
    const createRsoForm = document.getElementById('create-rso-form');
    if (createRsoForm) {
        createRsoForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('rso-name').value.trim();
            const description = document.getElementById('rso-description').value.trim();
            const universityId = document.getElementById('university-id').value.trim();

            const members = [];
            for (let i = 1; i <= 5; i++) {
                const email = document.getElementById(`member${i}`).value.trim();
                if (email) members.push(email);
            }

            const messageEl = document.getElementById('create-rso-message');
            messageEl.textContent = '';

            if (members.length < 5) {
                messageEl.textContent = 'Please enter 5 student emails to activate the RSO.';
                return;
            }

            try {
                const res = await fetch('API/create_rso.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, description, university_id: universityId, members })
                });
                const data = await res.json();
                messageEl.textContent = data.message;
            } catch (err) {
                messageEl.textContent = 'Error: ' + err.message;
            }
        });
    }

});
