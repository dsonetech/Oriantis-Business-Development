<?php
require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$nameEn = trim($_POST['name_en'] ?? '');
$nameFr = trim($_POST['name_fr'] ?? '');
$categoryId = (int)($_POST['category_id'] ?? 0);
$destinationId = (int)($_POST['destination_id'] ?? 0);
$currencyId = (int)($_POST['currency_id'] ?? 0);
$active = isset($_POST['active']) ? 1 : 0;

if ($nameEn === '' || $nameFr === '' || $categoryId <= 0 || $destinationId <= 0 || $currencyId <= 0) {
    http_response_code(422);
    exit('Missing or invalid fields.');
}

$catStmt = db()->prepare("SELECT code FROM service_categories WHERE id=:id");
$catStmt->execute(['id'=>$categoryId]);
$categoryCode = $catStmt->fetchColumn();

if (!$categoryCode) {
    http_response_code(422);
    exit('Invalid category.');
}

$pdo = db();
$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("
        INSERT INTO services
            (destination_id, category_id, name_fr, name_en, pricing_type, capacity, duration_hours, active)
        VALUES
            (:destination_id, :category_id, :name_fr, :name_en, 'PER_UNIT', NULL, NULL, :active)
    ");

    $stmt->execute([
        'destination_id' => $destinationId,
        'category_id' => $categoryId,
        'name_fr' => $nameFr,
        'name_en' => $nameEn,
        'active' => $active,
    ]);

    $serviceId = (int)$pdo->lastInsertId();

    $variantStmt = $pdo->prepare("
        INSERT INTO service_variants
            (service_id, option_code, name_fr, name_en, pricing_type, capacity, duration_hours, active)
        VALUES
            (:service_id, :option_code, :name_fr, :name_en, :pricing_type, :capacity, :duration_hours, 1)
    ");

    $rateStmt = $pdo->prepare("
        INSERT INTO service_variant_rates
            (service_variant_id, currency_id, amount, active)
        VALUES
            (:variant_id, :currency_id, :amount, 1)
    ");

    $addVariant = function(
        string $code,
        string $variantFr,
        string $variantEn,
        string $pricingType,
        ?int $capacity,
        ?float $duration,
        ?float $price
    ) use ($variantStmt, $rateStmt, $serviceId, $currencyId): void {
        if ($price === null) {
            return;
        }

        $variantStmt->execute([
            'service_id'=>$serviceId,
            'option_code'=>$code,
            'name_fr'=>$variantFr,
            'name_en'=>$variantEn,
            'pricing_type'=>$pricingType,
            'capacity'=>$capacity,
            'duration_hours'=>$duration
        ]);

        $variantId=(int)db()->lastInsertId();

        $rateStmt->execute([
            'variant_id'=>$variantId,
            'currency_id'=>$currencyId,
            'amount'=>$price
        ]);
    };

    $floatOrNull = static function(string $key): ?float {
        $value=trim((string)($_POST[$key] ?? ''));
        return $value==='' ? null : (float)$value;
    };

    switch ($categoryCode) {
        case 'TRANSFER':
            $capacity=(int)($_POST['transfer_capacity'] ?? 0);
            if ($capacity <= 0) throw new RuntimeException('Transfer capacity is required.');
            $addVariant('ONE_WAY', 'Aller simple', 'One Way', 'PER_UNIT', $capacity, null, $floatOrNull('one_way_price'));
            $addVariant('ROUND_TRIP', 'Aller-retour', 'Round Trip', 'PER_UNIT', $capacity, null, $floatOrNull('round_trip_price'));
            break;

        case 'BOAT':
            $capacity=(int)($_POST['boat_capacity'] ?? 0);
            $price=$floatOrNull('boat_price');
            if ($capacity <= 0 || $price === null) throw new RuntimeException('Boat capacity and price are required.');
            $withTransfer=(int)($_POST['boat_with_transfer'] ?? 0) === 1;
            $addVariant(
                $withTransfer ? 'WITH_TRANSFER' : 'WITHOUT_TRANSFER',
                $withTransfer ? 'Avec transfert' : 'Sans transfert',
                $withTransfer ? 'With Transfer' : 'Without Transfer',
                'PER_UNIT',
                $capacity,
                null,
                $price
            );
            break;

        case 'CITY_TOUR':
            $capacity=(int)($_POST['city_capacity'] ?? 0);
            if ($capacity <= 0) throw new RuntimeException('City tour capacity is required.');
            $p4=$floatOrNull('city_4h_price');
            $p8=$floatOrNull('city_8h_price');
            $addVariant('4H', '4 heures', '4 Hours', 'PER_UNIT', $capacity, 4, $p4);
            $addVariant('8H', '8 heures', '8 Hours', 'PER_UNIT', $capacity, 8, $p8);

            $guideEnabled=(int)($_POST['city_guide'] ?? 0)===1;
            $guideRate=$floatOrNull('guide_hour_rate');

            if ($guideEnabled && $guideRate !== null) {
                if ($p4 !== null) $addVariant('4H_WITH_GUIDE', '4 heures + guide', '4 Hours + Guide', 'PER_UNIT', $capacity, 4, $p4 + ($guideRate*4));
                if ($p8 !== null) $addVariant('8H_WITH_GUIDE', '8 heures + guide', '8 Hours + Guide', 'PER_UNIT', $capacity, 8, $p8 + ($guideRate*8));
            }
            break;

        case 'SAFARI':
            $capacity=(int)($_POST['safari_capacity'] ?? 0);
            if ($capacity <= 0) throw new RuntimeException('Safari capacity is required.');
            $addVariant('WITHOUT_DINNER', 'Sans dîner', 'Without Dinner', 'PER_UNIT', $capacity, 4, $floatOrNull('safari_no_dinner_price'));
            $addVariant('WITH_DINNER', 'Avec dîner', 'With Dinner', 'PER_UNIT', $capacity, 4, $floatOrNull('safari_dinner_price'));
            break;

        case 'SAFARI_LUXE':
            $capacity=(int)($_POST['safari_luxe_capacity'] ?? 0);
            if ($capacity <= 0) throw new RuntimeException('Safari Luxe capacity is required.');
            $addVariant('WITHOUT_DINNER', 'Sans dîner', 'Without Dinner', 'PER_UNIT', $capacity, 4, $floatOrNull('safari_luxe_no_dinner_price'));
            $addVariant('WITH_DINNER', 'Avec dîner', 'With Dinner', 'PER_UNIT', $capacity, 4, $floatOrNull('safari_luxe_dinner_price'));
            break;

        case 'VISA':
            $price=$floatOrNull('visa_price');
            if ($price === null) throw new RuntimeException('Visa price is required.');
            $addVariant('EVISA', 'eVisa', 'eVisa', 'PER_PAX', null, null, $price);
            break;

        case 'GUIDE':
            $price=$floatOrNull('guide_price');
            if ($price === null) throw new RuntimeException('Guide hourly price is required.');
            $addVariant('HOURLY', 'Guide à l’heure', 'Hourly Guide', 'PER_HOUR', null, 1, $price);
            break;

        default:
            $price=$floatOrNull('other_price');
            if ($price === null) throw new RuntimeException('Price is required.');
            $pricing=$_POST['other_pricing_type'] ?? 'PER_UNIT';
            if (!in_array($pricing,['PER_UNIT','PER_PAX','PER_GROUP','PER_HOUR'],true)) $pricing='PER_UNIT';
            $capacity=trim((string)($_POST['other_capacity'] ?? ''))!=='' ? (int)$_POST['other_capacity'] : null;
            $addVariant('DEFAULT', $nameFr, $nameEn, $pricing, $capacity, null, $price);
            break;
    }

    $pdo->commit();
    header('Location: index.php?created=1');
    exit;
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(422);
    exit($e->getMessage());
}
