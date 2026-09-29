<?php
require_once __DIR__ . '/../../bootstrap.php';
$currentPage='new_quote';
$pageTitle=t('new_quote');

$hotels=db()->query("SELECT h.id,h.name,c.code currency_code FROM hotels h INNER JOIN currencies c ON c.id=h.currency_id WHERE h.active=1 ORDER BY h.name")->fetchAll();
$hotelRates=db()->query("SELECT hr.hotel_id,hr.accommodation_type_id,hr.amount,hr.valid_from,hr.valid_to,at.code,at.divisor,at.name_fr,at.name_en FROM hotel_rates hr INNER JOIN accommodation_types at ON at.id=hr.accommodation_type_id WHERE hr.active=1 ORDER BY at.sort_order")->fetchAll();

$variants=db()->query("
 SELECT sv.id,sv.service_id,sv.name_fr,sv.name_en,sv.option_code,sv.pricing_type,sv.capacity,sv.duration_hours,
        s.name_fr service_fr,s.name_en service_en,sc.code category_code,sc.name_fr category_fr,sc.name_en category_en,
        r.amount,c.code currency_code
 FROM service_variants sv
 INNER JOIN services s ON s.id=sv.service_id
 INNER JOIN service_categories sc ON sc.id=s.category_id
 LEFT JOIN service_variant_rates r ON r.id=(
   SELECT r2.id FROM service_variant_rates r2
   WHERE r2.service_variant_id=sv.id AND r2.active=1
   ORDER BY COALESCE(r2.valid_from,'1900-01-01') DESC,r2.id DESC LIMIT 1
 )
 LEFT JOIN currencies c ON c.id=r.currency_id
 WHERE sv.active=1 AND s.active=1
 ORDER BY sc.name_en,sv.capacity,sv.name_en
")->fetchAll();

$currencies=db()->query("SELECT id,code FROM currencies WHERE active=1 ORDER BY code")->fetchAll();
$fxRows=db()->query("SELECT b.code base_code,t.code target_code,er.rate FROM exchange_rates er INNER JOIN currencies b ON b.id=er.base_currency_id INNER JOIN currencies t ON t.id=er.target_currency_id WHERE er.active=1")->fetchAll();
$roomTypes=db()->query("SELECT id,code,name_fr,name_en,divisor FROM accommodation_types WHERE active=1 ORDER BY sort_order")->fetchAll();
?>
<!doctype html><html lang="<?=e($langCode)?>"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($pageTitle)?> - Oriantis</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?=base_url('assets/css/app.css')?>" rel="stylesheet">
</head><body><div class="app-shell">
<?php require __DIR__.'/../partials/sidebar.php';?>
<main class="main"><?php require __DIR__.'/../partials/topbar.php';?>

<div class="row g-4">
<div class="col-12 col-xl-7">
<div class="content-card">
<h5 class="mb-3"><?=e(t('quote_details'))?></h5>
<div class="row g-3">
<div class="col-md-6"><label class="form-label"><?=e(t('client_agency'))?></label><input id="client" class="form-control" placeholder="Curuis Travel"></div>
<div class="col-md-3"><label class="form-label"><?=e(t('arrival'))?></label><input id="arrival" type="date" class="form-control"></div>
<div class="col-md-3"><label class="form-label"><?=e(t('departure'))?></label><input id="departure" type="date" class="form-control"></div>
<div class="col-md-6"><label class="form-label"><?=e(t('hotel'))?></label><select id="hotel" class="form-select"><option value=""><?=e(t('select_option'))?></option><?php foreach($hotels as $h):?><option value="<?=(int)$h['id']?>"><?=e($h['name'])?> (<?=e($h['currency_code'])?>)</option><?php endforeach;?></select></div>
<div class="col-md-3"><label class="form-label">ADT</label><input id="adults" type="number" min="0" class="form-control" value="21"></div>
<div class="col-md-3"><label class="form-label"><?=e(t('selling_currency'))?></label><select id="currency" class="form-select"><?php foreach($currencies as $c):?><option value="<?=e($c['code'])?>" <?=$c['code']==='USD'?'selected':''?>><?=e($c['code'])?></option><?php endforeach;?></select></div>
</div>

<h6 class="mt-4"><?=e(t('room_distribution'))?></h6>
<div class="row g-3">
<?php foreach($roomTypes as $rt):?>
<div class="col-6 col-md-4">
<label class="form-label"><?=e($langCode==='fr'?$rt['name_fr']:$rt['name_en'])?></label>
<input class="form-control roomqty" type="number" min="0" value="0" data-type-id="<?=(int)$rt['id']?>" data-code="<?=e($rt['code'])?>" data-divisor="<?=e((string)$rt['divisor'])?>">
</div>
<?php endforeach;?>
</div>

<h6 class="mt-4"><?=e(t('services'))?></h6>
<div class="row g-3">
<?php
$grouped=[];
foreach($variants as $v){$grouped[$v['category_code']][]=$v;}
foreach($grouped as $cat=>$rows):
?>
<div class="col-md-6">
<label class="form-label"><?=e($langCode==='fr'?$rows[0]['category_fr']:$rows[0]['category_en'])?></label>
<select class="form-select service-select" data-category="<?=e($cat)?>">
<option value=""><?=e(t('not_included'))?></option>
<?php foreach($rows as $v):?>
<option value="<?=(int)$v['id']?>"
 data-price="<?=e((string)($v['amount']??0))?>"
 data-currency="<?=e($v['currency_code']??'QAR')?>"
 data-capacity="<?=e((string)($v['capacity']??0))?>"
 data-pricing="<?=e($v['pricing_type'])?>"
 data-duration="<?=e((string)($v['duration_hours']??0))?>">
<?=e($langCode==='fr'?$v['name_fr']:$v['name_en'])?><?= $v['capacity']?' · '.$v['capacity'].' PAX':'' ?><?= $v['amount']!==null?' · '.number_format((float)$v['amount'],0).' '.e($v['currency_code']):'' ?>
</option>
<?php endforeach;?>
</select>
</div>
<?php endforeach;?>
</div>

<h6 class="mt-4"><?=e(t('profit_per_pax'))?></h6>
<div class="row g-3">
<div class="col-md-3"><label class="form-label">ADT</label><input id="profitAdult" type="number" class="form-control" value="100"></div>
<div class="col-md-3"><label class="form-label">CHD +6 WB</label><input id="profitChd" type="number" class="form-control" value="100"></div>
<div class="col-md-3"><label class="form-label">CHD -6 WOB</label><input id="profitChdNoBed" type="number" class="form-control" value="100"></div>
<div class="col-md-3"><label class="form-label">INF</label><input id="profitInf" type="number" class="form-control" value="0"></div>
</div>

<button class="btn btn-primary mt-4" type="button" onclick="calculateQuote()"><i class="bi bi-calculator me-2"></i><?=e(t('calculate_quote'))?></button>
</div></div>

<div class="col-12 col-xl-5">
<div class="content-card sticky-top" style="top:20px">
<h5><?=e(t('quote_preview'))?></h5>
<div id="preview" class="text-muted"><?=e(t('quote_preview_help'))?></div>
</div></div>
</div>

<script>
const HOTEL_RATES = <?=json_encode($hotelRates,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;
const FX = <?=json_encode($fxRows,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;

function nights(){
 const a=document.getElementById('arrival').value,d=document.getElementById('departure').value;
 if(!a||!d)return 0;
 return Math.max(0,Math.round((new Date(d)-new Date(a))/86400000));
}
function convert(amount,from,to){
 if(from===to)return amount;
 const forward=FX.find(x=>x.base_code===from&&x.target_code===to);
 if(forward)return amount*Number(forward.rate);
 const inverse=FX.find(x=>x.base_code===to&&x.target_code===from);
 if(inverse)return amount/Number(inverse.rate);
 let qar=amount;
 if(from!=='QAR'){
   const toQar=FX.find(x=>x.base_code===from&&x.target_code==='QAR');
   if(toQar)qar=amount*Number(toQar.rate);
 }
 if(to==='QAR')return qar;
 const out=FX.find(x=>x.base_code===to&&x.target_code==='QAR');
 return out?qar/Number(out.rate):qar;
}
function rateFor(hotelId,typeId,date){
 const c=HOTEL_RATES.filter(r=>Number(r.hotel_id)===hotelId&&Number(r.accommodation_type_id)===typeId&&(!r.valid_from||r.valid_from<=date)&&(!r.valid_to||r.valid_to>=date));
 return c.length?c[c.length-1]:null;
}
function money(v){return new Intl.NumberFormat('fr-FR',{maximumFractionDigits:0}).format(v);}
function calculateQuote(){
 const hotelId=Number(document.getElementById('hotel').value);
 const n=nights(),adt=Number(document.getElementById('adults').value||0);
 const cur=document.getElementById('currency').value,arrival=document.getElementById('arrival').value;
 if(!hotelId||!n||!adt){alert('Please select hotel, dates and PAX.');return;}

 let servicesTotalQAR=0,serviceLines=[];
 document.querySelectorAll('.service-select').forEach(sel=>{
   const opt=sel.options[sel.selectedIndex];
   if(!opt||!opt.value)return;
   const price=Number(opt.dataset.price||0),from=opt.dataset.currency||'QAR';
   const cap=Number(opt.dataset.capacity||0),pricing=opt.dataset.pricing;
   let qty=1;
   if(pricing==='PER_UNIT'&&cap>0)qty=Math.ceil(adt/cap);
   if(pricing==='PER_PAX')qty=adt;
   if(pricing==='PER_HOUR')qty=Number(opt.dataset.duration||1);
   const total=convert(price*qty,from,'QAR');
   servicesTotalQAR+=total;
   serviceLines.push(opt.textContent.trim()+' × '+qty+' = '+money(total)+' QAR');
 });

 const servicePaxQAR=servicesTotalQAR/adt;
 let rows=[];
 document.querySelectorAll('.roomqty').forEach(inp=>{
   const q=Number(inp.value||0); if(!q)return;
   const typeId=Number(inp.dataset.typeId),div=Number(inp.dataset.divisor||1),code=inp.dataset.code;
   const rate=rateFor(hotelId,typeId,arrival);
   if(!rate)return;
   const hotelPerPaxQAR=(Number(rate.amount)*n)/div;
   const costQAR=hotelPerPaxQAR+(code==='INF'?0:servicePaxQAR);
   let profit=Number(document.getElementById('profitAdult').value||0);
   if(code==='CHD_WB')profit=Number(document.getElementById('profitChd').value||0);
   if(code==='CHD_WOB')profit=Number(document.getElementById('profitChdNoBed').value||0);
   if(code==='INF')profit=Number(document.getElementById('profitInf').value||0);
   const cost=convert(costQAR,'QAR',cur),sell=cost+profit;
   rows.push({code,cost,profit,sell});
 });

 let html='<div class="small text-muted mb-3">'+document.getElementById('client').value+' · '+adt+' PAX · '+n+' nights</div>';
 html+='<div class="mb-3"><strong>Services</strong><div class="small mt-2">'+(serviceLines.length?serviceLines.join('<br>'):'—')+'</div></div>';
 html+='<table class="table table-sm"><thead><tr><th>Room</th><th class="text-end">Cost/PAX</th><th class="text-end">Profit</th><th class="text-end">Sell/PAX</th></tr></thead><tbody>';
 rows.forEach(r=>html+='<tr><td>'+r.code+'</td><td class="text-end">'+money(r.cost)+' '+cur+'</td><td class="text-end">'+money(r.profit)+' '+cur+'</td><td class="text-end fw-bold">'+money(r.sell)+' '+cur+'</td></tr>');
 html+='</tbody></table>';
 document.getElementById('preview').innerHTML=html;
}
</script>
</main></div></body></html>