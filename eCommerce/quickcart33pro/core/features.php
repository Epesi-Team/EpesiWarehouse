<?php

/**
* Return product features list
* @return string
* @param string $sFile
* @param int    $iProduct
*/
function listProductFeatures( $sFile = null, $iProduct = null ){
  //{ epesi
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
						WHERE pp.active=1 AND pp.f_language="'.LANGUAGE.'" AND pp.f_item_name=%d ORDER BY g.f_position,gl.f_label,g.f_group_code,p.f_position,pl.f_label,p.f_parameter_code',array($iProduct));

		$last_group = null;
		while($bExp = $ret2->FetchRow()) {
			$parameters[] = array('sGroup'=>($last_group!=$bExp['group_code']?($bExp['group_label']?$bExp['group_label']:$bExp['group_code']):''), 'sName'=>($bExp['parameter_label']?$bExp['parameter_label']:$bExp['parameter_code']), 'sValue'=>($bExp['f_value']=='Y'?'<span class="yes">Yes</span>':($bExp['f_value']=='N'?'<span class="no">No</span>':$bExp['f_value'])));
			if($last_group != $bExp['group_code']) {
    				$last_group = $bExp['group_code'];
			}
		}
		
		$row = DB::GetRow('SELECT it.f_sku,
					it.f_upc,
					it.f_product_code
					FROM premium_warehouse_items_data_1 it WHERE id=%d',array($iProduct));
		global $lang;
		$parameters[] = array('sGroup'=>$lang['Codes'],'sName'=>'SKU','sValue'=>$row['f_sku']);
		$parameters[] = array('sGroup'=>'','sName'=>'UPC','sValue'=>$row['f_upc']);
		$parameters[] = array('sGroup'=>'','sName'=>$lang['Product_Code'],'sValue'=>$row['f_product_code']);


	$oTpl =& TplParser::getInstance( );
	$content = '';

	foreach($parameters as $aData) {
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