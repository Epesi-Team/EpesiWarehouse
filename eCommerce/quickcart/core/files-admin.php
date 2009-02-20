<?php
class FilesAdmin extends Files
{

  var $aDirs;

  /**
  * Constructor
  * @return void
  */
  function FilesAdmin( ){
    $this->generateCache( );
    $this->generateThumbDirs( );
  } // end function FilesAdmin

  function &getInstance( ){  
    static $oInstance = null;
    if( !isset( $oInstance ) ){
      $oInstance = new FilesAdmin( );  
    }  
    return $oInstance;  
  } // end function getInstance

  /**
  * List all files in selected link
  * @return string
  * @param string $sFile
  * @param int    $iLink
  * @param int    $iLinkType
  */
  function listAllFilesAdmin( $sFile, $iLink, $iLinkType ){
    if( isset( $this->aFilesImages[$iLinkType] ) ){
      $aSizes     = $GLOBALS['config']['pages_images_sizes'];
      $aTypes     = $GLOBALS['aPhotoTypes'];
      $oFF        =& FlatFiles::getInstance( );
      $sFileName  = $this->throwDbNames( $iLinkType );
      $oTpl       =& TplParser::getInstance( );
      $content    = null;
      $aFiles     = $this->throwAllFilesByLink( $iLink, $iLinkType );

      if( isset( $aFiles ) ){
        $iCount = count( $aFiles );
        for( $i = 0; $i < $iCount; $i++ ){
          $aData = $this->aFilesImages[$iLinkType][$aFiles[$i]];
          $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
          $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;

          $aData['sSizes1Select']     = throwSelectFromArray( $aSizes, $aData['iSize1'] );
          $aData['sSizes2Select']     = throwSelectFromArray( $aSizes, $aData['iSize2'] );
          $aData['sPhotoTypesSelect'] = throwSelectFromArray( $aTypes, $aData['iType'] );

          if( !empty( $aData['iPhoto'] ) && $aData['iPhoto'] == 1 )
            $sBlock = 'IMAGES_LIST';
          else
            $sBlock = 'FILES_LIST';

          $oTpl->setVariables( 'aData', $aData );
          $content .= $oTpl->tbHtml( $sFile, $sBlock );
        } // end for
      }

      if( isset( $content ) ){
        if( $iCount < 4 )
          $aData['iHeight'] = '250';
        elseif( $iCount < 7 )
          $aData['iHeight'] = '300';
        elseif( $iCount < 11 )
          $aData['iHeight'] = '350';
        else
          $aData['iHeight'] = '400';

        $oTpl->setVariables( 'aData', $aData );
        return $oTpl->tbHtml( $sFile, 'FILES_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FILES_FOOT' );
      }
    }
  } // end function listAllFilesAdmin

  /**
  * Return array with all files
  * @return array
  * @param int    $iLink
  * @param in     $iLinkType
  */
  function throwAllFilesByLink( $iLink, $iLinkType ){
    if( isset( $this->aFiles[$iLinkType][$iLink] ) && isset( $this->aImages[$iLinkType][$iLink] ) ){
      return array_merge( $this->aImages[$iLinkType][$iLink], $this->aFiles[$iLinkType][$iLink] );
    }
    else{
      if( isset( $this->aImages[$iLinkType][$iLink] ) )
        return $this->aImages[$iLinkType][$iLink];
      elseif( isset( $this->aFiles[$iLinkType][$iLink] ) )
        return $this->aFiles[$iLinkType][$iLink];
    }
  } // end function throwAllFilesByLink

  /**
  * Delete all selected files for deletion
  * @return void
  * @param array  $aFiles
  * @param int    $iLinkType
  */
  function deleteSelectedFiles( $aFiles, $iLinkType ){
    if( isset( $aFiles ) && is_array( $aFiles ) ){
      $oFF        =& FlatFiles::getInstance( );
      $sFileName  = $this->throwDbNames( $iLinkType );

      foreach( $aFiles as $iFile => $iValue ){
        if( isset( $this->aFilesImages[$iLinkType][$iFile] ) ){
          $this->deleteFilesFromDirs( $this->aFilesImages[$iLinkType][$iFile]['sFileName'], $this->aFilesImages[$iLinkType][$iFile]['iPhoto'] );
        }
      }

      $oFF->deleteInFile( $sFileName, $aFiles, 'iFile' );
    }
  } // end function deleteSelectedFiles

