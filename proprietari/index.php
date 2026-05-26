<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Proprietari';

$result = $conn->query("SELECT p.*, COUNT(m.id) as nr_masini FROM proprietari p LEFT JOIN masini m ON m.proprietar_id = p.id GROUP BY p.id ORDER BY p.nume");

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Proprietari</h1>
    <?php if (!$isAnalyst): ?><a href="add.php" class="btn btn-primary">+ Adaugă proprietar</a><?php endif; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Nume</th><th>Telefon</th><th>Email</th><th>Mașini</th><th>Înregistrat</th><th></th></tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
          <tr><td colspan="6"><div class="empty-state"><div class="empty-icon"><?= icon('users') ?></div><p>Niciun proprietar înregistrat.</p></div></td></tr>
          <?php else: ?>
          <?php while ($p = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($p['nume']) ?></td>
            <td><?= htmlspecialchars($p['telefon'] ?? '—') ?></td>
            <td><?= htmlspecialchars($p['email'] ?? '—') ?></td>
            <td><?= $p['nr_masini'] ?></td>
            <td class="text-muted"><?= fmt_date($p['created_at']) ?></td>
            <td class="flex">
              <?php if (!$isAnalyst): ?><a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">Editează</a><?php endif; ?>
              <?php if (!$isAnalyst): ?><a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ștergi proprietarul?')">Șterge</a><?php endif; ?>
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
