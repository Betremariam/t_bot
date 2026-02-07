<?php
require_once 'db.php';

try {
    $pdo = get_db_connection();

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

    $pdo->exec($sql);
    echo "✅ Table created successfully!";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
