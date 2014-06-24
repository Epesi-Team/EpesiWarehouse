<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // date in the past
define('CID',false); //i know that i won't access $_SESSION['client']
define('SET_SESSION',false);
require_once('../../../../../include.php');
$old_user = Acl::get_user();
if(!$old_user) Acl::set_sa_user();

ModuleManager::load_modules();
if(!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['w']) || !is_numeric($_GET['w'])) {
    blank_img();
    if(!$old_user) Acl::set_user();
    die();
}

$photos = array();
Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_products/'.$_GET['id'],'collect_photos');
Utils_AttachmentCommon::call_user_func_on_file('premium_ecommerce_descriptions/pl/'.$_GET['id'],'collect_photos');

if(!$photos) {
    blank_img();
} else {
    header("Content-Type: image/gif");
    $cmd = 'convert -loop 0 +antialias -delay 1 -size '.$_GET['w'].'x'.$_GET['w'].' xc:white -delay 300 '.implode(' -delay 1 -size '.$_GET['w'].'x'.$_GET['w'].' xc:white -delay 300 ',$photos).' gif:-';
    $fp = popen ( $cmd , 'r' );
    fpassthru($fp);
    fclose($fp);
    foreach($photos as $ph) {
	@unlink($ph);
    }
}
if(!$old_user) Acl::set_user();


function collect_photos($id,$rev,$file,$original,$args=null) {
	global $photos;
	if(count($photos)>=4) return;
	$ext = strrchr($original,'.');
        if(preg_match('/^\.(jpg|jpeg)$/i',$ext)) {
            $th1 = Utils_ImageCommon::create_thumb($file,$_GET['w'],$_GET['w']);
            $photos[] = $th1['thumb'];
        }
}

function blank_img() {
    header("Content-type: image/jpeg");
    $im = imagecreate(1, 1);
    imagecolorallocate($im, 255, 255, 255);
    imagejpeg($im);
    imagedestroy($im);
}