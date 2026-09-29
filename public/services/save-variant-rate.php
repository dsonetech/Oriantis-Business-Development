<?php
require_once __DIR__ . '/../../bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;}
$id=(int)($_POST['variant_id']??0);$currency=(int)($_POST['currency_id']??0);$amount=(float)($_POST['amount']??0);
$from=trim($_POST['valid_from']??'');$to=trim($_POST['valid_to']??'');
if($id<=0||$currency<=0||$amount<0){http_response_code(422);exit('Invalid rate');}
$stmt=db()->prepare("INSERT INTO service_variant_rates(service_variant_id,currency_id,amount,valid_from,valid_to,active) VALUES(:id,:currency,:amount,:fromd,:tod,1)");
$stmt->execute(['id'=>$id,'currency'=>$currency,'amount'=>$amount,'fromd'=>$from!==''?$from:null,'tod'=>$to!==''?$to:null]);
header('Location:variant-rates.php?id='.$id.'&saved=1');exit;