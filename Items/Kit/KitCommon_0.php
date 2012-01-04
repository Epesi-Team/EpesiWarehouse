<?php
/**
 * 
 * @author pbukowski@telaxus.com
 * @copyright Telaxus LLC
 * @license Commercial
 * @version 0.1
 * @package epesi-Premium/Warehouse/Items/
 * @subpackage Kit
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_KitCommon extends ModuleCommon {

    public static function QFfield_kit_items(&$form, $field, $label, $mode, $default,$args, $rb) {
        if(isset($rb->record['item_type']) && $rb->record['item_type']=='kit') {
		if ($mode=='edit' || $mode=='add') {
			$form->addElement('automulti', $field, $label, array('Premium_Warehouse_Items_KitCommon', 'automulti_search'), array(), array('Premium_Warehouse_Items_KitCommon', 'automulti_format'));
			$form->setDefaults(array($field=>$default));
			$form->addRule($field,Base_LangCommon::ts('Utils_RecordBrowser','Field required'),'required');
		} else {
			$def = array();
			foreach ($default as $d) {
				$next = self::automulti_format($d);
				if($next)
					$def[] = $next;
			}
			$form->addElement('static', $field, $label, implode('<br/>',$def));
		}
	}
    }

    public static function automulti_search($arg) {
	$arg = DB::Concat(DB::qstr('%'),DB::qstr($arg),DB::qstr('%'));
	$cats = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array('(~"item_name'=>$arg,'|~"sku'=>$arg),array('item_name'),array('item_name'=>'ASC'),10);
	$ret = array();
    	foreach($cats as $c) {
    		$ret[$c['id']] = $c['item_name'];
    	}
    	return $ret;
    }
    
    public static function automulti_format($id) {
	$dist = Premium_Warehouse_WholesaleCommon::get_distributor_qty($id);
        return Utils_TooltipCommon::create(Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items','item_name',$id),'On stock: '.Premium_Warehouse_Items_LocationCommon::get_item_quantity_in_warehouse($id).($dist['qty']?'<br />Distributor: '.$dist['qty']:''));
    }

    public static function access_items($action, $param=null){
	$ret = Premium_Warehouse_Items_OrdersCommon::access_items($action,$param);
	static $first;
	if(!isset($first)) $first = $action;
	switch ($action) {
		case 'browse_crits':
		    if($first=='browse')
			Base_ActionBarCommon::add('add','Add kit',Utils_RecordBrowserCommon::create_new_record_href('premium_warehouse_items',array('quantity_on_hand'=>'0','reorder_point'=>'0','weight'=>1,'item_type'=>'kit')));
		    return $ret;
	}
	return $ret;
    }
    
    public static function submit_items($p,$mode) {
	static $up = null;
	if($up!=null) return;
	if(!isset($p['item_type']) || $p['item_type']!='kit') return;
	switch($mode) {
	    case 'added':
	    case 'edit':
		$up = true;
		self::process_kit($p);
		break;
	}
    }

    private static $photos;
    private static $photo_newest;
    public static function collect_photos($id,$rev,$file,$original,$args=null,$created_on) {
	if(count(self::$photos)==1) return;
	    $ext = strrchr($original,'.');
        if(preg_match('/^\.(jpg|jpeg)$/i',$ext)) {
            self::$photos[] = $file;
            if(self::$photo_newest<$created_on)
        	self::$photo_newest = $created_on;
        }
    }
    
    public static function process_kit($kit) {
            $av = 10000;
            $sum_price = array();
            $taxes = array();
            $sum_desc = array();
    	    $photos = array();
    	    self::$photo_newest = 0;
            foreach($kit['kit_items'] as $item) {
		self::$photos = array();
		Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_products/'.$item,array('Premium_Warehouse_Items_KitCommon','collect_photos'));
		Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_descriptions/pl/'.$item,array('Premium_Warehouse_Items_KitCommon','collect_photos'));
		$photos = array_merge($photos,self::$photos);
                $item_arr = Utils_RecordBrowserCommon::get_record('premium_warehouse_items', $item, array('item_type','item_name'));
                $desc = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions', array('item_name'=>$item),array('short_description','language','display_name'));
                foreach($desc as $dd) {
                    if(!isset($sum_desc[$dd['language']])) $sum_desc[$dd['language']] = array();
                    $sum_desc[$dd['language']][] = '<b>'.($dd['display_name']?$dd['display_name']:$item_arr['item_name']).'</b>: '.$dd['short_description'];
                }
                if($item_arr['item_type']==0 || $item_arr['item_type']==1 || $item_arr['item_type']=='kit') {
		    $dist = Premium_Warehouse_WholesaleCommon::get_distributor_qty($item);
                    $av = min($av,Premium_Warehouse_Items_LocationCommon::get_item_quantity_in_warehouse($item)+$dist['qty']);
                }
                $prices = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$item));
                foreach($prices as $price) {
                    if(!isset($sum_price[$price['currency']])) {
                        $sum_price[$price['currency']] = 0;
                        $taxes[$price['currency']] = array();
                    }
                    $sum_price[$price['currency']] += $price['gross_price'];
                    $taxes[$price['currency']][$price['tax_rate']]=1;
                }
            }
            
	    $old_photos = Utils_AttachmentCommon::get('premium_ecommerce_products/'.$kit['id']);
	    $update_photos = false;
	    $num_old = 0;
	    $old_ids = array();
	    foreach($old_photos as $ph) {
		if(preg_match('/^kit\_([0-9]+)\.jpg$/i',$ph['original'])) {
		    $num_old++;
		    $old_ids[] = $ph['id'];
		    if(self::$photo_newest>$ph['upload_on'])
			$update_photos = true;
		}
	    }
            $photos = array_unique($photos);
	    if($num_old!=count($photos)+1) $update_photos = true;
	    if($update_photos) {
		Utils_AttachmentCommon::persistent_mass_delete('premium_ecommerce_products/'.$kit['id'],true,$old_ids);
                if(count($photos)<=1) {
		    $photo_size = 640;
    	        }elseif(count($photos)<=4) {
        	    $photo_size = 320;
    		}elseif(count($photos)<=9) {
        	    $photo_size = 200;
    		}elseif(count($photos)<=36) {
        	    $photo_size = 100;
    		}else
        	    $photo_size = 50;
		$im = imagecreatetruecolor(640, 640);
		$white = imagecolorallocate($im, 255, 255, 255);
		imagefill($im, 0, 0, $white); 
        	foreach($photos as $i=>$ph) {
	    	    $orig = imagecreatefromjpeg($ph); 
	            if(!$orig) continue;
        	    //$th1 = Utils_ImageCommon::create_thumb($ph,$photo_size,$photo_size);
		    list($width,$height,$type,$attr) = getimagesize($ph);
		    if($height > $photo_size || $width > $photo_size) {
			if($height < $width) {
			    $thumb_width = $photo_size;
			    $thumb_height = $height * ( $photo_size / $width );
			} else if($width < $height) {
			    $thumb_width = $width * ( $photo_size / $height );
			    $thumb_height = $photo_size;
			} else {
			    $thumb_width = $photo_size;
			    $thumb_height = $photo_size;
			}
        	    } else {
        		$thumb_width = $width;
        		$thumb_height = $height;
        	    }
		    imagecopyresampled($im, $orig, ($photo_size*$i)%640, ($photo_size*floor($i*$photo_size/640)), 0, 0, $thumb_width, $thumb_height, $width, $height);
        	}
        	self::Instance()->create_data_dir();
        	$thumbs = DATA_DIR.'/Premium_Warehouse_Items_Kit/'.md5(microtime(true).print_r($kit,true)).'.jpg';
		imagejpeg($im,$thumbs,90);
		imagedestroy($im);
		Utils_AttachmentCommon::add('premium_ecommerce_products/'.$kit['id'],2,Acl::get_user(),'','kit_0.jpg',$thumbs,null,null,array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		$tmp = rtrim(sys_get_temp_dir(),'/\\');
		foreach($photos as $i=>$ph) {
		    $d = $tmp.'/'.basename($ph);
		    copy($ph,$d);
		    Utils_AttachmentCommon::add('premium_ecommerce_products/'.$kit['id'],2,Acl::get_user(),'','kit_'.($i+1).'.jpg',$d,null,null,array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		}
	    }
            
            foreach($sum_desc as $lang=>$text) {
        	$text = implode('<br />',$text);
                $to_up = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$kit['id'],'language'=>$lang),array('id'));
                if($to_up) {
                    foreach($to_up as $up)
                        Utils_RecordBrowserCommon::update_record('premium_ecommerce_descriptions',$up['id'],array('short_description'=>$text));
                } else {
                    Utils_RecordBrowserCommon::new_record('premium_ecommerce_descriptions',array('short_description'=>$text,'item_name'=>$kit['id'],'language'=>$lang));
                }
            }
            foreach($sum_price as $curr2=>$price) {
                if(count($taxes[$curr2])!==1)  {
                    $to_del = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$kit['id'],'currency'=>$curr2,'auto_update'=>1),array('id'));
                    foreach($to_del as $del)
                        Utils_RecordBrowserCommon::delete_record('premium_ecommerce_prices',$del['id']);
                    continue;
                }
                $to_up = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$kit['id'],'currency'=>$curr2),array('id'));
                if($to_up) {
                    foreach($to_up as $up)
                        Utils_RecordBrowserCommon::update_record('premium_ecommerce_prices',$up['id'],array('gross_price'=>$price));
                } else {
                    $tax = array_keys($taxes[$curr2]);
                    Utils_RecordBrowserCommon::new_record('premium_ecommerce_prices',array('gross_price'=>$price,'item_name'=>$kit['id'],'tax_rate'=>$tax[0]));
                }
            }
            Utils_RecordBrowserCommon::update_record('premium_warehouse_items',$kit['id'],array('quantity_on_hand'=>$av));
    }

    public static function cron() {
        $kits = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array('item_type'=>'kit'),array('kit_items'));
        foreach($kits as $kit) {
    	    self::process_kit($kit);
        }
    }

    public static function QFfield_item_quantity(&$form, $field, $label, $mode, $default) {
		$form->addElement('static', $field, $label, self::display_item_quantity(Utils_RecordBrowser::$last_record, false));
    }

    public static function display_item_quantity($r, $nolink=false) {
	        if($r['item_type']=='kit') 
	            return $r['quantity_on_hand'];
	        return Premium_Warehouse_Items_LocationCommon::display_item_quantity($r,$nolink);
    }
}


?>