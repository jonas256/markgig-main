<?php
/**
 * MarkGigs Database Connection (PDO)
 * 
 * This file establishes a secure connection to the MySQL database using PHP Data Objects (PDO).
 * It is included at the start of almost every page that needs to read or write database data.
 */
require_once 'config.php'; // Load database credentials

try {
    // The Data Source Name (DSN) tells PDO which database type, host, and database name to connect to.
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // Security and Performance Options for the connection
    $options = [
        // Throw exceptions (errors) immediately if a database query fails
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Return data as an associative array (e.g., $row['username']) instead of an indexed array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Disable emulated prepares to enforce true prepared statements (better security against SQL injection)
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    // Initialize the actual connection object, which will be used globally as `$pdo`
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // If the connection fails (e.g. wrong password or database is down), stop the script and show the error
    die("Database Connection Failed: " . $e->getMessage());
}
