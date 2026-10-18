<?php
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = 'USER';
    $code = '';

    if (isset($_POST['role']) && $_POST['role'] == 'ADMIN') {
        $role = 'ADMIN';
        $code = $_POST['code'];
    }

    if ($name == '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = 'Enter a name, a valid email and a password of 6 or more characters.';
    } else if ($role == 'ADMIN' && $code != ADMIN_CODE) {
        $error = 'Wrong admin access code.';
    } else {
        // check if the email is already used
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'That email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $name, $email, $hash, $role);
            $stmt->execute();
            header('Location: login.php');
            exit;
        }
    }
}

$page_title = 'Register';
include 'header.php';
?>
<div class="box">
    <h2>Create account</h2>
    <?php if ($error != '') { ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>
    <form method="post" onsubmit="return checkPasswords()">
        <label>Account type</label>
        <select name="role" id="role" onchange="showCodeBox()">
            <option value="USER">Student / Faculty / Staff</option>
            <option value="ADMIN">Admin (Guard / Admin Office)</option>
        </select>
        <div id="codebox" style="display: none;">
            <label>Admin access code</label>
            <input type="password" name="code" id="code">
        </div>
        <label>Full name</label>
        <input type="text" name="name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" id="password" required>
        <label>Confirm password</label>
        <input type="password" id="confirm" required>
        <br><br>
        <button type="submit">Register</button>
        <a href="login.php">I already have an account</a>
    </form>
</div>
<script src="register.js"></script>
<?php include 'footer.php'; ?>
