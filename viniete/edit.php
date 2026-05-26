<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Editează vinietă';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM viniete WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$vin = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$vin) { header("Location: index.php"); exit; }

$masini = $conn->query("SELECT id, nr_inmatriculare, marca, model FROM masini ORDER BY nr_inmatriculare");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $masina_id     = (int)$_POST['masina_id'];
    $tara          = trim($_POST['tara'] ?? '');
    $tip           = trim($_POST['tip'] ?? '');
    $data_start    = $_POST['data_start'] ?: null;
    $data_expirare = $_POST['data_expirare'] ?: null;
    $cost          = (float)$_POST['cost'];

    $stmt = $conn->prepare("UPDATE viniete SET masina_id=?, tara=?, tip=?, data_start=?, data_expirare=?, cost=? WHERE id=?");
    $stmt->bind_param("issssdi", $masina_id, $tara, $tip, $data_start, $data_expirare, $cost, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit;
}

$v = $_POST ?: $vin;
require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Editează vinietă</h1>
    <a href="index.php" class="btn btn-secondary">← Înapoi</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label>Mașină *</label>
            <select name="masina_id" class="form-control" required>
              <?php $masini->data_seek(0); while ($m = $masini->fetch_assoc()): ?>
              <option value="<?= $m['id'] ?>" <?= ($v['masina_id'] == $m['id']) ? 'selected' : '' ?>><?= htmlspecialchars($m['nr_inmatriculare'].' – '.$m['marca'].' '.$m['model']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Țară</label>
            <input type="text" name="tara" class="form-control" value="<?= htmlspecialchars($v['tara'] ?? '') ?>"/>
          </div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group">
            <label>Tip</label>
            <select name="tip" class="form-control">
              <?php foreach (['lunar','trimestrial','anual'] as $t): ?>
              <option value="<?= $t ?>" <?= ($v['tip'] === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Data start</label>
            <input type="date" name="data_start" class="form-control" value="<?= $v['data_start'] ?? '' ?>"/>
          </div>
          <div class="form-group">
            <label>Data expirare</label>
            <input type="date" name="data_expirare" class="form-control" value="<?= $v['data_expirare'] ?? '' ?>"/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Cost (RON)</label>
            <input type="number" name="cost" class="form-control" step="0.01" value="<?= $v['cost'] ?? '' ?>"/>
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
