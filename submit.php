<?php
require_once 'db.php';

try {
    $pdo = get_db_connection();

    $name = $_POST['name'] ?? 'Unknown';
    $email = $_POST['email'] ?? 'No email';
    $message = $_POST['message'] ?? '';

    $token = bin2hex(random_bytes(16));

    $stmt = $pdo->prepare("
      INSERT INTO telegram_forms (token, name, email, message)
      VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$token, $name, $email, $message]);

    $botUsername = getenv("BOT_USERNAME");
    
    // Clean BOT_USERNAME if it's a full URL
    $botHandle = str_replace(['https://t.me/', '/'], '', $botUsername);

    header("Location: https://t.me/$botHandle?start=$token");
    exit;

} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage());
}
