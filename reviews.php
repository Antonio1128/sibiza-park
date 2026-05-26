<?php
session_start();
require_once 'includes/auth.php';
require_once 'config/db.php';

$pageTitle = 'Recenzii – Sibiza Park';

$reviews = $conn->query("
    SELECT t.subiect, t.mesaj, t.stele, t.created_at, u.username
    FROM tichete t
    JOIN utilizatori u ON u.id = t.user_id
    WHERE t.tip = 'feedback' AND t.stele IS NOT NULL AND t.stele > 0
    ORDER BY t.created_at DESC
");

$row = $conn->query("SELECT ROUND(AVG(stele),1), COUNT(*) FROM tichete WHERE tip='feedback' AND stele > 0")->fetch_row();
$avgStele = (float)($row[0] ?? 0);
$totalReviews = (int)($row[1] ?? 0);

require_once 'includes/header.php';
?>
<div class="app-wrapper">
<?php require_once 'includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <div>
      <h1>Recenzii</h1>
      <div class="text-muted">Ce spun utilizatorii despre aplicație</div>
    </div>
    <?php if ($_SESSION['rol'] !== 'admin'): ?>
    <a href="/suport/submit.php?tip=feedback" class="btn btn-primary">+ Lasă o recenzie</a>
    <?php endif; ?>
  </div>

  <?php if ($totalReviews > 0): ?>
  <div class="reviews-summary">
    <div class="reviews-avg"><?= number_format($avgStele, 1, ',', '') ?></div>
    <div>
      <div class="reviews-stars-big">
        <?php for ($i = 1; $i <= 5; $i++): ?>
        <span style="color:<?= $i <= round($avgStele) ? '#fbbf24' : 'var(--muted2)' ?>">★</span>
        <?php endfor; ?>
      </div>
      <div class="text-muted" style="font-size:13px">
        Bazat pe <?= $totalReviews ?> recenz<?= $totalReviews === 1 ? 'ie' : 'ii' ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($reviews->num_rows === 0): ?>
  <div class="empty-state">
    <div class="empty-icon"><?= icon('star') ?></div>
    <p>Nu există recenzii încă. Fii primul care lasă o recenzie!</p>
    <?php if ($_SESSION['rol'] !== 'admin'): ?>
    <a href="/suport/submit.php?tip=feedback" class="btn btn-primary" style="margin-top:14px">Lasă o recenzie</a>
    <?php endif; ?>
  </div>
  <?php else: while ($r = $reviews->fetch_assoc()):
    $stele = (int)$r['stele'];
  ?>
  <div class="review-card">
    <div class="review-card-header">
      <div>
        <div style="font-weight:600;color:var(--accent2);margin-bottom:4px">
          <?= htmlspecialchars($r['username']) ?>
        </div>
        <div class="review-stars">
          <?php for ($i = 1; $i <= 5; $i++): ?>
          <span style="color:<?= $i <= $stele ? '#fbbf24' : 'var(--muted2)' ?>">★</span>
          <?php endfor; ?>
        </div>
      </div>
      <div class="review-card-meta"><?= date('d.m.Y', strtotime($r['created_at'])) ?></div>
    </div>
    <?php if ($r['subiect']): ?>
    <div class="review-card-subject"><?= htmlspecialchars($r['subiect']) ?></div>
    <?php endif; ?>
    <div class="review-card-body"><?= nl2br(htmlspecialchars($r['mesaj'])) ?></div>
  </div>
  <?php endwhile; endif; ?>

</div>
</div>
<?php require_once 'includes/footer.php'; ?>
