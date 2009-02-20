<?php
/**
* FlatFiles
* @access   public 
* @version  0.3.0
* @require  FileJobs
* @require  Trash
*/
class FlatFiles extends FileJobs
{

  var $aFieldsNames;
  var $aFieldsSort;
  var $aFieldsDefault;
  var $aFunctionsNames;
  var $aFilesDefNames;
  var $rFile;
  var $sFileName;
  var $mData;
	var $sSeperator   =	'$';
	var $sBreak       = "\n";
  var $iExp         = 30;
  var $bLock        = null;

  function &getInstance( ){  
    static $oInstance = null;
    if( !isset( $oInstance ) ){  
      $oInstance = new FlatFiles( );  
    }  
    return $oInstance;  
  } // end function getInstance

  /**
  * Add file lock
  * @return void
  */
  function setLock( ){
    $this->bLock = true;
  } // end function setRow

  /**
  * Set explode limit
  * @return void
  * @param array $iExp
  */
  function setExp( $iExp ){
    $this->iExp = $iExp;
  } // end function setExp

  /**
  * Set file name
  * @return void
  * @param string $sFileName
  */
  function setFileName( $sFileName ){
    $this->sFileName = $sFileName;
  } // end function setFileName

	/**
  * Open file
  * @return int
  * @param string $sAttribute
  */
	function openFile( $sAttribute ){
		$this->rFile = fopen( $this->sFileName, $sAttribute );
    if( isset( $this->bLock ) )
      flock( $this->rFile, LOCK_EX );
	} // end function openFile
	
	/**
  * Close file
  * @return void
  */
	function closeFile( ){
    if( isset( $this->bLock ) ){
      flock( $this->rFile, LOCK_UN );
      $this->bLock = null;
    }
		fclose( $this->rFile );
	} // end function closeFile

  /**
  * Return function name
  * @return string
  * @param string $sFileName
  */
  function throwFunctionName( $sFileName ){
    if( isset( $this->aFunctionsNames[$sFileName] ) )
      return $this->aFunctionsNames[$sFileName];
    else
      return basename( $this->throwNameOfFile( $sFileName ) );
  } // end function throwFunctionName

  /**
  * Return file definition content
  * @return array
  * @param string $sFileName
  */
  function getDefFileContent( $sFileName ){
    $this->setFileDefVariables( $sFileName );
    return Array( 'aFieldsNames' => $this->aFieldsNames[$sFileName], 'aFieldsSort' => $this->aFieldsSort[$sFileName], 'aFieldsDefault' => $this->aFieldsDefault[$sFileName], 'sFunctionName' => $this->aFunctionsNames[$sFileName] );
  } // end function getDefFileContent

  /**
  * Set variables from def file
  * @return void
  * @param string $sFileName
  */
  function setFileDefVariables( $sFileName ){
    if( !isset( $this->aFilesDefNames[$sFileName] ) ){
      $aExp = $this->throwNameExtOfFile( $sFileName );
      $this->aFilesDefNames[$sFileName] = $aExp[0].'.def'.'.'.$aExp[1];
      $this->aFunctionsNames[$sFileName] = $this->throwFunctionName( $sFileName );

      if( is_file( $this->aFilesDefNames[$sFileName] ) ){
        include $this->aFilesDefNames[$sFileName];

        $this->aFieldsNames[$sFileName] = $aFieldsNames;

        if( isset( $aFieldsSort ) )
          $this->aFieldsSort[$sFileName] = $aFieldsSort;
        else
          $this->aFieldsSort[$sFileName] = null;
        
        if( isset( $aFieldsDefault ) )
          $this->aFieldsDefault[$sFileName] = $aFieldsDefault;
        else
          $this->aFieldsDefault[$sFileName] = null;
      }
      else{
        echo 'ERROR - NO FILE <b>'.$this->aFilesDefNames[$sFileName].'</b><br />';
      }
    }
  } // end function setFileDefVariables

