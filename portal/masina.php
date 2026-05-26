<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: /login.php"); exit; }
if ($_SESSION['rol'] !== 'client') { header("Location: /index.php"); exit; }

$client_id = $_SESSION['client_id'];
$id = (int)($_GET['id'] ?? 0);

// Verifica ca masina apartine clientului
$stmt = $conn->prepare("SELECT * FROM masini WHERE id=? AND proprietar_id=?");
$stmt->bind_param("ii", $id, $client_id);
$stmt->execute();
$m = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$m) { header("Location: index.php"); exit; }

$servicii  = $conn->query("SELECT * FROM servicii WHERE masina_id=$id ORDER BY data DESC");
$asigurari = $conn->query("SELECT * FROM asigurari WHERE masina_id=$id ORDER BY data_expirare DESC");
$viniete   = $conn->query("SELECT * FROM viniete WHERE masina_id=$id ORDER BY data_expirare DESC");
$itpuri    = $conn->query("SELECT * FROM itp WHERE masina_id=$id ORDER BY data_expirare DESC");
$today     = date('Y-m-d');

$pageTitle = $m['nr_inmatriculare'].' – Istoric';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?= $pageTitle ?></title>
<link rel="stylesheet" href="/assets/css/style.css?v=7"/>
</head>
<body>

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
    <a href="index.php" class="btn btn-sm btn-secondary">← Înapoi</a>
    <a href="/logout.php" class="btn btn-sm btn-secondary" style="margin-left:8px">Ieșire</a>
  </div>
</nav>

<div class="portal-wrap">

  <?php if (!empty($m['poza'])): ?>
  <img src="/uploads/masini/<?= htmlspecialchars($m['poza']) ?>" class="car-photo" style="margin-bottom:24px" alt="<?= htmlspecialchars($m['marca'].' '.$m['model']) ?>"/>
  <?php endif; ?>

  <div class="page-header">
    <h1><?= htmlspecialchars($m['nr_inmatriculare']) ?> <span class="text-muted"><?= htmlspecialchars($m['marca'].' '.$m['model']) ?></span></h1>
    <span class="text-muted"><?= number_format($m['km_actuali']) ?> km · <?= $m['an_fabricatie'] ?></span>
  </div>

  <!-- ITP -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-header"><h2>ITP</h2></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Data efectuare</th><th>Data expirare</th><th>Stație</th><th>Cost</th><th>Status</th></tr></thead>
        <tbody>
          <?php if ($itpuri->num_rows === 0): ?>
          <tr><td colspan="5" class="text-center text-muted" style="padding:16px">Niciun ITP înregistrat.</td></tr>
          <?php else: while ($i = $itpuri->fetch_assoc()):
            $exp = $i['data_expirare'];
            if ($exp < $today) { $lbl = 'Expirat'; $cls = 'badge-accident'; }
            elseif ($exp <= date('Y-m-d', strtotime('+30 days'))) { $lbl = 'Expiră curând'; $cls = 'badge-reparatie'; }
            else { $lbl = 'Valabil'; $cls = 'badge-activ'; }
          ?>
          <tr>
            <td><?= fmt_date($i['data_efectuare']) ?></td>
            <td><?= fmt_date($i['data_expirare']) ?></td>
            <td><?= htmlspecialchars($i['statie'] ?? '—') ?></td>
            <td><?= number_format($i['cost'], 2, ',', '.') ?> RON</td>
            <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Asigurari -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-header"><h2>Asigurări</h2></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Tip</th><th>Companie</th><th>Nr. poliță</th><th>Start</th><th>Expiră</th><th>Preț</th></tr></thead>
        <tbody>
          <?php if ($asigurari->num_rows === 0): ?>
          <tr><td colspan="6" class="text-center text-muted" style="padding:16px">Nicio asigurare.</td></tr>
          <?php else: while ($a = $asigurari->fetch_assoc()): ?>
          <tr>
            <td><span class="badge badge-<?= $a['tip'] ?>"><?= $a['tip'] ?></span></td>
            <td><?= htmlspecialchars($a['companie'] ?? '—') ?></td>
            <td><?= htmlspecialchars($a['nr_polita'] ?? '—') ?></td>
            <td><?= fmt_date($a['data_start']) ?></td>
            <td <?= $a['data_expirare'] < $today ? 'style="color:#fca5a5"' : '' ?>><?= fmt_date($a['data_expirare']) ?></td>
            <td><?= number_format($a['pret'], 2, ',', '.') ?> RON</td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Viniete -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-header"><h2>Viniete</h2></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Țară</th><th>Tip</th><th>Start</th><th>Expiră</th><th>Cost</th></tr></thead>
        <tbody>
          <?php if ($viniete->num_rows === 0): ?>
          <tr><td colspan="5" class="text-center text-muted" style="padding:16px">Nicio vinietă.</td></tr>
          <?php else: while ($v = $viniete->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($v['tara']) ?></td>
            <td><?= htmlspecialchars($v['tip']) ?></td>
            <td><?= fmt_date($v['data_start']) ?></td>
            <td <?= $v['data_expirare'] < $today ? 'style="color:#fca5a5"' : '' ?>><?= fmt_date($v['data_expirare']) ?></td>
            <td><?= number_format($v['cost'], 2, ',', '.') ?> RON</td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Istoric service -->
  <div class="card">
    <div class="card-header"><h2>Istoric service</h2></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Data</th><th>Tip</th><th>Km</th><th>Lucrări</th><th>Cost</th></tr></thead>
        <tbody>
          <?php if ($servicii->num_rows === 0): ?>
          <tr><td colspan="5" class="text-center text-muted" style="padding:16px">Nicio intrare service.</td></tr>
          <?php else: while ($s = $servicii->fetch_assoc()):
            $lucrari = $conn->query("SELECT GROUP_CONCAT(denumire SEPARATOR ', ') as lista FROM interventii_service WHERE service_id={$s['id']}")->fetch_row()[0];
          ?>
          <tr>
            <td><?= fmt_date($s['data']) ?></td>
            <td><span class="badge badge-<?= $s['tip'] ?>"><?= ucfirst($s['tip']) ?></span></td>
            <td><?= number_format($s['km_la_intrare']) ?> km</td>
            <td class="text-muted" style="font-size:12px"><?= htmlspecialchars($lucrari ?? '—') ?></td>
            <td><strong><?= number_format($s['cost_total'], 2, ',', '.') ?> RON</strong></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once '../includes/footer.php'; ?>
