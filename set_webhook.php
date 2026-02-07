<?php
$BOT_TOKEN = getenv("BOT_TOKEN");
$url = $_GET['url'] ?? '';

if (!$BOT_TOKEN) {
    die("❌ BOT_TOKEN is not set.");
}

if (!$url) {
    die("❌ Usage: set_webhook.php?url=https://your-domain.com/bot.php");
}

$api_url = "https://api.telegram.org/bot$BOT_TOKEN/setWebhook?url=" . urlencode($url);

$response = file_get_contents($api_url);

echo "<h2>Setting Webhook</h2>";
echo "<b>Target URL:</b> $url<br>";
echo "<b>API Response:</b> $response<br><br>";

if (strpos($response, '"ok":true') !== false) {
    echo "✅ <b>Success!</b> Your bot is now connected to $url";
} else {
    echo "❌ <b>Failed!</b> Check your BOT_TOKEN and the URL.";
}
