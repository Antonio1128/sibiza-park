<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Cererile mele';
$uid = (int)$_SESSION['user_id'];

$tichete = $conn->prepare("SELECT * FROM tichete WHERE user_id=? ORDER BY created_at DESC");
$tichete->bind_param("i", $uid);
$tichete->execute();
$result = $tichete->get_result();
$tichete->close();

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Cererile mele</h1>
    <a href="/suport/submit.php" class="btn btn-primary">+ Cerere nouă</a>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Tip</th><th>Subiect</th><th>Status</th><th>Data</th><th></th></tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
          <tr><td colspan="5"><div class="empty-state"><div class="empty-icon"><?= icon('ticket') ?></div><p>Nu ai cereri trimise.</p></div></td></tr>
          <?php else: while ($t = $result->fetch_assoc()):
            $tipLabel = ['cerere_masina'=>'Cerere mașină','feedback'=>'Feedback','ajutor'=>'Ajutor'][$t['tip']] ?? $t['tip'];
            $tipCls   = ['cerere_masina'=>'badge-RCA','feedback'=>'badge-revizie','ajutor'=>'badge-reparatie'][$t['tip']] ?? '';
            $stCls    = ['nou'=>'badge-accident','in_lucru'=>'badge-reparatie','rezolvat'=>'badge-activ'][$t['status']] ?? '';
            $stLabel  = ['nou'=>'Nou','in_lucru'=>'În lucru','rezolvat'=>'Rezolvat'][$t['status']] ?? '';
          ?>
          <tr>
            <td><span class="badge <?= $tipCls ?>"><?= $tipLabel ?></span></td>
            <td><?= htmlspecialchars($t['subiect']) ?></td>
            <td><span class="badge <?= $stCls ?>"><?= $stLabel ?></span></td>
            <td class="text-muted"><?= date('d.m.Y H:i', strtotime($t['created_at'])) ?></td>
            <td><a href="detalii.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-secondary">Vezi răspuns</a></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
<?php require_once '../includes/footer.php'; ?>
