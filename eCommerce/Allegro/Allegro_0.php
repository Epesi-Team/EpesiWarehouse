<?php
/**
 * 
 * @author bukowski@crazyit.pl
 * @copyright Telaxus LLC
 * @license Commercial
 * @version 0.1
 * @package epesi-Premium/Warehouse/eCommerce
 * @subpackage Allegro
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerce_Allegro extends Module {

	public function body() {
		$gb = & $this->init_module('Utils/GenericBrowser',null,'t1');
		$gb->set_table_columns(array(
		array('name'=>'Przedmiot/Nazwa aukcji','width'=>'260px'),
		array('name'=>'Ceny konkurencji','width'=>60),
		array('name'=>'Ilość','width'=>'40px'),
		array('name'=>'Wysyłka P/K/PP/KP','width'=>'60px'),
		array('name'=>'Dni','width'=>'50px'),
		array('name'=>'Cena W/M/KT','width'=>'60px'),
		array('name'=>'Dost. ilość','width'=>'50px'),
		array('name'=>'Zarobek %/zł','width'=>'110px'),
		array('name'=>'Koszt aukcji','width'=>'120px'),
		array('name'=>'Data zakończenia ostatniej aukcji','width'=>'80px','order'=>'a.ended_on'),
		));

		$gb->force_per_page(10);
		$limit = DB::GetOne('SELECT count(*) FROM premium_ecommerce_allegro_auctions a WHERE a.active=0 AND (SELECT 1 FROM premium_ecommerce_allegro_auctions a2 WHERE a2.item_id=a.item_id AND a2.active=1 LIMIT 1) IS NULL AND ended_on is not null');
		$limit = $gb->get_limit($limit);
		
		$gb->set_default_order(array('Data zakończenia ostatniej aukcji'=>'DESC'));
		$order = $gb->get_query_order();
		$query = 'SELECT a.auction_id as id,a.item_id,a.ended_on,a.prefs FROM premium_ecommerce_allegro_auctions a WHERE a.active=0 AND (SELECT 1 FROM premium_ecommerce_allegro_auctions a2 WHERE a2.item_id=a.item_id AND a2.active=1 LIMIT 1) IS NULL AND ended_on is not null '.$order;
		$ret = DB::SelectLimit($query, $limit['numrows'], $limit['offset']);
//		$query = 'SELECT a.item_id,a.ended_on FROM premium_ecommerce_allegro_auctions a WHERE a.active=0 AND ended_on is not null ORDER BY a.ended_on DESC LIMIT 10';
		
//		$ret = DB::Execute($query);

		static $auction_forms;
		if(!isset($auction_forms)) $auction_forms = array();
		while($row=$ret->FetchRow()) {
			if(!($prefs = @unserialize($row['prefs']))) continue;
			$gb_row = $gb->get_new_row();
			
			$days = '<select id="allegro_days_'.$row['id'].'" style="width:40px" >';
			foreach(array('3','5','7','10','14','30') as $k=>$d) {
			    $days .= '<option value="'.$k.'"'.($k==$prefs['days']?' selected':'').'>'.$d.'</option>';
			}
			$days .= '</select>';
			
			$rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$row['item_id']);
			$item_name = Premium_Warehouse_ItemsCommon::display_item_name($row['item_id'],true);
			
			$currency = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
			$d_pr = DB::GetOne('SELECT MIN(price) FROM premium_warehouse_wholesale_items WHERE item_id=%d AND quantity>0 AND price_currency=%d', array($row['item_id'],$currency));
			$av_qty = Premium_Warehouse_Items_LocationCommon::get_item_quantity_in_warehouse($row['item_id']);
			$tax = Data_TaxRatesCommon::get_tax_rate($rec['tax_rate']);
			$buy_price_net = $av_qty>0?@array_shift(explode('_',$rec['last_purchase_price'])):$d_pr;
			$buy_price_gross = $buy_price_net*(100+$tax)/100;
			$search_name = (isset($prefs['search']) && $prefs['search']?$prefs['search']:$item_name);
			
			$gb_row->add_data('<a '.Utils_RecordBrowserCommon::create_record_href('premium_warehouse_items', $row['item_id'], 'view',array('switch_to_addon'=>'Allegro')).'>'.$item_name.'</a><br /><input type="text" id="allegro_name_'.$row['id'].'" style="width:250px" maxlength=50 value="'.htmlspecialchars($prefs['title']).'"></input><div style="color:red;font-weight:bold;" id="allegro_error_'.$row['id'].'"></div>',
					'<input class="allegro_konkurencja_field" value="'.$search_name.'" style="width:250px"></input><input type="button" value="Szukaj"></input><div class="allegro_konkurencja" name="'.htmlspecialchars($search_name).'" cat="'.htmlspecialchars($prefs['category']).'" auction_id="'.$row['id'].'"></div>',
					'<input style="width:30px" id="allegro_qty_'.$row['id'].'" type="text" value="'.$prefs['qty'].'"></input>',
					'<input '.Utils_TooltipCommon::open_tag_attrs("Poczta").' style="width:50px" type="text" id="allegro_post_service_price_'.$row['id'].'" value="'.$prefs['post_service_price'].'"></input><br /><input '.Utils_TooltipCommon::open_tag_attrs("Kurier").' style="width:50px" type="text" id="allegro_ups_price_'.$row['id'].'" value="'.$prefs['ups_price'].'"></input><br /><input '.Utils_TooltipCommon::open_tag_attrs("Poczta (pobranie)").' type="text" style="width:50px" id="allegro_post_service_price_p_'.$row['id'].'" value="'.$prefs['post_service_price_p'].'"></input><br /><input '.Utils_TooltipCommon::open_tag_attrs("Kurier (pobranie)").' style="width:50px" type="text" id="allegro_ups_price_p_'.$row['id'].'" value="'.$prefs['ups_price_p'].'"></input>',$days,
					'<input '.Utils_TooltipCommon::open_tag_attrs("Cena wywoławcza").' style="width:50px;color:orange;" type="text" id="allegro_initial_price_'.$row['id'].'" value="'.$prefs['initial_price'].'"></input><br /><input '.Utils_TooltipCommon::open_tag_attrs("Cena minimalna").' type="text" style="width:50px" id="allegro_minimal_price_'.$row['id'].'" value="'.$prefs['minimal_price'].'"></input><br /><input '.Utils_TooltipCommon::open_tag_attrs("Kup teraz").' style="width:50px;color:red;" type="text" id="allegro_buy_price_'.$row['id'].'" value="'.$prefs['buy_price'].'"></input>',
					Utils_RecordBrowserCommon::get_val('premium_warehouse_items', 'Quantity on Hand', $rec),
					'<b>Koszt:</b><br /><span id="allegro_cost_'.$row['id'].'">'.number_format($buy_price_gross,2,'.','').'<br /><b>Zysk:</b><br /><span id="allegro_profit_'.$row['id'].'"></span><br /><b>Sprzedaż:</b><br /><span id="allegro_profit_price_'.$row['id'].'"></span>','<div auction_id="'.$row['id'].'" class="allegro_koszt"></div><input type="checkbox" id="allegro_add_auction_cost_'.$row['id'].'" '.(isset($prefs['add_auction_cost']) && $prefs['add_auction_cost']?'checked=1 ':'').'/>dodaj do CM/KT',str_replace(' ','<br />',$row['ended_on']));
			$gb_row->add_action('onClick="Premium_Warehouse_eCommerce_Allegro_wystaw(this.parentNode,'.$row['id'].')" href="javascript:void(0);"','Edit','Wystaw');
			$gb_row->add_action('onClick="Premium_Warehouse_eCommerce_Allegro_usun(this.parentNode,'.$row['id'].')" href="javascript:void(0);"','Delete','Usuń z listy');
		}
		$this->display_module($gb);
		load_js($this->get_module_dir().'utils.js');
		eval_js('jQuery(".allegro_konkurencja").each(function(key,el) { Premium_Warehouse_eCommerce_Allegro_konkurencja(jQuery(el));});');
		eval_js('jQuery(".allegro_konkurencja_field").change(function() { Premium_Warehouse_eCommerce_Allegro_konkurencja_field(jQuery(this));});');
		eval_js('jQuery(".allegro_koszt").each(function(key,el) { Premium_Warehouse_eCommerce_Allegro_koszt(jQuery(el));});');
	}
	
	public function new_auction_form($r) {
		$desc = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$r['id'],'language'=>'pl'));
		if($desc) $desc = array_shift($desc);
		
//		$cats = DB::GetAssoc('SELECT id,name FROM premium_ecommerce_allegro_cats WHERE country=%d ORDER BY name',array($country));
//		if(empty($cats))
//			$cats = Premium_Warehouse_eCommerce_AllegroCommon::update_cats();
		
		$qf = $this->init_module('Libs_QuickForm',null, 'allegro_p_'.$r['id']);
		
		$qf->addElement('text','title',$this->t('Tytuł'),array('style'=>'width:300px','maxlength'=>'50'));
		$qf->addRule('title',$this->t('Field required'),'required');
		$qf->addRule('title','Maksymalna długość tytułu to 50 znaków','maxlength',50);
		$qf->addRule('title','Maksymalna długość tytułu to 50 znaków','callback',array($this,'check_auction_title'));
		$title = '';
		if(isset($desc['display_name']))
			$title=$desc['display_name'];
		else
			$title = $r['item_name'];
		$qf->setDefaults(array('title'=>html_entity_decode($r['item_name'])));
		
		$qf->addElement('autoselect','category','Kategoria', array(), array(array($this->get_type().'Common', 'autoselect_category_search'),array()),array($this->get_type().'Common', 'autoselect_category_format'));
//		$qf->addElement('select','category',$this->t('Kategoria'),$cats);
		$qf->addRule('category',$this->t('Field required'),'required');
		
		$qf->addElement('select','days',$this->t('Dni'),array('3','5','7','10','14','30'));
		$qf->addRule('days',$this->t('Field required'),'required');
		$qf->setDefaults(array('days'=>3));
		
		$qf->addElement('text','qty',$this->t('Ilość'));
		$qf->addRule('qty',$this->t('Field required'),'required');
		$qf->addRule('qty',$this->t('Invalid number'),'regex','/^[1-9][0-9]*$/');
		$qf->setDefaults(array('qty'=>1));
		
		$qf->addElement('text','initial_price',$this->t('Cena wywoławcza'));
		$qf->addRule('initial_price',$this->t('Invalid price'),'regex','/^[1-9][0-9]*(\.[0-9]+)?$/');
		
		$qf->addElement('text','minimal_price',$this->t('Cena minimalna'));
		$qf->addRule('minimal_price',$this->t('Invalid price'),'regex','/^[1-9][0-9]*(\.[0-9]+)?$/');
		
		$qf->addElement('text','buy_price',$this->t('Cena Kup Teraz'));
		$qf->addRule('buy_price',$this->t('Invalid price'),'regex','/^[1-9][0-9]*(\.[0-9]+)?$/');
		if($r['net_price'] && $r['tax_rate']) {
			$curr = Utils_CurrencyFieldCommon::get_values($r['net_price']);
			if(Utils_CurrencyFieldCommon::get_code($curr[1])=='PLN')
				$qf->setDefaults(array('buy_price'=>number_format($r['net_price']*(100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))/100,2,'.','')));
		} elseif($price = DB::GetOne('SELECT f_gross_price FROM premium_ecommerce_prices_data_1 WHERE f_item_name=%d AND f_currency=%d',array($r['id'],Utils_CurrencyFieldCommon::get_id_by_code('PLN')))) {
			$qf->setDefaults(array('buy_price'=>number_format($price)));
		}
		
		$qf->addElement('select','stan',$this->t('Stan'),array('---','Nowy','Używany'));
		$qf->setDefaults(array('stan'=>1));
		
		$qf->addElement('select','transport',$this->t('Transport'),array('Sprzedający pokrywa koszty transportu','Kupujący pokrywa koszty transportu'));
		$qf->addRule('transport',$this->t('Field required'),'required');
		$qf->setDefaults(array('transport'=>1));

		$qf->addElement('checkbox','abroad',$this->t('Zgadzam się na wysyłkę za granicę'));
		$qf->addElement('checkbox','in_shop',$this->t('Odbiór osobisty'));
		$qf->addElement('checkbox','in_shop_trans',$this->t('Odbiór osobisty po przedpłacie'));
		$qf->setDefaults(array('abroad'=>1,'in_shop'=>1,'in_shop_trans'=>1));
		
		$qf->addElement('text','post_service_price',$this->t('Cena paczki'));
		$qf->addRule('post_service_price',$this->t('Invalid price'),'regex','/^[0-9][0-9]*(\.[0-9]+)?$/');

		$qf->addElement('text','post_service_price_p',$this->t('Cena paczki (pobranie)'));
		$qf->addRule('post_service_price_p',$this->t('Invalid price'),'regex','/^[0-9][0-9]*(\.[0-9]+)?$/');

		$qf->addElement('text','ups_price',$this->t('Cena kuriera'));
		$qf->addRule('ups_price',$this->t('Invalid price'),'regex','/^[0-9][0-9]*(\.[0-9]+)?$/');

		$qf->addElement('text','ups_price_p',$this->t('Cena kuriera (pobranie)'));
		$qf->addRule('ups_price_p',$this->t('Invalid price'),'regex','/^[0-9][0-9]*(\.[0-9]+)?$/');

		$ship_cost = Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>1,'payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'), '>=max_weight'=>$r['weight'])); //poczta polska, pobranie
		$ship_cost = array_merge($ship_cost,Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>1,'payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'), 'max_weight'=>''))); //poczta polska, pobranie
		foreach($ship_cost as $sh) {
		    $ppp = $qf->exportValue('buy_price');
		    if(!$ppp) $ppp=$qf->exportValue('minimal_price');
		    if(!$ppp) $ppp=0;
		    $qf->setDefaults(array('post_service_price_p'=>number_format($sh['price']+$sh['percentage_of_amount']*$ppp/100,2,'.','')));
		    break;
		}

		$ship_cost = Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>array(2,3,4,5,7,8,9,10),'payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'), '>=max_weight'=>$r['weight'])); //poczta polska, pobranie
		$ship_cost = array_merge($ship_cost,Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>array(2,3,4,5,7,8,9,10),'payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'), 'max_weight'=>''))); //poczta polska, pobranie
		foreach($ship_cost as $sh) {
		    $ppp = $qf->exportValue('buy_price');
		    if(!$ppp) $ppp=$qf->exportValue('minimal_price');
		    if(!$ppp) $ppp=0;
		    $qf->setDefaults(array('ups_price_p'=>number_format($sh['price']+$sh['percentage_of_amount']*$ppp/100,2,'.','')));
		    break;
		}

		$ship_cost = Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>1,'!payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'), '>=max_weight'=>$r['weight'])); //poczta polska, pobranie
		$ship_cost = array_merge($ship_cost,Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>1,'!payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'), 'max_weight'=>''))); //poczta polska, pobranie
		foreach($ship_cost as $sh) {
		    $ppp = $qf->exportValue('buy_price');
		    if(!$ppp) $ppp=$qf->exportValue('minimal_price');
		    if(!$ppp) $ppp=0;
		    $qf->setDefaults(array('post_service_price'=>number_format($sh['price']+$sh['percentage_of_amount']*$ppp/100,2,'.','')));
		    break;
		}

		$ship_cost = Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>array(2,3,4,5,7,8,9,10),'!payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'), '>=max_weight'=>$r['weight'])); //poczta polska, pobranie
		$ship_cost = array_merge($ship_cost,Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>array(2,3,4,5,7,8,9,10),'!payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'), 'max_weight'=>''))); //poczta polska, pobranie
		foreach($ship_cost as $sh) {
		    $ppp = $qf->exportValue('buy_price');
		    if(!$ppp) $ppp=0;
		    $qf->setDefaults(array('ups_price'=>number_format($sh['price']+$sh['percentage_of_amount']*$ppp/100,2,'.','')));
		    break;
		}
		$qf->addElement('textarea','transport_description','Dodatkowe informacje o przesyłce i płatności');
		$transport_description = trim(Variable::get('allegro_transport_description'));
		if($transport_description)
		$qf->setDefaults(array('transport_description'=>$transport_description));

		$qf->addElement('checkbox','add_auction_cost',$this->t('Dodaj koszt aukcji'));
		$qf->setDefaults(array('add_auction_cost'=>1));
		
		$qf->addElement('header',null,'Wygląd aukcji');
		$qf->addElement('select','template',$this->t('Szablon'),Premium_Warehouse_eCommerce_AllegroCommon::get_templates());
		$qf->setDefaults(array('template'=>Variable::get('allegro_template')));
		$qf->addElement('checkbox','pr_bold',$this->t('Pogrubienie'));
		$qf->addElement('checkbox','pr_thumbnail',$this->t('Miniaturka'));
		$qf->setDefaults(array('pr_thumbnail'=>1));
		$qf->addElement('checkbox','pr_light',$this->t('Podświetlenie'));
		$qf->addElement('checkbox','pr_bigger',$this->t('Wyróżnienie'));
		$qf->addElement('checkbox','pr_catpage',$this->t('Strona kategorii'));
		$qf->addElement('checkbox','pr_mainpage',$this->t('Strona główna Allegro'));
		$qf->addElement('checkbox','pr_watermark',$this->t('Znak wodny'));
		
		$qf->addElement('hidden','publish',0,array('id'=>'allegro_publish'));
		
		$qf->addElement('submit','submit','Wystaw');
		
		$prefs = DB::GetOne('SELECT prefs FROM premium_ecommerce_allegro_auctions WHERE item_id=%d ORDER BY started_on DESC',array($r['id']));
		if(!$qf->exportValue('submited') && $prefs && $prefs = @unserialize($prefs)) {
		    unset($prefs['template']);
		    unset($prefs['publish']);
		    $qf->setDefaults(array('add_auction_cost'=>0));
		    $qf->setDefaults($prefs);
		}
		
		if($qf->validate()) {
			$vals = $qf->exportValues();
			$auction_cost = $this->isset_module_variable('auction_cost')?$this->get_module_variable('auction_cost'):0;
			$fields = Premium_Warehouse_eCommerce_AllegroCommon::get_publish_array($r,$vals,$auction_cost);
			if(!$fields) return;
			$a = Premium_Warehouse_eCommerce_AllegroCommon::get_lib();
				
			if(isset($vals['publish']) && $vals['publish']) {
				$local_id = Acl::get_user()*1000000+mt_rand(0,999999);
				$a->new_auction($fields,$local_id);
				eval_js('$("allegro_publish").value=0;');
				$err = $a->error();
				if($err)
				    Epesi::alert($err);
				else {
					Epesi::alert('Aukcja została dodana.');
					$ret = $a->verify_new_auction($local_id);
					$buy_now = $vals['buy_price']?$vals['buy_price']+((isset($vals['add_auction_cost']) && $vals['add_auction_cost'] && $auction_cost)?$auction_cost:0):'';
					DB::Execute('INSERT INTO premium_ecommerce_allegro_auctions (auction_id,item_id,created_by,started_on,buy_price,prefs) VALUES(%s,%d,%d,%T,%f,%s)',array($ret['itemId'],$r['id'],Acl::get_user(),$ret['itemStartingTime'],$buy_now?$buy_now:null,serialize($vals)));
				}
			} else {
				$ret = $a->check_new_auction_price($fields);
//				error_log(print_r($ret,true),3,'/tmp/dupa124');
				$err = $a->error();
				$auction_cost = &$this->get_module_variable('auction_cost');
				$auction_cost = 0;
				if($err)
				    Epesi::alert($err);
				elseif(isset($ret['itemPrice']) && isset($ret['itemPriceDesc'])) {
				    $ret['itemPrice'] = str_replace(',','.',$ret['itemPrice']);
				    if(preg_match('/([\d]+(\.[\d]+)?)/', $ret['itemPrice'], $match)) {
					    $auction_cost += (float)$match[0];
				    }
				    $sell_price = 0;
				    if(isset($vals['buy_price']) && $vals['buy_price']) {
					    $sell_price = $vals['buy_price'];
				    } elseif(isset($vals['minimal_price']) && $vals['minimal_price']) {
					    $sell_price = $vals['minimal_price'];
				    } elseif(isset($vals['initial_price']) && $vals['initial_price']) {
					    $sell_price = $vals['initial_price'];
				    }
				    if($sell_price) {
					if($sell_price<=100)
						$sell_price *= 5/100;
					elseif($sell_price<=1000)
						$sell_price = 5+($sell_price-100)*1.90/100;
					elseif($sell_price<=5000)
						$sell_price = 22.1+($sell_price-1000)*0.50/100;
					else
						$sell_price = 42.1+($sell_price-5000)*0.20/100;
					$sell_price = round($sell_price,2);
					$auction_cost += $sell_price;
				    }
				    eval_js('if(confirm("'.Epesi::escapeJS($ret['itemPriceDesc'],true,false).'\nOpłata łączna za wystawienie: '.Epesi::escapeJS($ret['itemPrice'],true,false).'\nOpłata za sprzedaż: '.$sell_price.' zł\n-------------------------------\nWystawienie + Sprzedaż: '.$auction_cost.' zł")) {$("allegro_publish").value=1;'.$qf->get_submit_form_js(true,'publikuję aukcję',true).'}');
				}else
					Epesi::alert('Nie można pobrać kosztu wystawienia aukcji');
				Libs_LeightboxCommon::close('new_auction_leightbox_'.$r['id']);
			} 
				
			Premium_Warehouse_eCommerce_AllegroCommon::delete_photos();
		}
		Libs_LeightboxCommon::display('new_auction_leightbox_'.$r['id'],$this->get_html_of_module($qf),'Wystaw aukcję');
	}
		
	public function warehouse_item_addon($r) {
		$this->new_auction_form($r);
		$gb = & $this->init_module('Utils/GenericBrowser',null,'t1');
		print('<div style="text-align:left;padding:10px"><a '.Libs_LeightboxCommon::get_open_href('new_auction_leightbox_'.$r['id']).'><span class="search_button">Nowa aukcja</span></a></div>');
		$gb->set_table_columns(array(
		array('name'=>'Aukcja','width'=>50,'order'=>'a.auction_id'),
		array('name'=>'Aktywna','width'=>20,'order'=>'a.active'),
		array('name'=>'Stworzona przez','width'=>50,'order'=>'u.login'),
		array('name'=>'Start','width'=>50,'order'=>'a.started_on'),
		array('name'=>'Koniec','width'=>50,'order'=>'a.ended_on'),
		));
		$gb->set_default_order(array('Start'=>'DESC'));
		
		$search = $gb->get_search_query();
		$query = 'SELECT u.login, a.active,a.started_on,a.auction_id,a.ended_on FROM premium_ecommerce_allegro_auctions a INNER JOIN user_login u ON u.id=a.created_by WHERE a.item_id='.$r['id'].($search?' AND '.$search:'');
		$query_qty = 'SELECT count(a.auction_id) FROM premium_ecommerce_allegro_auctions a INNER JOIN user_login u ON u.id=a.created_by WHERE a.item_id='.$r['id'].($search?' AND '.$search:'');
		
		$ret = $gb->query_order_limit($query, $query_qty);

		$on = '<span class="checkbox_on" />';
		$off = '<span class="checkbox_off" />';
		while(($row=$ret->FetchRow())) {
			$gb->add_row((Variable::get('allegro_country')!=1?'<a target="_blank" href="http://testwebapi.pl/i'.$row['auction_id'].'.html">'.$row['auction_id'].'</a>':'<a href="http://allegro.pl/ShowItem2.php?item='.$row['auction_id'].'" target="_blank">'.$row['auction_id'].'</a>'),
				$row['active']==1?$on:$off,$row['login'],$row['started_on'],$row['ended_on']);			
		}
		$this->display_module($gb);		
	}
	
	public function check_auction_title($a) {
		if(strlen(htmlspecialchars($a))>50)
			return false;
		return true;
	}

	public function applet($conf,$opts) {
		$opts['title'] = 'Allegro - ostatnio zakończone aukcje';
		$opts['go'] = true;
		
		$gb = & $this->init_module('Utils/GenericBrowser',null,'t1');
		$gb->set_table_columns(array(
		array('name'=>'Przedmiot','width'=>50),
		array('name'=>'Koniec','width'=>50),
		));
		
		$query = 'SELECT a.item_id,a.ended_on FROM premium_ecommerce_allegro_auctions a WHERE a.active=0 AND (SELECT 1 FROM premium_ecommerce_allegro_auctions a2 WHERE a2.item_id=a.item_id AND a2.active=1 LIMIT 1) IS NULL AND ended_on is not null ORDER BY a.ended_on DESC LIMIT 10';
//		$query = 'SELECT a.item_id,a.ended_on FROM premium_ecommerce_allegro_auctions a WHERE a.active=0 AND ended_on is not null ORDER BY a.ended_on DESC LIMIT 10';
		
		$ret = DB::Execute($query);

		static $auction_forms;
		if(!isset($auction_forms)) $auction_forms = array();
		while($row=$ret->FetchRow()) {
			if(!isset($auction_forms[$row['item_id']])) {
				$auction_forms[$row['item_id']] = 1;
				$this->new_auction_form(Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$row['item_id']));
			}
			
			$gb_row = $gb->get_new_row();
			$gb_row->add_data('<a '.Utils_RecordBrowserCommon::create_record_href('premium_warehouse_items', $row['item_id'], 'view',array('switch_to_addon'=>'Allegro')).'>'.Premium_Warehouse_ItemsCommon::display_item_name($row['item_id'],true).'</a>',$row['ended_on']);			
			$gb_row->add_action(Libs_LeightboxCommon::get_open_href('new_auction_leightbox_'.$row['item_id']),'Edit');
		}
		$this->display_module($gb);
	}
	
	public function admin() {
		if($this->is_back()) {
			if($this->parent->get_type()=='Base_Admin')
				$this->parent->reset();
			else
				location(array());
			return;
		}
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

   		$rule = array(array('message'=>'Field required', 'type'=>'required'));
   		$rule_pr = array(array('message'=>'Field required', 'type'=>'required'),array('type'=>'regex', 'message'=>'Nieprawidłowa cena','param'=>'/^[1-9][0-9]*(\.[0-9]+)?$/'));
   		$settings = array();
   		$countries = array();
		$countries[1] = 'Polska';
		$countries[228] = 'Neverland (webapi test)';
		$states = explode('|','--|dolnośląskie|kujawsko-pomorskie|lubelskie|lubuskie|łódzkie|małopolskie|mazowieckie|opolskie|podkarpackie|podlaskie|pomorskie|śląskie|świętokrzyskie|warmińsko-mazurskie|wielkopolskie|zachodniopomorskie');
   		
   		$settings[] = array('name'=>'key','label'=>'Klucz WEBAPI','type'=>'text','default'=>'','rule'=>$rule);
		$settings[] = array('name'=>'login','label'=>'Login','type'=>'text','default'=>'','rule'=>$rule);
   		$settings[] = array('name'=>'pass','label'=>'Hasło','type'=>'password','default'=>'');
   		$settings[] = array('name'=>'country','label'=>'Kraj','type'=>'select','values'=>$countries,'default'=>1,'rule'=>$rule);
   		$settings[] = array('name'=>'state','label'=>'Województwo','type'=>'select','values'=>$states,'default'=>0,'rule'=>$rule);
   		$settings[] = array('name'=>'city','label'=>'Miasto','type'=>'text','default'=>'','rule'=>$rule);
   		$settings[] = array('name'=>'postal_code','label'=>'Kod pocztowy','type'=>'text','default'=>'','rule'=>$rule);
   		$settings[] = array('name'=>'fvat','label'=>'Faktura VAT','type'=>'checkbox','default'=>1);
   		$settings[] = array('name'=>'transport_description','label'=>'Dodatkowe informacje o przesyłce i płatności','type'=>'textarea','default'=>'');
   		$settings[] = array('name'=>'template','label'=>'Szablon','type'=>'select','values'=>Premium_Warehouse_eCommerce_AllegroCommon::get_templates(),'default'=>'');
   		
   		$f = $this->init_module('Libs/QuickForm');
   		$f->add_array($settings);
   		foreach($settings as $s)
       		$f->setDefaults(array($s['name']=>Variable::get('allegro_'.$s['name'],false)));
   		
   		if($f->validate()) {
   		    $vars = $f->exportValues();
       		foreach($settings as $s)
       		    Variable::set('allegro_'.$s['name'],isset($vars[$s['name']])?$vars[$s['name']]:'');
   		    $this->parent->reset();
   		}
   		
   		Base_ActionBarCommon::add('save',__('Save'),$f->get_submit_form_href());
   		$this->display_module($f);
	}

}

?>