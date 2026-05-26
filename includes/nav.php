<?php
if (($_SESSION['rol'] ?? '') === 'client') return;

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
function navActive($dirs) {
    global $currentDir, $currentPage;
    $dirs = (array)$dirs;
    foreach ($dirs as $d) {
        if ($currentDir === $d || ($currentPage === $d)) return 'active';
    }
    return '';
}
$initial = strtoupper(substr($_SESSION['username'] ?? '?', 0, 1));
?>
<nav class="sidebar">
  <div class="sidebar-logo">
    <span class="sidebar-logo-mark">
      <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2"/>
        <circle cx="6.5" cy="16.5" r="2.5"/>
        <circle cx="16.5" cy="16.5" r="2.5"/>
      </svg>
    </span>
    Sibiza Park
  </div>

  <?php if (($_SESSION['rol'] ?? '') !== 'client'): ?>
  <div class="sidebar-section">General</div>
  <a href="/index.php" class="<?= $currentPage === 'index.php' && $currentDir === 'proiectpers' ? 'active' : '' ?>">
    <?= icon('dashboard') ?> Dashboard
  </a>
  <?php endif; ?>

  <?php if (($_SESSION['rol'] ?? '') !== 'client'): ?>
  <div class="sidebar-section">Gestiune</div>
  <a href="/masini/index.php" class="<?= navActive('masini') ?>">
    <?= icon('car') ?> Mașini
  </a>
  <a href="/clienti/index.php" class="<?= navActive('clienti') ?>">
    <?= icon('user') ?> Clienți
  </a>
  <a href="/curse/index.php" class="<?= navActive('curse') ?>">
    <?= icon('route') ?> Curse
  </a>

  <div class="sidebar-section">Service & Documente</div>
  <a href="/servicii/index.php" class="<?= navActive('servicii') ?>">
    <?= icon('wrench') ?> Service
  </a>
  <a href="/asigurari/index.php" class="<?= navActive('asigurari') ?>">
    <?= icon('shield') ?> Asigurări
  </a>
  <a href="/viniete/index.php" class="<?= navActive('viniete') ?>">
    <?= icon('tag') ?> Viniete
  </a>
  <a href="/anvelope/index.php" class="<?= navActive('anvelope') ?>">
    <?= icon('tire') ?> Anvelope
  </a>
  <a href="/itp/index.php" class="<?= navActive('itp') ?>">
    <?= icon('search') ?> ITP
  </a>
  <?php endif; ?>

  <?php if (($_SESSION['rol'] ?? '') === 'analyst'): ?>
  <div class="sidebar-section">Analiză</div>
  <a href="/rapoarte/index.php" class="<?= navActive('rapoarte') ?>">
    <?= icon('trending') ?> Rapoarte
  </a>
  <?php endif; ?>

  <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
  <div class="sidebar-section">Admin</div>
  <a href="/utilizatori/index.php" class="<?= navActive('utilizatori') ?>">
    <?= icon('users') ?> Utilizatori
  </a>
  <?php
    $nrTicheteNoi = $conn->query("SELECT COUNT(*) FROM tichete WHERE status='nou'")->fetch_row()[0] ?? 0;
  ?>
  <a href="/reviews.php" class="<?= $currentPage === 'reviews.php' ? 'active' : '' ?>">
    <?= icon('star') ?> Recenzii
  </a>
  <a href="/suport/index.php" class="<?= navActive('suport') ?>" style="justify-content:space-between">
    <span style="display:flex;align-items:center;gap:12px"><?= icon('ticket') ?> Suport</span>
    <?php if ($nrTicheteNoi > 0): ?>
    <span style="background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:99px;border:2px solid var(--bg2)"><?= $nrTicheteNoi ?></span>
    <?php endif; ?>
  </a>
  <?php endif; ?>

  <div class="sidebar-footer">
    <span class="sidebar-footer-user">
      <span class="sidebar-footer-avatar"><?= htmlspecialchars($initial) ?></span>
      <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
    </span>
    <a href="/logout.php" title="Ieșire"><?= icon('logout', 'icon icon-sm') ?></a>
  </div>
</nav>
