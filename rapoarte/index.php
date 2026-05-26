<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
if (!in_array($_SESSION['rol'] ?? '', ['admin','analyst'])) { header("Location: /index.php"); exit; }

$pageTitle = 'Rapoarte – Sibiza Park';

// Costuri service pe ultimele 6 luni
$costuriLuni = $conn->query("
    SELECT DATE_FORMAT(data,'%Y-%m') AS luna,
           DATE_FORMAT(data,'%b %Y') AS luna_label,
           SUM(cost_total) AS total,
           COUNT(*) AS intrari
    FROM servicii
    WHERE data >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY luna ORDER BY luna ASC
");
$luniData = []; $luniLabels = []; $luniCosturi = []; $luniIntrari = [];
while ($r = $costuriLuni->fetch_assoc()) {
    $luniLabels[] = $r['luna_label'];
    $luniCosturi[] = (float)$r['total'];
    $luniIntrari[] = (int)$r['intrari'];
}

// Status documente
$today = date('Y-m-d');
$soon  = date('Y-m-d', strtotime('+30 days'));

$itpStats = $conn->query("
    SELECT
      SUM(data_expirare < '$today') AS expirat,
      SUM(data_expirare BETWEEN '$today' AND '$soon') AS curand,
      SUM(data_expirare > '$soon') AS valabil
    FROM (SELECT masina_id, MAX(data_expirare) AS data_expirare FROM itp GROUP BY masina_id) t
")->fetch_assoc();

$rcaStats = $conn->query("
    SELECT
      SUM(data_expirare < '$today') AS expirat,
      SUM(data_expirare BETWEEN '$today' AND '$soon') AS curand,
      SUM(data_expirare > '$soon') AS valabil
    FROM (SELECT masina_id, MAX(data_expirare) AS data_expirare FROM asigurari WHERE tip='RCA' GROUP BY masina_id) t
")->fetch_assoc();

$vinStats = $conn->query("
    SELECT
      SUM(data_expirare < '$today') AS expirat,
      SUM(data_expirare BETWEEN '$today' AND '$soon') AS curand,
      SUM(data_expirare > '$soon') AS valabil
    FROM (SELECT masina_id, MAX(data_expirare) AS data_expirare FROM viniete GROUP BY masina_id) t
")->fetch_assoc();

// Top 5 masini costuri
$topMasini = $conn->query("
    SELECT m.nr_inmatriculare, m.marca, m.model,
           COUNT(s.id) AS nr_intrari,
           SUM(s.cost_total) AS total_cost
    FROM masini m
    JOIN servicii s ON s.masina_id = m.id
    GROUP BY m.id ORDER BY total_cost DESC LIMIT 5
");

// Curse per luna (ultimele 6 luni)
$curseStats = $conn->query("
    SELECT DATE_FORMAT(data,'%b %Y') AS luna,
           COUNT(*) AS nr_curse,
           SUM(km_final - km_start) AS km_total
    FROM curse
    WHERE data >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(data,'%Y-%m') ORDER BY DATE_FORMAT(data,'%Y-%m') ASC
");
$curseLabels = []; $curseKm = [];
while ($r = $curseStats->fetch_assoc()) {
    $curseLabels[] = $r['luna'];
    $curseKm[]     = (int)$r['km_total'];
}

// Totaluri generale
$totalMasini    = $conn->query("SELECT COUNT(DISTINCT nr_inmatriculare) FROM masini")->fetch_row()[0];
$totalServicii  = $conn->query("SELECT COUNT(*) FROM servicii")->fetch_row()[0];
$costTotal      = $conn->query("SELECT SUM(cost_total) FROM servicii")->fetch_row()[0] ?? 0;
$totalCurse     = $conn->query("SELECT COUNT(*) FROM curse")->fetch_row()[0];
$totalKm        = $conn->query("SELECT SUM(km_final-km_start) FROM curse")->fetch_row()[0] ?? 0;

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <div>
      <h1>Rapoarte</h1>
      <div class="text-muted">Statistici și analiză parc auto</div>
    </div>
  </div>

  <!-- KPI cards -->
  <div class="stats-grid" style="margin-bottom:28px">
    <div class="stat-card">
      <div class="stat-number"><?= $totalMasini ?></div>
      <div class="stat-label">Mașini active</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= $totalServicii ?></div>
      <div class="stat-label">Intrări service</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= number_format($costTotal, 0, ',', '.') ?> RON</div>
      <div class="stat-label">Cost total service</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= $totalCurse ?></div>
      <div class="stat-label">Curse efectuate</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= number_format($totalKm) ?> km</div>
      <div class="stat-label">Km total parcurși</div>
    </div>
  </div>

  <!-- Charts row -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px">

    <!-- Costuri service -->
    <div class="card">
      <div class="card-header"><h2>Costuri service – ultimele 6 luni</h2></div>
      <div class="card-body">
        <canvas id="chartCosturi" height="200"></canvas>
      </div>
    </div>

    <!-- Km curse -->
    <div class="card">
      <div class="card-header"><h2>Km parcurși – ultimele 6 luni</h2></div>
      <div class="card-body">
        <canvas id="chartKm" height="200"></canvas>
      </div>
    </div>
  </div>

  <!-- Status documente -->
  <div class="card" style="margin-bottom:28px">
    <div class="card-header"><h2>Status documente</h2></div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px">
        <?php
        function docBar($label, $iconKey, $stats) {
            $exp   = (int)($stats['expirat'] ?? 0);
            $soon  = (int)($stats['curand']  ?? 0);
            $valid = (int)($stats['valabil'] ?? 0);
            $total = $exp + $soon + $valid ?: 1;
            $iconHtml = icon($iconKey);
            echo "<div>";
            echo "<div style='font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:8px;color:var(--white)'>$iconHtml $label</div>";
            echo "<div style='display:flex;flex-direction:column;gap:8px'>";
            $bars = [
                ['Expirat', $exp,   '#f87171'],
                ['Curând',  $soon,  '#fb923c'],
                ['Valabil', $valid, '#34d399'],
            ];
            foreach ($bars as [$lbl, $val, $color]) {
                $pct = round($val / $total * 100);
                echo "<div>";
                echo "<div style='display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px'><span style='color:#aaa'>$lbl</span><span style='color:#fff;font-weight:600'>$val</span></div>";
                echo "<div style='background:rgba(255,255,255,0.06);border-radius:99px;height:6px'>";
                echo "<div style='width:{$pct}%;background:{$color};border-radius:99px;height:6px;transition:width .4s'></div>";
                echo "</div></div>";
            }
            echo "</div></div>";
        }
        docBar('ITP',       'search', $itpStats);
        docBar('Asigurare RCA', 'shield', $rcaStats);
        docBar('Vinietă',   'tag', $vinStats);
        ?>
      </div>
    </div>
  </div>

  <!-- Top 5 masini -->
  <div class="card">
    <div class="card-header"><h2>Top 5 mașini după cost service</h2></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Mașină</th><th>Intrări service</th><th>Cost total</th></tr></thead>
        <tbody>
          <?php if ($topMasini->num_rows === 0): ?>
          <tr><td colspan="3" class="text-center text-muted">Nicio intrare service.</td></tr>
          <?php else: while ($m = $topMasini->fetch_assoc()): ?>
          <tr>
            <td><strong><?= htmlspecialchars($m['nr_inmatriculare']) ?></strong> <span class="text-muted"><?= htmlspecialchars($m['marca'].' '.$m['model']) ?></span></td>
            <td><?= $m['nr_intrari'] ?></td>
            <td><strong style="color:var(--accent2)"><?= number_format($m['total_cost'], 2, ',', '.') ?> RON</strong></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const gridColor = 'rgba(255,255,255,0.06)';
const textColor = 'rgba(200,200,230,0.6)';

Chart.defaults.color = textColor;
Chart.defaults.borderColor = gridColor;
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size   = 12;

new Chart(document.getElementById('chartCosturi'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($luniLabels) ?>,
    datasets: [{
      label: 'Cost service (RON)',
      data: <?= json_encode($luniCosturi) ?>,
      backgroundColor: 'rgba(124,109,240,0.6)',
      borderColor: '#7c6df0',
      borderWidth: 1,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { grid: { color: gridColor }, ticks: { callback: v => v.toLocaleString('ro') + ' RON' } },
      x: { grid: { display: false } }
    }
  }
});

new Chart(document.getElementById('chartKm'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($curseLabels) ?>,
    datasets: [{
      label: 'Km parcurși',
      data: <?= json_encode($curseKm) ?>,
      backgroundColor: 'rgba(34,211,238,0.5)',
      borderColor: '#22d3ee',
      borderWidth: 1,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { grid: { color: gridColor }, ticks: { callback: v => v.toLocaleString('ro') + ' km' } },
      x: { grid: { display: false } }
    }
  }
});
</script>
<?php require_once '../includes/footer.php'; ?>
