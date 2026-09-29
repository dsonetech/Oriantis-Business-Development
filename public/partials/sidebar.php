<?php
$currentPage = $currentPage ?? '';
?>
<aside class="sidebar">
  <div class="brand d-flex align-items-center gap-3">
    <div class="brand-icon">O</div>
    <div><strong>Oriantis</strong><small>Business Development</small></div>
  </div>

  <div class="menu-label">MENU</div>
  <nav class="nav flex-column gap-1">
    <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('') ?>">
      <i class="bi bi-grid-1x2-fill"></i><span><?= e(t('dashboard')) ?></span>
    </a>
    <a class="nav-link <?= $currentPage === 'hotels' ? 'active' : '' ?>" href="<?= base_url('hotels/index.php') ?>">
      <i class="bi bi-building"></i><span><?= e(t('hotels')) ?></span>
    </a>
    <a class="nav-link <?= $currentPage === 'services' ? 'active' : '' ?>" href="#">
      <i class="bi bi-briefcase"></i><span><?= e(t('services')) ?></span>
    </a>
    <a class="nav-link <?= $currentPage === 'agencies' ? 'active' : '' ?>" href="#">
      <i class="bi bi-people"></i><span><?= e(t('agencies')) ?></span>
    </a>
  </nav>

  <div class="menu-label mt-4">QUOTES</div>
  <nav class="nav flex-column gap-1">
    <a class="nav-link <?= $currentPage === 'quotes' ? 'active' : '' ?>" href="#">
      <i class="bi bi-file-earmark-text"></i><span><?= e(t('quotes')) ?></span>
    </a>
    <a class="nav-link <?= $currentPage === 'new_quote' ? 'active' : '' ?>" href="#">
      <i class="bi bi-file-earmark-plus"></i><span><?= e(t('new_quote')) ?></span>
    </a>
  </nav>

  <div class="menu-label mt-4">SYSTEM</div>
  <nav class="nav flex-column gap-1">
    <a class="nav-link" href="#"><i class="bi bi-currency-exchange"></i><span><?= e(t('exchange_rates')) ?></span></a>
    <a class="nav-link" href="#"><i class="bi bi-gear"></i><span><?= e(t('settings')) ?></span></a>
  </nav>
</aside>
