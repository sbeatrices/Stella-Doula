<?php
require_once __DIR__ . '/../utils/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: ?route=dashboard');
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fef6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: #ffffff;
            padding: 2em;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 350px;
            width: 100%;
        }

        h1 {
            margin-bottom: 1em;
            color: #555;
        }

        h2 {
            margin-bottom: 1em;
            color: #555;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.75em;
            margin-bottom: 1em;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 0.75em;
            background-color: #ffd6e0;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #fcbecf;
        }
        .error {
            color: #d9534f;
            margin-bottom: 1em;
        }
        a {
            color: #888;
            font-size: 0.9em;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>POCKET DOULA</h1>
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="?route=login">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="?route=register">Register here</a></p>
    </div>
</body>
</html>
