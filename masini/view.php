<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT m.*, c.nume as proprietar_nume FROM masini m LEFT JOIN clienti c ON c.id=m.proprietar_id WHERE m.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$m = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$m) { header("Location: index.php"); exit; }

$pageTitle = $m['nr_inmatriculare'];

$servicii  = $conn->query("SELECT * FROM servicii WHERE masina_id=$id ORDER BY data DESC");
$asigurari = $conn->query("SELECT * FROM asigurari WHERE masina_id=$id ORDER BY data_expirare DESC");
$viniete   = $conn->query("SELECT * FROM viniete WHERE masina_id=$id ORDER BY data_expirare DESC");
$itpuri    = $conn->query("SELECT * FROM itp WHERE masina_id=$id ORDER BY data_expirare DESC");

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1><?= htmlspecialchars($m['nr_inmatriculare']) ?> <span class="text-muted"><?= htmlspecialchars($m['marca'].' '.$m['model']) ?></span></h1>
    <div class="flex">
      <a href="edit.php?id=<?= $id ?>" class="btn btn-secondary">Editează</a>
      <a href="delete.php?id=<?= $id ?>" class="btn btn-danger" onclick="return confirm('Ștergi mașina?')">Șterge</a>
    </div>
  </div>

  <!-- Date tehnice -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-header"><h2>Date tehnice</h2></div>
    <div class="card-body">
      <?php if ($m['poza']): ?>
      <div style="margin-bottom:20px">
        <img src="/uploads/masini/<?= htmlspecialchars($m['poza']) ?>" alt="<?= htmlspecialchars($m['nr_inmatriculare']) ?>" class="car-photo"/>
      </div>
      <?php endif; ?>
      <div class="form-row form-row-3">
        <div><div class="text-muted">Client</div><strong><?= htmlspecialchars($m['proprietar_nume'] ?? '—') ?></strong></div>
        <div><div class="text-muted">An fabricație</div><strong><?= $m['an_fabricatie'] ?: '—' ?></strong></div>
        <div><div class="text-muted">Tip motor</div><strong><?= htmlspecialchars($m['tip_motor'] ?? '—') ?></strong></div>
        <div><div class="text-muted">Capacitate</div><strong><?= $m['capacitate_cmc'] ? $m['capacitate_cmc'].' cmc' : '—' ?></strong></div>
        <div><div class="text-muted">Nr. locuri</div><strong><?= $m['nr_locuri'] ?: '—' ?></strong></div>
        <div><div class="text-muted">Km actuali</div><strong><?= number_format($m['km_actuali']) ?> km</strong></div>
        <div><div class="text-muted">Culoare</div><strong><?= htmlspecialchars($m['culoare'] ?? '—') ?></strong></div>
        <div><div class="text-muted">Serie șasiu</div><strong><?= htmlspecialchars($m['serie_sasiu'] ?? '—') ?></strong></div>
        <div><div class="text-muted">Tonaj</div><strong><?= $m['tonaj'] ? $m['tonaj'].' t' : '—' ?></strong></div>
      </div>
    </div>
  </div>

  <!-- Servicii -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-header">
      <h2>Istoric service</h2>
      <a href="../servicii/add.php?masina_id=<?= $id ?>" class="btn btn-sm btn-primary">+ Adaugă</a>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Data</th><th>Tip</th><th>Km</th><th>Cost</th><th>Service</th><th></th></tr></thead>
        <tbody>
          <?php if ($servicii->num_rows === 0): ?>
          <tr><td colspan="6" class="text-center text-muted" style="padding:20px">Nicio intrare service.</td></tr>
          <?php else: while ($s = $servicii->fetch_assoc()): ?>
          <tr>
            <td><?= fmt_date($s['data']) ?></td>
            <td><span class="badge badge-<?= $s['tip'] ?>"><?= ucfirst($s['tip']) ?></span></td>
            <td><?= number_format($s['km_la_intrare']) ?> km</td>
            <td><?= number_format($s['cost_total'], 2, ',', '.') ?> RON</td>
            <td><?= htmlspecialchars($s['service_extern'] ?? '—') ?></td>
            <td><a href="../servicii/view.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Vezi</a></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Asigurari -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-header">
      <h2>Asigurări</h2>
      <a href="../asigurari/add.php?masina_id=<?= $id ?>" class="btn btn-sm btn-primary">+ Adaugă</a>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Tip</th><th>Companie</th><th>Nr. poliță</th><th>Start</th><th>Expiră</th><th>Preț</th><th></th></tr></thead>
        <tbody>
          <?php if ($asigurari->num_rows === 0): ?>
          <tr><td colspan="7" class="text-center text-muted" style="padding:20px">Nicio asigurare.</td></tr>
          <?php else: while ($a = $asigurari->fetch_assoc()): ?>
          <?php $exp = $a['data_expirare'] < date('Y-m-d'); ?>
          <tr>
            <td><span class="badge badge-<?= $a['tip'] ?>"><?= $a['tip'] ?></span></td>
            <td><?= htmlspecialchars($a['companie'] ?? '—') ?></td>
            <td><?= htmlspecialchars($a['nr_polita'] ?? '—') ?></td>
            <td><?= fmt_date($a['data_start']) ?></td>
            <td <?= $exp ? 'style="color:#fca5a5"' : '' ?>><?= fmt_date($a['data_expirare']) ?></td>
            <td><?= number_format($a['pret'], 2, ',', '.') ?> RON</td>
            <td><a href="../asigurari/edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-secondary">Editează</a></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ITP -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-header">
      <h2>ITP</h2>
      <a href="../itp/add.php?masina_id=<?= $id ?>" class="btn btn-sm btn-primary">+ Adaugă</a>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Data efectuare</th><th>Data expirare</th><th>Stație</th><th>Cost</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php if ($itpuri->num_rows === 0): ?>
          <tr><td colspan="6" class="text-center text-muted" style="padding:20px">Niciun ITP înregistrat.</td></tr>
          <?php else: while ($irow = $itpuri->fetch_assoc()):
            $exp = $irow['data_expirare'];
            $today = date('Y-m-d');
            if ($exp < $today) { $status = 'Expirat'; $cls = 'badge-accident'; $st = 'style="color:#fca5a5"'; }
            elseif ($exp <= date('Y-m-d', strtotime('+30 days'))) { $status = 'Expiră curând'; $cls = 'badge-reparatie'; $st = 'style="color:#FFDCCD"'; }
            else { $status = 'Valabil'; $cls = 'badge-activ'; $st = ''; }
          ?>
          <tr>
            <td><?= fmt_date($irow['data_efectuare']) ?></td>
            <td <?= $st ?>><?= fmt_date($irow['data_expirare']) ?></td>
            <td><?= htmlspecialchars($irow['statie'] ?? '—') ?></td>
            <td><?= number_format($irow['cost'], 2, ',', '.') ?> RON</td>
            <td><span class="badge <?= $cls ?>"><?= $status ?></span></td>
            <td><a href="../itp/edit.php?id=<?= $irow['id'] ?>" class="btn btn-sm btn-secondary">Editează</a></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Viniete -->
  <div class="card">
    <div class="card-header">
      <h2>Viniete</h2>
      <a href="../viniete/add.php?masina_id=<?= $id ?>" class="btn btn-sm btn-primary">+ Adaugă</a>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Țară</th><th>Tip</th><th>Start</th><th>Expiră</th><th>Cost</th><th></th></tr></thead>
        <tbody>
          <?php if ($viniete->num_rows === 0): ?>
          <tr><td colspan="6" class="text-center text-muted" style="padding:20px">Nicio vinietă.</td></tr>
          <?php else: while ($v = $viniete->fetch_assoc()): ?>
          <?php $exp = $v['data_expirare'] < date('Y-m-d'); ?>
          <tr>
            <td><?= htmlspecialchars($v['tara']) ?></td>
            <td><?= htmlspecialchars($v['tip']) ?></td>
            <td><?= fmt_date($v['data_start']) ?></td>
            <td <?= $exp ? 'style="color:#fca5a5"' : '' ?>><?= fmt_date($v['data_expirare']) ?></td>
            <td><?= number_format($v['cost'], 2, ',', '.') ?> RON</td>
            <td><a href="../viniete/edit.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-secondary">Editează</a></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
<?php require_once '../includes/footer.php'; ?>
