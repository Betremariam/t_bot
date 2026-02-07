<?php
require_once 'db.php';

$BOT_TOKEN = getenv("BOT_TOKEN");

if (!$BOT_TOKEN) {
    error_log("BOT_TOKEN environment variable is not set.");
    exit;
}

try {
    $pdo = get_db_connection();

    $content = file_get_contents("php://input");
    $update = json_decode($content, true);

    if (!$update || !isset($update["message"])) {
        exit;
    }

    $message = $update["message"];
    $chatId = $message["chat"]["id"];
    $text = $message["text"] ?? "";

    if (strpos($text, "/start") === 0) {
        $parts = explode(" ", $text);
        $token = $parts[1] ?? null;

        if (!$token) {
            send("👋 *Hello!* Welcome to the bot. Please use the link provided after submitting the form to see your data.", $chatId);
            exit;
        }

        $stmt = $pdo->prepare("
          SELECT * FROM telegram_forms WHERE token = ? AND used = FALSE
        ");
        $stmt->execute([$token]);
        $form = $stmt->fetch();

        if (!$form) {
            send("❌ *Link Expired*\nThis link has already been used or is invalid.", $chatId);
            exit;
        }

        // Mark as used
        $pdo->prepare("
          UPDATE telegram_forms SET used = TRUE WHERE token = ?
        ")->execute([$token]);

        $msg = "✅ *Form Data Received*\n\n";
        $msg .= "👤 *Name:* " . htmlspecialchars($form['name']) . "\n";
        $msg .= "📧 *Email:* " . htmlspecialchars($form['email']) . "\n";
        $msg .= "📝 *Message:* \n" . htmlspecialchars($form['message']) . "\n\n";
        $msg .= "🕒 _Submitted at: " . $form['created_at'] . "_";

        send($msg, $chatId);
    } else {
        send("Hello! I am ready to process your form submissions.", $chatId);
    }

} catch (Exception $e) {
    error_log("Bot Error: " . $e->getMessage());
}

function send($text, $chatId) {
    global $BOT_TOKEN;
    $url = "https://api.telegram.org/bot$BOT_TOKEN/sendMessage";
    
    $postData = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        error_log('Curl Error: ' . curl_error($ch));
    }
    
    curl_close($ch);
    return $response;
}
