<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // date in the past
define('CID',false); //i know that i won't access $_SESSION['client']
define('SET_SESSION',false);
require_once('../../../../../include.php');
$old_user = Acl::get_user();
if(!$old_user) Acl::set_user(1);

ModuleManager::load_modules();
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    blank_img();
    if(!$old_user) Acl::set_user();
    die();
}

$auctions = Premium_Warehouse_eCommerce_AllegroCommon::get_other_auctions($_GET['id'],$_GET['i']!=0);
if(!isset($auctions[$_GET['i']]))  {
    blank_img();
    if(!$old_user) Acl::set_user();
    die();
}

$photo = null;
Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_products/'.$auctions[$_GET['i']]['item'],'collect_photos');
Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_descriptions/pl/'.$auctions[$_GET['i']]['item'],'collect_photos');

header("Content-type: image/jpeg");
$im = imagecreatetruecolor(240, 240);
$white = imagecolorallocate($im, 255, 255, 255);
imagefill($im, 0, 0, $white); 
$black = imagecolorallocate($im, 5, 5, 5);
$gray = imagecolorallocate($im, 155, 155, 155);
imagerectangle($im, 0, 0, 239, 239, $gray);
$is_photo = false;
if($photo!==null) {
    $ph = imagecreatefromjpeg($photo); 
    if($ph) {
	list($width,$height,$type,$attr) = getimagesize($photo);
	if($width>$height) {
		$max_dim = 238;
	}else {
		$max_dim = 210;
	}
	if($height > $max_dim || $width > $max_dim) {
	    if($height < $width) {
		$thumb_width = $max_dim;
		$thumb_height = $height * ( $max_dim / $width );
	    } else if($width < $height) {
		$thumb_width = $width * ( $max_dim / $height );
		$thumb_height = $max_dim;
	    } else {
		$thumb_width = $max_dim;
		$thumb_height = $max_dim;
	    }
	} else {
	    $thumb_width = $width;
	    $thumb_height = $height;
	}
	imagecopyresampled($im, $ph, (240-$thumb_width)/2, 216-$thumb_height, 0, 0, $thumb_width, $thumb_height, $width, $height);
	$is_photo = true;
    }
}
if(!$is_photo) {
    $red = imagecolorallocate($im, 255, 5, 5);
    imagettftext($im, 150, 0, 50, 200, $red, '/usr/share/fonts/truetype/ttf-dejavu/DejaVuSans.ttf', '?');
}
$desc = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$auctions[$_GET['i']]['item'],'language'=>'pl'));
if($desc) {
    $desc = array_shift($desc);
    $title = trim($desc['display_name']);
}
if(!isset($title) || !$title) {
    $desc = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$auctions[$_GET['i']]['item']);
    $title = trim($desc['item_name']);
}

$text = (strlen($title)>24)?substr($title,0,21).'...':$title;
imagettftext($im, 12, 0, 5+(24-strlen($text))*5, 235, $black, '/usr/share/fonts/truetype/ttf-dejavu/DejaVuSansMono.ttf', $text);
imagejpeg($im);
imagedestroy($im);
if(!$old_user) Acl::set_user();


function collect_photos($id,$rev,$file,$original,$args=null) {
	global $photo;
	if($photo!=null) return;
	$ext = strrchr($original,'.');
        if(preg_match('/^\.(jpg|jpeg)$/i',$ext)) {
            $photo = $file;
        }
}

function blank_img() {
    header("Content-type: image/jpeg");
    $im = imagecreate(240, 240);
    imagecolorallocate($im, 255, 255, 255);
    imagejpeg($im);
    imagedestroy($im);
}