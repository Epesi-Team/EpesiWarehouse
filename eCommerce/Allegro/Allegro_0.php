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
	
	}
	
	private $photos;
	public function collect_photos($id,$rev,$file,$original,$args=null) {
		if(count($this->photos)==1) return;
	    $ext = strrchr($original,'.');
        if(preg_match('/^\.(jpg|jpeg|gif|png|bmp)$/i',$ext)) {
            $th1 = Utils_ImageCommon::create_thumb($file,640,480);
            $this->photos[] = $th1['thumb'];
        }
	}
	
	public function warehouse_item_addon($r) {
		$desc = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$r['id'],'language'=>'pl'));
		if($desc) $desc = array_shift($desc);
		
		$country = Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','country');
		$cats = DB::GetAssoc('SELECT id,name FROM premium_ecommerce_allegro_cats WHERE country=%d ORDER BY name',array($country));
		if(empty($cats))
		$cats = Premium_Warehouse_eCommerce_AllegroCommon::update_cats();
		
		$qf = $this->init_module('Libs_QuickForm',null, 'allegro_p');
		
		$qf->addElement('text','title',$this->t('Tytuł'));
		$qf->addRule('title',$this->t('Field required'),'required',array('maxlength'=>50));
		$title = '';
		if(isset($desc['display_name']))
			$title=$desc['display_name'];
		else
			$title = $r['item_name'];
		$qf->setDefaults(array('title'=>substr(html_entity_decode($r['item_name']),0,40)));
		
		$qf->addElement('select','category',$this->t('Kategoria'),$cats);
		$qf->addRule('category',$this->t('Field required'),'required');
		
		$qf->addElement('select','days',$this->t('Dni'),array('3','5','7','10','14','30'));
		$qf->addRule('days',$this->t('Field required'),'required');
		$qf->setDefaults(array('days'=>4));
		
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
		
		$qf->addElement('select','transport',$this->t('Transport'),array('Sprzedający pokrywa koszty transportu','Kupujący pokrywa koszty transportu'));
		$qf->addRule('transport',$this->t('Field required'),'required');
		$qf->setDefaults(array('transport'=>1));
		
		$qf->addElement('checkbox','abroad',$this->t('Zgadzam się na wysyłkę za granicę'));
		
		$qf->addElement('text','post_service_price',$this->t('Cena paczki'));
		$qf->addRule('post_service_price',$this->t('Invalid price'),'regex','/^[1-9][0-9]*(\.[0-9]+)?$/');

		$qf->addElement('text','post_service_price_p',$this->t('Cena paczki (pobranie)'));
		$qf->addRule('post_service_price_p',$this->t('Invalid price'),'regex','/^[1-9][0-9]*(\.[0-9]+)?$/');

		$qf->addElement('text','ups_price',$this->t('Cena kuriera'));
		$qf->addRule('ups_price',$this->t('Invalid price'),'regex','/^[1-9][0-9]*(\.[0-9]+)?$/');

		$qf->addElement('text','ups_price_p',$this->t('Cena kuriera (pobranie)'));
		$qf->addRule('ups_price_p',$this->t('Invalid price'),'regex','/^[1-9][0-9]*(\.[0-9]+)?$/');

		$ship_cost = Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>1,'payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'))); //poczta polska, pobranie
		foreach($ship_cost as $sh) {
		    if($sh['max_weight']<$r['weight']) continue;
		    $ppp = $qf->exportValue('buy_price');
		    if(!$ppp) $ppp=0;
		    $qf->setDefaults(array('post_service_price_p'=>number_format($sh['price']+$sh['percentage_of_amount']*$ppp/100,2,'.','')));
		    break;
		}

		$ship_cost = Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>array(2,3,4,5,7,8,9,10),'payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'))); //poczta polska, pobranie
		foreach($ship_cost as $sh) {
		    if($sh['max_weight']<$r['weight']) continue;
		    $ppp = $qf->exportValue('buy_price');
		    if(!$ppp) $ppp=0;
		    $qf->setDefaults(array('ups_price_p'=>number_format($sh['price']+$sh['percentage_of_amount']*$ppp/100,2,'.','')));
		    break;
		}

		$ship_cost = Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>1,'!payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'))); //poczta polska, pobranie
		foreach($ship_cost as $sh) {
		    if($sh['max_weight']<$r['weight']) continue;
		    $ppp = $qf->exportValue('buy_price');
		    if(!$ppp) $ppp=0;
		    $qf->setDefaults(array('post_service_price'=>number_format($sh['price']+$sh['percentage_of_amount']*$ppp/100,2,'.','')));
		    break;
		}

		$ship_cost = Utils_RecordBrowserCommon::get_records('premium_ecommerce_payments_carriers',array('shipment'=>array(2,3,4,5,7,8,9,10),'!payment'=>9,'currency'=>Utils_CurrencyFieldCommon::get_id_by_code('PLN'))); //poczta polska, pobranie
		foreach($ship_cost as $sh) {
		    if($sh['max_weight']<$r['weight']) continue;
		    $ppp = $qf->exportValue('buy_price');
		    if(!$ppp) $ppp=0;
		    $qf->setDefaults(array('ups_price'=>number_format($sh['price']+$sh['percentage_of_amount']*$ppp/100,2,'.','')));
		    break;
		}
		$qf->addElement('textarea','transport_description','Dodatkowe informacje o przesyłce i płatności');
		$transport_description = trim(Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','transport_description'));
		if($transport_description)
		$qf->setDefaults(array('transport_description'=>$transport_description));

		$qf->addElement('checkbox','add_auction_cost',$this->t('Dodaj koszt aukcji'));
		$qf->setDefaults(array('add_auction_cost'=>1));
		
		$qf->addElement('header',null,'Wygląd aukcji');
		$qf->addElement('select','template',$this->t('Szablon'),Premium_Warehouse_eCommerce_AllegroCommon::get_templates());
		$qf->setDefaults(array('template'=>Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','template')));
		$qf->addElement('checkbox','pr_bold',$this->t('Pogrubienie'));
		$qf->addElement('checkbox','pr_thumbnail',$this->t('Miniaturka'));
		$qf->addElement('checkbox','pr_light',$this->t('Podświetlenie'));
		$qf->addElement('checkbox','pr_bigger',$this->t('Wyróżnienie'));
		$qf->addElement('checkbox','pr_catpage',$this->t('Strona kategorii'));
		$qf->addElement('checkbox','pr_mainpage',$this->t('Strona główna Allegro'));
		$qf->addElement('checkbox','pr_watermark',$this->t('Znak wodny'));
		
		$qf->addElement('hidden','publish',0,array('id'=>'allegro_publish'));
		
		$qf->addElement('submit','submit','Wystaw');
		
		if($qf->validate()) {
			$vals = $qf->exportValues();
			$a = Premium_Warehouse_eCommerce_AllegroCommon::get_lib();
			$this->photos = array();
			Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_products/'.$r['id'],array($this,'collect_photos'));
			Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_descriptions/pl/'.$r['id'],array($this,'collect_photos'));
			$fields = array();
			$fields[] =    array(
		      			'fid' => 1,   // Tytuł
		        		'fvalue-string' => $vals['title'],
				        'fvalue-int' => 0,
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
		      			'fid' => 2,   // Kategoria
		        		'fvalue-string' => '',
				        'fvalue-int' => $vals['category'],
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
				        'fid' => 3,   // Data rozpoczęcia
				        'fvalue-string' => '',
				        'fvalue-int' => time(),
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
				        'fid' => 4,   // Czas trwania
				        'fvalue-string' => '',
				        'fvalue-int' => $vals['days'],
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
				        'fid' => 5,   // Ilość sztuk
				        'fvalue-string' => '',
				        'fvalue-int' => $vals['qty'],
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
				        'fid' => 6,   // Cena wywoławcza
				        'fvalue-string' => '',
				        'fvalue-int' => 0,
				        'fvalue-float' => $vals['initial_price'],
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
				        'fid' => 7,   // Cena minimalna
				        'fvalue-string' => '',
				        'fvalue-int' => 0,
				        'fvalue-float' => $vals['minimal_price']?$vals['minimal_price']+((isset($vals['add_auction_cost']) && $vals['add_auction_cost'] && $this->isset_module_variable('auction_cost'))?$this->get_module_variable('auction_cost'):0):'',
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			
			$fields[] = array(
				        'fid' => 8,   // Cena kup teraz
				        'fvalue-string' => '',
				        'fvalue-int' => 0,
				        'fvalue-float' => $vals['buy_price']?$vals['buy_price']+((isset($vals['add_auction_cost']) && $vals['add_auction_cost'] && $this->isset_module_variable('auction_cost'))?$this->get_module_variable('auction_cost'):0):'',
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
				        'fid' => 9,   // Kraj
				        'fvalue-string' => '',
				        'fvalue-int' => Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','country'),
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
				        'fid' => 10,   // Województwo
				        'fvalue-string' => '',
				        'fvalue-int' => Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','country')==1?Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','state'):213,
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')			
			);
			$fields[] = array(
				        'fid' => 11,   // Miasto
				        'fvalue-string' => Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','city'),
				        'fvalue-int' => 0,
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')			
			);
			$fields[] = array(
				        'fid' => 32,   // Kod pocztowy
				        'fvalue-string' => Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','postal_code'),
				        'fvalue-int' => 0,
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')			
			);
			$fields[] = array(
				        'fid' => 12,   // Transport
				        'fvalue-string' => '',
				        'fvalue-int' => $vals['transport'],
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
				        'fid' => 13,   // Za granicę
				        'fvalue-string' => '',
				        'fvalue-int' => isset($vals['abroad']) && $vals['abroad']?32:0,
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
				        'fid' => 14,   // Formy płatności
				        'fvalue-string' => '',
				        'fvalue-int' => 1 + (Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','fvat')?(Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','country')==1?32:8):0),
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
				        'fid' => 15,   // promocja
				        'fvalue-string' => '',
				        'fvalue-int' => (isset($vals['pr_bold']) && $vals['pr_bold']?1:0)+(isset($vals['pr_thumbnail']) && $vals['pr_thumbnail']?2:0)
			+(isset($vals['pr_light']) && $vals['pr_light']?4:0)+(isset($vals['pr_bigger']) && $vals['pr_bigger']?8:0)
			+(isset($vals['pr_catpage']) && $vals['pr_catpage']?16:0)+(isset($vals['pr_mainpage']) && $vals['pr_mainpage']?32:0)
			+(isset($vals['pr_watermark']) && $vals['pr_watermark']?64:0),
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			foreach($this->photos as $i=>$ph) {
				$fields[] = array(
								        'fid' => 16+$i,   // Obrazek
								        'fvalue-string' => '',
								        'fvalue-int' => 0,
								        'fvalue-float' => 0,
								        'fvalue-image' => file_get_contents($ph),
								        'fvalue-datetime' => 0,
								        'fvalue-date' => '',
								        'fvalue-range-int' => array(
								                'fvalue-range-int-min' => 0,
								                'fvalue-range-int-max' => 0),
								        'fvalue-range-float' => array(
								                'fvalue-range-float-min' => 0,
								                'fvalue-range-float-max' => 0),
								        'fvalue-range-date' => array(
								                'fvalue-range-date-min' => '',
								                'fvalue-range-date-max' => '')
				);
			}
			$carriers = array();
			if(isset($vals['post_service_price']) && $vals['post_service_price']) {
				$fields[] = array(
					        'fid' => 36,   // Paczka pocztowa ekonomiczna
					        'fvalue-string' => '',
					        'fvalue-int' => 0,
					        'fvalue-float' => $vals['post_service_price'],
					        'fvalue-image' => 0,
					        'fvalue-datetime' => 0,
					        'fvalue-date' => '',
					        'fvalue-range-int' => array(
					                'fvalue-range-int-min' => 0,
					                'fvalue-range-int-max' => 0),
					        'fvalue-range-float' => array(
					                'fvalue-range-float-min' => 0,
					                'fvalue-range-float-max' => 0),
					        'fvalue-range-date' => array(
					                'fvalue-range-date-min' => '',
					                'fvalue-range-date-max' => '')
				);
				$carriers[] = 'Poczta Polska: '.$vals['post_service_price'].' zł';
			}
			if(isset($vals['post_service_price_p']) && $vals['post_service_price_p']) {
				$fields[] = array(
					        'fid' => 40,   // Paczka pocztowa ekonomiczna
					        'fvalue-string' => '',
					        'fvalue-int' => 0,
					        'fvalue-float' => $vals['post_service_price_p'],
					        'fvalue-image' => 0,
					        'fvalue-datetime' => 0,
					        'fvalue-date' => '',
					        'fvalue-range-int' => array(
					                'fvalue-range-int-min' => 0,
					                'fvalue-range-int-max' => 0),
					        'fvalue-range-float' => array(
					                'fvalue-range-float-min' => 0,
					                'fvalue-range-float-max' => 0),
					        'fvalue-range-date' => array(
					                'fvalue-range-date-min' => '',
					                'fvalue-range-date-max' => '')
				);
				$carriers[] = 'Poczta Polska (pobranie): '.$vals['post_service_price_p'].' zł';
			}
			if(isset($vals['ups_price']) && $vals['ups_price']) {
				$fields[] = array(
					        'fid' => 44,   // Paczka pocztowa ekonomiczna
					        'fvalue-string' => '',
					        'fvalue-int' => 0,
					        'fvalue-float' => $vals['ups_price'],
					        'fvalue-image' => 0,
					        'fvalue-datetime' => 0,
					        'fvalue-date' => '',
					        'fvalue-range-int' => array(
					                'fvalue-range-int-min' => 0,
					                'fvalue-range-int-max' => 0),
					        'fvalue-range-float' => array(
					                'fvalue-range-float-min' => 0,
					                'fvalue-range-float-max' => 0),
					        'fvalue-range-date' => array(
					                'fvalue-range-date-min' => '',
					                'fvalue-range-date-max' => '')
				);
				$carriers[] = 'Kurier: '.$vals['ups_price'].' zł';
			}
			if(isset($vals['ups_price_p']) && $vals['ups_price_p']) {
				$fields[] = array(
					        'fid' => 45,   // Paczka pocztowa ekonomiczna
					        'fvalue-string' => '',
					        'fvalue-int' => 0,
					        'fvalue-float' => $vals['ups_price_p'],
					        'fvalue-image' => 0,
					        'fvalue-datetime' => 0,
					        'fvalue-date' => '',
					        'fvalue-range-int' => array(
					                'fvalue-range-int-min' => 0,
					                'fvalue-range-int-max' => 0),
					        'fvalue-range-float' => array(
					                'fvalue-range-float-min' => 0,
					                'fvalue-range-float-max' => 0),
					        'fvalue-range-date' => array(
					                'fvalue-range-date-min' => '',
					                'fvalue-range-date-max' => '')
				);
				$carriers[] = 'Kurier (pobranie): '.$vals['ups_price_p'].' zł';
			}
				
			$description = (isset($desc['long_description']) && $desc['long_description']?$desc['long_description']:(isset($desc['short_description']) && $desc['short_description']?$desc['short_description']:$r['description']));
			$other_auctions = '<table border=0>';
			for($wiersz=0;$wiersz<3;$wiersz++) {
			    $other_auctions .= '<tr>';
			    for($kolumna=0; $kolumna<3; $kolumna++) 
				$other_auctions .= '<td><a target="_blank" href="'.get_epesi_url().'/modules/Premium/Warehouse/eCommerce/Allegro/redirect.php?id='.$r['id'].'&i='.($kolumna+$wiersz*3).'"><img src="'.get_epesi_url().'/modules/Premium/Warehouse/eCommerce/Allegro/img.php?id='.$r['id'].'&i='.($kolumna+$wiersz*3).'" border=0 /></a></td>';
			    $other_auctions .= '</tr>';
			}
			$other_auctions .= '</table>';
			$image = '<img src="'.get_epesi_url().'/modules/Premium/Warehouse/eCommerce/Allegro/imgs.php?id='.$r['id'].'&pos=0&w=240" border=0 />';
			$gallery = '';
			for($i=1; $i<5; $i++)
				$gallery .= '<img src="'.get_epesi_url().'/modules/Premium/Warehouse/eCommerce/Allegro/imgs.php?id='.$r['id'].'&pos='.$i.'&w=500" border=0 /><br />';
			$features = '<table id="features" cellspacing="1"><thead><tr><td colspan="3">Cechy</td></tr></thead><tbody>';
		        $parameters = array();
			$ret2 = DB::Execute('SELECT pp.f_item_name, pp.f_value,
									p.f_parameter_code as parameter_code,
									pl.f_label as parameter_label,
									g.f_group_code as group_code,
									gl.f_label as group_label
						FROM premium_ecommerce_products_parameters_data_1 pp
						INNER JOIN (premium_ecommerce_parameters_data_1 p,premium_ecommerce_parameter_groups_data_1 g) ON (p.id=pp.f_parameter AND g.id=pp.f_group)
						LEFT JOIN premium_ecommerce_parameter_labels_data_1 pl ON (pl.f_parameter=p.id AND pl.f_language="pl" AND pl.active=1)
						LEFT JOIN premium_ecommerce_param_group_labels_data_1 gl ON (gl.f_group=g.id AND gl.f_language="pl" AND gl.active=1)
						WHERE pp.active=1 AND pp.f_language="pl" AND pp.f_item_name=%d ORDER BY g.f_position,gl.f_label,g.f_group_code,p.f_position,pl.f_label,p.f_parameter_code',array($r['id']));
			$last_group = null;
			while($bExp = $ret2->FetchRow()) {
				$parameters[] = array('sGroup'=>($last_group!=$bExp['group_code']?($bExp['group_label']?$bExp['group_label']:$bExp['group_code']):''), 'sName'=>($bExp['parameter_label']?$bExp['parameter_label']:$bExp['parameter_code']), 'sValue'=>($bExp['f_value']=='Y'?'<span class="yes">Yes</span>':($bExp['f_value']=='N'?'<span class="no">No</span>':$bExp['f_value'])));
				if($last_group != $bExp['group_code']) {
    					$last_group = $bExp['group_code'];
				}
			}
			$row = DB::GetRow('SELECT it.f_sku,
					it.f_upc,
					it.f_product_code
					FROM premium_warehouse_items_data_1 it WHERE id=%d',array($r['id']));
			$parameters[] = array('sGroup'=>'Kody','sName'=>'SKU','sValue'=>$row['f_sku']);
			$parameters[] = array('sGroup'=>'','sName'=>'UPC','sValue'=>$row['f_upc']);
			$parameters[] = array('sGroup'=>'','sName'=>'Kod producenta','sValue'=>$row['f_product_code']);
			$i2=0;
			foreach($parameters as $aData) {
				$aData['iStyle'] = ( $i2 % 2 ) ? 0: 1;
				$features .= '<tr class="l'.$aData['iStyle'].'"><th>'.$aData['sGroup'].'</th><th>'.$aData['sName'].'</th><td>'.$aData['sValue'].'</td></tr>';
				$i2++;
			} // end for
			$features .= '</tbody></table>';
			if($vals['template'])
				$description = str_replace(array('{$description}','{$title}','{$shipping_cost}','{$other_auctions}','{$image}','{$gallery}','{$features}'),array($description,$vals['title'],implode('<br />',$carriers),$other_auctions,$image,$gallery,$features),file_get_contents($vals['template']));
			$fields[] = array(
				        'fid' => 24,   // Opis
				        'fvalue-string' => $description,
				        'fvalue-int' => 0,
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$transport_description = trim($vals['transport_description']);
			if($transport_description)
			$fields[] = array(
				        'fid' => 27,   // Dodatkowe info o przesyłce
				        'fvalue-string' => $transport_description,
				        'fvalue-int' => 0,
				        'fvalue-float' => 0,
				        'fvalue-image' => 0,
				        'fvalue-datetime' => 0,
				        'fvalue-date' => '',
				        'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				        'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				        'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
			$fields[] = array(
				         'fid' => 29,  // Format sprzedaży [Aukcja (z licytacją) lub Kup Teraz!]
				         'fvalue-string' => '',
				         'fvalue-int' => 0,
				         'fvalue-float' => 0,
				         'fvalue-image' => 0,
				         'fvalue-datetime' => 0,
				         'fvalue-date' => '',
				         'fvalue-range-int' => array(
				                'fvalue-range-int-min' => 0,
				                'fvalue-range-int-max' => 0),
				         'fvalue-range-float' => array(
				                'fvalue-range-float-min' => 0,
				                'fvalue-range-float-max' => 0),
				         'fvalue-range-date' => array(
				                'fvalue-range-date-min' => '',
				                'fvalue-range-date-max' => '')
			);
				
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
					DB::Execute('INSERT INTO premium_ecommerce_allegro_auctions (auction_id,item_id,created_by,started_on) VALUES(%d,%d,%d,%T)',array($ret['item-id'],$r['id'],Acl::get_user(),$ret['item-starting-time']));
				}
			} else {
				$ret = $a->check_new_auction_price($fields);
				$err = $a->error();
				$this->set_module_variable('auction_cost',null);
				if($err)
				    Epesi::alert($err);
				elseif(isset($ret['item-price']) && isset($ret['item-price-desc'])) {
				    $ret['item-price'] = str_replace(',','.',$ret['item-price']);
				    if(preg_match('/([\d]+(\.[\d]+)?)/', $ret['item-price'], $match)) {
					    $this->set_module_variable('auction_cost',(float)$match[0]);
				    }
				    eval_js('if(confirm("Opłata łączna: '.Epesi::escapeJS($ret['item-price'],true,false).'\n'.Epesi::escapeJS($ret['item-price-desc'],true,false).'")) {$("allegro_publish").value=1;'.$qf->get_submit_form_js(true,'publikuję aukcję',true).'}');
				}else
					Epesi::alert('Nie można pobrać kosztu wystawienia aukcji');
				Libs_LeightboxCommon::close('new_auction_leightbox');
			}
				
			foreach($this->photos as $ph)
				@unlink($ph);
				
		}
		
		Libs_LeightboxCommon::display('new_auction_leightbox',$this->get_html_of_module($qf),'Wystaw aukcję');
		
		
		$gb = & $this->init_module('Utils/GenericBrowser',null,'t1');
		print('<div style="text-align:left;padding:10px"><a '.Libs_LeightboxCommon::get_open_href('new_auction_leightbox').'><span class="search_button">Nowa aukcja</span></a></div>');
		$gb->set_table_columns(array(
		array('name'=>'Aukcja','width'=>50,'order'=>'a.auction_id'),
		array('name'=>'Aktywna','width'=>20,'order'=>'a.active'),
		array('name'=>'Stworzona przez','width'=>50,'order'=>'u.login'),
		array('name'=>'Start','width'=>50,'order'=>'a.started_on'),
		));
		$gb->set_default_order(array($this->t('Start')=>'DESC'));
		
		$search = $gb->get_search_query();
		$query = 'SELECT u.login, a.active,a.started_on,a.auction_id FROM premium_ecommerce_allegro_auctions a INNER JOIN user_login u ON u.id=a.created_by WHERE a.item_id='.$r['id'].($search?' AND '.$search:'');
		$query_qty = 'SELECT count(a.auction_id) FROM premium_ecommerce_allegro_auctions a INNER JOIN user_login u ON u.id=a.created_by WHERE a.item_id='.$r['id'].($search?' AND '.$search:'');
		
		$ret = $gb->query_order_limit($query, $query_qty);

		$on = '<span class="checkbox_on" />';
		$off = '<span class="checkbox_off" />';
		while(($row=$ret->FetchRow())) {
			$gb->add_row((Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','country')!=1?'<a target="_blank" href="http://testwebapi.pl/i'.$row['auction_id'].'.html">'.$row['auction_id'].'</a>':'<a href="http://allegro.pl/ShowItem2.php?item='.$row['auction_id'].'" target="_blank">'.$row['auction_id'].'</a>'),
				$row['active']?$on:$off,$row['login'],$row['started_on']);			
		}
		$this->display_module($gb);		
	}

}

?>