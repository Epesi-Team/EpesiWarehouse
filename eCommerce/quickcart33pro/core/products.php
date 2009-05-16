<?php
class Products
{

  var $aProducts = null;
  var $aProductsPages = null;
  var $mData = null;
  var $aPages = null;

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
    $this->generateCache( );
  } // end function Pages

  /**
  * Generate cache variables
  * @return void
  */
  function generateCache( ){
  	//epesi {
	global $config;
    $iStatus    = throwStatus( );

    $this->aProducts       = null;
    $this->aProductsPages  = null;

	$currency = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s',array($config['currency_symbol']));
	if($currency===false) 
		die('Currency not defined in Epesi: '.$config['currency_symbol']);

	$ret = DB::Execute('SELECT 	it.id as iProduct, 
								it.f_item_name as sName2, 
								pri.f_gross_price as fPrice, 
								pri.f_tax_rate as tax,
								pr.f_publish as iStatus, 
								pr.f_position as iPosition, 
								it.f_category,
								av.f_availability_code as sAvailable2, 
								avl.f_label as sAvailable, 
								d.f_display_name as sName,
								d.f_short_description as sDescriptionShort,
								d.f_long_description as sDescriptionFull,
								d.f_page_title as sNameTitle, 
								d.f_meta_description as sMetaDescription, 
								d.f_keywords as sMetaKeywords,
								it.f_vendor,
								loc.f_quantity
					FROM premium_ecommerce_products_data_1 pr
					INNER JOIN (premium_warehouse_items_data_1 it,premium_ecommerce_availability_data_1 av) ON (pr.f_item_name=it.id AND av.id=pr.f_available)
					LEFT JOIN premium_ecommerce_prices_data_1 pri ON (pri.f_item_name=it.id AND pri.active=1 AND pri.f_currency='.$currency.')
					LEFT JOIN premium_ecommerce_descriptions_data_1 d ON (d.f_item_name=it.id AND d.f_language="'.LANGUAGE.'")
					LEFT JOIN premium_ecommerce_availability_labels_data_1 avl ON (pr.f_available=avl.f_availability AND avl.f_language="'.LANGUAGE.'") 
					LEFT JOIN premium_warehouse_location_data_1 loc ON (loc.f_item_sku=it.id AND loc.f_quantity>0)
					 WHERE pr.f_publish>=%d AND pr.active=1 ORDER BY pr.f_position',array($iStatus));

	$uncategorized = false;

	while($aExp = $ret->FetchRow()) {
		$ret2 = DB::Execute('SELECT pp.f_value,
									p.f_parameter_code as parameter_code,
									pl.f_label as parameter_label,
									g.f_group_code as group_code,
									gl.f_label as group_label
						FROM premium_ecommerce_products_parameters_data_1 pp
						INNER JOIN (premium_ecommerce_parameters_data_1 p,premium_ecommerce_parameter_groups_data_1 g) ON (p.id=pp.f_parameter AND g.id=pp.f_group)
						LEFT JOIN premium_ecommerce_parameter_labels_data_1 pl ON (pl.f_parameter=p.id AND pl.f_language="'.LANGUAGE.'")
						LEFT JOIN premium_ecommerce_parameter_group_labels_data_1 gl ON (gl.f_group=g.id AND gl.f_language="'.LANGUAGE.'")
						WHERE pp.f_item_name=%d AND pp.f_language="'.LANGUAGE.'" ORDER BY g.f_position,gl.f_label,g.f_group_code,p.f_position,pl.f_label,p.f_parameter_code',array($aExp['iProduct']));
		$paramteres = array();
		$last_group = null;
		while($bExp = $ret2->FetchRow()) {
			$paramteres[] = '<td>'.($last_group!=$bExp['group_code']?($bExp['group_label']?$bExp['group_label']:$bExp['group_code']):'').'</td><td>'.($bExp['parameter_label']?$bExp['parameter_label']:$bExp['parameter_code']).'</td><td>'.$bExp['f_value'].'</td>'; 
			$last_group = $bExp['group_code'];
		}
		if (!empty($paramteres)) {
			$aExp['sDescriptionFull'] = $aExp['sDescriptionFull'].
				'<br>'.
				'<table>'.
					'<tr>'.
						implode('</tr><tr>',$paramteres).
					'</tr>'.
				'</table>';
		}
		if($aExp['sName']=='') 
			$aExp['sName'] = $aExp['sName2'];
		if($aExp['sAvailable']=='') 
			$aExp['sAvailable'] = $aExp['sAvailable2'];
		if(!$aExp['f_quantity'])
			unset($aExp['fPrice']);
		$aExp['iComments'] = 1;
		unset($aExp['sName2']);
		$cats = array_filter(explode('__',$aExp['f_category']));
		unset($aExp['f_category']);
		$pages = array();
		if(empty($cats)) {
		    $pages[23] = 23; //uncategorized
		    $uncategorized = true;
		} else {
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
		if($aExp['f_vendor']!=='') {
			$id = $aExp['f_vendor']*4+1;
			$pages[$id] = $id;
		}
		unset($aExp['f_vendor']);
        $this->aProducts[$aExp['iProduct']] = $aExp;
        $this->aProducts[$aExp['iProduct']]['sLinkName'] = '?'.$aExp['iProduct'].','.change2Url( $this->aProducts[$aExp['iProduct']]['sName'] );
        $this->aProductsPages[$aExp['iProduct']] = $pages;
	}
	if(!$uncategorized) {//remove uncategorized category
	    unset(Pages::getInstance()->aPages[23]);
	}
	//} epesi
/*
    if( !is_file( DB_PRODUCTS ) )
      return null;

    $oPage =& Pages::getInstance( );

    $aFile = file( DB_PRODUCTS_PAGES );
    $iCount= count( $aFile );
    for( $i = 1; $i < $iCount; $i++ ){
      $aExp = explode( '$', $aFile[$i] );
      if( isset( $oPage->aPages[$aExp[1]] ) ){
        $aPages[$aExp[0]][$aExp[1]] = $aExp[1];
      }
    } // end for


    $aFile      = file( DB_PRODUCTS );
    $iCount     = count( $aFile );
    $sFunction  = LANGUAGE.'_products';
    $iStatus    = throwStatus( );
    $sLanguageUrl  = ( LANGUAGE_IN_URL == true ) ? LANGUAGE.LANGUAGE_SEPARATOR : null;

    $this->aProducts       = null;
    $this->aProductsPages  = null;

    for( $i = 1; $i < $iCount; $i++ ){
      $aExp = explode( '$', $aFile[$i] );
      if( isset( $aExp[3] ) && $aExp[3] >= $iStatus && ( !defined( 'CUSTOMER_PAGE' ) || isset( $aPages[$aExp[0]] ) ) ){
        $this->aProducts[$aExp[0]] = $sFunction( $aExp );
        $this->aProducts[$aExp[0]]['sLinkName'] = throwPageUrl( $aExp[0].','.$sLanguageUrl.change2Url( $this->aProducts[$aExp[0]]['sName'] ) );
        $this->aProductsPages[$aExp[0]] = isset( $aPages[$aExp[0]] ) ? $aPages[$aExp[0]] : null;
      }
    } // end for*/
  } // end function generateCache


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

    if( !isset( $aProducts ) ){
      if( isset( $GLOBALS['sPhrase'] ) && !empty( $GLOBALS['sPhrase'] ) ){
        $aProducts = $this->generateProductsSearchListArray( $GLOBALS['sPhrase'] );
        $sUrlExt .= ((defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true)?null:'&amp;').'sPhrase='.$GLOBALS['sPhrase'];
      }
      else{
        if( DISPLAY_SUBCATEGORY_PRODUCTS === true ){
          // return all pages and subpages
          $aData = $oPage->throwAllChildrens( $iContent );
          if( isset( $aData ) ){
            foreach( $aData as $iValue ){
              $this->aPages[$iValue] = $iValue;
            }
          }
        }
        $this->aPages[$iContent] = $iContent;
        $aProducts = $this->generateProductsListArray( $iContent );
      }
    }

    if( isset( $aProducts ) ){
      $sBasketPage = ( isset( $GLOBALS['config']['basket_page'] ) && isset( $oPage->aPages[$GLOBALS['config']['basket_page']] ) ) ? $oPage->aPages[$GLOBALS['config']['basket_page']]['sLinkName'].((defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true)?'?':'&amp;') : null;

      $iCount    = count( $aProducts );
      if( !isset( $iList ) ){
        $iList = $GLOBALS['config']['products_list'];
      }

      $iProducts = ceil( $iCount / $iList );
      $iPageNumber = isset( $GLOBALS['aActions']['o2'] ) ? $GLOBALS['aActions']['o2'] : 1;
      if( !isset( $iPageNumber ) || !is_numeric( $iPageNumber ) || $iPageNumber < 1 )
        $iPageNumber = 1;
      if( $iPageNumber > $iProducts )
        $iPageNumber = $iProducts;

      $iEnd   = $iPageNumber * $iList;
      $iStart = $iEnd - $iList;

      if( $iEnd > $iCount )
        $iEnd = $iCount;
      $this->mData = null;

      for( $i = $iStart; $i < $iEnd; $i++ ){
        $aData = $this->aProducts[$aProducts[$i]];

        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData['sPrice'] = is_numeric( $aData['fPrice'] ) ? displayPrice( $aData['fPrice'] ) : $aData['fPrice'];
        $aData['sPages'] = $this->throwProductsPagesTree( $aData['iProduct'] );
        $aData['sBasket']= null;
        $aData['sRecommended'] = isset( $GLOBALS['aProductsRecommended'][$aData['iProduct']] ) ? $oTpl->tbHtml( $sFile, 'PRODUCTS_RECOMMENDED' ) : null;

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
  * Return page data
  * @return array
  * @param int  $iProduct
  */
  function throwProduct( $iProduct ){
    if( isset( $this->aProducts[$iProduct] ) ){
	  //epesi {
	  $aData = $this->aProducts[$iProduct];
          if( isset( $aData ) ){
            $aData['aCategories'] = $this->aProductsPages[$iProduct];
	    $aData['sPrice'] = is_numeric( $this->aProducts[$iProduct]['fPrice'] ) ? displayPrice( $this->aProducts[$iProduct]['fPrice'] ) : $this->aProducts[$iProduct]['fPrice'];
            return array_merge( $this->aProducts[$iProduct], $aData );
	  }
	  return $aData;
	  //} epesi
/*      $aFile      = file( DB_PRODUCTS_EXT );
      $iCount     = count( $aFile );
      $sFunction  = LANGUAGE.'_products_ext';
      for( $i = 1; $i < $iCount; $i++ ){
        $aExp = explode( '$', $aFile[$i] );
        if( $aExp[0] == $iProduct ){
          $aData = $sFunction( $aExp );
          break;
        }
      } // end for
      if( isset( $aData ) ){
        $aFile = null;
        $aData['aCategories'] = $this->aProductsPages[$iProduct];
        $aData['sPrice'] = is_numeric( $this->aProducts[$iProduct]['fPrice'] ) ? displayPrice( $this->aProducts[$iProduct]['fPrice'] ) : $this->aProducts[$iProduct]['fPrice'];
        return array_merge( $this->aProducts[$iProduct], $aData );
      }*/
    }
    else
      return null;
  } // end function throwProduct

  /**
  * Return array with products
  * @return array
  * @param int  $iContent
  */
  function generateProductsListArray( $iContent ){
    if( isset( $this->aProducts ) ){
      foreach( $this->aProductsPages as $iProduct => $aData ){
        foreach( $this->aPages as $iValue ){
          if( isset( $aData[$iValue] ) && !isset( $aProducts[$iProduct] ) ){
            $aReturn[] = $iProduct;
            $aProducts[$iProduct] = true;
          }
        } // end foreach
      } // end foreach
      if( isset( $aReturn ) )
        return $aReturn;
    }
  } // end function generateProductsListArray

  /**
  * Return array with products
  * @return array
  * @param string $sPhrase
  */
  function generateProductsSearchListArray( $sPhrase ){
    if( isset( $this->aProducts ) ){
      $aExp   = explode( ' ', $sPhrase );
      $iCount = count( $aExp );
      for( $i = 0; $i < $iCount; $i++ ){
        $aExp[$i] = trim( $aExp[$i] );
        if( !empty( $aExp[$i] ) )
          $aWords[] = $aExp[$i];
      } // end for

      if( isset( $aWords ) && is_array( $aWords ) && function_exists( 'saveSearchedWords' ) && defined( 'CUSTOMER_PAGE' ) )
        saveSearchedWords( $aWords );

      $iCount = isset( $aWords ) ? count( $aWords ) : 0;
      foreach( $this->aProducts as $iProduct => $aData ){
        $iFound = 0;

        for( $i = 0; $i < $iCount; $i++ ){
          if( stristr( implode( ' ', $aData ), $aWords[$i] ) )
            $iFound++;
        } // end for

        if( $iFound == $iCount ){
          $aFound[$iProduct] = true;
        }
        else{
          $aNotFound[$iProduct] = true;
        }
      }

      if( isset( $aNotFound ) && ( $GLOBALS['config']['search_products_description'] === true || !defined( 'CUSTOMER_PAGE' ) ) ){
        $rFile = fopen( DB_PRODUCTS_EXT, 'r' );
        $i2    = 0;
        while( ( $aFile = fgetcsv( $rFile, 200000, '$' ) ) !== FALSE ){
          if( $i2 >= 1 && isset( $aNotFound[$aFile[0]] ) ){
            $iFound = 0;

            for( $i = 0; $i < $iCount; $i++ ){
              if( stristr( implode( ' ', $aFile ), $aWords[$i] ) )
                $iFound++;
            } // end for

            if( $iFound == $iCount )
              $aFound[$aFile[0]] = true;
          }
          $i2++;
        } // end while
        fclose( $rFile );
      }

      if( isset( $aFound ) ){
        foreach( $this->aProducts as $iProduct => $aData ){
          if( isset( $aFound[$iProduct] ) )
            $aReturn[] = $iProduct;
        } // end foreach
        if( isset( $aReturn ) )
          return $aReturn;
      }
    }
  } // end function generateProductsSearchListArray

  /**
  * Return products pages tree
  * @return string
  * @param int  $iProduct
  */
  function throwProductsPagesTree( $iProduct ){
    global $oPage;
    if( isset( $this->aProductsPages[$iProduct] ) ){
      $content = null;
      $oPage->mData = null;
      foreach( $this->aProductsPages[$iProduct] as $iPage ){
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
    }
  } // end function throwProductsPagesTree

  /**
  * List products to sitemap
  * @return string
  * @param string $sFile
  * @param int    $iPage
  */
  function listSiteMap( $sFile, $iPage ){
    if( isset( $this->aProducts ) ){
      foreach( $this->aProductsPages as $iProduct => $aPages ){
        if( isset( $aPages[$iPage] ) ){
          $aProducts[] = $iProduct;
        }
      }

      if( isset( $aProducts ) ){
        $oTpl   =& TplParser::getInstance( );
        $content= null;
        $iCount = count( $aProducts );
        for( $i = 0; $i < $iCount; $i++ ){
          $aData = $this->aProducts[$aProducts[$i]];

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
    $iMax   = 5;
    $content= null;
    $sLang  = strtolower( LANGUAGE );
    $oTpl   =& TplParser::getInstance( );

    $aFile = file( DB_ORDERS );
    $iCount= count( $aFile );
    for( $i = 1; $i < $iCount; $i++ ){
      $aExp = explode( '$', $aFile[$i], 3 );
      if( $aExp[1] == $sLang )
        $aOrdersLang[$aExp[0]] = true;
    } // end for

    if( isset( $aOrdersLang ) ){
      $aFile  = file( DB_ORDERS_PRODUCTS );
      $iCount = count( $aFile );
      for( $i = 1; $i < $iCount; $i++ ){
        $aExp =  explode( '$', $aFile[$i], 4 );
        if( $aExp[2] == $iProduct && isset( $aOrdersLang[$aExp[1]] ) ){
          $aOrders[$aExp[1]] = true;
        }
      } // end for
    }

    if( isset( $aOrders ) ){
      for( $i = 1; $i < $iCount; $i++ ){
        $aExp =  explode( '$', $aFile[$i], 5 );
        if( isset( $this->aProducts[$aExp[2]] ) && isset( $aOrders[$aExp[1]] ) && $aExp[2] != $iProduct ){
          if( !isset( $aSort[$aExp[2]][0] ) ){
            $aSort[$aExp[2]][0] = 0;
            $aSort[$aExp[2]][1] = $aExp[2];
          }
          $aSort[$aExp[2]][0] += $aExp[3];
        }
      } // end for

      if( isset( $aSort ) ){
        rsort( $aSort );
        $iCount = count( $aSort );
        if( $iCount > $iMax )
          $iCount = $iMax;

        for( $i = 0; $i < $iCount; $i++ ){
          $aData = $this->aProducts[$aSort[$i][1]];
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
      }
    }
  } // end function listProductsCrossSell
}
?>