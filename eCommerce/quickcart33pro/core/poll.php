<?php

/**
* Return list of questions for poll
* @return string
* @param string $sFile
* @param int    $iPoll
* @param bool   $bResult
* @param string $sOption
*/
function throwPollAnswers( $sFile, $iPoll, $bResult = null, $sOption = null, $iWidth = 150 ){
  $oTpl   =& TplParser::getInstance( );
  //{ epesi

  if( isset( $bResult ) )
    $sBlock = 'ANSWERS_RESULT_';
  else
    $sBlock = 'ANSWERS_FORM_';

  $ret = DB::GetAll('SELECT id as iAnswer, f_answer as sAnswer, f_votes as iCountAnswers, f_poll as iPoll FROM premium_ecommerce_poll_answers_data_1 WHERE f_poll=%d',array($iPoll));
  if( isset( $bResult ) ){
    $iSummary = 0;
    foreach( $ret as $r ){
        $iSummary += $r['iCountAnswers'];
    }
  }

  $content = null;
  foreach( $ret as $r ){
    if(!isset($r['iCountAnswers']))
      $r['iCountAnswers'] = 0;
    if( isset( $bResult )){
      if( $r['iCountAnswers'] > 0 && $iSummary > 0 )
        $r['iPercentage'] = round( $r['iCountAnswers'] / $iSummary * 100 );
      else
        $r['iPercentage'] = 0;

      $r['iWidth'] = round( $r['iPercentage'] * $iWidth / 100 );
    }
    $oTpl->setVariables( 'aList', $r );
    $content .= $oTpl->tbHtml( $sFile, $sBlock.'LIST' );
  } // end for
    //} epesi
/*
  if( isset( $iPoll ) ){
    $aData =  dbThrowPollAnswers( $iPoll );
    $aVotes = throwPollVotes( );
  }

  if( isset( $bResult ) )
    $sBlock = 'ANSWERS_RESULT_';
  else
    $sBlock = 'ANSWERS_FORM_';

  if( isset( $aData ) )
    $iCount = count( $aData );
  else
    $iCount = 0;

  if( isset( $bResult ) || $sOption == 'admin' ){
    $iSummary = 0;
    for( $i = 0; $i < $iCount; $i++ ){
      if( isset( $aVotes[$aData[$i]['iAnswer']] ) && is_numeric( $aVotes[$aData[$i]['iAnswer']] ) )
        $iSummary += $aVotes[$aData[$i]['iAnswer']];
    } // end for
  }

  $content = null;
  $iLast = 0;
  for( $i = 0; $i < $iCount; $i++ ){
    $aList = $aData[$i];
    if( isset( $aVotes[$aList['iAnswer']] ) && is_numeric( $aVotes[$aList['iAnswer']] ) )
      $aList['iCountAnswers'] = $aVotes[$aList['iAnswer']];
    else
      $aList['iCountAnswers'] = 0;
    if( isset( $bResult ) || $sOption == 'admin' ){
      if( $aList['iCountAnswers'] > 0 && $iSummary > 0 )
        $aList['iPercentage'] = round( $aList['iCountAnswers'] / $iSummary * 100 );
      else
        $aList['iPercentage'] = 0;

      $aList['iWidth'] = round( $aList['iPercentage'] * $iWidth / 100 );
    }
    $oTpl->setVariables( 'aList', $aList );
    $content .= $oTpl->tbHtml( $sFile, $sBlock.'LIST' );
    $iLast = $i+1;
  } // end for
  
  if( $sOption == 'admin' && $iLast < POLL_MAX_ANSWERS ){
    for( $i = $iLast; $i < POLL_MAX_ANSWERS; $i++ ){
      $content .= $oTpl->tbHtml( $sFile, $sBlock.'NEW' );
    } // end for
  }
  */
  if( isset( $content ) )
    $content = $oTpl->tbHtml( $sFile, $sBlock.'HEAD' ).$content.$oTpl->tbHtml( $sFile, $sBlock.'FOOT' );
  return $content;
} // end function throwPollAnswers

/**
* Return list of votes for poll questions
* @return array
*/
/*
function throwPollVotes( ){
  $oFF    =& FlatFiles::getInstance( );
  return $oFF->throwFileArraySmall( DB_POLL_VOTES, 'iAnswer', 'iVotes' );
} // end function throwPollVotes

*/
/**
* Checks if answer is for selected poll
* @return boolean
* @param array  $aData
* @param int    $iPoll
*/
/*
function checkThrowPollAnswers( $aData, $iPoll ){
  if( $aData['iPoll'] == $iPoll )
    return true;
  else
    return null;
} // end function checkThrowPollAnswers
*/

/**
* Return list of questions for poll
* @return array
* @param int $iPoll
*/
/*
function dbThrowPollAnswers( $iPoll ){
  $oFF    =& FlatFiles::getInstance( );
  return $oFF->throwFileArray( DB_POLL_ANSWERS, 'checkThrowPollAnswers', $iPoll );
} // end function dbThrowPollAnswers
*/

