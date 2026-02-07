<?php
// Connect to your existing Render DB
$pdo = new PDO(getenv("DATABASE_URL"));

// SQL to create the table
$sql = "
CREATE TABLE IF NOT EXISTS telegram_forms (
  id SERIAL PRIMARY KEY,
  token TEXT UNIQUE,
  name TEXT,
  email TEXT,
  message TEXT,
  used BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT NOW()
);
";

try {
    $pdo->exec($sql);
    echo "✅ Table created successfully!";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
