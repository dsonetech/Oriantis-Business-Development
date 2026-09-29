<?php

require_once __DIR__ . '/../../bootstrap.php';

$currentPage = 'hotels';
$pageTitle = t('hotels');

$dbError = null;
$hotels = [];

try {
    $stmt = db()->query("
        SELECT
            h.id,
            h.name,
            h.stars,
            h.active,
            d.name_en AS destination_en,
            d.name_fr AS destination_fr,
            c.code AS currency_code,
            COUNT(hr.id) AS rates_count
        FROM hotels h
        INNER JOIN destinations d ON d.id = h.destination_id
        INNER JOIN currencies c ON c.id = h.currency_id
        LEFT JOIN hotel_rates hr ON hr.hotel_id = h.id AND hr.active = 1
        GROUP BY h.id
        ORDER BY h.name
    ");
    $hotels = $stmt->fetchAll();
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

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="mb-1"><?= e(t('hotel_management')) ?></h4>
    <p class="text-muted mb-0"><?= e(t('hotel_management_help')) ?></p>
  </div>
  <a href="create.php" class="btn btn-primary">
    <i class="bi bi-plus-lg me-2"></i><?= e(t('add_hotel')) ?>
  </a>
</div>

<?php if ($dbError): ?>
<div class="alert alert-danger">
  <strong><?= e(t('database_error')) ?>:</strong> <?= e($dbError) ?>
  <div class="small mt-2"><?= e(t('database_import_hint')) ?></div>
</div>
<?php endif; ?>

<?php if (isset($_GET['created'])): ?>
<div class="alert alert-success"><?= e(t('hotel_created_success')) ?></div>
<?php endif; ?>

<div class="content-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th><?= e(t('hotel_name')) ?></th>
          <th><?= e(t('destination')) ?></th>
          <th><?= e(t('stars')) ?></th>
          <th><?= e(t('currency')) ?></th>
          <th><?= e(t('room_rates')) ?></th>
          <th><?= e(t('status')) ?></th>
          <th class="text-end"><?= e(t('actions')) ?></th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$hotels): ?>
        <tr>
          <td colspan="7">
            <div class="empty-state py-5">
              <div class="empty-icon"><i class="bi bi-building-add"></i></div>
              <h6><?= e(t('no_hotels')) ?></h6>
              <p><?= e(t('no_hotels_help')) ?></p>
              <a href="create.php" class="btn btn-primary btn-sm"><?= e(t('add_hotel')) ?></a>
            </div>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($hotels as $hotel): ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= e($hotel['name']) ?></div>
            <div class="text-muted small">#<?= (int)$hotel['id'] ?></div>
          </td>
          <td><?= e($langCode === 'fr' ? $hotel['destination_fr'] : $hotel['destination_en']) ?></td>
          <td><?= $hotel['stars'] ? str_repeat('★', (int)$hotel['stars']) : '—' ?></td>
          <td><span class="badge text-bg-light border"><?= e($hotel['currency_code']) ?></span></td>
          <td><?= (int)$hotel['rates_count'] ?></td>
          <td>
            <span class="badge <?= $hotel['active'] ? 'text-bg-success-subtle text-success' : 'text-bg-secondary-subtle text-secondary' ?>">
              <?= e($hotel['active'] ? t('active') : t('inactive')) ?>
            </span>
          </td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="rates.php?id=<?= (int)$hotel['id'] ?>">
              <i class="bi bi-cash-stack me-1"></i><?= e(t('manage_rates')) ?>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
