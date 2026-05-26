<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Anvelope';

$result = $conn->query("
    SELECT a.*, m.nr_inmatriculare, m.marca, m.model
    FROM seturi_anvelope a
    JOIN masini m ON m.id=a.masina_id
    ORDER BY m.nr_inmatriculare, a.tip_sezon
");

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Anvelope</h1>
    <?php if (!$isAnalyst): ?><a href="add.php" class="btn btn-primary">+ Adaugă set</a><?php endif; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Mașină</th><th>Sezon</th><th>Marcă</th><th>Dimensiune</th><th>Tip</th><th>Stare</th><th>Buc.</th><th></th></tr></thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
          <tr><td colspan="8"><div class="empty-state"><div class="empty-icon"><?= icon('tire') ?></div><p>Niciun set de anvelope înregistrat.</p></div></td></tr>
          <?php else: while ($a = $result->fetch_assoc()): ?>
          <tr>
            <td><strong><?= htmlspecialchars($a['nr_inmatriculare']) ?></strong> <span class="text-muted"><?= htmlspecialchars($a['marca_masina'] ?? $a['marca']) ?></span></td>
            <td><span class="badge badge-<?= $a['tip_sezon'] === 'vara' ? 'RCA' : ($a['tip_sezon'] === 'iarna' ? 'revizie' : 'altele') ?>"><?= ucfirst($a['tip_sezon']) ?></span></td>
            <td><?= htmlspecialchars($a['marca'] ?? '—') ?></td>
            <td><?= htmlspecialchars($a['dimensiune'] ?? '—') ?></td>
            <td><?= ucfirst($a['tip_set']) ?></td>
            <td><span class="badge <?= $a['stare'] === 'montate' ? 'badge-activ' : 'badge-inactiv' ?>"><?= ucfirst($a['stare']) ?></span></td>
            <td><?= $a['nr_bucati'] ?></td>
            <td><?php if (!$isAnalyst): ?><a href="delete.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ștergi?')">Șterge</a><?php endif; ?></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
<?php require_once '../includes/footer.php'; ?>
