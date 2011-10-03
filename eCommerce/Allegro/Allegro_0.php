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
		if(count($this->photos)==8) break;
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
		$qf->addRule('title',$this->t('Field required'),'required');
		if(isset($desc['display_name']))
			$qf->setDefaults(array('title'=>$desc['display_name']));
		else
			$qf->setDefaults(array('title'=>$r['item_name']));
		
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
				$qf->setDefaults(array('buy_price'=>$r['net_price']*(100+Data_TaxRatesCommon::get_tax_rate($r['tax_rate']))/100));
		}

		$qf->addElement('select','transport',$this->t('Transport'),array('Sprzedający pokrywa koszty transportu','Kupujący pokrywa koszty transportu'));
		$qf->addRule('transport',$this->t('Field required'),'required');
		$qf->setDefaults(array('transport'=>1));

		$qf->addElement('checkbox','abroad',$this->t('Zgadzam się na wysyłkę za granicę'));
		
		$qf->addElement('text','post_service_price',$this->t('Cena paczki'));
		$qf->addRule('post_service_price',$this->t('Invalid price'),'regex','/^[1-9][0-9]*(\.[0-9]+)?$/');
		$qf->setDefaults(array('post_service_price'=>Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','post_service_price')));
		$qf->addElement('textarea','transport_description','Dodatkowe informacje o przesyłce i płatności');
		$transport_description = trim(Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','transport_description'));
		if($transport_description)
			$qf->setDefaults(array('transport_description'=>$transport_description));

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
		        'fvalue-float' => $vals['minimal_price'],
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
		        'fvalue-float' => $vals['buy_price'],
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
			
			$description = (isset($desc['long_description']) && $desc['long_description']?$desc['long_description']:(isset($desc['short_description']) && $desc['short_description']?$desc['short_description']:$r['description']));
			if($vals['template'])
				$description = str_replace('{$description}',$description,file_get_contents($vals['template']));
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
			if(isset($vals['post_service_price']) && $vals['post_service_price'])
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
			
			if(isset($vals['publish']) && $vals['publish']) { 
				$a->new_auction($fields);
				eval_js('$("allegro_publish").value=0;');
				$err = $a->error();
				if($err)
					Epesi::alert($err);
				else 
					Epesi::alert('Aukcja została dodana.');
			} else {
				$ret = $a->check_new_auction_price($fields);
				$err = $a->error();
				if($err)
					Epesi::alert($err);
				elseif(isset($ret['item-price']) && isset($ret['item-price-desc']))
					eval_js('if(confirm("Opłata łączna: '.Epesi::escapeJS($ret['item-price'],true,false).'\n'.Epesi::escapeJS($ret['item-price-desc'],true,false).'")) {$("allegro_publish").value=1;'.$qf->get_submit_form_js(true,'publikuję aukcję',true).'}');
				else
					Epesi::alert('Nie można pobrać kosztu wystawienia aukcji');
			}
			
			foreach($this->photos as $ph)
				@unlink($ph);
			
		}
		
		$this->display_module($qf);
	}

}

?>