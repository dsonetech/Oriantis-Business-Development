<?php
require_once __DIR__ . '/../../bootstrap.php';

$currentPage = 'services';
$pageTitle = t('add_service');

$destinations = db()->query("SELECT id, name_fr, name_en FROM destinations WHERE active = 1 ORDER BY name_en")->fetchAll();
$categories = db()->query("SELECT id, code, name_fr, name_en FROM service_categories WHERE active = 1 ORDER BY name_en")->fetchAll();
$currencies = db()->query("SELECT id, code FROM currencies WHERE active = 1 ORDER BY code")->fetchAll();
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
<style>
.service-fields{display:none}
.service-fields.active{display:block}
</style>
</head>
<body>
<div class="app-shell">
<?php require __DIR__ . '/../partials/sidebar.php'; ?>
<main class="main">
<?php require __DIR__ . '/../partials/topbar.php'; ?>

<div class="row justify-content-center">
<div class="col-12 col-xl-10">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-1"><?= e(t('add_service')) ?></h4>
      <p class="text-muted mb-0"><?= e(t('simple_service_help')) ?></p>
    </div>
    <a href="index.php" class="btn btn-light border"><i class="bi bi-arrow-left me-2"></i><?= e(t('back')) ?></a>
  </div>

  <form method="post" action="store.php" class="content-card" id="serviceForm">
    <div class="row g-4">
      <div class="col-md-4">
        <label class="form-label"><?= e(t('category')) ?> *</label>
        <select name="category_id" id="category" class="form-select" required>
          <option value=""><?= e(t('select_option')) ?></option>
          <?php foreach ($categories as $category): ?>
          <option value="<?= (int)$category['id'] ?>" data-code="<?= e($category['code']) ?>">
            <?= e($langCode === 'fr' ? $category['name_fr'] : $category['name_en']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
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
        <label class="form-label"><?= e(t('currency')) ?> *</label>
        <select name="currency_id" class="form-select" required>
          <?php foreach ($currencies as $currency): ?>
          <option value="<?= (int)$currency['id'] ?>" <?= $currency['code']==='QAR'?'selected':'' ?>><?= e($currency['code']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label"><?= e(t('service_name_en')) ?> *</label>
        <input type="text" name="name_en" class="form-control" required placeholder="Van Sprinter">
      </div>
      <div class="col-md-6">
        <label class="form-label"><?= e(t('service_name_fr')) ?> *</label>
        <input type="text" name="name_fr" class="form-control" required placeholder="Van Sprinter">
      </div>
    </div>

    <hr class="my-4">

    <div id="chooseHint" class="alert alert-light border mb-0">
      <i class="bi bi-arrow-up-circle me-2"></i><?= e(t('choose_service_category')) ?>
    </div>

    <!-- TRANSFER -->
    <div class="service-fields" data-for="TRANSFER">
      <h5 class="mb-3"><?= e(t('transfer_details')) ?></h5>
      <div class="row g-4">
        <div class="col-md-4">
          <label class="form-label"><?= e(t('capacity_seats')) ?> *</label>
          <input type="number" name="transfer_capacity" min="1" class="form-control" placeholder="10">
        </div>
        <div class="col-md-4">
          <label class="form-label"><?= e(t('one_way_price')) ?></label>
          <div class="input-group"><input type="number" name="one_way_price" min="0" step=".01" class="form-control" placeholder="600"><span class="input-group-text">QAR</span></div>
        </div>
        <div class="col-md-4">
          <label class="form-label"><?= e(t('round_trip_price')) ?></label>
          <div class="input-group"><input type="number" name="round_trip_price" min="0" step=".01" class="form-control" placeholder="1200"><span class="input-group-text">QAR</span></div>
        </div>
      </div>
      <div class="form-text mt-2"><?= e(t('transfer_route_help')) ?></div>
    </div>

    <!-- BOAT -->
    <div class="service-fields" data-for="BOAT">
      <h5 class="mb-3"><?= e(t('boat_details')) ?></h5>
      <div class="row g-4">
        <div class="col-md-4">
          <label class="form-label"><?= e(t('capacity_pax')) ?> *</label>
          <input type="number" name="boat_capacity" min="1" class="form-control" placeholder="45">
        </div>
        <div class="col-md-4">
          <label class="form-label"><?= e(t('with_transfer')) ?> *</label>
          <select name="boat_with_transfer" class="form-select">
            <option value="0"><?= e(t('no')) ?></option>
            <option value="1"><?= e(t('yes')) ?></option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label"><?= e(t('price')) ?> *</label>
          <div class="input-group"><input type="number" name="boat_price" min="0" step=".01" class="form-control" placeholder="1800"><span class="input-group-text">QAR</span></div>
        </div>
      </div>
    </div>

    <!-- CITY TOUR -->
    <div class="service-fields" data-for="CITY_TOUR">
      <h5 class="mb-3"><?= e(t('city_tour_details')) ?></h5>
      <div class="row g-4">
        <div class="col-md-4">
          <label class="form-label"><?= e(t('capacity_seats')) ?> *</label>
          <input type="number" name="city_capacity" min="1" class="form-control" placeholder="10">
        </div>
        <div class="col-md-4">
          <label class="form-label"><?= e(t('price_4_hours')) ?></label>
          <div class="input-group"><input type="number" name="city_4h_price" min="0" step=".01" class="form-control" placeholder="1800"><span class="input-group-text">QAR</span></div>
        </div>
        <div class="col-md-4">
          <label class="form-label"><?= e(t('price_8_hours')) ?></label>
          <div class="input-group"><input type="number" name="city_8h_price" min="0" step=".01" class="form-control" placeholder="2500"><span class="input-group-text">QAR</span></div>
        </div>
        <div class="col-md-4">
          <label class="form-label"><?= e(t('guide_available')) ?></label>
          <select name="city_guide" id="cityGuide" class="form-select">
            <option value="0"><?= e(t('no')) ?></option>
            <option value="1"><?= e(t('yes')) ?></option>
          </select>
        </div>
        <div class="col-md-4" id="guideRateBox" style="display:none">
          <label class="form-label"><?= e(t('guide_price_hour')) ?></label>
          <div class="input-group"><input type="number" name="guide_hour_rate" min="0" step=".01" class="form-control" placeholder="140"><span class="input-group-text">QAR/h</span></div>
        </div>
      </div>
      <div class="form-text mt-2"><?= e(t('guide_auto_help')) ?></div>
    </div>

    <!-- SAFARI -->
    <div class="service-fields" data-for="SAFARI">
      <h5 class="mb-3"><?= e(t('safari_details')) ?></h5>
      <div class="row g-4">
        <div class="col-md-4"><label class="form-label"><?= e(t('capacity_pax')) ?> *</label><input type="number" name="safari_capacity" min="1" class="form-control" placeholder="6"></div>
        <div class="col-md-4"><label class="form-label"><?= e(t('without_dinner_price')) ?></label><div class="input-group"><input type="number" name="safari_no_dinner_price" min="0" step=".01" class="form-control"><span class="input-group-text">QAR</span></div></div>
        <div class="col-md-4"><label class="form-label"><?= e(t('with_dinner_price')) ?></label><div class="input-group"><input type="number" name="safari_dinner_price" min="0" step=".01" class="form-control"><span class="input-group-text">QAR</span></div></div>
      </div>
    </div>

    <!-- SAFARI LUXE -->
    <div class="service-fields" data-for="SAFARI_LUXE">
      <h5 class="mb-3"><?= e(t('safari_luxe_details')) ?></h5>
      <div class="row g-4">
        <div class="col-md-4"><label class="form-label"><?= e(t('capacity_pax')) ?> *</label><input type="number" name="safari_luxe_capacity" min="1" class="form-control" placeholder="6"></div>
        <div class="col-md-4"><label class="form-label"><?= e(t('without_dinner_price')) ?></label><div class="input-group"><input type="number" name="safari_luxe_no_dinner_price" min="0" step=".01" class="form-control"><span class="input-group-text">QAR</span></div></div>
        <div class="col-md-4"><label class="form-label"><?= e(t('with_dinner_price')) ?></label><div class="input-group"><input type="number" name="safari_luxe_dinner_price" min="0" step=".01" class="form-control"><span class="input-group-text">QAR</span></div></div>
      </div>
    </div>

    <!-- VISA -->
    <div class="service-fields" data-for="VISA">
      <h5 class="mb-3"><?= e(t('visa_details')) ?></h5>
      <div class="row g-4">
        <div class="col-md-4"><label class="form-label"><?= e(t('price_per_pax')) ?> *</label><div class="input-group"><input type="number" name="visa_price" min="0" step=".01" class="form-control" placeholder="100"><span class="input-group-text">QAR/PAX</span></div></div>
      </div>
    </div>

    <!-- GUIDE -->
    <div class="service-fields" data-for="GUIDE">
      <h5 class="mb-3"><?= e(t('guide_details')) ?></h5>
      <div class="row g-4">
        <div class="col-md-4"><label class="form-label"><?= e(t('guide_price_hour')) ?> *</label><div class="input-group"><input type="number" name="guide_price" min="0" step=".01" class="form-control" placeholder="140"><span class="input-group-text">QAR/h</span></div></div>
      </div>
    </div>

    <!-- OTHER -->
    <div class="service-fields" data-for="OTHER">
      <h5 class="mb-3"><?= e(t('other_service_details')) ?></h5>
      <div class="row g-4">
        <div class="col-md-4"><label class="form-label"><?= e(t('price')) ?> *</label><input type="number" name="other_price" min="0" step=".01" class="form-control"></div>
        <div class="col-md-4"><label class="form-label"><?= e(t('pricing_type')) ?></label>
          <select name="other_pricing_type" class="form-select">
            <option value="PER_UNIT"><?= e(t('per_unit')) ?></option>
            <option value="PER_PAX"><?= e(t('per_pax')) ?></option>
            <option value="PER_GROUP"><?= e(t('per_group')) ?></option>
            <option value="PER_HOUR"><?= e(t('per_hour')) ?></option>
          </select>
        </div>
        <div class="col-md-4"><label class="form-label"><?= e(t('capacity')) ?></label><input type="number" name="other_capacity" min="1" class="form-control"></div>
      </div>
    </div>

    <div class="form-check form-switch mt-4">
      <input class="form-check-input" type="checkbox" name="active" value="1" id="active" checked>
      <label class="form-check-label" for="active"><?= e(t('active_service')) ?></label>
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
<script>
const category = document.getElementById('category');
const chooseHint = document.getElementById('chooseHint');
const fields = document.querySelectorAll('.service-fields');
const cityGuide = document.getElementById('cityGuide');
const guideRateBox = document.getElementById('guideRateBox');

function refreshCategory(){
  fields.forEach(el=>el.classList.remove('active'));
  const opt=category.options[category.selectedIndex];
  const code=opt ? opt.dataset.code : '';
  if(code){
    chooseHint.style.display='none';
    const box=document.querySelector('.service-fields[data-for="'+code+'"]');
    if(box) box.classList.add('active');
  } else {
    chooseHint.style.display='block';
  }
}
category.addEventListener('change',refreshCategory);
cityGuide.addEventListener('change',()=>guideRateBox.style.display=cityGuide.value==='1'?'block':'none');
refreshCategory();
</script>
</body>
</html>