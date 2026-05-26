<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Clienți';

$result = $conn->query("
    SELECT c.*, COUNT(m.id) as nr_masini
    FROM clienti c
    LEFT JOIN masini m ON m.proprietar_id = c.id
    GROUP BY c.id
    ORDER BY c.nume
");
$today = date('Y-m-d');
$soon  = date('Y-m-d', strtotime('+30 days'));

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Clienți</h1>
    <?php if (!$isAnalyst): ?><a href="add.php" class="btn btn-primary">+ Adaugă client</a><?php endif; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Nume</th><th>Telefon</th><th>Email</th><th>Nr. permis</th><th>Expiră permis</th><th>Mașini</th><th></th></tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
          <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><?= icon('user') ?></div><p>Niciun client înregistrat.</p></div></td></tr>
          <?php else: while ($c = $result->fetch_assoc()):
            $exp = $c['data_expirare_permis'];
            $expStyle = '';
            if ($exp && $exp < $today) $expStyle = 'style="color:#fca5a5"';
            elseif ($exp && $exp <= $soon) $expStyle = 'style="color:#FFDCCD"';
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($c['nume']) ?></strong></td>
            <td><?= htmlspecialchars($c['telefon'] ?? '—') ?></td>
            <td><?= htmlspecialchars($c['email'] ?? '—') ?></td>
            <td><?= htmlspecialchars($c['nr_permis'] ?? '—') ?></td>
            <td <?= $expStyle ?>><?= fmt_date($exp) ?></td>
            <td><?= $c['nr_masini'] ?></td>
            <td class="flex">
              <?php if (!$isAnalyst): ?><a href="edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">Editează</a><?php endif; ?>
              <?php if (!$isAnalyst): ?><a href="delete.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ștergi clientul?')">Șterge</a><?php endif; ?>
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
