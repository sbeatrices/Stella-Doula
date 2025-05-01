<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doula') {
    echo "Access denied. You must be logged in as a doula.";
    exit;
}

$user_id = $_SESSION['user_id'];

require_once __DIR__ . '/../utils/db.php';
$db = getDB();

// Add new service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;

    if (!empty($title)) {
        $stmt = $db->prepare("INSERT INTO services (doula_id, title, description, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $description, $price]);
        header("Location: ?route=doula-services");
        exit;
    } else {
        echo "<p class='error-msg'>Title is required.</p>";
    }
}

// Delete service
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM services WHERE id = ? AND doula_id = ?");
    $stmt->execute([$_GET['delete'], $user_id]);
    header("Location: ?route=doula-services");
    exit;
}

// Fetch service to edit
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ? AND doula_id = ?");
    $stmt->execute([$_GET['edit'], $user_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$service) {
        echo "<p class='error-msg'>Service not found or access denied.</p>";
        exit;
    }
}

// Update service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_service'])) {
    $stmt = $db->prepare("UPDATE services SET title = ?, description = ?, price = ? WHERE id = ? AND doula_id = ?");
    $stmt->execute([
        $_POST['title'],
        $_POST['description'],
        floatval($_POST['price']),
        $_POST['service_id'],
        $user_id
    ]);
    header("Location: ?route=doula-services");
    exit;
}

// Get all services
$stmt = $db->prepare("SELECT * FROM services WHERE doula_id = ?");
$stmt->execute([$user_id]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="services-container">
    <h2>Manage My Services</h2>

    <div class="form-section">
        <h3><?= isset($service) ? "Edit Service" : "Add New Service" ?></h3>
        <form method="POST">
            <?php if (isset($service)): ?>
                <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
            <?php endif; ?>
            <label>Title:</label>
            <input type="text" name="title" value="<?= htmlspecialchars($service['title'] ?? '') ?>" required>

            <label>Description:</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($service['description'] ?? '') ?></textarea>

            <label>Price ($):</label>
            <input type="text" name="price" value="<?= htmlspecialchars($service['price'] ?? '') ?>" required>

            <button type="submit" name="<?= isset($service) ? 'update_service' : 'add_service' ?>">
                <?= isset($service) ? 'Update Service' : 'Add Service' ?>
            </button>
        </form>
    </div>

    <div class="list-section">
        <h3>My Existing Services</h3>
        <?php if ($services): ?>
            <?php foreach ($services as $s): ?>
                <div class="service-card">
                    <h4><?= htmlspecialchars($s['title']) ?></h4>
                    <p><?= nl2br(htmlspecialchars($s['description'])) ?></p>
                    <p><strong>$<?= number_format($s['price'], 2) ?></strong></p>
                    <div class="card-actions">
                        <a href="?route=doula-services&edit=<?= $s['id'] ?>">Edit</a>
                        <a href="?route=doula-services&delete=<?= $s['id'] ?>" onclick="return confirm('Delete this service?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No services added yet.</p>
        <?php endif; ?>
    </div>

    <a href="?route=dashboard" class="back-link">← Back to Dashboard</a>
</div>

<style>
    .services-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background: #fff;
        border-radius: 12px;
        font-family: 'Segoe UI', sans-serif;
    }

    h2, h3 {
        color: #6a4c93;
    }

    label {
        display: block;
        margin-top: 15px;
        font-weight: 600;
    }

    input[type="text"],
    textarea {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    button {
        margin-top: 15px;
        background: #6a4c93;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }

    button:hover {
        background: #5a3c83;
    }

    .service-card {
        background: #f8f4fb;
        border: 1px solid #dcd0ef;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .card-actions a {
        margin-right: 15px;
        text-decoration: none;
        color: #6a4c93;
    }

    .card-actions a:hover {
        text-decoration: underline;
    }

    .form-section,
    .list-section {
        margin-bottom: 40px;
    }

    .back-link {
        text-decoration: none;
        color: #6a4c93;
        font-weight: 600;
    }

    .error-msg {
        color: red;
        margin-bottom: 10px;
    }
</style>
