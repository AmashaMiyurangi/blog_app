
<?php

function load_env($path = __DIR__ . '/.env') {
    $vars = [];
    
    // Check if .env file exists
    if (!file_exists($path)) {
        return $vars;
    }
    
    // Read .env file line by line
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Parse each line
    foreach ($lines as $line) {
        // Skip comment lines starting with #
        if (strpos(trim($line), '#') === 0) continue;
        
        // Skip lines without = sign
        if (strpos($line, '=') === false) continue;
        
        // Split key and value
        list($key, $val) = array_map('trim', explode('=', $line, 2));
        
        // Store in array if key exists
        if ($key) {
            $vars[$key] = $val;
        }
    }
    
    return $vars;
}

// Load environment variables
$env = load_env();


// STEP 2: Set Database Configuration


// Check if .env file was loaded successfully
if (empty($env)) {
    error_log("Critical Error: .env file not found or empty");
    die("❌ Configuration error. Please contact system administrator.");
}

// Validate required environment variables exist
$required_vars = ['DB_HOST', 'DB_USER', 'DB_NAME'];
foreach ($required_vars as $var) {
    if (!isset($env[$var])) {
        error_log("Critical Error: Missing required environment variable: {$var}");
        die("❌ Configuration error. Please contact system administrator.");
    }
}

// Database credentials from .env (NO FALLBACK DEFAULTS for security)
$DB_HOST = $env['DB_HOST'];                     // Database host
$DB_USER = $env['DB_USER'];                     // Database username
$DB_PASS = $env['DB_PASS'] ?? '';               // Database password 
$DB_NAME = $env['DB_NAME'];                     // Database name

// STEP 3: Create MySQLi Connection (for procedural/OOP style)

// Create MySQLi connection object
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check if connection was successful
if ($conn->connect_error) {
    // Log error (in production, log to file instead of displaying)
    error_log("Database Connection Failed: " . $conn->connect_error);
    
    // Display user-friendly error message
    die("❌ Database connection failed. Please try again later.");
}

// Set character encoding to UTF-8 for proper handling of special characters
$conn->set_charset("utf8mb4");

// 
// STEP 4: Create PDO Connection (alternative, for prepared statements)

// Build DSN (Data Source Name) for PDO
$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

try {
    // Create PDO instance with error handling
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,              // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Fetch as associative array
        PDO::ATTR_EMULATE_PREPARES => false,                      // Use real prepared statements
        PDO::ATTR_PERSISTENT => false,                             // Don't use persistent connections
    ]);
    
} catch (PDOException $e) {
    // Log error (in production, log to file instead of displaying)
    error_log("PDO Connection Failed: " . $e->getMessage());
    
    // Display user-friendly error message
    die("❌ Database connection failed. Please try again later.");
}


?>