<?php
require_once __DIR__ . '/../../bootstrap.php';

$currentPage = 'services';
$pageTitle = t('add_service');

$destinations = db()->query("SELECT id, name_fr, name_en FROM destinations WHERE active = 1 ORDER BY name_en")->fetchAll();
$categories = db()->query("SELECT id, name_fr, name_en FROM service_categories WHERE active = 1 ORDER BY name_en")->fetchAll();
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
      <h4 class="mb-1"><?= e(t('add_service')) ?></h4>
      <p class="text-muted mb-0"><?= e(t('add_service_help')) ?></p>
    </div>
    <a href="index.php" class="btn btn-light border"><i class="bi bi-arrow-left me-2"></i><?= e(t('back')) ?></a>
  </div>

  <form method="post" action="store.php" class="content-card">
    <div class="row g-4">
      <div class="col-md-6">
        <label class="form-label"><?= e(t('service_name_en')) ?> *</label>
        <input type="text" name="name_en" class="form-control" required placeholder="Bus 22 PAX">
      </div>
      <div class="col-md-6">
        <label class="form-label"><?= e(t('service_name_fr')) ?> *</label>
        <input type="text" name="name_fr" class="form-control" required placeholder="Bus 22 PAX">
      </div>

      <div class="col-md-6">
        <label class="form-label"><?= e(t('category')) ?> *</label>
        <select name="category_id" class="form-select" required>
          <option value=""><?= e(t('select_option')) ?></option>
          <?php foreach ($categories as $category): ?>
          <option value="<?= (int)$category['id'] ?>">
            <?= e($langCode === 'fr' ? $category['name_fr'] : $category['name_en']) ?>
          </option>
          <?php endforeach; ?>
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

      <div class="col-md-4">
        <label class="form-label"><?= e(t('pricing_type')) ?> *</label>
        <select name="pricing_type" class="form-select" required>
          <option value="PER_UNIT"><?= e(t('per_unit')) ?></option>
          <option value="PER_PAX"><?= e(t('per_pax')) ?></option>
          <option value="PER_GROUP"><?= e(t('per_group')) ?></option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label"><?= e(t('capacity')) ?></label>
        <input type="number" name="capacity" class="form-control" min="1" placeholder="22">
      </div>

      <div class="col-md-4">
        <label class="form-label"><?= e(t('duration_hours')) ?></label>
        <input type="number" name="duration_hours" class="form-control" min="0" step="0.5" placeholder="4">
      </div>

      <div class="col-12">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="active" value="1" id="active" checked>
          <label class="form-check-label" for="active"><?= e(t('active_service')) ?></label>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
      <a href="index.php" class="btn btn-light border"><?= e(t('cancel')) ?></a>
      <button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-2"></i><?= e(t('save_service')) ?></button>
    </div>
  </form>
</div>
</div>

</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>