<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $sql = "INSERT INTO users (nickname, username, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $nickname, $username, $password);
        $stmt->execute();
        $_SESSION['success'] = 'Pengguna berhasil ditambahkan. Silahkan login';
        header('Location: register.php');
        exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $_SESSION['error'] = 'Username sudah ada, silakan pilih username lain.';
        } else {
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        }
        header('Location: register.php');
        exit;
    }
}

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        input[type="password"] {
            width: calc(100% - 20px);
            padding: 5px;
            margin-top: 5px;
        }
        .login {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Tambah Pengguna</h1>
    <?php if ($error): ?>
        <script>
            alert("<?php echo $error; ?>");
        </script>
    <?php endif; ?>
    <?php if ($success): ?>
        <script>
            alert("<?php echo $success; ?>");
        </script>
    <?php endif; ?>
    <form method="POST" action="register.php">
        <label for="nickname">Nickname:</label>
        <input type="text" name="nickname" id="nickname" required><br>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>
        <button type="submit">Tambah Pengguna</button>
        <div class="login">
            <a href="login.php">Sudah punya akun? Login!</a>
        </div>
    </form>
</body>
</html>
