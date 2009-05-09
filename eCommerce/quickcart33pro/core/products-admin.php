<?php
class ProductsAdmin extends Products
{

  function &getInstance( ){
    static $oInstance = null;
    if( !isset( $oInstance ) ){
      $oInstance = new ProductsAdmin( );
    }
    return $oInstance;
  } // end function getInstance

  /**
  * Constructor
  * @return void
  */
  function ProductsAdmin( ){
    $this->generateCache( );
  } // end function Pages

  /**
  * List products
  * @return string
  * @param string $sFile
  * @param int    $iContent
  */
  function listProductsAdmin( $sFile, $iList = null ){
    $oTpl   =& TplParser::getInstance( );
    $oFile  =& Files::getInstance( );
    $content= null;

    if( isset( $GLOBALS['sPhrase'] ) && !empty( $GLOBALS['sPhrase'] ) ){
      $aProducts = $this->generateProductsSearchListArray( $GLOBALS['sPhrase'] );
    }
    else{
      if( isset( $this->aProducts ) ){
        foreach( $this->aProducts as $iProduct => $aData ){
          $aProducts[] = $iProduct;
        } // end foreach
      }
    }

    if( isset( $aProducts ) ){
      $iCount    = count( $aProducts );
      if( !isset( $iList ) ){
        $iList = $GLOBALS['config']['admin_list'];
      }

      $iProducts = ceil( $iCount / $iList );
      $iPageNumber = isset( $_GET['iPage'] ) ? $_GET['iPage'] : 1;
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
        $aData['sPrice'] = displayPrice( $aData['fPrice'] );
        $aData['sPages'] = ereg_replace( '\|', '&nbsp;|&nbsp;', strip_tags( $this->throwProductsPagesTree( $aData['iProduct'] ) ) );
        $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
        $aData['sStatusBox'] = ( $aData['iStatus'] == 1 ) ? ' checked="checked"' : null;
        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'LIST' );
      } // end for

