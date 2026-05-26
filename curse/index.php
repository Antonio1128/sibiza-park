<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Curse';

$result = $conn->query("
    SELECT c.*, m.nr_inmatriculare, cl.nume as sofer_nume
    FROM curse c
    JOIN masini m ON m.id=c.masina_id
    JOIN clienti cl ON cl.id=c.sofer_id
    ORDER BY c.data DESC
");

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Curse</h1>
    <?php if (!$isAnalyst): ?><a href="add.php" class="btn btn-primary">+ Adaugă cursă</a><?php endif; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Data</th><th>Mașină</th><th>Șofer</th><th>Destinație</th><th>Km start</th><th>Km final</th><th>Distanță</th><th></th></tr></thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
          <tr><td colspan="8"><div class="empty-state"><div class="empty-icon"><?= icon('route') ?></div><p>Nicio cursă înregistrată.</p></div></td></tr>
          <?php else: while ($c = $result->fetch_assoc()): ?>
          <tr>
            <td><?= fmt_date($c['data']) ?></td>
            <td><?= htmlspecialchars($c['nr_inmatriculare']) ?></td>
            <td><?= htmlspecialchars($c['sofer_nume']) ?></td>
            <td><?= htmlspecialchars($c['destinatie'] ?? '—') ?></td>
            <td><?= number_format($c['km_start']) ?></td>
            <td><?= number_format($c['km_final']) ?></td>
            <td><strong><?= number_format($c['km_final'] - $c['km_start']) ?> km</strong></td>
            <td><?php if (!$isAnalyst): ?><a href="delete.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ștergi?')">Șterge</a><?php endif; ?></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
<?php require_once '../includes/footer.php'; ?>
