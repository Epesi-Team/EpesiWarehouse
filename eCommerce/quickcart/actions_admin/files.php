<?php
if( $a == 'list' ){
  if( isset( $_POST['sOption'] ) ){
    $oFile->saveAllFiles( $_POST, $iLinkType );
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&iLinkType='.$iLinkType.'&sOption=save' );
    exit;
  }

  if( isset( $sOption ) ){
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );
  }
  
  if( !isset( $iLinkType ) || !is_numeric( $iLinkType ) )
    $iLinkType = 1;
  $aLinkTypes = Array( 1 => $lang['Pages'], 2 => $lang['Products'] );
  $sLinkTypeSelect = throwSelectFromArray( $aLinkTypes, $iLinkType );
  $sLinkType = $aLinkTypes[$iLinkType];
  $sList = $oFile->listAllFiles( 'files.tpl', $iLinkType );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );
}
?>