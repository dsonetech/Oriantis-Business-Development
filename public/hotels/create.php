<?php

require_once __DIR__ . '/../../bootstrap.php';

$currentPage = 'hotels';
$pageTitle = t('add_hotel');
$dbError = null;
$destinations = [];
$currencies = [];

try {
    $destinations = db()->query("SELECT id, name_fr, name_en FROM destinations WHERE active = 1 ORDER BY name_en")->fetchAll();
    $currencies = db()->query("SELECT id, code, name_fr, name_en FROM currencies WHERE active = 1 ORDER BY code")->fetchAll();
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}
?>
<!doctype html>
<html lang="<?= e($langCode) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> - Oriantis</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body>
<div class="app-shell">
<?php require __DIR__ . '/../partials/sidebar.php'; ?>
<main class="main">
<?php require __DIR__ . '/../partials/topbar.php'; ?>

<div class="row justify-content-center">
<div class="col-12 col-xl-9">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-1"><?= e(t('add_hotel')) ?></h4>
      <p class="text-muted mb-0"><?= e(t('add_hotel_help')) ?></p>
    </div>
    <a href="index.php" class="btn btn-light border"><i class="bi bi-arrow-left me-2"></i><?= e(t('back')) ?></a>
  </div>

  <?php if ($dbError): ?>
  <div class="alert alert-danger"><?= e($dbError) ?></div>
  <?php endif; ?>

  <form method="post" action="store.php" class="content-card">
    <div class="row g-4">
      <div class="col-md-8">
        <label class="form-label"><?= e(t('hotel_name')) ?> *</label>
        <input type="text" name="name" class="form-control" required placeholder="Gloria Hotel & Suites">
      </div>

      <div class="col-md-4">
        <label class="form-label"><?= e(t('stars')) ?></label>
        <select name="stars" class="form-select">
          <option value="">—</option>
          <?php for ($i = 1; $i <= 5; $i++): ?>
          <option value="<?= $i ?>"><?= $i ?> ★</option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label"><?= e(t('destination')) ?> *</label>
        <select name="destination_id" class="form-select" required>
          <option value=""><?= e(t('select_option')) ?></option>
          <?php foreach ($destinations as $destination): ?>
          <option value="<?= (int)$destination['id'] ?>">
            <?= e($langCode === 'fr' ? $destination['name_fr'] : $destination['name_en']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label"><?= e(t('purchase_currency')) ?> *</label>
        <select name="currency_id" class="form-select" required>
          <option value=""><?= e(t('select_option')) ?></option>
          <?php foreach ($currencies as $currency): ?>
          <option value="<?= (int)$currency['id'] ?>"><?= e($currency['code']) ?> — <?= e($langCode === 'fr' ? $currency['name_fr'] : $currency['name_en']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label"><?= e(t('address')) ?></label>
        <textarea name="address" class="form-control" rows="2"></textarea>
      </div>

      <div class="col-12">
        <label class="form-label"><?= e(t('notes')) ?></label>
        <textarea name="notes" class="form-control" rows="3"></textarea>
      </div>

      <div class="col-12">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="active" value="1" id="active" checked>
          <label class="form-check-label" for="active"><?= e(t('active_hotel')) ?></label>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
      <a href="index.php" class="btn btn-light border"><?= e(t('cancel')) ?></a>
      <button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-2"></i><?= e(t('save_hotel')) ?></button>
    </div>
  </form>
</div>
</div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