  /**
  * Save data to file
  * @return bool
  * @param string $sFileName
  * @param array  $aData
  * @param string $sId
  * @param string $sSort
  */
  function save( $sFileName, $aData, $sId = null, $sSort = null ){
    $this->setFileName( $sFileName );
    $this->setFileDefVariables( $this->sFileName );

    if( is_file( $this->sFileName ) && is_file( $this->aFilesDefNames[$this->sFileName] ) && is_array( $aData ) ){
      
      $sFunctionName = $this->aFunctionsNames[$this->sFileName];

      foreach( $this->aFieldsNames[$this->sFileName] as $sIndex => $iIndex ){

        if( isset( $aData[$sIndex] ) ){
          if( $sIndex[0] == 'i' ) // if index starts from i letter, then value will have type int
            $aData[$sIndex] = (int) $aData[$sIndex];
          $aFields[$iIndex] = $aData[$sIndex];

          if( isset( $sId ) && $sIndex == $sId ){
            $iId = $iIndex;
          }          
        }
        else{
          if( isset( $this->aFieldsDefault[$this->sFileName] ) && !empty( $this->aFieldsDefault[$this->sFileName][$iIndex] ) ){
            $aFields[$iIndex] = $this->aFieldsDefault[$this->sFileName][$iIndex];
          }
          else
            $aFields[$iIndex] = null;
        }
      } // end foreach
      
      if( isset( $this->aFieldsSort[$this->sFileName] ) && isset( $aFields ) && !empty( $sSort ) && $sSort != 'end' && $sSort != 'top' && function_exists( $sSort ) ){ // if sort variable

        $aData  = null;
        $aFile  = file( $this->sFileName );
        $iCount = count( $aFile );
        
        if( $this->throwExtOfFile( $this->sFileName ) == 'php' ){
          $iStart = 1;
          $sSaveBefore = $aFile[0];
        }
        else{
          $iStart = 0;
          $sSaveBefore = null;
        }

        if( $iCount == $iStart ){
          $aFile[] = $aFields;
          $iCount++;
        }

        for( $i = $iStart; $i < $iCount; $i++ ){
          if( is_array( $aFile[$i] ) ){
            $aExp   = $aFile[$i];
            $bFound = true;
          }
          else
            $aExp = explode( $this->sSeperator, $aFile[$i] );

          if( isset( $iId ) && $aFields[$iId] == $aExp[$iId] ){
            $aExp = $aFields;
            $bFound = true;
          }

          if( !isset( $bFound ) && $i == $iCount - 1 ){
            $aFile[] = $aFields;
            $iCount++;
            $bFound = true;
          }

          // $iCount - all indexes count from 1st line from .def file
          foreach( $this->aFieldsSort[$this->sFileName] as $iIndex => $sIndex ){
            // new array created to sort by sort/rsort function
            $iIndex = $this->aFieldsNames[$this->sFileName][$sIndex];
            if( !isset( $aExp[$iIndex] ) )
              $aExp[$iIndex] = null;

            $aData[$i][] = $aExp[$iIndex];          
          } // end foreach
        } // end for

        // sorting array
        $sSort( $aData );

        foreach( $this->aFieldsSort[$this->sFileName] as $iIndex => $sIndex ){
          $aDataMap[$this->aFieldsNames[$this->sFileName][$sIndex]] = $iIndex;
        } // end for

        // save to file
        $this->openFile( 'w' );
        $iCount = count( $aData );
        fwrite( $this->rFile, $sSaveBefore );
        for( $i = 0; $i < $iCount; $i++ ){
          foreach( $this->aFieldsNames[$this->sFileName] as $sIndex => $iIndex ){
            $aSave[] = $aData[$i][$aDataMap[$iIndex]];
          }
          fwrite( $this->rFile, implode( $this->sSeperator, $aSave ).$this->sSeperator.$this->sBreak );
          $aSave = null;
        } // end for
        $this->closeFile( );
      }
      else{ // if sort is not defined
        if( isset( $iId ) ){ // if id is defined
          $aFile  = file( $this->sFileName );
          $iCount = count( $aFile );
          
          if( $this->throwExtOfFile( $this->sFileName ) == 'php' ){
            $iStart = 1;
            $sSaveBefore = $aFile[0];
          }
          else{
            $iStart = 0;
            $sSaveBefore = null;
          }

          $this->openFile( 'w' );
          fwrite( $this->rFile, $sSaveBefore );

          for( $i = $iStart; $i < $iCount; $i++ ){
            $aExp = explode( $this->sSeperator, $aFile[$i] );

            if( $aFields[$iId] == $aExp[$iId] ){
              fwrite( $this->rFile, rtrim( implode( $this->sSeperator, $aFields ) ).$this->sSeperator.$this->sBreak );
            }
            else{
              fwrite( $this->rFile, $aFile[$i] );
            }
          } // end for
          $this->closeFile( );
        }
        else{
          if( $sSort == 'top' ){ // save to top of file
            $aFile  = file( $this->sFileName );
            $iCount = count( $aFile );
            
            if( $this->throwExtOfFile( $this->sFileName ) == 'php' ){
              $iStart = 1;
              $sSaveBefore = $aFile[0];
            }
            else{
              $iStart = 0;
              $sSaveBefore = null;
            }

            $this->openFile( 'w' );
            fwrite( $this->rFile, $sSaveBefore );
            fwrite( $this->rFile, rtrim( implode( $this->sSeperator, $aFields ) ).$this->sSeperator.$this->sBreak );

            for( $i = $iStart; $i < $iCount; $i++ ){
              fwrite( $this->rFile, $aFile[$i] );
            } // end for
            $this->closeFile( );
          }
          else{ // save to end of file
            $this->openFile( 'a' );
            fwrite( $this->rFile, implode( $this->sSeperator, $aFields ).$this->sSeperator.$this->sBreak );
            $this->closeFile( );
          }
        }
      }
    }
    else
      return false;
  } // end function save

