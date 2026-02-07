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

    if (!$update) {
        exit;
    }

    // Log the incoming update for debugging
    error_log("Bot Update: " . json_encode($update));

    if (!isset($update["message"])) {
        exit;
    }

    $message = $update["message"];
    $chatId = $message["chat"]["id"];
    $text = $message["text"] ?? "";

    if (strpos($text, "/start") === 0) {
        $parts = explode(" ", $text);
        $token = $parts[1] ?? null;

        if (!$token) {
            send("👋 <b>Hello!</b> Welcome to the bot. Please use the link provided after submitting the form to see your data.", $chatId);
            exit;
        }

        error_log("Processing token: " . $token);

        $stmt = $pdo->prepare("
          SELECT * FROM telegram_forms WHERE token = ? AND used = FALSE
        ");
        $stmt->execute([$token]);
        $form = $stmt->fetch();

        if (!$form) {
            error_log("No unused form found for token: " . $token);
            send("❌ <b>Link Expired</b>\nThis link has already been used or is invalid.", $chatId);
            exit;
        }

        error_log("Found form data for token: " . $token);

        // Mark as used
        $pdo->prepare("
          UPDATE telegram_forms SET used = TRUE WHERE token = ?
        ")->execute([$token]);

        $msg = "✅ <b>Form Data Received</b>\n\n";
        $msg .= "👤 <b>Name:</b> " . htmlspecialchars($form['name']) . "\n";
        $msg .= "📧 <b>Email:</b> " . htmlspecialchars($form['email']) . "\n";
        $msg .= "📝 <b>Message:</b> \n" . htmlspecialchars($form['message']) . "\n\n";
        $msg .= "🕒 <i>Submitted at: " . $form['created_at'] . "</i>";

        send($msg, $chatId);
    } else {
        send("Hello! I am ready to process your form submissions.", $chatId);
    }

} catch (Exception $e) {
    error_log("Bot Exception: " . $e->getMessage());
}

function send($text, $chatId) {
    global $BOT_TOKEN;
    $url = "https://api.telegram.org/bot$BOT_TOKEN/sendMessage";
    
    $postData = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML'
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
    
    error_log("Telegram API Response: " . $response);
    
    curl_close($ch);
    return $response;
}
