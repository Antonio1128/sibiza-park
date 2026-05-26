<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Adaugă cursă';
$error = '';

$masini = $conn->query("SELECT id, nr_inmatriculare, marca, model, km_actuali FROM masini ORDER BY nr_inmatriculare");
$clienti_list = $conn->query("SELECT id, nume FROM clienti ORDER BY nume");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $masina_id  = (int)$_POST['masina_id'];
    $sofer_id   = (int)$_POST['sofer_id'];
    $data       = $_POST['data'] ?? date('Y-m-d');
    $km_start   = (int)$_POST['km_start'];
    $km_final   = (int)$_POST['km_final'];
    $destinatie = trim($_POST['destinatie'] ?? '');
    $observatii = trim($_POST['observatii'] ?? '');

    if (!$masina_id || !$sofer_id) {
        $error = 'Mașina și șoferul sunt obligatorii.';
    } elseif ($km_final < $km_start) {
        $error = 'Km final trebuie să fie mai mare decât km start.';
    } else {
        $stmt = $conn->prepare("INSERT INTO curse (masina_id, sofer_id, data, km_start, km_final, destinatie, observatii) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("iisiiss", $masina_id, $sofer_id, $data, $km_start, $km_final, $destinatie, $observatii);
        $stmt->execute();
        $stmt->close();
        $conn->query("UPDATE masini SET km_actuali=$km_final WHERE id=$masina_id AND km_actuali < $km_final");
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
    <h1>Adaugă cursă</h1>
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
            <select name="masina_id" class="form-control" required id="sel-masina">
              <option value="">— Selectează —</option>
              <?php $masini->data_seek(0); while ($m = $masini->fetch_assoc()): ?>
              <option value="<?= $m['id'] ?>" data-km="<?= $m['km_actuali'] ?>" <?= (($_POST['masina_id'] ?? 0) == $m['id']) ? 'selected' : '' ?>><?= htmlspecialchars($m['nr_inmatriculare'].' – '.$m['marca'].' '.$m['model']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Client *</label>
            <select name="sofer_id" class="form-control" required>
              <option value="">— Selectează —</option>
              <?php $clienti_list->data_seek(0); while ($s = $clienti_list->fetch_assoc()): ?>
              <option value="<?= $s['id'] ?>" <?= (($_POST['sofer_id'] ?? 0) == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['nume']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group">
            <label>Data</label>
            <input type="date" name="data" class="form-control" value="<?= $_POST['data'] ?? date('Y-m-d') ?>"/>
          </div>
          <div class="form-group">
            <label>Km start</label>
            <input type="number" name="km_start" id="km-start" class="form-control" value="<?= (int)($_POST['km_start'] ?? 0) ?>"/>
          </div>
          <div class="form-group">
            <label>Km final</label>
            <input type="number" name="km_final" class="form-control" value="<?= (int)($_POST['km_final'] ?? 0) ?>"/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Destinație</label>
            <input type="text" name="destinatie" class="form-control" value="<?= htmlspecialchars($_POST['destinatie'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Observații</label>
            <input type="text" name="observatii" class="form-control" value="<?= htmlspecialchars($_POST['observatii'] ?? '') ?>"/>
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
<script>
document.getElementById('sel-masina').addEventListener('change', function() {
  const km = this.options[this.selectedIndex].dataset.km;
  if (km) document.getElementById('km-start').value = km;
});
</script>
<?php require_once '../includes/footer.php'; ?>
