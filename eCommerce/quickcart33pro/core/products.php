<?php
class Products
{

  //var $aProducts = null;
  //var $aProductsPages = null;
  var $mData = null;

  function &getInstance( ){
    static $oInstance = null;
    if( !isset( $oInstance ) ){
      $oInstance = new Products( );
    }
    return $oInstance;
  } // end function getInstance

  /**
  * Constructor
  * @return void
  */
  function Products( ){
//    $this->generateCache( );
	$uncategorized = DB::GetOne('SELECT 1 FROM premium_ecommerce_products_data_1 pr
					INNER JOIN (premium_warehouse_items_data_1 it,premium_ecommerce_availability_data_1 av) ON (pr.f_item_name=it.id AND av.id=pr.f_available)
					 WHERE pr.f_publish=1 AND pr.active=1 AND it.f_category is NULL');

	if(!$uncategorized) {//remove uncategorized category
	    unset(Pages::getInstance()->aPages[23]);
	}
  } // end function Pages
/*
  function generateCache() {
	$this->aProductsPages = array();
	$this->aProducts = $this->getProducts();
	foreach($this->aProducts as &$p) {
		$this->aProductsPages[$p['iProduct']] = $p['aCategories'];
	}
  }
  */
  
  function getProduct($id) {
	$arr = $this->getProducts('it.id='.(int)$id);
	return array_shift($arr);
  }

  /**
  * Generate cache variables
  * @return void
  */
  function getProducts($where = '',$limit=null,$offset=null){
	global $config;
    $products = array();

	$currency = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s',array($config['currency_symbol']));
	if($currency===false) 
		die('Currency not defined in Epesi: '.$config['currency_symbol']);

	$ret = DB::Execute('SELECT 	it.id as iProduct, 
								it.f_item_name as sName2, 
								pri.f_gross_price as fPrice, 
								pri.f_tax_rate as tax,
								pr.f_position as iPosition, 
								pr.f_recommended as sRecommended,
								it.f_category,
								av.f_availability_code as sAvailable2, 
								avl.f_label as sAvailable, 
								d.f_display_name as sName,
								d.f_short_description as sDescriptionShort,
								d.f_long_description as sDescriptionFull,
								d.f_page_title as sNameTitle, 
								d.f_meta_description as sMetaDescription, 
								d.f_keywords as sMetaKeywords,
								it.f_weight as sWeight,
								it.f_manufacturer as iProducer,
								SUM(loc.f_quantity) as f_quantity,
								it.f_cost fPrice2,
								it.f_tax_rate tax2,
								dist.quantity as distributorQuantity,
								dist.price fPrice3,
								dist.price_currency,
								distributor.f_tax_rate tax3
					FROM premium_ecommerce_products_data_1 pr
					INNER JOIN (premium_warehouse_items_data_1 it,premium_ecommerce_availability_data_1 av) ON (pr.f_item_name=it.id AND av.id=pr.f_available)
					LEFT JOIN premium_ecommerce_prices_data_1 pri ON (pri.f_item_name=it.id AND pri.active=1 AND pri.f_currency='.$currency.')
					LEFT JOIN premium_ecommerce_descriptions_data_1 d ON (d.f_item_name=it.id AND d.f_language="'.LANGUAGE.'" AND d.active=1)
					LEFT JOIN premium_ecommerce_availability_labels_data_1 avl ON (pr.f_available=avl.f_availability AND avl.f_language="'.LANGUAGE.'" AND avl.active=1) 
					LEFT JOIN premium_warehouse_location_data_1 loc ON (loc.f_item_sku=it.id AND loc.f_quantity>0 AND loc.active=1)
					LEFT JOIN (premium_warehouse_wholesale_items dist, premium_warehouse_distributor_data_1 distributor) ON (dist.item_id=it.id AND dist.quantity>0 AND distributor.id=dist.distributor_id AND dist.price=(SELECT MIN(tmp.price) FROM premium_warehouse_wholesale_items tmp WHERE tmp.item_id=it.id))
					 WHERE pr.f_publish=1 AND pr.active=1 '.($where?' AND ('.$where.')':'').' GROUP BY it.id ORDER BY pr.f_position'.($limit!==null?' LIMIT '.(int)$limit.($offset!==null?' OFFSET '.(int)$offset:''):''));

        $taxes = DB::GetAssoc('SELECT id, f_percentage FROM data_tax_rates_data_1 WHERE active=1');
	$autoprice = getVariable('ecommerce_autoprice');
	$minimal = getVariable('ecommerce_minimal_profit');
	$percentage = getVariable('ecommerce_percentage_profit');
	while($aExp = $ret->FetchRow()) {
		if($aExp['sName']=='') 
			$aExp['sName'] = $aExp['sName2'];
		if($aExp['sAvailable']=='') 
			$aExp['sAvailable'] = $aExp['sAvailable2'];
		if($autoprice && !$aExp['fPrice']) {
			$rr = explode('__',$aExp['fPrice2']);
			if($rr && $rr[0] && $rr[1]==$currency) {
				$netto = $rr[0];
				$profit = $netto*$percentage/100;
				if($profit<$minimal) $profit = $minimal;
				$aExp['fPrice'] = (float)($netto+$profit)*(100+$taxes[$aExp['tax2']])/100;
				$aExp['tax'] = $aExp['tax2'];
			} elseif($aExp['fPrice3'] && $aExp['price_currency']==$currency) {
				$netto = $aExp['fPrice3'];
				$profit = $netto*$percentage/100;
				if($profit<$minimal) $profit = $minimal;
				$aExp['fPrice'] = (float)($netto+$profit)*(100+$taxes[$aExp['tax3']])/100;
				$aExp['tax'] = $aExp['tax3'];
			}
		}
		if(!$aExp['f_quantity'] && !$aExp['distributorQuantity'])
			$aExp['fPrice']='';
//		if($aExp['fPrice'])
//			$aExp['fPrice'] = number_format($aExp['fPrice'],2);
		$aExp['iComments'] = 1;
		unset($aExp['sName2']);
		$cats = array_filter(explode('__',$aExp['f_category']));
		unset($aExp['f_category']);
		$pages = array();
		if(!empty($cats)) {
    		    foreach($cats as $c) {
			$pos = strrpos($c,'/');
			if($pos!==false)
				$last_cat = substr($c,$pos+1);
			else
				$last_cat = $c;
			$last_cat *= 4;
			$pages[$last_cat] = $last_cat;
		    }
		}
		if(empty($pages))
			$pages[23] = 23; //uncategorized
		if($aExp['iProducer']!==null && $aExp['iProducer']!=='') {
			$aExp['iProducer'] = $aExp['iProducer']*4+1;
			$pages[$aExp['iProducer']] = $aExp['iProducer'];
		}

    		$products[$aExp['iProduct']] = $aExp;
	        $products[$aExp['iProduct']]['sLinkName'] = '?'.$aExp['iProduct'].','.change2Url( $products[$aExp['iProduct']]['sName'] );
		$products[$aExp['iProduct']]['aCategories'] = $pages;
	}
	
	return $products;
  } // end function generateCache

  function listProductsQuery($iContent,$aProducts) {
    $oPage  =& Pages::getInstance( );
    $query = '';
    if( !isset( $aProducts ) ){
      if( isset( $GLOBALS['sPhrase'] ) && !empty( $GLOBALS['sPhrase'] ) ){
          $aExp   = explode( ' ', $GLOBALS['sPhrase'] );
	  $iCount = count( $aExp );
	  $aWords = array();
          for( $i = 0; $i < $iCount; $i++ ){
	    $aExp[$i] = trim( $aExp[$i] );
    	    if( !empty( $aExp[$i] ) )
        	  $aWords[] = $aExp[$i];
	  } // end for
    	  saveSearchedWords( $aWords );
	$query = '0';
	foreach($aWords as $w) {
		$query .= ' OR it.f_item_name LIKE \'%'.DB::addq($w).'%\' OR d.f_display_name LIKE \'%'.DB::addq($w).'%\' OR d.f_long_description LIKE \'%'.DB::addq($w).'%\' OR d.f_short_description LIKE \'%'.DB::addq($w).'%\'';
	}
        $sUrlExt .= ((defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true)?null:'&amp;').'sPhrase='.$GLOBALS['sPhrase'];
      }
      else{
	if($iContent==23) {
    	    $query .= 'it.f_category is null OR it.f_category=\'\'';
	} elseif($iContent==35) {
    	    $query .= 'pr.f_recommended=1';
	} elseif($iContent%4==0) {
            if( DISPLAY_SUBCATEGORY_PRODUCTS === true ){
	      // return all pages and subpages
    	      $aData = $oPage->throwAllChildrens( $iContent );
              if( isset( $aData ) ){
	        foreach( $aData as $iValue ){
	          $query .= 'it.f_category LIKE \'%__'.($iValue/4).'__%\' OR it.f_category LIKE \'%/'.($iValue/4).'__%\' OR ';
        	}
              }
	    }
    	    $query .= 'it.f_category LIKE \'%__'.($iContent/4).'__%\' OR it.f_category LIKE \'%/'.($iContent/4).'__%\'';
	} else {
    	    $query .= 'it.f_manufacturer='.(($iContent-1)/4);	
	}
      }
    } elseif(!empty($aProducts)) {
	$query = '0';
	foreach($aProducts as $p)
		$query .= ' OR it.id='.(int)$p;
    }
    return $query;
  }

  /**
  * List products
  * @return string
  * @param string $sFile
  * @param int    $iContent
  * @param int    $iList
  * @param array  $aProducts
  */
  function listProducts( $sFile, $iContent, $iList = null, $aProducts = null ){
    $oTpl   =& TplParser::getInstance( );
    $oFile  =& Files::getInstance( );
    $oPage  =& Pages::getInstance( );
    $content= null;
    $sUrlExt= null;

    $query = $this->listProductsQuery($iContent,$aProducts);

    if( $query ){
      $sBasketPage = ( isset( $GLOBALS['config']['basket_page'] ) && isset( $oPage->aPages[$GLOBALS['config']['basket_page']] ) ) ? $oPage->aPages[$GLOBALS['config']['basket_page']]['sLinkName'].((defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true)?'?':'&amp;') : null;

      $iCount = DB::GetOne('SELECT 	count(it.id) 
					FROM premium_ecommerce_products_data_1 pr
					INNER JOIN (premium_warehouse_items_data_1 it,premium_ecommerce_availability_data_1 av) ON (pr.f_item_name=it.id AND av.id=pr.f_available)
					LEFT JOIN premium_ecommerce_descriptions_data_1 d ON (d.f_item_name=it.id AND d.f_language="'.LANGUAGE.'" AND d.active=1)
					 WHERE pr.f_publish=1 AND pr.active=1 AND ('.$query.') ORDER BY pr.f_position');

      if( !isset( $iList ) ){
        $iList = $GLOBALS['config']['products_list'];
      }

      $iProducts = ceil( $iCount / $iList );
      $iPageNumber = isset( $GLOBALS['aActions']['o2'] ) ? $GLOBALS['aActions']['o2'] : 1;
      if( !isset( $iPageNumber ) || !is_numeric( $iPageNumber ) || $iPageNumber < 1 )
        $iPageNumber = 1;
      if( $iPageNumber > $iProducts && $iProducts>0)
        $iPageNumber = $iProducts;

      $iStart = ($iPageNumber-1) * $iList;

      $this->mData = null;

      $products = $this->getProducts($query,$iList,$iStart);
      $i=0;
      foreach($products as $aData){

        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData['sPrice'] = is_numeric( $aData['fPrice'] ) ? displayPrice( $aData['fPrice'] ) : $aData['fPrice'];
        $aData['sPages'] = $this->throwProductsPagesTree( $aData['aCategories'] );
        $aData['sBasket']= null;
        $aData['sRecommended'] = $aData['sRecommended']? $oTpl->tbHtml( $sFile, 'PRODUCTS_RECOMMENDED' ) : null;

        if( !empty( $aData['sDescriptionShort'] ) ){
          $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
          $oTpl->setVariables( 'aData', $aData );
          $aData['sDescriptionShort'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_DESCRIPTION' );
        }

        $oTpl->setVariables( 'aData', $aData );

        if( isset( $oFile->aImagesDefault[2][$aData['iProduct']] ) ){
          $aDataImage = $oFile->aFilesImages[2][$oFile->aImagesDefault[2][$aData['iProduct']]];
          $oTpl->setVariables( 'aDataImage', $aDataImage );
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_IMAGE' );
        }
        else{
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_NO_IMAGE' );
        }

        if( is_numeric( $aData['fPrice'] ) ){
          if( isset( $sBasketPage ) ){
            $aData['sBasketPage'] = $sBasketPage;
            $oTpl->setVariables( 'aData', $aData );
            $aData['sBasket'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_BASKET' );
          }
          $oTpl->setVariables( 'aData', $aData );
          $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_PRICE' );
        }
        else{
          $oTpl->setVariables( 'aData', $aData );
          $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_NO_PRICE' );
        }

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'PRODUCTS_LIST' );
        $i++;
      } // end for

      if( isset( $content ) ){
        if( $iCount > $iList ){
          $aData['sPages'] = countPages( $iCount, $iList, $iPageNumber, throwPageUrl( $oPage->aPages[$iContent]['sLinkName'], true ), $sUrlExt, FRIENDLY_LINKS );
          $aData['sHidePages'] = null;
        }
        else
          $aData['sHidePages'] = ' hide';

        $oTpl->setVariables( 'aData', $aData );
        return $oTpl->tbHtml( $sFile, 'PRODUCTS_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'PRODUCTS_FOOT' );
      }
    }
  } // end function listProducts

  /**
  * List products
  * @return string
  * @param string $sFile
  * @param int    $iContent
  * @param int    $iList
  * @param array  $aProducts
  */
  function listProductsGallery( $sFile, $iContent, $iList = null, $aProducts = null ){
    $oTpl   =& TplParser::getInstance( );
    $oFile  =& Files::getInstance( );
    $oPage  =& Pages::getInstance( );
    $content= null;
    $sUrlExt= null;
    $iColumns = 3;
    $iWidth   = (int) ( 100 / $iColumns );

    $query = $this->listProductsQuery($iContent,$aProducts);
    
    if( $query ){
      $sBasketPage = ( isset( $GLOBALS['config']['basket_page'] ) && isset( $oPage->aPages[$GLOBALS['config']['basket_page']] ) ) ? $oPage->aPages[$GLOBALS['config']['basket_page']]['sLinkName'].((defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true)?'?':'&amp;') : null;

      $iCount = DB::GetOne('SELECT 	count(it.id) 
					FROM premium_ecommerce_products_data_1 pr
					INNER JOIN (premium_warehouse_items_data_1 it,premium_ecommerce_availability_data_1 av) ON (pr.f_item_name=it.id AND av.id=pr.f_available)
					LEFT JOIN premium_ecommerce_descriptions_data_1 d ON (d.f_item_name=it.id AND d.f_language="'.LANGUAGE.'" AND d.active=1)
					 WHERE pr.f_publish=1 AND pr.active=1 AND ('.$query.') ORDER BY pr.f_position');

      if( !isset( $iList ) ){
        $iList = $GLOBALS['config']['products_list'];
      }
      $iList *= $iColumns;

      $iProducts = ceil( $iCount / $iList );
      $iPageNumber = isset( $GLOBALS['aActions']['o2'] ) ? $GLOBALS['aActions']['o2'] : 1;
      if( !isset( $iPageNumber ) || !is_numeric( $iPageNumber ) || $iPageNumber < 1 )
        $iPageNumber = 1;
      if( $iPageNumber > $iProducts && $iProducts>0)
        $iPageNumber = $iProducts;

      $iStart = ($iPageNumber-1) * $iList;

      $this->mData = null;

      $products = $this->getProducts($query,$iList,$iStart);
      $i = 0;
      foreach($products as $aData){

        $aData['iWidth']  = $iWidth;
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData['sPrice'] = is_numeric( $aData['fPrice'] ) ? displayPrice( $aData['fPrice'] ) : $aData['fPrice'];
        $aData['sPages'] = $this->throwProductsPagesTree( $aData['aCategories'] );
        $aData['sBasket']= null;
        $aData['sRecommended'] = $aData['sRecommended']? $oTpl->tbHtml( $sFile, 'PRODUCTS_RECOMMENDED' ) : null;

        if( $i > 0 && $i % $iColumns == 0 ){
          $oTpl->setVariables( 'aData', $aData );
          $content .= $oTpl->tbHtml( $sFile, 'PRODUCTS_GALLERY_BREAK' );
        }

        if( !empty( $aData['sDescriptionShort'] ) ){
          $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
          $oTpl->setVariables( 'aData', $aData );
          $aData['sDescriptionShort'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_DESCRIPTION' );
        }

        $oTpl->setVariables( 'aData', $aData );

        if( isset( $oFile->aImagesDefault[2][$aData['iProduct']] ) ){
          $aDataImage = $oFile->aFilesImages[2][$oFile->aImagesDefault[2][$aData['iProduct']]];
          $oTpl->setVariables( 'aDataImage', $aDataImage );
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_IMAGE' );
        }
        else{
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_NO_IMAGE' );
        }

        if( is_numeric( $aData['fPrice'] ) ){
          if( isset( $sBasketPage ) ){
            $aData['sBasketPage'] = $sBasketPage;
            $oTpl->setVariables( 'aData', $aData );
            $aData['sBasket'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_BASKET' );
          }
          $oTpl->setVariables( 'aData', $aData );
          $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_PRICE' );
        }
        else{
          $oTpl->setVariables( 'aData', $aData );
          $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_NO_PRICE' );
        }

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'PRODUCTS_GALLERY_LIST' );
        $i++;
      } // end for

      while( $i % $iColumns > 0 ){
        $content .= $oTpl->tbHtml( $sFile, 'PRODUCTS_GALLERY_BLANK' );
        $i++;
      } // end while

      if( isset( $content ) ){
        if( $iCount > $iList ){
          $aData['sPages'] = countPages( $iCount, $iList, $iPageNumber, throwPageUrl( $oPage->aPages[$iContent]['sLinkName'], true ), $sUrlExt, FRIENDLY_LINKS );
          $aData['sHidePages'] = null;
        }
        else
          $aData['sHidePages'] = ' hide';

        $oTpl->setVariables( 'aData', $aData );
        return $oTpl->tbHtml( $sFile, 'PRODUCTS_GALLERY_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'PRODUCTS_GALLERY_FOOT' );
      }
    }
  } // end function listProducts

  /**
  * Return page data
  * @return array
  * @param int  $iProduct
  */
  function throwProduct( $iProduct ){
    $aData = $this->getProduct($iProduct);
    if( $aData ){
	    $aData['sPrice'] = is_numeric( $aData['fPrice'] ) ? displayPrice( $aData['fPrice'] ) : $aData['fPrice'];
	    return $aData;
    }
    return null;
  } // end function throwProduct

  /**
  * Return products pages tree
  * @return string
  * @param int  $iProduct
  */
  function throwProductsPagesTree( $pages ){
      global $oPage;
      $content = null;
      $oPage->mData = null;
      foreach( $pages as $iPage ){
        if( isset( $content ) )
          $content .= '<em>|</em>';
        if( isset( $this->mData[$iPage] ) ){
          $content .= $this->mData[$iPage];
        }
        else{
          $sTree = $oPage->throwPagesTree( $iPage );
          if( !empty( $sTree ) )
            $sTree .= '&nbsp;&raquo;&nbsp;';
          $sTree .= '<a href="'.$oPage->aPages[$iPage]['sLinkName'].'">'.$oPage->aPages[$iPage]['sName'].'</a>';
          $content .= $this->mData[$iPage] = $sTree;
        }
      }

      return $content;
  } // end function throwProductsPagesTree

  /**
  * List products to sitemap
  * @return string
  * @param string $sFile
  * @param int    $iPage
  */
  function listSiteMap( $sFile, $iPage ){
    if( isset( $this->aProducts ) ){
      foreach( $this->aProducts as $prod){
        if( isset( $prod['aCategories'][$iPage] ) ){
          $aProducts[] = $prod['iProduct'];
        }
      }
      $aProducts = $this->getProducts('it.f_category LIKE \'%__'.($iPage/4).'__%\' OR it.f_category LIKE \'%/'.($iPage/4).'__%\'');

      if( isset( $aProducts ) ){
        $oTpl   =& TplParser::getInstance( );
        $content= null;
        foreach($aProducts as $aData) {
          $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
          $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
          if( is_numeric( $aData['fPrice'] ) ){
            $aData['sPrice'] = displayPrice( $aData['fPrice'] );
            $oTpl->setVariables( 'aData', $aData );
            $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'SITEMAP_PRODUCTS_PRICE' );
          }
          else{
            $aData['sPrice'] = $aData['fPrice'];
            $oTpl->setVariables( 'aData', $aData );
            $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'SITEMAP_PRODUCTS_NO_PRICE' );
          }

          $oTpl->setVariables( 'aData', $aData );
          $content .= $oTpl->tbHtml( $sFile, 'SITEMAP_PRODUCTS' );
        } // end for
        return $oTpl->tbHtml( $sFile, 'SITEMAP_HEAD_PRODUCTS' ).$content.$oTpl->tbHtml( $sFile, 'SITEMAP_FOOT_PRODUCTS' );
      }
    }
  } // end function listSiteMap

  /**
  * Return products cross sell
  * @return string
  * @param string $sFile
  * @param int    $iProduct
  */
  function listProductsCrossSell( $sFile, $iProduct ){
    $content= null;
    $oTpl   =& TplParser::getInstance( );
    
    $products = DB::GetAssoc('SELECT or_det.f_item_name,count(or_det.f_item_name) FROM premium_warehouse_items_orders_details_data_1 or_det 
			    WHERE or_det.f_item_name!=%d AND or_det.f_transaction_id IN 
			    (SELECT ord.f_transaction_id FROM premium_warehouse_items_orders_details_data_1 or_det2 
			    INNER JOIN premium_ecommerce_orders_data_1 ord ON ord.f_transaction_id=or_det2.f_transaction_id
			    WHERE ord.f_language=%s AND or_det2.f_item_name=%d) GROUP BY or_det.f_item_name ORDER BY count(or_det.f_item_name) DESC LIMIT 5',array($iProduct,LANGUAGE,$iProduct));


    foreach($products as $p=>$num) {
          $aData = $this->getProduct($p);
          $aData['iQuantity'] = $aSort[$i][0];
          $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
          $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
          if( is_numeric( $aData['fPrice'] ) ){
            $aData['sPrice'] = displayPrice( $aData['fPrice'] );
            $oTpl->setVariables( 'aData', $aData );
            $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'CROSS_SELL_PRICE' );
          }
          else{
            $aData['sPrice'] = $aData['fPrice'];
            $oTpl->setVariables( 'aData', $aData );
            $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'CROSS_SELL_NO_PRICE' );
          }

          $oTpl->setVariables( 'aData', $aData );
          $content .= $oTpl->tbHtml( $sFile, 'CROSS_SELL_LIST' );
        } // end for

        if( isset( $content ) )
          return $oTpl->tbHtml( $sFile, 'CROSS_SELL_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'CROSS_SELL_FOOT' );
  } // end function listProductsCrossSell
}
?>