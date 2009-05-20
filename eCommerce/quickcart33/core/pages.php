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
        if( $iDepthLimit > $iDepth && ( $iPageCurrent == $iPage || isset( $this->aPageParents[$iPage] ) || DISPLAY_EXPANDED_MENU === true ) ){
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
            $aData['sPages'] = countPages( count( $aExp ), 1, $iPageContent, $this->aPages[$iPage]['sLinkName'].',,', null, null, null, MAX_PAGES );
          }
        }
        return array_merge( $this->aPages[$iPage], $aData );
      }
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
    $iStatus    = throwStatus( );

    $this->aPages             = null;
    $this->aPagesChildrens    = null;
    $this->aPagesParents      = null;
    $this->aPagesParentsTypes = null;

	//categories - id mod 4 == 0
	$query = 'SELECT c.id, c.f_category_name, c.f_parent_category, 
					d.f_page_title, d.f_meta_description, d.f_keywords, 
					d.f_display_name, d.f_short_description, d.f_long_description,
					c.f_position
			FROM premium_warehouse_items_categories_data_1 c LEFT JOIN premium_ecommerce_cat_descriptions_data_1 d ON (c.id=d.f_category AND d.f_language="'.LANGUAGE.'") WHERE c.active=1';
	$x = DB::GetAll($query.' ORDER BY c.f_parent_category,c.f_position');
	foreach($x as $r) {
		if(!$r['f_parent_category']) 
			$r['f_parent_category'] = 0;
		else
			$r['f_parent_category'] *= 4;
		$id = $r['id']*4;
        $this->aPages[$id] = array('iPage' => $id, 'iPageParent' => $r['f_parent_category'], 'sName' => $r['f_display_name']?$r['f_display_name']:$r['f_category_name'], 'sNameTitle' => $r['f_page_title'], 'sDescriptionShort' => $r['f_short_description'], 'iStatus' => 1, 'iPosition' => $r['f_position'], 'iType' => 3, 'iSubpagesShow' => 1, 'iProducts' => 1, 'sDescriptionFull'=>$r['f_long_description'], 'sMetaDescription' => $r['f_meta_description'], 'sMetaKeywords' =>$r['f_keywords'] );
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
			ON (c.id=i.f_vendor AND d.f_item_name=i.id)
			WHERE c.active=1';
			
	$x = DB::GetAll($query);
	foreach($x as $r) {
		$id = $r['id']*4+1;
        $this->aPages[$id] = array('iPage' => $id, 'iPageParent' => 0, 'sName' => $r['f_company_name'], 'sNameTitle' => $r['f_company_name'], 'sDescriptionShort' => '', 'iStatus' => 1, 'iPosition' => 0, 'iType' => 4, 'iSubpagesShow' => 1, 'iProducts' => 1, 'sDescriptionFull'=>'', 'sMetaDescription' => '', 'sMetaKeywords' =>'' );
        $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
        $this->aPagesParentsTypes[4][] = $id;
	}
	
	//pages - id mod 4 == 2
	$i = 0;
	$query = 'SELECT p.id, p.f_page_name, p.f_parent_page, p.f_type,
					p.f_position, d.f_name, d.f_short_description, d.f_long_description, 
					d.f_page_title, d.f_meta_description, d.f_keywords
			FROM premium_ecommerce_pages_data_1 p INNER JOIN premium_ecommerce_pages_data_data_1 d ON (p.id=d.f_page AND d.f_language="'.LANGUAGE.'") WHERE p.active=1 AND p.f_publish=1 AND p.f_parent_page';
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
        $this->aPages[$id] = array('iPage' => $id, 'iPageParent' => $r['f_parent_page'], 'sName' => $r['f_name'], 'sNameTitle' => $r['f_page_title'], 'sDescriptionShort' => $r['f_short_description'], 'iStatus' => 1, 'iPosition' => $r['f_position'], 'iType' => $r['f_type'], 'iSubpagesShow' => 1, 'iProducts' => 0, 'sDescriptionFull'=>$r['f_long_description'], 'sMetaDescription' => $r['f_meta_description'], 'sMetaKeywords' =>$r['f_keywords'] );
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
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'basket', 'sNameTitle' => '', 'sDescriptionShort' => '', 'iStatus' => 1, 'iPosition' => 0,
					 'iType' => 1, 'iSubpagesShow' => 1, 'iProducts' => 0);
    $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
    $this->aPagesParentsTypes[1][] = $id;
	//order
	$id = 7;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'order form', 'sNameTitle' => '', 'sDescriptionShort' => '', 'iStatus' => 1, 'iPosition' => 0,
					 'iType' => 5, 'iSubpagesShow' => 1, 'iProducts' => 0, 'sTheme'=>'order.php');
    $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
    $this->aPagesParentsTypes[5][] = $id;
	//start page
	$id = 11;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'Start', 'sNameTitle' => '', 'sDescriptionShort' => unserialize(DB::GetOne('SELECT value FROM variables WHERE name=%s',array('ecommerce_start_page'))), 'iStatus' => 1, 'iPosition' => 0,
					 'iType' => 2, 'iSubpagesShow' => 1, 'iProducts' => 0);
    $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
    $this->aPagesParentsTypes[2][] = $id;
	//rules and policies
	$id = 15;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'Rules and Policies', 'sNameTitle' => '', 'sDescriptionShort' => unserialize(DB::GetOne('SELECT value FROM variables WHERE name=%s',array('ecommerce_rules'))), 'iStatus' => 1, 'iPosition' => 1,
					 'iType' => 2, 'iSubpagesShow' => 1, 'iProducts' => 0);
    $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
    $this->aPagesParentsTypes[2][] = $id;
	//search
	$id = 19;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'Search results', 'sNameTitle' => '', 'sDescriptionShort' => '', 'iStatus' => 1, 'iPosition' => 0,
					 'iType' => 5, 'iSubpagesShow' => 1, 'iProducts' => 0);
    $this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
    $this->aPagesParentsTypes[5][] = $id;

	//uncategorized products
	$id = 23;
	$this->aPages[$id] = array ( 'iPage' => $id, 'iPageParent' => 0, 'sName' => 'Uncategorized', 'sNameTitle' => 'Uncategorized', 'sDescriptionShort' => '', 'iStatus' => 1, 'iPosition' => 1000, 'iType' => 3, 'iSubpagesShow' => 1, 'iProducts' => 1, 'sDescriptionFull'=>'', 'sMetaDescription' => '', 'sMetaKeywords' =>'' );
	$this->aPages[$id]['sLinkName'] = '?'.change2Url( $this->aPages[$id]['sName'] ).','.$id;
	$this->aPagesParentsTypes[3][] = $id;
	//} epesi

