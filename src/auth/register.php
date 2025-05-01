<?php
require_once __DIR__ . '/../utils/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    try {
        $stmt->execute([$name, $email, $password, $role]);
        echo "<div class='success'>Registration successful! <a href='?route=login'>Login here</a></div>";
    } catch (PDOException $e) {
        echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f9f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background: #ffffff;
            border: 1px solid #e0dede;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.05);
            width: 320px;
        }
        h2 {
            text-align: center;
            margin-bottom: 1rem;
            color: #5d5c61;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #d8d8d8;
            border-radius: 8px;
            font-size: 1rem;
        }
        label {
            margin-right: 10px;
            font-size: 0.95rem;
            color: #5d5c61;
        }
        input[type="radio"] {
            margin-right: 5px;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #a3d2ca;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            color: #fff;
            cursor: pointer;
        }
        button:hover {
            background-color: #92c7bd;
        }
        .success, .error {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.95rem;
        }
        .success {
            color: #3c9d9b;
        }
        .error {
            color: #d66a6a;
        }
    </style>
</head>
<body>
    <form method="POST" action="?route=register">
        <h2>Register</h2>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <div style="margin-bottom: 1rem;">
            <label for="role_client">Client</label>
            <input type="radio" id="role_client" name="role" value="client" checked>

            <label for="role_doula">Doula</label>
            <input type="radio" id="role_doula" name="role" value="doula">
        </div>

        <button type="submit">Register</button>
    </form>
</body>
</html>
