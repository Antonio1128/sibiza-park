<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
if ($_SESSION['rol'] !== 'admin') { header("Location: /index.php"); exit; }
$pageTitle = 'Suport – Tichete';

$filter = $_GET['status'] ?? 'toate';
$where  = $filter !== 'toate' ? "WHERE t.status = '$filter'" : '';

$tichete = $conn->query("
    SELECT t.*, u.username
    FROM tichete t
    JOIN utilizatori u ON u.id = t.user_id
    $where
    ORDER BY
      FIELD(t.status,'nou','in_lucru','rezolvat'),
      t.created_at DESC
");

$counts = $conn->query("
    SELECT status, COUNT(*) as nr FROM tichete GROUP BY status
")->fetch_all(MYSQLI_ASSOC);
$cnt = ['nou'=>0,'in_lucru'=>0,'rezolvat'=>0,'toate'=>0];
foreach ($counts as $c) { $cnt[$c['status']] = $c['nr']; $cnt['toate'] += $c['nr']; }

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Tichete suport</h1>
  </div>

  <!-- Filtre status -->
  <div class="ticket-filters">
    <?php foreach ([
      'toate'    => ['label'=>'Toate',       'cls'=>''],
      'nou'      => ['label'=>'Noi',         'cls'=>'tf-nou'],
      'in_lucru' => ['label'=>'În lucru',    'cls'=>'tf-lucru'],
      'rezolvat' => ['label'=>'Rezolvate',   'cls'=>'tf-rezolvat'],
    ] as $key => $info): ?>
    <a href="?status=<?= $key ?>" class="ticket-filter-btn <?= $info['cls'] ?> <?= $filter===$key?'active':'' ?>">
      <?= $info['label'] ?> <span class="tf-count"><?= $cnt[$key] ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>ID</th><th>Utilizator</th><th>Tip</th><th>Subiect</th><th>Status</th><th>Data</th><th></th></tr>
        </thead>
        <tbody>
          <?php if ($tichete->num_rows === 0): ?>
          <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><?= icon('ticket') ?></div><p>Niciun tichet.</p></div></td></tr>
          <?php else: while ($t = $tichete->fetch_assoc()):
            $tipLabel = ['cerere_masina'=>'Cerere mașină','feedback'=>'Feedback','ajutor'=>'Ajutor'][$t['tip']] ?? $t['tip'];
            $tipCls   = ['cerere_masina'=>'badge-RCA','feedback'=>'badge-revizie','ajutor'=>'badge-reparatie'][$t['tip']] ?? '';
            $stCls    = ['nou'=>'badge-accident','in_lucru'=>'badge-reparatie','rezolvat'=>'badge-activ'][$t['status']] ?? '';
            $stLabel  = ['nou'=>'Nou','in_lucru'=>'În lucru','rezolvat'=>'Rezolvat'][$t['status']] ?? '';
          ?>
          <tr>
            <td class="text-muted">#<?= $t['id'] ?></td>
            <td><strong><?= htmlspecialchars($t['username']) ?></strong></td>
            <td><span class="badge <?= $tipCls ?>"><?= $tipLabel ?></span></td>
            <td><?= htmlspecialchars($t['subiect']) ?></td>
            <td><span class="badge <?= $stCls ?>"><?= $stLabel ?></span></td>
            <td class="text-muted"><?= date('d.m.Y H:i', strtotime($t['created_at'])) ?></td>
            <td><a href="view.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-primary">Răspunde</a></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
<?php require_once '../includes/footer.php'; ?>
