<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?> | UlanAlert</title>
    <script>
        var savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light' || savedTheme === 'dark') {
            document.documentElement.dataset.theme = savedTheme;
        }
    </script>
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
            <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode" aria-pressed="false">
                <span class="theme-toggle-sun" aria-hidden="true">☀</span>
                <span class="theme-toggle-moon" aria-hidden="true">☾</span>
                <span class="theme-toggle-thumb" aria-hidden="true"></span>
            </button>
            <?php if (isset($_SESSION['user_id'])) { ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['name']); ?>)</a>
            <?php } else { ?>
                <a class="btn" href="login.php">Login</a>
            <?php } ?>
        </nav>
    </header>
    <script>
        var themeToggle = document.getElementById('theme-toggle');
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
        var currentTheme = document.documentElement.dataset.theme ||
            (prefersDark.matches ? 'dark' : 'light');
        document.documentElement.dataset.theme = currentTheme;

        function updateThemeToggle(theme) {
            themeToggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
            themeToggle.setAttribute('aria-label', 'Switch to ' + (theme === 'dark' ? 'light' : 'dark') + ' mode');
        }

        function applyTheme(theme) {
            currentTheme = theme;
            document.documentElement.dataset.theme = theme;
            updateThemeToggle(theme);
        }

        window.setWeatherTheme = function (theme) {
            if (!localStorage.getItem('theme')) {
                applyTheme(theme);
            }
        };

        updateThemeToggle(currentTheme);
        themeToggle.addEventListener('click', function () {
            applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
            localStorage.setItem('theme', currentTheme);
        });
    </script>
    <main>