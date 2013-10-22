<?php
/**
* Create a token for non-safe REST calls.
**/
function epesicommerce_get_csrf_header() {
  $curl_get = curl_init();
  curl_setopt_array($curl_get, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'http://drupal.crazyit.pl/services/session/token',
  ));
  $csrf_token = curl_exec($curl_get);
  curl_close($curl_get);
  return 'X-CSRF-Token: ' . $csrf_token;
}

function epesicommerce_request($url,$user_data=array(),$use_token=true) {
  static $token;
  static $session_name;
  static $session_id;
  $request_url = 'http://drupal.crazyit.pl/epesi/'.$url;
  
  $curl = curl_init($request_url);
  $headers = array('Accept: application/json');
  if($use_token) {
    if(!isset($token)) $token = epesicommerce_get_csrf_header();
    $headers[] = $token;
  }
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); // Accept JSON response
  curl_setopt($curl, CURLOPT_POST, TRUE); // Do a regular HTTP POST
  curl_setopt($curl, CURLOPT_POSTFIELDS, $user_data); // Set POST data
  curl_setopt($curl, CURLOPT_HEADER, FALSE);  // Ask to not return Header
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
  if(isset($session_name)) curl_setopt($curl, CURLOPT_COOKIE, $session_name.'='.$session_id);

  $response = curl_exec($curl);
  $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  // Check if login was successful
  if ($http_code == 200) {
    $ret = json_decode($response);
    if(!$ret) die($response);
    if(isset($ret->session_name) && isset($ret->sessid)) {
      $session_name = $ret->session_name;
      $session_id = $ret->sessid;
    }
    return $ret;
  }
  else {
    // Get error msg
    $http_message = curl_error($curl);
    die($http_message);
  }
}

/*
* Server REST - user.login
*/

// User data
$user_data = array(
  'username' => 'test',
  'password' => 'test'
);
$user = epesicommerce_request('user/login',$user_data,false);

$data = array(1,0,99);
$taxonomy = epesicommerce_request('taxonomy_vocabulary/getTree',$data);

die('ok '.print_r($taxonomy,true)."\n");
/*
* Server REST - node.create
*/

// REST Server URL
$request_url = 'http://drupal.crazyit.pl/epesi/node';

// Node data
$node_data = array(
  'title' => 'A node created with services 3.x and REST server',
  'type' => 'page',
  'body[und][0][value]' => '<p>Body</p>',
);
$node_data = http_build_query($node_data);

// Define cookie session
$cookie_session = $logged_user->session_name . '=' . $logged_user->sessid;

// cURL
$curl = curl_init($request_url);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', epesicommerce_get_csrf_header())); // Accept JSON response
curl_setopt($curl, CURLOPT_POST, 1); // Do a regular HTTP POST
curl_setopt($curl, CURLOPT_POSTFIELDS, $node_data); // Set POST data
curl_setopt($curl, CURLOPT_HEADER, FALSE);  // Ask to not return Header
curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session"); // use the previously saved session
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

// Check if login was successful
if ($http_code == 200) {
  // Convert json response as array
  $node = json_decode($response);
}
else {
  // Get error msg
  $http_message = curl_error($curl);
  die($http_message);
}

print_r($node);
?>