/*    if( !is_file( DB_PAGES ) )
      return null;

    $aFile      = file( DB_PAGES );
    $iCount     = count( $aFile );
    $sFunction  = LANGUAGE.'_pages';
    $iStatus    = throwStatus( );
    $sLanguageUrl  = ( LANGUAGE_IN_URL == true ) ? LANGUAGE.LANGUAGE_SEPARATOR : null;
    
    $this->aPages             = null;
    $this->aPagesChildrens    = null;
    $this->aPagesParents      = null;
    $this->aPagesParentsTypes = null;

    for( $i = 1; $i < $iCount; $i++ ){
      $aExp = explode( '$', $aFile[$i] );
      if( isset( $aExp[5] ) && $aExp[5] >= $iStatus ){
        if( !is_numeric( $aExp[1] ) )
          $aExp[1] = 0;
        $this->aPages[$aExp[0]] = $sFunction( $aExp );
        $this->aPages[$aExp[0]]['sLinkName'] = '?'.$sLanguageUrl.change2Url( $this->aPages[$aExp[0]]['sName'] ).','.$aExp[0];
        if( $aExp[1] > 0 ){
          $this->aPagesChildrens[$aExp[1]][] = $aExp[0];
          $this->aPagesParents[$aExp[0]] = $aExp[1];
        }
        else{
          if( !empty( $aExp[7] ) )
            $this->aPagesParentsTypes[$aExp[7]][] = $aExp[0];
        }
      }
    } // end for
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
};

?>