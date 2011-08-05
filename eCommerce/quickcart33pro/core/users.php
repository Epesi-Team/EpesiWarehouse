<?php
class Users
{

  function &getInstance( ){
    static $oInstance = null;
    if( !isset( $oInstance ) ){
      $oInstance = new Users( );
    }
    return $oInstance;
  } // end function getInstance
  
  function Users() {
  	global $aUser;
	$aUser = array();
	if(self::logged()) {
		$aUser = DB::GetRow('SELECT f_email as sEmail, f_last_name as sLastName, f_first_name as sFirstName, f_address_1 as sStreet, f_postal_code as sZipCode, f_city as sCity, f_country as sCountry, f_work_phone as sPhone, f_company_name, f_zone as sState FROM contact_data_1 WHERE id=%d',array($_SESSION['contact']));
		if(isset($_SESSION['company']) && $_SESSION['company']) {
			$aUser += DB::GetRow('SELECT f_company_name as sCompanyName, f_tax_id as sNip FROM company_data_1 WHERE id=%d',array($_SESSION['company']));
		}
	}
  }

  function login( $v ){
	global $config;

	$uid = DB::GetRow('SELECT e.id, e.f_password, c.id as cid, c.f_company_name FROM premium_ecommerce_users_data_1 e INNER JOIN contact_data_1 c ON c.id=e.f_contact WHERE c.f_email=%s AND e.active=1 AND c.active=1',array($v['sEmail']));
	if(!$uid) return false;
    
    $mdpass = md5($v['sPassword']);
    
    if(strlen($uid['f_password'])==35) { //OS Commerce
        $stack = explode(':', $uid['f_password']);
        if (sizeof($stack) == 2) {
           if (md5($stack[1] . $aForm['sPassword']) == $stack[0]) {
              $uid['f_password'] = $mdpass;
              DB::Execute('UPDATE premium_ecommerce_users_data_1 SET f_password=%s WHERE f_contact=%d',
     				array($mdpass,$uid['id']));
           }
        }
    }
	if($uid['f_password']!=$mdpass) return false;

	$oPage =& Pages::getInstance( );
      	
      	$_SESSION['user'] = $uid['id'];
      	$_SESSION['contact'] = $uid['cid'];
      	$company = explode('__',trim($uid['f_company_name'],'__'));
      	$_SESSION['company'] = array_shift($company);
      	if(!$_SESSION['company'] || !DB::GetOne('SELECT 1 FROM company_data_1 WHERE id=%d AND active=1',array($_SESSION['company'])))
      		$_SESSION['company'] = null;
  
        if( $_SESSION['iOrderQuantity'.LANGUAGE] && isset( $config['order_page'] ) && isset( $oPage->aPages[$config['order_page']] ) ){
          header( 'Location: '.REDIRECT.$oPage->aPages[$config['order_page']]['sLinkName'] );
        } else {
          header( 'Location: '.REDIRECT);
        }
        exit;
  } 

  function remind_password( $v , $sFile){
	global $config;
	global $lang;

	$uid = DB::GetOne('SELECT e.id FROM premium_ecommerce_users_data_1 e INNER JOIN contact_data_1 c ON c.id=e.f_contact WHERE c.f_email=%s AND e.active=1 AND c.active=1',array($v['sEmail']));
	if(!$uid) return false;

	$pass = substr(md5(microtime(true)),0,8);
	DB::Execute('UPDATE premium_ecommerce_users_data_1 SET f_password=%s WHERE id=%d',array(md5($pass),$uid));
	
	////
	$oTpl     =& TplParser::getInstance( );
	$aData = array();
	$aData['contactus'] = getVariable('ecommerce_contactus_'.LANGUAGE);

	if(!$aData['contactus'])
		$aData['contactus'] = getVariable('ecommerce_contactus');

	$aData['content'] = sprintf($lang['Password_reminder_mail_body'],$pass);
	$oTpl->setVariables( 'aData', $aData );
	////

	$aSend['sMailContent'] = $oTpl->tbHtml( $sFile, 'PASSWORD_REMINDER_EMAIL_BODY' );
	$aSend['sTopic'] = $lang['Password_reminder_mail_subject'];
	$aSend['sSender']= $GLOBALS['config']['email'];

	sendEmail( $aSend, null, $v['sEmail'], true ); //send e-mail to client
	$_SESSION['mail'] = $v['sEmail'];
	return true;
  } 

