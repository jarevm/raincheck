<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?> | UlanAlert</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <a class="logo" href="index.php">NU<span>lanAhead</span></a>
        <nav>
            <a href="index.php">Forecast</a>
            <a href="index.php#reports">Reports</a>
            <a href="post.php">Post</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'ADMIN') { ?>
                <a href="admin.php">Admin</a>
            <?php } ?>
            <?php if (isset($_SESSION['user_id'])) { ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['name']); ?>)</a>
            <?php } else { ?>
                <a href="login.php">Login</a>
            <?php } ?>
        </nav>
    </header>
    <main>