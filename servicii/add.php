<?php
session_start();
require_once '../includes/auth.php';
if (($_SESSION['rol'] ?? '') === 'analyst') { header('Location: /index.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Adaugă intrare service';
$error = '';

$masini = $conn->query("SELECT id, nr_inmatriculare, marca, model FROM masini ORDER BY nr_inmatriculare");
$preselect_masina = (int)($_GET['masina_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $masina_id      = (int)$_POST['masina_id'];
    $data           = $_POST['data'] ?? date('Y-m-d');
    $km_la_intrare  = (int)$_POST['km_la_intrare'];
    $tip            = $_POST['tip'] ?? 'revizie';
    $descriere      = trim($_POST['descriere'] ?? '');
    $service_extern = trim($_POST['service_extern'] ?? '');

    $denumiri   = $_POST['denumire'] ?? [];
    $cantitati  = $_POST['cantitate'] ?? [];
    $preturi    = $_POST['pret_unitar'] ?? [];

    if (!$masina_id) {
        $error = 'Selectează mașina.';
    } else {
        // Calculare cost total
        $cost_total = 0;
        foreach ($denumiri as $i => $den) {
            if (trim($den) === '') continue;
            $cost_total += (float)$cantitati[$i] * (float)$preturi[$i];
        }

        $stmt = $conn->prepare("INSERT INTO servicii (masina_id, data, km_la_intrare, tip, descriere, cost_total, service_extern) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("isisdss", $masina_id, $data, $km_la_intrare, $tip, $descriere, $cost_total, $service_extern);
        $stmt->execute();
        $service_id = $stmt->insert_id;
        $stmt->close();

        // Salvare interventii
        foreach ($denumiri as $i => $den) {
            if (trim($den) === '') continue;
            $cant  = (float)$cantitati[$i];
            $pret  = (float)$preturi[$i];
            $total = $cant * $pret;
            $stmt2 = $conn->prepare("INSERT INTO interventii_service (service_id, denumire, cantitate, pret_unitar, total) VALUES (?,?,?,?,?)");
            $stmt2->bind_param("isddd", $service_id, $den, $cant, $pret, $total);
            $stmt2->execute();
            $stmt2->close();
        }

        // Update km masina
        $conn->query("UPDATE masini SET km_actuali=$km_la_intrare WHERE id=$masina_id AND km_actuali < $km_la_intrare");

        header("Location: view.php?id=$service_id");
        exit;
    }
}

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Adaugă intrare service</h1>
    <a href="index.php" class="btn btn-secondary">← Înapoi</a>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST" id="form-service">
        <div class="form-row">
          <div class="form-group">
            <label>Mașină *</label>
            <select name="masina_id" class="form-control" required>
              <option value="">— Selectează —</option>
              <?php $masini->data_seek(0); while ($m = $masini->fetch_assoc()): ?>
              <option value="<?= $m['id'] ?>" <?= ($preselect_masina == $m['id'] || (($_POST['masina_id'] ?? 0) == $m['id'])) ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['nr_inmatriculare'].' – '.$m['marca'].' '.$m['model']) ?>
              </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Tip</label>
            <select name="tip" class="form-control">
              <?php foreach (['revizie','reparatie','accident','vopsitorie','altele'] as $t): ?>
              <option value="<?= $t ?>" <?= (($_POST['tip'] ?? 'revizie') === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Data</label>
            <input type="date" name="data" class="form-control" value="<?= $_POST['data'] ?? date('Y-m-d') ?>"/>
          </div>
          <div class="form-group">
            <label>Km la intrare</label>
            <input type="number" name="km_la_intrare" class="form-control" value="<?= (int)($_POST['km_la_intrare'] ?? 0) ?>"/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Service extern</label>
            <input type="text" name="service_extern" class="form-control" value="<?= htmlspecialchars($_POST['service_extern'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Descriere generală</label>
            <input type="text" name="descriere" class="form-control" value="<?= htmlspecialchars($_POST['descriere'] ?? '') ?>"/>
          </div>
        </div>

        <!-- Interventii -->
        <div style="margin-top:20px;margin-bottom:10px;font-size:13px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Piese / Manoperă</div>
        <div style="display:grid;grid-template-columns:2.5fr 0.7fr 1fr 1fr 40px;gap:8px;margin-bottom:6px;font-size:10px;color:var(--muted);text-transform:uppercase">
          <div>Denumire</div><div>Cant.</div><div>Preț/u (RON)</div><div>Total</div><div></div>
        </div>
        <div id="rows">
          <?php
          $rowDenumiri = $_POST['denumire'] ?? [''];
          $rowCant     = $_POST['cantitate'] ?? [1];
          $rowPret     = $_POST['pret_unitar'] ?? [''];
          foreach ($rowDenumiri as $i => $_):
          ?>
          <div class="piesa-row" style="display:grid;grid-template-columns:2.5fr 0.7fr 1fr 1fr 40px;gap:8px;margin-bottom:6px;align-items:center">
            <input type="text" name="denumire[]" class="form-control" placeholder="ex: Filtru ulei" value="<?= htmlspecialchars($rowDenumiri[$i] ?? '') ?>"/>
            <input type="number" name="cantitate[]" class="form-control cant" min="0" step="0.01" value="<?= $rowCant[$i] ?? 1 ?>"/>
            <input type="number" name="pret_unitar[]" class="form-control pret" min="0" step="0.01" value="<?= $rowPret[$i] ?? '' ?>"/>
            <input type="number" class="form-control total-field" readonly placeholder="0.00" style="opacity:.6"/>
            <button type="button" class="btn btn-sm btn-danger rm-row">✕</button>
          </div>
          <?php endforeach; ?>
        </div>
        <button type="button" id="add-row" class="btn btn-sm btn-secondary" style="margin-top:4px">+ Adaugă rând</button>

        <div style="display:flex;justify-content:flex-end;align-items:center;gap:12px;margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">
          <span style="color:var(--muted);font-size:13px">Cost total:</span>
          <span id="grand-total" style="font-size:20px;font-weight:500;color:var(--blue)">0.00 RON</span>
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
function recalc() {
  let total = 0;
  document.querySelectorAll('.piesa-row').forEach(row => {
    const c = parseFloat(row.querySelector('.cant').value) || 0;
    const p = parseFloat(row.querySelector('.pret').value) || 0;
    const t = c * p;
    row.querySelector('.total-field').value = t.toFixed(2);
    total += t;
  });
  document.getElementById('grand-total').textContent = total.toFixed(2) + ' RON';
}

document.getElementById('rows').addEventListener('input', recalc);

document.getElementById('add-row').addEventListener('click', () => {
  const row = document.createElement('div');
  row.className = 'piesa-row';
  row.style.cssText = 'display:grid;grid-template-columns:2.5fr 0.7fr 1fr 1fr 40px;gap:8px;margin-bottom:6px;align-items:center';
  row.innerHTML = `<input type="text" name="denumire[]" class="form-control" placeholder="ex: Filtru ulei"/>
    <input type="number" name="cantitate[]" class="form-control cant" min="0" step="0.01" value="1"/>
    <input type="number" name="pret_unitar[]" class="form-control pret" min="0" step="0.01"/>
    <input type="number" class="form-control total-field" readonly placeholder="0.00" style="opacity:.6"/>
    <button type="button" class="btn btn-sm btn-danger rm-row">✕</button>`;
  document.getElementById('rows').appendChild(row);
});

document.getElementById('rows').addEventListener('click', e => {
  if (e.target.classList.contains('rm-row')) {
    e.target.closest('.piesa-row').remove();
    recalc();
  }
});

recalc();
</script>
<?php require_once '../includes/footer.php'; ?>
