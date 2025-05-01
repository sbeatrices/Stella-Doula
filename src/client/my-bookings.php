<?php
session_start();
include __DIR__ . '/../utils/db.php';
$db = getDB();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header('Location: ?route=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['user_name'];

echo "<div class='container'>";
echo "<h1>Welcome, $name!</h1>";
echo "<p>You are logged in as a <strong>Client</strong>.</p>";
echo "<a href='?route=browse-doulas' class='primary-button'>Find a Doula</a><br>";
echo "<a href='?route=logout' class='secondary-button'>Logout</a><br><br>";

// Handle cancellation request
if (isset($_GET['cancel_booking'])) {
    $bookingId = $_GET['cancel_booking'];

    $stmt = $db->prepare("DELETE FROM bookings WHERE id = ? AND client_id = ?");
    $stmt->execute([$bookingId, $user_id]);

    header('Location: ?route=my-bookings');
    exit;
}

// Fetch client's bookings
$stmt = $db->prepare("
    SELECT 
        bookings.id,
        bookings.booking_date,
        bookings.start_time,
        bookings.end_time,
        bookings.completed,
        services.title AS service_title,
        users.name AS doula_name
    FROM bookings
    JOIN services ON bookings.service_id = services.id
    JOIN users ON bookings.doula_id = users.id
    WHERE bookings.client_id = ?
    ORDER BY bookings.booking_date ASC, bookings.start_time ASC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display bookings
echo "<h3>My Bookings</h3>";
if (count($bookings) > 0) {
    echo "<table class='styled-table'>";
    echo "<tr><th>Doula</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th><th>Invoice</th></tr>";
    foreach ($bookings as $booking) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($booking['doula_name']) . "</td>";
        echo "<td>" . htmlspecialchars($booking['service_title']) . "</td>";
        echo "<td>" . htmlspecialchars($booking['booking_date']) . "</td>";
        echo "<td>" . htmlspecialchars($booking['start_time']) . " – " . htmlspecialchars($booking['end_time']) . "</td>";
        echo "<td>" . ($booking['completed'] ? 'Completed' : 'Pending') . "</td>";
        echo "<td>";
        if (!$booking['completed']) {
            echo "<a href='?route=my-bookings&cancel_booking=" . $booking['id'] . "' class='cancel-button' onclick='return confirm(\"Are you sure you want to cancel this booking?\");'>Cancel</a>";
        } else {
            echo "-";
        }
        echo "</td>";
        echo "<td>";
        if ($booking['completed']) {
            echo "<a href='?route=view-invoice&id=" . $booking['id'] . "' class='view-invoice-button'>View Invoice</a>";
        } else {
            echo "-";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>You have no bookings yet.</p>";
}

echo "</div>";
?>

<!-- Add styles for the my-bookings page -->
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

    h1 {
        font-size: 2.5rem;
        color: #5a6a74;
    }

    h3 {
        font-size: 1.8rem;
        color: #6c757d;
        margin-top: 20px;
    }

    p {
        font-size: 1.1rem;
        color: #333;
    }

    .primary-button, .secondary-button, .cancel-button, .view-invoice-button {
        display: inline-block;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        font-size: 1rem;
        margin: 10px 0;
    }

    .primary-button {
        background-color: #6fa3f9;
        color: white;
    }

    .primary-button:hover {
        background-color: #5a8cd9;
    }

    .secondary-button {
        background-color: #8e44ad;
        color: white;
    }

    .secondary-button:hover {
        background-color: #7d3c99;
    }

    .cancel-button {
        background-color: #e74c3c;
        color: white;
        padding: 8px 15px;
    }

    .cancel-button:hover {
        background-color: #c0392b;
    }

    .view-invoice-button {
        background-color: #2ecc71;
        color: white;
    }

    .view-invoice-button:hover {
        background-color: #27ae60;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    .styled-table {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
    }

    .styled-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .styled-table tr:hover {
        background-color: #f1f1f1;
    }

</style>
