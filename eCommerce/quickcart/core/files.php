<?php
class Files
{

  var $aFiles;
  var $aImages;
  var $aImagesDefault;
  var $aFilesImages;
  var $aImagesTypes;
  var $mData = null;

  function &getInstance( ){  
    static $oInstance = null;
    if( !isset( $oInstance ) ){  
      $oInstance = new Files( );  
    }  
    return $oInstance;  
  } // end function getInstance
  
  /**
  * Constructor
  * @return void
  */
  function Files( ){
    $this->generateCache( );
  } // end function Files

  /**
  * Return database name
  * @return mixed
  * @param int  $iDbType
  */
/*  function throwDbNames( $iDbType = null ){
    $aFiles[1] = DB_PAGES_FILES;
    $aFiles[2] = DB_PRODUCTS_FILES;

    if( isset( $iDbType ) )
      return isset( $aFiles[$iDbType] ) ? $aFiles[$iDbType] : null;
    else
      return $aFiles;
  } // end function throwDbNames
*/
  /**
  * List all images by types
  * @return array
  * @param string $sFile
  * @param int    $iLink
  * @param int    $iLinkType
  */
  function listImagesByTypes( $sFile, $iLink, $iLinkType = 1 ){
    if( isset( $this->aImagesTypes[$iLinkType][$iLink] ) ){
      $aReturn  = Array( 1 => null, null, null, null );
      $oTpl     =& TplParser::getInstance( );
      foreach( $this->aImagesTypes[$iLinkType][$iLink] as $iType => $aImages ){
        if( $iType < 3 ){
          $iCount = count( $aImages );
          for( $i = 0; $i < $iCount; $i++ ){
            if( isset( $this->aFilesImages[$iLinkType][$aImages[$i]] ) ){
              $aData = $this->aFilesImages[$iLinkType][$aImages[$i]];
              $aData['iStyle']  = ( $i % 2 ) ? 0: 1;
              $aData['sStyle']  = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;

              if( !empty( $aData['sDescription'] ) ){
                $oTpl->setVariables( 'aData', $aData );
                $aData['sDescriptionContent'] = $oTpl->tbHtml( $sFile, 'IMAGES_DESCRIPTION_'.$iType );
              }

              $oTpl->setVariables( 'aData', $aData );
              $aReturn[$iType] .= $oTpl->tbHtml( $sFile, 'IMAGES_LIST_'.$iType );
            }
          } // end for
          if( isset( $aReturn[$iType] ) )
            $aReturn[$iType] = $oTpl->tbHtml( $sFile, 'IMAGES_HEAD_'.$iType ).$aReturn[$iType].$oTpl->tbHtml( $sFile, 'IMAGES_FOOT_'.$iType );
        }
        else{
          // gallery
        }
      }
      if( isset( $aReturn ) )
        return $aReturn;
    }
  } // end function listImagesByTypes

  /**
  * List all files
  * @return array
  * @param string $sFile
  * @param int    $iLink
  * @param int    $iLinkType
  */
  function listFiles( $sFile, $iLink, $iLinkType = 1 ){
    $content = null;
    if( isset( $this->aFiles[$iLinkType][$iLink] ) ){
      $oTpl   =& TplParser::getInstance( );
      $oFF    = new FileJobs(); //epesi - was: $oFF =& FlatFiles::getInstance( );
      $iCount = count( $this->aFiles[$iLinkType][$iLink] );
      $aExt   = throwIconsFromExt( );

      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aFilesImages[$iLinkType][$this->aFiles[$iLinkType][$iLink][$i]];
        $aData['iStyle']  = ( $i % 2 ) ? 0: 1;
        $aData['sStyle']  = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;

        if( !empty( $aData['sDescription'] ) ){
          $oTpl->setVariables( 'aData', $aData );
          $aData['sDescriptionContent'] = $oTpl->tbHtml( $sFile, 'FILES_DESCRIPTION' );
        }
        
        $aName = $oFF->throwNameExtOfFile( $aData['sFileName'] );
        if( !isset( $aExt[$aName[1]] ) )
          $aExt[$aName[1]] = 'nn';
        $aData['sIcon'] = 'ico_'.$aExt[$aName[1]];

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'FILES_LIST' );
      } // end for

      if( isset( $content ) ){
        $oTpl->setVariables( 'aData', $aData );
        return $oTpl->tbHtml( $sFile, 'FILES_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FILES_FOOT' );
      }
    }
  } // end function listFiles

  /**
  * Return default image
  * @return string
  * @param int  $iLink
  * @param int  $iLinkType
  */
  function throwDefaultImage( $iLink, $iLinkType ){
    if( isset( $this->aImagesDefault[$iLinkType][$iLink] ) )
      return $this->aFilesImages[$iLinkType][$this->aImagesDefault[$iLinkType][$iLink]];
  } // end function throwDefaultImage

  /**
  * Generate cache variables
  * @return void
  */
  function generateCache( ){
    global $config;

/*    $oFF    =& FlatFiles::getInstance( );
    $aFiles = $this->throwDbNames( );

    foreach( $aFiles as $iKey => $sValue ){
      if( $iKey == 1 ){
        $iSize1 = 0;
        $iSize2 = 0;
      }

      $this->aImages[$iKey]     = null;
      $this->aFiles[$iKey]      = null;
      $this->aImagesTypes[$iKey]= null;

      if( is_file( $sValue ) ){
        $aFile      = file( $sValue );
        $iCount     = count( $aFile );
        $sFunction  = $oFF->throwFunctionName( $sValue );
        for( $i = 1; $i < $iCount; $i++ ){
          $aExp = explode( '$', $aFile[$i] );
          
          $this->aFilesImages[$iKey][$aExp[0]] = $sFunction( $aExp );

          if( !empty( $aExp[4] ) && $aExp[4] == 1 ){
            if( !isset( $this->aImagesDefault[$iKey][$aExp[1]] ) )
              $this->aImagesDefault[$iKey][$aExp[1]] = $aExp[0];

            $this->aImages[$iKey][$aExp[1]][] = $aExp[0];

            if( !is_numeric( $this->aFilesImages[$iKey][$aExp[0]]['iSize1'] ) ){
              $this->aFilesImages[$iKey][$aExp[0]]['iSize1'] = $iSize1;
            }
            if( !is_numeric( $this->aFilesImages[$iKey][$aExp[0]]['iSize2'] ) )
              $this->aFilesImages[$iKey][$aExp[0]]['iSize2'] = $iSize2;

            $this->aFilesImages[$iKey][$aExp[0]]['iSizeValue1'] = $config['pages_images_sizes'][$this->aFilesImages[$iKey][$aExp[0]]['iSize1']];
            $this->aFilesImages[$iKey][$aExp[0]]['iSizeValue2'] = $config['pages_images_sizes'][$this->aFilesImages[$iKey][$aExp[0]]['iSize2']];

            $this->aImagesTypes[$iKey][$aExp[1]][$aExp[6]][] = $aExp[0];
          }
          else{
            $this->aFiles[$iKey][$aExp[1]][] = $aExp[0];
          }
          
        }
      }
    }*/
    // { epesi
    //pages
    $this->aImages[0]     = null;
    $this->aFiles[0]      = null;
    $this->aImagesTypes[0]= null;
    
    //products
    $this->aImages[1]     = null;
    $this->aFiles[1]      = null;
    $this->aImagesTypes[1]= null;
    
    // } epesi
  } // end function generateCache
};
?>