  /**
  * Delete all files in selected link
  * @return void
  * @param array  $aData
  * @param int    $iLinkType
  * @param string $sIndex
  */
  function deleteFiles( $aData, $iLinkType, $sIndex ){
    if( isset( $this->aFilesImages[$iLinkType] ) ){
      $oFF        =& FlatFiles::getInstance( );
      $sFileName  = $this->throwDbNames( $iLinkType );

      foreach( $this->aFilesImages[$iLinkType] as $iFile => $aFile ){
        if( isset( $aData[$aFile[$sIndex]] ) ){
          $this->deleteFilesFromDirs( $aFile['sFileName'], $aFile['iPhoto'] );
        }
      }

      $oFF->deleteInFile( $sFileName, $aData, $sIndex );
    }
  } // end function deleteFiles

  /**
  * Return files list from directory
  * @return string
  * @param string $sFile
  * @param int    $iLink
  * @param in     $iLinkType
  */
  function listFilesInDir( $sFile, $iLink = null, $iLinkType = null ){
    $oTpl       =& TplParser::getInstance( );
    $oFF        =& FlatFiles::getInstance( );
    $content    = null;

    if( isset( $iLink ) && is_numeric( $iLink ) ){
      $aFiles = $this->throwAllFilesByLink( $iLink, $iLinkType );
      if( isset( $aFiles ) ){
        $iCount = count( $aFiles );
        for( $i = 0; $i < $iCount; $i++ ){
          $aFilesInLink[$this->aFilesImages[$iLinkType][$aFiles[$i]]['sFileName']] = true;
        } // end for
      }
      $aFiles = null;
    }

    $oDir = dir( DIR_FILES );
    
    while( false !== ( $sFileName = $oDir->read( ) ) ){
      if( is_file( DIR_FILES.$sFileName ) && $sFileName != '.htaccess' ){
        $aFiles[] = $sFileName;
      }
    }
    $oDir->close( );

    if( isset( $aFiles ) ){
      sort( $aFiles );
      $iCount = count( $aFiles );
      for( $i = 0; $i < $iCount; $i++ ){
        $aData['sFileName'] = $aFiles[$i];
        $aData['iStyle']    = ( $i % 2 ) ? 0: 1;
        $aData['iStatus']   = isset( $aFilesInLink[$aData['sFileName']] ) ? 1 : 0;
        $aData['iFile']     = $i;
        $aData['iPhoto']    = ( $oFF->checkCorrectFile( $aData['sFileName'], 'gif|jpg|png|jpeg' ) == true ) ? 1 : 0;

        $oTpl->setVariables( 'aData', $aData );

        if( $aData['iPhoto'] == 1 )
          $content .= $oTpl->tbHtml( $sFile, 'IMAGES_LIST_DIR' );
        else
          $content .= $oTpl->tbHtml( $sFile, 'FILES_LIST_DIR' );
      } // end for

      return $oTpl->tbHtml( $sFile, 'FILES_HEAD_DIR' ).$content.$oTpl->tbHtml( $sFile, 'FILES_FOOT_DIR' );
    }
  } // end function listFilesInDir

  /**
  * Delete files from directories
  * @return void
  * @param string $sFileName
  * @param int    $iImage
  */
  function deleteFilesFromDirs( $sFileName, $iImage ){
    if( $iImage == 1 && isset( $this->aDirs ) ){
      foreach( $this->aDirs as $mDir => $bValue ){
        if( is_file( DIR_FILES.$mDir.'/'.$sFileName ) )
          unlink ( DIR_FILES.$mDir.'/'.$sFileName );
      }
    }
    if( is_file( DIR_FILES.$sFileName ) )
      unlink ( DIR_FILES.$sFileName );
  } // end function deleteFilesFromDirs

