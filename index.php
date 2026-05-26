<?php
session_start();
require_once 'includes/auth.php';
require_once 'config/db.php';
require_once 'includes/alerts.php';

$pageTitle = 'Dashboard – Sibiza Park';

if ($_SESSION['rol'] === 'client') {
    header("Location: /portal/index.php");
    exit;
}

$totalMasini   = $conn->query("SELECT COUNT(DISTINCT nr_inmatriculare) FROM masini")->fetch_row()[0];
$totalClienti  = $conn->query("SELECT COUNT(*) FROM clienti")->fetch_row()[0];
$totalServicii = $conn->query("SELECT COUNT(*) FROM servicii")->fetch_row()[0];
$costTotal     = $conn->query("SELECT SUM(cost_total) FROM servicii")->fetch_row()[0] ?? 0;

$ultimeleServicii = $conn->query("
    SELECT s.*, m.nr_inmatriculare, m.marca, m.model
    FROM servicii s
    JOIN masini m ON m.id = s.masina_id
    ORDER BY s.data DESC LIMIT 5
");

$alerts = getAlerts($conn);

require_once 'includes/header.php';
?>
<div class="app-wrapper">
<?php require_once 'includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Dashboard</h1>
    <div class="flex">
      <span class="text-muted">Bun venit, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
      <?php if (!empty($alerts)): ?>
      <div class="notif-wrap" id="notifWrap">
        <button class="notif-btn" id="notifBtn" onclick="toggleNotif()" aria-label="Notificări">
          <?= icon('bell') ?>
          <span class="notif-badge"><?= count($alerts) ?></span>
        </button>
        <div class="notif-dropdown" id="notifDropdown">
          <div class="notif-header">
            <span>Notificări</span>
            <span class="notif-count"><?= count($alerts) ?></span>
          </div>
          <div class="notif-list">
            <?php foreach ($alerts as $a): ?>
            <div class="notif-item notif-<?= $a['expired'] ? 'danger' : 'warning' ?>">
              <span class="notif-icon"><?= icon($a['iconKey'] ?? 'alert-tri') ?></span>
              <div class="notif-body">
                <div class="notif-msg"><?= htmlspecialchars($a['message']) ?></div>
                <div class="notif-date"><?= $a['date'] ?></div>
              </div>
              <span class="notif-status"><?= $a['expired'] ? 'Expirat' : 'Curând' ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Statistici -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-number"><?= $totalMasini ?></div>
      <div class="stat-label">Mașini</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= $totalClienti ?></div>
      <div class="stat-label">Clienți</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= $totalServicii ?></div>
      <div class="stat-label">Intrări Service</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= number_format($costTotal, 0, ',', '.') ?> RON</div>
      <div class="stat-label">Cost Total Service</div>
    </div>
  </div>

  <!-- Ultimele servicii -->
  <div class="card">
    <div class="card-header">
      <h2>Ultimele intrări service</h2>
      <a href="/servicii/index.php" class="btn btn-sm btn-secondary">Vezi toate</a>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Mașină</th><th>Data</th><th>Tip</th><th>Km</th><th>Cost</th><th></th></tr>
        </thead>
        <tbody>
          <?php if ($ultimeleServicii->num_rows === 0): ?>
          <tr><td colspan="6" class="text-center text-muted">Nicio intrare service înregistrată.</td></tr>
          <?php else: ?>
          <?php while ($s = $ultimeleServicii->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($s['nr_inmatriculare']) ?> <span class="text-muted"><?= htmlspecialchars($s['marca'].' '.$s['model']) ?></span></td>
            <td><?= fmt_date($s['data']) ?></td>
            <td><span class="badge badge-<?= $s['tip'] ?>"><?= ucfirst($s['tip']) ?></span></td>
            <td><?= number_format($s['km_la_intrare']) ?> km</td>
            <td><?= number_format($s['cost_total'], 2, ',', '.') ?> RON</td>
            <td><a href="/servicii/view.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Vezi</a></td>
          </tr>
          <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- .main-content -->
</div><!-- .app-wrapper -->

<script>
function toggleNotif() {
  const wrap = document.getElementById('notifWrap');
  wrap.classList.toggle('open');
}
document.addEventListener('click', function(e) {
  const wrap = document.getElementById('notifWrap');
  if (wrap && !wrap.contains(e.target)) wrap.classList.remove('open');
});
</script>

<?php require_once 'includes/footer.php'; ?>
