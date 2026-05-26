<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Service';

$result = $conn->query("
    SELECT s.*, m.nr_inmatriculare, m.marca, m.model
    FROM servicii s
    JOIN masini m ON m.id = s.masina_id
    ORDER BY s.data DESC
");

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Service</h1>
    <?php if (!$isAnalyst): ?><a href="add.php" class="btn btn-primary">+ Adaugă intrare</a><?php endif; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Mașină</th><th>Data</th><th>Tip</th><th>Km</th><th>Cost total</th><th>Service extern</th><th></th></tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
          <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><?= icon('wrench') ?></div><p>Nicio intrare service înregistrată.</p></div></td></tr>
          <?php else: ?>
          <?php while ($s = $result->fetch_assoc()): ?>
          <tr>
            <td><strong><?= htmlspecialchars($s['nr_inmatriculare']) ?></strong> <span class="text-muted"><?= htmlspecialchars($s['marca'].' '.$s['model']) ?></span></td>
            <td><?= fmt_date($s['data']) ?></td>
            <td><span class="badge badge-<?= $s['tip'] ?>"><?= ucfirst($s['tip']) ?></span></td>
            <td><?= number_format($s['km_la_intrare']) ?> km</td>
            <td><?= number_format($s['cost_total'], 2, ',', '.') ?> RON</td>
            <td><?= htmlspecialchars($s['service_extern'] ?? '—') ?></td>
            <td class="flex">
              <a href="view.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Vezi</a>
              <?php if (!$isAnalyst): ?><a href="delete.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ștergi?')">Șterge</a><?php endif; ?>
            </td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
<?php require_once '../includes/footer.php'; ?>
