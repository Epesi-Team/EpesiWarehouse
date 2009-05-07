<?php
/**
* TplParser - class parse all HTML files and display from
* this files PHP variables
* @access public
* @version 1.2.3
*/
class TplParser
{

	var $content;
	var $sFile;
  var $sFileAlt = null;
	var $sBlock;
	var $startBlock = '<!-- BEGIN ';
	var $endBlock = '<!-- END ';
  var $endBlockLine = ' -->';
  var $aFilesContent;	
  var $sBlockContent;
  var $bEmbedPHP = null;
  var $sDir;
  var $bTrim = true;
  var $aVariables;

  function &getInstance( $sDir = null, $bEmbedPHP = null ){  
    static $oInstance = null;
    if( !isset( $oInstance ) ){  
      $oInstance = new TplParser( $sDir, $bEmbedPHP );  
    }  
    return $oInstance;  
  } // end function getInstance

  /**
  * Constructor
  * @return void
  * @param string $sDir
  * @param bool   $bEmbedPHP
  */
  function TplParser( $sDir, $bEmbedPHP ){
    $this->setEmbedPHP( $bEmbedPHP );
    $this->setDir( $sDir );
  } // end function TplParser
	
  /**
  * Set variables
  * @return void
  * @param string $sName
  * @param mixed  $mValue
  */
  function setVariables( $sName, $mValue ){
    $this->aVariables[$sName] = $mValue;
  } // end function setVariables

  /**
  * Unset variables
  * @return void
  */
  function unsetVariables( ){
    $this->aVariables = null;
  } // end function unsetVariables
  
  /**
  * Display parsed file
  * @return void
  * @param string $sFile - file *.tpl
  * @param bool   $bTrim
  */
	function dHtml( $sFile, $bTrim = true ){
		$this->setFile( $this->sDir.$sFile );
    $this->bTrim = $bTrim;

		$this->display( );
    echo $this->content;
	} // end function dHtml

  /**
  * Return parsed file
  * @return string
  * @param string $sFile - file *.tpl
  * @param bool   $bTrim
  
  */
	function tHtml( $sFile, $bTrim = true ){
		$this->setFile( $this->sDir.$sFile );
    $this->bTrim = $bTrim;

		$this->display( );
		return $this->content;
	} // end function tHtml

  /**
  * Display parsed sBlock from file
  * @return void
  * @param string $sFile - file *.tpl
  * @param string $sBlock
  * @param bool   $bTrim
  */
	function dbHtml( $sFile, $sBlock, $bTrim = true ){
		$this->setFile( $this->sDir.$sFile );
		$this->setBlock( $sBlock );
    $this->bTrim = $bTrim;

		$this->display( true );
    echo $this->content;
	} // end function dbHtml
	
  /**
  * Return parsed sBlock from file
  * @return string
  * @param string $sFile - file *.tpl
  * @param string $sBlock
  * @param bool   $bTrim
  */
	function tbHtml( $sFile, $sBlock, $bTrim = true ){
		$this->setFile( $this->sDir.$sFile );
		$this->setBlock( $sBlock );
    $this->bTrim = $bTrim;

		$this->display( true );
		return $this->content;
	} // end function tbHtml

  /**
  * Function execute functions depend by parameter
  * @return void
  * @param bool $bBlock [optional]
  */
	function display( $bBlock = null ){
		if( $this->checkFile( ) ){
			if( isset( $bBlock ) )
				$this->blockParse( );
			else
				$this->allParse( );
		}
	} // end function display
	
	/**
  * Function check if file exists
  * @return boolean
  */
	function checkFile( ){
		if( is_file( $this->sFile ) ){
	  	return true;
	  }
		else {
      $this->content = null;
      if( isset( $this->sFileAlt ) && is_file( $this->sDir.$this->sFileAlt ) ){
        $this->setFile( $this->sDir.$this->sFileAlt );
        return true;
      }
      else{
        echo 'ERROR - NO TEMPLATE FILE <b>'.$this->sFile.'</b><br />';
        return null;
      }
		}
	} // end function checkFile

