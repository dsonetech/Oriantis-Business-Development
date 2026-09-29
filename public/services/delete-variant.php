<?php
require_once __DIR__ . '/../../bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;}
$id=(int)($_POST['id']??0);
$serviceId=(int)($_POST['service_id']??0);
if($id<=0||$serviceId<=0){header('Location:index.php');exit;}
$pdo=db();
$pdo->beginTransaction();
try{
    $pdo->prepare("DELETE FROM service_variant_rates WHERE service_variant_id=:id")->execute(['id'=>$id]);
    $pdo->prepare("DELETE FROM service_variants WHERE id=:id AND service_id=:service_id")->execute(['id'=>$id,'service_id'=>$serviceId]);
    $pdo->commit();
    header('Location:variants.php?id='.$serviceId.'&deleted=1');
    exit;
}catch(Throwable $e){
    $pdo->rollBack();
    http_response_code(500);
    exit($e->getMessage());
}
