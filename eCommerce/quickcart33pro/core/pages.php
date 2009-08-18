<?php
class Pages
{

  var $aPages = null;
  var $aPagesChildrens = null;
  var $aPagesParentsTypes = null;
  var $aPagesParents = null;
  var $aPageParents = null;
  var $mData = null;

  function &getInstance( ){
    static $oInstance = null;
    if( !isset( $oInstance ) ){
      $oInstance = new Pages( );
    }
    return $oInstance;
  } // end function getInstance

  /**
  * Constructor
  * @return void
  */
  function Pages( ){
    $this->generateCache( );
  } // end function Pages

  /**
  * Return pages to menu
  * @return string
  * @param string $sFile
  * @param int    $iType
  * @param int    $iPageCurrent
  * @param int    $iDepthLimit
  */
  function throwMenu( $sFile, $iType, $iPageCurrent = null, $iDepthLimit = 1 ){

    if( !isset( $this->aPagesParentsTypes[$iType] ) )
      return null;
    $this->mData = null;

    if( isset( $iPageCurrent ) )
      $this->generatePageParents( $iPageCurrent );

    $this->generateMenuData( $iType, $iPageCurrent, $iDepthLimit, 0 );
    if( isset( $this->mData[0] ) ){
      $oTpl     =& TplParser::getInstance( );
      $content  = null;
      $i        = 0;
      $iCount   = count( $this->mData[0] );

      if($iType==1) {
        global $config;
	$iCount += count($config['available_lang']);
	
	$url = null;
	foreach( $_GET as $mKey => $mValue )
    	    if( strstr( $mKey, ',' ) ){
	        $x = explode( ',', $mKey );
		foreach($x as &$v) {
			if(is_numeric($v)) continue;
			$y = explode(LANGUAGE_SEPARATOR,$v,2);
			if(isset($y[1])) $v = $y[1];
			$v = '__LANG__'.$v;
		}
		$url = implode(',',$x);
		break;
	    }
	if(!$url)
	    $url = '__LANG__,';
	foreach($config['available_lang'] as $lang) {
    	    $aData = array();
	    $aData['sLinkName'] = $_SERVER['SCRIPT_NAME'].'?'.str_replace('__LANG__',$lang.LANGUAGE_SEPARATOR,$url);
	    $aData['sName'] = '<img src="config/'.$lang.'.gif" />';
    	    $aData['sStyle']    = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
    	    $aData['sSelected'] = ( $lang==LANGUAGE ) ? $oTpl->tbHtml( $sFile, 'SELECTED' ) : null;
    	
	    $oTpl->setVariables( 'aData', $aData );
	    $content .= $oTpl->tbHtml( $sFile, 'LIST_LANG' );
	}

      }
      
      foreach( $this->mData[0] as $iPage => $bValue ){
        $aData = $this->aPages[$iPage];

        $aData['sSubContent'] = isset( $this->mData[$iPage] ) ? $this->throwSubMenu( $sFile, $iPage, $iPageCurrent, 1 ) : null;

        $aData['iStyle']    = ( $i % 2 ) ? 0: 1;
        $aData['sStyle']    = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData['iDepth']    = 0;
        $aData['sSelected'] = ( $aData['iPage'] == $iPageCurrent ) ? $oTpl->tbHtml( $sFile, 'SELECTED' ) : null;

        $oTpl->setVariables( 'aData', $aData );
        $content .= ( isset( $GLOBALS['config']['basket_page'] ) && $GLOBALS['config']['basket_page'] == $iPage ) ? $oTpl->tbHtml( $sFile, 'LIST_BASKET' ) : $oTpl->tbHtml( $sFile, 'LIST' );

        $i++;
      } // end foreach

      if( isset( $content ) ){
        $aData['sMenuType'] = $GLOBALS['aMenuTypes'][$iType];
        $oTpl->setVariables( 'aData', $aData );
        return $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );
      }
    }
  } // end function throwMenu

  /**
  * Display sub menu
  * @return string
  * @param string $sFile
  * @param int    $iPageParent
  * @param int    $iPageCurrent
  * @param int    $iDepth
  */
  function throwSubMenu( $sFile, $iPageParent, $iPageCurrent, $iDepth = 1 ){
    if( isset( $this->mData[$iPageParent] ) ){
      $oTpl     =& TplParser::getInstance( );
      $content  = null;
      $i        = 0;
      $iCount   = count( $this->mData[$iPageParent] );

      foreach( $this->mData[$iPageParent] as $iPage => $bValue ){
        $aData = $this->aPages[$iPage];

        $aData['sSubContent'] = isset( $this->aPagesChildrens[$iPage] ) ? $this->throwSubMenu( $sFile, $iPage, $iPageCurrent, $iDepth + 1 ) : null;

        $aData['iStyle']    = ( $i % 2 ) ? 0: 1;
        $aData['sStyle']    = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData['iDepth']    = $iDepth;
        $aData['sSelected'] = ( $aData['iPage'] == $iPageCurrent ) ? $oTpl->tbHtml( $sFile, 'SELECTED' ) : null;

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'LIST' );
        $i++;
      }

      if( isset( $content ) ){
        return $oTpl->tbHtml( $sFile, 'HEAD_SUB' ).$content.$oTpl->tbHtml( $sFile, 'FOOT_SUB' );
      }
    }
  } // end function throwSubMenu

  /**
  * Return variable with menu
  * @return null
  * @param int    $iType
  * @param int    $iPageCurrent
  * @param int    $iDepthLimit
  * @param int    $iDepth
  * @param int    $iPageParent
  */
  function generateMenuData( $iType, $iPageCurrent, $iDepthLimit, $iDepth = 0, $iPageParent = null ){
    if( !isset( $this->mData ) ){
      $aData = $this->aPagesParentsTypes[$iType];
    }
    else{
      if( isset( $this->aPagesChildrens[$iPageParent] ) )
        $aData = $this->aPagesChildrens[$iPageParent];
    }

    if( isset( $aData ) ){
      foreach( $aData as $iKey => $iPage ){
        $this->mData[$this->aPages[$iPage]['iPageParent']][$iPage] = true;
        if( $iDepthLimit > $iDepth && ( $iPageCurrent == $iPage || isset( $this->aPageParents[$iPage] ) || DISPLAY_EXPANDED_MENU === true ) && $this->aPages[$iPage]['iSubpagesShow'] != 3 ){
          $this->generateMenuData( $iType, $iPageCurrent, $iDepthLimit, $iDepth + 1, $iPage );
        }
      } // end foreach
    }
  } // end function generateMenuData

  /**
  * Return page data
  * @return array
  * @param int  $iPage
  */
  function throwPage( $iPage ){
    if( isset( $this->aPages[$iPage] ) ){
/*      $aFile      = file( DB_PAGES_EXT );
      $iCount     = count( $aFile );
      $sFunction  = LANGUAGE.'_pages_ext';
      for( $i = 1; $i < $iCount; $i++ ){
        $aExp = explode( '$', $aFile[$i] );
        if( $aExp[0] == $iPage ){
          $aData = $sFunction( $aExp );
          break;
        }
      } // end for
      if( isset( $aData ) ){
        $aFile = null;
        if( defined( 'CUSTOMER_PAGE' ) && strstr( $aData['sDescriptionFull'], '[break]' ) ){
          $aExp = explode( '[break]', $aData['sDescriptionFull'] );
          if( isset( $GLOBALS['aActions']['o4'] ) && is_numeric( $GLOBALS['aActions']['o4'] ) )
            $iPageContent = $GLOBALS['aActions']['o4'];
          else
            $iPageContent = 1;

          if( isset( $aExp[$iPageContent - 1] ) ){
            $aData['sDescriptionFull'] = $aExp[$iPageContent - 1];
            $aData['sPages'] = countPages( count( $aExp ), 1, $iPageContent, throwPageUrl( $this->aPages[$iPage]['sLinkName'].',,', true ), null, FRIENDLY_LINKS );
          }
        }

        return array_merge( $this->aPages[$iPage], $aData );
      }*/
      	  //epesi {
	  return $this->aPages[$iPage];
	  //} epesi

    }
    else
      return null;
  } // end function throwPage

  /**
  * Return pages tree
  * @return string
  * @param int  $iPage
  * @param int  $iPageCurrent
  */
  function throwPagesTree( $iPage, $iPageCurrent = null ){
    if( !isset( $iPageCurrent ) ){
      $iPageCurrent = $iPage;
      $this->mData  = null;
    }

    if( isset( $this->aPagesParents[$iPage] ) && isset( $this->aPages[$this->aPagesParents[$iPage]] ) ){
      $this->mData[] = '<a href="'.$this->aPages[$this->aPagesParents[$iPage]]['sLinkName'].'">'.$this->aPages[$this->aPagesParents[$iPage]]['sName'].'</a>';
      return $this->throwPagesTree( $this->aPagesParents[$iPage], $iPageCurrent );
    }
    else{
      if( isset( $this->mData ) ){
        $aReturn = array_reverse( $this->mData );
        $this->mData = null;
        return implode( '&nbsp;&raquo;&nbsp;', $aReturn );
      }
    }
  } // end function throwPagesTree

  /**
  * Return all childrens
  * @return array
  * @param int  $iPage
  */
  function throwAllChildrens( $iPage ){
    $bFirst = !isset( $this->mData ) ? true : null;
    if( isset( $this->aPagesChildrens[$iPage] ) ){
      foreach( $this->aPagesChildrens[$iPage] as $iValue ){
        if( isset( $this->aPages[$iValue] ) ){
          $this->mData[] = $iValue;
          $this->throwAllChildrens( $iValue );
        }
      }
    }
    return isset( $bFirst ) ? $this->mData : null;
  } // end function throwAllChildrens

  /**
  * Return list of subpages
  * @return string
  * @param int    $iPage
  * @param string $sFile
  * @param int    $iType
  */
  function listSubpages( $iPage, $sFile, $iType ){

    if( isset( $this->aPagesChildrens[$iPage] ) ){
      if( $iType > 1 ){
        $oFile =& Files::getInstance( );
      }

      $iCount = count( $this->aPagesChildrens[$iPage] );
      $content= null;
      $oTpl   =& TplParser::getInstance( );

      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aPages[$this->aPagesChildrens[$iPage][$i]];
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;

        if( !empty( $aData['sDescriptionShort'] ) ){
          $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
          $oTpl->setVariables( 'aData', $aData );
          $aData['sDescriptionShort'] = $oTpl->tbHtml( $sFile, 'SUBPAGES_DESCRIPTION_'.$iType );
        }

        $oTpl->setVariables( 'aData', $aData );

        if( isset( $oFile ) && isset( $oFile->aImagesDefault[1][$aData['iPage']] ) ){
          $aDataImage = $oFile->aFilesImages[1][$oFile->aImagesDefault[1][$aData['iPage']]];
          $oTpl->setVariables( 'aDataImage', $aDataImage );
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'SUBPAGES_IMAGE_'.$iType );
        }
        else{
          if( $iType > 1 ){
            $aData['sImage'] = $oTpl->tbHtml( $sFile, 'SUBPAGES_NO_IMAGE_'.$iType );
          }
        }

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'SUBPAGES_LIST_'.$iType );
      } // end for

      if( isset( $content ) ){
        return $oTpl->tbHtml( $sFile, 'SUBPAGES_HEAD_'.$iType ).$content.$oTpl->tbHtml( $sFile, 'SUBPAGES_FOOT_'.$iType );
      }
    }
  } // end function listSubpages

  /**
  * Generate cache variables
  * @return void
  */
  function generateCache( ){
  	//epesi {
	global $config;
    $this->aPages             = null;
    $this->aPagesChildrens    = null;
    $this->aPagesParents      = null;
    $this->aPagesParentsTypes = null;

	//categories - id mod 4 == 0
	$query = 'SELECT c.id, c.f_category_name, c.f_parent_category, 
					d.f_page_title, d.f_meta_description, d.f_keywords, 
					d.f_display_name, d.f_short_description, d.f_long_description,
					c.f_position
			FROM premium_warehouse_items_categories_data_1 c LEFT JOIN premium_ecommerce_cat_descriptions_data_1 d ON (c.id=d.f_category AND d.f_language="'.LANGUAGE.'" AND d.active=1) WHERE c.active=1';
	$x = DB::GetAll($query.' ORDER BY c.f_parent_category,c.f_position');
	foreach($x as $r) {
		if(!$r['f_parent_category']) 
			$r['f_parent_category'] = 0;
		else
			$r['f_parent_category'] *= 4;
		$id = $r['id']*4;
        $this->aPages[$id] = array('iPage' => $id, 'iPageParent' => $r['f_parent_category'], 'sName' => $r['f_display_name']?$r['f_display_name']:$r['f_category_name'], 'sNameTitle' => $r['f_page_title'], 'sDescriptionShort' => $r['f_short_description'], 'iPosition' => $r['f_position'], 'iType' => 3, 'iSubpagesShow' => 1, 'iProducts' => 1, 'sDescriptionFull'=>$r['f_long_description'], 'sMetaDescription' => $r['f_meta_description'], 'sMetaKeywords' =>$r['f_keywords'] );
        $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
        if( $r['f_parent_category'] > 0 ){
          $this->aPagesChildrens[$r['f_parent_category']][] = $id;
          $this->aPagesParents[$id] = $r['f_parent_category'];
        }else{
            $this->aPagesParentsTypes[3][] = $id;
        }
	}

	//companies - id mod 4 == 1
	$query = 'SELECT c.id, c.f_company_name
			FROM premium_warehouse_items_data_1 i INNER JOIN (company_data_1 c,premium_ecommerce_products_data_1 d) 
			ON (c.id=i.f_manufacturer AND d.f_item_name=i.id AND d.active=1)
			WHERE i.active=1';
			
	$x = DB::GetAll($query);
	foreach($x as $r) {
		$id = $r['id']*4+1;
        $this->aPages[$id] = array('iPage' => $id, 'iPageParent' => 0, 'sName' => $r['f_company_name'], 'sNameTitle' => $r['f_company_name'], 'sDescriptionShort' => '', 'iPosition' => 0, 'iType' => 4, 'iSubpagesShow' => 1, 'iProducts' => 1, 'sDescriptionFull'=>'', 'sMetaDescription' => '', 'sMetaKeywords' =>'' );
        $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
        $this->aPagesParentsTypes[4][] = $id;
	}
	
	//pages - id mod 4 == 2
	$i = 0;
	$query = 'SELECT p.id, p.f_page_name, p.f_parent_page, p.f_type,
					p.f_position, p.f_show_subpages_as, d.f_name, d.f_short_description, d.f_long_description, 
					d.f_page_title, d.f_meta_description, d.f_keywords
			FROM premium_ecommerce_pages_data_1 p INNER JOIN premium_ecommerce_pages_data_data_1 d ON (p.id=d.f_page AND d.f_language="'.LANGUAGE.'" AND d.active=1) WHERE p.active=1 AND p.f_publish=1 AND p.f_parent_page';
	$x = DB::GetAll($query.' is null');
	while($i<count($x)) {
		$ret = DB::Execute($query.'=%d',array($x[$i]['id']));
		while($row = $ret->FetchRow())
			$x[] = $row;
		$i++;
	}
	foreach($x as $r) {
		if(!$r['f_parent_page']) 
			$r['f_parent_page'] = 0;
		else
			$r['f_parent_page'] = $r['f_parent_page']*4+2;
		$id = $r['id']*4+2;
        $this->aPages[$id] = array('iPage' => $id, 'iPageParent' => $r['f_parent_page'], 'sName' => $r['f_name'], 'sNameTitle' => $r['f_page_title'], 'sDescriptionShort' => $r['f_short_description'], 'iPosition' => $r['f_position'], 'iType' => $r['f_type'], 'iSubpagesShow' => $r['f_show_subpages_as'], 'iProducts' => 0, 'sDescriptionFull'=>$r['f_long_description'], 'sMetaDescription' => $r['f_meta_description'], 'sMetaKeywords' =>$r['f_keywords'] );
        $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
        if( $r['f_parent_page'] > 0 ){
          $this->aPagesChildrens[$r['f_parent_page']][] = $id;
          $this->aPagesParents[$id] = $r['f_parent_page'];
        }else{
	        $this->aPagesParentsTypes[$r['f_type']][] = $id;
        }
	}
	
	//other pages - id mod 4 == 3
	//basket
	$id = 3;
	$config['basket_page'] = $id;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'basket', 'sNameTitle' => '', 'sDescriptionShort' => '', 'iPosition' => 0,
					 'iType' => 1, 'iSubpagesShow' => 1, 'iProducts' => 0);
    $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
    $this->aPagesParentsTypes[1][] = $id;
	//order
	$id = 7;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'order form', 'sNameTitle' => '', 'sDescriptionShort' => '', 'iPosition' => 0,
					 'iType' => 5, 'iSubpagesShow' => 1, 'iProducts' => 0, 'sTheme'=>'order.php');
    $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
    $this->aPagesParentsTypes[5][] = $id;
	//start page
	$id = 11;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'Start', 'sNameTitle' => '', 'sDescriptionShort' => getVariable('ecommerce_start_page'), 'iPosition' => 0,
					 'iType' => 2, 'iSubpagesShow' => 1, 'iProducts' => 0);
    $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
    $this->aPagesParentsTypes[2][] = $id;
	//rules and policies
	$id = 15;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'Rules and Policies', 'sNameTitle' => '', 'sDescriptionShort' => getVariable('ecommerce_rules'), 'iPosition' => 1,
					 'iType' => 2, 'iSubpagesShow' => 1, 'iProducts' => 0);
    $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
    $this->aPagesParentsTypes[2][] = $id;
	//search
	$id = 19;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'Search results', 'sNameTitle' => '', 'sDescriptionShort' => '', 'iPosition' => 0,
					 'iType' => 5, 'iSubpagesShow' => 1, 'iProducts' => 0);
    $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
    $this->aPagesParentsTypes[5][] = $id;

	//uncategorized products
	$id = 23;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'Uncategorized', 'sNameTitle' => 'Uncategorized', 'sDescriptionShort' => '', 'iPosition' => 1000, 'iType' => 3, 'iSubpagesShow' => 1, 'iProducts' => 1, 'sDescriptionFull'=>'', 'sMetaDescription' => '', 'sMetaKeywords' =>'' );
	$this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
	$this->aPagesParentsTypes[3][] = $id;
	
	//sitemap
	$id = 27;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'site map', 'sNameTitle' => '', 'sDescriptionShort' => '', 'iPosition' => 1000, 'iType' => 0, 'iSubpagesShow' => 1, 'iProducts' => 0, 'sDescriptionFull'=>'', 'sMetaDescription' => '', 'sMetaKeywords' =>'' );
	$this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
	$this->aPagesParentsTypes[1][] = $id;

	//sitemap
	$id = 31;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'contact us', 'sNameTitle' => '', 'sDescriptionShort' => getVariable('ecommerce_contactus'), 'iPosition' => 1000, 'iType' => 0, 'iSubpagesShow' => 1, 'iProducts' => 0, 'sDescriptionFull'=>'', 'sMetaDescription' => '', 'sMetaKeywords' =>'');
	$this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
	$this->aPagesParentsTypes[2][] = $id;
	//} epesi
  } // end function generateCache

  /**
  * Generate page all parents
  * @return void
  * @param int  $iPage
  */
  function generatePageParents( $iPage ){
    if( isset( $this->aPagesParents[$iPage] ) ){
      $this->aPageParents[$this->aPagesParents[$iPage]] = true;
      $this->generatePageParents( $this->aPagesParents[$iPage] );
    }
  } // end function generatePageParents

  /**
  * Return sitemap list
  * @return string
  * @param string $sFile
  * @param int    $iPageParent
  * @param int    $iDepth
  */
  function listSiteMap( $sFile, $iPageParent = null, $iDepth = 0 ){
    $iSiteMap = $GLOBALS['config']['site_map'];
    if( !isset( $iPageParent ) ){
      foreach( $this->aPages as $iPage => $aData ){
        if( !isset( $this->aPagesParents[$iPage] ) && $aData['iType'] != 5 && $iSiteMap != $iPage )
          $aPages[] = $iPage;
      }
    }
    else{
      if( isset( $this->aPagesChildrens[$iPageParent] ) )
        $aPages = $this->aPagesChildrens[$iPageParent];
    }

    if( isset( $aPages ) ){
      $oTpl     =& TplParser::getInstance( );
      $oProduct =& Products::getInstance( );
      $content  = null;
      $i        = 0;
      $iCount   = count( $aPages );

      for( $i = 0; $i < $iCount; $i++ ){
        $iPage = $aPages[$i];
        $aData = $this->aPages[$iPage];

        $aData['sSubContent'] = ( isset( $this->aPagesChildrens[$iPage] ) && $aData['iSubpagesShow'] != 3 ) ? $this->listSiteMap( $sFile, $iPage, $iDepth + 1 ) : null;
        $aData['sProducts'] = ( $aData['iProducts'] == 1 && isset( $GLOBALS['config']['site_map_products'] ) ) ? $oProduct->listSiteMap( $sFile, $iPage ) : null;

        $aData['iStyle']    = ( $i % 2 ) ? 0: 1;
        $aData['sStyle']    = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData['iDepth']    = $iDepth;

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'SITEMAP_LIST' );
      } // end for

      if( isset( $content ) ){
        $oTpl->setVariables( 'aData', $aData );
        if( isset( $iPageParent ) ){
          return $oTpl->tbHtml( $sFile, 'SITEMAP_HEAD_SUB' ).$content.$oTpl->tbHtml( $sFile, 'SITEMAP_FOOT_SUB' );
        }
        else{
          return $oTpl->tbHtml( $sFile, 'SITEMAP_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'SITEMAP_FOOT' );
        }
      }
    }
  } // end function listSiteMap

  /**
  * Return list of subpages in RSS file
  * @return string
  * @param int    $iPage
  * @param string $sFile
  */
  function listSubpagesRss( $iPage, $sFile ){

    if( isset( $this->aPagesChildrens[$iPage] ) ){
      $sSiteUrl = 'http://'.$_SERVER['HTTP_HOST'].dirname( $_SERVER['REQUEST_URI'] ).'/';
      $iCount   = count( $this->aPagesChildrens[$iPage] );
      $content  = null;
      $oTpl     =& TplParser::getInstance( );

      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aPages[$this->aPagesChildrens[$iPage][$i]];
        if( !isset( $aData['iTime'] ) || empty( $aData['iTime'] ) )
          $aData['iTime'] = time( );

        $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
        $aData['sDate']             = date( 'r', $aData['iTime'] );
        $aData['sSiteUrl']          = $sSiteUrl;

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'RSS_LIST' );
      } // end for

      if( isset( $content ) ){
        return $oTpl->tbHtml( $sFile, 'RSS_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'RSS_FOOT' );
      }
    }
  } // end function listSubpagesRss

  /**
  * Return list of subpages
  * @return string
  * @param int    $iPage
  * @param string $sFile
  */
  function listSubpagesGallery( $iPage, $sFile ){

    if( isset( $this->aPagesChildrens[$iPage] ) ){
      $iCount   = count( $this->aPagesChildrens[$iPage] );
      $iColumns = 3;
      $iWidth   = (int) ( 100 / $iColumns );
      $content  = null;
      $oTpl     =& TplParser::getInstance( );
      $oFile    =& Files::getInstance( );

      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aPages[$this->aPagesChildrens[$iPage][$i]];
        $aData['iWidth']  = $iWidth;
        $aData['iStyle']  = ( $i % 2 ) ? 0: 1;
        $aData['sStyle']  = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;

        if( $i > 0 && $i % $iColumns == 0 ){
          $oTpl->setVariables( 'aData', $aData );
          $content .= $oTpl->tbHtml( $sFile, 'SUBPAGES_GALLERY_BREAK' );
        }

        $oTpl->setVariables( 'aData', $aData );

        if( isset( $oFile->aImagesDefault[1][$aData['iPage']] ) ){
          $aDataImage = $oFile->aFilesImages[1][$oFile->aImagesDefault[1][$aData['iPage']]];
          $oTpl->setVariables( 'aDataImage', $aDataImage );
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'SUBPAGES_GALLERY_IMAGE' );
        }
        else{
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'SUBPAGES_GALLERY_NO_IMAGE' );
        }

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'SUBPAGES_GALLERY_LIST' );
      } // end for

      while( $i % $iColumns > 0 ){
        $content .= $oTpl->tbHtml( $sFile, 'SUBPAGES_GALLERY_BLANK' );
        $i++;
      } // end while

      if( isset( $content ) ){
        return $oTpl->tbHtml( $sFile, 'SUBPAGES_GALLERY_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'SUBPAGES_GALLERY_FOOT' );
      }
    }
  } // end function listSubpagesGallery

  /**
  * Return list of subpages as news
  * @return string
  * @param int    $iPage
  * @param string $sFile
  */
  function listSubpagesNews( $iPage, $sFile ){
    $iType = 3;
    if( isset( $this->aPagesChildrens[$iPage] ) ){
      $oFile  =& Files::getInstance( );
      $iCount = count( $this->aPagesChildrens[$iPage] );
      $content= null;
      $iList  = $GLOBALS['config']['news_list'];
      $oTpl   =& TplParser::getInstance( );
      $iPages = ceil( $iCount / $iList );

      $iPageNumber = isset( $GLOBALS['aActions']['o3'] ) ? $GLOBALS['aActions']['o3'] : 1;
      if( !isset( $iPageNumber ) || !is_numeric( $iPageNumber ) || $iPageNumber < 1 )
        $iPageNumber = 1;
      if( $iPageNumber > $iPages )
        $iPageNumber = $iPages;

      $iEnd   = $iPageNumber * $iList;
      $iStart = $iEnd - $iList;

      if( $iEnd > $iCount )
        $iEnd = $iCount;

      for( $i = 0; $i < $iCount; $i++ ){
        $aSort[$i][0] = $this->aPages[$this->aPagesChildrens[$iPage][$i]]['iTime'];
        $aSort[$i][1] = $this->aPagesChildrens[$iPage][$i];
      } // end for

      rsort( $aSort );

      for( $i = $iStart; $i < $iEnd; $i++ ){
        $aData = $this->aPages[$aSort[$i][1]];
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;

        if( !empty( $aData['sDescriptionShort'] ) ){
          $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
          $oTpl->setVariables( 'aData', $aData );
          $aData['sDescriptionShort'] = $oTpl->tbHtml( $sFile, 'SUBPAGES_DESCRIPTION_'.$iType );
        }

        $oTpl->setVariables( 'aData', $aData );

        if( isset( $oFile->aImagesDefault[1][$aData['iPage']] ) ){
          $aDataImage = $oFile->aFilesImages[1][$oFile->aImagesDefault[1][$aData['iPage']]];
          $oTpl->setVariables( 'aDataImage', $aDataImage );
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'SUBPAGES_IMAGE_'.$iType );
        }
        else{
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'SUBPAGES_NO_IMAGE_'.$iType );
        }

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'SUBPAGES_LIST_'.$iType );
      } // end for

      if( isset( $content ) ){
        if( $iCount > $iList ){
          $aData['sPages'] = countPages( $iCount, $iList, $iPageNumber, throwPageUrl( $this->aPages[$iPage]['sLinkName'].',', true ), null, FRIENDLY_LINKS );
          $aData['sHidePages'] = null;
        }
        else
          $aData['sHidePages'] = ' hide';

        $oTpl->setVariables( 'aData', $aData );
        return $oTpl->tbHtml( $sFile, 'SUBPAGES_HEAD_'.$iType ).$content.$oTpl->tbHtml( $sFile, 'SUBPAGES_FOOT_'.$iType );
      }
    }
  } // end function listSubpagesNews
};

?>