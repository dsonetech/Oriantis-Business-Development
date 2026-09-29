<?php

require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$destinationId = (int)($_POST['destination_id'] ?? 0);
$currencyId = (int)($_POST['currency_id'] ?? 0);
$stars = ($_POST['stars'] ?? '') !== '' ? (int)$_POST['stars'] : null;
$address = trim($_POST['address'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$active = isset($_POST['active']) ? 1 : 0;

if ($name === '' || $destinationId <= 0 || $currencyId <= 0) {
    http_response_code(422);
    exit('Missing required fields.');
}

$stmt = db()->prepare("
    INSERT INTO hotels
        (destination_id, name, stars, currency_id, address, notes, active)
    VALUES
        (:destination_id, :name, :stars, :currency_id, :address, :notes, :active)
");

$stmt->execute([
    'destination_id' => $destinationId,
    'name' => $name,
    'stars' => $stars,
    'currency_id' => $currencyId,
    'address' => $address !== '' ? $address : null,
    'notes' => $notes !== '' ? $notes : null,
    'active' => $active,
]);

$hotelId = (int) db()->lastInsertId();

header('Location: rates.php?id=' . $hotelId . '&created=1');
exit;
