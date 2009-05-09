<?php
/**
* Save email to newsletter list
* @return void
* @param string $sEmail
*/
function saveNewsletter( $sEmail ){
  $oFF =& FlatFiles::getInstance( );

  deleteNewsletter( $sEmail );
  $oFF->save( DB_NEWSLETTER, Array( 'sEmail' => $sEmail ) );
} // end function saveNewsletter

/**
* Delete email from newsletter
* @return void
* @param string $sEmail
*/
function deleteNewsletter( $sEmail ){
  $oFF =& FlatFiles::getInstance( );
  $oFF->deleteInFile( DB_NEWSLETTER, $sEmail, 'sEmail' );
} // end function deleteNewsletter

if( !function_exists( 'checkEmail' ) ){
  /**
  * Check e-mail format is correct
  * @return bool
  * @param  string  $sEmail
  */
  function checkEmail( $sEmail ){
    if( eregi( "^[a-z0-9_.-]+([_\\.-][a-z0-9]+)*@([a-z0-9_\.-]+([\.][a-z]{2,4}))+$", $sEmail ) )
      return true;
    else
      return false;
  } // end function checkEmail
}
?>