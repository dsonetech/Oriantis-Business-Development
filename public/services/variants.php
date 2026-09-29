<?php
require_once __DIR__ . '/../../bootstrap.php';

$currentPage = 'services';
$serviceId = (int)($_GET['id'] ?? 0);
if ($serviceId <= 0) { header('Location: index.php'); exit; }

$stmt = db()->prepare("
    SELECT s.*, sc.code AS category_code, sc.name_fr AS category_fr, sc.name_en AS category_en,
           d.name_fr AS destination_fr, d.name_en AS destination_en
    FROM services s
    INNER JOIN service_categories sc ON sc.id = s.category_id
    INNER JOIN destinations d ON d.id = s.destination_id
    WHERE s.id = :id
");
$stmt->execute(['id'=>$serviceId]);
$service=$stmt->fetch();
if(!$service){http_response_code(404);exit('Service not found');}

$pageTitle = t('service_options_rates');

$vstmt=db()->prepare("
    SELECT
        sv.*,
        (
            SELECT r.amount
            FROM service_variant_rates r
            WHERE r.service_variant_id = sv.id AND r.active = 1
            ORDER BY COALESCE(r.valid_from,'1900-01-01') DESC, r.id DESC
            LIMIT 1
        ) AS current_rate,
        (
            SELECT c.code
            FROM service_variant_rates r
            INNER JOIN currencies c ON c.id = r.currency_id
            WHERE r.service_variant_id = sv.id AND r.active = 1
            ORDER BY COALESCE(r.valid_from,'1900-01-01') DESC, r.id DESC
            LIMIT 1
        ) AS currency_code,
        (
            SELECT COUNT(*)
            FROM service_variant_rates r
            WHERE r.service_variant_id = sv.id AND r.active = 1
        ) AS rates_count
    FROM service_variants sv
    WHERE sv.service_id=:id
    ORDER BY
        CASE sv.option_code
            WHEN 'ONE_WAY' THEN 10
            WHEN 'ROUND_TRIP' THEN 20
            WHEN 'WITHOUT_TRANSFER' THEN 30
            WHEN 'WITH_TRANSFER' THEN 40
            WHEN '4H' THEN 50
            WHEN '4H_WITH_GUIDE' THEN 60
            WHEN '8H' THEN 70
            WHEN '8H_WITH_GUIDE' THEN 80
            WHEN 'WITHOUT_DINNER' THEN 90
            WHEN 'WITH_DINNER' THEN 100
            WHEN 'EVISA' THEN 110
            WHEN 'HOURLY' THEN 120
            ELSE 999
        END,
        sv.name_en
");
$vstmt->execute(['id'=>$serviceId]);
$variants=$vstmt->fetchAll();

function option_label(array $v, string $langCode): string {
    if ($langCode === 'fr') {
        return match($v['option_code']) {
            'ONE_WAY' => 'Aller simple',
            'ROUND_TRIP' => 'Aller-retour',
            'WITHOUT_TRANSFER' => 'Sans transfert',
            'WITH_TRANSFER' => 'Avec transfert',
            '4H' => '4 heures',
            '4H_WITH_GUIDE' => '4 heures + guide',
            '8H' => '8 heures',
            '8H_WITH_GUIDE' => '8 heures + guide',
            'WITHOUT_DINNER' => 'Sans dîner',
            'WITH_DINNER' => 'Avec dîner',
            'EVISA' => 'eVisa',
            'HOURLY' => 'Guide à l’heure',
            default => $v['name_fr']
        };
    }
    return match($v['option_code']) {
        'ONE_WAY' => 'One Way',
        'ROUND_TRIP' => 'Round Trip',
        'WITHOUT_TRANSFER' => 'Without Transfer',
        'WITH_TRANSFER' => 'With Transfer',
        '4H' => '4 Hours',
        '4H_WITH_GUIDE' => '4 Hours + Guide',
        '8H' => '8 Hours',
        '8H_WITH_GUIDE' => '8 Hours + Guide',
        'WITHOUT_DINNER' => 'Without Dinner',
        'WITH_DINNER' => 'With Dinner',
        'EVISA' => 'eVisa',
        'HOURLY' => 'Hourly Guide',
        default => $v['name_en']
    };
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
</head>
<body>
<div class="app-shell">
<?php require __DIR__.'/../partials/sidebar.php'; ?>
<main class="main">
<?php require __DIR__.'/../partials/topbar.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <a href="index.php" class="text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i><?=e(t('services'))?>
        </a>
        <h3 class="mt-2 mb-1"><?=e($langCode==='fr'?$service['name_fr']:$service['name_en'])?></h3>
        <div class="text-muted">
            <?=e($langCode==='fr'?$service['category_fr']:$service['category_en'])?>
            · <?=e($langCode==='fr'?$service['destination_fr']:$service['destination_en'])?>
        </div>
    </div>

    <a href="create.php" class="btn btn-outline-primary">
        <i class="bi bi-plus-lg me-2"></i><?=e(t('add_another_service'))?>
    </a>
</div>

<?php if(isset($_GET['saved'])):?><div class="alert alert-success"><?=e(t('variant_saved_success'))?></div><?php endif;?>
<?php if(isset($_GET['deleted'])):?><div class="alert alert-success"><?=e(t('variant_deleted_success'))?></div><?php endif;?>

<div class="content-card">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h5 class="mb-1"><?=e(t('service_options_rates'))?></h5>
            <div class="text-muted small"><?=e(t('service_options_rates_help'))?></div>
        </div>
        <span class="badge text-bg-light border"><?=count($variants)?> <?=e(t('options'))?></span>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th><?=e(t('option'))?></th>
                    <th><?=e(t('capacity'))?></th>
                    <th><?=e(t('duration'))?></th>
                    <th><?=e(t('current_rate'))?></th>
                    <th><?=e(t('rates'))?></th>
                    <th class="text-end"><?=e(t('actions'))?></th>
                </tr>
            </thead>
            <tbody>
            <?php if(!$variants):?>
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-icon mx-auto"><i class="bi bi-sliders"></i></div>
                        <h6 class="mt-3"><?=e(t('no_options'))?></h6>
                        <p class="text-muted mb-0"><?=e(t('no_options_help'))?></p>
                    </td>
                </tr>
            <?php else: foreach($variants as $v):?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?=e(option_label($v,$langCode))?></div>
                        <div class="small text-muted"><?=e($v['option_code'] ?: '—')?></div>
                    </td>
                    <td>
                        <?php if($v['capacity']):?>
                            <?= (int)$v['capacity'] ?> <?=e(t('pax'))?>
                        <?php else:?>
                            —
                        <?php endif;?>
                    </td>
                    <td>
                        <?php if($v['duration_hours']):?>
                            <?= rtrim(rtrim(number_format((float)$v['duration_hours'],1,'.',''),'0'),'.') ?> h
                        <?php else:?>
                            —
                        <?php endif;?>
                    </td>
                    <td class="fw-semibold">
                        <?php if($v['current_rate'] !== null):?>
                            <?=number_format((float)$v['current_rate'],2)?> <?=e($v['currency_code'])?>
                            <?php if($v['pricing_type']==='PER_PAX'):?>
                                <span class="text-muted small">/ PAX</span>
                            <?php elseif($v['pricing_type']==='PER_HOUR'):?>
                                <span class="text-muted small">/ h</span>
                            <?php endif;?>
                        <?php else:?>
                            <span class="text-danger"><?=e(t('no_rate'))?></span>
                        <?php endif;?>
                    </td>
                    <td><?= (int)$v['rates_count'] ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="variant-rates.php?id=<?=(int)$v['id']?>">
                            <i class="bi bi-cash-stack me-1"></i><?=e(t('manage_rates'))?>
                        </a>
                        <form action="delete-variant.php" method="post" class="d-inline" onsubmit="return confirm('<?=e(t('confirm_delete_variant'))?>');">
                            <input type="hidden" name="id" value="<?=(int)$v['id']?>">
                            <input type="hidden" name="service_id" value="<?=$serviceId?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                <i class="bi bi-trash me-1"></i><?=e(t('delete'))?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif;?>
            </tbody>
        </table>
    </div>
</div>

</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>