<?php
/**
* FileJobs
* @access   public 
* @version  0.1.9
*/
class FileJobs
{

  var $sFileName;
	var $sChmod = '0777';
	
  /**
  * Add name to variable
  * @return void
  * @param string $sFileName
  */
  function setFileName( $sFileName ){
    $this->sFileName = $sFileName;
  } // end function setFileName
	
  /**
  * Creates file
  * @return bool
  * @param string $sFileName
  */
	function addFile( $sFileName = null ){
		
		if( isset( $sFileName ) )
			$this->setFileName( $sFileName );	

		if( is_file( $this->sFileName ) )
			return false;
		else{
			@touch( $this->sFileName );
			@chmod( $this->sFileName, $this->sChmod );
			if( is_file( $this->sFileName ) )
				return true;
			else
				return false;
		}
	} // end function addFile

  /**
  * Return file name without extension
  * @return string
  * @param string $sName
  */
	function throwNameOfFile( $sName ){
		$aExp = explode( '.', $sName );
    if( isset( $aExp[0] ) && isset( $aExp[1] ) ){
      unset( $aExp[count( $aExp )-1] );
      $sName = implode( '.', $aExp );
      return $sName;
    }
    else
      return $sName;
	} // end function throwNameOfFIle

  /**
  * Return extension from file name
  * @return string
  * @param string $sName
  */
	function throwExtOfFile( $sName ){
		$aExp = explode( '.', $sName );
    if( isset( $aExp[0] ) && isset( $aExp[1] ) ){
      return strtolower( $aExp[count( $aExp )-1] ); 
    }
    else
      return null;
	} // end function throwExtOfFile

  /**
  * Return extension and file name in array
  * @return array
  * @param string $sName
  */
  function throwNameExtOfFile( $sName ){
    return Array( $this->throwNameOfFile( $sName ), $this->throwExtOfFile( $sName ) );
  } // end function throwNameExtOfFile

  /**
  * Return file content
  * @return string
  * @param string $sFile
  */
  function throwFile( $sFile ){
    if( is_file( $sFile ) ){
      $rFile    = fopen( $sFile, 'r' );
      $sContent = fread( $rFile, filesize( $sFile ) );
      fclose( $rFile );
      return $sContent;
    }
    else
      return null;
  } // end function throwFile

  /**
  * Check file extensions
  * For example if file have jpg or jpeg or gif or png extension then function return true
  * @return int
  * @param string $sName
  * @param string $is
  */
	function checkCorrectFile( $sName, $is = 'jpg|jpeg|png|gif' ){
		return preg_match( '/^('.$is.')$/', $this->throwExtOfFile( $sName ) );
	} // end function checkCorrectFile

  /**
  * Change file name from strange name to latin
  * @return string
  * @param string $sFileName
  */
  function changeFileName( $sFileName ){
    return change2Latin( str_replace( Array( '$', '\'', '"', '~', '/', '\\', '?', '#', '%', '+', '*', ':', '|', '<', '>' ), '_', $sFileName ) );
  } // end function changeFileName

  /**
  * If file with set name exists then create uniq name for file
  * @return string
  * @param string $sFileOutName
  * @param string $sOutDir
  */
  function checkIsFile( $sFileOutName, $sOutDir = '' ){
    
    $sFileName =  $this->throwNameOfFile( $sFileOutName );
    $sExt =       $this->throwExtOfFile( $sFileOutName );

    for( $i = 1; is_file( $sOutDir.$sFileOutName ); $i++ )
      $sFileOutName = $sFileName.'['.$i.'].'.$sExt;

    return $sFileOutName;
  } // end function checkIsFile

  /**
  * Upload file on server
  * @return string
  * @param array  $aFiles
  * @param string $sOutDir
  * @param mixed  $sFileOutName
  */
  function uploadFile( $aFiles, $sOutDir = null, $sFileOutName = null ){
    $sUpFileSrc   =   $aFiles['tmp_name'];
    $sUpFileName  =  $this->changeFileName( $aFiles['name'] );

    if( !isset( $sFileOutName ) )
      $sFileOutName = $sUpFileName;

    $sFileOutName = $this->checkIsFile( $sFileOutName, $sOutDir );

    if( move_uploaded_file( $sUpFileSrc, $sOutDir.$sFileOutName ) ){
      chmod( $sOutDir.$sFileOutName, 0777 );
      return $sFileOutName;
    }
    else
      return null; 
  } // end function uploadFile

  /**
  * Delete all files and directories from directory
  * @return void
  * @param string $sDir
  */
  function truncateDir( $sDir ){
    $oDir = dir( $sDir );
    while( false !== ( $sPosition = $oDir->read( ) ) ){
      if( is_file( $sDir.$sPosition ) ){
        unlink( $sDir.$sPosition );
      }
      else{
        if( is_dir( $sDir.$sPosition ) && ( !strstr( $sPosition, '.' ) && !strlen( $sPosition ) < 3 ) ){
          $this->truncateDir( $sDir.$sPosition.'/' );
          rmdir( $sDir.$sPosition );
        }
      }
    }
    $oDir->close( );    
  } // end function truncateDir

};
?>