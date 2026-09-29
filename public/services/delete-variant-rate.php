<?php
require_once __DIR__ . '/../../bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;}
$id=(int)($_POST['id']??0);
$variantId=(int)($_POST['variant_id']??0);
if($id<=0||$variantId<=0){header('Location:index.php');exit;}
$stmt=db()->prepare("DELETE FROM service_variant_rates WHERE id=:id AND service_variant_id=:variant_id");
$stmt->execute(['id'=>$id,'variant_id'=>$variantId]);
header('Location:variant-rates.php?id='.$variantId.'&deleted=1');
exit;
