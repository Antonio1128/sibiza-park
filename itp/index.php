<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'ITP';

$result = $conn->query("
    SELECT i.*, m.nr_inmatriculare, m.marca, m.model
    FROM itp i
    JOIN masini m ON m.id = i.masina_id
    ORDER BY i.data_expirare ASC
");
$today = date('Y-m-d');
$soon  = date('Y-m-d', strtotime('+30 days'));

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>ITP</h1>
    <?php if (!$isAnalyst): ?><a href="add.php" class="btn btn-primary">+ Adaugă ITP</a><?php endif; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Mașină</th><th>Data efectuare</th><th>Data expirare</th><th>Stație</th><th>Cost</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
          <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><?= icon('search') ?></div><p>Niciun ITP înregistrat.</p></div></td></tr>
          <?php else: while ($i = $result->fetch_assoc()):
            $exp = $i['data_expirare'];
            if ($exp < $today) { $status = 'Expirat'; $cls = 'badge-accident'; $rowStyle = 'style="color:#fca5a5"'; }
            elseif ($exp <= $soon) { $status = 'Expiră curând'; $cls = 'badge-reparatie'; $rowStyle = 'style="color:#FFDCCD"'; }
            else { $status = 'Valabil'; $cls = 'badge-activ'; $rowStyle = ''; }
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($i['nr_inmatriculare']) ?></strong> <span class="text-muted"><?= htmlspecialchars($i['marca'].' '.$i['model']) ?></span></td>
            <td><?= fmt_date($i['data_efectuare']) ?></td>
            <td <?= $rowStyle ?>><?= fmt_date($i['data_expirare']) ?></td>
            <td><?= htmlspecialchars($i['statie'] ?? '—') ?></td>
            <td><?= number_format($i['cost'], 2, ',', '.') ?> RON</td>
            <td><span class="badge <?= $cls ?>"><?= $status ?></span></td>
            <td class="flex">
              <?php if (!$isAnalyst): ?><a href="edit.php?id=<?= $i['id'] ?>" class="btn btn-sm btn-secondary">Editează</a><?php endif; ?>
              <?php if (!$isAnalyst): ?><a href="delete.php?id=<?= $i['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ștergi ITP-ul?')">Șterge</a><?php endif; ?>
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
