<?php
require_once __DIR__ . '/../../bootstrap.php';

if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;}

$nameEn=trim($_POST['name_en']??'');
$nameFr=trim($_POST['name_fr']??'');
$categoryId=(int)($_POST['category_id']??0);
$destinationId=(int)($_POST['destination_id']??0);
$currencyId=(int)($_POST['currency_id']??0);
$active=isset($_POST['active'])?1:0;

if($nameEn===''||$nameFr===''||$categoryId<=0||$destinationId<=0||$currencyId<=0){
  http_response_code(422);exit('Missing or invalid fields.');
}

$cat=db()->prepare("SELECT code FROM service_categories WHERE id=:id");
$cat->execute(['id'=>$categoryId]);
$categoryCode=$cat->fetchColumn();
if(!$categoryCode){http_response_code(422);exit('Invalid category.');}

$pdo=db();
$pdo->beginTransaction();

try{
  $s=$pdo->prepare("INSERT INTO services(destination_id,category_id,name_fr,name_en,pricing_type,capacity,duration_hours,active) VALUES(:destination_id,:category_id,:name_fr,:name_en,'PER_UNIT',NULL,NULL,:active)");
  $s->execute(['destination_id'=>$destinationId,'category_id'=>$categoryId,'name_fr'=>$nameFr,'name_en'=>$nameEn,'active'=>$active]);
  $serviceId=(int)$pdo->lastInsertId();

  $v=$pdo->prepare("INSERT INTO service_variants(service_id,option_code,name_fr,name_en,pricing_type,capacity,duration_hours,active) VALUES(:service_id,:option_code,:name_fr,:name_en,:pricing_type,:capacity,:duration_hours,1)");
  $r=$pdo->prepare("INSERT INTO service_variant_rates(service_variant_id,currency_id,amount,active) VALUES(:variant_id,:currency_id,:amount,1)");

  $num=static function(string $key):?float{
    $x=trim((string)($_POST[$key]??''));
    return $x===''?null:(float)$x;
  };

  $add=function(string $code,string $fr,string $en,string $pricing,?int $capacity,?float $duration,?float $price)use($v,$r,$serviceId,$currencyId){
    if($price===null)return;
    $v->execute(['service_id'=>$serviceId,'option_code'=>$code,'name_fr'=>$fr,'name_en'=>$en,'pricing_type'=>$pricing,'capacity'=>$capacity,'duration_hours'=>$duration]);
    $variantId=(int)db()->lastInsertId();
    $r->execute(['variant_id'=>$variantId,'currency_id'=>$currencyId,'amount'=>$price]);
  };

  switch($categoryCode){
    case 'VEHICLE_RENTAL':
      $capacity=(int)($_POST['rental_capacity']??0);
      if($capacity<=0)throw new RuntimeException('Vehicle capacity is required.');
      $add('HALF_DAY','Demi-journée 4-5 heures','Half Day 4-5 Hours','PER_UNIT',$capacity,4.5,$num('half_day_rate'));
      $add('FULL_DAY','Journée complète 8-9 heures','Full Day 8-9 Hours','PER_UNIT',$capacity,8.5,$num('full_day_rate'));
      break;

    case 'TRANSFER':
      $capacity=(int)($_POST['transfer_capacity']??0);
      if($capacity<=0)throw new RuntimeException('Transfer capacity is required.');
      $add('ONE_WAY','Aller simple','One Way','PER_UNIT',$capacity,null,$num('one_way_price'));
      $add('ROUND_TRIP','Aller-retour','Round Trip','PER_UNIT',$capacity,null,$num('round_trip_price'));
      break;

    case 'SAFARI':
      $capacity=(int)($_POST['safari_capacity']??0);
      $car=$num('safari_vehicle_rate');
      if($capacity<=0||$car===null)throw new RuntimeException('Safari capacity and vehicle rate are required.');
      $add('SAFARI_CAR','Véhicule Safari','Safari Vehicle','PER_UNIT',$capacity,null,$car);
      $add('DINNER','Dîner','Dinner','PER_PAX',null,null,$num('safari_dinner_rate'));
      break;

    case 'SAFARI_LUXE':
      $capacity=(int)($_POST['safari_luxe_capacity']??0);
      $car=$num('safari_luxe_vehicle_rate');
      if($capacity<=0||$car===null)throw new RuntimeException('Safari Luxe capacity and vehicle rate are required.');
      $add('SAFARI_CAR','Véhicule Safari Luxe','Safari Luxe Vehicle','PER_UNIT',$capacity,null,$car);
      $add('DINNER','Dîner','Dinner','PER_PAX',null,null,$num('safari_luxe_dinner_rate'));
      break;

    case 'BOAT':
      $capacity=(int)($_POST['boat_capacity']??0);
      $boat4=$num('boat_4h_rate');
      $boat7=$num('boat_7h_rate');
      if($capacity<=0||($boat4===null&&$boat7===null))throw new RuntimeException('Boat capacity and at least one cruise rate are required.');
      $add('BOAT_4H','Dhow Cruise 4 heures','Dhow Cruise 4 Hours','PER_UNIT',$capacity,4,$boat4);
      $add('BOAT_7H','Dhow Cruise 7 heures','Dhow Cruise 7 Hours','PER_UNIT',$capacity,7,$boat7);
      $add('DINNER','Dîner','Dinner','PER_PAX',null,null,$num('boat_dinner_rate'));
      break;

    case 'CITY_TOUR':
      $capacity=(int)($_POST['city_capacity']??0);
      if($capacity<=0)throw new RuntimeException('City Tour capacity is required.');
      $add('4H','4 heures','4 Hours','PER_UNIT',$capacity,4,$num('city_4h_price'));
      $add('8H','8 heures','8 Hours','PER_UNIT',$capacity,8,$num('city_8h_price'));
      $add('GUIDE_HOURLY','Guide à l’heure','Hourly Guide','PER_HOUR',null,1,$num('city_guide_hour_rate'));
      break;

    case 'MEETING_ROOM':
      $capacity=(int)($_POST['meeting_capacity']??0);
      if($capacity<=0)throw new RuntimeException('Meeting room capacity is required.');
      $add('HALF_DAY','Demi-journée','Half Day','PER_GROUP',$capacity,4,$num('meeting_half_rate'));
      $add('FULL_DAY','Journée complète','Full Day','PER_GROUP',$capacity,8,$num('meeting_full_rate'));
      break;

    case 'CONFERENCE_ROOM':
      $capacity=(int)($_POST['conference_capacity']??0);
      if($capacity<=0)throw new RuntimeException('Conference room capacity is required.');
      $add('HALF_DAY','Demi-journée','Half Day','PER_GROUP',$capacity,4,$num('conference_half_rate'));
      $add('FULL_DAY','Journée complète','Full Day','PER_GROUP',$capacity,8,$num('conference_full_rate'));
      break;

    case 'ACTIVITY':
      $price=$num('activity_rate');
      if($price===null)throw new RuntimeException('Activity rate is required.');
      $pricing=$_POST['activity_pricing']??'PER_PAX';
      if(!in_array($pricing,['PER_PAX','PER_GROUP'],true))$pricing='PER_PAX';
      $capacity=trim((string)($_POST['activity_capacity']??''))!==''?(int)$_POST['activity_capacity']:null;
      $add('DEFAULT',$nameFr,$nameEn,$pricing,$capacity,null,$price);
      break;

    case 'VISA':
      $price=$num('visa_price');
      if($price===null)throw new RuntimeException('Visa price is required.');
      $add('EVISA','eVisa','eVisa','PER_PAX',null,null,$price);
      break;

    case 'GUIDE':
      $price=$num('guide_price');
      if($price===null)throw new RuntimeException('Guide hourly rate is required.');
      $add('HOURLY','Guide à l’heure','Hourly Guide','PER_HOUR',null,1,$price);
      break;

    default:
      $price=$num('other_price');
      if($price===null)throw new RuntimeException('Price is required.');
      $pricing=$_POST['other_pricing_type']??'PER_UNIT';
      if(!in_array($pricing,['PER_UNIT','PER_PAX','PER_GROUP','PER_HOUR'],true))$pricing='PER_UNIT';
      $capacity=trim((string)($_POST['other_capacity']??''))!==''?(int)$_POST['other_capacity']:null;
      $add('DEFAULT',$nameFr,$nameEn,$pricing,$capacity,null,$price);
      break;
  }

  $pdo->commit();
  header('Location:variants.php?id='.$serviceId.'&created=1');
  exit;
}catch(Throwable $e){
  $pdo->rollBack();
  http_response_code(422);
  exit($e->getMessage());
}
