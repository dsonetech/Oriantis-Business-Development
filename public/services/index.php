<?php
require_once __DIR__ . '/../../bootstrap.php';

$currentPage='services';
$pageTitle=t('services');

$dbError=null;
$services=[];
$categories=[];
$destinations=[];

try{
    $categories=db()->query("SELECT id,code,name_fr,name_en FROM service_categories WHERE active=1 ORDER BY name_en")->fetchAll();
    $destinations=db()->query("SELECT id,name_fr,name_en FROM destinations WHERE active=1 ORDER BY name_en")->fetchAll();

    $stmt=db()->query("
        SELECT
            s.id,
            s.name_fr,
            s.name_en,
            s.active,
            sc.code AS category_code,
            sc.name_fr AS category_fr,
            sc.name_en AS category_en,
            d.name_fr AS destination_fr,
            d.name_en AS destination_en,
            sv.id AS variant_id,
            sv.option_code,
            sv.name_fr AS variant_fr,
            sv.name_en AS variant_en,
            sv.capacity,
            sv.pricing_type,
            r.amount AS current_rate,
            c.code AS currency_code
        FROM services s
        INNER JOIN service_categories sc ON sc.id=s.category_id
        INNER JOIN destinations d ON d.id=s.destination_id
        LEFT JOIN service_variants sv ON sv.service_id=s.id AND sv.active=1
        LEFT JOIN service_variant_rates r ON r.id=(
            SELECT r2.id
            FROM service_variant_rates r2
            WHERE r2.service_variant_id=sv.id AND r2.active=1
            ORDER BY COALESCE(r2.valid_from,'1900-01-01') DESC,r2.id DESC
            LIMIT 1
        )
        LEFT JOIN currencies c ON c.id=r.currency_id
        ORDER BY sc.name_en,s.name_en,sv.id
    ");

    $rows=$stmt->fetchAll();

    foreach($rows as $row){
        $id=(int)$row['id'];

        if(!isset($services[$id])){
            $services[$id]=[
                'id'=>$id,
                'name_fr'=>$row['name_fr'],
                'name_en'=>$row['name_en'],
                'active'=>$row['active'],
                'category_code'=>$row['category_code'],
                'category_fr'=>$row['category_fr'],
                'category_en'=>$row['category_en'],
                'destination_fr'=>$row['destination_fr'],
                'destination_en'=>$row['destination_en'],
                'capacities'=>[],
                'rates'=>[],
                'variants_count'=>0
            ];
        }

        if($row['variant_id']){
            $services[$id]['variants_count']++;

            if($row['capacity']){
                $services[$id]['capacities'][(int)$row['capacity']]=true;
            }

            if($row['current_rate']!==null){
                $label=$langCode==='fr'?$row['variant_fr']:$row['variant_en'];
                $suffix='';
                if($row['pricing_type']==='PER_PAX') $suffix=' / PAX';
                elseif($row['pricing_type']==='PER_HOUR') $suffix=' / h';

                $services[$id]['rates'][]=[
                    'label'=>$label,
                    'amount'=>(float)$row['current_rate'],
                    'currency'=>$row['currency_code'] ?: '',
                    'suffix'=>$suffix
                ];
            }
        }
    }

    $services=array_values($services);
}catch(Throwable $e){
    $dbError=$e->getMessage();
}
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
<style>
.filter-bar{background:#f8f9fa;border:1px solid #e8ecf2;border-radius:12px;padding:14px}
.rate-list{min-width:190px}
.rate-line{display:flex;justify-content:space-between;gap:12px;font-size:.875rem;padding:2px 0}
.rate-line .rate-label{color:#6c757d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:125px}
.capacity-badge{display:inline-block;margin:2px 4px 2px 0}
</style>
</head>
<body>
<div class="app-shell">
<?php require __DIR__.'/../partials/sidebar.php';?>
<main class="main">
<?php require __DIR__.'/../partials/topbar.php';?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="mb-1"><?=e(t('service_management'))?></h4>
    <p class="text-muted mb-0"><?=e(t('service_management_help'))?></p>
  </div>
  <a href="create.php" class="btn btn-primary">
    <i class="bi bi-plus-lg me-2"></i><?=e(t('add_service'))?>
  </a>
</div>

<?php if($dbError):?><div class="alert alert-danger"><?=e($dbError)?></div><?php endif;?>
<?php if(isset($_GET['created'])):?><div class="alert alert-success"><?=e(t('service_created_success'))?></div><?php endif;?>
<?php if(isset($_GET['deleted'])):?><div class="alert alert-success"><?=e(t('service_deleted_success'))?></div><?php endif;?>

<div class="filter-bar mb-3">
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-4">
      <label class="form-label small mb-1"><?=e(t('search'))?></label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input id="serviceSearch" class="form-control" placeholder="<?=e(t('search_service_placeholder'))?>">
      </div>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label small mb-1"><?=e(t('category'))?></label>
      <select id="categoryFilter" class="form-select">
        <option value=""><?=e(t('all_categories'))?></option>
        <?php foreach($categories as $c):?>
        <option value="<?=e($c['code'])?>"><?=e($langCode==='fr'?$c['name_fr']:$c['name_en'])?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label small mb-1"><?=e(t('destination'))?></label>
      <select id="destinationFilter" class="form-select">
        <option value=""><?=e(t('all_destinations'))?></option>
        <?php foreach($destinations as $d):?>
        <option value="<?=e($langCode==='fr'?$d['name_fr']:$d['name_en'])?>"><?=e($langCode==='fr'?$d['name_fr']:$d['name_en'])?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div class="col-12 col-md-2">
      <button id="resetFilters" class="btn btn-light border w-100" type="button">
        <i class="bi bi-arrow-counterclockwise me-1"></i><?=e(t('reset_filters'))?>
      </button>
    </div>
  </div>
</div>

<div class="content-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0" id="servicesTable">
      <thead>
        <tr>
          <th><?=e(t('category'))?></th>
          <th><?=e(t('service_name'))?></th>
          <th><?=e(t('capacity'))?></th>
          <th><?=e(t('service_rate'))?></th>
          <th><?=e(t('variants'))?></th>
          <th><?=e(t('destination'))?></th>
          <th class="text-end"><?=e(t('actions'))?></th>
        </tr>
      </thead>
      <tbody>
      <?php if(!$services):?>
        <tr class="empty-row">
          <td colspan="7">
            <div class="empty-state py-5">
              <div class="empty-icon"><i class="bi bi-briefcase"></i></div>
              <h6><?=e(t('no_services'))?></h6>
              <p><?=e(t('no_services_help'))?></p>
              <a href="create.php" class="btn btn-primary btn-sm"><?=e(t('add_service'))?></a>
            </div>
          </td>
        </tr>
      <?php else:foreach($services as $service):?>
        <?php
          $categoryName=$langCode==='fr'?$service['category_fr']:$service['category_en'];
          $serviceName=$langCode==='fr'?$service['name_fr']:$service['name_en'];
          $destinationName=$langCode==='fr'?$service['destination_fr']:$service['destination_en'];
          $capacityValues=array_keys($service['capacities']);
          sort($capacityValues,SORT_NUMERIC);
        ?>
        <tr class="service-row"
            data-category="<?=e($service['category_code'])?>"
            data-destination="<?=e(mb_strtolower($destinationName))?>"
            data-search="<?=e(mb_strtolower($categoryName.' '.$serviceName.' '.$destinationName))?>">
          <td>
            <span class="badge rounded-pill text-bg-light border"><?=e($categoryName)?></span>
          </td>
          <td>
            <div class="fw-semibold"><?=e($serviceName)?></div>
            <div class="text-muted small">#<?=(int)$service['id']?></div>
          </td>
          <td>
            <?php if($capacityValues):?>
              <?php foreach($capacityValues as $cap):?>
                <span class="badge text-bg-light border capacity-badge"><?= (int)$cap ?> PAX</span>
              <?php endforeach;?>
            <?php else:?>
              <span class="text-muted">—</span>
            <?php endif;?>
          </td>
          <td>
            <div class="rate-list">
              <?php if($service['rates']):?>
                <?php foreach($service['rates'] as $rate):?>
                  <div class="rate-line">
                    <span class="rate-label" title="<?=e($rate['label'])?>"><?=e($rate['label'])?></span>
                    <strong><?=number_format($rate['amount'],2)?> <?=e($rate['currency'])?><?=e($rate['suffix'])?></strong>
                  </div>
                <?php endforeach;?>
              <?php else:?>
                <span class="text-muted"><?=e(t('no_rate'))?></span>
              <?php endif;?>
            </div>
          </td>
          <td><span class="badge text-bg-light border"><?=(int)$service['variants_count']?></span></td>
          <td><?=e($destinationName)?></td>
          <td class="text-end text-nowrap">
            <a href="variants.php?id=<?=(int)$service['id']?>" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-sliders me-1"></i><?=e(t('options_rates'))?>
            </a>
            <form action="delete.php" method="post" class="d-inline" onsubmit="return confirm('<?=e(t('confirm_delete_service'))?>');">
              <input type="hidden" name="id" value="<?=(int)$service['id']?>">
              <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash me-1"></i><?=e(t('delete'))?>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach;endif;?>
      <tr id="noFilterResults" style="display:none">
        <td colspan="7" class="text-center text-muted py-5">
          <i class="bi bi-funnel fs-3 d-block mb-2"></i>
          <?=e(t('no_filter_results'))?>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</div>

</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
const searchInput=document.getElementById('serviceSearch');
const categoryFilter=document.getElementById('categoryFilter');
const destinationFilter=document.getElementById('destinationFilter');
const resetFilters=document.getElementById('resetFilters');
const rows=[...document.querySelectorAll('.service-row')];
const noResults=document.getElementById('noFilterResults');

function applyFilters(){
  const q=searchInput.value.trim().toLowerCase();
  const category=categoryFilter.value;
  const destination=destinationFilter.value.trim().toLowerCase();
  let visible=0;

  rows.forEach(row=>{
    const matchesSearch=!q || row.dataset.search.includes(q);
    const matchesCategory=!category || row.dataset.category===category;
    const matchesDestination=!destination || row.dataset.destination===destination;
    const show=matchesSearch&&matchesCategory&&matchesDestination;
    row.style.display=show?'':'none';
    if(show)visible++;
  });

  noResults.style.display=(rows.length && visible===0)?'':'none';
}

searchInput.addEventListener('input',applyFilters);
categoryFilter.addEventListener('change',applyFilters);
destinationFilter.addEventListener('change',applyFilters);
resetFilters.addEventListener('click',()=>{
  searchInput.value='';
  categoryFilter.value='';
  destinationFilter.value='';
  applyFilters();
});
</script>
</body>
</html>