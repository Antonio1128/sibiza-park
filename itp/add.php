<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Adaugă ITP';
$error = '';

$masini    = $conn->query("SELECT id, nr_inmatriculare, marca, model FROM masini ORDER BY nr_inmatriculare");
$preselect = (int)($_GET['masina_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $masina_id      = (int)$_POST['masina_id'];
    $data_efectuare = $_POST['data_efectuare'] ?? date('Y-m-d');
    $data_expirare  = $_POST['data_expirare'] ?? '';
    $statie         = trim($_POST['statie'] ?? '');
    $cost           = (float)$_POST['cost'];
    $observatii     = trim($_POST['observatii'] ?? '');

    if (!$masina_id || !$data_expirare) {
        $error = 'Mașina și data expirării sunt obligatorii.';
    } else {
        $stmt = $conn->prepare("INSERT INTO itp (masina_id, data_efectuare, data_expirare, statie, cost, observatii) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("isssds", $masina_id, $data_efectuare, $data_expirare, $statie, $cost, $observatii);
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
    <h1>Adaugă ITP</h1>
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
              <option value="<?= $m['id'] ?>" <?= ($preselect == $m['id'] || (($_POST['masina_id'] ?? 0) == $m['id'])) ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['nr_inmatriculare'].' – '.$m['marca'].' '.$m['model']) ?>
              </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Stație ITP</label>
            <input type="text" name="statie" class="form-control" placeholder="ex: RAR Pitești" value="<?= htmlspecialchars($_POST['statie'] ?? '') ?>"/>
          </div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group">
            <label>Data efectuare</label>
            <input type="date" name="data_efectuare" class="form-control" value="<?= $_POST['data_efectuare'] ?? date('Y-m-d') ?>"/>
          </div>
          <div class="form-group">
            <label>Data expirare *</label>
            <input type="date" name="data_expirare" class="form-control" value="<?= $_POST['data_expirare'] ?? '' ?>" required/>
          </div>
          <div class="form-group">
            <label>Cost (RON)</label>
            <input type="number" name="cost" class="form-control" step="0.01" min="0" value="<?= $_POST['cost'] ?? '' ?>"/>
          </div>
        </div>
        <div class="form-group">
          <label>Observații</label>
          <input type="text" name="observatii" class="form-control" value="<?= htmlspecialchars($_POST['observatii'] ?? '') ?>"/>
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
