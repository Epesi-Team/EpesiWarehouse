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
  * List all images by types
  * @return array
  * @param string $sFile
  * @param int    $iLink
  * @param int    $iLinkType
  */
  function listImagesByTypes( $sFile, $iLink, $iLinkType = 1 ){
    $this->getFiles($iLink,$iLinkType);
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
    $this->getFiles($iLink,$iLinkType);
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
    $this->getFiles($iLink,$iLinkType);
    if( isset( $this->aImagesDefault[$iLinkType][$iLink] ) )
      return $this->aFilesImages[$iLinkType][$this->aImagesDefault[$iLinkType][$iLink]];
    return false;
  } // end function throwDefaultImage

  /**
  * Generate cache variables
  * @return void
  */
  function getFiles( $product ,$iKey){
    global $config;
    static $cache;
    if(!isset($cache))
    	$cache = array();
    if(isset($cache[$product][$iKey]))
    	return;
    $cache[$product][$iKey] = 1;

    //pages
    $this->aImages[1]     = null;
    $this->aFiles[1]      = null;
    $this->aImagesTypes[1]= null;
    
    //products
    $this->aImages[2]     = null;
    $this->aFiles[2]      = null;
    $this->aImagesTypes[2]= null;
    
    if($iKey==1) {
    	$id = ($product-2)/4;
    	$where = 'ual.local=CONCAT(\'premium_ecommerce_pages/\',%d) OR ual.local=CONCAT(\'premium_ecommerce_pages_data/\',%s,\'/\',%d)';
    } else {
    	$id = $product;
    	$where = 'ual.local=CONCAT(\'premium_ecommerce_products/\',%d) OR ual.local=CONCAT(\'premium_ecommerce_descriptions/\',%s,\'/\',%d)';
    }
    
    $ret = DB::Execute('SELECT ual.id,ual.local, f.original, ual.sticky, f.revision, d.text
			FROM utils_attachment_link ual 
			INNER JOIN utils_attachment_file f ON (f.attach_id=ual.id AND f.revision=(SELECT max(revision) FROM utils_attachment_file WHERE attach_id=ual.id)) 
			INNER JOIN utils_attachment_note d ON (d.attach_id=ual.id AND d.revision=(SELECT max(revision) FROM utils_attachment_note WHERE attach_id=ual.id)) 
			WHERE ual.deleted=0 AND ('.$where.')',array($id,LANGUAGE,$id));
    $th_size = $config['default_image_size'];
    
    $isDuplicateFile = array();
    while($row = $ret->FetchRow()) {
    	if(isset($isDuplicateFile[$row['original']]))
    		continue;
    	$isDuplicateFile[$row['original']] = 1;
	$ext = strrchr($row['original'],'.');
	if(!file_exists('files/epesi/'.$row['id'].'_'.$row['revision'].$ext)) continue;
	$photo = preg_match('/^\.(jpg|jpeg|gif|png|bmp)$/i',$ext);
	$type = 2; // ??????????
	$this->aFilesImages[$iKey][$row['id']] = array( 'iFile' => $row['id'], 'iProduct' => $product, 'sFileName' => 'epesi/'.$row['id'].'_'.$row['revision'].$ext, 'sDescription' => $row['original']!=$row['text']?$row['text']:'', 'iPhoto' => $photo, 'iPosition' => 0, 'iType' =>$type, 'iSize1' => $th_size, 'iSize2' => $th_size );

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
    $this->getFiles($iLink,$iLinkType);
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