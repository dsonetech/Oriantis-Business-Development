<?php

require_once __DIR__ . '/../../bootstrap.php';

$currentPage = 'hotels';
$rateId = (int)($_GET['id'] ?? 0);

if ($rateId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare("
    SELECT hr.*, h.name AS hotel_name, h.id AS hotel_id, c.code AS currency_code
    FROM hotel_rates hr
    INNER JOIN hotels h ON h.id = hr.hotel_id
    INNER JOIN currencies c ON c.id = h.currency_id
    WHERE hr.id = :id
");
$stmt->execute(['id' => $rateId]);
$rate = $stmt->fetch();

if (!$rate) {
    http_response_code(404);
    exit('Rate not found.');
}

$pageTitle = t('edit_rate');

$types = db()->query("
    SELECT id, code, name_fr, name_en
    FROM accommodation_types
    WHERE active = 1
    ORDER BY sort_order, id
")->fetchAll();
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
  <div class="col-12 col-xl-8">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1"><?= e(t('edit_rate')) ?></h4>
        <p class="text-muted mb-0"><?= e($rate['hotel_name']) ?></p>
      </div>
      <a href="rates.php?id=<?= (int)$rate['hotel_id'] ?>" class="btn btn-light border">
        <i class="bi bi-arrow-left me-2"></i><?= e(t('back')) ?>
      </a>
    </div>

    <form action="update-rate.php" method="post" class="content-card">
      <input type="hidden" name="rate_id" value="<?= (int)$rate['id'] ?>">
      <input type="hidden" name="hotel_id" value="<?= (int)$rate['hotel_id'] ?>">

      <div class="row g-4">
        <div class="col-md-6">
          <label class="form-label"><?= e(t('room_type')) ?> *</label>
          <select name="accommodation_type_id" class="form-select" required>
            <?php foreach ($types as $type): ?>
            <option value="<?= (int)$type['id'] ?>" <?= (int)$type['id'] === (int)$rate['accommodation_type_id'] ? 'selected' : '' ?>>
              <?= e($langCode === 'fr' ? $type['name_fr'] : $type['name_en']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label"><?= e(t('purchase_rate')) ?> (<?= e($rate['currency_code']) ?>) *</label>
          <input type="number" name="amount" class="form-control" min="0" step="0.01" value="<?= e((string)$rate['amount']) ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label"><?= e(t('meal_plan')) ?></label>
          <select name="meal_plan" class="form-select">
            <?php foreach (['RO','BB','HB','FB','AI'] as $mealPlan): ?>
            <option value="<?= e($mealPlan) ?>" <?= $rate['meal_plan'] === $mealPlan ? 'selected' : '' ?>><?= e($mealPlan) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label"><?= e(t('valid_from')) ?></label>
          <input type="date" name="valid_from" class="form-control" value="<?= e($rate['valid_from']) ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label"><?= e(t('valid_to')) ?></label>
          <input type="date" name="valid_to" class="form-control" value="<?= e($rate['valid_to']) ?>">
        </div>

        <div class="col-12">
          <label class="form-label"><?= e(t('notes')) ?></label>
          <textarea name="notes" rows="3" class="form-control"><?= e($rate['notes']) ?></textarea>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
        <a href="rates.php?id=<?= (int)$rate['hotel_id'] ?>" class="btn btn-light border"><?= e(t('cancel')) ?></a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check2 me-2"></i><?= e(t('save_changes')) ?>
        </button>
      </div>
    </form>
  </div>
</div>

</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
