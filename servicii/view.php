<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT s.*, m.nr_inmatriculare, m.marca, m.model FROM servicii s JOIN masini m ON m.id=s.masina_id WHERE s.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$s) { header("Location: index.php"); exit; }

$pageTitle = 'Service – '.$s['nr_inmatriculare'];
$interventii = $conn->query("SELECT * FROM interventii_service WHERE service_id=$id");

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Service – <?= htmlspecialchars($s['nr_inmatriculare']) ?></h1>
    <div class="flex">
      <a href="../masini/view.php?id=<?= $s['masina_id'] ?>" class="btn btn-secondary">← Mașină</a>
      <a href="delete.php?id=<?= $id ?>" class="btn btn-danger" onclick="return confirm('Ștergi?')">Șterge</a>
    </div>
  </div>

  <div class="card" style="margin-bottom:20px">
    <div class="card-body">
      <div class="form-row form-row-3">
        <div><div class="text-muted">Mașină</div><strong><?= htmlspecialchars($s['marca'].' '.$s['model']) ?></strong></div>
        <div><div class="text-muted">Data</div><strong><?= fmt_date($s['data']) ?></strong></div>
        <div><div class="text-muted">Tip</div><span class="badge badge-<?= $s['tip'] ?>"><?= ucfirst($s['tip']) ?></span></div>
        <div><div class="text-muted">Km la intrare</div><strong><?= number_format($s['km_la_intrare']) ?> km</strong></div>
        <div><div class="text-muted">Service extern</div><strong><?= htmlspecialchars($s['service_extern'] ?? '—') ?></strong></div>
        <div><div class="text-muted">Cost total</div><strong style="color:var(--blue);font-size:18px"><?= number_format($s['cost_total'], 2, ',', '.') ?> RON</strong></div>
      </div>
      <?php if ($s['descriere']): ?>
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)">
        <div class="text-muted" style="margin-bottom:4px">Descriere</div>
        <div><?= htmlspecialchars($s['descriere']) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h2>Piese / Manoperă</h2></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Denumire</th><th>Cantitate</th><th>Preț/u</th><th>Total</th></tr></thead>
        <tbody>
          <?php if ($interventii->num_rows === 0): ?>
          <tr><td colspan="4" class="text-center text-muted" style="padding:20px">Nicio intervenție înregistrată.</td></tr>
          <?php else: while ($i = $interventii->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($i['denumire']) ?></td>
            <td><?= $i['cantitate'] ?></td>
            <td><?= number_format($i['pret_unitar'], 2, ',', '.') ?> RON</td>
            <td><strong><?= number_format($i['total'], 2, ',', '.') ?> RON</strong></td>
          </tr>
          <?php endwhile; endif; ?>
          <tr>
            <td colspan="3" style="text-align:right;color:var(--muted)">Total</td>
            <td><strong style="color:var(--blue);font-size:16px"><?= number_format($s['cost_total'], 2, ',', '.') ?> RON</strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
<?php require_once '../includes/footer.php'; ?>
