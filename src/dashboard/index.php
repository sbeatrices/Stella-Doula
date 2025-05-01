<?php
session_start();
include __DIR__ . '/../utils/db.php';
$db = getDB();

if (!isset($_SESSION['user_id'])) {
    header('Location: ?route=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['user_name'];
$role = $_SESSION['user_role'];

echo "<div class='container'>";
echo "<h2>Welcome, $name!</h2>";
echo "<p>You are logged in as a <strong>$role</strong>.</p>";

if ($role === 'doula') {
    echo "<div class='links'>";
    echo "<a href='?route=doula-profile'>Manage Profile</a><br>";
    echo "<a href='?route=doula-services'>My Services</a><br>";
    echo "<a href='?route=availability'>My Availability</a><br><br>";
    echo "<a href='?route=generate-invoice'>Generate Invoice</a><br>";
    echo "</div>";

    // Handle mark as complete
    if (isset($_GET['mark_complete'])) {
        $bookingId = $_GET['mark_complete'];
        $stmt = $db->prepare("UPDATE bookings SET completed = 1 WHERE id = ? AND doula_id = ?");
        $stmt->execute([$bookingId, $user_id]);
        header("Location: ?route=dashboard");
        exit;
    }

    // Handle cancellation
    if (isset($_GET['cancel_booking'])) {
        $bookingId = $_GET['cancel_booking'];
        $stmt = $db->prepare("DELETE FROM bookings WHERE id = ? AND doula_id = ?");
        $stmt->execute([$bookingId, $user_id]);
        header("Location: ?route=dashboard");
        exit;
    }

    // Fetch bookings
    $stmt = $db->prepare("
        SELECT 
            bookings.id,
            bookings.booking_date,
            bookings.start_time,
            bookings.end_time,
            bookings.completed,
            services.title AS service_title,
            users.name AS client_name
        FROM bookings
        JOIN services ON bookings.service_id = services.id
        JOIN users ON bookings.client_id = users.id
        WHERE bookings.doula_id = ?
        ORDER BY bookings.booking_date ASC, bookings.start_time ASC
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Upcoming Bookings</h3>";
    if (count($bookings) > 0) {
        echo "<table class='bookings-table'>";
        echo "<tr><th>Client</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr>";
        foreach ($bookings as $booking) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($booking['client_name']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['service_title']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['booking_date']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['start_time']) . " – " . htmlspecialchars($booking['end_time']) . "</td>";
            echo "<td>" . ($booking['completed'] ? 'Completed' : 'Pending') . "</td>";
            echo "<td>";
            if (!$booking['completed']) {
                echo "<a href='?route=dashboard&mark_complete=" . $booking['id'] . "' class='action-button'>Mark as Complete</a> | ";
                echo "<a href='?route=dashboard&cancel_booking=" . $booking['id'] . "' onclick='return confirm(\"Cancel this booking?\");' class='action-button cancel'>Cancel</a>";
            } else {
                echo "-";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No bookings yet.</p>";
    }

} else {
    // Client view
    echo "<div class='client-links'>";
    echo "<a href='?route=browse-doulas'>Find a Doula</a><br>";
    echo "<a href='?route=my-bookings'>My Bookings</a><br>";
    echo "</div>";
}

echo "<br><a href='?route=logout' class='logout-button'>Logout</a>";
echo "</div>";
?>

<!-- Add styles for the dashboard page -->
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

    p {
        font-size: 1.2rem;
        margin-bottom: 20px;
    }

    .links a,
    .client-links a {
        font-size: 1.1rem;
        color: #6fa3f9;
        text-decoration: none;
        margin-right: 20px;
        display: inline-block;
    }

    .links a:hover,
    .client-links a:hover {
        text-decoration: underline;
    }

    .bookings-table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }

    .bookings-table th,
    .bookings-table td {
        padding: 10px;
        border: 1px solid #e0e0e0;
        text-align: left;
    }

    .bookings-table th {
        background-color: #f5f5f5;
    }

    .action-button {
        background-color: #6fa3f9;
        color: white;
        padding: 8px 15px;
        text-decoration: none;
        border-radius: 5px;
        margin-right: 10px;
        font-size: 1rem;
    }

    .action-button:hover {
        background-color: #5a8cd9;
    }

    .action-button.cancel {
        background-color: #f44336;
    }

    .action-button.cancel:hover {
        background-color: #d32f2f;
    }

    .logout-button {
        background-color: #8e44ad;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        font-size: 1.1rem;
    }

    .logout-button:hover {
        background-color: #7d3c99;
    }
</style>
