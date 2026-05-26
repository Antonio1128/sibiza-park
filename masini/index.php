<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Mașini';

$result = $conn->query("
    SELECT m.*, c.nume as proprietar_nume
    FROM masini m
    LEFT JOIN clienti c ON c.id = m.proprietar_id
    ORDER BY m.nr_inmatriculare
");

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Mașini</h1>
    <?php if (!$isAnalyst): ?><a href="add.php" class="btn btn-primary">+ Adaugă mașină</a><?php endif; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th></th><th>Nr. înmatriculare</th><th>Marcă / Model</th><th>An</th><th>Motor</th><th>Km</th><th>Client</th><th></th></tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
          <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><?= icon('car') ?></div><p>Nicio mașină înregistrată.</p></div></td></tr>
          <?php else: ?>
          <?php while ($m = $result->fetch_assoc()): ?>
          <tr>
            <td style="width:52px;padding:6px 8px 6px 14px">
              <?php if ($m['poza']): ?>
              <img src="/uploads/masini/<?= htmlspecialchars($m['poza']) ?>" style="width:44px;height:36px;object-fit:cover;border-radius:6px;border:1px solid var(--border);display:block"/>
              <?php else: ?>
              <div style="width:44px;height:36px;border-radius:6px;border:1px solid var(--border);background:var(--bg3);display:flex;align-items:center;justify-content:center;color:var(--muted2)"><?= icon('car', 'icon icon-sm') ?></div>
              <?php endif; ?>
            </td>
            <td><strong><?= htmlspecialchars($m['nr_inmatriculare']) ?></strong></td>
            <td><?= htmlspecialchars($m['marca'].' '.$m['model']) ?></td>
            <td><?= $m['an_fabricatie'] ?></td>
            <td><?= htmlspecialchars($m['tip_motor'] ?? '—') ?></td>
            <td><?= number_format($m['km_actuali']) ?> km</td>
            <td><?= htmlspecialchars($m['proprietar_nume'] ?? '—') ?></td>
            <td class="flex">
              <a href="view.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-secondary">Vezi</a>
              <?php if (!$isAnalyst): ?><a href="edit.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-secondary">Editează</a><?php endif; ?>
              <?php if (!$isAnalyst): ?><a href="delete.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ștergi mașina?')">Șterge</a><?php endif; ?>
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
