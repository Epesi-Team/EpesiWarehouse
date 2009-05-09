<?php
class PagesAdmin extends Pages{

  function &getInstance( ){
    static $oInstance = null;
    if( !isset( $oInstance ) ){
      $oInstance = new PagesAdmin( );
    }
    return $oInstance;
  } // end function getInstance

  /**
  * Constructor
  * @return void
  */
  function PagesAdmin( ){
    $this->generateCache( );
  } // end function PagesAdmin

  /**
  * Return pages list
  * @return string
  * @param string $sFile
  */
  function listPagesAdmin( $sFile ){
    if( isset( $this->aPagesParentsTypes ) ){
      $oTpl =& TplParser::getInstance( );
      $content = null;

      foreach( $this->aPagesParentsTypes as $iType => $aPages ){
        $iCount = count( $aPages );

        $aData['sType'] = $GLOBALS['aMenuTypes'][$iType];
        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'TYPE' );

        for( $i = 0; $i < $iCount; $i++ ){
          $aData = $this->aPages[$aPages[$i]];
          $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
          $aData['iDepth'] = 0;

          $aData['sStatusBox'] = ( $aData['iStatus'] == 1 ) ? ' checked="checked"' : null;

          $oTpl->setVariables( 'aData', $aData );
          $content .= $oTpl->tbHtml( $sFile, 'LIST' );
          if( isset( $this->aPagesChildrens[$aData['iPage']] ) ){
            $content .= ( $aData['iSubpagesShow'] == 3 ) ? $this->listSubpagesNewsAdmin( $sFile, $aData['iPage'], $aData['iDepth'] + 1 ) : $this->listSubpagesAdmin( $sFile, $aData['iPage'], $aData['iDepth'] + 1 );
          }
        } // end for
      }
      if( isset( $content ) )
        return $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );
    }
  } // end function listPages

  /**
  * Return subpages to admin
  * @return string
  * @param string $sFile
  * @param int    $iPageParent
  * @param int    $iDepth
  */
  function listSubPagesAdmin( $sFile, $iPageParent, $iDepth ){
    $oTpl =& TplParser::getInstance( );
    $content = null;
    $iCount  = count( $this->aPagesChildrens[$iPageParent] );
    for( $i = 0; $i < $iCount; $i++ ){
      $aData = $this->aPages[$this->aPagesChildrens[$iPageParent][$i]];
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['iDepth'] = $iDepth;

      $aData['sStatusBox'] = ( $aData['iStatus'] == 1 ) ? ' checked="checked"' : null;

      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'LIST' );
      if( isset( $this->aPagesChildrens[$aData['iPage']] ) ){
        $content .= ( $aData['iSubpagesShow'] == 3 ) ? $this->listSubpagesNewsAdmin( $sFile, $aData['iPage'], $aData['iDepth'] + 1 ) : $this->listSubpagesAdmin( $sFile, $aData['iPage'], $aData['iDepth'] + 1 );
      }
    } // end for
    return $content;
  } // end function listSubPagesAdmin

  /**
  * Return pages select for admin panel
  * @return string
  * @param int  $iPageSelected
  */
  function throwPagesSelectAdmin( $iPageSelected ){
    if( isset( $this->aPagesParentsTypes ) ){
      $content = null;
      foreach( $this->aPagesParentsTypes as $iType => $aPages ){
        $iCount = count( $aPages );
        $sType  = $GLOBALS['aMenuTypes'][$iType];
        $content .= '<option value="0" disabled="disabled" style="color:#999;">'.$sType.'</option>';

        for( $i = 0; $i < $iCount; $i++ ){
          $sSelected = ( $iPageSelected == $this->aPages[$aPages[$i]]['iPage'] ) ? ' selected="selected"' : null;
          $content .= '<option value="'.$this->aPages[$aPages[$i]]['iPage'].'"'.$sSelected.'>'.$this->aPages[$aPages[$i]]['sName'].'</option>';
          if( isset( $this->aPagesChildrens[$aPages[$i]] ) ){
            $content .= $this->throwSubPagesSelectAdmin( $iPageSelected, $aPages[$i], 1 );
          }
        } // end for
      }
      return $content;
    }
  } // end function throwPagesSelectAdmin

  /**
  * Return pages select for admin panel
  * @return string
  * @param int    $iPageSelected
  * @param int    $iPageParent
  * @param int    $iDepth
  */
  function throwSubPagesSelectAdmin( $iPageSelected, $iPageParent, $iDepth = 1 ){
    $iCount     = count( $this->aPagesChildrens[$iPageParent] );
    $sSeparator = ( $iDepth > 0 ) ? str_repeat( '&nbsp;&nbsp;', $iDepth ) : null;
    $content    = null;

    for( $i = 0; $i < $iCount; $i++ ){
      $iPage      = $this->aPagesChildrens[$iPageParent][$i];
      $sSelected  = ( $iPageSelected == $iPage ) ? ' selected="selected"' : null;
      $content .= '<option value="'.$this->aPages[$iPage]['iPage'].'"'.$sSelected.'>'.$sSeparator.$this->aPages[$iPage]['sName'].'</option>';
      if( isset( $this->aPagesChildrens[$iPage] ) ){
        $content .= $this->throwSubPagesSelectAdmin( $iPageSelected, $iPage, $iDepth + 1 );
      }
    } // end for
    return $content;
  } // end function throwSubPagesSelectAdmin

  /**
  * Delete page and subpages
  * @return void
  * @param int  $iPage
  */
  function deletePage( $iPage ){
    $oFile =& FilesAdmin::getInstance( );
    $oFF   =& FlatFiles::getInstance( );

    $this->mData[$iPage] = true;
    if( isset( $this->aPagesChildrens[$iPage] ) ){
      $this->throwSubpagesIdAdmin( $iPage );
    }

    $oFF->deleteInFile( DB_PAGES, $this->mData, 'iPage' );
    $oFF->deleteInFile( DB_PAGES_EXT, $this->mData, 'iPage' );
    $oFile->deleteFiles( $this->mData, 1, 'iPage' );
    $oFF->deleteInFile( DB_PAGES_STATS, $this->mData, 'iPage' );
    $oFF->deleteInFile( DB_PAGES_COMMENTS, $this->mData, 'iLink' );

  } // end function deletePage

  /**
  * Return all subpages id
  * @return void
  * @param int  $iPage
  */
  function throwSubpagesIdAdmin( $iPage ){
    $iCount = count( $this->aPagesChildrens[$iPage] );
    for( $i = 0; $i < $iCount; $i++ ){
      $this->mData[$this->aPagesChildrens[$iPage][$i]] = true;
      if( isset( $this->aPagesChildrens[$this->aPagesChildrens[$iPage][$i]] ) ){
        $this->throwSubpagesIdAdmin( $this->aPagesChildrens[$iPage][$i] );
      }
    } // end for
  } // end function throwSubpagesIdAdmin

  /**
  * Save page data
  * @return int
  * @param array  $aForm
  */
  function savePage( $aForm ){
    $oFF    =& FlatFiles::getInstance( );
    $oFile  =& FilesAdmin::getInstance( );

    if( isset( $aForm['iPage'] ) && is_numeric( $aForm['iPage'] ) && isset( $this->aPages[$aForm['iPage']] ) ){
      $sParam = 'iPage';
    }
    else{
      $sParam = null;
      $aForm['iPage'] = $oFF->throwLastId( DB_PAGES, 'iPage' ) + 1;
    }

    if( empty( $aForm['iPageParent'] ) || ( !empty( $aForm['iPageParent'] ) && $aForm['iPageParent'] == $aForm['iPage'] ) )
      $aForm['iPageParent'] = 0;
    else{
      if( $aForm['iPageParent'] > 0 && isset( $this->aPages[$aForm['iPageParent']] ) ){
        $aForm['iType'] = $this->aPages[$aForm['iPageParent']]['iType'];
      }
    }

    if( !empty( $aForm['sTemplate'] ) && $aForm['sTemplate'] == $GLOBALS['config']['default_pages_template'] )
      $aForm['sTemplate'] = '';

    if( !empty( $aForm['sTheme'] ) && $aForm['sTheme'] == $GLOBALS['config']['default_theme'] )
      $aForm['sTheme'] = '';

    if( !isset( $aForm['iPosition'] ) || !is_numeric( $aForm['iPosition'] ) || $aForm['iPosition'] < -99 || $aForm['iPosition'] > 999 )
      $aForm['iPosition'] = 0;

    if( !isset( $aForm['iStatus'] ) )
      $aForm['iStatus'] = 0;

    if( !isset( $aForm['iComments'] ) )
      $aForm['iComments'] = 0;

    if( !isset( $aForm['iRss'] ) )
      $aForm['iRss'] = 0;

    if( !isset( $aForm['iProducts'] ) )
      $aForm['iProducts'] = '';

    $aForm = changeMassTxt( $aForm, '', Array( 'sDescriptionShort', 'Nds' ), Array( 'sDescriptionFull', 'Nds' ), Array( 'sMetaDescription', 'Nds' ) );
    $aForm['iTime'] = (int) abs( ( !empty( $aForm['sDate'] ) ) ? dateToTime( $aForm['sDate'].':00' ) : time( ) );

    if( isset( $aForm['iBannerDel'] ) ){
      unlink( DIR_FILES.$aForm['sBanner'] );
      $aForm['sBanner'] = null;
    }

    if( !empty( $_FILES['sBannerFile']['name'] ) && $oFF->checkCorrectFile( $_FILES['sBannerFile']['name'], 'gif|jpg|png|jpeg|swf|bmp|tiff' ) == true ){
      $aForm['sBanner'] = $oFF->uploadFile( $_FILES['sBannerFile'], DIR_FILES );
    }

    $oFF->save( DB_PAGES, $aForm, $sParam, 'sort' );
    $oFF->save( DB_PAGES_EXT, $aForm, $sParam );

    if( isset( $aForm['aFilesDelete'] ) )
      $oFile->deleteSelectedFiles( $aForm['aFilesDelete'], 1 );
    if( isset( $aForm['aFilesDescription'] ) )
      $oFile->saveFiles( $aForm, $aForm['iPage'], 1 );
    if( isset( $_FILES['aNewFiles'] ) )
      $oFile->addFilesUploaded( $aForm, $aForm['iPage'], 1, 'iPage' );
    if( isset( $aForm['aDirFiles'] ) )
      $oFile->addFilesFromServer( $aForm, $aForm['iPage'], 1, 'iPage' );

    if( isset( $sParam ) && $aForm['iStatus'] == 0 && $aForm['iStatus'] != $this->aPages[$aForm['iPage']]['iStatus'] && isset( $this->aPagesChildrens[$aForm['iPage']] ) ){
      $this->mData = null;
      $this->throwSubpagesIdAdmin( $aForm['iPage'] );
      foreach( $this->mData as $iPage => $bValue ){
        $aChange[$iPage]['iStatus'] = 0;
      } // end foreach
      if( isset( $aChange ) ){
        $this->savePagesData( $aChange );
      }
    }

    $this->generateCache( );
    return $aForm['iPage'];
  } // end function savePage

  /**
  * Save pages position and status
  * @return void
  * @param array  $aForm
  */
  function savePages( $aForm ){
    if( isset( $aForm['aPositions'] ) && is_array( $aForm['aPositions'] ) ){
      foreach( $this->aPages as $iPage => $aData ){
        if( isset( $aForm['aPositions'][$iPage] ) ){
          $aForm['aPositions'][$iPage] = trim( $aForm['aPositions'][$iPage] );
          if( is_numeric( $aForm['aPositions'][$iPage] ) && $aForm['aPositions'][$iPage] != $aData['iPosition'] ){
            $aChange[$iPage]['iPosition'] = $aForm['aPositions'][$iPage];
          }

          $iStatus = isset( $aForm['aStatus'][$iPage] ) ? 1 : 0;

          if( !isset( $aChange[$iPage]['iStatus'] ) && $iStatus != $this->aPages[$iPage]['iStatus'] ){
            $aChange[$iPage]['iStatus'] = $iStatus;
            if( $iStatus == 0 && isset( $this->aPagesChildrens[$iPage] ) ){
              $this->mData = null;
              $this->throwSubpagesIdAdmin( $iPage );
              foreach( $this->mData as $iPage => $bValue ){
                $aChange[$iPage]['iStatus'] = 0;
              } // end foreach
            }
          }
        }
      } // end foreach

      if( isset( $aChange ) ){
        $this->savePagesData( $aChange );
        $this->generateCache( );
      }
    }
  } // end function savePages

  /**
  * Save pages status, position etc.
  * @return void
  * @param array  $aChange
  */
  function savePagesData( $aChange ){
    $oFF    =& FlatFiles::getInstance( );
    $iCount = count( $aChange );
    $i      = 1;

    foreach( $aChange as $iPage => $aData ){
      $aSave = array_merge( $this->aPages[$iPage], $aData );

      if( $i == $iCount )
        $oFF->save( DB_PAGES, $aSave, 'iPage', 'sort' );
      else
        $oFF->save( DB_PAGES, $aSave, 'iPage' );

      $i++;
    } // end foreach

  } // end function savePagesData

  /**
  * Return subpages as news to admin
  * @return string
  * @param string $sFile
  * @param int    $iPageParent
  * @param int    $iDepth
  */
  function listSubPagesNewsAdmin( $sFile, $iPageParent, $iDepth ){
    $oTpl =& TplParser::getInstance( );
    $content = null;
    $iCount  = count( $this->aPagesChildrens[$iPageParent] );
    for( $i = 0; $i < $iCount; $i++ ){
      $aSort[$i][0] = $this->aPages[$this->aPagesChildrens[$iPageParent][$i]]['iTime'];
      $aSort[$i][1] = $this->aPagesChildrens[$iPageParent][$i];
    } // end for

    rsort( $aSort );

    for( $i = 0; $i < $iCount; $i++ ){
      $aData = $this->aPages[$aSort[$i][1]];
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['iDepth'] = $iDepth;

      $aData['sStatusBox'] = ( $aData['iStatus'] == 1 ) ? ' checked="checked"' : null;

      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'LIST' );
      if( isset( $this->aPagesChildrens[$aData['iPage']] ) ){
        $content .= ( $aData['iSubpagesShow'] == 3 ) ? $this->listSubpagesNewsAdmin( $sFile, $aData['iPage'], $aData['iDepth'] + 1 ) : $this->listSubpagesAdmin( $sFile, $aData['iPage'], $aData['iDepth'] + 1 );
      }
    } // end for
    return $content;
  } // end function listSubPagesNewsAdmin

  /**
  * Return products pages select for admin panel
  * @return string
  * @param array  $aPagesSelected
  */
  function throwProductsPagesSelectAdmin( $aPagesSelected ){
    if( isset( $this->aPagesParentsTypes ) ){
      $content = null;
      foreach( $this->aPagesParentsTypes as $iType => $aPages ){
        $iCount = count( $aPages );
        $sType  = $GLOBALS['aMenuTypes'][$iType];

        for( $i = 0; $i < $iCount; $i++ ){
          $sSelected = ( isset( $aPagesSelected ) && isset( $aPagesSelected[$this->aPages[$aPages[$i]]['iPage']] ) ) ? ' selected="selected"' : null;
          if( $this->aPages[$aPages[$i]]['iProducts'] == 1 ){
            $sDisabled = null;
            $iValue = $this->aPages[$aPages[$i]]['iPage'];
          }
          else{
            $sDisabled = ' disabled="disabled" style="color:#999;"';
            $iValue = null;
          }
          $content .= '<option value="'.$iValue.'"'.$sSelected.$sDisabled.'>'.$this->aPages[$aPages[$i]]['sName'].'</option>';
          if( isset( $this->aPagesChildrens[$aPages[$i]] ) ){
            $content .= $this->throwProductsSubPagesSelectAdmin( $aPagesSelected, $aPages[$i], 1 );
          }
        } // end for
      }
      return $content;
    }
  } // end function throwProductsPagesSelectAdmin

  /**
  * Return products pages select for admin panel
  * @return string
  * @param array  $aPagesSelected
  * @param int    $iPageParent
  * @param int    $iDepth
  */
  function throwProductsSubPagesSelectAdmin( $aPagesSelected, $iPageParent, $iDepth = 1 ){
    $iCount     = count( $this->aPagesChildrens[$iPageParent] );
    $sSeparator = ( $iDepth > 0 ) ? str_repeat( '&nbsp;&nbsp;', $iDepth ) : null;
    $content    = null;

    for( $i = 0; $i < $iCount; $i++ ){
      $iPage      = $this->aPagesChildrens[$iPageParent][$i];
      $sSelected = ( isset( $aPagesSelected ) && isset( $aPagesSelected[$this->aPages[$iPage]['iPage']] ) ) ? ' selected="selected"' : null;
      if( $this->aPages[$iPage]['iProducts'] == 1 ){
        $sDisabled = null;
        $iValue = $this->aPages[$iPage]['iPage'];
      }
      else{
        $sDisabled = ' disabled="disabled" style="color:#999;"';
        $iValue = null;
      }
      $content .= '<option value="'.$iValue.'"'.$sSelected.$sDisabled.'>'.$sSeparator.$this->aPages[$iPage]['sName'].'</option>';
      if( isset( $this->aPagesChildrens[$iPage] ) ){
        $content .= $this->throwProductsSubPagesSelectAdmin( $aPagesSelected, $iPage, $iDepth + 1 );
      }
    } // end for
    return $content;
  } // end function throwProductsSubPagesSelectAdmin
};
?>