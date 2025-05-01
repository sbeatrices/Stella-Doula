<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    echo "Access denied. You must be logged in as a client to view this page.";
    exit;
}

include __DIR__ . '/../utils/db.php';
$db = getDB();

// Fetch all doulas
$stmt = $db->query("SELECT id, name, bio FROM users WHERE role = 'doula'");
$doulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Browse Doulas</h2>

    <?php if (count($doulas) > 0): ?>
        <ul class="doula-list">
            <?php foreach ($doulas as $doula): ?>
                <li class="doula-item">
                    <strong><?= htmlspecialchars($doula['name']) ?></strong><br>
                    <?= nl2br(htmlspecialchars($doula['bio'])) ?><br>
                    <a href="?route=view-profile&doula_id=<?= $doula['id'] ?>" class="view-profile-button">View Profile</a>
                </li>
                <br>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No doulas available at the moment.</p>
    <?php endif; ?>

    <a href="?route=dashboard" class="back-button">Back to Dashboard</a>
</div>

<!-- Add styles for the browse doulas page -->
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f9f9f9;
        color: #333;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 600px;
        margin: 30px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        font-size: 2rem;
        color: #5a6a74;
        margin-bottom: 10px;
    }

    .doula-list {
        list-style-type: none;
        padding: 0;
    }

    .doula-item {
        font-size: 1.1rem;
        margin-bottom: 20px;
    }

    .doula-item strong {
        color: #5a6a74;
        font-size: 1.2rem;
    }

    .view-profile-button {
        background-color: #6fa3f9;
        color: white;
        padding: 8px 15px;
        text-decoration: none;
        border-radius: 5px;
        margin-top: 10px;
        display: inline-block;
    }

    .view-profile-button:hover {
        background-color: #5a8cd9;
    }

    .back-button {
        background-color: #8e44ad;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        font-size: 1.1rem;
        margin-top: 20px;
        display: inline-block;
    }

    .back-button:hover {
        background-color: #7d3c99;
    }
</style>
