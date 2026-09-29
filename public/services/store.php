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
$active = isset($_POST['active']) ? 1 : 0;

if ($nameEn === '' || $nameFr === '' || $categoryId <= 0 || $destinationId <= 0) {
    http_response_code(422);
    exit('Missing or invalid fields.');
}

$stmt = db()->prepare("
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

$serviceId = (int) db()->lastInsertId();

header('Location: rates.php?id=' . $serviceId . '&created=1');
exit;