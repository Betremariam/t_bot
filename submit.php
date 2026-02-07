<?php
$dbUrl = getenv("DATABASE_URL");
$pdo = new PDO($dbUrl);

$name = $_POST['name'];
$email = $_POST['email'];
$message = $_POST['message'];

$token = bin2hex(random_bytes(16));

$stmt = $pdo->prepare("
  INSERT INTO telegram_forms (token, name, email, message)
  VALUES (?, ?, ?, ?)
");
$stmt->execute([$token, $name, $email, $message]);

$botUsername = getenv("BOT_USERNAME");

header("Location: https://t.me/$botUsername?start=$token");
exit;
