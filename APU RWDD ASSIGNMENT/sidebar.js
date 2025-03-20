window.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const menuIcon = document.querySelector('.menu-icon');
    const sidebarFooter = document.querySelector('.sidebar-footer');
    const toggleButton = document.getElementById('toggle-sidebar-mode');
    const navView = document.getElementById('nav-view');
    const friendView = document.getElementById('friend-view');
    const friendList = document.getElementById('friend-list');
    const addFriendBtn = document.getElementById('add-friend-btn');
    const friendEmailInput = document.getElementById('friend-email');
    const currentUserId = '<?= $_SESSION["user_id"] ?>';

    let isOpen = false;
    let activeFriendId = null;
    let friendPolling =null;

    // Check if elements exist
    if (!sidebar) console.error("Sidebar element not found!");
    if (!menuIcon) console.error("Menu icon not found!");
    if (!sidebarFooter) console.error("Sidebar footer (user profile) not found!");
    if (!toggleButton) console.error("Toggle button not found!");
    if (!navView) console.error("Navigation view not found!");
    if (!friendView) console.error("Friend view not found!");
    if (!friendList) console.error("Friend list container not found!");
    if (!addFriendBtn) console.error("Add friend button not found!");
    if (!friendEmailInput) console.error("Friend email input not found!");

    // Function to toggle sidebar open
    const toggleSidebar = () => {
        isOpen = !isOpen;
        sidebar.classList.toggle('open', isOpen);
        mainContent.style.marginLeft = isOpen ? '220px' : '0';
    };

    // Function to check if we're in mobile view
    const isMobileView = () => window.innerWidth <= 768;

    // Desktop hover functionality
    document.addEventListener('mousemove', (event) => {
        if (!isMobileView()) {
            if (event.clientX < 50 && !isOpen) {
                toggleSidebar();
            } else if (event.clientX > 250 && isOpen) {
                toggleSidebar();
            }
        }
    });

    // Menu icon click for all views, especially important for mobile
    if (menuIcon) {
        menuIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });
    }

    // Close sidebar when clicking outside
    document.addEventListener('click', (event) => {
        if (isOpen && !event.target.closest('.sidebar') && !event.target.closest('.menu-icon')) {
            toggleSidebar();
        }
    });

    // Handle responsive behavior when resizing
    window.addEventListener('resize', () => {
        if (isMobileView()) {
            isOpen = false;
            sidebar.classList.remove('open');
            mainContent.style.marginLeft = '0';
        }
    });

    // Function to toggle sidebar views (Navigation & Friends)
    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            navView.classList.toggle('hidden');
            friendView.classList.toggle('hidden');
            if (!friendView.classList.contains('hidden')) {
                loadFriends();
            }
        });
    }

    // Fetch and load friends list dynamically
    function loadFriends() {
        fetch('get_friends_status.php')
            .then(response => response.json())
            .then(data => {
                console.log("Fetched friends:", data); // Debugging
    
                const friendList = document.getElementById("friend-list");
                if (!friendList) {
                    console.error("Friend list element not found!");
                    return;
                }
    
                friendList.innerHTML = ""; // Clear old content
    
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(friend => {
                        console.log("Processing friend:", friend); // Debugging
    
                        if (!friend.name || !friend.status) {
                            console.error("Missing properties in friend:", friend);
                            return;
                        }
    
                        const friendItem = document.createElement("div");
                        friendItem.classList.add("nav-item");

                        friendItem.addEventListener("click", () => {
                            openFriendChat(friend.user_id, friend.name);
                        });                        
    
                        // Set online/offline class
                        const statusClass = friend.status === "online" ? "online" : "offline";
                        friendItem.innerHTML = `
                            <i>👤</i> ${friend.name} 
                            <span class="friend-status ${statusClass}">${friend.status}</span>
                        `;
    
                        friendList.appendChild(friendItem);
                    });
                } else {
                    friendList.innerHTML = "<div class='nav-item'>No friends found</div>";
                }
            })
            .catch(error => {
                console.error("Error fetching friends:", error);
                document.getElementById("friend-list").innerHTML = "<div class='nav-item'>Error loading friends</div>";
            });
    }
    
    // Auto-refresh every 10 seconds
    setInterval(loadFriends, 10000);    
    
    // Call function when the sidebar is opened (or on page load)
    loadFriends();                

    // Add friend request
    addFriendBtn.addEventListener("click", () => {
        const friendEmail = friendEmailInput.value.trim();
        if (friendEmail === "") {
            alert("Please enter a friend's email.");
            return;
        }

        fetch("add_friend.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `friend_email=${encodeURIComponent(friendEmail)}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.success || data.error);
            if (data.success) {
                loadFriends(); // Refresh friend list
                friendEmailInput.value = "";
            }
        })
        .catch(error => console.error("Error adding friend:", error));
    });

    // Fetch and load user profile dynamically
    function loadUserProfile() {
        if (!sidebarFooter) {
            console.error("Sidebar footer not found during loadUserProfile");
            return;
        }

        fetch('sidebar.php')
            .then(response => response.text())
            .then(html => {
                if (html.trim() !== "") {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const newFooter = tempDiv.querySelector('.sidebar-footer');

                    if (newFooter) {
                        sidebarFooter.innerHTML = newFooter.innerHTML;
                    } else {
                        const userProfile = tempDiv.querySelector('.user-profile');
                        if (userProfile) {
                            sidebarFooter.innerHTML = userProfile.outerHTML;
                        }
                    }

                    sidebarFooter.style.display = "flex";
                    console.log("Sidebar footer updated:", sidebarFooter.innerHTML);
                } else {
                    console.warn('Sidebar footer content is empty.');
                }
            })
            .catch(error => console.error('Error fetching sidebar profile:', error));
    }

    // Load user profile with a slight delay
    setTimeout(loadUserProfile, 500);

    // Initial load if Friends View is open
    if (!friendView.classList.contains('hidden')) {
        loadFriends();
    }
});
