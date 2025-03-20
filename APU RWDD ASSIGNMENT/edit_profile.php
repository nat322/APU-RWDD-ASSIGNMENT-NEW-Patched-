<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_title = $_POST['job_title'];
    $bio = $_POST['bio'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $skills = implode(',', array_map('trim', explode(',', $_POST['skills'])));

    try {
        $sql = "UPDATE users SET job_title = :job_title, bio = :bio, phone = :phone, location = :location, skills = :skills WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':job_title' => $job_title,
            ':bio' => $bio,
            ':phone' => $phone,
            ':location' => $location,
            ':skills' => $skills,
            ':user_id' => $user_id
        ]);
        header('Location: profile.php');
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Fetch user details for the form
try {
    $sql = "SELECT job_title, bio, phone, location, skills FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Productivity App</title>
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
            height: 120px;
            background: linear-gradient(to right, #3b82f6, #4f46e5);
            display: flex;
            align-items: center;
            padding-left: 40px;
        }

        .header-title {
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .profile-content {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #d1d5db;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            background-color: #1f2937;
            border: 1px solid #374151;
            border-radius: 6px;
            color: #f3f4f6;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 40px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-secondary {
            background-color: #374151;
            color: #f3f4f6;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .form-hint {
            font-size: 0.85rem;
            color: #9ca3af;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1 class="header-title">Edit Your Profile</h1>
        </div>
        <div class="profile-content">
            <form action="edit_profile.php" method="POST">
                <div class="form-group">
                    <label for="job_title" class="form-label">Job Title</label>
                    <input type="text" id="job_title" name="job_title" class="form-control" value="<?php echo htmlspecialchars($user['job_title'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea id="bio" name="bio" class="form-control"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    <div class="form-hint">Tell us about yourself in a few sentences</div>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="skills" class="form-label">Skills</label>
                    <input type="text" id="skills" name="skills" class="form-control" value="<?php echo htmlspecialchars($user['skills'] ?? ''); ?>">
                    <div class="form-hint">Separate skills with commas (e.g., PHP, JavaScript, UI Design)</div>
                </div>

                <div class="form-actions">
                    <a href="profile.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>