<?php
require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$hotelId = (int)($_POST['id'] ?? 0);

if ($hotelId <= 0) {
    header('Location: index.php?delete_error=1');
    exit;
}

$pdo = db();

try {
    $quoteStmt = $pdo->prepare("SELECT COUNT(*) FROM quotes WHERE hotel_id = :hotel_id");
    $quoteStmt->execute(['hotel_id' => $hotelId]);
    $quoteCount = (int)$quoteStmt->fetchColumn();

    if ($quoteCount > 0) {
        header('Location: index.php?delete_error=1');
        exit;
    }

    $pdo->beginTransaction();

    $pdo->prepare("DELETE FROM hotel_rates WHERE hotel_id = :hotel_id")
        ->execute(['hotel_id' => $hotelId]);

    $pdo->prepare("DELETE FROM hotels WHERE id = :hotel_id")
        ->execute(['hotel_id' => $hotelId]);

    $pdo->commit();

    header('Location: index.php?deleted=1');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: index.php?delete_error=1');
    exit;
}
