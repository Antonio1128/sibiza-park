<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Asigurări';

$result = $conn->query("SELECT a.*, m.nr_inmatriculare, m.marca, m.model FROM asigurari a JOIN masini m ON m.id=a.masina_id ORDER BY a.data_expirare ASC");
$today  = date('Y-m-d');
$soon   = date('Y-m-d', strtotime('+30 days'));

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Asigurări</h1>
    <?php if (!$isAnalyst): ?><a href="add.php" class="btn btn-primary">+ Adaugă asigurare</a><?php endif; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Mașină</th><th>Tip</th><th>Companie</th><th>Nr. poliță</th><th>Start</th><th>Expiră</th><th>Preț</th><th></th></tr></thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
          <tr><td colspan="8"><div class="empty-state"><div class="empty-icon"><?= icon('shield') ?></div><p>Nicio asigurare înregistrată.</p></div></td></tr>
          <?php else: while ($a = $result->fetch_assoc()):
            $exp = $a['data_expirare'];
            $expStyle = '';
            if ($exp < $today) $expStyle = 'style="color:#fca5a5"';
            elseif ($exp <= $soon) $expStyle = 'style="color:#FFDCCD"';
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($a['nr_inmatriculare']) ?></strong> <span class="text-muted"><?= htmlspecialchars($a['marca'].' '.$a['model']) ?></span></td>
            <td><span class="badge badge-<?= $a['tip'] ?>"><?= $a['tip'] ?></span></td>
            <td><?= htmlspecialchars($a['companie'] ?? '—') ?></td>
            <td><?= htmlspecialchars($a['nr_polita'] ?? '—') ?></td>
            <td><?= fmt_date($a['data_start']) ?></td>
            <td <?= $expStyle ?>><?= fmt_date($a['data_expirare']) ?></td>
            <td><?= number_format($a['pret'], 2, ',', '.') ?> RON</td>
            <td class="flex">
              <?php if (!$isAnalyst): ?><a href="edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-secondary">Editează</a><?php endif; ?>
              <?php if (!$isAnalyst): ?><a href="delete.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ștergi?')">Șterge</a><?php endif; ?>
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
