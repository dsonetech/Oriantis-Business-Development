<?php
require_once __DIR__ . '/../../bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;}
$id=(int)($_POST['id']??0);
if($id<=0){header('Location:index.php');exit;}
$pdo=db();
$pdo->beginTransaction();
try{
    $stmt=$pdo->prepare("SELECT id FROM service_variants WHERE service_id=:id");
    $stmt->execute(['id'=>$id]);
    $variantIds=$stmt->fetchAll(PDO::FETCH_COLUMN);

    if($variantIds){
        $marks=implode(',',array_fill(0,count($variantIds),'?'));
        $pdo->prepare("DELETE FROM service_variant_rates WHERE service_variant_id IN ($marks)")->execute($variantIds);
    }

    $pdo->prepare("DELETE FROM service_variants WHERE service_id=:id")->execute(['id'=>$id]);
    $pdo->prepare("DELETE FROM service_rates WHERE service_id=:id")->execute(['id'=>$id]);
    $pdo->prepare("DELETE FROM services WHERE id=:id")->execute(['id'=>$id]);

    $pdo->commit();
    header('Location:index.php?deleted=1');
    exit;
}catch(Throwable $e){
    $pdo->rollBack();
    http_response_code(500);
    exit($e->getMessage());
}
