<?php
require_once __DIR__ . '/../includes/helpers.php';
$host = "localhost";
$username = "root";
$password = "";
$database = "parc_auto";

try {
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        die("Conexiune eșuată: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Eroare: " . $e->getMessage());
}
?>
