<?php
session_start();
include 'koneksi.php';

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect to index.php or another appropriate page
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, nickname, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nickname'] = $row['nickname'];
            $_SESSION['login_success'] = true; // Set login success flag
            header('Location: index.php');
            exit;
        } else {
            $error = "Username atau kata sandi salah.";
        }
    } else {
        $error = "Username atau kata sandi salah.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        input[type="password"] {
            width: calc(100% - 20px);
            padding: 5px;
            margin-top: 5px;
        }
        .register {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Login</h1>
    <form method="POST" action="login.php">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>
        <button type="submit">Login</button>
        <div class="register">
            <a href="register.php">Belum punya akun? Register!</a>
        </div>
    </form>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <?php if ($error != ''): ?>
        <script>
            toastr.error("<?php echo $error; ?>");
        </script>
    <?php endif; ?>
</body>
</html>
