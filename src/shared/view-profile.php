<?php
session_start();
include __DIR__ . '/../utils/db.php';
$db = getDB();

// Get doula ID from query param
if (!isset($_GET['doula_id'])) {
    echo "No doula specified.";
    exit;
}

$doula_id = $_GET['doula_id'];

// Fetch doula info
$stmt = $db->prepare("SELECT name, bio FROM users WHERE id = ? AND role = 'doula'");
$stmt->execute([$doula_id]);
$doula = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doula) {
    echo "Doula not found.";
    exit;
}

// Fetch doula's services
$stmt = $db->prepare("SELECT id, title, description, price FROM services WHERE doula_id = ?");
$stmt->execute([$doula_id]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch doula's availability
$stmt = $db->prepare("SELECT start_date, end_date, start_time, end_time FROM availability WHERE doula_id = ? ORDER BY start_date ASC, start_time ASC");
$stmt->execute([$doula_id]);
$availability = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle booking form (client only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_service']) && isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'client') {
    $client_id = $_SESSION['user_id'];
    $service_id = $_POST['service_id'];
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Fetch doula_id from service to validate
    $stmt = $db->prepare("SELECT doula_id FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($service && $service['doula_id'] == $doula_id) {
        // 1. Check for overlapping bookings
        $stmt = $db->prepare("SELECT COUNT(*) FROM bookings 
            WHERE doula_id = ? AND booking_date = ? AND (
                (start_time < ? AND end_time > ?) OR 
                (start_time >= ? AND start_time < ?)
            )");
        $stmt->execute([
            $doula_id, $booking_date,
            $end_time, $start_time,
            $start_time, $end_time
        ]);
    
        if ($stmt->fetchColumn() > 0) {
            $error = "This time slot is already booked. Please choose a different time.";
        } else {
            // 2. Check if booking is within any availability slot
            $stmt = $db->prepare("SELECT COUNT(*) FROM availability 
                WHERE doula_id = ? AND start_date <= ? AND end_date >= ? 
                  AND start_time <= ? AND end_time >= ?");
            $stmt->execute([
                $doula_id, $booking_date, $booking_date,
                $start_time, $end_time
            ]);
    
            if ($stmt->fetchColumn() === 0) {
                $error = "This time is outside the doula’s availability.";
            } else {
                // 3. Proceed with booking
                $stmt = $db->prepare("INSERT INTO bookings (client_id, doula_id, service_id, booking_date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$client_id, $doula_id, $service_id, $booking_date, $start_time, $end_time]);
                $success = "Booking submitted successfully!";
            }
        }
    } else {
        $error = "Invalid service selected.";
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($doula['name']) ?>'s Profile</title>
    <style>
        body {
            background-color: #f4f4f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            padding: 20px;
            margin: 0;
        }

        h2, h3 {
            color: #ff6f61;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
        }

        label {
            font-weight: bold;
        }

        input, select, button {
            font-size: 1rem;
            padding: 10px;
            margin-top: 5px;
            border-radius: 8px;
            border: 1px solid #ccc;
            width: 100%;
        }

        button {
            background-color: #ff6f61;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background-color: #e64a44;
        }

        /* Success and Error Messages */
        .success-message {
            color: green;
            font-weight: bold;
        }

        .error-message {
            color: red;
            font-weight: bold;
        }

        a.back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #ccc;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        a.back-link:hover {
            background-color: #bbb;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><?= htmlspecialchars($doula['name']) ?>'s Profile</h2>
    <p><strong>Bio:</strong> <?= nl2br(htmlspecialchars($doula['bio'])) ?></p>

    <h3>Services</h3>
    <?php if (count($services) > 0): ?>
        <ul>
            <?php foreach ($services as $service): ?>
                <li>
                    <strong><?= htmlspecialchars($service['title']) ?></strong><br>
                    <?= htmlspecialchars($service['description']) ?><br>
                    $<?= number_format((float)$service['price'], 2) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>This doula has not listed any services yet.</p>
    <?php endif; ?>

    <h3>Availability</h3>
    <?php if (count($availability) > 0): ?>
        <ul>
            <?php foreach ($availability as $slot): ?>
                <li>
                    <?= htmlspecialchars($slot['start_date']) ?> to <?= htmlspecialchars($slot['end_date']) ?>,
                    <?= htmlspecialchars($slot['start_time']) ?> – <?= htmlspecialchars($slot['end_time']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>This doula has not set any availability yet.</p>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <p class="success-message"><?= $success ?></p>
    <?php elseif (isset($error)): ?>
        <p class="error-message"><?= $error ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'client' && count($services) > 0): ?>
        <h3>Book a Service</h3>
        <form method="POST" action="?route=view-profile&doula_id=<?= $doula_id ?>">
            <label for="service_id">Select a Service:</label><br>
            <select name="service_id" required>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['title']) ?> — $<?= number_format($service['price'], 2) ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="booking_date">Date:</label><br>
            <input type="date" name="booking_date" required><br><br>

            <label for="start_time">Start Time:</label><br>
            <input type="time" name="start_time" required><br><br>

            <label for="end_time">End Time:</label><br>
            <input type="time" name="end_time" required><br><br>

            <button type="submit" name="book_service">Book Now</button>
        </form>
    <?php endif; ?>

    <br>
    <a href="?route=dashboard" class="back-link">Back</a>
</div>

</body>
</html>
