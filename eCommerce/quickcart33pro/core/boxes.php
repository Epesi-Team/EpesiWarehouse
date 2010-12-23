<?php
/**
* Return to array list of boxes
* @return array
* @param string $sFile
*/
function throwBoxes( $sFile = 'container.tpl' ){
    //{ epesi
    $oTpl   =& TplParser::getInstance( );
  
    $ret = DB::Execute('SELECT f_content as sContent,f_name as sName FROM premium_ecommerce_boxes_data_1 WHERE active=1 AND f_publish=1 AND f_language=\''.LANGUAGE.'\' ORDER BY f_position');
    $aReturn = array();
    $id = 1;
    while($row = $ret->FetchRow()) {
      $row['iBox'] = $id;
      $row['sContent'] = changeTxt( $row['sContent'], 'NdsNl' );
      $oTpl->setVariables( 'aData', $row );
      $aReturn[$id] = $oTpl->tbHtml( $sFile, 'BOX' );
      $id++;
    }
  
    return $aReturn;
    //} epesi
} // end function throwBoxes
?>