  /**
  * Parse content with PHP
  * @return void
  */
  function parsePHP( ){
    extract( $GLOBALS );
    while( $iPosition1 = strpos( $this->content, '<?php' ) ){
      $iPosition2 = strpos( $this->content, '?>' );
      $sPhpCode = substr( $this->content, $iPosition1 + 5, $iPosition2 - $iPosition1 - 5 );
      ob_start( );
      eval( $sPhpCode );
      $this->content = substr( $this->content, 0, $iPosition1 ).ob_get_contents( ).substr( $this->content, $iPosition2 + 2  );
      ob_end_clean( );
    } // end while
  } // end function parsePHP 
	
  /**
  * Function parse $this->content
  * @return boolean
  */
	function parse( ){
    if( isset( $this->bEmbedPHP ) && $this->bEmbedPHP === true && preg_match( '/<?php/', $this->content ) )
      $this->parsePHP( );

    preg_match_all( '/(\$[a-zA-Z_]+[a-zA-Z0-9_]*)(([\[]+[\']*[a-zA-Z0-9_]+[\']*[\]]+)*)/', $this->content, $aResults );
    if( isset( $aResults[1] ) && is_array( $aResults[1] ) ){
      $iCount = count( $aResults[1] );
      for( $i = 0; $i < $iCount; $i++ ){
        $aResults[1][$i] = substr( $aResults[1][$i], 1 );
        if( isset( $this->aVariables[$aResults[1][$i]] ) )
          $$aResults[1][$i] = $this->aVariables[$aResults[1][$i]];
        else
          global $$aResults[1][$i];

        // array
        if( isset( $aResults[2] ) && !empty( $aResults[2][$i] ) ){
          if( preg_match( '/\'/', $aResults[2][$i] ) ){
            $aResults[2][$i] = str_replace( '\'', null, $aResults[2][$i] );
            $sSlash = '\'';
          }
          else
            $sSlash = null;

          preg_match_all( '/[a-zA-Z_\'0-9]+/', $aResults[2][$i], $aResults2 );
          $iCount2 = count( $aResults2[0] );
          if( $iCount2 == 2 ){
            if( isset( ${$aResults[1][$i]}[$aResults2[0][0]][$aResults2[0][1]] ) )
              $aReplace[] = ${$aResults[1][$i]}[$aResults2[0][0]][$aResults2[0][1]];
            else
              $aReplace[] = null;
            $aFind[] = '/\$'.$aResults[1][$i].'\['.$sSlash.$aResults2[0][0].$sSlash.'\]\['.$sSlash.$aResults2[0][1].$sSlash.'\]/';
          }
          else{
            if( isset( ${$aResults[1][$i]}[$aResults2[0][0]] ) )
              $aReplace[] = ${$aResults[1][$i]}[$aResults2[0][0]];
            else
              $aReplace[] = null;
            $aFind[] = '/\$'.$aResults[1][$i].'\['.$sSlash.$aResults2[0][0].$sSlash.'\]/';
          }
        }
        else{
          if( !is_array( $$aResults[1][$i] ) ){
            $aReplace[] = $$aResults[1][$i].'\\1';
            $aFind[] = '/\$'.$aResults[1][$i].'([^a-zA-Z0-9])/';
          }
        }
      } // end for
    }

    if( isset( $aFind ) )
      $this->content = preg_replace( $aFind, $aReplace, $this->content );
    if( isset( $this->bTrim ) )
      $this->content = trim( $this->content );
    return true;
		
	} // end function parse
	
  /**
  * Function return all data from file
  * @return void
  */
	function allParse( ){
    $this->content = $this->getContent( );
		$this->parse( );
	} // end function allParse
	
