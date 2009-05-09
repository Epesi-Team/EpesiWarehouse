<?php
/**
* FotoJobs - changing photos
* @access   public 
* @version  0.1.0 
* @require  FileJobs
* @require  Trash
* @date     2007-02-13 08:51:46
*/
class FotoJobs extends FileJobs
{

  var $iThumbX          = 100;
  var $iThumbY          = 100;
  var $iQuality         = 80;
  var $iThumbAdd        = '_m';
  var $iCustomThumbX    = 250;
  var $iCustomThumbY    = 250;
  var $sCustomThumbAdd  = null;
  var $sExt             = 'jpg';
  var $sExtDot          = '.jpg';
  var $iMaxForThumbSize = 2000;
  var $fRatio           = 0.80;

  function &getInstance( ){  
    static $oInstance = null;
    if( !isset( $oInstance ) ){  
      $oInstance = new FotoJobs( );  
    }  
    return $oInstance;  
  } // end function getInstance

  /**
  * Constuctor
  * @return void
  * @param  int   $iThumbSize
  */
  function FotoJobs( $iThumbSize = 100 ){
    $this->iThumbX = $iThumbSize;
  } // end function FotoJobs

  /**
  * Sets thumb size
  * @return void
  * @param  int   $iThumbSize
  */
  function setThumbSize( $iThumbSize = 100 ){
    $this->iThumbX = $iThumbSize;
  } // end function setThumbSize

  /**
  * Sets thumb quality
  * @return void
  * @param  int   $iThumbQuality
  */
  function setThumbQuality( $iThumbQuality = 80 ){
    $this->iQuality = $iThumbQuality;
  } // end function setThumbQuality

  /**
  * Sets name addition for thumb
  * @return void
  * @param  int   $iThumbAdd
  */
  function setThumbAdd( $iThumbAdd = '_m' ){
    $this->iThumbAdd = $iThumbAdd;
  } // end function setThumbAdd

  /**
  * Sets name addition for custom thumb
  * @return void
  * @param  int   $iThumbAdd
  */
  function setCustomThumbAdd( $sThumbAdd = null ){
    $this->sCustomThumbAdd = $sThumbAdd;
  } // end function setCustomThumbAdd

  /**
  * Sets max dimension of picture (when bigger thumb wont be create)
  * @return void
  * @param  int   $iMaxForThumbSize
  */
  function setMaxForThumbSize( $iMaxForThumbSize = 2000 ){
    $this->iMaxForThumbSize = $iMaxForThumbSize;
  } // end function setMaxForThumbSize

  /**
  * Sets ratio of image
  * @return void
  * @param  int   $fRatio
  */
  function setRatio( $fRatio = 0.80 ){
    $this->fRatio = $fRatio;
  } // end function setRatio

