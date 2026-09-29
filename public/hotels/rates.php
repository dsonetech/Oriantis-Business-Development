<?php

require_once __DIR__ . '/../../bootstrap.php';

$currentPage = 'hotels';
$hotelId = (int)($_GET['id'] ?? 0);

if ($hotelId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare("
    SELECT h.*, c.code AS currency_code,
           d.name_en AS destination_en, d.name_fr AS destination_fr
    FROM hotels h
    INNER JOIN currencies c ON c.id = h.currency_id
    INNER JOIN destinations d ON d.id = h.destination_id
    WHERE h.id = :id
");
$stmt->execute(['id' => $hotelId]);
$hotel = $stmt->fetch();

if (!$hotel) {
    http_response_code(404);
    exit('Hotel not found.');
}

$pageTitle = $hotel['name'];

$types = db()->query("
    SELECT id, code, name_fr, name_en, divisor
    FROM accommodation_types
    WHERE active = 1
    ORDER BY sort_order, id
")->fetchAll();

$rateStmt = db()->prepare("
    SELECT hr.*, at.code AS accommodation_code, at.name_fr, at.name_en
    FROM hotel_rates hr
    INNER JOIN accommodation_types at ON at.id = hr.accommodation_type_id
    WHERE hr.hotel_id = :hotel_id
    ORDER BY at.sort_order, hr.valid_from DESC, hr.id DESC
");
$rateStmt->execute(['hotel_id' => $hotelId]);
$rates = $rateStmt->fetchAll();
?>
<!doctype html>
<html lang="<?= e($langCode) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($hotel['name']) ?> - Oriantis</title>
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
    <a href="index.php" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i><?= e(t('hotels')) ?></a>
    <h3 class="mb-1 mt-2"><?= e($hotel['name']) ?></h3>
    <div class="text-muted">
      <?= e($langCode === 'fr' ? $hotel['destination_fr'] : $hotel['destination_en']) ?>
      · <?= e($hotel['currency_code']) ?>
      <?= $hotel['stars'] ? ' · ' . str_repeat('★', (int)$hotel['stars']) : '' ?>
    </div>
  </div>
</div>

<?php if (isset($_GET['created'])): ?>
<div class="alert alert-success"><?= e(t('hotel_created_add_rates')) ?></div>
<?php endif; ?>
<?php if (isset($_GET['rate_saved'])): ?>
<div class="alert alert-success"><?= e(t('rate_saved_success')) ?></div>
<?php endif; ?>
<?php if (isset($_GET['rate_updated'])): ?>
<div class="alert alert-success"><?= e(t('rate_updated_success')) ?></div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-12 col-xl-4">
    <div class="content-card">
      <h5 class="mb-1"><?= e(t('add_room_rate')) ?></h5>
      <p class="text-muted small mb-4"><?= e(t('room_rate_help')) ?></p>

      <form action="save-rate.php" method="post">
        <input type="hidden" name="hotel_id" value="<?= $hotelId ?>">

        <div class="mb-3">
          <label class="form-label"><?= e(t('room_type')) ?> *</label>
          <select name="accommodation_type_id" class="form-select" required>
            <option value=""><?= e(t('select_option')) ?></option>
            <?php foreach ($types as $type): ?>
            <option value="<?= (int)$type['id'] ?>"><?= e($langCode === 'fr' ? $type['name_fr'] : $type['name_en']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label"><?= e(t('purchase_rate')) ?> (<?= e($hotel['currency_code']) ?>) *</label>
          <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><?= e(t('meal_plan')) ?></label>
          <select name="meal_plan" class="form-select">
            <option value="RO">RO - Room Only</option>
            <option value="BB" selected>BB - Bed & Breakfast</option>
            <option value="HB">HB - Half Board</option>
            <option value="FB">FB - Full Board</option>
            <option value="AI">AI - All Inclusive</option>
          </select>
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

        <div class="mb-3 mt-3">
          <label class="form-label"><?= e(t('notes')) ?></label>
          <textarea name="notes" rows="2" class="form-control"></textarea>
        </div>

        <button class="btn btn-primary w-100" type="submit">
          <i class="bi bi-plus-lg me-2"></i><?= e(t('add_rate')) ?>
        </button>
      </form>
    </div>
  </div>

  <div class="col-12 col-xl-8">
    <div class="content-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="mb-1"><?= e(t('purchase_rates')) ?></h5>
          <div class="text-muted small"><?= e(t('purchase_rates_internal')) ?></div>
        </div>
        <span class="badge text-bg-light border"><?= e($hotel['currency_code']) ?></span>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th><?= e(t('room_type')) ?></th>
              <th><?= e(t('meal_plan')) ?></th>
              <th><?= e(t('period')) ?></th>
              <th class="text-end"><?= e(t('purchase_rate')) ?></th>
              <th class="text-end"><?= e(t('actions')) ?></th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$rates): ?>
            <tr><td colspan="5" class="text-center text-muted py-5"><?= e(t('no_rates')) ?></td></tr>
          <?php else: ?>
            <?php foreach ($rates as $rate): ?>
            <tr>
              <td><strong><?= e($langCode === 'fr' ? $rate['name_fr'] : $rate['name_en']) ?></strong><div class="small text-muted"><?= e($rate['accommodation_code']) ?></div></td>
              <td><?= e($rate['meal_plan']) ?></td>
              <td>
                <?php if ($rate['valid_from'] || $rate['valid_to']): ?>
                <?= e($rate['valid_from'] ?: '—') ?> → <?= e($rate['valid_to'] ?: '—') ?>
                <?php else: ?>
                <?= e(t('all_dates')) ?>
                <?php endif; ?>
              </td>
              <td class="text-end fw-semibold"><?= number_format((float)$rate['amount'], 2) ?> <?= e($hotel['currency_code']) ?></td>
              <td class="text-end">
                <a href="edit-rate.php?id=<?= (int)$rate['id'] ?>" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-pencil-square me-1"></i><?= e(t('edit')) ?>
                </a>
              </td>
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
