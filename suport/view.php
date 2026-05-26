<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
if ($_SESSION['rol'] !== 'admin') { header("Location: /index.php"); exit; }

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT t.*, u.username FROM tichete t JOIN utilizatori u ON u.id=t.user_id WHERE t.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$t = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$t) { header("Location: index.php"); exit; }

$pageTitle = 'Tichet #'.$id;
$error = '';

// Schimbare status
if (isset($_POST['change_status']) && in_array($_POST['status'] ?? '', ['nou','in_lucru','rezolvat'])) {
    $newStatus = $_POST['status'];
    $stmtS = $conn->prepare("UPDATE tichete SET status=? WHERE id=?");
    $stmtS->bind_param("si", $newStatus, $id);
    $stmtS->execute();
    $stmtS->close();
    $t['status'] = $newStatus;
}

// Adaugare raspuns
if (isset($_POST['raspuns'])) {
    $mesaj = trim($_POST['mesaj'] ?? '');
    if ($mesaj) {
        $uid = (int)$_SESSION['user_id'];
        $stmtR = $conn->prepare("INSERT INTO tichete_raspunsuri (tichet_id, user_id, mesaj) VALUES (?,?,?)");
        $stmtR->bind_param("iis", $id, $uid, $mesaj);
        $stmtR->execute();
        $stmtR->close();
        // auto: daca era nou -> in_lucru
        if ($t['status'] === 'nou') {
            $conn->query("UPDATE tichete SET status='in_lucru' WHERE id=$id");
            $t['status'] = 'in_lucru';
        }
        header("Location: view.php?id=$id");
        exit;
    }
}

// Raspunsuri
$raspunsuri = $conn->query("
    SELECT r.*, u.username, u.rol
    FROM tichete_raspunsuri r
    JOIN utilizatori u ON u.id = r.user_id
    WHERE r.tichet_id = $id
    ORDER BY r.created_at ASC
");

$tipLabel = ['cerere_masina'=>'Cerere mașină','feedback'=>'Feedback','ajutor'=>'Ajutor'][$t['tip']] ?? $t['tip'];

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <div>
      <h1>Tichet #<?= $id ?> <span class="text-muted" style="font-size:16px"><?= htmlspecialchars($tipLabel) ?></span></h1>
      <div class="text-muted" style="margin-top:4px">De la <strong><?= htmlspecialchars($t['username']) ?></strong> · <?= date('d.m.Y H:i', strtotime($t['created_at'])) ?></div>
    </div>
    <div class="flex">
      <form method="POST" class="flex">
        <select name="status" class="form-control" style="width:auto;padding:7px 12px">
          <?php foreach (['nou'=>'Nou','in_lucru'=>'În lucru','rezolvat'=>'Rezolvat'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= $t['status']===$v?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
        <button name="change_status" class="btn btn-secondary">Salvează status</button>
      </form>
      <a href="index.php" class="btn btn-secondary">← Înapoi</a>
    </div>
  </div>

  <!-- Mesaj original -->
  <div class="ticket-msg ticket-msg-user">
    <div class="ticket-msg-header">
      <span class="ticket-msg-author"><?= icon('user', 'icon icon-sm') ?> <?= htmlspecialchars($t['username']) ?></span>
      <span class="ticket-msg-time"><?= date('d.m.Y H:i', strtotime($t['created_at'])) ?></span>
    </div>
    <div class="ticket-msg-subject"><?= htmlspecialchars($t['subiect']) ?></div>
    <div class="ticket-msg-body"><?= nl2br(htmlspecialchars($t['mesaj'])) ?></div>
  </div>

  <!-- Raspunsuri -->
  <?php while ($r = $raspunsuri->fetch_assoc()):
    $isAdmin = $r['rol'] === 'admin';
  ?>
  <div class="ticket-msg <?= $isAdmin ? 'ticket-msg-admin' : 'ticket-msg-user' ?>">
    <div class="ticket-msg-header">
      <span class="ticket-msg-author"><?= $isAdmin ? icon('settings', 'icon icon-sm') : icon('user', 'icon icon-sm') ?> <?= htmlspecialchars($r['username']) ?> <?= $isAdmin ? '<span class="badge badge-admin" style="font-size:9px">Admin</span>' : '' ?></span>
      <span class="ticket-msg-time"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></span>
    </div>
    <div class="ticket-msg-body"><?= nl2br(htmlspecialchars($r['mesaj'])) ?></div>
  </div>
  <?php endwhile; ?>

  <!-- Formular raspuns -->
  <?php if ($t['status'] !== 'rezolvat'): ?>
  <div class="card">
    <div class="card-header"><h2>Răspunde</h2></div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="status" value="rezolvat"/>
        <div class="form-group">
          <textarea name="mesaj" class="form-control" rows="4" placeholder="Scrie răspunsul..." style="resize:vertical"></textarea>
        </div>
        <div class="form-actions">
          <button name="raspuns" class="btn btn-primary">Trimite răspuns</button>
          <button name="change_status" class="btn btn-secondary">Marchează rezolvat</button>
        </div>
      </form>
    </div>
  </div>
  <?php else: ?>
  <div class="alert alert-success">✓ Tichet rezolvat.</div>
  <?php endif; ?>

</div>
</div>
<?php require_once '../includes/footer.php'; ?>
