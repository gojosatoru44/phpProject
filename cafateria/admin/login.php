<?php
require_once '../config/database.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Fetch admin user
    $sql = "SELECT * FROM users WHERE username = '$username' AND role = 'admin'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Since you are using a PLAIN password (123456) — NO HASH.
        if ($password === $user['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'];

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }

    } else {
        $error = 'Invalid username or password';
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Campus Cafeteria</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>🔐 Admin Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                </div>
            </form>
            
            <p style="text-align: center; margin-top: 20px;">
                <small>Use: ADMIN / 123456</small><br>
                <a href="../index.php">← Back to Home</a>
            </p>
        </div>
    </div>
</body>
</html>