  /**
  * Return thumbs dir names
  * @return array
  */
  function generateThumbDirs( ){
    $oDir = dir( DIR_FILES );
    while( false !== ( $mDir = $oDir->read( ) ) ){
      if( is_numeric( $mDir ) && is_dir( DIR_FILES.$mDir ) ){
        $this->aDirs[$mDir] = true;
      }
    }
    $oDir->close( );
  } // end function generateThumbDirs

  /**
  * Save files description and sizes
  * @return void
  * @param array  $aForm
  * @param int    $iLink
  * @param int    $iLinkType
  */
  function saveFiles( $aForm, $iLink, $iLinkType ){
    if( isset( $aForm['aFilesDescription'] ) && is_array( $aForm['aFilesDescription'] ) ){

      $aFiles = $this->throwAllFilesByLink( $iLink, $iLinkType );

      if( isset( $aFiles ) ){
        $iCount = count( $aFiles );
        for( $i = 0; $i < $iCount; $i++ ){
          if( !isset( $aForm['aFilesDelete'][$aFiles[$i]] ) ){
            $aData = $this->aFilesImages[$iLinkType][$aFiles[$i]];
            $aForm['aFilesDescription'][$aData['iFile']] = changeTxt( trim( $aForm['aFilesDescription'][$aData['iFile']] ), '' );
            
            if( isset( $aForm['aFilesDescription'][$aData['iFile']] ) && $aForm['aFilesDescription'][$aData['iFile']] != $aData['sDescription'] ){
              $aChange[$aData['iFile']]['sDescription'] = $aForm['aFilesDescription'][$aData['iFile']];
            }

            if( isset( $aForm['aFilesSizes1'][$aData['iFile']] ) && $aForm['aFilesSizes1'][$aData['iFile']] != $aData['iSize1'] ){
              $aChange[$aData['iFile']]['iSize1'] = ( $aForm['aFilesSizes1'][$aData['iFile']] > 0 ) ? $aForm['aFilesSizes1'][$aData['iFile']] : '';
            }

            if( isset( $aForm['aFilesSizes2'][$aData['iFile']] ) && $aForm['aFilesSizes2'][$aData['iFile']] != $aData['iSize2'] ){
              $aChange[$aData['iFile']]['iSize2'] = ( $aForm['aFilesSizes2'][$aData['iFile']] > 0 ) ? $aForm['aFilesSizes2'][$aData['iFile']] : '';
            }

            if( $aForm['aFilesPositions'][$aData['iFile']] != $aData['iPosition'] ){
              $aChange[$aData['iFile']]['iPosition'] = $aForm['aFilesPositions'][$aData['iFile']];
            }
            
            if( isset( $aForm['aFilesTypes'][$aData['iFile']] ) && $aForm['aFilesTypes'][$aData['iFile']] != $aData['iType'] ){
              $aChange[$aData['iFile']]['iType'] = $aForm['aFilesTypes'][$aData['iFile']];
            }
          }
        } // end for
      }

      if( isset( $aChange ) ){
        $oFF    =& FlatFiles::getInstance( );
        $iCount = count( $aChange );
        $i      = 1;
        $sFile  = $this->throwDbNames( $iLinkType );

        foreach( $aChange as $iFile => $aData ){
          $aSave = array_merge( $this->aFilesImages[$iLinkType][$iFile], $aData );
          
          if( $i == $iCount )
            $oFF->save( $sFile, $aSave, 'iFile', 'sort' );
          else
            $oFF->save( $sFile, $aSave, 'iFile' );

          if( isset( $aData['iSize1'] ) || isset( $aData['iSize2'] ) )
            $this->generateThumbs( $aSave['sFileName'], $aSave['iSize1'], $aSave['iSize2'] );
          $i++;
        } // end foreach

      }
    }
  } // end function saveFiles

