<?php
/**
* Return banner stats
* @return array
* @param int  $iBanner
*/
function throwBannersStats( $iBanner = null ){
  $oFF  =& FlatFiles::getInstance( );

  $aFile  = $oFF->throwFileArray( DB_BANNERS_STATS );
  $iCount = count( $aFile );
  for( $i = 0; $i < $iCount; $i++ ){
    $aStats[$aFile[$i]['iBanner']] = $aFile[$i];
  } // end for

  if( isset( $iBanner ) ){
    if( isset( $aStats[$iBanner] ) )
      return $aStats[$iBanner];
  }
  else{
    if( isset( $aStats ) )
      return $aStats;
  }
} // end function throwBannersStats

/**
* Return random banners
* @return array
* @param string $sFile
* @param array  $aTypes
*/
function throwBannersRand( $sFile, $aTypes = null ){

  $oFF  =& FlatFiles::getInstance( );
  $oTpl =& TplParser::getInstance( );

  $aFile  = $oFF->throwFileArray( DB_BANNERS );
  $iCount = count( $aFile );
  $aStats = throwBannersStats( );

  for( $i = 0; $i < $iCount; $i++ ){
    $aData = $aFile[$i];
    if( $aData['iStatus'] == 1 && ( !isset( $aStats[$aData['iBanner']] ) || isset( $aStats[$aData['iBanner']] ) && ( $aData['iMax'] == 0 || ( $aData['iMax'] > 0 && $aStats[$aData['iBanner']]['iViews'] < $aData['iMax'] ) ) ) ){
      $aBanners[$aData['iType']][] = $aData;
    }
  }

  if( isset( $aBanners ) ){
    $iCountTypes = count( $GLOBALS['aBannersTypes'] );
    for( $i = 0; $i < $iCountTypes; $i++ ){
      if( isset( $aBanners[$i] ) && is_array( $aBanners[$i] ) && ( ( isset( $aTypes ) && in_array( $i, $aTypes, true ) ) || !isset( $aTypes ) ) ){
        $iCount = count( $aBanners[$i] );
        if( $iCount > 0 ){
          $aData = $aBanners[$i][rand( 0, $iCount - 1 )];
          $aData['sExt'] = $oFF->throwExtOfFile( $aData['sFile'] );
          if( $aData['sExt'] == 'swf' ){
            $sBlock = 'FLASH';
          }
          else{
            $sBlock = 'NORMAL';
            if( !empty( $aData['sLink'] ) ){
              $aData['sBannerLink'] = ( defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true ) ? $aData['iBanner'].',banners.html' : '?,'.$aData['iBanner'].',banners';
            }
            else{
              $aData['sBannerLink'] = null;
              $sBlock .= '_NO_LINK';
            }
          }

          $oTpl->setVariables( 'aData', $aData );
          $aReturn[$i]  = $oTpl->tbHtml( $sFile, 'BANNER_'.$sBlock );
          $aId[]        = $aData['iBanner'];
        }
      }
    } // end for

    if( isset( $aId ) && is_array( $aId ) )
      changeBannersStats( $aId, 'iViews' );

    if( isset( $aReturn ) && is_array( $aReturn ) ){
      return $aReturn;
    }
  }
} // end function throwBannersRand

/**
* Return banners data
* @return array
* @param int  $iBanner
*/
function throwBanner( $iBanner ){
  $oFF =& FlatFiles::getInstance( );
  return $oFF->throwDataFromFiles( Array( DB_BANNERS, DB_BANNERS_STATS ), $iBanner, 'iBanner' );
} // end function throwBanner

/**
* Redirect to banner link
* @return void
* @param int  $iBanner
*/
function goToBannerLink( $iBanner ){
  $aData = throwBanner( $iBanner );
  if( isset( $aData ) && is_array( $aData ) && !empty( $aData['sLink'] ) ){
    changeBannersStats( Array( $iBanner ), 'iClicks' );
    header( "Location: ".$aData['sLink'] );
    exit;
  }
} // end function goToBannerLink

/**
* Change banners stats
* @return void
* @param array  $aId
* @param string $sIndex
*/
function changeBannersStats( $aId, $sIndex ){
  $oFF =& FlatFiles::getInstance( );
  $aStats = throwBannersStats( );
  foreach( $aId as $iKey => $iBanner ){
    if( isset( $aStats[$iBanner] ) ){
      $aStats[$iBanner][$sIndex]++;
      $sParam = 'iBanner'; 
    }
    else{
      $sParam = null;
      $aStats[$iBanner]['iClicks'] = 0;
      $aStats[$iBanner]['iViews'] = 0;
    }

    $aStats[$iBanner]['iBanner'] = $iBanner;

    $oFF->save( DB_BANNERS_STATS, $aStats[$iBanner], $sParam );
  } // end foreach
} // end function changeBannersStats
?>