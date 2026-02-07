<?php
require_once 'db.php';

$BOT_TOKEN = getenv("BOT_TOKEN");

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
            send("❌ Invalid link", $chatId);
            exit;
        }

        $stmt = $pdo->prepare("
          SELECT * FROM telegram_forms WHERE token = ? AND used = FALSE
        ");
        $stmt->execute([$token]);
        $form = $stmt->fetch();

        if (!$form) {
            send("❌ Token expired or already used", $chatId);
            exit;
        }

        $pdo->prepare("
          UPDATE telegram_forms SET used = TRUE WHERE token = ?
        ")->execute([$token]);

        $msg = "✅ *Form Received*\n\n";
        $msg .= "👤 Name: {$form['name']}\n";
        $msg .= "📧 Email: {$form['email']}\n";
        $msg .= "📝 Message:\n{$form['message']}";

        send($msg, $chatId);
    }

} catch (Exception $e) {
    // Silently fail or log error to avoid Telegram retries if it's a code error
    error_log("Bot Error: " . $e->getMessage());
}

function send($text, $chatId) {
    global $BOT_TOKEN;
    $url = "https://api.telegram.org/bot$BOT_TOKEN/sendMessage";
    $data = [
        "chat_id" => $chatId,
        "text" => $text,
        "parse_mode" => "Markdown"
    ];

    file_get_contents($url . "?" . http_build_query($data));
}
