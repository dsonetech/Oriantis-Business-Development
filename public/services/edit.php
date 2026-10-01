<?php
require_once __DIR__ . '/../../bootstrap.php';

$currentPage='services';
$serviceId=(int)($_GET['id']??0);
if($serviceId<=0){header('Location:index.php');exit;}

$stmt=db()->prepare("
  SELECT s.*, sc.name_fr category_fr, sc.name_en category_en
  FROM services s
  INNER JOIN service_categories sc ON sc.id=s.category_id
  WHERE s.id=:id
");
$stmt->execute(['id'=>$serviceId]);
$service=$stmt->fetch();
if(!$service){http_response_code(404);exit('Service not found');}

$destinations=db()->query("SELECT id,name_fr,name_en FROM destinations WHERE active=1 ORDER BY name_en")->fetchAll();

$pageTitle=t('edit_service');
?>
<!doctype html>
<html lang="<?=e($langCode)?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($pageTitle)?> - Oriantis</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?=base_url('assets/css/app.css')?>" rel="stylesheet">
</head>
<body>
<div class="app-shell">
<?php require __DIR__.'/../partials/sidebar.php';?>
<main class="main">
<?php require __DIR__.'/../partials/topbar.php';?>

<div class="row justify-content-center">
<div class="col-12 col-xl-9">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1"><?=e(t('edit_service'))?></h4>
      <p class="text-muted mb-0"><?=e(t('edit_service_help'))?></p>
    </div>
    <a href="index.php" class="btn btn-light border"><i class="bi bi-arrow-left me-2"></i><?=e(t('back'))?></a>
  </div>

  <form method="post" action="update.php" class="content-card">
    <input type="hidden" name="id" value="<?=$serviceId?>">

    <div class="row g-4">
      <div class="col-md-6">
        <label class="form-label"><?=e(t('destination'))?> *</label>
        <select name="destination_id" class="form-select" required>
          <?php foreach($destinations as $d):?>
          <option value="<?=(int)$d['id']?>" <?=(int)$service['destination_id']===(int)$d['id']?'selected':''?>>
            <?=e($langCode==='fr'?$d['name_fr']:$d['name_en'])?>
          </option>
          <?php endforeach;?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label"><?=e(t('category'))?></label>
        <input class="form-control" value="<?=e($langCode==='fr'?$service['category_fr']:$service['category_en'])?>" readonly>
        <div class="form-text"><?=e(t('category_locked_help'))?></div>
      </div>

      <div class="col-md-6">
        <label class="form-label"><?=e(t('service_name_en'))?> *</label>
        <input name="name_en" class="form-control" required value="<?=e($service['name_en'])?>">
      </div>

      <div class="col-md-6">
        <label class="form-label"><?=e(t('service_name_fr'))?> *</label>
        <input name="name_fr" class="form-control" required value="<?=e($service['name_fr'])?>">
      </div>
    </div>

    <div class="form-check form-switch mt-4">
      <input class="form-check-input" type="checkbox" name="active" value="1" id="active" <?=$service['active']?'checked':''?>>
      <label class="form-check-label" for="active"><?=e(t('active_service'))?></label>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
      <a href="index.php" class="btn btn-light border"><?=e(t('cancel'))?></a>
      <button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-2"></i><?=e(t('save_changes'))?></button>
    </div>
  </form>
</div>
</div>

</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>