      if( isset( $content ) ){
        $aData['sPages'] = countPagesClassic( $iCount, $iList, $iPageNumber, changeUri( $_SERVER['REQUEST_URI'] ) );
        $oTpl->setVariables( 'aData', $aData );
        return $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );
      }
    }
  } // end function listProductsAdmin

  /**
  * Save products position, status and price
  * @return void
  * @param array  $aForm
  */
  function saveProducts( $aForm ){
    if( isset( $aForm['aPositions'] ) && is_array( $aForm['aPositions'] ) ){
      foreach( $this->aProducts as $iProduct => $aData ){
        if( isset( $aForm['aPositions'][$iProduct] ) ){
          $aForm['aPositions'][$iProduct] = trim( $aForm['aPositions'][$iProduct] );
          $iStatus = isset( $aForm['aStatus'][$iProduct] ) ? 1 : 0;
          if( is_numeric( ereg_replace( ',', '.', $aForm['aPrices'][$iProduct] ) ) )
            $aForm['aPrices'][$iProduct] = normalizePrice( trim( $aForm['aPrices'][$iProduct] ) );
          else
            $aForm['aPrices'][$iProduct] = trim( $aForm['aPrices'][$iProduct] );

          if( is_numeric( $aForm['aPositions'][$iProduct] ) && $aForm['aPositions'][$iProduct] != $aData['iPosition'] ){
            $aChange[$iProduct]['iPosition'] = $aForm['aPositions'][$iProduct];
          }
          if( $iStatus != $aData['iStatus'] ){
            $aChange[$iProduct]['iStatus'] = $iStatus;
          }
          if( $aForm['aPrices'][$iProduct] != $aData['fPrice'] ){
            $aChange[$iProduct]['fPrice'] = $aForm['aPrices'][$iProduct];
          }
        }
      } // end foreach

      if( isset( $aChange ) ){
        $this->saveProductsData( $aChange );
        $this->generateCache( );
      }
    }
  } // end function saveProducts

  /**
  * Save products status, position etc.
  * @return void
  * @param array  $aChange
  */
  function saveProductsData( $aChange ){
    $oFF    =& FlatFiles::getInstance( );
    $iCount = count( $aChange );
    $i      = 1;

    foreach( $aChange as $iProduct => $aData ){
      $aSave = array_merge( $this->aProducts[$iProduct], $aData );

      if( $i == $iCount )
        $oFF->save( DB_PRODUCTS, $aSave, 'iProduct', 'sort' );
      else
        $oFF->save( DB_PRODUCTS, $aSave, 'iProduct' );

      $i++;
    } // end foreach
  } // end function saveProductsData

  /**
  * Save product data
  * @return int
  * @param array  $aForm
  */
  function saveProduct( $aForm ){
    $oFF    =& FlatFiles::getInstance( );
    $oFile  =& FilesAdmin::getInstance( );

    if( isset( $aForm['iProduct'] ) && is_numeric( $aForm['iProduct'] ) && isset( $this->aProducts[$aForm['iProduct']] ) ){
      $sParam = 'iProduct';
    }
    else{
      $sParam = null;
      $aForm['iProduct'] = $oFF->throwLastId( DB_PRODUCTS, 'iProduct' ) + 1;
    }

    if( !empty( $aForm['sTemplate'] ) && $aForm['sTemplate'] == $GLOBALS['config']['default_products_template'] )
      $aForm['sTemplate'] = '';

    if( !empty( $aForm['sTheme'] ) && $aForm['sTheme'] == $GLOBALS['config']['default_theme'] )
      $aForm['sTheme'] = '';

    if( !isset( $aForm['iPosition'] ) || !is_numeric( $aForm['iPosition'] ) || $aForm['iPosition'] < -99 || $aForm['iPosition'] > 999 )
      $aForm['iPosition'] = 0;

    if( !isset( $aForm['iStatus'] ) )
      $aForm['iStatus'] = 0;

    if( !isset( $aForm['iComments'] ) )
      $aForm['iComments'] = 0;

    if( is_numeric( ereg_replace( ',', '.', $aForm['fPrice'] ) ) )
      $aForm['fPrice'] = normalizePrice( $aForm['fPrice'] );

    $aForm = changeMassTxt( $aForm, '', Array( 'sDescriptionShort', 'Nds' ), Array( 'sDescriptionFull', 'Nds' ), Array( 'sMetaDescription', 'Nds' ) );

    $oFF->save( DB_PRODUCTS, $aForm, $sParam, 'sort' );
    $oFF->save( DB_PRODUCTS_EXT, $aForm, $sParam );

    if( isset( $aForm['aPages'] ) && is_array( $aForm['aPages'] ) ){
      $oFF->deleteInFile( DB_PRODUCTS_PAGES, $aForm['iProduct'], 'iProduct' );
      foreach( $aForm['aPages'] as $iPage ){
        $oFF->save( DB_PRODUCTS_PAGES, Array( 'iProduct' => $aForm['iProduct'], 'iPage' => $iPage ), null );
      }
    }

    if( isset( $aForm['aFilesDelete'] ) )
      $oFile->deleteSelectedFiles( $aForm['aFilesDelete'], 2 );
    if( isset( $aForm['aFilesDescription'] ) )
      $oFile->saveFiles( $aForm, $aForm['iProduct'], 2 );
    if( isset( $_FILES['aNewFiles'] ) )
      $oFile->addFilesUploaded( $aForm, $aForm['iProduct'], 2, 'iProduct' );
    if( isset( $aForm['aDirFiles'] ) )
      $oFile->addFilesFromServer( $aForm, $aForm['iProduct'], 2, 'iProduct' );

    $oFF->deleteInFile( DB_PRODUCTS_RELATED, $aForm['iProduct'], 'iProduct' );
    if( isset( $aForm['aProductsRelated'] ) ){
      foreach( $aForm['aProductsRelated'] as $aForm['iRelated'] ){
        if( !empty( $aForm['iRelated'] ) ){
          $oFF->save( DB_PRODUCTS_RELATED, $aForm );
        }
      }
    }
    if( isset( $aForm['aFeatures'] ) ){
      $oFF->deleteInFile( DB_FEATURES_PRODUCTS, $aForm['iProduct'], 'iProduct' );
      foreach( $aForm['aFeatures'] as $aForm['iFeature'] => $aForm['sValue'] ){
        if( !empty( $aForm['sValue'] ) ){
          $aForm['sValue'] = changeTxt( $aForm['sValue'] );
          $oFF->save( DB_FEATURES_PRODUCTS, $aForm );
        }
      }
    }

    if( ( isset( $aForm['iRecommended'] ) && !isset( $GLOBALS['aProductsRecommended'][$aForm['iProduct']] ) ) || ( !isset( $aForm['iRecommended'] ) && isset( $GLOBALS['aProductsRecommended'][$aForm['iProduct']] ) ) ){
      $this->saveProductConfig( $aForm['iProduct'], isset( $aForm['iRecommended'] ) ? 1 : null, 'aProductsRecommended' );
    }

    $this->generateCache( );
    return $aForm['iProduct'];
  } // end function saveProduct

  /**
  * Delete product
  * @return void
  * @param int  $iProduct
  */
  function deleteProduct( $iProduct ){
    $oFile =& FilesAdmin::getInstance( );
    $oFF   =& FlatFiles::getInstance( );

    $oFF->deleteInFile( DB_PRODUCTS, $iProduct, 'iProduct' );
    $oFF->deleteInFile( DB_PRODUCTS_EXT, $iProduct, 'iProduct' );
    $oFF->deleteInFile( DB_PRODUCTS_PAGES, $iProduct, 'iProduct' );
    $oFile->deleteFiles( Array( $iProduct => $iProduct ), 2, 'iProduct' );
    $oFF->deleteInFile( DB_PRODUCTS_RELATED, $iProduct, 'iProduct' );
    $oFF->deleteInFile( DB_PRODUCTS_RELATED, $iProduct, 'iRelated' );
    $oFF->deleteInFile( DB_PRODUCTS_STATS, $iProduct, 'iProduct' );
    $oFF->deleteInFile( DB_FEATURES_PRODUCTS, $iProduct, 'iProduct' );
    $oFF->deleteInFile( DB_PRODUCTS_COMMENTS, $iProduct, 'iLink' );

  } // end function deleteProduct

  /**
  * Save product id defined variable in configuration
  * @return void
  * @param int    $iProduct
  * @param int    $iOk
  * @param string $sVar
  */
  function saveProductConfig( $iProduct, $iOk = null, $sVar = 'aProductsPromoted' ){
    $aFile  = file( DB_CONFIG_LANG );
    $iCount = count( $aFile );
    $rFile  = fopen( DB_CONFIG_LANG, 'w' );

    foreach( $GLOBALS[$sVar] as $iKey => $iValue ){
      if( $iProduct == $iKey && !isset( $iOk ) ){
        unset( $GLOBALS[$sVar][$iKey] );
      }
    }

    if( isset( $iOk ) ){
      $GLOBALS[$sVar][$iProduct] = $iProduct;
    }

    for( $i = 0; $i < $iCount; $i++ ){
      if( ereg( $sVar, $aFile[$i] ) && ereg( '=', $aFile[$i] ) ){
        $aFile[$i] = '$'.$sVar.' = '.ereg_replace( "\n| |'", '', var_export( $GLOBALS[$sVar], true ) ).';'."\n";
      }

      fwrite( $rFile, $aFile[$i] );

    } // end for
    fclose( $rFile );
  } // end function saveProductConfig

};
?>