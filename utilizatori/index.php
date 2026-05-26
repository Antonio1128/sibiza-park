<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
if ($_SESSION['rol'] !== 'admin') { header("Location: /index.php"); exit; }
$pageTitle = 'Utilizatori';

$result = $conn->query("SELECT u.*, c.nume as client_nume FROM utilizatori u LEFT JOIN clienti c ON c.id=u.client_id ORDER BY u.rol, u.username");

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Utilizatori</h1>
    <a href="add.php" class="btn btn-primary">+ Adaugă utilizator</a>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Username</th><th>Email</th><th>Rol</th><th>Client asociat</th><th></th></tr></thead>
        <tbody>
          <?php while ($u = $result->fetch_assoc()): ?>
          <tr>
            <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
            <td><?= htmlspecialchars($u['email'] ?? '—') ?></td>
            <td><span class="badge badge-<?= $u['rol'] ?>"><?= ucfirst($u['rol']) ?></span></td>
            <td><?= htmlspecialchars($u['client_nume'] ?? '—') ?></td>
            <td class="flex">
              <?php if ($u['id'] != $_SESSION['user_id']): ?>
              <a href="delete.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ștergi utilizatorul?')">Șterge</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
<?php require_once '../includes/footer.php'; ?>
