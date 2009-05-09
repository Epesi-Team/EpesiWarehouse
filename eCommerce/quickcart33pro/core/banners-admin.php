<?php

/**
* Delete banner
* @return void
* @param int  $iBanner
*/
function deleteBanner( $iBanner ){
  $aData = throwBanner( $iBanner );
  if( isset( $aData ) && is_array( $aData ) ){
    $oFF =& FlatFiles::getInstance( );
    $oFF->deleteInFile( DB_BANNERS, $iBanner, 'iBanner' );
    $oFF->deleteInFile( DB_BANNERS_STATS, $iBanner, 'iBanner' );
    unlink( DIR_FILES.$aData['sFile'] );
  }
} // end function deleteBanner

/**
* Return list of banners
* @return string
* @param string $sFile
*/
function listBanners( $sFile = 'banners.tpl' ){
  $oFF  =& FlatFiles::getInstance( );
  $oTpl =& TplParser::getInstance( );

  $aFile = $oFF->throwFileArray( DB_BANNERS );
  if( isset( $aFile ) ){
    $content  = null;
    $iCount   = count( $aFile );
    $aStats   = throwBannersStats( );
    $aTypes   = $GLOBALS['aBannersTypes'];

    for( $i = 0; $i < $iCount; $i++ ){
      $aData = $aFile[$i];

      $aData['sExt'] = $oFF->throwExtOfFile( $aData['sFile'] );
      
      if( isset( $aStats[$aData['iBanner']] ) ){
        $aData['iViews']  = $aStats[$aData['iBanner']]['iViews'];
        $aData['iClicks'] = $aStats[$aData['iBanner']]['iClicks'];
      }
      else{
        $aData['iViews']  = null;
        $aData['iClicks'] = null;
      }

      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
      $aData['sType']  = isset( $aTypes[$aData['iType']] ) ? $aTypes[$aData['iType']] : null;
      $sBlock          = ( $aData['sExt'] == 'swf' ) ? 'FLASH' : 'NORMAL';

      $oTpl->setVariables( 'aData', $aData );
      $aData['sBanner']   = $oTpl->tbHtml( $sFile, $sBlock );
      $aData['sStatus']   = throwYesNoTxt( $aData['iStatus'] );

      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'LIST_LIST' );
    } // end for

    if( isset( $content ) )
      return $oTpl->tbHtml( $sFile, 'LIST_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'LIST_FOOT' );
  }
} // end function listBanners

/**
* Save banner data
* @return int
* @param array  $aForm
*/
function saveBanner( $aForm ){

  $oFF =& FlatFiles::getInstance( );

  if( !isset( $aForm['iStatus'] ) )
    $aForm['iStatus'] = 0;

  if( !empty( $aForm['sLink'] ) && !eregi( 'http://', $aForm['sLink'] ) )
    $aForm['sLink'] = 'http://'.$aForm['sLink'];

  if( isset( $_FILES['sFile']['name'] ) && !empty( $_FILES['sFile']['name'] ) ){
    $aForm['sFile'] = $oFF->uploadFile( $_FILES['sFile'], DIR_FILES );
  }

  if( isset( $aForm['iBanner'] ) && is_numeric( $aForm['iBanner'] ) ){
    $sParam = 'iBanner';
  }
  else{
    $aForm['iBanner'] = $oFF->throwLastId( DB_BANNERS, 'iBanner' ) + 1;
    $sParam = null;
  }

  $aForm = changeMassTxt( $aForm, '' );

  $oFF->save( DB_BANNERS, $aForm, $sParam );
  $oFF->save( DB_BANNERS_STATS, $aForm, $sParam );

  return $aForm['iBanner'];
} // end function saveBanner
?>