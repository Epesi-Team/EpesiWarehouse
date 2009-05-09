<?php
/**
* Delete comment
* @return array
* @param int    $iComment
* @param string $sFileDb
*/
function deleteComment( $iComment, $sFileDb = null ){
  if( empty( $sFileDb ) )
    $sFileDb = DB_PAGES_COMMENTS;
  if( !is_file( $sFileDb ) )
    return null;
 
 $oFF =& FlatFiles::getInstance( );
 $oFF->deleteInFile( $sFileDb, $iComment, 'iComment' );
} // end function deleteComment

/**
* Delete all coments from selected link
* @return array
* @param int    $iLink
* @param string $sFileDb
*/
function deleteComments( $iLink, $sFileDb = null ){
  if( empty( $sFileDb ) )
    $sFileDb = DB_PAGES_COMMENTS;
  if( !is_file( $sFileDb ) )
    return null;

  $oFF =& FlatFiles::getInstance( );
  $oFF->deleteInFile( $sFileDb, $iLink );
} // end function deleteComments

  /**
  * Save comments
  * @return void
  * @param array  $aForm
  * @param string $sFileDb
  */
  function saveComments( $aForm, $sFileDb = null ){
    if( empty( $sFileDb ) )
      $sFileDb = DB_PAGES_COMMENTS;
    if( !is_file( $sFileDb ) )
      return null;

    $oFF =& FlatFiles::getInstance( );

    $aFile  = $oFF->throwFileArray( $sFileDb );
    $iCount = count( $aFile );
    for( $i = 0; $i < $iCount; $i++ ){
      if( empty( $aFile[$i]['iStatus'] ) )
        $aFile[$i]['iStatus'] = 0;
      if( isset( $aForm['aId'][$aFile[$i]['iComment']] ) ){
        $iStatus = isset( $aForm['aStatus'][$aFile[$i]['iComment']] ) ? 1 : 0;
        if( $iStatus != $aFile[$i]['iStatus'] ){
          $aFile[$i]['iStatus'] = $iStatus;
          $oFF->save( $sFileDb, $aFile[$i], 'iComment' );
        }
      }
    } // end for
  } // end function saveComments
?>