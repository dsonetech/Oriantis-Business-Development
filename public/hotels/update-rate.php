<?php

require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$rateId = (int)($_POST['rate_id'] ?? 0);
$hotelId = (int)($_POST['hotel_id'] ?? 0);
$typeId = (int)($_POST['accommodation_type_id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);
$mealPlan = trim($_POST['meal_plan'] ?? 'BB');
$validFrom = trim($_POST['valid_from'] ?? '');
$validTo = trim($_POST['valid_to'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($rateId <= 0 || $hotelId <= 0 || $typeId <= 0 || $amount < 0) {
    http_response_code(422);
    exit('Invalid rate data.');
}

$stmt = db()->prepare("
    UPDATE hotel_rates
    SET accommodation_type_id = :accommodation_type_id,
        amount = :amount,
        meal_plan = :meal_plan,
        valid_from = :valid_from,
        valid_to = :valid_to,
        notes = :notes
    WHERE id = :id AND hotel_id = :hotel_id
");

$stmt->execute([
    'accommodation_type_id' => $typeId,
    'amount' => $amount,
    'meal_plan' => $mealPlan,
    'valid_from' => $validFrom !== '' ? $validFrom : null,
    'valid_to' => $validTo !== '' ? $validTo : null,
    'notes' => $notes !== '' ? $notes : null,
    'id' => $rateId,
    'hotel_id' => $hotelId,
]);

header('Location: rates.php?id=' . $hotelId . '&rate_updated=1');
exit;
