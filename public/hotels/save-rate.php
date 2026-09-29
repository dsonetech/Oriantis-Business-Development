<?php

require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$hotelId = (int)($_POST['hotel_id'] ?? 0);
$typeId = (int)($_POST['accommodation_type_id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);
$mealPlan = trim($_POST['meal_plan'] ?? 'BB');
$validFrom = trim($_POST['valid_from'] ?? '');
$validTo = trim($_POST['valid_to'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($hotelId <= 0 || $typeId <= 0 || $amount < 0) {
    http_response_code(422);
    exit('Invalid rate data.');
}

$hotelStmt = db()->prepare("SELECT currency_id FROM hotels WHERE id = :id");
$hotelStmt->execute(['id' => $hotelId]);
$hotel = $hotelStmt->fetch();

if (!$hotel) {
    http_response_code(404);
    exit('Hotel not found.');
}

$stmt = db()->prepare("
    INSERT INTO hotel_rates
        (hotel_id, accommodation_type_id, currency_id, amount, valid_from, valid_to, meal_plan, notes, active)
    VALUES
        (:hotel_id, :accommodation_type_id, :currency_id, :amount, :valid_from, :valid_to, :meal_plan, :notes, 1)
");

$stmt->execute([
    'hotel_id' => $hotelId,
    'accommodation_type_id' => $typeId,
    'currency_id' => $hotel['currency_id'],
    'amount' => $amount,
    'valid_from' => $validFrom !== '' ? $validFrom : null,
    'valid_to' => $validTo !== '' ? $validTo : null,
    'meal_plan' => $mealPlan,
    'notes' => $notes !== '' ? $notes : null,
]);

header('Location: rates.php?id=' . $hotelId . '&rate_saved=1');
exit;
