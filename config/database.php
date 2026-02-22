<?php
// -------------------------------------------------------
// Heroku / Render: parses DATABASE_URL automatically
// Railway / local XAMPP: reads individual DB_* env vars
// -------------------------------------------------------

$db_url = getenv('DATABASE_URL');

if ($db_url) {
    // Heroku injects DATABASE_URL — parse it
    $parsed   = parse_url($db_url);
    $host     = $parsed['host'];
    $port     = $parsed['port'] ?? 5432;
    $db_name  = ltrim($parsed['path'], '/');
    $username = $parsed['user'];
    $password = $parsed['pass'];
} else {
    // Railway / local XAMPP — use individual env vars
    $host     = getenv('DB_HOST')     ?: 'localhost';
    $port     = getenv('DB_PORT')     ?: '5432';
    $db_name  = getenv('DB_NAME')     ?: 'hostel_finder';
    $username = getenv('DB_USER')     ?: 'postgres';
    $password = getenv('DB_PASSWORD') ?: 'postgres';
}

try {
    $dsn  = "pgsql:host=$host;port=$port;dbname=$db_name";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 10,
        PDO::ATTR_SSL_KEY            => null,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}
?>
