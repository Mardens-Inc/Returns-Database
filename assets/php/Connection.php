<?php

namespace ReturnsDatabase;
use mysqli;
use mysqli_sql_exception;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");

/**
 * Database connection class
 */
class Connection
{
    /**
     * Establish database connection
     * @return mysqli The database handler
     */
    public static function connect(): mysqli
    {
        global $DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME;
        require_once 'config.inc.php';

        // Connecting to mysql database
        try {
            $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD);
            $conn->query("CREATE DATABASE IF NOT EXISTS `$DB_NAME`");
            $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
        } catch (mysqli_sql_exception $e) {
            header("Content-Type: application/json");
            die(json_encode(["error" => "Connection failed: " . mysqli_connect_error(), "message" => $e->getMessage()]));
        }

        // return database handler
        return $conn;
    }
}
