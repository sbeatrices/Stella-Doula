<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doula') {
    echo "Access denied. You must be logged in as a doula to view this page.";
    exit;
}

$user_id = $_SESSION['user_id'];

include __DIR__ . '/../utils/db.php';
$db = getDB();

// Handle form submission
$updated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $bio = $_POST['bio'] ?? '';

    $stmt = $db->prepare("UPDATE users SET name = ?, bio = ? WHERE id = ?");
    $stmt->execute([$name, $bio, $user_id]);

    // Redirect with success flag
    header("Location: ?route=doula-profile&updated=1");
    exit;
}

// Fetch user data after any updates
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if redirected with update success
$updated = isset($_GET['updated']);
?>

<div class="profile-container">
    <h2>Manage Profile</h2>

    <?php if ($updated): ?>
        <div class="success-msg">Profile updated successfully!</div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="name">Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label for="bio">Bio:</label>
        <textarea name="bio" rows="5" required><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>

        <button type="submit">Update Profile</button>
    </form>

    <div class="nav-links">
        <a href="?route=dashboard">← Back to Dashboard</a> |
        <a href="?route=view-profile&doula_id=<?= $user_id ?>">View My Public Profile</a>
    </div>
</div>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background-color: #fefefe;
        color: #333;
        margin: 0;
        padding: 0;
    }

    .profile-container {
        max-width: 700px;
        margin: 40px auto;
        padding: 30px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.06);
    }

    h2 {
        font-size: 1.8rem;
        margin-bottom: 20px;
        color: #6a4c93;
    }

    label {
        display: block;
        margin: 12px 0 6px;
        font-weight: 600;
    }

    input[type="text"],
    textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
    }

    textarea {
        resize: vertical;
    }

    button {
        background-color: #6a4c93;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        margin-top: 15px;
        cursor: pointer;
        font-size: 1rem;
    }

    button:hover {
        background-color: #5a3c83;
    }

    .success-msg {
        background-color: #d7f5d7;
        color: #2c6e2c;
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 15px;
        border: 1px solid #a3e4a3;
    }

    .nav-links {
        margin-top: 20px;
        font-size: 0.95rem;
    }

    .nav-links a {
        color: #6a4c93;
        text-decoration: none;
    }

    .nav-links a:hover {
        text-decoration: underline;
    }
</style>
