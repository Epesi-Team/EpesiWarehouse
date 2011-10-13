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
    	/* @var $a Allegro */
    	$a = self::get_lib();
    	$ret = $a->get_categories();
    	$cats = array();
    	$cats_with_ch = array();
    	foreach($ret['cats-list'] as $c) {
    		if($c->{'cat-parent'}==0) {
    			$cats[$c->{'cat-id'}] = $c->{'cat-name'};    			
    		} elseif(isset($cats[$c->{'cat-parent'}])) {
    			$cats[$c->{'cat-id'}] = $cats[$c->{'cat-parent'}].'&rarr;'.$c->{'cat-name'};
    			$cats_with_ch[$c->{'cat-parent'}] = $cats[$c->{'cat-parent'}];
    			unset($cats[$c->{'cat-parent'}]);
    		} elseif(isset($cats_with_ch[$c->{'cat-parent'}])) {
    			$cats[$c->{'cat-id'}] = $cats_with_ch[$c->{'cat-parent'}].'&rarr;'.$c->{'cat-name'};
    		} else {
    			$ret['cats-list'][] = $c;
    		}    		
    	}
    	$country = Base_User_SettingsCommon::get('Premium_Warehouse_eCommerce_Allegro','country');
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
    		DB::Execute('UPDATE premium_ecommerce_allegro_auction SET active=0 WHERE auction_id IN ('.implode(',',$close).')');
    	
    	if($ret['array-items-not-found'])
    		DB::GetCol('DELETE FROM premium_ecommerce_allegro_auctions WHERE active=0 AND auction_id IN ('.implode(',',$ret['array-items-not-found']).')');
    	if($ret['array-items-admin-killed'])
    		DB::GetCol('DELETE FROM premium_ecommerce_allegro_auctions WHERE active=0 AND auction_id IN ('.implode(',',$ret['array-items-admin-killed']).')');
    }
    
    public static function cron() {
    	$c = Variable::get('ecommerce_allegro_cats_up',0);
    	$t = time();
    	if($c+3600*48<$t) {
    		self::update_cats();
    		Variable::set('ecommerce_allegro_cats_up',$t);
    	}
    	self::update_statuses();
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
			$states = explode('|','dolnośląskie|kujawsko-pomorskie|lubelskie|lubuskie|łódzkie|małopolskie|mazowieckie|opolskie|podkarpackie|podlaskie|pomorskie|śląskie|świętokrzyskie|warmińsko-mazurskie|wielkopolskie|zachodniopomorskie');
    		
    		$settings[] = array('name'=>'key','label'=>'Klucz WEBAPI','type'=>'text','default'=>'','rule'=>$rule);
			$settings[] = array('name'=>'login','label'=>'Login','type'=>'text','default'=>'','rule'=>$rule);
    		$settings[] = array('name'=>'pass','label'=>'Hasło','type'=>'text','default'=>'','rule'=>$rule);
    		$settings[] = array('name'=>'country','label'=>'Kraj','type'=>'select','values'=>$countries,'default'=>1,'rule'=>$rule);
    		$settings[] = array('name'=>'state','label'=>'Województwo','type'=>'select','values'=>$states,'default'=>0,'rule'=>$rule);
    		$settings[] = array('name'=>'city','label'=>'Miasto','type'=>'text','default'=>'','rule'=>$rule);
    		$settings[] = array('name'=>'postal_code','label'=>'Kod pocztowy','type'=>'text','default'=>'','rule'=>$rule);
    		$settings[] = array('name'=>'fvat','label'=>'Faktura VAT','type'=>'checkbox','default'=>1);
    		$settings[] = array('name'=>'post_service_price','label'=>'Cena paczki','type'=>'text','default'=>'','rule'=>$rule_pr);
    		$settings[] = array('name'=>'transport_description','label'=>'Dodatkowe informacje o przesyłce i płatności','type'=>'textarea','default'=>'');
    		$settings[] = array('name'=>'template','label'=>'Szablon','type'=>'select','values'=>self::get_templates(),'default'=>'');
    		
    		return array('Allegro'=>$settings);
    	}
    	return array();
    }
}

?>