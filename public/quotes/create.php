<?php
require_once __DIR__ . '/../../bootstrap.php';
$currentPage='new_quote';
$pageTitle=t('new_quote');

$hotels=db()->query("
    SELECT h.id,h.name,c.code currency_code
    FROM hotels h
    INNER JOIN currencies c ON c.id=h.currency_id
    WHERE h.active=1
    ORDER BY h.name
")->fetchAll();

$hotelRates=db()->query("
    SELECT
        hr.hotel_id,hr.accommodation_type_id,hr.amount,hr.valid_from,hr.valid_to,
        at.code,at.divisor,at.name_fr,at.name_en,
        c.code currency_code
    FROM hotel_rates hr
    INNER JOIN accommodation_types at ON at.id=hr.accommodation_type_id
    INNER JOIN currencies c ON c.id=hr.currency_id
    WHERE hr.active=1
    ORDER BY at.sort_order,hr.valid_from,hr.id
")->fetchAll();

$variants=db()->query("
    SELECT
        sv.id,sv.service_id,sv.name_fr,sv.name_en,sv.option_code,sv.pricing_type,sv.capacity,sv.duration_hours,
        s.name_fr service_fr,s.name_en service_en,
        sc.code category_code,sc.name_fr category_fr,sc.name_en category_en,
        r.amount,r.valid_from AS rate_valid_from,r.valid_to AS rate_valid_to,c.code currency_code
    FROM service_variants sv
    INNER JOIN services s ON s.id=sv.service_id
    INNER JOIN service_categories sc ON sc.id=s.category_id
    LEFT JOIN service_variant_rates r ON r.id=(
        SELECT r2.id
        FROM service_variant_rates r2
        WHERE r2.service_variant_id=sv.id AND r2.active=1
        ORDER BY COALESCE(r2.valid_from,'1900-01-01') DESC,r2.id DESC
        LIMIT 1
    )
    LEFT JOIN currencies c ON c.id=r.currency_id
    WHERE sv.active=1 AND s.active=1
      AND sc.code IN ('VISA','TRANSFER','SAFARI','SAFARI_LUXE','BOAT','CITY_TOUR','GUIDE','VEHICLE_RENTAL')
    ORDER BY sc.code,sv.capacity,sv.name_en
")->fetchAll();

$currencies=db()->query("SELECT id,code FROM currencies WHERE active=1 ORDER BY code")->fetchAll();
$fxRows=db()->query("
    SELECT b.code base_code,t.code target_code,er.rate
    FROM exchange_rates er
    INNER JOIN currencies b ON b.id=er.base_currency_id
    INNER JOIN currencies t ON t.id=er.target_currency_id
    WHERE er.active=1
")->fetchAll();

$roomTypes=db()->query("
    SELECT id,code,name_fr,name_en,divisor
    FROM accommodation_types
    WHERE active=1
    ORDER BY sort_order
")->fetchAll();
?>
<!doctype html>
<html lang="<?=e($langCode)?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($pageTitle)?> - Oriantis</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?=base_url('assets/css/app.css')?>" rel="stylesheet">
<style>
.quote-step{border:1px solid #e9ecef;border-radius:14px;padding:18px;background:#fff}
.step-badge{width:34px;height:34px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#0d6efd;color:#fff;font-weight:700}
.summary-box{background:#f8f9fa;border-radius:12px;padding:14px}
</style>
</head>
<body>
<div class="app-shell">
<?php require __DIR__.'/../partials/sidebar.php';?>
<main class="main">
<?php require __DIR__.'/../partials/topbar.php';?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1"><?=e(t('new_quote'))?></h4>
    <p class="text-muted mb-0"><?=e(t('simple_quote_help'))?></p>
  </div>
</div>

<div class="row g-4">
<div class="col-12 col-xl-7">
<div class="content-card">

<div class="quote-step mb-3">
  <div class="d-flex align-items-center gap-2 mb-3"><span class="step-badge">1</span><h6 class="mb-0"><?=e(t('stay_details'))?></h6></div>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label"><?=e(t('client_agency'))?></label>
      <input id="client" class="form-control" placeholder="Curuis Travel">
    </div>
    <div class="col-md-6">
      <label class="form-label"><?=e(t('hotel'))?> *</label>
      <select id="hotel" class="form-select">
        <option value=""><?=e(t('select_option'))?></option>
        <?php foreach($hotels as $h):?>
        <option value="<?=(int)$h['id']?>"><?=e($h['name'])?> (<?=e($h['currency_code'])?>)</option>
        <?php endforeach;?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label"><?=e(t('arrival'))?> *</label>
      <input id="arrival" type="date" class="form-control">
    </div>
    <div class="col-md-4">
      <label class="form-label"><?=e(t('departure'))?> *</label>
      <input id="departure" type="date" class="form-control">
    </div>
    <div class="col-md-4">
      <label class="form-label"><?=e(t('number_of_nights'))?></label>
      <input id="nightsDisplay" class="form-control" value="0" readonly>
    </div>
  </div>
</div>

<div class="quote-step mb-3">
  <div class="d-flex align-items-center gap-2 mb-3"><span class="step-badge">2</span><h6 class="mb-0"><?=e(t('group_details'))?></h6></div>
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label"><?=e(t('number_of_pax'))?> *</label>
      <input id="pax" type="number" min="1" class="form-control" value="21">
      <div class="form-text"><?=e(t('pax_example'))?></div>
    </div>
    <div class="col-md-4">
      <label class="form-label"><?=e(t('selling_currency'))?></label>
      <select id="currency" class="form-select">
        <?php foreach($currencies as $c):?>
        <option value="<?=e($c['code'])?>" <?=$c['code']==='QAR'?'selected':''?>><?=e($c['code'])?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label"><?=e(t('profit_per_pax'))?></label>
      <input id="profit" type="number" min="0" step=".01" class="form-control" value="0">
    </div>
  </div>
</div>

<div class="quote-step mb-3">
  <div class="d-flex align-items-center gap-2 mb-3"><span class="step-badge">3</span><h6 class="mb-0"><?=e(t('select_services'))?></h6></div>
  <div class="row g-3">
    <div class="col-md-6">
      <div class="form-check form-switch mt-2">
        <input class="form-check-input" type="checkbox" id="includeVisa">
        <label class="form-check-label fw-semibold" for="includeVisa"><?=e(t('include_evisa'))?></label>
      </div>
      <div class="form-text"><?=e(t('evisa_per_pax_help'))?></div>
    </div>
    <div class="col-md-6">
      <label class="form-label"><?=e(t('transfer'))?></label>
      <select id="transferOption" class="form-select">
        <option value=""><?=e(t('not_included'))?></option>
        <option value="ONE_WAY"><?=e(t('one_way'))?></option>
        <option value="ROUND_TRIP"><?=e(t('round_trip'))?></option>
      </select>
      <div class="form-text"><?=e(t('transfer_auto_vehicle_help'))?></div>
    </div>

    <div class="col-md-6">
      <label class="form-label"><?=e(t('safari'))?></label>
      <select id="safariOption" class="form-select">
        <option value=""><?=e(t('not_included'))?></option>
        <option value="SAFARI"><?=e(t('safari_standard'))?></option>
        <option value="SAFARI_LUXE"><?=e(t('safari_luxe'))?></option>
      </select>
      <div class="form-check form-switch mt-2">
        <input class="form-check-input" type="checkbox" id="safariDinner">
        <label class="form-check-label" for="safariDinner"><?=e(t('add_dinner'))?></label>
      </div>
    </div>

    <div class="col-md-6">
      <label class="form-label"><?=e(t('boat_cruise'))?></label>
      <select id="boatOption" class="form-select">
        <option value=""><?=e(t('not_included'))?></option>
        <option value="CRUISE"><?=e(t('include_boat_cruise'))?></option>
      </select>
      <div class="form-check form-switch mt-2">
        <input class="form-check-input" type="checkbox" id="boatDinner">
        <label class="form-check-label" for="boatDinner"><?=e(t('add_dinner'))?></label>
      </div>
    </div>

    <div class="col-md-6">
      <label class="form-label"><?=e(t('city_tour'))?></label>
      <select id="cityTourOption" class="form-select">
        <option value=""><?=e(t('not_included'))?></option>
        <option value="4H"><?=e(t('four_hours'))?></option>
        <option value="8H"><?=e(t('eight_hours'))?></option>
      </select>
      <div class="form-check form-switch mt-2">
        <input class="form-check-input" type="checkbox" id="cityGuide">
        <label class="form-check-label" for="cityGuide"><?=e(t('add_guide'))?></label>
      </div>
    </div>

    <div class="col-md-6">
      <label class="form-label"><?=e(t('vehicle_rental'))?></label>
      <select id="rentalOption" class="form-select">
        <option value=""><?=e(t('not_included'))?></option>
        <option value="HALF_DAY"><?=e(t('half_day_rate'))?></option>
        <option value="FULL_DAY"><?=e(t('full_day_rate'))?></option>
      </select>
      <div class="form-text"><?=e(t('automatic_capacity_selection'))?></div>
    </div>
  </div>
</div>

<button class="btn btn-primary btn-lg w-100" type="button" onclick="calculateQuote()">
  <i class="bi bi-calculator me-2"></i><?=e(t('calculate_quote'))?>
</button>

</div>
</div>

<div class="col-12 col-xl-5">
<div class="content-card sticky-top" style="top:20px">
  <h5 class="mb-3"><?=e(t('quote_preview'))?></h5>
  <div id="preview" class="text-muted"><?=e(t('quote_preview_simple_help'))?></div>
</div>
</div>
</div>

<script>
const HOTEL_RATES=<?=json_encode($hotelRates,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;
const SERVICE_VARIANTS=<?=json_encode($variants,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;
const FX=<?=json_encode($fxRows,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;
const ROOM_TYPES=<?=json_encode($roomTypes,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;
const IS_FR=document.documentElement.lang==='fr';

function nights(){
  const a=document.getElementById('arrival').value;
  const d=document.getElementById('departure').value;
  if(!a||!d)return 0;
  return Math.max(0,Math.round((new Date(d+'T00:00:00')-new Date(a+'T00:00:00'))/86400000));
}

function refreshNights(){
  document.getElementById('nightsDisplay').value=nights();
}
document.getElementById('arrival').addEventListener('change',refreshNights);
document.getElementById('departure').addEventListener('change',refreshNights);

function convert(amount,from,to){
  if(from===to)return amount;
  const direct=FX.find(x=>x.base_code===from&&x.target_code===to);
  if(direct)return amount*Number(direct.rate);
  const inverse=FX.find(x=>x.base_code===to&&x.target_code===from);
  if(inverse)return amount/Number(inverse.rate);

  let qar=amount;
  if(from!=='QAR'){
    const toQar=FX.find(x=>x.base_code===from&&x.target_code==='QAR');
    const fromQar=FX.find(x=>x.base_code==='QAR'&&x.target_code===from);
    if(toQar) qar=amount*Number(toQar.rate);
    else if(fromQar) qar=amount/Number(fromQar.rate);
  }
  if(to==='QAR')return qar;

  const qarTo=FX.find(x=>x.base_code==='QAR'&&x.target_code===to);
  const toQar=FX.find(x=>x.base_code===to&&x.target_code==='QAR');
  if(qarTo)return qar*Number(qarTo.rate);
  if(toQar)return qar/Number(toQar.rate);
  return qar;
}

function money(v){
  return new Intl.NumberFormat('fr-FR',{minimumFractionDigits:0,maximumFractionDigits:2}).format(v);
}

function dateInRange(date,from,to){
  return (!from||from<=date)&&(!to||to>=date);
}

function nightDates(arrival,count){
  const out=[];
  const d=new Date(arrival+'T00:00:00');
  for(let i=0;i<count;i++){
    const x=new Date(d);
    x.setDate(x.getDate()+i);
    out.push(x.toISOString().slice(0,10));
  }
  return out;
}

function hotelStayCostQar(hotelId,typeId,arrival,count){
  const dates=nightDates(arrival,count);
  let total=0;
  for(const date of dates){
    const candidates=HOTEL_RATES.filter(r=>
      Number(r.hotel_id)===hotelId &&
      Number(r.accommodation_type_id)===typeId &&
      dateInRange(date,r.valid_from,r.valid_to)
    );
    if(!candidates.length)return null;
    const rate=candidates[candidates.length-1];
    total+=convert(Number(rate.amount),rate.currency_code||'QAR','QAR');
  }
  return total;
}

function validServiceRate(v,date){
  return v.amount!==null && dateInRange(date,v.rate_valid_from,v.rate_valid_to);
}

function visaFor(date){
  const candidates=SERVICE_VARIANTS.filter(v=>
    v.category_code==='VISA' &&
    v.pricing_type==='PER_PAX' &&
    validServiceRate(v,date)
  );
  if(!candidates.length)return null;
  candidates.sort((a,b)=>convert(Number(a.amount),a.currency_code||'QAR','QAR')-convert(Number(b.amount),b.currency_code||'QAR','QAR'));
  return candidates[0];
}

function bestTransfer(option,pax,date){
  const candidates=SERVICE_VARIANTS.filter(v=>
    v.category_code==='TRANSFER' &&
    v.option_code===option &&
    v.pricing_type==='PER_UNIT' &&
    Number(v.capacity||0)>0 &&
    validServiceRate(v,date)
  );

  let best=null;
  candidates.forEach(v=>{
    const cap=Number(v.capacity);
    const qty=Math.ceil(pax/cap);
    const unitQar=convert(Number(v.amount),v.currency_code||'QAR','QAR');
    const totalQar=qty*unitQar;

    if(!best || totalQar<best.totalQar || (totalQar===best.totalQar && qty<best.qty)){
      best={...v,qty,cap,unitQar,totalQar};
    }
  });
  return best;
}


function bestUnit(category,option,pax,date){
  const candidates=SERVICE_VARIANTS.filter(v=>
    v.category_code===category &&
    v.option_code===option &&
    v.pricing_type==='PER_UNIT' &&
    Number(v.capacity||0)>0 &&
    validServiceRate(v,date)
  );
  let best=null;
  candidates.forEach(v=>{
    const cap=Number(v.capacity);
    const qty=Math.ceil(pax/cap);
    const unitQar=convert(Number(v.amount),v.currency_code||'QAR','QAR');
    const totalQar=qty*unitQar;
    if(!best || totalQar<best.totalQar || (totalQar===best.totalQar && qty<best.qty)){
      best={...v,qty,cap,unitQar,totalQar};
    }
  });
  return best;
}

function cheapestPerPax(category,option,date){
  const candidates=SERVICE_VARIANTS.filter(v=>
    v.category_code===category &&
    v.option_code===option &&
    v.pricing_type==='PER_PAX' &&
    validServiceRate(v,date)
  );
  if(!candidates.length)return null;
  candidates.sort((a,b)=>convert(Number(a.amount),a.currency_code||'QAR','QAR')-convert(Number(b.amount),b.currency_code||'QAR','QAR'));
  return candidates[0];
}

function cheapestHourly(date){
  const candidates=SERVICE_VARIANTS.filter(v=>
    (v.category_code==='CITY_TOUR'||v.category_code==='GUIDE') &&
    (v.option_code==='GUIDE_HOURLY'||v.option_code==='HOURLY') &&
    v.pricing_type==='PER_HOUR' &&
    validServiceRate(v,date)
  );
  if(!candidates.length)return null;
  candidates.sort((a,b)=>convert(Number(a.amount),a.currency_code||'QAR','QAR')-convert(Number(b.amount),b.currency_code||'QAR','QAR'));
  return candidates[0];
}

function escapeHtml(s){
  return String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}

function calculateQuote(){
  const hotelId=Number(document.getElementById('hotel').value);
  const arrival=document.getElementById('arrival').value;
  const departure=document.getElementById('departure').value;
  const n=nights();
  const pax=Number(document.getElementById('pax').value||0);
  const cur=document.getElementById('currency').value;
  const profit=Number(document.getElementById('profit').value||0);

  if(!hotelId||!arrival||!departure||n<=0||pax<=0){
    alert(IS_FR?'Veuillez sélectionner l’hôtel, les dates et le nombre de PAX.':'Please select the hotel, dates and number of PAX.');
    return;
  }

  let servicesTotalQar=0;
  const serviceLines=[];

  if(document.getElementById('includeVisa').checked){
    const visa=visaFor(arrival);
    if(!visa){
      serviceLines.push('<span class="text-danger">'+(IS_FR?'Aucun tarif eVisa valide.':'No valid eVisa rate.')+'</span>');
    }else{
      const unitQar=convert(Number(visa.amount),visa.currency_code||'QAR','QAR');
      const total=unitQar*pax;
      servicesTotalQar+=total;
      serviceLines.push(
        '<div class="d-flex justify-content-between gap-3"><span>eVisa · '+pax+' PAX</span><strong>'+money(total)+' QAR</strong></div>'+
        '<div class="small text-muted">'+money(unitQar)+' QAR / PAX</div>'
      );
    }
  }

  const transferOption=document.getElementById('transferOption').value;
  if(transferOption){
    const transfer=bestTransfer(transferOption,pax,arrival);
    if(!transfer){
      serviceLines.push('<span class="text-danger">'+(IS_FR?'Aucun véhicule avec tarif valide pour ce transfert.':'No vehicle with a valid transfer rate.')+'</span>');
    }else{
      servicesTotalQar+=transfer.totalQar;
      const vehicle=IS_FR?transfer.service_fr:transfer.service_en;
      const option=transferOption==='ROUND_TRIP'?(IS_FR?'Aller-retour':'Round Trip'):(IS_FR?'Aller simple':'One Way');
      serviceLines.push(
        '<div class="d-flex justify-content-between gap-3"><span>'+escapeHtml(option)+' · '+transfer.qty+' × '+escapeHtml(vehicle)+'</span><strong>'+money(transfer.totalQar)+' QAR</strong></div>'+
        '<div class="small text-muted">'+(IS_FR?'Capacité':'Capacity')+': '+transfer.cap+' PAX · '+money(transfer.totalQar/pax)+' QAR / PAX</div>'
      );
    }
  }

  const safariCategory=document.getElementById('safariOption').value;
  if(safariCategory){
    const safari=bestUnit(safariCategory,'SAFARI_CAR',pax,arrival);
    if(!safari){
      serviceLines.push('<span class="text-danger">'+(IS_FR?'Aucun tarif Safari valide.':'No valid Safari rate.')+'</span>');
    }else{
      servicesTotalQar+=safari.totalQar;
      const vehicle=IS_FR?safari.service_fr:safari.service_en;
      serviceLines.push(
        '<div class="d-flex justify-content-between gap-3"><span>Safari · '+safari.qty+' × '+escapeHtml(vehicle)+'</span><strong>'+money(safari.totalQar)+' QAR</strong></div>'+
        '<div class="small text-muted">'+(IS_FR?'Capacité':'Capacity')+': '+safari.cap+' PAX · '+money(safari.totalQar/pax)+' QAR / PAX</div>'
      );
      if(document.getElementById('safariDinner').checked){
        const dinner=cheapestPerPax(safariCategory,'DINNER',arrival);
        if(dinner){
          const perPax=convert(Number(dinner.amount),dinner.currency_code||'QAR','QAR');
          const total=perPax*pax;
          servicesTotalQar+=total;
          serviceLines.push('<div class="d-flex justify-content-between gap-3"><span>'+(IS_FR?'Dîner Safari':'Safari Dinner')+' · '+pax+' PAX</span><strong>'+money(total)+' QAR</strong></div><div class="small text-muted">'+money(perPax)+' QAR / PAX</div>');
        }else{
          serviceLines.push('<span class="text-danger">'+(IS_FR?'Aucun tarif dîner Safari valide.':'No valid Safari dinner rate.')+'</span>');
        }
      }
    }
  }

  if(document.getElementById('boatOption').value){
    const boat=bestUnit('BOAT','CRUISE',pax,arrival);
    if(!boat){
      serviceLines.push('<span class="text-danger">'+(IS_FR?'Aucun tarif Boat Cruise valide.':'No valid Boat Cruise rate.')+'</span>');
    }else{
      servicesTotalQar+=boat.totalQar;
      const boatName=IS_FR?boat.service_fr:boat.service_en;
      serviceLines.push(
        '<div class="d-flex justify-content-between gap-3"><span>'+escapeHtml(boatName)+' · '+boat.qty+' × '+(IS_FR?'bateau':'boat')+'</span><strong>'+money(boat.totalQar)+' QAR</strong></div>'+
        '<div class="small text-muted">'+(IS_FR?'Capacité':'Capacity')+': '+boat.cap+' PAX · '+money(boat.totalQar/pax)+' QAR / PAX</div>'
      );
      if(document.getElementById('boatDinner').checked){
        const dinner=cheapestPerPax('BOAT','DINNER',arrival);
        if(dinner){
          const perPax=convert(Number(dinner.amount),dinner.currency_code||'QAR','QAR');
          const total=perPax*pax;
          servicesTotalQar+=total;
          serviceLines.push('<div class="d-flex justify-content-between gap-3"><span>'+(IS_FR?'Dîner croisière':'Cruise Dinner')+' · '+pax+' PAX</span><strong>'+money(total)+' QAR</strong></div><div class="small text-muted">'+money(perPax)+' QAR / PAX</div>');
        }else{
          serviceLines.push('<span class="text-danger">'+(IS_FR?'Aucun tarif dîner croisière valide.':'No valid cruise dinner rate.')+'</span>');
        }
      }
    }
  }

  const cityOption=document.getElementById('cityTourOption').value;
  if(cityOption){
    const city=bestUnit('CITY_TOUR',cityOption,pax,arrival);
    if(!city){
      serviceLines.push('<span class="text-danger">'+(IS_FR?'Aucun tarif City Tour valide.':'No valid City Tour rate.')+'</span>');
    }else{
      servicesTotalQar+=city.totalQar;
      const vehicle=IS_FR?city.service_fr:city.service_en;
      const hours=cityOption==='8H'?8:4;
      serviceLines.push(
        '<div class="d-flex justify-content-between gap-3"><span>City Tour '+hours+'h · '+city.qty+' × '+escapeHtml(vehicle)+'</span><strong>'+money(city.totalQar)+' QAR</strong></div>'+
        '<div class="small text-muted">'+money(city.totalQar/pax)+' QAR / PAX</div>'
      );
      if(document.getElementById('cityGuide').checked){
        const guide=cheapestHourly(arrival);
        if(guide){
          const hourly=convert(Number(guide.amount),guide.currency_code||'QAR','QAR');
          const total=hourly*hours;
          servicesTotalQar+=total;
          serviceLines.push('<div class="d-flex justify-content-between gap-3"><span>'+(IS_FR?'Guide':'Guide')+' · '+hours+'h × '+money(hourly)+' QAR</span><strong>'+money(total)+' QAR</strong></div><div class="small text-muted">'+money(total/pax)+' QAR / PAX</div>');
        }else{
          serviceLines.push('<span class="text-danger">'+(IS_FR?'Aucun tarif guide horaire valide.':'No valid hourly guide rate.')+'</span>');
        }
      }
    }
  }

  const rentalOption=document.getElementById('rentalOption').value;
  if(rentalOption){
    const rental=bestUnit('VEHICLE_RENTAL',rentalOption,pax,arrival);
    if(!rental){
      serviceLines.push('<span class="text-danger">'+(IS_FR?'Aucun tarif location véhicule valide.':'No valid vehicle rental rate.')+'</span>');
    }else{
      servicesTotalQar+=rental.totalQar;
      const vehicle=IS_FR?rental.service_fr:rental.service_en;
      serviceLines.push(
        '<div class="d-flex justify-content-between gap-3"><span>'+(rentalOption==='HALF_DAY'?(IS_FR?'Demi-journée':'Half Day'):(IS_FR?'Journée complète':'Full Day'))+' · '+rental.qty+' × '+escapeHtml(vehicle)+'</span><strong>'+money(rental.totalQar)+' QAR</strong></div>'+
        '<div class="small text-muted">'+money(rental.totalQar/pax)+' QAR / PAX</div>'
      );
    }
  }

  const servicesPerPaxQar=servicesTotalQar/pax;
  const rows=[];

  ROOM_TYPES.forEach(rt=>{
    const totalRoomQar=hotelStayCostQar(hotelId,Number(rt.id),arrival,n);
    if(totalRoomQar===null)return;

    const divisor=Math.max(1,Number(rt.divisor||1));
    const hotelPerPaxQar=totalRoomQar/divisor;
    const totalCostQar=hotelPerPaxQar+servicesPerPaxQar;

    const hotelPerPax=convert(hotelPerPaxQar,'QAR',cur);
    const servicesPerPax=convert(servicesPerPaxQar,'QAR',cur);
    const totalCost=convert(totalCostQar,'QAR',cur);
    const selling=totalCost+profit;

    rows.push({
      name:IS_FR?rt.name_fr:rt.name_en,
      code:rt.code,
      hotelPerPax,
      servicesPerPax,
      totalCost,
      profit,
      selling
    });
  });

  let html='';
  html+='<div class="summary-box mb-3">';
  html+='<div class="fw-semibold">'+escapeHtml(document.getElementById('client').value||'—')+'</div>';
  html+='<div class="small text-muted">'+pax+' PAX · '+n+' '+(IS_FR?'nuits':'nights')+' · '+arrival+' → '+departure+'</div>';
  html+='</div>';

  html+='<div class="mb-3"><div class="fw-semibold mb-2">'+(IS_FR?'Services sélectionnés':'Selected services')+'</div>';
  html+=serviceLines.length?serviceLines.join('<hr class="my-2">'):'<span class="text-muted">—</span>';
  html+='</div>';

  html+='<div class="summary-box mb-3">';
  html+='<div class="d-flex justify-content-between"><span>'+(IS_FR?'Coût services / PAX':'Services cost / PAX')+'</span><strong>'+money(convert(servicesPerPaxQar,'QAR',cur))+' '+cur+'</strong></div>';
  html+='</div>';

  if(!rows.length){
    html+='<div class="alert alert-warning mb-0">'+(IS_FR?'Aucun tarif hôtel valide pour les dates sélectionnées.':'No valid hotel rates for the selected dates.')+'</div>';
  }else{
    html+='<div class="table-responsive"><table class="table table-sm align-middle">';
    html+='<thead><tr><th>'+(IS_FR?'Type':'Type')+'</th><th class="text-end">'+(IS_FR?'Hôtel/PAX':'Hotel/PAX')+'</th><th class="text-end">'+(IS_FR?'Services/PAX':'Services/PAX')+'</th><th class="text-end">'+(IS_FR?'Coût/PAX':'Cost/PAX')+'</th><th class="text-end">'+(IS_FR?'Marge':'Profit')+'</th><th class="text-end">'+(IS_FR?'Vente/PAX':'Sell/PAX')+'</th></tr></thead><tbody>';
    rows.forEach(r=>{
      html+='<tr><td><strong>'+escapeHtml(r.name)+'</strong><div class="small text-muted">'+escapeHtml(r.code)+'</div></td>'+
        '<td class="text-end">'+money(r.hotelPerPax)+' '+cur+'</td>'+
        '<td class="text-end">'+money(r.servicesPerPax)+' '+cur+'</td>'+
        '<td class="text-end">'+money(r.totalCost)+' '+cur+'</td>'+
        '<td class="text-end">'+money(r.profit)+' '+cur+'</td>'+
        '<td class="text-end fw-bold">'+money(r.selling)+' '+cur+'</td></tr>';
    });
    html+='</tbody></table></div>';
  }

  document.getElementById('preview').innerHTML=html;
}

refreshNights();
</script>
</main>
</div>
</body>
</html>