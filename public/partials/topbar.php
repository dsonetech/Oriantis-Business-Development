<?php
$pageTitle = $pageTitle ?? t('dashboard');
?>
<header class="topbar">
  <div>
    <div class="eyebrow">ORIANTIS BUSINESS DEVELOPMENT</div>
    <h1><?= e($pageTitle) ?></h1>
  </div>
  <div class="d-flex align-items-center gap-2">
    <div class="btn-group">
      <a class="btn btn-sm <?= $langCode === 'en' ? 'btn-primary' : 'btn-light border' ?>" href="?lang=en">EN</a>
      <a class="btn btn-sm <?= $langCode === 'fr' ? 'btn-primary' : 'btn-light border' ?>" href="?lang=fr">FR</a>
    </div>
    <button class="btn btn-light border rounded-circle"><i class="bi bi-bell"></i></button>
    <div class="avatar">OB</div>
  </div>
</header>