  /**
  * Add uploaded files
  * @param array  $aForm
  * @param int    $iLink
  * @param int    $iLinkType
  * @param string $sLinkName
  */
  function addFilesUploaded( $aForm, $iLink, $iLinkType, $sLinkName ){
    if( isset( $_FILES['aNewFiles']['name'] ) ){
      $iCount = count( $_FILES['aNewFiles']['name'] );
      $i2     = 0;
      $oFF    =& FlatFiles::getInstance( );

      $this->mData = null;

      for( $i = 0; $i < $iCount; $i++ ){
        if( !empty( $_FILES['aNewFiles']['name'][$i] ) && !preg_match( '/(.php|.php2|.php3|.php4|.php5|.phtml|.pwml|.inc|.asp|.aspx|.ascx|.jsp|.cfm|.cfc|.pl|.bat|.exe|.com|.dll|.vbs|.js|.reg|.cgi|.htaccess|.asis|.sh|.shtml|.shtm|.phtm)/i', $_FILES['aNewFiles']['name'][$i] ) ){
          $this->mData[$i2]['sFileName']    = $oFF->uploadFile( Array( 'tmp_name' => $_FILES['aNewFiles']['tmp_name'][$i], 'name' => ( $GLOBALS['config']['change_files_names'] === true && isset( $_POST['sName'] ) ) ? change2Url( $_POST['sName'] ).'.'.$oFF->throwExtOfFile( $_FILES['aNewFiles']['name'][$i] ) : $_FILES['aNewFiles']['name'][$i] ), DIR_FILES );
          $this->mData[$i2]['iSize1']       = ( $aForm['aNewFilesSizes1'][$i] > 0 ) ? $aForm['aNewFilesSizes1'][$i] : null;
          $this->mData[$i2]['iSize2']       = ( $aForm['aNewFilesSizes2'][$i] > 0 ) ? $aForm['aNewFilesSizes2'][$i] : null;
          $this->mData[$i2]['iType']        = is_numeric( $aForm['aNewFilesTypes'][$i] ) ? $aForm['aNewFilesTypes'][$i] : 1;
          $this->mData[$i2]['iPosition']    = is_numeric( $aForm['aNewFilesPositions'][$i] ) ? $aForm['aNewFilesPositions'][$i] : 0;
          $this->mData[$i2]['sDescription'] = changeTxt( $aForm['aNewFilesDescriptions'][$i], '' );
          $this->mData[$i2][$sLinkName]     = $iLink;

          $i2++;
        }
      } // end for

      if( isset( $this->mData ) )
        $this->addFiles( $iLinkType );
    }
  } // end function addFilesUploaded

  /**
  * Add files from server
  * @param array  $aForm
  * @param int    $iLink
  * @param int    $iLinkType
  * @param string $sLinkName
  */
  function addFilesFromServer( $aForm, $iLink, $iLinkType, $sLinkName ){
    if( isset( $aForm['aDirFiles'] ) ){
      $i    = 0;
      $oFF  =& FlatFiles::getInstance( );

      $this->mData = null;

      foreach( $aForm['aDirFiles'] as $iKey => $sFile ){
        if( is_file( DIR_FILES.$sFile ) ){
          $this->mData[$i]['sFileName']    = $oFF->checkIsFile( ( $GLOBALS['config']['change_files_names'] === true && isset( $_POST['sName'] ) ) ? change2Url( $_POST['sName'] ).'.'.$oFF->throwExtOfFile( $sFile ) : $sFile, DIR_FILES );
          $this->mData[$i]['iSize1']       = ( $aForm['aDirFilesSizes1'][$iKey] > 0 ) ? $aForm['aDirFilesSizes1'][$iKey] : null;
          $this->mData[$i]['iSize2']       = ( $aForm['aDirFilesSizes2'][$iKey] > 0 ) ? $aForm['aDirFilesSizes2'][$iKey] : null;
          $this->mData[$i]['iType']        = is_numeric( $aForm['aDirFilesTypes'][$iKey] ) ? $aForm['aDirFilesTypes'][$iKey] : 1;
          $this->mData[$i]['iPosition']    = is_numeric( $aForm['aDirFilesPositions'][$iKey] ) ? $aForm['aDirFilesPositions'][$iKey] : 0;
          $this->mData[$i]['sDescription'] = changeTxt( $aForm['aDirFilesDescriptions'][$iKey], '' );
          $this->mData[$i][$sLinkName]     = $iLink;
          copy( DIR_FILES.$sFile, DIR_FILES.$this->mData[$i]['sFileName'] );
          $i++;
        }
      }

      if( isset( $this->mData ) )
        $this->addFiles( $iLinkType );
    }
  } // end function addFilesFromServer

