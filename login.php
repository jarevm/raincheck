<?php
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // save the user info in the session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        // admins go to the dashboard
        if ($user['role'] == 'ADMIN') {
            header('Location: admin.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = 'Wrong email or password.';
    }
}

$page_title = 'Login';
include 'header.php';
?>
<div class="box">
    <h2>Login</h2>
    <?php if ($error != '') { ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>
    <form method="post">
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <br><br>
        <button type="submit">Login</button>
        <a href="register.php">Create account</a>
    </form>
</div>
<?php include 'footer.php'; ?>
