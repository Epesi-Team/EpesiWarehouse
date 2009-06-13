<?php
/**
* Return features from product
* @return array
* @param int  $iProduct
*/
/*
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
*/

/**
* Return product features list
* @return string
* @param string $sFile
* @param int    $iProduct
*/
function listProductFeatures( $sFile = null, $iProduct = null ){
/*  if( !is_file( DB_FEATURES ) )
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
  }*/
  //{ epesi
	static $parameters;
	if(!isset($parameters)) {
	        $parameters = array();
		$ret2 = DB::Execute('SELECT pp.f_item_name, pp.f_value,
									p.f_parameter_code as parameter_code,
									pl.f_label as parameter_label,
									g.f_group_code as group_code,
									gl.f_label as group_label
						FROM premium_ecommerce_products_parameters_data_1 pp
						INNER JOIN (premium_ecommerce_parameters_data_1 p,premium_ecommerce_parameter_groups_data_1 g) ON (p.id=pp.f_parameter AND g.id=pp.f_group)
						LEFT JOIN premium_ecommerce_parameter_labels_data_1 pl ON (pl.f_parameter=p.id AND pl.f_language="'.LANGUAGE.'" AND pl.active=1)
						LEFT JOIN premium_ecommerce_parameter_group_labels_data_1 gl ON (gl.f_group=g.id AND gl.f_language="'.LANGUAGE.'" AND gl.active=1)
						WHERE pp.active=1 AND pp.f_language="'.LANGUAGE.'" ORDER BY pp.f_item_name,g.f_position,gl.f_label,g.f_group_code,p.f_position,pl.f_label,p.f_parameter_code');

		$last_group = null;
		$last_product = null;
		while($bExp = $ret2->FetchRow()) {
			if($last_product!=$bExp['f_item_name']) {
			        $parameters[$bExp['f_item_name']] = array();
				$last_product = $bExp['f_item_name'];
				$last_group = null;
			}
			$parameters[$bExp['f_item_name']][] = array('sGroup'=>($last_group!=$bExp['group_code']?($bExp['group_label']?$bExp['group_label']:$bExp['group_code']):''), 'sName'=>($bExp['parameter_label']?$bExp['parameter_label']:$bExp['parameter_code']), 'sValue'=>$bExp['f_value']);
			if($last_group != $bExp['group_code']) {
    				$last_group = $bExp['group_code'];
			}
		}
	}
	$oTpl =& TplParser::getInstance( );
	$content = '';

	if(isset($parameters[$iProduct]))
	foreach($parameters[$iProduct] as $aData) {
    		$aData['iStyle'] = ( $i2 % 2 ) ? 0: 1;
    		$aData['sStyle'] = ( $i2 == ( $iCount - 1 ) ) ? 'L' : $i2 + 1;
    		$oTpl->setVariables( 'aData', $aData );
    		$content .= $oTpl->tbHtml( $sFile, 'FEATURES_LIST' );
    		$i2++;
	} // end for
	if( $content )
    	    return $oTpl->tbHtml( $sFile, 'FEATURES_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FEATURES_FOOT' );
  //} epesi
} // end function listProductFeatures
?>