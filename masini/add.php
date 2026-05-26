<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Adaugă mașină';
$error = '';

$proprietari = $conn->query("SELECT id, nume FROM clienti ORDER BY nume");

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
        $poza = null;
        if (!empty($_FILES['poza']['name'])) {
            $ext = strtolower(pathinfo($_FILES['poza']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
                $error = 'Doar imagini JPG, PNG sau WEBP sunt acceptate.';
            } elseif ($_FILES['poza']['size'] > 5 * 1024 * 1024) {
                $error = 'Imaginea nu poate depăși 5 MB.';
            } else {
                $poza = uniqid('car_') . '.' . $ext;
                move_uploaded_file($_FILES['poza']['tmp_name'], '../uploads/masini/' . $poza);
            }
        }
        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO masini (proprietar_id, nr_inmatriculare, marca, model, an_fabricatie, capacitate_cmc, tip_motor, nr_locuri, tonaj, culoare, serie_sasiu, km_actuali, poza) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("isssiiisidsis", $proprietar_id, $nr_inmatriculare, $marca, $model, $an_fabricatie, $capacitate_cmc, $tip_motor, $nr_locuri, $tonaj, $culoare, $serie_sasiu, $km_actuali, $poza);
            $stmt->execute();
            $newId = $stmt->insert_id;
            $stmt->close();
            header("Location: view.php?id=$newId");
            exit;
        }
    }
}

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Adaugă mașină</h1>
    <a href="index.php" class="btn btn-secondary">← Înapoi</a>
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
            <input type="text" name="nr_inmatriculare" class="form-control" value="<?= htmlspecialchars($_POST['nr_inmatriculare'] ?? '') ?>" required style="text-transform:uppercase"/>
          </div>
          <div class="form-group">
            <label>Client *</label>
            <select name="proprietar_id" class="form-control" required>
              <option value="">— Selectează —</option>
              <?php $proprietari->data_seek(0); while ($p = $proprietari->fetch_assoc()): ?>
              <option value="<?= $p['id'] ?>" <?= (($_POST['proprietar_id'] ?? '') == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nume']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Marcă</label>
            <input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Model</label>
            <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($_POST['model'] ?? '') ?>"/>
          </div>
        </div>

        <div class="form-row form-row-3">
          <div class="form-group">
            <label>An fabricație</label>
            <input type="number" name="an_fabricatie" class="form-control" min="1900" max="2030" value="<?= htmlspecialchars($_POST['an_fabricatie'] ?? date('Y')) ?>"/>
          </div>
          <div class="form-group">
            <label>Capacitate (cmc)</label>
            <input type="number" name="capacitate_cmc" class="form-control" value="<?= htmlspecialchars($_POST['capacitate_cmc'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Tip motor</label>
            <select name="tip_motor" class="form-control">
              <option value="">— —</option>
              <?php foreach (['Benzină','Diesel','Electric','Hibrid','GPL'] as $t): ?>
              <option value="<?= $t ?>" <?= (($_POST['tip_motor'] ?? '') === $t) ? 'selected' : '' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row form-row-3">
          <div class="form-group">
            <label>Nr. locuri</label>
            <input type="number" name="nr_locuri" class="form-control" min="1" max="100" value="<?= htmlspecialchars($_POST['nr_locuri'] ?? 5) ?>"/>
          </div>
          <div class="form-group">
            <label>Tonaj</label>
            <input type="number" name="tonaj" class="form-control" step="0.01" value="<?= htmlspecialchars($_POST['tonaj'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Km actuali</label>
            <input type="number" name="km_actuali" class="form-control" value="<?= htmlspecialchars($_POST['km_actuali'] ?? 0) ?>"/>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Culoare</label>
            <input type="text" name="culoare" class="form-control" value="<?= htmlspecialchars($_POST['culoare'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Serie șasiu (VIN)</label>
            <input type="text" name="serie_sasiu" class="form-control" value="<?= htmlspecialchars($_POST['serie_sasiu'] ?? '') ?>"/>
          </div>
        </div>

        <div class="form-group">
          <label>Poză mașină</label>
          <div class="upload-zone" id="uploadZone" onclick="document.getElementById('pozaInput').click()">
            <div class="upload-preview" id="uploadPreview" style="display:none">
              <img id="previewImg" src="" alt="preview"/>
            </div>
            <div class="upload-placeholder" id="uploadPlaceholder">
              <?= icon('upload') ?>
              <div style="margin-top:10px">Click pentru a adăuga o poză</div>
              <div style="font-size:11px;color:var(--muted);margin-top:4px">JPG, PNG, WEBP · max 5 MB</div>
            </div>
          </div>
          <input type="file" name="poza" id="pozaInput" accept="image/*" style="display:none" onchange="previewPhoto(this)"/>
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