/**
* Returns selected answer data
* @return array
* @param int $iPoll
*/
/*
function throwPollAnswerVotes( $iAnswer ){
  $oFF    =& FlatFiles::getInstance( );
  $aData = $oFF->throwData( DB_POLL_VOTES, $iAnswer, 'iAnswer' );
  if( !is_numeric( $aData['iVotes'] ) )
    $aData['iVotes'] = 0;
  return $aData;
} // end function throwPollAnswerVotese
*/

/**
* Return list of questions for poll
* @return string
* @param string $sFile
*/
function throwPollQuestions( $iPoll = null ){
    //{ epesi
    if( !isset( $iPoll ) )
	return DB::GetRow('SELECT id as iPoll, f_question as sQuestions FROM premium_ecommerce_polls_data_1 WHERE active=1 AND f_publish=1 AND f_language=\''.LANGUAGE.'\' ORDER BY f_position DESC LIMIT 1');
    return DB::GetRow('SELECT id as iPoll, f_question as sQuestions FROM premium_ecommerce_polls_data_1 WHERE active=1 AND f_publish=1 AND f_language=\''.LANGUAGE.'\' AND id=%d',array($iPoll));
    //} epesi
  /*$oFF    =& FlatFiles::getInstance( );
  if( !isset( $iPoll ) )
    $iPoll = $oFF->throwLastId( DB_POLL_QUESTIONS, 'iPoll' );
  return $oFF->throwData( DB_POLL_QUESTIONS, $iPoll, 'iPoll' );*/
} // end function throwPollQuestions


/**
* Returns poll content
* @return string
* @param string $sFile
* @param int $iPoll
* @param string $sOption
*/
function throwPoll( $sFile, $iPoll = null, $sOption = null, $iWidth = 150 ){
  $oTpl   =& TplParser::getInstance( );

  if( isset( $iPoll ) && $iPoll == 0 )
    $aData = throwPollQuestions( null );
  elseif( isset( $iPoll ) )
    $aData = throwPollQuestions( $iPoll );

    //{ epesi
    if( isset( $aData['iPoll'] ) ){
      if( isset( $_COOKIE[LANGUAGE.'_'.POLL_COOKIE_NAME.$aData['iPool']] ) )
        $bResult = true;
      else
        $bResult = null;
      $aData['sAnswers'] = throwPollAnswers( $sFile, $aData['iPoll'], $bResult, $sOption, $iWidth );
      $oTpl->setVariables( 'aData', $aData );
      return $oTpl->tbHtml( $sFile, 'POLL' );
    }
    else
      return $oTpl->tbHtml( $sFile, 'NOT_EXIST' );
    //} epesi

} // end function throwPoll

/**
* Saves user selected answer
* @param int $iAnswer
*/
function dbSavePollAnswer( $iAnswer, $iPoll ){
/*
  $oFF  =& FlatFiles::getInstance( );
  if( !isset( $iPoll ) || $iPoll == 0 )
    $iPoll = $oFF->throwLastId( DB_POLL_QUESTIONS, 'iPoll' );
  $sPollCookieName = throwPollCookieName( $iPoll );
  if( !isset( $_COOKIE[LANGUAGE.'_'.POLL_COOKIE_NAME.$sPollCookieName] ) ){
    $aData = throwPollAnswerVotes( $iAnswer );
    $oFF->save( DB_POLL_VOTES, Array( 'iAnswer' => $aData['iAnswer'], 'iVotes' => $aData['iVotes']+1 ), 'iAnswer' );
    $_COOKIE[LANGUAGE.'_'.POLL_COOKIE_NAME.$sPollCookieName] = $aData['iAnswer'];
    setCookie( LANGUAGE.'_'.POLL_COOKIE_NAME.$sPollCookieName, $aData['iAnswer'], time( ) + 2592000 );
  }
  */
  //{ epesi
  if( !isset( $iPoll ) || $iPoll == 0 )
    $iPoll = DB::GetOne('SELECT id FROM premium_ecommerce_polls_data_1 WHERE active=1 AND f_publish=1 AND f_language=\''.LANGUAGE.'\' ORDER BY f_position DESC LIMIT 1');
  if( !isset( $_COOKIE[LANGUAGE.'_'.POLL_COOKIE_NAME.$iPoll] ) ){
    DB::Execute('UPDATE premium_ecommerce_poll_answers_data_1 SET f_votes=f_votes+1 WHERE id=%d AND f_poll=%d',array($iAnswer,$iPoll));
    $_COOKIE[LANGUAGE.'_'.POLL_COOKIE_NAME.$sPollCookieName] = $iAnswer;
    setCookie( LANGUAGE.'_'.POLL_COOKIE_NAME.$sPollCookieName, $iAnswer, time( ) + 2592000 );
  }
  //} epesi
} // end function dbSavePollAnswer

/**
* Throws poll cookie name
* @param int $iPoll
*/
/*
function throwPollCookieName( $iPoll ){
  $aData = throwPollQuestions( $iPoll );
  if( isset( $aData['sCookieName'] ) && !empty( $aData['sCookieName'] ) )
    return $aData['sCookieName'];
  else
    return null;
} // end function throwPollCookieName
*/

?>