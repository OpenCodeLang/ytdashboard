<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

define('ADMIN_USER', $_ENV['ADMIN_USER']);
define('ADMIN_PASS', $_ENV['ADMIN_PASS']);
define('OAUTH_CLIENT_ID', $_ENV['OAUTH_CLIENT_ID']);
define('OAUTH_CLIENT_SECRET', $_ENV['OAUTH_CLIENT_SECRET']);
define('OAUTH_REDIRECT_URI', $_ENV['OAUTH_REDIRECT_URI']);
define('CHANNEL_ID', $_ENV['CHANNEL_ID']);