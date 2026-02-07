<?php

/**
 * Returns a PDO connection to the PostgreSQL database.
 * Parses the DATABASE_URL environment variable which is usually in the format:
 * postgresql://user:pass@host:port/dbname
 */
function get_db_connection() {
    $url = getenv("DATABASE_URL");
    
    if (!$url) {
        throw new Exception("DATABASE_URL environment variable is not set.");
    }

    $dbopts = parse_url($url);

    if ($dbopts === false || !isset($dbopts["host"])) {
        // Fallback for direct connection strings like pgsql:host=...
        return new PDO($url);
    }

    $dsn = sprintf(
        "pgsql:host=%s;port=%d;dbname=%s",
        $dbopts["host"],
        $dbopts["port"] ?? 5432,
        ltrim($dbopts["path"], '/')
    );

    return new PDO($dsn, $dbopts["user"], $dbopts["pass"], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}
