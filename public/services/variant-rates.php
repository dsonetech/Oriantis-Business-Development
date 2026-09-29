<?php
require_once __DIR__ . '/../../bootstrap.php';
$currentPage='services';
$variantId=(int)($_GET['id']??0);
if($variantId<=0){header('Location:index.php');exit;}
$stmt=db()->prepare("
 SELECT sv.*,s.id service_id,s.name_fr service_fr,s.name_en service_en
 FROM service_variants sv INNER JOIN services s ON s.id=sv.service_id WHERE sv.id=:id
");
$stmt->execute(['id'=>$variantId]);$variant=$stmt->fetch();
if(!$variant){http_response_code(404);exit('Variant not found');}
$pageTitle=t('variant_rates');
$currencies=db()->query("SELECT id,code FROM currencies WHERE active=1 ORDER BY code")->fetchAll();
$rs=db()->prepare("SELECT r.*,c.code currency_code FROM service_variant_rates r INNER JOIN currencies c ON c.id=r.currency_id WHERE r.service_variant_id=:id ORDER BY r.valid_from DESC,r.id DESC");
$rs->execute(['id'=>$variantId]);$rates=$rs->fetchAll();
?>
<!doctype html><html lang="<?=e($langCode)?>"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($pageTitle)?> - Oriantis</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?=base_url('assets/css/app.css')?>" rel="stylesheet"></head>
<body><div class="app-shell"><?php require __DIR__.'/../partials/sidebar.php';?><main class="main"><?php require __DIR__.'/../partials/topbar.php';?>
<div class="mb-4"><a href="variants.php?id=<?=(int)$variant['service_id']?>" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i><?=e(t('service_variants'))?></a>
<h3 class="mt-2 mb-1"><?=e($langCode==='fr'?$variant['name_fr']:$variant['name_en'])?></h3></div>
<?php if(isset($_GET['saved'])):?><div class="alert alert-success"><?=e(t('rate_saved_success'))?></div><?php endif;?>
<?php if(isset($_GET['deleted'])):?><div class="alert alert-success"><?=e(t('rate_deleted_success'))?></div><?php endif;?>
<div class="row g-4">
<div class="col-12 col-xl-4"><div class="content-card">
<h5><?=e(t('add_rate'))?></h5>
<form method="post" action="save-variant-rate.php">
<input type="hidden" name="variant_id" value="<?=$variantId?>">
<div class="mb-3"><label class="form-label"><?=e(t('currency'))?> *</label><select name="currency_id" class="form-select" required>
<?php foreach($currencies as $c):?><option value="<?=(int)$c['id']?>" <?=$c['code']==='QAR'?'selected':''?>><?=e($c['code'])?></option><?php endforeach;?>
</select></div>
<div class="mb-3"><label class="form-label"><?=e(t('purchase_rate'))?> *</label><input type="number" min="0" step=".01" name="amount" class="form-control" required></div>
<div class="row g-3"><div class="col-6"><label class="form-label"><?=e(t('valid_from'))?></label><input type="date" name="valid_from" class="form-control"></div><div class="col-6"><label class="form-label"><?=e(t('valid_to'))?></label><input type="date" name="valid_to" class="form-control"></div></div>
<button class="btn btn-primary w-100 mt-4"><?=e(t('add_rate'))?></button>
</form></div></div>
<div class="col-12 col-xl-8"><div class="content-card"><h5><?=e(t('purchase_rates'))?></h5>
<table class="table align-middle"><thead><tr><th><?=e(t('period'))?></th><th><?=e(t('currency'))?></th><th class="text-end"><?=e(t('purchase_rate'))?></th><th class="text-end"><?=e(t('actions'))?></th></tr></thead><tbody>
<?php if(!$rates):?><tr><td colspan="4" class="text-center text-muted py-5"><?=e(t('no_rates'))?></td></tr>
<?php else:foreach($rates as $r):?><tr><td><?=($r['valid_from']||$r['valid_to'])?e(format_date($r['valid_from'])).' → '.e(format_date($r['valid_to'])):e(t('all_dates'))?></td><td><?=e($r['currency_code'])?></td><td class="text-end fw-semibold"><?=number_format((float)$r['amount'],2)?> <?=e($r['currency_code'])?></td><td class="text-end"><form action="delete-variant-rate.php" method="post" class="d-inline" onsubmit="return confirm('<?=e(t('confirm_delete_rate'))?>');"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><input type="hidden" name="variant_id" value="<?=$variantId?>"><button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash me-1"></i><?=e(t('delete'))?></button></form></td></tr><?php endforeach;endif;?>
</tbody></table></div></div>
</div></main></div></body></html>