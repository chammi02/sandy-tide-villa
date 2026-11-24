<?php
/** Database configuration - change these values to match your XAMPP/WAMP setup. */
const DB_HOST = 'localhost';
const DB_NAME = 'sandy_tide_villa';
const DB_USER = 'root';
const DB_PASS = '';          // XAMPP default is an empty password

/** Returns one shared PDO connection (prepared statements everywhere = SQL injection safe). */
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database connection failed. Check config/db.php and make sure MySQL is running and db_structure.sql is imported.');
        }
    }
    return $pdo;
}
