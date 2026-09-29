<?php
require_once __DIR__ . '/../../bootstrap.php';

$currentPage = 'services';
$pageTitle = t('services');

$dbError = null;
$services = [];

try {
    $stmt = db()->query("
        SELECT
            s.id,
            s.name_fr,
            s.name_en,
            s.active,
            sc.name_fr AS category_fr,
            sc.name_en AS category_en,
            d.name_fr AS destination_fr,
            d.name_en AS destination_en,
            COUNT(DISTINCT sv.id) AS variants_count
        FROM services s
        INNER JOIN service_categories sc ON sc.id = s.category_id
        INNER JOIN destinations d ON d.id = s.destination_id
        LEFT JOIN service_variants sv ON sv.service_id = s.id AND sv.active = 1
        GROUP BY s.id
        ORDER BY sc.name_en, s.name_en
    ");
    $services = $stmt->fetchAll();
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
    <h4 class="mb-1"><?= e(t('service_management')) ?></h4>
    <p class="text-muted mb-0"><?= e(t('service_management_help')) ?></p>
  </div>
  <a href="create.php" class="btn btn-primary">
    <i class="bi bi-plus-lg me-2"></i><?= e(t('add_service')) ?>
  </a>
</div>

<?php if ($dbError): ?>
<div class="alert alert-danger"><?= e($dbError) ?></div>
<?php endif; ?>

<?php if (isset($_GET['created'])): ?>
<div class="alert alert-success"><?= e(t('service_created_success')) ?></div>
<?php endif; ?>

<div class="content-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th><?= e(t('service_name')) ?></th>
          <th><?= e(t('category')) ?></th>
          <th><?= e(t('destination')) ?></th>
          <th><?= e(t('variants')) ?></th>
          <th class="text-end"><?= e(t('actions')) ?></th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$services): ?>
        <tr>
          <td colspan="5">
            <div class="empty-state py-5">
              <div class="empty-icon"><i class="bi bi-briefcase"></i></div>
              <h6><?= e(t('no_services')) ?></h6>
              <p><?= e(t('no_services_help')) ?></p>
              <a href="create.php" class="btn btn-primary btn-sm"><?= e(t('add_service')) ?></a>
            </div>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($services as $service): ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= e($langCode === 'fr' ? $service['name_fr'] : $service['name_en']) ?></div>
            <div class="text-muted small">#<?= (int)$service['id'] ?></div>
          </td>
          <td><?= e($langCode === 'fr' ? $service['category_fr'] : $service['category_en']) ?></td>
          <td><?= e($langCode === 'fr' ? $service['destination_fr'] : $service['destination_en']) ?></td>
          <td><span class="badge text-bg-light border"><?= (int)$service['variants_count'] ?></span></td>
          <td class="text-end">
            <a href="variants.php?id=<?= (int)$service['id'] ?>" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-diagram-3 me-1"></i><?= e(t('manage_variants')) ?>
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