 /**
  * Return position by name of index
  * @return string
  * @param string $sFileName
  * @param string $sIndex
  */
  function getPositionByIndex( $sFileName, $sIndex ){
    $this->setFileDefVariables( $sFileName );
    if( isset( $this->aFieldsNames[$sFileName][$sIndex] ) )
      return $this->aFieldsNames[$sFileName][$sIndex];
  } // end function getPositionByIndex

  /**
  * Return last id of file
  * @return int
  * @param string $sFile
  * @param string $sIndex
  */
  function throwLastId( $sFile, $sIndex ){
    $this->setFileName( $sFile );
    $iPosition = $this->getPositionByIndex( $this->sFileName, $sIndex );

    if( is_file( $this->sFileName ) && is_numeric( $iPosition ) ){
      $aFile  = file( $this->sFileName );
      $iCount = count( $aFile );
      $iMax   = 0;
      $iStart = $this->throwExtOfFile( $this->sFileName ) == 'php' ? 1 : 0;
      for( $i = $iStart; $i < $iCount; $i++ ){
        $aExp = explode( $this->sSeperator, $aFile[$i], $this->iExp );
        if( $aExp[$iPosition] > $iMax )
          $iMax = $aExp[$iPosition];
      } // end for
      return $iMax;
    }
    else
      return null;
  } // end function throwLastId

  /**
  * Return file in array with 2 defined indexes
  * @return array
  * @param string $sFile
  * @param string $sIndexFirst - index array'a
  * @param string $sIndexSecond - index wartosci array'a
  */
  function throwFileArraySmall( $sFile, $sIndexFirst, $sIndexSecond ){
    $this->setFileName( $sFile );
    $iPosition1 = $this->getPositionByIndex( $this->sFileName, $sIndexFirst );
    $iPosition2 = $this->getPositionByIndex( $this->sFileName, $sIndexSecond );
    if( is_numeric( $iPosition1 ) && is_numeric( $iPosition2 ) ){
      $aFile  = file( $this->sFileName );
      $iCount = count( $aFile );
      $iStart = $this->throwExtOfFile( $this->sFileName ) == 'php' ? 1 : 0;
      for( $i = $iStart; $i < $iCount; $i++ ){
        $aExp = explode( $this->sSeperator, $aFile[$i], $this->iExp );
        $aData[$aExp[$iPosition1]] = $aExp[$iPosition2];
      } // end for
      if( isset( $aData ) )
        return $aData;
    }
    else
      return null;
  } // end function throwFileArraySmall