  function change_password($v) {
	global $config;

	if($v['sPassword']!=$v['sPassword2'])
		return false;
	
	$ok = DB::GetOne('SELECT 1 FROM premium_ecommerce_users_data_1 WHERE f_password=%s AND id=%d',array(md5($v['sOldPassword']),$_SESSION['user']));
	if(!$ok) return false;
	
	DB::Execute('UPDATE premium_ecommerce_users_data_1 SET f_password=%s WHERE id=%d',array(md5($v['sPassword']),$_SESSION['user']));

	$oPage =& Pages::getInstance( );
	print('<script type="text/javascript">alert(\''.addcslashes($GLOBALS['lang']['Password_changed'],'\\\'').'\');window.location=\''.addcslashes(REDIRECT.$oPage->aPages[43]['sLinkName'], '\\\'').'\';</script>');
	return true;
  }

  function logout( ){
         unset($_SESSION['user']);
         unset($_SESSION['contact']);
         unset($_SESSION['company']);
         header( 'Location: '.REDIRECT);
         exit;
  } 

  function logged() {
	return isset($_SESSION['contact']);
  }
  
  function orders($tpl) {
  	global $oOrder, $oTpl;
  	$ret = DB::Execute('SELECT id FROM premium_warehouse_items_orders_data_1 WHERE f_contact=%d AND active=1 ORDER BY created_on DESC LIMIT 10',array($_SESSION['contact']));
  	$sOrder = '';
        $sOrder .= $oTpl->tbHtml( 'orders_panel.tpl', 'ORDER_HEADER' );
  	while($row = $ret->FetchRow()) {
  	    global $aOrder, $aPayment, $sOrderProducts;
  	    $oOrder->aProducts = null;
	    $oOrder->fProductsSummary = null;
            $aOrder = $oOrder->throwOrder( $row['id'] );
            $aOrder['sComment'] = preg_replace( '/\|n\|/', '<br />' , $aOrder['sComment'] );
            $sOrderProducts = $oOrder->listProducts( 'orders_panel.tpl', $row['id'], 'ORDER_PRINT_' );
            $aPayment = $oOrder->throwPaymentCarrier( $aOrder['iCarrier'], $aOrder['iPayment'] );
            $sOrder .= $oTpl->tbHtml( 'orders_panel.tpl', 'ORDER_PRINT' );
        }
        $sOrder .= $oTpl->tbHtml( 'orders_panel.tpl', 'ORDER_FOOTER' );
        return $sOrder;
  }
  
  function throwAddresses() {
    if(!self::logged()) return null;
    global $aUser;
    $ret = array('default'=>array('sLastName'=>$aUser['sLastName'],'sFirstName'=>$aUser['sFirstName'],'sCompanyName'=>$aUser['sCompanyName'],'sStreet'=>$aUser['sStreet'],'sCity'=>$aUser['sCity'],'sCountry'=>$aUser['sCountry'],'sState'=>$aUser['sState'],'sZipCode'=>$aUser['sZipCode'],'sPhone'=>$aUser['sPhone']));
    $a = DB::Execute('SELECT * from premium_multiple_addresses_data_1 WHERE f_record_type="contact" and f_record_id=%d ORDER BY id DESC',array($_SESSION['contact']));
    while($row = $a->FetchRow()) {
        $ret[$row['id']] = array('sLastName'=>$row['f_last_name'],'sFirstName'=>$row['f_first_name'],'sCompanyName'=>$row['f_company_name'],'sStreet'=>$row['f_address_1'],'sCity'=>$row['f_city'],'sCountry'=>$row['f_country'],'sZipCode'=>$row['f_postal_code']);
    }
    return $ret;
  } 
}

?>