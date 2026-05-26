<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Detalii cerere';
$uid = (int)$_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM tichete WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $uid);
$stmt->execute();
$t = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$t) { header("Location: mele.php"); exit; }

if (isset($_POST['reply'])) {
    $mesaj = trim($_POST['mesaj'] ?? '');
    if ($mesaj && $t['status'] !== 'rezolvat') {
        $stmtR = $conn->prepare("INSERT INTO tichete_raspunsuri (tichet_id, user_id, mesaj) VALUES (?,?,?)");
        $stmtR->bind_param("iis", $id, $uid, $mesaj);
        $stmtR->execute();
        $stmtR->close();
        header("Location: detalii.php?id=$id");
        exit;
    }
}

$raspunsuri = $conn->query("
    SELECT r.*, u.username, u.rol
    FROM tichete_raspunsuri r
    JOIN utilizatori u ON u.id = r.user_id
    WHERE r.tichet_id = $id
    ORDER BY r.created_at ASC
");

$tipLabel = ['cerere_masina'=>'Cerere mașină','feedback'=>'Feedback','ajutor'=>'Ajutor'][$t['tip']] ?? $t['tip'];
$stCls    = ['nou'=>'badge-accident','in_lucru'=>'badge-reparatie','rezolvat'=>'badge-activ'][$t['status']] ?? '';
$stLabel  = ['nou'=>'Nou','in_lucru'=>'În lucru','rezolvat'=>'Rezolvat'][$t['status']] ?? '';

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <div>
      <h1><?= htmlspecialchars($tipLabel) ?> <span class="badge <?= $stCls ?>"><?= $stLabel ?></span></h1>
      <div class="text-muted" style="margin-top:4px"><?= date('d.m.Y H:i', strtotime($t['created_at'])) ?></div>
    </div>
    <a href="mele.php" class="btn btn-secondary">← Înapoi</a>
  </div>

  <!-- Mesajul original -->
  <div class="ticket-msg ticket-msg-user">
    <div class="ticket-msg-header">
      <span class="ticket-msg-author"><?= icon('user', 'icon icon-sm') ?> Tu</span>
      <span class="ticket-msg-time"><?= date('d.m.Y H:i', strtotime($t['created_at'])) ?></span>
    </div>
    <div class="ticket-msg-subject"><?= htmlspecialchars($t['subiect']) ?></div>
    <div class="ticket-msg-body"><?= nl2br(htmlspecialchars($t['mesaj'])) ?></div>
  </div>

  <!-- Raspunsuri -->
  <?php if ($raspunsuri->num_rows === 0): ?>
  <div class="alert alert-info"><?= icon('inbox') ?> Cererea ta este în așteptare. Echipa de suport îți va răspunde în curând.</div>
  <?php else: while ($r = $raspunsuri->fetch_assoc()):
    $isAdmin = $r['rol'] === 'admin';
  ?>
  <div class="ticket-msg <?= $isAdmin ? 'ticket-msg-admin' : 'ticket-msg-user' ?>">
    <div class="ticket-msg-header">
      <span class="ticket-msg-author"><?= $isAdmin ? icon('settings', 'icon icon-sm') . ' Suport' : icon('user', 'icon icon-sm') . ' Tu' ?></span>
      <span class="ticket-msg-time"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></span>
    </div>
    <div class="ticket-msg-body"><?= nl2br(htmlspecialchars($r['mesaj'])) ?></div>
  </div>
  <?php endwhile; endif; ?>

  <!-- Clientul poate raspunde -->
  <?php if ($t['status'] !== 'rezolvat'): ?>
  <div class="card">
    <div class="card-header"><h2>Răspunde</h2></div>
    <div class="card-body">
      <form method="POST">
        <div class="form-group">
          <textarea name="mesaj" class="form-control" rows="3" placeholder="Scrie un mesaj..." required style="resize:vertical"></textarea>
        </div>
        <div class="form-actions">
          <button name="reply" class="btn btn-primary">Trimite</button>
        </div>
      </form>
    </div>
  </div>
  <?php else: ?>
  <div class="alert alert-success">✓ Această cerere a fost rezolvată.</div>
  <?php endif; ?>

</div>
</div>
<?php require_once '../includes/footer.php'; ?>