  /**
  * Upload and copy of files and create thumbs from them
  * @return array
  * @param string $sDestDir - destination directory
  * @param mixed  $mImgSrc - when upload = $_FILES or when copy = file path
  * @param string $sImgOutput - suggested output file name
  * @param mixed  $sOption - upload or copy
  */
  function copyAndCreateThumb( $sDestDir, $mImgSrc, $sImgOutput, $sOption = null ){

    // remember thumb size
    $iOldSize = $this->iThumbX;

    if( !is_dir( $sDestDir ) )
      return null;

    $sImgOutput = $this->throwNameOfFile( $sImgOutput );

    $sImgOutput = $this->changeFileName( $sImgOutput );

    if( $sOption == 'upload' ){
      if( is_uploaded_file( $mImgSrc['tmp_name'] ) && is_file( $mImgSrc['tmp_name'] ) && filesize( $mImgSrc['tmp_name'] ) > 0 && $this->checkCorrectFile( $mImgSrc['name'], 'jpg|jpeg|gif|png' ) == 1 ){
        $this->sExt = $this->throwExtOfFile( $mImgSrc['name'] );
        $this->sExtDot = isset( $this->sExt ) ? '.'.$this->sExt : null;
        $aNewFiles['bFile'] = $this->uploadFile( $mImgSrc, $sDestDir, $sImgOutput.$this->sExtDot );
      }
      else
        return null;
    }
    elseif( $sOption == 'copy' ){
      if( is_file( $mImgSrc ) && filesize( $mImgSrc ) > 0 && $this->checkCorrectFile( $mImgSrc, 'jpg|jpeg|gif|png' ) == 1 ){
        $this->sExt = $this->throwExtOfFile( $mImgSrc );
        $this->sExtDot = isset( $this->sExt ) ? '.'.$this->sExt : null;
        $aNewFiles['bFile'] = $this->checkIsFile( $sImgOutput.$this->sExtDot, $sDestDir );
        if( !copy( $mImgSrc, $sDestDir.$aNewFiles['bFile'] ) )
          return null;
      }
      else
        return null;
    }
    $sImgPatch = $sDestDir.$aNewFiles['bFile'];

    $aNewFiles['bName'] = $this->throwNameOfFile( $aNewFiles['bFile'] );
    $aNewFiles['sFile'] = $aNewFiles['bName'] . $this->iThumbAdd . '.' . $this->sExtDot;
    $aImgSize = $this->throwImgSize( $sImgPatch );

    if( defined( 'MAX_DIMENSION_OF_IMAGE' ) && ( $aImgSize['width'] > MAX_DIMENSION_OF_IMAGE || $aImgSize ['height'] > MAX_DIMENSION_OF_IMAGE ) ){
      if( $aImgSize['width'] < $this->iMaxForThumbSize && $aImgSize ['height'] < $this->iMaxForThumbSize ){
        $iOldAdd  = $this->iThumbAdd;
        $this->setThumbSize( MAX_DIMENSION_OF_IMAGE );
        $this->setThumbAdd( '' );
        $aNewFiles['bFile'] = $this->createThumb( $sImgPatch, $sDestDir, $aNewFiles['bFile'] );
        $this->setThumbSize( $iOldSize );
        $this->setThumbAdd( $iOldAdd );
      }
      else
        return null;
    }
  
    if( $aImgSize['width'] >= $this->iThumbX || $aImgSize['height'] >= $this->iThumbX ){
      if( $aImgSize['width'] < $this->iMaxForThumbSize && $aImgSize ['height'] < $this->iMaxForThumbSize )
        $aNewFiles['sFile'] = $this->createThumb( $sImgPatch, $sDestDir );
      else
        return null;
    }
    else
      copy( $sImgPatch, $sDestDir.$aNewFiles['sFile'] );

    $aNewFiles['bWidth']    = $aImgSize['width'];
    $aNewFiles['bHeight']   = $aImgSize['height'];
    $aNewFiles['sWidth']    = $this->iThumbX;
    $aNewFiles['sHeight']   = $this->iThumbY;
    $aNewFiles['sName']     = basename( $aNewFiles['sFile'], '.'.$this->sExt );
    $aNewFiles['ext']       = $this->sExt;

    $this->iThumbY = 100;
    $this->setThumbSize( $iOldSize );

    return $aNewFiles;
  } // end function copyAndCreateThumb

  /**
  * Clears propeties of object (set to default)
  * @return void
  */
  function clearAll( ){
    $this->iThumbX = 100;
    $this->iThumbY = 100;
  } // end function clearAll

  /**
  * Returns image size in px
  * @return array/int
  * @param string $imgSrc
  * @param mixed  $sOption
  */
  function throwImgSize( $imgSrc, $sOption = null ){
    $aImg = getImageSize( $imgSrc );

    $aImgSize['width'] = $aImg[0];
    $aImgSize['height'] = $aImg[1];

    if( $sOption == 'width' || $sOption == 'height' )
      return $aImgSize[$sOption];
    else
      return $aImgSize;
  } // end function throwImgSize

  /**
  * Returns image width in px
  * @return int
  * @param  string  $imgSrc
  */
  function throwImgWidth( $imgSrc ){
    return $this->throwImgSize( $imgSrc, 'width' );
  } // end function throwImgWidth
  
  /**
  * Returns image height in px
  * @return int
  * @param  string  $imgSrc
  */
  function throwImgHeight( $imgSrc ){
    return $this->throwImgSize( $imgSrc, 'height' );
  } // end function throwImgHeight


  /**
  * Creates new custom size thumb
  * @return string
  * @param string $sImgSource
  * @param string $sImgDestDir
  * @param int $iSize
  * @param string $sImgOutput
  * @param bool $bOverwrite
  */
  function createCustomThumb( $sImgSource, $sImgDestDir, $iSize = null, $sImgOutput = false, $bOverwrite = null ){

    if( !is_dir( $sImgDestDir ) || $this->checkCorrectFile( $sImgSource, 'jpg|jpeg|gif|png' ) == 0 )
      return null;

    $aImgSize = $this->throwImgSize( $sImgSource );
    if( $aImgSize['width'] < $this->iMaxForThumbSize && $aImgSize ['height'] < $this->iMaxForThumbSize ){

      $sImgExt = $this->throwExtOfFile( $sImgSource );

      if( $sImgOutput == false )
        $sImgOutput = $this->throwNameOfFile( $sImgSource ) . $this->sCustomThumbAdd . '.' . $sImgExt;

      if( !isset( $bOverwrite ) )
        $sImgOutput = $this->checkIsFile( $sImgOutput, $sImgDestDir );

      $iOldSize = $this->iThumbX;
      if( is_numeric( $iSize ) )
        $this->setThumbSize( $iSize );
      else
        $this->setThumbSize( $this->iCustomThumbX );
        
      $sFile = $this->createThumb( $sImgSource, $sImgDestDir, $sImgOutput );
      $this->setThumbSize( $iOldSize );
      return $sFile;
    }
    else
      return null;
  } // end function createCustomThumb


