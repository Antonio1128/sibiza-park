<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?= htmlspecialchars($pageTitle ?? 'Sibiza Park') ?></title>
<link rel="stylesheet" href="/assets/css/style.css?v=7"/>
</head>
<body<?php if (($_SESSION['rol'] ?? '') === 'client'): ?> class="no-sidebar"<?php endif; ?>>