  /**
  * Return file content
  * @return array
  * @param string $sFile
  * @param mixed  $mValue
  * @param string $sIndex
  */
  function throwData( $sFile, $mValue, $sIndex ){
    $this->setFileName( $sFile );
    $iPosition  = $this->getPositionByIndex( $this->sFileName, $sIndex );
    $sFunction  = $this->throwFunctionName( $this->sFileName );
    if( is_numeric( $iPosition ) && function_exists( $sFunction ) ){
      $aFile  = file( $this->sFileName );
      $iCount = count( $aFile );
      $iStart = $this->throwExtOfFile( $this->sFileName ) == 'php' ? 1 : 0;
      for( $i = $iStart; $i < $iCount; $i++ ){
        $aExp = explode( $this->sSeperator, $aFile[$i], $this->iExp );
        if( $mValue == $aExp[$iPosition] )
          return $sFunction( $aExp );
      } // end for
    }
    else{
      echo 'ERROR - NO FUNCTION <i>'.$sFunction.'</i> OR INDEX <i>'.$iPosition.'</i> IN file <b>'.$this->sFileName.'</b><br />';
      return null;
    }
  } // end function throwData

  /**
  * Return array with all files
  * @return array
  * @param array  $aFiles
  * @param mixed  $mValues
  * @param mixed  $mIndexes
  */
  function throwDataFromFiles( $aFiles, $mValues, $mIndexes ){
    if( is_array( $aFiles ) ){
      $iCount = count( $aFiles );
      for( $i = 0; $i < $iCount; $i++ ){
        $sIndex = is_array( $mIndexes ) ? $mIndexes[$i] : $mIndexes;
        $mValue = is_array( $mValues ) ? $mValues[$i] : $mValues;

        $aData = $this->throwData( $aFiles[$i], $mValue, $sIndex );

        if( isset( $aData ) ){
          if( isset( $aReturn ) )
            $aReturn = array_merge( $aReturn, $aData );
          else
            $aReturn = $aData;
        }
      } // end for

      if( isset( $aReturn ) )
        return $aReturn;
    }
  } // end function throwDataFromFiles

  /**
  * Return file data in HTML select
  * @return string
  * @param string $sFile
  * @param int    $iId
  * @param string $sIndexVerify
  * @param string $sIndexValue
  * @param mixed  $mIndexName
  * @param mixed  $mSeparators
  */
  function throwFileSelect( $sFile, $iId, $sIndexVerify, $sIndexValue, $mIndexName, $mSeparators = null ){
    $this->setFileName( $sFile );
    $iPosition1 = $this->getPositionByIndex( $this->sFileName, $sIndexVerify );
    $iPosition2 = $this->getPositionByIndex( $this->sFileName, $sIndexValue );
    if( is_array( $mIndexName ) ){
      foreach( $mIndexName as $iKey => $sIndexName ){
        if( !isset( $mSeparators[$iKey] ) )
          $mSeparators[$iKey] = ' ';
        $aPostions3[$iKey] = $this->getPositionByIndex( $this->sFileName, $sIndexName );
      } // end foreach
    }
    else{
      $iPosition3 = $this->getPositionByIndex( $this->sFileName, $mIndexName );
    }

    if( is_numeric( $iPosition1 ) && is_numeric( $iPosition2 ) && ( ( isset( $iPosition3 ) && is_numeric( $iPosition3 ) ) || ( isset( $aPostions3 ) && is_array( $aPostions3 ) ) ) ){
      $aFile  = file( $this->sFileName );
      $iCount = count( $aFile );
      $iStart = $this->throwExtOfFile( $this->sFileName ) == 'php' ? 1 : 0;
      $sOption= null;

      for( $i = $iStart; $i < $iCount; $i++ ){
        $aExp = explode( $this->sSeperator, $aFile[$i], $this->iExp );

        if( isset( $aPostions3 ) ){
          $sName = null;
          foreach( $aPostions3 as $iKey => $iPosition3 ){
            if( isset( $aExp[$iPosition3] ) ){
              $sName .= $aExp[$iPosition3].$mSeparators[$iKey];
            }
          } // end foreach
        }
        else{
          $sName = $aExp[$iPosition3];
        }

        $sSelected = ( isset( $iId ) && $aExp[$iPosition1] == $iId ) ? ' selected="selected"' : null;
        $sOption .= '<option value="'.$aExp[$iPosition2].'"'.$sSelected.'>'.$sName.'</option>';
      } // end for

      return $sOption;
    }
  } // end function throwFileSelect

