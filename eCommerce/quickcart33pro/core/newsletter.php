<?php
/**
* Save email to newsletter list
* @return void
* @param string $sEmail
*/
function saveNewsletter( $sEmail ){
  //{ epesi
  DB::Execute('INSERT INTO premium_ecommerce_newsletter_data_1(f_email,created_on) VALUES(%s,%T)',array($sEmail,time()));
  //} epesi
} // end function saveNewsletter


if( !function_exists( 'checkEmail' ) ){
  /**
  * Check e-mail format is correct
  * @return bool
  * @param  string  $sEmail
  */
  function checkEmail( $sEmail ){
    if( preg_match( "/^[a-z0-9_.-]+([_\\.-][a-z0-9]+)*@([a-z0-9_\.-]+([\.][a-z]{2,4}))+$/i", $sEmail ) )
      return true;
    else
      return false;
  } // end function checkEmail
}
?>