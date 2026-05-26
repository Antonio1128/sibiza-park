<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
if ($_SESSION['rol'] !== 'admin') { header("Location: /index.php"); exit; }
$id = (int)($_GET['id'] ?? 0);
if ($id !== $_SESSION['user_id']) {
    $conn->query("DELETE FROM utilizatori WHERE id=$id");
}
header("Location: index.php");
exit;
?>
