<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerce_3rdp__Plugin_bdk implements Premium_Warehouse_eCommerce_3rdp__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'BDK';
	}
	
	/**
	 * Returns parameter list for the plugin
	 * The list should be an array where key is paramter name/label and value is type [text|password]
	 * 
	 * @return array parameters list 
	 */
	public function get_parameters() {
		return array();
	}

    public function download($parameters,$item,$langs) {
        if(!in_array('pl',$langs)) return;
        
    	include("xmlrpc.inc");

	    $c = new xmlrpc_client("/export/test/", "www.kupic.pl", 80);
    	$c->return_type = 'phpvals'; // let client give us back php values instead of xmlrpcvals
    	$f = new xmlrpcmsg('test.getProductByEAN', array(php_xmlrpc_encode('08808987823825')));
    	$r =& $c->send($f);
    	if($r->faultCode()) {
    	    if(preg_match('/denied/si',$r->faultString()))
    	        Epesi::alert('BDK: access denied');
	        return;
	    }
		Epesi::alert(print_r($r->value(),true));
        return array('pl');
    }	
}
?>
