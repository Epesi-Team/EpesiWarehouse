<?php
define('CID',false); //i know that i won't access $_SESSION['client']
require_once('../../../../../include.php');
ModuleManager::load_modules();
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    blank_img();
    die();
}

$auctions = Premium_Warehouse_eCommerce_AllegroCommon::get_other_auctions($_GET['id']);
if(!isset($auctions[$_GET['i']]))  {
    blank_img();
    die();
}

$photos = array();
Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_products/'.$auctions[$_GET['i']]['item'],'collect_photos');
Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_descriptions/pl/'.$auctions[$_GET['i']]['item'],'collect_photos');
if($photos) {
    $photo = array_shift($photos);
    header("Contet-type: image/jpeg");
    print(file_get_contents($photo));
    unlink($photo);
}

function collect_photos($id,$rev,$file,$original,$args=null) {
	global $photos;
	if(count($photos)==1) break;
	$ext = strrchr($original,'.');
        if(preg_match('/^\.(jpg|jpeg)$/i',$ext)) {
            $th1 = Utils_ImageCommon::create_thumb($file,240,240);
            $photos[] = $th1['thumb'];
        }
}

function blank_img() {
    header("Contet-type: image/jpeg");
    $im = imagecreate(240, 240);
    imagecolorallocate($im, 255, 255, 255);
    imagejpeg($im);
    imagedestroy($im);
}