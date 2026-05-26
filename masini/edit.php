<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Editează mașină';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM masini WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$m = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$m) { header("Location: index.php"); exit; }

$proprietari = $conn->query("SELECT id, nume FROM clienti ORDER BY nume");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proprietar_id    = (int)$_POST['proprietar_id'];
    $nr_inmatriculare = trim($_POST['nr_inmatriculare'] ?? '');
    $marca            = trim($_POST['marca'] ?? '');
    $model            = trim($_POST['model'] ?? '');
    $an_fabricatie    = (int)$_POST['an_fabricatie'];
    $capacitate_cmc   = (int)$_POST['capacitate_cmc'];
    $tip_motor        = trim($_POST['tip_motor'] ?? '');
    $nr_locuri        = (int)$_POST['nr_locuri'];
    $tonaj            = (float)$_POST['tonaj'];
    $culoare          = trim($_POST['culoare'] ?? '');
    $serie_sasiu      = trim($_POST['serie_sasiu'] ?? '');
    $km_actuali       = (int)$_POST['km_actuali'];

    if (!$nr_inmatriculare || !$proprietar_id) {
        $error = 'Numărul de înmatriculare și proprietarul sunt obligatorii.';
    } else {
        $poza = $m['poza'];
        if (!empty($_FILES['poza']['name'])) {
            $ext = strtolower(pathinfo($_FILES['poza']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
                $error = 'Doar imagini JPG, PNG sau WEBP sunt acceptate.';
            } elseif ($_FILES['poza']['size'] > 5 * 1024 * 1024) {
                $error = 'Imaginea nu poate depăși 5 MB.';
            } else {
                if ($poza && file_exists('../uploads/masini/' . $poza)) {
                    unlink('../uploads/masini/' . $poza);
                }
                $poza = uniqid('car_') . '.' . $ext;
                move_uploaded_file($_FILES['poza']['tmp_name'], '../uploads/masini/' . $poza);
            }
        }
        if (!$error) {
            $stmt = $conn->prepare("UPDATE masini SET proprietar_id=?, nr_inmatriculare=?, marca=?, model=?, an_fabricatie=?, capacitate_cmc=?, tip_motor=?, nr_locuri=?, tonaj=?, culoare=?, serie_sasiu=?, km_actuali=?, poza=? WHERE id=?");
            if (!$stmt) { $error = 'SQL prepare error: ' . $conn->error; }
            else {
                $stmt->bind_param("isssiisidssisi", $proprietar_id, $nr_inmatriculare, $marca, $model, $an_fabricatie, $capacitate_cmc, $tip_motor, $nr_locuri, $tonaj, $culoare, $serie_sasiu, $km_actuali, $poza, $id);
                if (!$stmt->execute()) { $error = 'SQL execute error: ' . $stmt->error; }
                $stmt->close();
                if (!$error) { header("Location: view.php?id=$id"); exit; }
            }
        }
    }
}

$v = $_POST ?: $m;
require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Editează mașină</h1>
    <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">← Înapoi</a>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
          <div class="form-group">
            <label>Număr înmatriculare *</label>
            <input type="text" name="nr_inmatriculare" class="form-control" value="<?= htmlspecialchars($v['nr_inmatriculare']) ?>" required style="text-transform:uppercase"/>
          </div>
          <div class="form-group">
            <label>Client *</label>
            <select name="proprietar_id" class="form-control" required>
              <option value="">— Selectează —</option>
              <?php $proprietari->data_seek(0); while ($p = $proprietari->fetch_assoc()): ?>
              <option value="<?= $p['id'] ?>" <?= ($v['proprietar_id'] == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nume']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Marcă</label>
            <input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($v['marca']) ?>"/>
          </div>
          <div class="form-group">
            <label>Model</label>
            <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($v['model']) ?>"/>
          </div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group">
            <label>An fabricație</label>
            <input type="number" name="an_fabricatie" class="form-control" value="<?= $v['an_fabricatie'] ?>"/>
          </div>
          <div class="form-group">
            <label>Capacitate (cmc)</label>
            <input type="number" name="capacitate_cmc" class="form-control" value="<?= $v['capacitate_cmc'] ?>"/>
          </div>
          <div class="form-group">
            <label>Tip motor</label>
            <select name="tip_motor" class="form-control">
              <option value="">— —</option>
              <?php foreach (['Benzină','Diesel','Electric','Hibrid','GPL'] as $t): ?>
              <option value="<?= $t ?>" <?= ($v['tip_motor'] === $t) ? 'selected' : '' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group">
            <label>Nr. locuri</label>
            <input type="number" name="nr_locuri" class="form-control" value="<?= $v['nr_locuri'] ?>"/>
          </div>
          <div class="form-group">
            <label>Tonaj</label>
            <input type="number" name="tonaj" class="form-control" step="0.01" value="<?= $v['tonaj'] ?>"/>
          </div>
          <div class="form-group">
            <label>Km actuali</label>
            <input type="number" name="km_actuali" class="form-control" value="<?= $v['km_actuali'] ?>"/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Culoare</label>
            <input type="text" name="culoare" class="form-control" value="<?= htmlspecialchars($v['culoare']) ?>"/>
          </div>
          <div class="form-group">
            <label>Serie șasiu (VIN)</label>
            <input type="text" name="serie_sasiu" class="form-control" value="<?= htmlspecialchars($v['serie_sasiu']) ?>"/>
          </div>
        </div>
        <div class="form-group">
          <label>Poză mașină</label>
          <div class="upload-zone" id="uploadZone" onclick="document.getElementById('pozaInput').click()">
            <div class="upload-preview" id="uploadPreview" style="<?= $m['poza'] ? '' : 'display:none' ?>">
              <img id="previewImg" src="<?= $m['poza'] ? '/uploads/masini/'.htmlspecialchars($m['poza']) : '' ?>" alt="preview"/>
            </div>
            <div class="upload-placeholder" id="uploadPlaceholder" style="<?= $m['poza'] ? 'display:none' : '' ?>">
              <?= icon('upload') ?>
              <div style="margin-top:10px">Click pentru a schimba poza</div>
              <div style="font-size:11px;color:var(--muted);margin-top:4px">JPG, PNG, WEBP · max 5 MB</div>
            </div>
          </div>
          <input type="file" name="poza" id="pozaInput" accept="image/*" style="display:none" onchange="previewPhoto(this)"/>
        </div>

        <div class="form-actions">
          <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">Anulează</a>
          <button type="submit" class="btn btn-primary">Salvează</button>
        </div>
      </form>
    </div>
  </div>

</div>
</div>
<script>
function previewPhoto(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('previewImg').src = e.target.result;
    document.getElementById('uploadPreview').style.display = 'block';
    document.getElementById('uploadPlaceholder').style.display = 'none';
  };
  reader.readAsDataURL(input.files[0]);
}
</script>
<?php require_once '../includes/footer.php'; ?>
