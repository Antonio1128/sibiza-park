<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
if ($_SESSION['rol'] !== 'admin') { header("Location: /index.php"); exit; }
$pageTitle = 'Adaugă utilizator';
$error = '';

$clienti = $conn->query("
    SELECT id, nume FROM clienti
    WHERE id NOT IN (SELECT client_id FROM utilizatori WHERE client_id IS NOT NULL)
    ORDER BY nume
");
$soferi = $conn->query("
    SELECT MIN(id) AS id, nume FROM soferi
    WHERE id NOT IN (SELECT sofer_id FROM utilizatori WHERE sofer_id IS NOT NULL)
    GROUP BY nume
    ORDER BY nume
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $email     = trim($_POST['email'] ?? '');
    $rol       = $_POST['rol'] ?? 'admin';
    $client_id = ($rol === 'client') ? ((int)$_POST['client_id'] ?: null) : null;
    $sofer_id  = null;

    if (!$username || !$password) {
        $error = 'Username și parola sunt obligatorii.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO utilizatori (username, password, email, rol, client_id, sofer_id) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssii", $username, $hash, $email, $rol, $client_id, $sofer_id);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php");
            exit;
        } else {
            $error = 'Username deja există.';
        }
        $stmt->close();
    }
}

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Adaugă utilizator</h1>
    <a href="index.php" class="btn btn-secondary">← Înapoi</a>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST" id="frm">
        <div class="form-row">
          <div class="form-group">
            <label>Username *</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required/>
          </div>
          <div class="form-group">
            <label>Parolă *</label>
            <input type="password" name="password" class="form-control" required/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Rol</label>
            <select name="rol" class="form-control" id="rol-sel">
              <?php foreach (['admin','analyst','client'] as $r): ?>
              <option value="<?= $r ?>" <?= (($_POST['rol'] ?? 'admin') === $r) ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group" id="client-group" style="display:none">
          <label>Client asociat <span class="text-muted">(obligatoriu pentru rol Client)</span></label>
          <select name="client_id" class="form-control">
            <option value="">— Selectează clientul —</option>
            <?php $clienti->data_seek(0); while ($c = $clienti->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= (($_POST['client_id'] ?? 0) == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nume']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-actions">
          <a href="index.php" class="btn btn-secondary">Anulează</a>
          <button type="submit" class="btn btn-primary">Salvează</button>
        </div>
      </form>
    </div>
  </div>

</div>
</div>

<script>
const rolSel      = document.getElementById('rol-sel');
const clientGroup = document.getElementById('client-group');
function toggleGroups() {
  clientGroup.style.display = rolSel.value === 'client' ? 'block' : 'none';
}
rolSel.addEventListener('change', toggleGroups);
toggleGroups();
</script>
<?php require_once '../includes/footer.php'; ?>
