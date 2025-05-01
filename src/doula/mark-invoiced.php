<?php
session_start();
include __DIR__ . '/../utils/db.php';
$db = getDB();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ?route=login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Mark bookings as invoiced
if (isset($_POST['mark_invoiced'])) {
    // Update bookings to mark them as invoiced
    $stmt = $db->prepare("UPDATE bookings SET invoiced = 1 WHERE doula_id = ? AND completed = 1 AND invoiced = 0");
    $stmt->execute([$user_id]);

    // Redirect to the invoice page to refresh the state
    header('Location: ?route=generate-invoice');
    exit;
}

?>
