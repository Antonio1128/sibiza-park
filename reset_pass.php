<?php
require_once 'config/db.php';
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("UPDATE utilizatori SET password='$hash' WHERE username='admin'");
echo "Parola resetata! Acum sterge acest fisier.";
?>
