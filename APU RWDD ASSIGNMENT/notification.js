document.addEventListener("DOMContentLoaded", function () {
    const notificationIcon = document.getElementById("notification-icon");
    const notificationDot = document.getElementById("notification-dot");
    const notificationDropdown = document.getElementById("notification-dropdown");
    const notificationList = document.getElementById("notification-list");
    const overlay = document.getElementById("friend-request-overlay");
    const requestMessage = document.getElementById("request-message");
    const acceptBtn = document.getElementById("accept-request");
    const declineBtn = document.getElementById("decline-request");
    const closeOverlayBtn = document.getElementById("close-overlay");

    let selectedRequestId = null;

    // ✅ Toggle dropdown when clicking the bell icon
    notificationIcon.addEventListener("click", function (event) {
        event.stopPropagation();
        notificationDropdown.classList.toggle("active");

        if (notificationDropdown.classList.contains("active")) {
            loadNotifications();
        }
    });

    // ✅ Hide dropdown when clicking outside
    document.addEventListener("click", function (event) {
        if (!notificationIcon.contains(event.target) && !notificationDropdown.contains(event.target)) {
            notificationDropdown.classList.remove("active");
        }
    });

    // ✅ Fetch friend requests
    function loadNotifications() {
        fetch("get_notifications.php")
            .then(response => response.json())
            .then(data => {
                notificationList.innerHTML = "";
                if (data.length > 0) {
                    notificationDot.classList.remove("hidden");
                    data.forEach(request => {
                        let listItem = document.createElement("li");
                        listItem.textContent = `${request.name} sent a friend request`;
                        listItem.dataset.id = request.id;
                        listItem.addEventListener("click", function () {
                            openOverlay(request.id, request.name);
                        });
                        notificationList.appendChild(listItem);
                    });
                } else {
                    notificationDot.classList.add("hidden");
                    notificationList.innerHTML = "<li>No new friend requests</li>";
                }
            });
    }

    // ✅ Show overlay when clicking request
    function openOverlay(id, name) {
        selectedRequestId = id;
        requestMessage.textContent = `${name} wants to be your friend.`;
        overlay.classList.add("active");
    }

    // ✅ Handle accept/decline
    function handleRequest(action) {
        fetch("handle_friend_request.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ request_id: selectedRequestId, action: action })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                overlay.classList.remove("active");
                loadNotifications(); // Refresh notification list
                loadFriends(); // Refresh sidebar friend list
            }
        });
    }

    acceptBtn.addEventListener("click", () => handleRequest("accept"));
    declineBtn.addEventListener("click", () => handleRequest("decline"));
    closeOverlayBtn.addEventListener("click", () => overlay.classList.remove("active"));

    // ✅ Load friends dynamically
    function loadFriends() {
        fetch("get_friends.php")
            .then(response => response.json())
            .then(data => {
                let friendList = document.getElementById("friend-list");
                friendList.innerHTML = "";
                data.forEach(friend => {
                    let li = document.createElement("li");
                    li.textContent = `${friend.name}`;
                    friendList.appendChild(li);
                });
            });
    }

    // ✅ Load notifications on page load
    loadNotifications();
    loadFriends();
});
