<?php
/**
* Return features from product
* @return array
* @param int  $iProduct
*/
function throwProductFeatures( $iProduct ){
  if( !is_file( DB_FEATURES_PRODUCTS ) )
    return null;
  $oFF =& FlatFiles::getInstance( );

  $aFile = $oFF->throwFileArray( DB_FEATURES_PRODUCTS, null );
  if( isset( $aFile ) && is_array( $aFile ) ){
    $iCount   = count( $aFile );
    $content  = null;

    for( $i = 0; $i < $iCount; $i++ ){
      if( $iProduct == $aFile[$i]['iProduct'] ){
        $aReturn[$aFile[$i]['iFeature']] = $aFile[$i]['sValue'];
      }
    } // end for

    if( isset( $aReturn ) && is_array( $aReturn ) ){
      return $aReturn;
    }
  }
} // end function throwProductFeatures

/**
* Return product features list
* @return string
* @param string $sFile
* @param int    $iProduct
*/
function listProductFeatures( $sFile = null, $iProduct = null ){
  if( !is_file( DB_FEATURES ) )
    return null;
  $oFF  =& FlatFiles::getInstance( );
  $oTpl =& TplParser::getInstance( );

  $aFile = $oFF->throwFileArray( DB_FEATURES );
  if( isset( $aFile ) ){
    $content  = null;
    $iCount   = count( $aFile );
    $i2       = 0;
    $aProductFeatures = throwProductFeatures( $iProduct );

    for( $i = 0; $i < $iCount; $i++ ){
      if( isset( $aProductFeatures[$aFile[$i]['iFeature']] ) ){
        $aData = $aFile[$i];
        $aData['iStyle'] = ( $i2 % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i2 == ( $iCount - 1 ) ) ? 'L' : $i2 + 1;
        $aData['sValue'] = isset( $aProductFeatures[$aData['iFeature']] ) ? $aProductFeatures[$aData['iFeature']] : null;

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'FEATURES_LIST' );
        $i2++;
      }
    } // end for
    if( isset( $content ) )
      return $oTpl->tbHtml( $sFile, 'FEATURES_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FEATURES_FOOT' );
  }
} // end function listProductFeatures
?>