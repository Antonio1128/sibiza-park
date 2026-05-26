<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Adaugă set anvelope';
$error = '';

$masini = $conn->query("SELECT id, nr_inmatriculare, marca, model FROM masini ORDER BY nr_inmatriculare");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $masina_id  = (int)$_POST['masina_id'];
    $tip_sezon  = $_POST['tip_sezon'] ?? 'vara';
    $marca      = trim($_POST['marca'] ?? '');
    $dimensiune = trim($_POST['dimensiune'] ?? '');
    $tip_set    = $_POST['tip_set'] ?? 'anvelope';
    $stare      = $_POST['stare'] ?? 'depozitate';
    $nr_bucati  = (int)$_POST['nr_bucati'];

    if (!$masina_id) {
        $error = 'Selectează mașina.';
    } else {
        $stmt = $conn->prepare("INSERT INTO seturi_anvelope (masina_id, tip_sezon, marca, dimensiune, tip_set, stare, nr_bucati) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("isssssi", $masina_id, $tip_sezon, $marca, $dimensiune, $tip_set, $stare, $nr_bucati);
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
    <h1>Adaugă set anvelope</h1>
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
              <option value="<?= $m['id'] ?>" <?= (($_POST['masina_id'] ?? 0) == $m['id']) ? 'selected' : '' ?>><?= htmlspecialchars($m['nr_inmatriculare'].' – '.$m['marca'].' '.$m['model']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Sezon</label>
            <select name="tip_sezon" class="form-control">
              <?php foreach (['vara','iarna','all_season'] as $t): ?>
              <option value="<?= $t ?>" <?= (($_POST['tip_sezon'] ?? 'vara') === $t) ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Marcă anvelope</label>
            <input type="text" name="marca" class="form-control" placeholder="ex: Michelin, Bridgestone" value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Dimensiune</label>
            <input type="text" name="dimensiune" class="form-control" placeholder="ex: 205/55 R16" value="<?= htmlspecialchars($_POST['dimensiune'] ?? '') ?>"/>
          </div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group">
            <label>Tip set</label>
            <select name="tip_set" class="form-control">
              <option value="anvelope" <?= (($_POST['tip_set'] ?? '') === 'anvelope') ? 'selected' : '' ?>>Anvelope</option>
              <option value="roti_complete" <?= (($_POST['tip_set'] ?? '') === 'roti_complete') ? 'selected' : '' ?>>Roți complete</option>
            </select>
          </div>
          <div class="form-group">
            <label>Stare</label>
            <select name="stare" class="form-control">
              <option value="depozitate" <?= (($_POST['stare'] ?? '') === 'depozitate') ? 'selected' : '' ?>>Depozitate</option>
              <option value="montate" <?= (($_POST['stare'] ?? '') === 'montate') ? 'selected' : '' ?>>Montate</option>
            </select>
          </div>
          <div class="form-group">
            <label>Nr. bucăți</label>
            <input type="number" name="nr_bucati" class="form-control" min="1" max="10" value="<?= (int)($_POST['nr_bucati'] ?? 4) ?>"/>
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
