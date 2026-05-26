<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: /index.php");
    exit;
}

require_once 'config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT id, username, password, rol, sofer_id, client_id FROM utilizatori WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['rol']      = $user['rol'];
            $_SESSION['sofer_id'] = $user['sofer_id'] ?? null;
            $_SESSION['client_id']= $user['client_id'] ?? null;
            if ($user['rol'] === 'client') {
                header("Location: /portal/index.php");
            } else {
                header("Location: /index.php");
            }
            exit;
        } else {
            $error = "Username sau parolă incorectă.";
        }
        $stmt->close();
    } else {
        $error = "Completează toate câmpurile.";
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Login – Sibiza Park</title>
<link rel="stylesheet" href="/assets/css/style.css?v=7"/>
</head>
<body class="login-body">

<div class="login-hero">
  <div class="login-hero-brand">
    <span class="login-hero-brand-mark">
      <svg class="icon" viewBox="0 0 24 24" style="color:#1a0f00;stroke-width:2.2">
        <path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2"/>
        <circle cx="6.5" cy="16.5" r="2.5"/>
        <circle cx="16.5" cy="16.5" r="2.5"/>
      </svg>
    </span>
    Sibiza Park
  </div>

  <div class="login-hero-text">
    <div class="login-hero-eyebrow">Fleet Management Platform</div>
    <h1 class="login-hero-title">Noi ne ocupăm<br/>de tot. <span>Tu conduci.</span></h1>
    <p class="login-hero-sub">Uită de stresul cu documente, termene și service-uri. Platforma noastră îți gestionează automat întreaga flotă — de la RCA și ITP, la revizie și anvelope.</p>
    <div class="login-hero-bullets">
      <div class="login-hero-bullet">Alertele automate îți spun exact când expiră ceva. Nu mai pierzi nicio dată limită, nu mai primești amenzi, nu mai cauți prin dosare.</div>
      <div class="login-hero-bullet">Kilometraj, costuri, intervenții, asigurări, viniete — totul centralizat și la zi. Zero hârtii, zero griji.</div>
    </div>
  </div>

  <div class="login-hero-stats">
    <div>
      <div class="login-hero-stat-num">24/7</div>
      <div class="login-hero-stat-lbl">Monitorizare flotă</div>
    </div>
    <div>
      <div class="login-hero-stat-num">100%</div>
      <div class="login-hero-stat-lbl">Documente digitale</div>
    </div>
    <div>
      <div class="login-hero-stat-num">0€</div>
      <div class="login-hero-stat-lbl">Amenzi pentru termen depășit</div>
    </div>
  </div>
</div>

<div class="login-panel">
  <div class="login-wrap">
    <div class="login-card">
      <h1 class="login-title">Bine ai revenit</h1>
      <p class="login-sub">Autentifică-te pentru a continua în cont</p>

      <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom:20px">
          <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus/>
        </div>
        <div class="form-group">
          <label>Parolă</label>
          <input type="password" name="password" class="form-control" required/>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Intră în cont</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
