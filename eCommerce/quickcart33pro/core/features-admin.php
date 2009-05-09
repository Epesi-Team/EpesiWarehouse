<?php
/**
* List of features
* @return string
* @param string $sFile
*/
function listFeatures( $sFile = 'features.tpl' ){
  $oFF  =& FlatFiles::getInstance( );
  $oTpl =& TplParser::getInstance( );

  if( !is_file( DB_FEATURES ) )
    return null;

  $aFile = $oFF->throwFileArray( DB_FEATURES );
  if( isset( $aFile ) ){
    $content  = null;
    $iCount   = count( $aFile );

    for( $i = 0; $i < $iCount; $i++ ){
      $aData = $aFile[$i];
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L' : $i + 1;

      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'LIST' );
    } // end for

    if( isset( $content ) )
      return $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );
  }
} // end function listFeatures

/**
* Save feature
* @return int
* @param array  $aForm
*/
function saveFeature( $aForm ){
  if( !is_file( DB_FEATURES ) )
    return null;
  $oFF =& FlatFiles::getInstance( );

  if( isset( $aForm['iFeature'] ) && is_numeric( $aForm['iFeature'] ) ){
    $sParam = 'iFeature';
  }
  else{
    $aForm['iFeature'] = $oFF->throwLastId( DB_FEATURES, 'iFeature' ) + 1;
    $sParam = null;
  }

  $aForm = changeMassTxt( $aForm, '' );

  $oFF->save( DB_FEATURES, $aForm, $sParam, 'sort' );
  return $aForm['iFeature'];
} // end function saveFeature

/**
* Return feature data
* @return array
* @param int  $iFeature
*/
function throwFeature( $iFeature ){
  if( !is_file( DB_FEATURES ) )
    return null;
  $oFF =& FlatFiles::getInstance( );
  return $oFF->throwData( DB_FEATURES, $iFeature, 'iFeature' );
} // end function throwFeature

/**
* Delete feature
* @return void
* @param int  $iFeature
*/
function deleteFeature( $iFeature ){
  if( !is_file( DB_FEATURES ) )
    return null;
  $oFF =& FlatFiles::getInstance( );
  $oFF->deleteInFile( DB_FEATURES, $iFeature, 'iFeature' );
  $oFF->deleteInFile( DB_FEATURES_PRODUCTS, $iFeature, 'iFeature' );
} // end function deleteFeature

/**
* Return product features list
* @return string
* @param string $sFile
* @param int    $iProduct
*/
function listProductFeaturesAdmin( $sFile = null, $iProduct = null ){
  if( !is_file( DB_FEATURES ) )
    return null;
  $oFF  =& FlatFiles::getInstance( );
  $oTpl =& TplParser::getInstance( );

  $aFile = $oFF->throwFileArray( DB_FEATURES );
  if( isset( $aFile ) ){
    $content  = null;
    $iCount   = count( $aFile );
    $aProductFeatures = throwProductFeatures( $iProduct );

    for( $i = 0; $i < $iCount; $i++ ){
      $aData = $aFile[$i];
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L' : $i + 1;
      $aData['sValue'] = isset( $aProductFeatures[$aData['iFeature']] ) ? $aProductFeatures[$aData['iFeature']] : null;

      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'FEATURES_LIST' );
    } // end for
    if( isset( $content ) )
      return $oTpl->tbHtml( $sFile, 'FEATURES_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FEATURES_FOOT' );
  }
} // end function listProductFeaturesAdmin
?>