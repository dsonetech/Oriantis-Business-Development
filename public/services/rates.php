<?php
require_once __DIR__ . '/../../bootstrap.php';

$currentPage = 'services';
$serviceId = (int)($_GET['id'] ?? 0);

if ($serviceId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare("
    SELECT s.*, sc.name_fr AS category_fr, sc.name_en AS category_en,
           d.name_fr AS destination_fr, d.name_en AS destination_en
    FROM services s
    INNER JOIN service_categories sc ON sc.id = s.category_id
    INNER JOIN destinations d ON d.id = s.destination_id
    WHERE s.id = :id
");
$stmt->execute(['id' => $serviceId]);
$service = $stmt->fetch();

if (!$service) {
    http_response_code(404);
    exit('Service not found.');
}

$pageTitle = $langCode === 'fr' ? $service['name_fr'] : $service['name_en'];

$currencies = db()->query("SELECT id, code FROM currencies WHERE active = 1 ORDER BY code")->fetchAll();

$rateStmt = db()->prepare("
    SELECT sr.*, c.code AS currency_code
    FROM service_rates sr
    INNER JOIN currencies c ON c.id = sr.currency_id
    WHERE sr.service_id = :service_id
    ORDER BY sr.valid_from DESC, sr.id DESC
");
$rateStmt->execute(['service_id' => $serviceId]);
$rates = $rateStmt->fetchAll();
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

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div>
    <a href="index.php" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i><?= e(t('services')) ?></a>
    <h3 class="mb-1 mt-2"><?= e($pageTitle) ?></h3>
    <div class="text-muted">
      <?= e($langCode === 'fr' ? $service['category_fr'] : $service['category_en']) ?>
      · <?= e($langCode === 'fr' ? $service['destination_fr'] : $service['destination_en']) ?>
      <?php if ($service['capacity']): ?> · <?= (int)$service['capacity'] ?> PAX<?php endif; ?>
      <?php if ($service['duration_hours']): ?> · <?= e((string)$service['duration_hours']) ?> h<?php endif; ?>
    </div>
  </div>
</div>

<?php if (isset($_GET['created'])): ?>
<div class="alert alert-success"><?= e(t('service_created_add_rates')) ?></div>
<?php endif; ?>
<?php if (isset($_GET['rate_saved'])): ?>
<div class="alert alert-success"><?= e(t('rate_saved_success')) ?></div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-12 col-xl-4">
    <div class="content-card">
      <h5 class="mb-1"><?= e(t('add_service_rate')) ?></h5>
      <p class="text-muted small mb-4"><?= e(t('service_rate_help')) ?></p>

      <form action="save-rate.php" method="post">
        <input type="hidden" name="service_id" value="<?= $serviceId ?>">

        <div class="mb-3">
          <label class="form-label"><?= e(t('currency')) ?> *</label>
          <select name="currency_id" class="form-select" required>
            <option value=""><?= e(t('select_option')) ?></option>
            <?php foreach ($currencies as $currency): ?>
            <option value="<?= (int)$currency['id'] ?>" <?= $currency['code'] === 'QAR' ? 'selected' : '' ?>><?= e($currency['code']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label"><?= e(t('purchase_rate')) ?> *</label>
          <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
        </div>

        <div class="row g-3">
          <div class="col-6">
            <label class="form-label"><?= e(t('valid_from')) ?></label>
            <input type="date" name="valid_from" class="form-control">
          </div>
          <div class="col-6">
            <label class="form-label"><?= e(t('valid_to')) ?></label>
            <input type="date" name="valid_to" class="form-control">
          </div>
        </div>

        <button class="btn btn-primary w-100 mt-4" type="submit">
          <i class="bi bi-plus-lg me-2"></i><?= e(t('add_rate')) ?>
        </button>
      </form>
    </div>
  </div>

  <div class="col-12 col-xl-8">
    <div class="content-card">
      <h5 class="mb-1"><?= e(t('purchase_rates')) ?></h5>
      <p class="text-muted small"><?= e(t('service_rates_internal')) ?></p>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <th><?= e(t('period')) ?></th>
            <th><?= e(t('currency')) ?></th>
            <th class="text-end"><?= e(t('purchase_rate')) ?></th>
          </tr>
          </thead>
          <tbody>
          <?php if (!$rates): ?>
            <tr><td colspan="3" class="text-center text-muted py-5"><?= e(t('no_rates')) ?></td></tr>
          <?php else: ?>
            <?php foreach ($rates as $rate): ?>
            <tr>
              <td>
                <?php if ($rate['valid_from'] || $rate['valid_to']): ?>
                <?= e(format_date($rate['valid_from'])) ?> → <?= e(format_date($rate['valid_to'])) ?>
                <?php else: ?>
                <?= e(t('all_dates')) ?>
                <?php endif; ?>
              </td>
              <td><span class="badge text-bg-light border"><?= e($rate['currency_code']) ?></span></td>
              <td class="text-end fw-semibold"><?= number_format((float)$rate['amount'], 2) ?> <?= e($rate['currency_code']) ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>