<?php
require_once __DIR__ . '/../../bootstrap.php';

$currentPage = 'services';
$serviceId = (int)($_GET['id'] ?? 0);
if ($serviceId <= 0) { header('Location: index.php'); exit; }

$stmt = db()->prepare("
    SELECT s.*, sc.name_fr AS category_fr, sc.name_en AS category_en
    FROM services s
    INNER JOIN service_categories sc ON sc.id = s.category_id
    WHERE s.id = :id
");
$stmt->execute(['id'=>$serviceId]);
$service=$stmt->fetch();
if(!$service){http_response_code(404);exit('Service not found');}

$pageTitle = t('service_variants');

$vstmt=db()->prepare("
    SELECT sv.*,
           (SELECT COUNT(*) FROM service_variant_rates r WHERE r.service_variant_id=sv.id AND r.active=1) AS rates_count
    FROM service_variants sv
    WHERE sv.service_id=:id
    ORDER BY sv.capacity, sv.name_en
");
$vstmt->execute(['id'=>$serviceId]);
$variants=$vstmt->fetchAll();
?>
<!doctype html>
<html lang="<?=e($langCode)?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($pageTitle)?> - Oriantis</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?=base_url('assets/css/app.css')?>" rel="stylesheet">
</head>
<body><div class="app-shell">
<?php require __DIR__.'/../partials/sidebar.php'; ?>
<main class="main"><?php require __DIR__.'/../partials/topbar.php'; ?>

<div class="d-flex justify-content-between align-items-start mb-4">
 <div>
  <a href="index.php" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i><?=e(t('services'))?></a>
  <h3 class="mt-2 mb-1"><?=e($langCode==='fr'?$service['name_fr']:$service['name_en'])?></h3>
  <div class="text-muted"><?=e($langCode==='fr'?$service['category_fr']:$service['category_en'])?></div>
 </div>
</div>

<?php if(isset($_GET['saved'])):?><div class="alert alert-success"><?=e(t('variant_saved_success'))?></div><?php endif;?>

<div class="row g-4">
 <div class="col-12 col-xl-4">
  <div class="content-card">
   <h5><?=e(t('add_variant'))?></h5>
   <p class="text-muted small"><?=e(t('variant_help'))?></p>
   <form method="post" action="save-variant.php">
    <input type="hidden" name="service_id" value="<?=$serviceId?>">
    <div class="mb-3"><label class="form-label"><?=e(t('variant_name_en'))?> *</label><input name="name_en" class="form-control" required placeholder="Round Trip / Bus 22 PAX"></div>
    <div class="mb-3"><label class="form-label"><?=e(t('variant_name_fr'))?> *</label><input name="name_fr" class="form-control" required placeholder="Aller-retour / Bus 22 PAX"></div>
    <div class="mb-3"><label class="form-label"><?=e(t('option_code'))?></label><input name="option_code" class="form-control" placeholder="ROUND_TRIP"></div>
    <div class="mb-3"><label class="form-label"><?=e(t('pricing_type'))?> *</label>
      <select name="pricing_type" class="form-select">
       <option value="PER_UNIT"><?=e(t('per_unit'))?></option>
       <option value="PER_PAX"><?=e(t('per_pax'))?></option>
       <option value="PER_GROUP"><?=e(t('per_group'))?></option>
       <option value="PER_HOUR"><?=e(t('per_hour'))?></option>
      </select>
    </div>
    <div class="row g-3">
      <div class="col-6"><label class="form-label"><?=e(t('capacity'))?></label><input type="number" min="1" name="capacity" class="form-control"></div>
      <div class="col-6"><label class="form-label"><?=e(t('duration_hours'))?></label><input type="number" min="0" step=".5" name="duration_hours" class="form-control"></div>
    </div>
    <button class="btn btn-primary w-100 mt-4"><i class="bi bi-plus-lg me-2"></i><?=e(t('add_variant'))?></button>
   </form>
  </div>
 </div>

 <div class="col-12 col-xl-8">
  <div class="content-card">
   <h5><?=e(t('service_variants'))?></h5>
   <div class="table-responsive">
    <table class="table align-middle">
     <thead><tr><th><?=e(t('variant'))?></th><th><?=e(t('pricing_type'))?></th><th><?=e(t('capacity'))?></th><th><?=e(t('duration'))?></th><th><?=e(t('rates'))?></th><th class="text-end"><?=e(t('actions'))?></th></tr></thead>
     <tbody>
     <?php if(!$variants):?><tr><td colspan="6" class="text-center text-muted py-5"><?=e(t('no_variants'))?></td></tr>
     <?php else: foreach($variants as $v):?>
      <tr>
       <td><strong><?=e($langCode==='fr'?$v['name_fr']:$v['name_en'])?></strong><div class="small text-muted"><?=e($v['option_code'])?></div></td>
       <td><span class="badge text-bg-light border"><?=e($v['pricing_type'])?></span></td>
       <td><?=$v['capacity']?(int)$v['capacity'].' PAX':'—'?></td>
       <td><?=$v['duration_hours']?e((string)$v['duration_hours']).' h':'—'?></td>
       <td><?=(int)$v['rates_count']?></td>
       <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="variant-rates.php?id=<?=(int)$v['id']?>"><i class="bi bi-cash-stack me-1"></i><?=e(t('manage_rates'))?></a></td>
      </tr>
     <?php endforeach; endif;?>
     </tbody>
    </table>
   </div>
  </div>
 </div>
</div>

</main></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body></html>