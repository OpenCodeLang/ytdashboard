<?php
require 'config.php';
require 'functions.php';


$client = getYouTubeClient();
$youtube = new Google_Service_YouTube($client);

// --- Admin Login ---
if (!isset($_SESSION['logged_in'])) {
    if (isset($_POST['user'], $_POST['pass']) &&
        $_POST['user'] === ADMIN_USER && $_POST['pass'] === ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>DJ YouTube Dashboard - Login</title>
        <link rel="icon" href="https://www.youtube.com/s/desktop/d743f786/img/favicon.ico">
        <link rel="stylesheet" href="assets/pma.css">
        <link rel="stylesheet" href="assets/dark.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            .login-form {
                width: 300px;
                padding: 20px;
                border: 1px solid #c0c0c0;
                background: #e9e9e9;
            }
            .login-form input {
                width: 100%;
                padding: 10px;
                margin-bottom: 10px;
                box-sizing: border-box;
            }
            .login-form button {
                width: 100%;
                padding: 10px;
                background: #6c757d;
                color: white;
                border: none;
                cursor: pointer;
            }
            body.dark-mode .login-form {
                background: #1a1a1a;
                border-color: #444;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="topbar" style="justify-content: center;">
                <img src="https://s.ytimg.com/yts/img/yt_1200-vflhSIVnY.png" alt="Logo" height="24">
                <h1>YT DASHBOARD</h1>
            </div>
            <form method="post" class="login-form">
                <input type="text" name="user" placeholder="Username" required>
                <input type="password" name="pass" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
        <script>
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark-mode');
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// --- OAuth flow ---
if (!isset($_SESSION['access_token'])) {
    if (isset($_GET['code'])) {
        $client->authenticate($_GET['code']);
        $_SESSION['access_token'] = $client->getAccessToken();
        header('Location: index.php');
        exit;
    } else {
        header('Location: ' . $client->createAuthUrl());
        exit;
    }
}
$client->setAccessToken($_SESSION['access_token']);

// --- Delete comment ---
if (isset($_GET['delete_comment'])) {
    if (!isset($_GET['csrf_token']) || !validateCsrfToken($_GET['csrf_token'])) {
        die('Invalid CSRF token.');
    }
    deleteComment($youtube, $_GET['delete_comment']);
    header('Location: ' . $_SERVER['PHP_SELF'] . '?video=' . $_GET['video']);
    exit;
}

// --- Get videos ---
$videos = fetchVideos($youtube, CHANNEL_ID, 10);

// --- Selected video ---
$selectedVideoId = $_GET['video'] ?? ($videos[0]->id->videoId ?? null);
$videoStats = $selectedVideoId ? fetchVideoStats($youtube, $selectedVideoId) : null;
$comments = $selectedVideoId ? fetchComments($youtube, $selectedVideoId, 50) : [];

// --- Filter comments ---
if (isset($_GET['filter'])) {
    $comments = filterComments($comments, $_GET['filter']);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>DJ YouTube Dashboard</title>
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
                <li class="video-item"><a href="index.php" class="active"><i class="fas fa-video"></i> Videos</a></li>
                <li class="video-item"><a href="status.php"><i class="fas fa-server"></i> Status</a></li>
                <li class="video-item-header"><i class="fas fa-video"></i> Videos</li>
                <?php foreach($videos as $v): 
                    $vidId = $v->id->videoId;
                    $title = $v->snippet->title;
                    $active = $vidId === $selectedVideoId ? 'active' : '';
                ?>
                    <li class="video-item">
                        <a class="<?= $active ?>" href="?video=<?= htmlspecialchars($vidId) ?>">
                            <i class="fas fa-play-circle"></i> <?= htmlspecialchars($title) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="main">
            <?php if($videoStats): ?>
                <h1><i class="fab fa-youtube"></i> <?= htmlspecialchars($videoStats['title']) ?></h1>
                <div class="video-container">
                    <iframe width="560" height="315" 
                        src="https://www.youtube.com/embed/<?= htmlspecialchars($selectedVideoId) ?>" 
                        frameborder="0" allowfullscreen></iframe>
                </div>
                <div class="banner">LIVE</div>
                <div class="stats">
                    <p><strong><i class="fas fa-calendar-alt"></i> Published:</strong> <?= htmlspecialchars($videoStats['publishedAt']) ?></p>
                    <p><strong><i class="fas fa-eye"></i> Views:</strong> <?= $videoStats['views'] ?></p>
                    <p><strong><i class="fas fa-thumbs-up"></i> Likes:</strong> <?= $videoStats['likes'] ?></p>
                    <p><strong><i class="fas fa-comments"></i> Comments:</strong> <?= $videoStats['comments'] ?></p>
                </div>

                <h2><i class="fas fa-file-alt"></i> Description</h2>
                <p><?= nl2br(htmlspecialchars($videoStats['description'])) ?></p>

                <div class="comments-section">
                    <h2><i class="fas fa-comment-dots"></i> Comments</h2>
                    <form method="get">
                        <input type="hidden" name="video" value="<?= htmlspecialchars($selectedVideoId) ?>">
                        <input type="text" name="filter" placeholder="Filter comments">
                        <button type="submit">Filter</button>
                    </form>
                    <table class="comments">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Author</th>
                                <th><i class="fas fa-comment"></i> Comment</th>
                                <th><i class="fas fa-cog"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($comments as $c):
                                $commentId = $c['id'];
                                $author = $c['snippet']['topLevelComment']['snippet']['authorDisplayName'];
                                $comment = $c['snippet']['topLevelComment']['snippet']['textDisplay'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($author) ?></td>
                                    <td><?= htmlspecialchars($comment) ?></td>
                                    <td>
                                        <a href="?video=<?= htmlspecialchars($selectedVideoId) ?>&delete_comment=<?= htmlspecialchars($commentId) ?>&csrf_token=<?= htmlspecialchars(generateCsrfToken()) ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p><small>Note: Deleting comments requires the `Google_Service_YouTube::YOUTUBE_FORCE_SSL` scope.</small></p>
                </div>
            <?php else: ?>
                <p>No video selected.</p>
            <?php endif; ?>
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

