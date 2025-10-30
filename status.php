<?php


require 'config.php';
require 'functions.php';

$client = getYouTubeClient();
$youtube = new Google_Service_YouTube($client);

$api_status = 'Not Connected';
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $api_status = 'Connected';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>DJ YouTube Dashboard - Status</title>
    <link rel="icon" href="https://www.youtube.com/s/desktop/d743f786/img/favicon.ico">
    <link rel="stylesheet" href="assets/pma.css">
    <link rel="stylesheet" href="assets/dark.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="topbar">
        <img src="https://s.ytimg.com/yts/img/yt_1200-vflhSIVnY.png" alt="Logo" height="24">
        <h1>YT DASHBOARD</h1>
    </div>
    <div class="container">
        <div class="sidebar">
            <ul class="videos-list">
                <li class="video-item-header"><i class="fas fa-database"></i> Menu</li>
                <li class="video-item"><a href="index.php"><i class="fas fa-video"></i> Videos</a></li>
                <li class="video-item"><a href="status.php" class="active"><i class="fas fa-server"></i> Status</a></li>
            </ul>
        </div>
        <div class="main">
            <h1><i class="fas fa-server"></i> Server Status</h1>
            
            <h2><i class="fab fa-youtube"></i> YouTube API Status</h2>
            <p><?= $api_status ?></p>

            <h2><i class="fab fa-php"></i> PHP Information</h2>
            <p>PHP Version: <?= phpversion() ?></p>

            <h2><i class="fas fa-info-circle"></i> Server Information</h2>
            <table class="comments">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SERVER as $key => $value): ?>
                        <tr>
                            <td><?= htmlspecialchars($key) ?></td>
                            <td><?= is_array($value) ? print_r($value, true) : htmlspecialchars($value) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="footer">
        <button id="theme-switcher">Toggle Dark Mode</button>
    </div>
    <script>
        const themeSwitcher = document.getElementById('theme-switcher');
        const body = document.body;

        themeSwitcher.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });

        if (localStorage.getItem('theme') === 'dark') {
            body.classList.add('dark-mode');
        }
    </script>
</body>
</html>