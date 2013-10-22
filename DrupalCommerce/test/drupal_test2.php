<?php
class DrupalREST {
    var $url;
    var $username;
    var $password;
    var $session;
    var $endpoint;
    var $debug;

    function __construct($url,$endpoint, $username, $password, $debug)
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
        //TODO: Check for trailing slash and fix if needed
        $this->endpoint = $endpoint;
        $this->debug = $debug;
    }

    function login()
    {
        $ch = curl_init(rtrim($this->url,'/').'/'.trim($this->endpoint,'/') . '/user/login.json');
        $post_data = array(
        'username' => $this->username,
        'password' => $this->password,
        );
        $post = json_encode($post_data);//http_build_query($post_data, '', '&');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array (
        "Accept: application/json",
        "Content-type: application/json"
        ));
        $content = curl_exec($ch);
        $response = json_decode($content);
        //Save Session information to be sent as cookie with future calls
        $this->session = $response->session_name . '=' . $response->sessid;
        // GET CSRF Token
        curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => rtrim($this->url,'/').'/services/session/token',
        ));
        curl_setopt($ch, CURLOPT_COOKIE, "$this->session"); 

        $ret = new stdClass;

        $ret->response = curl_exec($ch);
        $ret->error    = curl_error($ch);
        $ret->info     = curl_getinfo($ch);
        
        $this->csrf_token = $ret->response;
    }

    // Retrieve a node from a node id
    function call($op,$args=array())
    {
        $ch = curl_init(rtrim($this->url,'/').'/'.trim($this->endpoint,'/')  . '/' . $op );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array (
        "Accept: application/json",
        "Content-type: application/json",
        "Cookie: $this->session",
        'X-CSRF-Token: ' .$this->csrf_token
        ));

        $post = json_encode($args);//http_build_query($post_data, '', '&');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        
        $result = $this->_handleResponse($ch);

        curl_close($ch);

        return $result;
    }

    // Private Helper Functions
    private function _handleResponse($ch)
    {
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        //break apart header & body
        $header = substr($response, 0, $info['header_size']);
        $body = substr($response, $info['header_size']);

        $result = new stdClass();

        if ($info['http_code'] != '200')
        {
            $header_arrray = explode("\n",$header);
            $result->ErrorCode = $info['http_code'];
            $result->ErrorText = $header_arrray['0'];
            } else {
            $result->ErrorCode = NULL;
            $decodedBody= json_decode($body);
            $result = (object) array_merge((array) $result, (array) $decodedBody );
        }

        if ($this->debug)
        {
            $result->header = $header;
            $result->body = $body;
        }

        return $result;
    }
}

$r = new DrupalREST('http://drupal.crazyit.pl/','epesi','admin','admin',false);
$r->login();
  $rr2 = $r->call('taxonomy_term',60);
  print_r($rr2);
die();
$ret = $r->call('taxonomy_vocabulary/getTree',array('vid'=>5));
//print_r($ret);
foreach($ret as $rr) {
  if(!isset($rr->tid)) continue;
  $rr2 = $r->call('entity_taxonomy_term',array('tid'=>$rr->tid));
  print_r($rr2);
}
