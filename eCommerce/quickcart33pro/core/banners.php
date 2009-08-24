<?php
/**
* Return random banners
* @return array
* @param string $sFile
* @param array  $aTypes
*/
function throwBannersRand( $sFile, $aTypes = null ){
//{ epesi
  $oTpl =& TplParser::getInstance( );
  $aBanners = array();
  $ret = DB::Execute('SELECT id as iBanner, 
				f_file as sFile,
				f_link as sLink,
				f_type as iType,
				f_width as iWidth,
				f_height as iHeight,
				f_color as sColor
				FROM premium_ecommerce_banners_data_1 WHERE active=1 AND f_publish=1 AND (f_views_limit=0 OR f_views<f_views_limit) AND f_language="'.LANGUAGE.'"');
  while($row = $ret->FetchRow()) {
    $aBanners[$row['iType']][] = $row;
  }
//} epesi
  if( isset( $aBanners ) ){
    $iCountTypes = 2;
    for( $i = 0; $i < $iCountTypes; $i++ ){
      if( isset( $aBanners[$i] ) && is_array( $aBanners[$i] ) && ( ( isset( $aTypes ) && in_array( $i, $aTypes, true ) ) || !isset( $aTypes ) ) ){
        $iCount = count( $aBanners[$i] );
        if( $iCount > 0 ){
          $aData = $aBanners[$i][rand( 0, $iCount - 1 )];
          //$aData['sExt'] = $oFF->throwExtOfFile( $aData['sFile'] );//epesi
	  //{ epesi
	  $aData['sFile'] = 'epesi/banners/'.basename($aData['sFile']);
	  $aData['sExt'] = FileJobs::throwExtOfFile( $aData['sFile'] );
	  if(!eregi('^http[s]?:\/\/',$aData['sLink']))
	    $aData['sLink'] = 'http://'.$aData['sLink'];
	  //} epesi
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
//      changeBannersStats( $aId, 'iViews' );
//{ epesi
	foreach($aId as $iBanner)
	    DB::Execute('UPDATE premium_ecommerce_banners_data_1 SET f_views=f_views+1 WHERE id=%d',array($iBanner));
//} epesi

    if( isset( $aReturn ) && is_array( $aReturn ) ){
      return $aReturn;
    }
  }
} // end function throwBannersRand

/**
* Redirect to banner link
* @return void
* @param int  $iBanner
*/
function goToBannerLink( $iBanner ){
  //{ epesi
  $link =  DB::GetOne('SELECT f_link FROM premium_ecommerce_banners_data_1 WHERE active=1 AND id=%d',array($iBanner));
  if($link) {
    DB::Execute('UPDATE premium_ecommerce_banners_data_1 SET f_clicks=f_clicks+1 WHERE id=%d',array($iBanner));
    if(!eregi('^http[s]?:\/\/',$link))
	    $link = 'http://'.$link;
    header( "Location: ".$link );
    exit;
  }
  //} epesi
} // end function goToBannerLink

?>