  /**
  * Get defined sBlock from file
  * @return boolean
  */
	function blockParse( ){
    if( isset( $this->sBlockContent[$this->sFile][$this->sBlock] ) )
      $this->content = $this->sBlockContent[$this->sFile][$this->sBlock];
    else{
      $this->content = $this->getFileBlock( );
      if( isset( $this->content ) ){
        $this->sBlockContent[$this->sFile][$this->sBlock] = $this->content;
      }
    }
    $this->parse( );
	} // end function blockParse

  /**
  * Get file data from file or from variable ($this->aFilesContent)
  * @return array
  * @param bool $bBlock
  */
  function getContent( $bBlock = null ){
    if( isset( $this->aFilesContent[$this->sFile] ) )
      return $this->aFilesContent[$this->sFile];
    else
      return $this->aFilesContent[$this->sFile] = $this->getFile( $this->sFile );
  } // end function getContent

  /**
  * Return file content
  * @return string
  * @param string $sFile
  */
  function getFile( $sFile ){
    $rFile  = fopen( $sFile, 'r' );
    $iSize  = filesize( $sFile );
    $sContent = $iSize > 0 ? fread( $rFile, $iSize ) : null;
    fclose( $rFile );
    return $sContent;
  } // end function getFile

  /**
  * Return sBlock from file
  * @return string
  * @param string $sFile [optional]
  * @param string $sBlock [optional]
  */
  function getFileBlock( $sFile = null, $sBlock = null ){
    if( isset( $sFile ) && isset( $sBlock ) ){
      $this->setFile( $sFile );
      $this->setBlock( $sBlock );
    }

    $sFile = $this->getContent( true );

    $iStart = strpos( $sFile, $this->startBlock.$this->sBlock.$this->endBlockLine );
    $iEnd   = strpos( $sFile, $this->endBlock.$this->sBlock.$this->endBlockLine );

    if( is_int( $iStart ) && is_int( $iEnd ) ){
      $iStart += strlen( $this->startBlock.$this->sBlock.$this->endBlockLine );
      return substr( $sFile, $iStart, $iEnd - $iStart );
    }
    else {
      if( isset( $this->sFileAlt ) && is_file( $this->sDir.$this->sFileAlt ) ){
        $this->setFile( $this->sDir.$this->sFileAlt );
        return $this->getFileBlock( $this->sFile, $sBlock );
      }
      else{
        echo 'No sBlock: <i>'.$this->sBlock.'</i> in file: '.$this->sFile.' <br />';
        return null;
      }
    }
  } // end function getFileBlock

  /**
  * Return file to array
  * @return array
  * @param string $sFile
  */
  function getFileArray( $sFile ){
    return file( $sFile );
  } // end function getFileArray

  /**
  * Return defined $this->sDir variable
  * @return string
  */
  function getDir( ){
    return $this->sDir;
  } // end function getDir

  /**
  * Function define $this->sDir variable
  * @return void
  * @param string $sDir
  */
  function setDir( $sDir ){
    $this->sDir = $sDir;
  } // end function setDir

  /**
  * Function define $this->bEmbedPHP variable
  * @return void
  * @param bool $bEmbed
  */
  function setEmbedPHP( $bEmbed ){
    $this->bEmbedPHP = $bEmbed;
  } // end function setEmbedPHP

  /**
  * Function define $this->sFile variable
  * @return void
  * @param string $sFile
  */
  function setFile( $sFile ){
    $this->sFile = $sFile;
  } // end function setFile

  /**
  * Function define $this->sFileAlt variable
  * @return void
  * @param string $sFileAlt
  */
  function setFileAlt( $sFileAlt ){
    $this->sFileAlt = $sFileAlt;
  } // end function setFileAlt

  /**
  * Function define $this->sBlock variable
  * @return void
  * @param string $sBlock
  */
  function setBlock( $sBlock ){
    $this->sBlock = $sBlock;
  } // end function setBlock

}; // end class TplParser
?>