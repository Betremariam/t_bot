<?php
$BOT_TOKEN = getenv("BOT_TOKEN");
$dbUrl = getenv("DATABASE_URL");
$pdo = new PDO($dbUrl);

$update = json_decode(file_get_contents("php://input"), true);

if (!isset($update["message"])) exit;

$chatId = $update["message"]["chat"]["id"];
$text = $update["message"]["text"] ?? "";

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
  $form = $stmt->fetch(PDO::FETCH_ASSOC);

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

function send($text, $chatId) {
  global $BOT_TOKEN;
  file_get_contents("https://api.telegram.org/bot$BOT_TOKEN/sendMessage?" . http_build_query([
    "chat_id" => $chatId,
    "text" => $text,
    "parse_mode" => "Markdown"
  ]));
}
