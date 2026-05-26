<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Adaugă client';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume                 = trim($_POST['nume'] ?? '');
    $telefon              = trim($_POST['telefon'] ?? '');
    $email                = trim($_POST['email'] ?? '');
    $adresa               = trim($_POST['adresa'] ?? '');
    $nr_permis            = trim($_POST['nr_permis'] ?? '');
    $categorie_permis     = trim($_POST['categorie_permis'] ?? '');
    $data_expirare_permis = $_POST['data_expirare_permis'] ?: null;

    if (!$nume) {
        $error = 'Numele este obligatoriu.';
    } else {
        $stmt = $conn->prepare("INSERT INTO clienti (nume, telefon, email, adresa, nr_permis, categorie_permis, data_expirare_permis) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssss", $nume, $telefon, $email, $adresa, $nr_permis, $categorie_permis, $data_expirare_permis);
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
    <h1>Adaugă client</h1>
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
            <label>Nume complet *</label>
            <input type="text" name="nume" class="form-control" value="<?= htmlspecialchars($_POST['nume'] ?? '') ?>" required/>
          </div>
          <div class="form-group">
            <label>Telefon</label>
            <input type="text" name="telefon" class="form-control" value="<?= htmlspecialchars($_POST['telefon'] ?? '') ?>"/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Adresă</label>
            <input type="text" name="adresa" class="form-control" value="<?= htmlspecialchars($_POST['adresa'] ?? '') ?>"/>
          </div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group">
            <label>Nr. permis</label>
            <input type="text" name="nr_permis" class="form-control" value="<?= htmlspecialchars($_POST['nr_permis'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Categorii permis</label>
            <input type="text" name="categorie_permis" class="form-control" placeholder="ex: B, C, D" value="<?= htmlspecialchars($_POST['categorie_permis'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Expiră permis</label>
            <input type="date" name="data_expirare_permis" class="form-control" value="<?= htmlspecialchars($_POST['data_expirare_permis'] ?? '') ?>"/>
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
