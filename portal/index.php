<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: /login.php"); exit; }
if ($_SESSION['rol'] !== 'client') { header("Location: /index.php"); exit; }

$client_id = (int)($_SESSION['client_id'] ?? 0);
if (!$client_id) { die("Contul nu este asociat unui client. Contactează administratorul."); }

// Datele clientului
$stmt = $conn->prepare("SELECT * FROM clienti WHERE id=?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Masinile clientului
$masini = $conn->query("SELECT * FROM masini WHERE proprietar_id=$client_id ORDER BY nr_inmatriculare");

$pageTitle = 'Portalul meu – Sibiza Park';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?= $pageTitle ?></title>
<link rel="stylesheet" href="/assets/css/style.css?v=7"/>
</head>
<body class="no-sidebar">

<nav class="portal-nav">
  <div class="portal-nav-logo">
    <span class="login-hero-brand-mark" style="width:32px;height:32px;border-radius:8px">
      <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="#1a0f00" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2"/>
        <circle cx="6.5" cy="16.5" r="2.5"/>
        <circle cx="16.5" cy="16.5" r="2.5"/>
      </svg>
    </span>
    Sibiza Park
  </div>
  <div class="portal-nav-user">
    <?= htmlspecialchars($client['nume']) ?>
    <a href="/logout.php" class="btn btn-sm btn-secondary" style="margin-left:12px">Ieșire</a>
  </div>
</nav>

<div class="portal-wrap">
  <div class="page-header">
    <h1>Bun venit, <?= htmlspecialchars($client['nume']) ?>!</h1>
    <span class="text-muted">Mașinile și documentele tale</span>
  </div>

  <?php if ($masini->num_rows === 0): ?>
  <div class="empty-state">
    <div class="empty-icon"><?= icon('car') ?></div>
    <p>Nu ai nicio mașină înregistrată încă.</p>
  </div>
  <?php else: while ($m = $masini->fetch_assoc()):
    $today = date('Y-m-d');
    $soon  = date('Y-m-d', strtotime('+30 days'));

    $itp_r  = $conn->query("SELECT * FROM itp WHERE masina_id={$m['id']} ORDER BY data_expirare DESC LIMIT 1");
    $itp    = $itp_r->fetch_assoc();

    $asi_r  = $conn->query("SELECT * FROM asigurari WHERE masina_id={$m['id']} ORDER BY data_expirare DESC LIMIT 1");
    $asi    = $asi_r->fetch_assoc();

    $vin_r  = $conn->query("SELECT * FROM viniete WHERE masina_id={$m['id']} ORDER BY data_expirare DESC LIMIT 1");
    $vin    = $vin_r->fetch_assoc();

    $srv_count = $conn->query("SELECT COUNT(*) FROM servicii WHERE masina_id={$m['id']}")->fetch_row()[0];
    $srv_cost  = $conn->query("SELECT SUM(cost_total) FROM servicii WHERE masina_id={$m['id']}")->fetch_row()[0] ?? 0;
  ?>

  <div class="portal-car-card">
    <?php if (!empty($m['poza'])): ?>
    <img src="/uploads/masini/<?= htmlspecialchars($m['poza']) ?>" class="portal-car-img" alt="<?= htmlspecialchars($m['marca'].' '.$m['model']) ?>"/>
    <?php else: ?>
    <div class="portal-car-img-placeholder"><?= icon('car', 'icon') ?></div>
    <?php endif; ?>
    <div class="portal-car-header">
      <div>
        <div class="portal-car-plate"><?= htmlspecialchars($m['nr_inmatriculare']) ?></div>
        <div class="portal-car-name"><?= htmlspecialchars($m['marca'].' '.$m['model']) ?> · <?= $m['an_fabricatie'] ?></div>
      </div>
      <div class="text-muted" style="font-size:13px"><?= number_format($m['km_actuali']) ?> km</div>
    </div>

    <!-- Documente -->
    <div class="portal-docs">

      <!-- ITP -->
      <div class="portal-doc <?= !$itp ? '' : ($itp['data_expirare'] < $today ? 'doc-expired' : ($itp['data_expirare'] <= $soon ? 'doc-soon' : 'doc-ok')) ?>">
        <div class="portal-doc-icon"><?= icon('search') ?></div>
        <div>
          <div class="portal-doc-name">ITP</div>
          <?php if ($itp): ?>
          <div class="portal-doc-date">Expiră: <?= fmt_date($itp['data_expirare']) ?></div>
          <?php else: ?>
          <div class="portal-doc-date text-muted">Neînregistrat</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Asigurare -->
      <div class="portal-doc <?= !$asi ? '' : ($asi['data_expirare'] < $today ? 'doc-expired' : ($asi['data_expirare'] <= $soon ? 'doc-soon' : 'doc-ok')) ?>">
        <div class="portal-doc-icon"><?= icon('shield') ?></div>
        <div>
          <div class="portal-doc-name">Asigurare <?= $asi ? $asi['tip'] : '' ?></div>
          <?php if ($asi): ?>
          <div class="portal-doc-date">Expiră: <?= fmt_date($asi['data_expirare']) ?></div>
          <?php else: ?>
          <div class="portal-doc-date text-muted">Neînregistrat</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Vinietă -->
      <div class="portal-doc <?= !$vin ? '' : ($vin['data_expirare'] < $today ? 'doc-expired' : ($vin['data_expirare'] <= $soon ? 'doc-soon' : 'doc-ok')) ?>">
        <div class="portal-doc-icon"><?= icon('tag') ?></div>
        <div>
          <div class="portal-doc-name">Vinietă</div>
          <?php if ($vin): ?>
          <div class="portal-doc-date">Expiră: <?= fmt_date($vin['data_expirare']) ?></div>
          <?php else: ?>
          <div class="portal-doc-date text-muted">Neînregistrat</div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- Service stats -->
    <div class="portal-service-row">
      <span class="text-muted">Intrări service: <strong><?= $srv_count ?></strong></span>
      <span class="text-muted">Cost total: <strong style="color:var(--blue)"><?= number_format($srv_cost, 2, ',', '.') ?> RON</strong></span>
      <a href="masina.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-secondary">Istoric complet →</a>
    </div>
  </div>

  <?php endwhile; endif; ?>

  <!-- Butoane suport client -->
  <?php
    $nrRaspunsuri = $conn->query("
        SELECT COUNT(*) FROM tichete_raspunsuri r
        JOIN tichete t ON t.id = r.tichet_id
        JOIN utilizatori u ON u.id = r.user_id
        WHERE t.user_id = $client_id AND u.rol = 'admin'
    ")->fetch_row()[0] ?? 0;
  ?>
  <div class="support-buttons" style="margin-top:28px;grid-template-columns:repeat(3,1fr)">
    <a href="/suport/submit.php?tip=cerere_masina" class="support-btn">
      <div class="support-btn-icon"><?= icon('car') ?></div>
      <div>
        <div class="support-btn-label">Cerere mașină nouă</div>
        <div class="support-btn-desc">Solicită adăugarea unei mașini</div>
      </div>
    </a>

    <a href="/suport/submit.php?tip=ajutor" class="support-btn">
      <div class="support-btn-icon"><?= icon('sos') ?></div>
      <div>
        <div class="support-btn-label">Ajutor</div>
        <div class="support-btn-desc">Ai o problemă? Scrie-ne!</div>
      </div>
    </a>
    <a href="/suport/mele.php" class="support-btn" style="position:relative">
      <div class="support-btn-icon"><?= icon('clipboard') ?></div>
      <div>
        <div class="support-btn-label">Cererile mele
          <?php if ($nrRaspunsuri > 0): ?>
          <span style="background:var(--red);color:#fff;font-size:9px;font-weight:700;padding:1px 6px;border-radius:99px;margin-left:6px;vertical-align:middle"><?= $nrRaspunsuri ?></span>
          <?php endif; ?>
        </div>
        <div class="support-btn-desc">Ai <?= $nrRaspunsuri > 0 ? "$nrRaspunsuri răspuns(uri) noi" : 'niciun răspuns nou' ?></div>
      </div>
    </a>
    <a href="/reviews.php" class="support-btn" style="grid-column:2">
      <div class="support-btn-icon"><?= icon('star') ?></div>
      <div>
        <div class="support-btn-label">Recenzii</div>
        <div class="support-btn-desc">Vezi ce spun alți utilizatori</div>
      </div>
    </a>
  </div>

</div>

<?php require_once '../includes/footer.php'; ?>
