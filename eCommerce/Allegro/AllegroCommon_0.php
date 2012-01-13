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

class Premium_Warehouse_eCommerce_AllegroCommon extends ModuleCommon {
	public static function warehouse_item_addon_label($r, $rb) {
        $x = Utils_RecordBrowserCommon::get_records_count('premium_ecommerce_products',array('item_name'=>$r['id']));
        if (!$x || !Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','login')) return array('show'=>false);
        return array('label'=>'Allegro', 'show'=>true);
    }
    
    public static function update_cats() {
    	$country = Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','country');

    	/* @var $a Allegro */
    	$a = self::get_lib();
    	$fields = $a->get_sell_form_fields();
    	if(!isset($fields['sell-form-fields'])) return array();
    	$cats = array();
    	$stan = array();
    	$stan_old = DB::GetAssoc('SELECT cat_id,field_id FROM premium_ecommerce_allegro_stan WHERE country=%d',array($country));
    	
    	foreach($fields['sell-form-fields'] as $f) {
    	    if($f->{'sell-form-title'} == 'Stan') {
    		if(!isset($stan_old[$f->{'sell-form-cat'}]) || $stan_old[$f->{'sell-form-cat'}]!=$f->{'sell-form-id'})
			DB::Replace('premium_ecommerce_allegro_stan', array('field_id'=>$f->{'sell-form-id'},'cat_id'=>$f->{'sell-form-cat'},'country'=>$country), array('cat_id','country'));
	    	$stan[$f->{'sell-form-cat'}] = $f->{'sell-form-id'};
	    	unset($stan_old[$f->{'sell-form-cat'}]);
	    }
    	}

    	$ret = $a->get_categories();
    	if(!is_array($ret['cats-list'])) return array();
    	$cats = array();
    	$cats_with_ch = array();
    	foreach($ret['cats-list'] as $c) {
    		if($c->{'cat-parent'}==0) {
    			$cats[$c->{'cat-id'}] = $c->{'cat-name'};
    		} else {
    		    if(!isset($stan[$c->{'cat-id'}]) && isset($stan[$c->{'cat-parent'}])) {
		    	DB::Replace('premium_ecommerce_allegro_stan', array('field_id'=>$stan[$c->{'cat-parent'}],'cat_id'=>$c->{'cat-id'},'country'=>$country), array('cat_id','country'));
		    	$stan[$c->{'cat-id'}] = $stan[$c->{'cat-parent'}];
		    	unset($stan_old[$c->{'cat-id'}]);
    		    }
    		    if(isset($cats[$c->{'cat-parent'}])) {
    			$cats[$c->{'cat-id'}] = $cats[$c->{'cat-parent'}].'&rarr;'.$c->{'cat-name'};
    			$cats_with_ch[$c->{'cat-parent'}] = $cats[$c->{'cat-parent'}];
    			unset($cats[$c->{'cat-parent'}]);
    		    } elseif(isset($cats_with_ch[$c->{'cat-parent'}])) {
    			$cats[$c->{'cat-id'}] = $cats_with_ch[$c->{'cat-parent'}].'&rarr;'.$c->{'cat-name'};
    		    } else {
    			$ret['cats-list'][] = $c;
    		    }
    		}
    	}
    	if($stan_old)
    		DB::Execute('DELETE FROM premium_ecommerce_allegro_stan WHERE country=%d AND cat_id IN ('.implode(',',array_keys($stan_old)).')',array($country));
    	foreach($cats as $k=>$v)
	    	DB::Replace('premium_ecommerce_allegro_cats', array('id'=>$k,'name'=>DB::qstr($v),'country'=>$country), array('id','country'));
    	return $cats;
    }
    
    public static function update_statuses() {
    	$a = self::get_lib();
    	$ids = DB::GetCol('SELECT auction_id FROM premium_ecommerce_allegro_auctions WHERE active=1');
    	$ret = $a->get_auctions_info($ids);
    	$close = array();
    	foreach($ret['array-item-list-info'] as $it) {
    		if($it->{'item-info'}->{'it-ending-info'}>1)
    			$close[] = $it->{'item-info'}->{'it-id'};
    	}
    	if($close)    	
    		DB::Execute('UPDATE premium_ecommerce_allegro_auctions SET active=0,ended_on=%T WHERE auction_id IN ('.implode(',',$close).')',array(time()));
    	
    	if($ret['array-items-not-found'])
    		DB::GetCol('DELETE FROM premium_ecommerce_allegro_auctions WHERE active=0 AND auction_id IN ('.implode(',',$ret['array-items-not-found']).')');
    	if($ret['array-items-admin-killed'])
    		DB::GetCol('DELETE FROM premium_ecommerce_allegro_auctions WHERE active=0 AND auction_id IN ('.implode(',',$ret['array-items-admin-killed']).')');
    }
    
    public static function cron() {
	$us = Acl::get_user();
	Acl::set_user(2);
    	$c = Variable::get('ecommerce_allegro_cats_up',0);
    	$t = time();
    	if($c+3600*24*3<$t || true) {
//    		print("up cats\n");
    		if(self::update_cats())
    			Variable::set('ecommerce_allegro_cats_up',$t);
    	}
    	self::update_statuses();
	DB::Execute('DELETE FROM premium_ecommerce_allegro_cross WHERE created_on<%T',array(time()-3600*12));
	Acl::set_user($us);
    }
    
    public static function get_lib() {
    	static $a;
    	if($a===null) {
    		require_once('modules/Premium/Warehouse/eCommerce/Allegro/allegro.php');
    		$a = new Allegro(Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','login'),
    			Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','pass'),
    			Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','country'),
    			Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','key'));
    		if($a->error()) trigger_error($a->error(),E_USER_ERROR);
    	}
    	return $a;
    }
    
    public static function get_templates() {
		$templates = array(''=>'---');
		$dd = self::Instance()->get_data_dir();
		foreach(scandir($dd) as $d) {
			if(!preg_match('/\.tpl$/i',$d)) continue;
			$templates[$dd.$d] = $d;
		}
		return $templates;    	
    }
    
    public static function user_settings(){
    	if(self::Instance()->acl_check('settings')) {
    		$rule = array(array('message'=>'Field required', 'type'=>'required'));
    		$rule_pr = array(array('message'=>'Field required', 'type'=>'required'),array('type'=>'regex', 'message'=>'Nieprawidłowa cena','param'=>'/^[1-9][0-9]*(\.[0-9]+)?$/'));
    		$settings = array();
    		$countries = array();
			$countries[1] = 'Polska';
			$countries[228] = 'Neverland (webapi test)';
			$states = explode('|','--|dolnośląskie|kujawsko-pomorskie|lubelskie|lubuskie|łódzkie|małopolskie|mazowieckie|opolskie|podkarpackie|podlaskie|pomorskie|śląskie|świętokrzyskie|warmińsko-mazurskie|wielkopolskie|zachodniopomorskie');
    		
    		$settings[] = array('name'=>'key','label'=>'Klucz WEBAPI','type'=>'text','default'=>'','rule'=>$rule);
			$settings[] = array('name'=>'login','label'=>'Login','type'=>'text','default'=>'','rule'=>$rule);
    		$settings[] = array('name'=>'pass','label'=>'Hasło','type'=>'text','default'=>'','rule'=>$rule);
    		$settings[] = array('name'=>'country','label'=>'Kraj','type'=>'select','values'=>$countries,'default'=>1,'rule'=>$rule);
    		$settings[] = array('name'=>'state','label'=>'Województwo','type'=>'select','values'=>$states,'default'=>0,'rule'=>$rule);
    		$settings[] = array('name'=>'city','label'=>'Miasto','type'=>'text','default'=>'','rule'=>$rule);
    		$settings[] = array('name'=>'postal_code','label'=>'Kod pocztowy','type'=>'text','default'=>'','rule'=>$rule);
    		$settings[] = array('name'=>'fvat','label'=>'Faktura VAT','type'=>'checkbox','default'=>1);
    		$settings[] = array('name'=>'transport_description','label'=>'Dodatkowe informacje o przesyłce i płatności','type'=>'textarea','default'=>'');
    		$settings[] = array('name'=>'template','label'=>'Szablon','type'=>'select','values'=>self::get_templates(),'default'=>'');
    		
    		return array('Allegro'=>$settings);
    	}
    	return array();
    }
    
    public static function get_other_auctions($id,$force_cache = false) {
	$lock_dir = DATA_DIR.'/Premium_Warehouse_eCommerce_Allegro/lock';
	@mkdir($lock_dir);
	$lock_file = $lock_dir.'/'.$id.'_'.$_SERVER['REMOTE_ADDR'];
	if(file_exists($lock_file)) {
		$fp = fopen($lock_file, "w");
		flock($fp, LOCK_EX);
		fclose($fp);
	}
        $result = DB::GetAll('SELECT c.ret_item_id as item, c.ret_auction_id as auction,a.buy_price FROM premium_ecommerce_allegro_cross c INNER JOIN premium_ecommerce_allegro_auctions a ON a.auction_id=c.ret_auction_id AND a.item_id=c.ret_item_id  WHERE c.item_id=%d AND c.ip=%s ORDER BY c.position',array($id,$_SERVER['REMOTE_ADDR']));
	if($result) return $result;
	$fp = fopen($lock_file, "w");
	flock($fp, LOCK_EX);

	$result = array();
	$skip_item_ids = array($id);
	$items = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products',array('item_name'=>$id));
	foreach($items as $i) {
		foreach(array_merge($i['popup_products'],$i['related_products']) as $p) {
			$pp = Utils_RecordBrowserCommon::get_record('premium_ecommerce_products',$p,array('item_name'));
			$x = DB::GetRow('SELECT auction_id,buy_price FROM premium_ecommerce_allegro_auctions WHERE item_id=%d AND active=1',array($pp['item_name']));
			if($x) {
			    $result[] = array('auction'=>$x['auction_id'],'item'=>$pp['item_name'],'buy_price'=>$x['buy_price']);
			    $skip_item_ids[] = $pp['item_name'];
			}
		}
	}
        $products = DB::GetCol('SELECT or_det.f_item_name FROM premium_warehouse_items_orders_details_data_1 or_det 
			    WHERE or_det.f_item_name!=%d AND or_det.f_transaction_id IN 
			    (SELECT ord.f_transaction_id FROM premium_warehouse_items_orders_details_data_1 or_det2 
			    INNER JOIN premium_ecommerce_orders_data_1 ord ON ord.f_transaction_id=or_det2.f_transaction_id
			    WHERE ord.f_language="pl" AND or_det2.f_item_name=%d) GROUP BY or_det.f_item_name ORDER BY count(or_det.f_item_name) DESC LIMIT 9',array($id,$id));
	foreach($products as $p) {
		$pp = Utils_RecordBrowserCommon::get_record('premium_ecommerce_products',$p,array('item_name'));
		$x = DB::GetRow('SELECT auction_id,buy_price FROM premium_ecommerce_allegro_auctions WHERE item_id=%d AND active=1',array($pp['item_name']));
		if($x) {
			$result[] = array('auction'=>$x['auction_id'],'item'=>$pp['item_name'],'buy_price'=>$x['buy_price']);
			$skip_item_ids[] = $pp['item_name'];
		}
	}
	for($i=count($result); $i<9; $i++) {
	        $row = DB::GetRow('SELECT c.ret_item_id as item, c.ret_auction_id as auction,a.buy_price FROM premium_ecommerce_allegro_cross c INNER JOIN premium_ecommerce_allegro_auctions a ON a.auction_id=c.ret_auction_id AND a.item_id=c.ret_item_id  WHERE c.item_id=%d AND c.position=%d AND c.ip=%s ORDER BY c.position',array($id,$i,$_SERVER['REMOTE_ADDR']));
		if(!$row) {
			$row = DB::GetRow('SELECT auction_id as auction,item_id as item,buy_price FROM premium_ecommerce_allegro_auctions WHERE active=1 AND item_id NOT IN ('.implode(',',$skip_item_ids).') ORDER BY RAND()');
			if(!$row) break;
		}
		$skip_item_ids[] = $row['item'];
		$result[] = $row;
	}
	foreach($result as $pos => $row) {
		DB::Execute('INSERT INTO premium_ecommerce_allegro_cross(ret_item_id,ret_auction_id,item_id,position,ip) VALUES(%d,%d,%d,%d,%s)',array($row['item'],$row['auction'],$id,$pos,$_SERVER['REMOTE_ADDR']));
	}
	
	fclose($fp);
	@unlink($lock_file);
	return $result;
    }

	public static function allegro_filter($choice) {
		if ($choice=='__NULL__') return array();
		$ids = DB::GetCol('SELECT item_id FROM premium_ecommerce_allegro_auctions WHERE active=1');
		if($choice) return array('id'=>$ids);
		return array('!id'=>$ids);
	}


	public static function autoselect_category_search($arg=null, $id=null) {
		$country = Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','country');
		$cats = DB::GetOne('SELECT 1 FROM premium_ecommerce_allegro_cats WHERE country=%d',array($country));
		if(!$cats)
			Premium_Warehouse_eCommerce_AllegroCommon::update_cats();
			
		$args = explode(' ',$arg);
		$wh = '';
		foreach($args as $aaa) {
			$wh .= ' AND name LIKE CONCAT("%%",'.DB::qstr($aaa).',"%%")';
		}
		$cats = DB::GetAssoc('SELECT id,name FROM premium_ecommerce_allegro_cats WHERE country=%d'.$wh.' ORDER BY name',array($country));
		return $cats;
	}

	public static function autoselect_category_format($arg=null) {
		$country = Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','country');
		$emps = DB::GetOne('SELECT name FROM premium_ecommerce_allegro_cats WHERE country=%d AND id=%d',array($country,$arg));
		return $emps;
	}


	public static function applet_caption() {
		return "Allegro";
	}

	public static function applet_info() {
		return "Ostatnio zakończone aukcje na allegro";
	}
}

?>