  /**
  * Add files
  * @return void
  * @param int    $iLinkType
  */
  function addFiles( $iLinkType ){
    if( isset( $this->mData ) && is_array( $this->mData ) ){
      $oFF      =& FlatFiles::getInstance( );
      $sFile    = $this->throwDbNames( $iLinkType );
      $iLastId  = $oFF->throwLastId( $sFile, 'iFile' );
      $iCount   = count( $this->mData );
      $i        = 0;

      foreach( $this->mData as $iKey => $aData ){
        $aData['iPhoto'] = ( $oFF->checkCorrectFile( $aData['sFileName'], 'gif|jpg|png|jpeg' ) == true ) ? 1 : 0;

        if( $aData['iPhoto'] == 1 ){
          $this->generateThumbs( $aData['sFileName'], $aData['iSize1'], $aData['iSize2'] );
        }
        else{
          $aData['iType']   = null;
          $aData['iSize1']  = null;
          $aData['iSize2']  = null;
        }
        
        $aData['iFile'] = ++$iLastId;

        if( $i + 1 == $iCount ){
          $oFF->save( $sFile, $aData, null, 'sort' );
        }
        else
          $oFF->save( $sFile, $aData );
        $i++;
      } // end foreach

      $this->mData = null;
    }
  } // end function addFiles 

  /**
  * Generate photo thumbnails
  * @return void
  * @param string $sFileName
  * @param int    $iSize1
  * @param int    $iSize2
  */
  function generateThumbs( $sFileName, $iSize1, $iSize2 ){
    $oFoto  =& FotoJobs::getInstance( );

    $aImgSize = $oFoto->throwImgSize( DIR_FILES.$sFileName );
    if( defined( 'MAX_DIMENSION_OF_IMAGE' ) && ( $aImgSize['width'] > MAX_DIMENSION_OF_IMAGE || $aImgSize ['height'] > MAX_DIMENSION_OF_IMAGE ) ){
      if( $aImgSize['width'] < $oFoto->iMaxForThumbSize && $aImgSize['height'] < $oFoto->iMaxForThumbSize ){
        $oFoto->setThumbSize( MAX_DIMENSION_OF_IMAGE );
        $oFoto->createThumb( DIR_FILES.$sFileName, DIR_FILES, $sFileName );
      }
    }
    
    if( isset( $GLOBALS['config']['pages_images_sizes'][$iSize1] ) )
      $iSize1 = $GLOBALS['config']['pages_images_sizes'][$iSize1];
    else
      $iSize1 = $GLOBALS['config']['pages_images_sizes'][0];

    if( isset( $GLOBALS['config']['pages_images_sizes'][$iSize2] ) )
      $iSize2 = $GLOBALS['config']['pages_images_sizes'][$iSize2];
    else
      $iSize2 = $GLOBALS['config']['pages_images_sizes'][0];

    $sThumbsDir1 = DIR_FILES.$iSize1.'/';
    $sThumbsDir2 = DIR_FILES.$iSize2.'/';

    if( !is_dir( $sThumbsDir1 ) ){
      mkdir( $sThumbsDir1 );
      chmod( $sThumbsDir1, 0777 );
    }
    if( !is_dir( $sThumbsDir2 ) ){
      mkdir( $sThumbsDir2 );
      chmod( $sThumbsDir2, 0777 );
    }

    if( !is_file( $sThumbsDir1.$sFileName ) )
      $oFoto->createCustomThumb( DIR_FILES.$sFileName, $sThumbsDir1, $iSize1, $sFileName, true );
    if( !is_file( $sThumbsDir2.$sFileName ) )
      $oFoto->createCustomThumb( DIR_FILES.$sFileName, $sThumbsDir2, $iSize2, $sFileName, true );
  } // end function generateThumbs