  /**
  * Delete data from file
  * @return bool
  * @param string $sFile
  * @param mixed  $mValue
  * @param string $sIndex
  */
  function deleteInFile( $sFile, $mValue, $sIndex ){

  	$this->setFileName( $sFile );
    $iPosition = $this->getPositionByIndex( $this->sFileName, $sIndex );

    if( is_file( $this->sFileName ) && is_numeric( $iPosition ) ){

      $iStart = $this->throwExtOfFile( $this->sFileName ) == 'php' ? 0 : -1;

      $bFound = null;
      $aFile  = file( $this->sFileName );
      $iCount = count( $aFile );
      $this->openFile( 'w' );
      for( $i = 0; $i < $iCount; $i++ ){
        if( $i > $iStart ){
          $aExp = explode( '$', $aFile[$i], $this->iExp );
          if( is_array( $mValue ) ){
            if( isset( $mValue[$aExp[$iPosition]] ) ){
              $aFile[$i] = null;
              $bFound = true;            
            }
          }
          else{
            if( $aExp[$iPosition] == $mValue ){
              $aFile[$i] = null;
              $bFound = true;
            }
          }
        }
        fwrite( $this->rFile, $aFile[$i] );
      } // end for
      $this->closeFile( );		
      return $bFound;
    }
	} // end function deleteInFile

  /**
  * Return file array
  * @return array
  * @param string $sFile
  * @param string $sFunction
  * @param mixed  $mFunctionParam
  * @param int    $iPage
  * @param int    $iMax
  * @param string $sSort
  * @param bool   $bSortNumeric
  */
  function throwFileArray( $sFile, $sFunction = null, $mFunctionParam = null, $iPage = null, $iMax = null, $sSort = null, $bSortNumeric = null ){
	  $this->setFileName( $sFile );

    $sFunctionList = $this->throwFunctionName( $this->sFileName );

    $aFile  = file( $this->sFileName );
    $iStart = $this->throwExtOfFile( $this->sFileName ) == 'php' ? 1 : 0;
    $iCount = count( $aFile );

    if( !isset( $iMax ) )
      $iMax = $iCount - $iStart;
    if( !isset( $iPage ) )
      $iPage = 1;

    $iFindPage  = 0;
    $iFindAll   = 0;

    if( isset( $sSort ) ){
      
      if( $iStart == 1 ){
        unset( $aFile[0] );
        $iStart = 0;
        $iCount--;
      }

      if( isset( $bSortNumeric ) )
        $sSort( $aFile, SORT_NUMERIC );
      else
        $sSort( $aFile );
    }

    for( $i = $iStart; $i < $iCount; $i++ ){
      $aList = $sFunctionList( explode( '$', $aFile[$i], $this->iExp ) );
      
      $bReturn = isset( $sFunction ) ? $sFunction( $aList, $mFunctionParam ) : true;

      if( isset( $bReturn ) ){
        $iFindPage++;
        $iFindAll++;
        
        if( $iFindPage == 1 )
          $aPageStart[] = $i;

        if( isset( $aPageStart[$iPage - 1] ) && !isset( $aPageEnd[$iPage - 1] ) ){
          $aData[] = $aList;
        }

        if( $iFindPage == $iMax ){
          $aPageEnd[] = $i;
          $iFindPage  = 0;
        }
      }
    } // end for

    if( isset( $aData ) ){
      $aData[0]['iFindAll'] = $iFindAll;
      return $aData;
    }
    else
      return null;
  } // end function throwFileArray

  /**
  * Cache files to variables
  * @return void
  * @param mixed $mFiles
  */
  function cacheFilesIndexes( $mFiles ){
    if( is_array( $mFiles ) ){
      foreach( $mFiles as $iKey => $sFile ){
        if( is_file( $sFile ) )
          $this->setFileDefVariables( $sFile );
      }
    }
    else{
      if( is_file( $mFiles ) )
        $this->setFileDefVariables( $mFiles );
    }
  } // end function cacheFilesIndexes
};
?>