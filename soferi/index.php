<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Șoferi';

$result = $conn->query("SELECT * FROM soferi ORDER BY nume");
$today  = date('Y-m-d');
$soon   = date('Y-m-d', strtotime('+30 days'));

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Șoferi</h1>
    <?php if (!$isAnalyst): ?><a href="add.php" class="btn btn-primary">+ Adaugă șofer</a><?php endif; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Nume</th><th>Telefon</th><th>Nr. permis</th><th>Categorii</th><th>Expiră permis</th><th></th></tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
          <tr><td colspan="6"><div class="empty-state"><div class="empty-icon"><?= icon('user') ?></div><p>Niciun șofer înregistrat.</p></div></td></tr>
          <?php else: ?>
          <?php while ($s = $result->fetch_assoc()):
            $exp = $s['data_expirare_permis'];
            $expClass = '';
            if ($exp && $exp < $today) $expClass = 'style="color:#fca5a5"';
            elseif ($exp && $exp <= $soon) $expClass = 'style="color:#FFDCCD"';
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($s['nume']) ?></strong></td>
            <td><?= htmlspecialchars($s['telefon'] ?? '—') ?></td>
            <td><?= htmlspecialchars($s['nr_permis'] ?? '—') ?></td>
            <td><?= htmlspecialchars($s['categorie_permis'] ?? '—') ?></td>
            <td <?= $expClass ?>><?= fmt_date($exp) ?></td>
            <td class="flex">
              <?php if (!$isAnalyst): ?><a href="edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Editează</a><?php endif; ?>
              <?php if (!$isAnalyst): ?><a href="delete.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ștergi șoferul?')">Șterge</a><?php endif; ?>
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
