<?php
require DIR_LIBRARIES.'pclzip.lib.php';

if( $a == 'create' ){
  if( function_exists( 'gzcompress' ) ){
    $sFile = DIR_BACKUP.'backup_'.date('Y-m-d_H-i').'.zip';
    $oBackup = new PclZip( $sFile );
    $oBackup->create( DIR_DB );
    header( 'Location: '.$sFile );
    exit;
  }
  else{
    $content .= $oTpl->tbHtml( 'messages.tpl', 'ERROR' );
  }
}
elseif( $a == 'list' ){
  if( isset( $sOption ) && $sOption == 'restore' && isset( $sFile ) && is_file( DIR_BACKUP.$sFile ) ){
    if( function_exists( 'gzcompress' ) ){
      deleteDbFiles( );
      $oBackup = new PclZip( DIR_BACKUP.$sFile );
      $oBackup->extract( );
    }
    else{
      $content .= $oTpl->tbHtml( 'messages.tpl', 'ERROR' );
      $sOption = null;
    }
  }

  if( isset( $sOption ) )
    $content .= $oTpl->tbHtml( 'messages.tpl', 'DONE' );

  $content .= $oTpl->tbHtml( 'backup.tpl', 'LIST_TITLE' );

  $sList = listBackupFiles( 'backup.tpl' );
  $content .= !empty( $sList ) ? $sList : $oTpl->tbHtml( 'messages.tpl', 'EMPTY' );

}
elseif( $a == 'delete' && isset( $sFile ) && is_file( DIR_BACKUP.$sFile ) ){
  unlink( DIR_BACKUP.$sFile );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$aActions['f'].'-list&sOption=del' );
  exit;
}
?>