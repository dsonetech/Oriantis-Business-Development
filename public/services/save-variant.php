<?php
require_once __DIR__ . '/../../bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;}
$serviceId=(int)($_POST['service_id']??0);
$nameEn=trim($_POST['name_en']??'');
$nameFr=trim($_POST['name_fr']??'');
$optionCode=trim($_POST['option_code']??'');
$pricing=$_POST['pricing_type']??'PER_UNIT';
$capacity=trim($_POST['capacity']??'')!==''?(int)$_POST['capacity']:null;
$duration=trim($_POST['duration_hours']??'')!==''?(float)$_POST['duration_hours']:null;
$allowed=['PER_PAX','PER_UNIT','PER_GROUP','PER_HOUR'];
if($serviceId<=0||$nameEn===''||$nameFr===''||!in_array($pricing,$allowed,true)){http_response_code(422);exit('Invalid data');}
$stmt=db()->prepare("INSERT INTO service_variants(service_id,option_code,name_fr,name_en,pricing_type,capacity,duration_hours,active) VALUES(:service_id,:option_code,:name_fr,:name_en,:pricing_type,:capacity,:duration_hours,1)");
$stmt->execute([
 'service_id'=>$serviceId,'option_code'=>$optionCode!==''?$optionCode:null,'name_fr'=>$nameFr,'name_en'=>$nameEn,
 'pricing_type'=>$pricing,'capacity'=>$capacity,'duration_hours'=>$duration
]);
header('Location:variants.php?id='.$serviceId.'&saved=1');exit;