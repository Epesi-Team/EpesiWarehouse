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
          $aReturn[$iType] = $this->listGalleryImages( $sFile, $iLink, $iLinkType, $iType );
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
/*
    $oFF    =& FlatFiles::getInstance( );
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
    }
    */
    // { epesi
    //pages
    $this->aImages[1]     = null;
    $this->aFiles[1]      = null;
    $this->aImagesTypes[1]= null;
    
    //products
    $this->aImages[2]     = null;
    $this->aFiles[2]      = null;
    $this->aImagesTypes[2]= null;
    
    $ret = DB::Execute('SELECT ual.id,ual.local, f.original, ual.sticky, f.revision, d.text
			FROM utils_attachment_link ual 
			INNER JOIN utils_attachment_file f ON (f.attach_id=ual.id AND f.revision=(SELECT max(revision) FROM utils_attachment_file WHERE attach_id=ual.id)) 
			INNER JOIN utils_attachment_note d ON (d.attach_id=ual.id AND d.revision=(SELECT max(revision) FROM utils_attachment_note WHERE attach_id=ual.id)) 
			WHERE ual.deleted=0 AND ual.local LIKE \'Premium/Warehouse/eCommerce/%\'');
    $th_size = unserialize(DB::GetOne('SELECT value FROM variables WHERE name=%s',array('quickcart_thumbnail_size')));
    
    while($row = $ret->FetchRow()) {
	$ext = strrchr($row['original'],'.');
	if(!file_exists('files/epesi/'.$row['id'].'_'.$row['revision'].$ext)) continue;
	$photo = eregi('^\.(jpg|jpeg|gif|png|bmp)$',$ext);
	$product = basename($row['local']);
	if(ereg('^Premium/Warehouse/eCommerce/Products',$row['local'])) {
	    $iKey = 2;
	    if(ereg('^Premium/Warehouse/eCommerce/ProductsDesc',$row['local'])) {
		$lang = basename(dirname($row['local']));
		if($lang!=LANGUAGE) continue;
	    }
	} else {
	    $iKey = 1;
	    $product = $product*4+2;
	}	    
	$type = 2; // ??????????
	$this->aFilesImages[$iKey][$row['id']] = array( 'iFile' => $row['id'], 'iProduct' => $product, 'sFileName' => 'epesi/'.$row['id'].'_'.$row['revision'].$ext, 'sDescription' => $row['text'], 'iPhoto' => $photo, 'iPosition' => 0, 'iType' =>$type, 'iSize1' => $th_size, 'iSize2' => $th_size );

	if( $photo ){
	    //sticky image is default one
            if( !isset( $this->aImagesDefault[$iKey][$product] ) || $row['sticky'] )
        	$this->aImagesDefault[$iKey][$product] = $row['id'];

            $this->aImages[$iKey][$product][] = $row['id'];

            $this->aFilesImages[$iKey][$row['id']]['iSizeValue1'] = $config['pages_images_sizes'][$this->aFilesImages[$iKey][$row['id']]['iSize1']];
            $this->aFilesImages[$iKey][$row['id']]['iSizeValue2'] = $config['pages_images_sizes'][$this->aFilesImages[$iKey][$row['id']]['iSize2']];

            $this->aImagesTypes[$iKey][$product][$type][] = $row['id'];
          }
          else{
            $this->aFiles[$iKey][$product][] = $row['id'];
          }
    }
    // } epesi

  } // end function generateCache

  /**
  * Returns images gallery
  * @return string
  * @param string $sFile
  * @param int    $iLink
  * @param int    $iLinkType
  * @param int    $iType
  */
  function listGalleryImages( $sFile, $iLink, $iLinkType, $iType ){
    $aImages  = $this->aImagesTypes[$iLinkType][$iLink][$iType];
    $iCount   = count( $aImages );
    $iColumns = 3;
    $iWidth   = (int) ( 100 / $iColumns );
    $oTpl     =& TplParser::getInstance( );
    $content  = null;

    for( $i = 0; $i < $iCount; $i++ ){
      $aData = $this->aFilesImages[$iLinkType][$aImages[$i]];
      $aData['iWidth']  = $iWidth;
      $aData['iStyle']  = ( $i % 2 ) ? 0: 1;
      $aData['sStyle']  = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;

      if( $i > 0 && $i % $iColumns == 0 ){
        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'GALLERY_BREAK' );
      }

      if( !empty( $aData['sDescription'] ) ){
        $oTpl->setVariables( 'aData', $aData );
        $aData['sDescriptionContent'] = $oTpl->tbHtml( $sFile, 'GALLERY_DESCRIPTION' );
      }

      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'GALLERY_LIST' );
    } // end for

    while( $i % $iColumns > 0 ){
      $content .= $oTpl->tbHtml( $sFile, 'GALLERY_BLANK' );
      $i++;
    } // end while  

    if( isset( $content ) )
      return $oTpl->tbHtml( $sFile, 'GALLERY_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'GALLERY_FOOT' );
  } // end function listGalleryImages
};
?>