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

// Fetch completed, un-invoiced bookings
$stmt = $db->prepare("
    SELECT 
        bookings.id,
        bookings.booking_date,
        bookings.start_time,
        bookings.end_time,
        services.title AS service_title,
        services.price,
        users.name AS client_name
    FROM bookings
    JOIN services ON bookings.service_id = services.id
    JOIN users ON bookings.client_id = users.id
    WHERE bookings.doula_id = ? AND bookings.completed = 1 AND bookings.invoiced = 0
    ORDER BY bookings.booking_date ASC, bookings.start_time ASC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1>Generate Invoice</h1>

    <?php if (count($bookings) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; ?>
                <?php foreach ($bookings as $booking): ?>
                    <?php $total += $booking['price']; ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['client_name']) ?></td>
                        <td><?= htmlspecialchars($booking['service_title']) ?></td>
                        <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                        <td><?= htmlspecialchars($booking['start_time']) ?> – <?= htmlspecialchars($booking['end_time']) ?></td>
                        <td>$<?= number_format((float)$booking['price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="4"><strong>Total</strong></td>
                    <td><strong>$<?= number_format((float)$total, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <form method="POST" action="?route=mark-invoiced" class="invoice-form">
            <button type="submit" name="mark_invoiced" value="1">Mark as Invoiced</button>
        </form>
    <?php else: ?>
        <p>No completed, un-invoiced bookings found.</p>
    <?php endif; ?>

    <a href="?route=dashboard" class="secondary-button">Back to Dashboard</a>
</div>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background-color: #f9f9f9;
        margin: 0;
        padding: 0;
        color: #333;
    }

    .container {
        max-width: 900px;
        margin: 40px auto;
        padding: 25px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    h1 {
        font-size: 2.2rem;
        margin-bottom: 20px;
        color: #5a6a74;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    th, td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    th {
        background-color: #f0e6f6;
        color: #5a3d73;
    }

    .total-row {
        background-color: #f6f6f6;
    }

    .invoice-form {
        text-align: right;
        margin-top: 15px;
    }

    button {
        background-color: #8e44ad;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        font-size: 1rem;
        cursor: pointer;
    }

    button:hover {
        background-color: #7d3c99;
    }

    .secondary-button {
        display: inline-block;
        margin-top: 20px;
        background-color: #8e44ad;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 6px;
        font-size: 1rem;
    }

    .secondary-button:hover {
        background-color: #7d3c99;
    }

    p {
        font-size: 1.1rem;
    }
</style>