  /**
  * Function make photo thumbs
  * @return int
  * @param string $sImgSource   - source file, from it thumb is created
  * @param string $sImgDestDir  - destination directory for thumb
  * @param string $sImgOutput   - picture name after change (default old name with _m addition)
  * @param mixed  $sOption - b/d
  */ 
  function createThumb( $sImgSource, $sImgDestDir, $sImgOutput = false, $iQuality = null, $sOption = null ) { 
    
    if( !is_dir( $sImgDestDir ) || $this->checkCorrectFile( $sImgSource, 'jpg|jpeg|gif|png' ) == 0 )
      return null;

    if( !is_numeric( $iQuality ) )
      $iQuality = $this->iQuality;

    $sImgExt = $this->throwExtOfFile( $sImgSource );

    if( $sImgOutput == false )
      $sImgOutput = basename( $sImgSource, '.'.$sImgExt ) . $this->iThumbAdd . '.' . $sImgExt;

    $sImgOutput = $this->changeFileName( $sImgOutput );

    $sImgBackup = $sImgDestDir.$sImgOutput . "_backup.jpg";
    copy( $sImgSource, $sImgBackup );
    $aImgProperties = GetImageSize( $sImgBackup );

    if ( !$aImgProperties[2] == 2 ) {
      return null;
    }
    else {
      switch( $sImgExt ) {
        case 'jpg':
          $mImgCreate = ImageCreateFromJPEG( $sImgBackup );
            break;
        case 'jpeg':
          $mImgCreate = ImageCreateFromJPEG( $sImgBackup );
            break;
        case 'png':
          $mImgCreate = ImageCreateFromPNG( $sImgBackup );
            break;
        case 'gif':
          $mImgCreate = ImageCreateFromGIF( $sImgBackup );
      }

      $iImgCreateX = ImageSX( $mImgCreate );
      $iImgCreateY = ImageSY( $mImgCreate );

      $iScaleX = $this->iThumbX / ( $iImgCreateX );
      $this->iThumbY = $iImgCreateY * $iScaleX;

      $iRatio  = $this->iThumbX / $this->iThumbY;

      if( $iRatio < $this->fRatio ) {
        $this->iThumbY  = $this->iThumbX;
        $iScaleY        = $this->iThumbY / ( $iImgCreateY );
        $this->iThumbX  = $iImgCreateX * $iScaleY;
      }

      $this->iThumbX  = ( int )( $this->iThumbX );
      $this->iThumbY  = ( int )( $this->iThumbY );
      $mImgDest       = imagecreatetruecolor( $this->iThumbX, $this->iThumbY );
      unlink( $sImgBackup );

      if( function_exists( 'imagecopyresampled' ) )
        $sCreateFunction = 'imagecopyresampled';
      else
        $sCreateFunction = 'imagecopyresized';

      if( !$sCreateFunction( $mImgDest, $mImgCreate, 0, 0, 0, 0, $this->iThumbX + 1, $this->iThumbY + 1, $iImgCreateX, $iImgCreateY ) ) {
        imagedestroy( $mImgCreate );
        imagedestroy( $mImgDest );
        return null;
      }
      else {
        imagedestroy( $mImgCreate );
        if( !is_file( $sImgDestDir.$sImgOutput ) ) {
          touch( $sImgDestDir.$sImgOutput );
          chmod( $sImgDestDir.$sImgOutput, 0666 );
        }
        switch( $sImgExt ) {
          case 'jpg':
            $Image = ImageJPEG( $mImgDest, $sImgDestDir.$sImgOutput, $iQuality );
            break;
          case 'jpeg':
            $Image = ImageJPEG( $mImgDest, $sImgDestDir.$sImgOutput, $iQuality );
            break;
          case 'png':
            $Image = ImagePNG( $mImgDest, $sImgDestDir.$sImgOutput );
            break;
          case 'gif':
            if( function_exists( "imagegif" ) )
              $Image = ImageGIF( $mImgDest, $sImgDestDir.$sImgOutput );
            else{
              if( $iQuality > 0 )
                $iQuality = floor( ( $iQuality - 1 ) / 10 );
              $Image = ImagePNG( $mImgDest, $sImgDestDir.$sImgOutput, $iQuality );
            }
        }
        if ( $Image  ) {
          imagedestroy( $mImgDest );
          return $sImgOutput;
        }
        imagedestroy( $mImgDest );
      }
    return null;
    }

  } // end function createThumb

};
?>