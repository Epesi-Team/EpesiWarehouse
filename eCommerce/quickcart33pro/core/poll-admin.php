<?php

/**
* Return list of questions for poll
* @return array
* @param int $iPoll
*/
function dbThrowPollAnswersId( $iPoll ){
  $aData = dbThrowPollAnswers( $iPoll );
  $iCount = count( $aData );

  $aReturn = Array( );
  for( $i = 0; $i < $iCount; $i++ ){
    $aReturn[$aData[$i]['iAnswer']] = true;
  } // end for
  return $aReturn;
} // end function dbThrowPollAnswersId

/**
* Saves poll data
* @param array $aForm
*/
function savePoll( $aForm ){
  $oFF    =& FlatFiles::getInstance( );
  $aForm  = changeMassTxt( $aForm, '' );
  if( !isset( $aForm['iStatus'] ) )
    $aForm['iStatus'] = 0;

  if( isset( $aForm['iPoll'] ) && is_numeric( $aForm['iPoll'] ) ){
    $sParam = 'iPoll';
    $aForm['sCookieName'] = throwPollCookieName( $aForm['iPoll'] );
  }
  else{
    $sParam = null;
    $aForm['iPoll'] = $oFF->throwLastId( DB_POLL_QUESTIONS, 'iPoll' ) + 1;
    $aForm['sCookieName'] = generateCookieName( );
  }

  $oFF->save( DB_POLL_QUESTIONS, $aForm, $sParam );
  
  $aAnswersOld =  dbThrowPollAnswersCount( );
  $aAnswers =     changeMassTxt( $aForm['aAnswers'] );
  if( isset( $aForm['aAnswersId'] ) )
    $aAnswersId = $aForm['aAnswersId'];
  else
    $aAnswersId = null;

  $iCount = count( $aAnswers );
  for( $i = 0; $i < $iCount; $i++ ){
    if( isset( $aAnswers[$i] ) && !empty( $aAnswers[$i] ) ){
      if( !isset( $aAnswersId[$i] ) || !is_numeric( $aAnswersId[$i] ) ){
        $aAnswersId[$i] = $oFF->throwLastId( DB_POLL_ANSWERS, 'iAnswer' ) + 1;
        $aAnswersOld[$aAnswersId[$i]] = 0;
        $sParam = null;
      }
      else
        $sParam = 'iAnswer';

      $oFF->save( DB_POLL_ANSWERS, Array( 'iAnswer' => $aAnswersId[$i], 'iPoll' => $aForm['iPoll'], 'sAnswer' => $aAnswers[$i] ), $sParam );
      $oFF->save( DB_POLL_VOTES, Array( 'iAnswer' => $aAnswersId[$i], 'iVotes' => $aAnswersOld[$aAnswersId[$i]] ), $sParam );
    }
    elseif( isset( $aAnswersId[$i] ) && is_numeric( $aAnswersId[$i] ) ){
      $oFF->deleteInFile( DB_POLL_ANSWERS, $aAnswersId[$i], 'iAnswer' );
      $oFF->deleteInFile( DB_POLL_VOTES, $aAnswersId[$i], 'iAnswer' );
    }
    
  } // end for
  
  return $aForm['iPoll'];
} // end function savePoll

/**
* Return answers count
* @return array
*/
function dbThrowPollAnswersCount( ){
  $oFF    =& FlatFiles::getInstance( );
  return $oFF->throwFileArraySmall( DB_POLL_VOTES, 'iAnswer', 'iVotes' );
} // end function dbThrowPollAnswersCount

/**
* Resets poll answers
* @param int $iPoll
*/
function resetPollAnswers( $iPoll ){
  $aAnswers = dbThrowPollAnswersId( $iPoll );
  $sFile  = DB_POLL_VOTES;
  $aFile  = file( $sFile );
  $rFile  = fopen( $sFile, 'w' );
  $iCount = count( $aFile );

  for( $i = 0; $i < $iCount; $i++ ){
    if( $i > 0 ){
      $aFile[$i]  = trim( $aFile[$i] );
      $aExp       = explode( '$', $aFile[$i] );
      
      if( isset( $aAnswers[$aExp[0]] ) ){
        $aExp[1] = 0;
        $aFile[$i] = implode( '$', $aExp );
      }
      $aFile[$i]  .= "\n";
    }
    fwrite( $rFile, $aFile[$i] );
  } // end for
  fclose( $rFile );
  changePollCookieName( $iPoll );
} // end function resetPollAnswers

/**
* Change poll cookie name
* @param int $iPoll
*/
function changePollCookieName( $iPoll ){
  $oFF =& FlatFiles::getInstance( );
  $aData = throwPollQuestions( $iPoll );
  if( isset( $aData ) ){
    $aData['sCookieName'] = generateCookieName( );
    $oFF->save( DB_POLL_QUESTIONS, Array( 'iPoll' => $aData['iPoll'], 'sQuestions' => $aData['sQuestions'], 'sCookieName' => $aData['sCookieName'], 'iStatus' => $aData['iStatus'] ), 'iPoll' );
  }
} // end function changePollCookieName

/**
* Generates new cookie name
* @return int
*/
function generateCookieName( ){
  return rand( 1, 9999 );
} // end function generateCookieName

/**
* Show list of polls
* @return string
* @param string $sFile
*/
function listPolls( $sFile = 'poll.tpl' ){
  if( !is_file( DB_POLL_QUESTIONS ) )
    return null;

  $oFF    =& FlatFiles::getInstance( );
  $oTpl   =& TplParser::getInstance( );
  $aFile  = $oFF->throwFileArray( DB_POLL_QUESTIONS );

  if( isset( $aFile ) && is_array( $aFile ) ){
    $iCount   = count( $aFile );
    $content  = null;

    for( $i = 0; $i < $iCount; $i++ ){
      $aData = $aFile[$i];
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
      $oTpl->setVariables( 'aData', $aData );
      $content .= $oTpl->tbHtml( $sFile, 'LIST' );
    } // end for

    return $oTpl->tbHtml( $sFile, 'HEAD' ).$content.$oTpl->tbHtml( $sFile, 'FOOT' );
  }
} // end function listPolls

/**
* Deletes poll
* @return void
* @param int $iPoll
*/
function delPoll( $iPoll ){
  $oFF =& FlatFiles::getInstance( );
  $oFF->deleteInFile( DB_POLL_QUESTIONS, $iPoll, 'iPoll' );
  $aData =  dbThrowPollAnswers( $iPoll );

  $iCount = count( $aData );
  $aDeleteAnswers = null;
  for( $i = 0; $i < $iCount; $i++ ){
    $aDeleteAnswers[$aData[$i]['iAnswer']] = true;
  } // end for
  $oFF->deleteInFile( DB_POLL_ANSWERS, $aDeleteAnswers, 'iAnswer' );
  $oFF->deleteInFile( DB_POLL_VOTES, $aDeleteAnswers, 'iAnswer' );
} // end function delPoll

?>