  /**
  * List all files from db
  * @return string
  * @param string $sFile
  * @param int    $iLinkType
  */
  function listAllFiles( $sFile, $iLinkType = 1 ){
    $aSizes     = $GLOBALS['config']['pages_images_sizes'];
    $aTypes     = $GLOBALS['aPhotoTypes'];
    $oFF        =& FlatFiles::getInstance( );
    $oTpl       =& TplParser::getInstance( );
    $oPage      =& PagesAdmin::getInstance( );
    $oProduct   =& ProductsAdmin::getInstance( );
    $content    = null;

    if( isset( $this->aFilesImages[$iLinkType] ) ){
      $aSort = Array( );
      foreach( $this->aFilesImages[$iLinkType] as $aData ){
        $aSort[] = Array( $aData['sFileName'], $aData['iFile'] );
      } // end foreach
      sort( $aSort );

      $iCount = count( $aSort );
      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aFilesImages[$iLinkType][$aSort[$i][1]];
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;

        $aData['sPhotoTypesSelect'] = throwSelectFromArray( $aTypes, $aData['iType'] );
        $aData['sLink'] = ( $iLinkType == 1 ) ? '<a href="?p=p-form&amp;iPage='.$aData['iPage'].'" target="_blank">'.$oPage->aPages[$aData['iPage']]['sName'].'</a>' : '<a href="?p=products-form&amp;iProduct='.$aData['iProduct'].'" target="_blank">'.$oProduct->aProducts[$aData['iProduct']]['sName'].'</a>';

        if( !empty( $aData['iPhoto'] ) && $aData['iPhoto'] == 1 )
          $sBlock = 'ALL_IMAGES_LIST';
        else
          $sBlock = 'ALL_FILES_LIST';

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, $sBlock );
      } // end for
    }

    if( isset( $content ) )
      return $oTpl->tbHtml( $sFile, 'ALL_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'ALL_FOOT' );
  } // end function listAllFiles

  /**
  * Save all files data
  * @return void
  * @param array  $aForm
  * @param int    $iLinkType
  */
  function saveAllFiles( $aForm, $iLinkType = 1 ){
    if( isset( $aForm['aFilesDescription'] ) && is_array( $aForm['aFilesDescription'] ) ){

      if( isset( $aForm['aFilesDelete'] ) )
        $this->deleteSelectedFiles( $aForm['aFilesDelete'], $iLinkType );
      foreach( $this->aFilesImages[$iLinkType] as $aData ){
        if( !isset( $aForm['aFilesDelete'][$aData['iFile']] ) ){
          $aForm['aFilesDescription'][$aData['iFile']] = changeTxt( trim( $aForm['aFilesDescription'][$aData['iFile']] ), '' );
          
          if( isset( $aForm['aFilesDescription'][$aData['iFile']] ) && $aForm['aFilesDescription'][$aData['iFile']] != $aData['sDescription'] ){
            $aChange[$aData['iFile']]['sDescription'] = $aForm['aFilesDescription'][$aData['iFile']];
          }

          if( $aForm['aFilesPositions'][$aData['iFile']] != $aData['iPosition'] ){
            $aChange[$aData['iFile']]['iPosition'] = $aForm['aFilesPositions'][$aData['iFile']];
          }
          
          if( isset( $aForm['aFilesTypes'][$aData['iFile']] ) && $aForm['aFilesTypes'][$aData['iFile']] != $aData['iType'] ){
            $aChange[$aData['iFile']]['iType'] = $aForm['aFilesTypes'][$aData['iFile']];
          }
        }
      } // end foreach

      if( isset( $aChange ) ){
        $oFF    =& FlatFiles::getInstance( );
        $iCount = count( $aChange );
        $i      = 1;
        $sFile  = $this->throwDbNames( $iLinkType );

        foreach( $aChange as $iFile => $aData ){
          $aSave = array_merge( $this->aFilesImages[$iLinkType][$iFile], $aData );
          
          if( $i == $iCount )
            $oFF->save( $sFile, $aSave, 'iFile', 'sort' );
          else
            $oFF->save( $sFile, $aSave, 'iFile' );
          $i++;
        } // end foreach
      }
    }
  } // end function saveAllFiles
};
?>