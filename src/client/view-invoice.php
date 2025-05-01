<?php
session_start();
include __DIR__ . '/../utils/db.php';
$db = getDB();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header('Location: ?route=login');
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    echo "<p>Missing booking ID.</p>";
    exit;
}

$booking_id = $_GET['id'];

// Fetch booking details
$stmt = $db->prepare("
    SELECT 
        bookings.id,
        bookings.booking_date,
        bookings.start_time,
        bookings.end_time,
        bookings.completed,
        bookings.created_at,
        services.title AS service_title,
        services.description AS service_description,
        services.price AS service_price,
        users.name AS doula_name,
        users.email AS doula_email
    FROM bookings
    JOIN services ON bookings.service_id = services.id
    JOIN users ON bookings.doula_id = users.id
    WHERE bookings.id = ? AND bookings.client_id = ?
");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "<p>Invoice not found or access denied.</p>";
    exit;
}

if (!$booking['completed']) {
    echo "<p>This invoice is not available until the service is marked as completed.</p>";
    exit;
}

// Render invoice
echo "<div class='container'>";
echo "<h2>Invoice for Booking #" . htmlspecialchars($booking['id']) . "</h2>";
echo "<p><strong>Doula:</strong> " . htmlspecialchars($booking['doula_name']) . " (" . htmlspecialchars($booking['doula_email']) . ")</p>";
echo "<p><strong>Service:</strong> " . htmlspecialchars($booking['service_title']) . "</p>";
echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($booking['service_description'])) . "</p>";
echo "<p><strong>Date:</strong> " . htmlspecialchars($booking['booking_date']) . "</p>";
echo "<p><strong>Time:</strong> " . htmlspecialchars($booking['start_time']) . " – " . htmlspecialchars($booking['end_time']) . "</p>";
echo "<p><strong>Status:</strong> Completed</p>";
echo "<p><strong>Price:</strong> $" . number_format($booking['service_price'], 2) . "</p>";
echo "<br><a href='?route=my-bookings' class='secondary-button'>&larr; Back to My Bookings</a>";
echo "</div>";
?>

<!-- Add styles for the invoice page -->
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

    p {
        font-size: 1.1rem;
        color: #333;
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
