<?php
require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$serviceId = (int)($_POST['service_id'] ?? 0);
$currencyId = (int)($_POST['currency_id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);
$validFrom = trim($_POST['valid_from'] ?? '');
$validTo = trim($_POST['valid_to'] ?? '');

if ($serviceId <= 0 || $currencyId <= 0 || $amount < 0) {
    http_response_code(422);
    exit('Invalid service rate.');
}

$stmt = db()->prepare("
    INSERT INTO service_rates
        (service_id, currency_id, amount, valid_from, valid_to, active)
    VALUES
        (:service_id, :currency_id, :amount, :valid_from, :valid_to, 1)
");

$stmt->execute([
    'service_id' => $serviceId,
    'currency_id' => $currencyId,
    'amount' => $amount,
    'valid_from' => $validFrom !== '' ? $validFrom : null,
    'valid_to' => $validTo !== '' ? $validTo : null,
]);

header('Location: rates.php?id=' . $serviceId . '&rate_saved=1');
exit;