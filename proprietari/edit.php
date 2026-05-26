<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Editează proprietar';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM proprietari WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$p) { header("Location: index.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume    = trim($_POST['nume'] ?? '');
    $telefon = trim($_POST['telefon'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $adresa  = trim($_POST['adresa'] ?? '');
    if (!$nume) {
        $error = 'Numele este obligatoriu.';
    } else {
        $stmt = $conn->prepare("UPDATE proprietari SET nume=?, telefon=?, email=?, adresa=? WHERE id=?");
        $stmt->bind_param("ssssi", $nume, $telefon, $email, $adresa, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit;
    }
}

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Editează proprietar</h1>
    <a href="index.php" class="btn btn-secondary">← Înapoi</a>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label>Nume complet / Firmă *</label>
            <input type="text" name="nume" class="form-control" value="<?= htmlspecialchars($_POST['nume'] ?? $p['nume']) ?>" required/>
          </div>
          <div class="form-group">
            <label>Telefon</label>
            <input type="text" name="telefon" class="form-control" value="<?= htmlspecialchars($_POST['telefon'] ?? $p['telefon']) ?>"/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $p['email']) ?>"/>
          </div>
          <div class="form-group">
            <label>Adresă</label>
            <input type="text" name="adresa" class="form-control" value="<?= htmlspecialchars($_POST['adresa'] ?? $p['adresa']) ?>"/>
          </div>
        </div>
        <div class="form-actions">
          <a href="index.php" class="btn btn-secondary">Anulează</a>
          <button type="submit" class="btn btn-primary">Salvează</button>
        </div>
      </form>
    </div>
  </div>

</div>
</div>
<?php require_once '../includes/footer.php'; ?>
