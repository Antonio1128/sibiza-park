<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$id = (int)($_GET['id'] ?? 0);
$conn->query("DELETE FROM clienti WHERE id=$id");
header("Location: index.php");
exit;
?>
