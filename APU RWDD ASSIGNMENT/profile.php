<?php
require 'db.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Track the referrer for back button functionality
$referrer = $_SERVER['HTTP_REFERER'] ?? '';
if (!empty($referrer) && strpos($referrer, $_SERVER['HTTP_HOST']) !== false) {
    if (!str_contains($referrer, 'edit_profile.php')) {
        $_SESSION['last_page'] = $referrer; // Only store if it's not edit_profile.php
    }
}

// Default back location
$back_location = $_SESSION['last_page'] ?? 'dashboard.php';
// Use the stored referrer if available
if (isset($_SESSION['last_page'])) {
    $back_location = $_SESSION['last_page'];
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch user profile data
    $sql = "SELECT name, email, phone, location, bio, job_title, skills FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }

    // Extract user details
    $name = htmlspecialchars($user['name']);
    $email = htmlspecialchars($user['email']);
    $phone = htmlspecialchars($user['phone'] ?? 'Not provided');
    $location = htmlspecialchars($user['location'] ?? 'Not provided');
    $bio = htmlspecialchars($user['bio'] ?? 'No bio available.');
    $job_title = htmlspecialchars($user['job_title'] ?? 'No job title');
    $skills = !empty($user['skills']) ? explode(',', $user['skills']) : [];
    
    // Generate avatar initials
    $nameParts = explode(" ", $name);
    $initials = "";
    foreach ($nameParts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Productivity App</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #1f2937;
            color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .profile-container {
            width: 100%;
            max-width: 1000px;
            background-color: #111827;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .profile-header {
            position: relative;
            height: 200px;
            background: linear-gradient(to right, #3b82f6, #4f46e5);
        }

        .profile-avatar {
            position: absolute;
            bottom: -50px;
            left: 40px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #374151;
            border: 5px solid #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
        }

        .profile-actions {
            position: absolute;
            bottom: 20px;
            right: 30px;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 10;
        }

        .btn {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-left: 10px;
        }

        .btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .btn-primary {
            background-color: #3b82f6;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .profile-content {
            padding: 70px 40px 40px;
        }

        .profile-name {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .profile-title {
            color: #9ca3af;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .profile-bio {
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-card {
            background-color: #1f2937;
            border-radius: 8px;
            padding: 15px;
        }

        .detail-label {
            color: #9ca3af;
            font-size: 0.9rem;
            margin-bottom: 6px;
        }

        .detail-value {
            font-weight: 500;
        }

        .skills-section {
            margin-top: 30px;
        }

        .section-title {
            margin-bottom: 15px;
            color: #d1d5db;
            font-size: 1.2rem;
        }

        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .skill-tag {
            background-color: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="back-button">
                <button class="btn" onclick="goBack()">← Back</button>
            </div>
            <div class="profile-avatar"><?php echo $initials; ?></div>
            <div class="profile-actions">
                <button class="btn">Settings</button>
                <button class="btn btn-primary" onclick="location.href='edit_profile.php'">Edit Profile</button>
            </div>
        </div>
        <div class="profile-content">
            <h1 class="profile-name"><?php echo $name; ?></h1>
            <div class="profile-title"><?php echo $job_title; ?></div>
            <p class="profile-bio"><?php echo $bio; ?></p>
            
            <div class="profile-details">
                <div class="detail-card">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><?php echo $email; ?></div>
                </div>
                <div class="detail-card">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value"><?php echo $phone; ?></div>
                </div>
                <div class="detail-card">
                    <div class="detail-label">Location</div>
                    <div class="detail-value"><?php echo $location; ?></div>
                </div>
            </div>
            
            <div class="skills-section">
                <h2 class="section-title">Skills</h2>
                <div class="skills-list">
                    <?php foreach ($skills as $skill): ?>
                        <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function goBack() {
            window.location.href = '<?php echo $back_location; ?>';
        }
    </script>
</body>
</html>