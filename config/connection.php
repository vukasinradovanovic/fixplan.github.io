<?php
$envPath = __DIR__ . '/.env';


if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        list($name, $value) = explode('=', $line, 2);
        
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        
        // Populate system environment arrays
        $_ENV[$name] = $value;
    }
} else {
    die("Greska: Konfiguracioni fajl (.env) nije pronadjen.");
}

// Map the parsed environment arrays to standard variables
$server   = $_ENV['DB_SERVER'] ?? '127.0.0.1';
$port     = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'] ?? '';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

try {
    $dsn = "mysql:host={$server};port={$port};dbname={$database};charset=utf8";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Konekcija sa bazom neuspesna: " . $e->getMessage());
    die("Konekcija sa bazom podataka trenutno nije moguca.");
}
?>