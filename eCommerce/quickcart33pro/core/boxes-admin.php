<?php
/**
* Save box
* @return int
* @param array  $aForm
* @param bool   $bExist
*/
function saveBox( $aForm, $bExist = null ){

  if( !is_file( DB_BOXES ) )
    return null;

  $oFF =& FlatFiles::getInstance( );

  if( isset( $aForm['iBox'] ) && is_numeric( $aForm['iBox'] ) ){
    $sParam = 'iBox';
  }
  else{
    $aForm['iBox'] = $oFF->throwLastId( DB_BOXES, 'iBox' ) + 1;
    $sParam = null;
  }

  $aForm = changeMassTxt( $aForm, '', Array( 'sContent', 'nds' ) );

  $oFF->save( DB_BOXES, $aForm, $sParam );

  return $aForm['iBox'];
} // end function saveBox

/**
* Delete box
* @return void
* @param int  $iBox
*/
function deleteBox( $iBox ){
  if( !is_file( DB_BOXES ) )
    return null;

  $oFF =& FlatFiles::getInstance( );

  $oFF->deleteInFile( DB_BOXES, $iBox, 'iBox' );
} // end function deleteBox

/**
* Throw data of box
* @return array
* @param int  $iBox
*/
function throwBox( $iBox ){
  if( !is_file( DB_BOXES ) )
    return null;

  $oFF =& FlatFiles::getInstance( );

  $aData = $oFF->throwData( DB_BOXES, $iBox, 'iBox' );
  if( isset( $aData ) && is_array( $aData ) ){
    $aData['sContent'] = changeTxt( $aData['sContent'], 'Ndsnl' );
    return $aData;
  }
  else{
    return null;
  }
} // end function throwBox

/**
* Show list of boxes
* @return string
* @param string $sFile
*/
function listBoxes( $sFile = 'boxes.tpl' ){
  if( !is_file( DB_BOXES ) )
    return null;

  $oFF    =& FlatFiles::getInstance( );
  $oTpl   =& TplParser::getInstance( );
  $aFile  = $oFF->throwFileArray( DB_BOXES );

  if( isset( $aFile ) && is_array( $aFile ) ){
    $iCount   = count( $aFile );
    $content  = null;

    for( $i = 0; $i < $iCount; $i++ ){
      $aData = $aFile[$i];
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'LIST' );
    } // end for

    return $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );
  }
} // end function listBoxes
?>