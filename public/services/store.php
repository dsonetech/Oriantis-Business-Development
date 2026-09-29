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
$pricingType = $_POST['pricing_type'] ?? 'PER_UNIT';
$capacity = trim($_POST['capacity'] ?? '') !== '' ? (int)$_POST['capacity'] : null;
$duration = trim($_POST['duration_hours'] ?? '') !== '' ? (float)$_POST['duration_hours'] : null;
$active = isset($_POST['active']) ? 1 : 0;

$allowedPricing = ['PER_PAX','PER_UNIT','PER_GROUP'];

if ($nameEn === '' || $nameFr === '' || $categoryId <= 0 || $destinationId <= 0 || !in_array($pricingType, $allowedPricing, true)) {
    http_response_code(422);
    exit('Missing or invalid fields.');
}

$stmt = db()->prepare("
    INSERT INTO services
        (destination_id, category_id, name_fr, name_en, pricing_type, capacity, duration_hours, active)
    VALUES
        (:destination_id, :category_id, :name_fr, :name_en, :pricing_type, :capacity, :duration_hours, :active)
");

$stmt->execute([
    'destination_id' => $destinationId,
    'category_id' => $categoryId,
    'name_fr' => $nameFr,
    'name_en' => $nameEn,
    'pricing_type' => $pricingType,
    'capacity' => $capacity,
    'duration_hours' => $duration,
    'active' => $active,
]);

$serviceId = (int) db()->lastInsertId();

header('Location: rates.php?id=' . $serviceId . '&created=1');
exit;