<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Adaugă asigurare';
$error = '';

$masini = $conn->query("SELECT id, nr_inmatriculare, marca, model FROM masini ORDER BY nr_inmatriculare");
$preselect = (int)($_GET['masina_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $masina_id    = (int)$_POST['masina_id'];
    $tip          = $_POST['tip'] ?? 'RCA';
    $companie     = trim($_POST['companie'] ?? '');
    $nr_polita    = trim($_POST['nr_polita'] ?? '');
    $data_start   = $_POST['data_start'] ?: null;
    $data_expirare= $_POST['data_expirare'] ?: null;
    $pret         = (float)$_POST['pret'];

    if (!$masina_id) {
        $error = 'Selectează mașina.';
    } else {
        $stmt = $conn->prepare("INSERT INTO asigurari (masina_id, tip, companie, nr_polita, data_start, data_expirare, pret) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("isssssd", $masina_id, $tip, $companie, $nr_polita, $data_start, $data_expirare, $pret);
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
    <h1>Adaugă asigurare</h1>
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
            <label>Mașină *</label>
            <select name="masina_id" class="form-control" required>
              <option value="">— Selectează —</option>
              <?php $masini->data_seek(0); while ($m = $masini->fetch_assoc()): ?>
              <option value="<?= $m['id'] ?>" <?= ($preselect == $m['id'] || (($_POST['masina_id'] ?? 0) == $m['id'])) ? 'selected' : '' ?>><?= htmlspecialchars($m['nr_inmatriculare'].' – '.$m['marca'].' '.$m['model']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Tip</label>
            <select name="tip" class="form-control">
              <option value="RCA" <?= (($_POST['tip'] ?? '') === 'RCA') ? 'selected' : '' ?>>RCA</option>
              <option value="CASCO" <?= (($_POST['tip'] ?? '') === 'CASCO') ? 'selected' : '' ?>>CASCO</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Companie asiguratoare</label>
            <input type="text" name="companie" class="form-control" value="<?= htmlspecialchars($_POST['companie'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Număr poliță</label>
            <input type="text" name="nr_polita" class="form-control" value="<?= htmlspecialchars($_POST['nr_polita'] ?? '') ?>"/>
          </div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group">
            <label>Data start</label>
            <input type="date" name="data_start" class="form-control" value="<?= $_POST['data_start'] ?? date('Y-m-d') ?>"/>
          </div>
          <div class="form-group">
            <label>Data expirare</label>
            <input type="date" name="data_expirare" class="form-control" value="<?= $_POST['data_expirare'] ?? '' ?>"/>
          </div>
          <div class="form-group">
            <label>Preț (RON)</label>
            <input type="number" name="pret" class="form-control" step="0.01" min="0" value="<?= $_POST['pret'] ?? '' ?>"/>
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
