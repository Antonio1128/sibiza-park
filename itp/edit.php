<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Editează ITP';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM itp WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$itp = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$itp) { header("Location: index.php"); exit; }

$masini = $conn->query("SELECT id, nr_inmatriculare, marca, model FROM masini ORDER BY nr_inmatriculare");
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $masina_id      = (int)$_POST['masina_id'];
    $data_efectuare = $_POST['data_efectuare'] ?? '';
    $data_expirare  = $_POST['data_expirare'] ?? '';
    $statie         = trim($_POST['statie'] ?? '');
    $cost           = (float)$_POST['cost'];
    $observatii     = trim($_POST['observatii'] ?? '');

    $stmt = $conn->prepare("UPDATE itp SET masina_id=?, data_efectuare=?, data_expirare=?, statie=?, cost=?, observatii=? WHERE id=?");
    $stmt->bind_param("isssdsi", $masina_id, $data_efectuare, $data_expirare, $statie, $cost, $observatii, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit;
}

$v = $_POST ?: $itp;
require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Editează ITP</h1>
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
              <option value="<?= $m['id'] ?>" <?= ($v['masina_id'] == $m['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['nr_inmatriculare'].' – '.$m['marca'].' '.$m['model']) ?>
              </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Stație ITP</label>
            <input type="text" name="statie" class="form-control" value="<?= htmlspecialchars($v['statie'] ?? '') ?>"/>
          </div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group">
            <label>Data efectuare</label>
            <input type="date" name="data_efectuare" class="form-control" value="<?= $v['data_efectuare'] ?? '' ?>"/>
          </div>
          <div class="form-group">
            <label>Data expirare *</label>
            <input type="date" name="data_expirare" class="form-control" value="<?= $v['data_expirare'] ?? '' ?>" required/>
          </div>
          <div class="form-group">
            <label>Cost (RON)</label>
            <input type="number" name="cost" class="form-control" step="0.01" value="<?= $v['cost'] ?? '' ?>"/>
          </div>
        </div>
        <div class="form-group">
          <label>Observații</label>
          <input type="text" name="observatii" class="form-control" value="<?= htmlspecialchars($v['observatii'] ?? '') ?>"/>
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
