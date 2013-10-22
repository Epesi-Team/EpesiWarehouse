<?php
$taxonomy_category = 5;
$drupal_url = 'http://drupal.crazyit.pl';
$drupal_user = 'admin';
$drupal_password = 'admin';

require_once("lib/xmlrpc.inc");

$c=new xmlrpc_client(rtrim($drupal_url,'/')."/epesi");
$c->setDebug(0);

$f=new xmlrpcmsg('user.login',
			array(new xmlrpcval($drupal_user),new xmlrpcval($drupal_password))
		);
$r=&$c->send($f);
if($r->faultCode())
{
	print "An error occurred: ";
	print "Code: " . htmlspecialchars($r->faultCode())
		. " Reason: '" . htmlspecialchars($r->faultString()) . "'</pre><br/>";
	die();
}
$cookies = $r->cookies();
foreach($cookies as $cookie_name=>$cookie_value) {
    if(isset($cookie_value['value'])) $c->setCookie($cookie_name,$cookie_value['value']);
}

$csrf_token_get=new xmlrpcmsg('user.token');
$r = &$c->send($csrf_token_get);
if($r->faultCode())
{
	print "An error occurred: ";
	print "Code: " . htmlspecialchars($r->faultCode())
		. " Reason: '" . htmlspecialchars($r->faultString()) . "'</pre><br/>";
	die();
}
$csrf_token = php_xmlrpc_decode($r->value());
$c->setHeader('X-CSRF-Token',$csrf_token['token']);

//$taxonomy=new xmlrpcmsg('entity_taxonomy_vocabulary.getTree',array(new xmlrpcval($taxonomy_category),new xmlrpcval(0),new xmlrpcval(99)));
$taxonomy=new xmlrpcmsg('taxonomy_term.retrieve',array(new xmlrpcval(60)));
$r = &$c->send($taxonomy);
if($r->faultCode())
{
	print "An error occurred: ";
	print "Code: " . htmlspecialchars($r->faultCode())
		. " Reason: '" . htmlspecialchars($r->faultString()) . "'</pre><br/>";
	die();
}

$v=php_xmlrpc_decode($r->value());
print_r($v);