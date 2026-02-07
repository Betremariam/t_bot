<?php
$BOT_TOKEN = getenv("BOT_TOKEN");

if (!$BOT_TOKEN) {
    die("❌ BOT_TOKEN is not set.");
}

$api_url = "https://api.telegram.org/bot$BOT_TOKEN/getWebhookInfo";
$response = file_get_contents($api_url);
$data = json_decode($response, true);

echo "<h2>Bot Info & Webhook Status</h2>";
echo "<b>BOT_TOKEN:</b> " . substr($BOT_TOKEN, 0, 5) . "..." . substr($BOT_TOKEN, -5) . "<br>";
echo "<b>BOT_USERNAME:</b> " . getenv("BOT_USERNAME") . "<br>";

if ($data && $data['ok']) {
    $info = $data['result'];
    echo "<h3>Webhook Details</h3>";
    echo "<b>URL:</b> " . ($info['url'] ?: "<i>None (Not set!)</i>") . "<br>";
    echo "<b>Pending Updates:</b> " . $info['pending_update_count'] . "<br>";
    echo "<b>Last Error Date:</b> " . (isset($info['last_error_date']) ? date("Y-m-d H:i:s", $info['last_error_date']) : "None") . "<br>";
    echo "<b>Last Error Message:</b> " . ($info['last_error_message'] ?? "None") . "<br>";
    
    if (!$info['url']) {
        echo "<p style='color:red;'>⚠️ <b>Your webhook is not set!</b> Use set_webhook.php to link your bot.</p>";
    }
} else {
    echo "❌ <b>Error fetching info from Telegram:</b> " . $response;
}
