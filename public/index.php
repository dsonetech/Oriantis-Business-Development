<?php
$config = require __DIR__ . '/../config/app.php';
$langCode = $_GET['lang'] ?? $config['default_language'];
if (!in_array($langCode, $config['supported_languages'], true)) {
    $langCode = $config['default_language'];
}
$lang = require __DIR__ . '/../lang/' . $langCode . '.php';
?>
<!doctype html>
<html lang="<?= htmlspecialchars($langCode) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($config['app_name']) ?></title>
<style>
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f6f8fb;color:#1f2937}
.app{display:flex;min-height:100vh}
.sidebar{width:250px;background:#111827;color:#fff;padding:22px}
.brand{font-size:18px;font-weight:700;margin-bottom:26px}
.nav a{display:block;color:#d1d5db;text-decoration:none;padding:11px 12px;border-radius:8px;margin-bottom:5px}
.nav a:hover,.nav a.active{background:#1f2937;color:#fff}
.main{flex:1;padding:28px}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;box-shadow:0 4px 18px rgba(0,0,0,.04)}
.lang a{margin-left:8px;color:#374151;text-decoration:none}
</style>
</head>
<body>
<div class="app">
<aside class="sidebar">
  <div class="brand">Oriantis Business Development</div>
  <nav class="nav">
    <a class="active" href="#"><?= htmlspecialchars($lang['dashboard']) ?></a>
    <a href="#"><?= htmlspecialchars($lang['hotels']) ?></a>
    <a href="#"><?= htmlspecialchars($lang['services']) ?></a>
    <a href="#"><?= htmlspecialchars($lang['agencies']) ?></a>
    <a href="#"><?= htmlspecialchars($lang['quotes']) ?></a>
    <a href="#"><?= htmlspecialchars($lang['new_quote']) ?></a>
    <a href="#"><?= htmlspecialchars($lang['settings']) ?></a>
  </nav>
</aside>
<main class="main">
  <div class="topbar">
    <div>
      <h1><?= htmlspecialchars($lang['dashboard']) ?></h1>
      <p>Oriantis Quote Management</p>
    </div>
    <div class="lang">
      <a href="?lang=en">EN</a>
      <a href="?lang=fr">FR</a>
    </div>
  </div>
  <div class="card">
    <h2>Project initialized</h2>
    <p>Base PHP structure, bilingual language files and database schema are ready.</p>
  </div>
</main>
</div>
</body>
</html>
