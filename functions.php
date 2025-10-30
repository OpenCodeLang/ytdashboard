<?php
require __DIR__ . '/vendor/autoload.php';

function getYouTubeClient() {
    $client = new Google_Client();
    $client->setClientId(OAUTH_CLIENT_ID);
    $client->setClientSecret(OAUTH_CLIENT_SECRET);
    $client->setRedirectUri(OAUTH_REDIRECT_URI);
    $client->addScope([
        Google_Service_YouTube::YOUTUBE_READONLY,
        Google_Service_YouTube::YOUTUBE_FORCE_SSL
    ]);

    // Optional: disable SSL verification for local dev
    $client->setHttpClient(new \GuzzleHttp\Client([
        'verify' => false
    ]));

    if (isset($_SESSION['access_token'])) {
        $client->setAccessToken($_SESSION['access_token']);
        if ($client->isAccessTokenExpired()) {
            unset($_SESSION['access_token']);
        }
    }

    return $client;
}

// --- Videos ---
function fetchLatestVideoId($youtube, $channelId) {
    $response = $youtube->search->listSearch('snippet', [
        'channelId' => $channelId,
        'maxResults' => 1,
        'order' => 'date',
        'type' => 'video'
    ]);
    return $response['items'][0]->id->videoId ?? null;
}

function fetchVideos($youtube, $channelId, $maxResults = 10) {
    $response = $youtube->search->listSearch('snippet', [
        'channelId' => $channelId,
        'maxResults' => $maxResults,
        'order' => 'date',
        'type' => 'video'
    ]);
    return $response['items'] ?? [];
}

function fetchVideoStats($youtube, $videoId) {
    $response = $youtube->videos->listVideos('snippet,statistics', [
        'id' => $videoId
    ]);
    $item = $response->items[0] ?? null;
    if (!$item) return null;

    return [
        'title' => $item->snippet->title,
        'description' => $item->snippet->description,
        'publishedAt' => $item->snippet->publishedAt,
        'views' => $item->statistics->viewCount ?? 0,
        'likes' => $item->statistics->likeCount ?? 0,
        'comments' => $item->statistics->commentCount ?? 0
    ];
}

// --- Comments ---
function fetchComments($youtube, $videoId, $maxResults = 50) {
    $response = $youtube->commentThreads->listCommentThreads('snippet', [
        'videoId' => $videoId,
        'textFormat' => 'plainText',
        'maxResults' => $maxResults
    ]);
    return $response['items'] ?? [];
}

function filterComments($comments, $keyword) {
    return array_filter($comments, function($c) use ($keyword) {
        $text = strtolower($c['snippet']['topLevelComment']['snippet']['textDisplay']);
        return strpos($text, strtolower($keyword)) !== false;
    });
}

// --- Optional: Delete comment (requires proper OAuth scopes) ---
function deleteComment($youtube, $commentId) {
    try {
        $youtube->comments->delete($commentId);
        return true;
    } catch(Exception $e) {
        return false;
    }
}
