<?php
/**
* Return to array list of boxes
* @return array
* @param string $sFile
*/
function throwBoxes( $sFile = 'container.tpl' ){
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
      $aData['sContent'] = changeTxt( $aData['sContent'], 'NdsNl' );
      
      $oTpl->setVariables( 'aData', $aData );
      $aReturn[$aData['iBox']] = $oTpl->tbHtml( $sFile, 'BOX' );
    } // end for

    return $aReturn;
  }
  else
    return Array( );
} // end function throwBoxes
?>