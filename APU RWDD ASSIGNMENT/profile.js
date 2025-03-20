function fetchProfileData() {
    fetch('get_profile.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            document.getElementById('profileName').textContent = data.name;
            document.getElementById('profileEmail').textContent = data.email;

            // Generate Avatar Initials
            const initials = data.name.split(' ').map(n => n.charAt(0)).join('');
            document.getElementById('profileAvatar').textContent = initials;
        })
        .catch(error => console.error('Error fetching profile:', error));
}

document.addEventListener('DOMContentLoaded', fetchProfileData);
