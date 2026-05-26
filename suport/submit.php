<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Cerere / Feedback';
$success = false;
$error   = '';

$tip_param = $_GET['tip'] ?? 'ajutor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tip    = $_POST['tip'] ?? 'ajutor';
    $subiect= trim($_POST['subiect'] ?? '');
    $mesaj  = trim($_POST['mesaj'] ?? '');
    $uid    = (int)$_SESSION['user_id'];
    $stele  = ($tip === 'feedback') ? max(1, min(5, (int)($_POST['stele'] ?? 0))) : null;

    if (!$subiect || !$mesaj) {
        $error = 'Subiectul și mesajul sunt obligatorii.';
    } elseif ($tip === 'feedback' && !$stele) {
        $error = 'Te rugăm să dai o notă cu stele pentru feedback.';
    } elseif (!in_array($tip, ['cerere_masina','feedback','ajutor'])) {
        $error = 'Tip invalid.';
    } else {
        $stmt = $conn->prepare("INSERT INTO tichete (user_id, tip, subiect, mesaj) VALUES (?,?,?,?)");
        $stmt->bind_param("isss", $uid, $tip, $subiect, $mesaj);
        $stmt->execute();
        $newId = $conn->insert_id;
        $stmt->close();
        if ($tip === 'feedback' && $stele && $upd = $conn->prepare("UPDATE tichete SET stele=? WHERE id=?")) {
            $upd->bind_param("ii", $stele, $newId);
            $upd->execute();
            $upd->close();
        }
        $success = true;
    }
}

require_once '../includes/header.php';
?>
<div class="app-wrapper">
<?php require_once '../includes/nav.php'; ?>
<div class="main-content">

  <div class="page-header">
    <h1>Cerere / Feedback</h1>
    <a href="/index.php" class="btn btn-secondary">← Înapoi</a>
  </div>

  <?php if ($success): ?>
  <div class="alert alert-success" style="margin-bottom:24px">
    ✓ Cererea ta a fost trimisă! Echipa de suport îți va răspunde în curând.
    <a href="/index.php" class="btn btn-sm btn-secondary" style="margin-left:12px">Înapoi la dashboard</a>
  </div>
  <?php else: ?>

  <?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Tip selector -->
  <div class="ticket-type-grid">
    <label class="ticket-type-card <?= ($tip_param==='cerere_masina')?'active':'' ?>">
      <input type="radio" name="_tip" value="cerere_masina" <?= ($tip_param==='cerere_masina')?'checked':'' ?> onchange="setTip('cerere_masina')" style="display:none"/>
      <div class="ticket-type-icon"><?= icon('car', 'icon icon-lg') ?></div>
      <div class="ticket-type-label">Cerere adăugare mașină</div>
      <div class="ticket-type-desc">Solicită adăugarea unei mașini noi în parcul auto</div>
    </label>
    <label class="ticket-type-card <?= ($tip_param==='feedback')?'active':'' ?>">
      <input type="radio" name="_tip" value="feedback" <?= ($tip_param==='feedback')?'checked':'' ?> onchange="setTip('feedback')" style="display:none"/>
      <div class="ticket-type-icon"><?= icon('star', 'icon icon-lg') ?></div>
      <div class="ticket-type-label">Feedback / Review</div>
      <div class="ticket-type-desc">Trimite o sugestie sau o recenzie despre aplicație</div>
    </label>
    <label class="ticket-type-card <?= ($tip_param==='ajutor')?'active':'' ?>">
      <input type="radio" name="_tip" value="ajutor" <?= ($tip_param==='ajutor')?'checked':'' ?> onchange="setTip('ajutor')" style="display:none"/>
      <div class="ticket-type-icon"><?= icon('sos', 'icon icon-lg') ?></div>
      <div class="ticket-type-label">Ajutor</div>
      <div class="ticket-type-desc">Ai o problemă sau o întrebare? Scrie-ne!</div>
    </label>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" id="submitForm">
        <input type="hidden" name="tip" id="tipInput" value="<?= htmlspecialchars($tip_param) ?>"/>
        <input type="hidden" name="stele" id="steleInput" value="0"/>

        <!-- Star rating (only for feedback) -->
        <div class="form-group" id="starWrap" style="display:<?= $tip_param==='feedback'?'block':'none' ?>">
          <label>Notă *</label>
          <div class="star-rating" id="starRating">
            <span class="star" data-val="1">★</span>
            <span class="star" data-val="2">★</span>
            <span class="star" data-val="3">★</span>
            <span class="star" data-val="4">★</span>
            <span class="star" data-val="5">★</span>
          </div>
          <div class="text-muted" id="starLabel" style="font-size:12px;margin-top:2px">Selectează numărul de stele</div>
        </div>

        <div class="form-group">
          <label>Subiect *</label>
          <input type="text" name="subiect" class="form-control" placeholder="ex: Adaugare Logan B-123-ABC" value="<?= htmlspecialchars($_POST['subiect'] ?? '') ?>" required/>
        </div>
        <div class="form-group">
          <label>Mesaj *</label>
          <textarea name="mesaj" class="form-control" rows="5" placeholder="Descrie cererea sau problema ta..." required style="resize:vertical"><?= htmlspecialchars($_POST['mesaj'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
          <a href="/index.php" class="btn btn-secondary">Anulează</a>
          <button type="submit" class="btn btn-primary">Trimite cererea</button>
        </div>
      </form>
    </div>
  </div>

  <?php endif; ?>

</div>
</div>

<script>
const starLabels = ['','Foarte slab','Slab','Ok','Bun','Excelent!'];
let currentStele = 0;

function setTip(val) {
  document.getElementById('tipInput').value = val;
  document.querySelectorAll('.ticket-type-card').forEach(c => c.classList.remove('active'));
  event.currentTarget.classList.add('active');
  document.getElementById('starWrap').style.display = (val === 'feedback') ? 'block' : 'none';
  if (val !== 'feedback') {
    currentStele = 0;
    document.getElementById('steleInput').value = 0;
    updateStars(0);
  }
}

function updateStars(val) {
  document.querySelectorAll('.star').forEach((s, i) => {
    s.classList.toggle('on', i < val);
  });
  document.getElementById('starLabel').textContent = val ? starLabels[val] : 'Selectează numărul de stele';
}

document.querySelectorAll('.star').forEach(s => {
  s.addEventListener('click', function() {
    currentStele = +this.dataset.val;
    document.getElementById('steleInput').value = currentStele;
    updateStars(currentStele);
  });
  s.addEventListener('mouseover', function() {
    document.querySelectorAll('.star').forEach((st, i) => {
      st.classList.toggle('on', i < +this.dataset.val);
    });
  });
  s.addEventListener('mouseout', function() {
    updateStars(currentStele);
  });
});

// Init star state if returning after error
(function(){
  const saved = +document.getElementById('steleInput').value;
  if (saved) { currentStele = saved; updateStars(saved); }
})();
</script>
<?php require_once '../includes/footer.php'; ?>
