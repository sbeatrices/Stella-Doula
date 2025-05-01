<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doula') {
    echo "Access denied. You must be logged in as a doula to view this page.";
    exit;
}

include __DIR__ . '/../utils/db.php';
$db = getDB();

$doula_id = $_SESSION['user_id'];
$message = '';

// Add availability
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';

    if ($start_date && $end_date && $start_time && $end_time) {
        $stmt = $db->prepare("INSERT INTO availability (doula_id, start_date, end_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$doula_id, $start_date, $end_date, $start_time, $end_time]);
        $message = "Availability added!";
    } else {
        $message = "Please fill in all fields.";
    }
}

// Delete availability
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM availability WHERE id = ? AND doula_id = ?");
    $stmt->execute([$_GET['delete'], $doula_id]);
    $message = "Availability removed.";
}

// Fetch existing availability
$stmt = $db->prepare("SELECT * FROM availability WHERE doula_id = ? ORDER BY start_date ASC, start_time ASC");
$stmt->execute([$doula_id]);
$availability = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Manage Availability</h2>

    <?php if ($message): ?>
        <p><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <form method="POST" class="availability-form">
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" required>

        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" required><br><br>

        <label for="start_time">Start Time:</label>
        <input type="time" name="start_time" required>

        <label for="end_time">End Time:</label>
        <input type="time" name="end_time" required><br><br>

        <button type="submit">Add Availability</button>
    </form>

    <h3>Your Availability</h3>
    <ul>
        <?php foreach ($availability as $slot): ?>
            <li>
                <?= htmlspecialchars($slot['start_date']) ?> to <?= htmlspecialchars($slot['end_date']) ?>,
                <?= htmlspecialchars($slot['start_time']) ?> – <?= htmlspecialchars($slot['end_time']) ?>
                <a href="?route=doula-availability&delete=<?= $slot['id'] ?>" onclick="return confirm('Are you sure you want to delete this availability?');">[Delete]</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <a href="?route=dashboard" class="secondary-button">Back to Dashboard</a>
</div>

<!-- Add styles for the manage availability page -->
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f9f9f9;
        color: #333;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 800px;
        margin: 30px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        font-size: 2.5rem;
        color: #5a6a74;
    }

    form {
        margin-top: 20px;
    }

    label {
        font-size: 1.1rem;
        display: block;
        margin-bottom: 5px;
    }

    input[type="date"], input[type="time"] {
        font-size: 1rem;
        padding: 8px;
        margin: 5px 0 10px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
        width: 100%;
        box-sizing: border-box;
    }

    button {
        background-color: #8e44ad;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 1rem;
        cursor: pointer;
        border: none;
    }

    button:hover {
        background-color: #7d3c99;
    }

    h3 {
        font-size: 2rem;
        margin-top: 20px;
        color: #5a6a74;
    }

    ul {
        list-style: none;
        padding: 0;
    }

    li {
        margin: 10px 0;
        font-size: 1.1rem;
    }

    .secondary-button {
        display: inline-block;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        font-size: 1rem;
        margin-top: 20px;
        background-color: #8e44ad;
        color: white;
    }

    .secondary-button:hover {
        background-color: #7d3c99;
    }
</style>
