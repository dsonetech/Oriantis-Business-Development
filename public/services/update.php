<?php
require_once __DIR__ . '/../../bootstrap.php';

if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;}

$id=(int)($_POST['id']??0);
$nameEn=trim($_POST['name_en']??'');
$nameFr=trim($_POST['name_fr']??'');
$destinationId=(int)($_POST['destination_id']??0);
$active=isset($_POST['active'])?1:0;

if($id<=0||$nameEn===''||$nameFr===''||$destinationId<=0){
  http_response_code(422);
  exit('Missing or invalid fields.');
}

$stmt=db()->prepare("
  UPDATE services
  SET name_en=:name_en,
      name_fr=:name_fr,
      destination_id=:destination_id,
      active=:active
  WHERE id=:id
");
$stmt->execute([
  'name_en'=>$nameEn,
  'name_fr'=>$nameFr,
  'destination_id'=>$destinationId,
  'active'=>$active,
  'id'=>$id
]);

header('Location:index.php?updated=1');
exit;
