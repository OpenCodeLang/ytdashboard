<?php
require __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

session_start();

define('ADMIN_USER', getenv('ADMIN_USER'));
define('ADMIN_PASS', getenv('ADMIN_PASS'));
define('OAUTH_CLIENT_ID', getenv('OAUTH_CLIENT_ID'));
define('OAUTH_CLIENT_SECRET', getenv('OAUTH_CLIENT_SECRET'));
define('OAUTH_REDIRECT_URI', getenv('OAUTH_REDIRECT_URI'));
define('CHANNEL_ID', getenv('CHANNEL_ID'));