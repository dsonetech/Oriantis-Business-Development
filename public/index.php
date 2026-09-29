<?php
$config = require __DIR__ . '/../config/app.php';
$langCode = $_GET['lang'] ?? $config['default_language'];
if (!in_array($langCode, $config['supported_languages'], true)) $langCode = $config['default_language'];
$lang = require __DIR__ . '/../lang/' . $langCode . '.php';
function tr(array $lang,string $key,string $fallback=''){return htmlspecialchars($lang[$key] ?? ($fallback ?: $key));}
?>
<!doctype html>
<html lang="<?= htmlspecialchars($langCode) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($config['app_name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="app-shell">
<aside class="sidebar">
  <div class="brand d-flex align-items-center gap-3">
    <div class="brand-icon">O</div>
    <div><strong>Oriantis</strong><small>Business Development</small></div>
  </div>

  <div class="menu-label">MENU</div>
  <nav class="nav flex-column gap-1">
    <a class="nav-link active" href="#"><i class="bi bi-grid-1x2-fill"></i><span><?=tr($lang,'dashboard','Dashboard')?></span></a>
    <a class="nav-link" href="/hotels/index.php"><i class="bi bi-building"></i><span><?=tr($lang,'hotels','Hotels')?></span></a>
    <a class="nav-link" href="#"><i class="bi bi-briefcase"></i><span><?=tr($lang,'services','Services')?></span></a>
    <a class="nav-link" href="#"><i class="bi bi-people"></i><span><?=tr($lang,'agencies','Agencies')?></span></a>
  </nav>

  <div class="menu-label mt-4">QUOTES</div>
  <nav class="nav flex-column gap-1">
    <a class="nav-link" href="#"><i class="bi bi-file-earmark-text"></i><span><?=tr($lang,'quotes','Quotes')?></span></a>
    <a class="nav-link" href="#"><i class="bi bi-file-earmark-plus"></i><span><?=tr($lang,'new_quote','New Quote')?></span></a>
  </nav>

  <div class="menu-label mt-4">SYSTEM</div>
  <nav class="nav flex-column gap-1">
    <a class="nav-link" href="#"><i class="bi bi-currency-exchange"></i><span><?=tr($lang,'exchange_rates','Exchange Rates')?></span></a>
    <a class="nav-link" href="#"><i class="bi bi-gear"></i><span><?=tr($lang,'settings','Settings')?></span></a>
  </nav>
</aside>

<main class="main">
  <header class="topbar">
    <div>
      <div class="eyebrow">ORIANTIS BUSINESS DEVELOPMENT</div>
      <h1><?=tr($lang,'dashboard','Dashboard')?></h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <div class="btn-group">
        <a class="btn btn-sm <?= $langCode==='en'?'btn-primary':'btn-light border' ?>" href="?lang=en">EN</a>
        <a class="btn btn-sm <?= $langCode==='fr'?'btn-primary':'btn-light border' ?>" href="?lang=fr">FR</a>
      </div>
      <button class="btn btn-light border rounded-circle"><i class="bi bi-bell"></i></button>
      <div class="avatar">OB</div>
    </div>
  </header>

  <section class="hero-card mb-4">
    <div>
      <span class="badge bg-primary-subtle text-primary mb-2">Quote Management</span>
      <h2>Welcome to Oriantis</h2>
      <p class="text-secondary mb-0">Manage hotels, purchase rates, services, currencies and quotations from one workspace.</p>
    </div>
    <a href="#" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i><?=tr($lang,'new_quote','New Quote')?></a>
  </section>

  <div class="row g-4 mb-4">
    <?php
    $cards=[
      ['Hotels','bi-building','primary'],
      ['Quotes','bi-file-earmark-text','success'],
      ['Agencies','bi-people','warning'],
      ['Currencies','bi-currency-exchange','info']
    ];
    foreach($cards as [$title,$icon,$color]): ?>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="stat-card h-100">
        <div class="stat-icon bg-<?=$color?>-subtle text-<?=$color?>"><i class="bi <?=$icon?>"></i></div>
        <div class="text-muted small"><?=$title?></div>
        <div class="display-6 fw-bold">0</div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4">
    <div class="col-12 col-xl-8">
      <div class="content-card h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div><h5 class="mb-1">Recent Quotes</h5><div class="text-muted small">Latest quotations created in Oriantis</div></div>
          <button class="btn btn-sm btn-outline-primary">View all</button>
        </div>
        <div class="empty-state">
          <div class="empty-icon"><i class="bi bi-file-earmark-plus"></i></div>
          <h6>No quotes yet</h6>
          <p>Create your first quotation to start using the platform.</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-xl-4">
      <div class="content-card h-100">
        <h5>Quick Actions</h5>
        <a class="quick-action" href="/hotels/create.php"><i class="bi bi-building-add"></i><span><strong>Add Hotel</strong><small>Add hotel and room purchase rates</small></span></a>
        <a class="quick-action" href="#"><i class="bi bi-plus-square"></i><span><strong>Add Service</strong><small>Add transfers, tours and activities</small></span></a>
        <a class="quick-action" href="#"><i class="bi bi-file-earmark-plus"></i><span><strong>New Quote</strong><small>Create a multi-currency quotation</small></span></a>
      </div>
    </div>
  </div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>