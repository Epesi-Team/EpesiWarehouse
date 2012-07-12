<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_WebcamCommon extends ModuleCommon {

    public static function get_photos($id) {
        return glob(DATA_DIR.'/Premium_Warehouse_Items_Webcam/'.session_id().'/'.CID.'/'.md5($id).'/*.jpg');
    }

    public static function attach_webcam_button($values,$mode=null) {
        if(($mode==null||$mode=='view') && Utils_RecordBrowserCommon::get_access('premium_warehouse_items','edit',$values)) {
            if(!is_array($values)) $values = array('id'=>md5($values));
            Libs_LeightboxCommon::display('webcam','<div style="text-align:center; padding: 20px; width:100%"><object	classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
    width="512"
	height="414"
	id="webcam_applet"
	align="middle">
<param name="movie" value="modules/Premium/Warehouse/Items/Webcam/src/video.swf?cid='.CID.'&rid='.$values['id'].'"/>
<param name="allowScriptAccess" value="always" />
<param name="quality" value="high" />
<param name="scale" value="noscale" />
<param name="salign" value="ct" />
<param name="bgcolor" value="#ffffff"/>
<embed src="modules/Premium/Warehouse/Items/Webcam/src/video.swf?cid='.CID.'&rid='.$values['id'].'"
       bgcolor="#ffffff"
       width="512"
       height="414"
       name="webcam_applet"
       quality="high"
       align="middle"
       allowScriptAccess="always"
       type="application/x-shockwave-flash"
       pluginspage="http://www.macromedia.com/go/getflashplayer"
/>
</object><a style="margin:20px auto 0px auto;width:512px;padding:10px;display:block;border:1px solid gray;font-size:16px;background-color:#EEEEEE" href="javascript:leightbox_deactivate(\'webcam\')">'.__('Close').'</a></div>','Webcam',1);
        
			$icon = Base_ThemeCommon::get_template_file('Premium_Warehouse_Items_Webcam','photo.png');
			$label = __('Webcam');
			Base_ActionBarCommon::add($icon,$label,Libs_LeightboxCommon::get_open_href('webcam'));
        }
    }
}
?>
