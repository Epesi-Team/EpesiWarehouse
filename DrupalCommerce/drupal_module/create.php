<?php
chdir(dirname(__FILE__));
$parent_dir = getcwd();

function download_drupal_module($name) {
    $xml = simplexml_load_file('http://updates.drupal.org/release-history/'.$name.'/7.x');
    system('wget -O '.$name.'.tar.gz -q '.(string)$xml->releases->release[0]->download_link);
    system('tar xzf '.$name.'.tar.gz');
    ob_start();
    system('tar -tf '.$name.'.tar.gz | grep -o "^[^/]\+" | sort -u');
    $ret = trim(ob_get_clean());
    system('rm '.$name.'.tar.gz');
    return $ret;
}

$dir = download_drupal_module('commerce_kickstart');
chdir($dir.'/sites/all/modules');
download_drupal_module('services');
download_drupal_module('commerce_multicurrency');
download_drupal_module('commerce_stock');
download_drupal_module('entity_translation');
download_drupal_module('services_views');
download_drupal_module('services_entity');
system('tar xzf "'.$parent_dir.'/epesi_commerce.tar.gz"');
system('tar xzf "'.$parent_dir.'/commerce_services.tar.gz"');
system('tar xzf "'.$parent_dir.'/commerce_epesi_payment.tar.gz"');
system('tar xzf "'.$parent_dir.'/services_entity_translation.tar.gz"');

chdir($parent_dir);
system('tar czf epesi_commerce_kickstart.tar.gz '.$dir);
system('rm -Rf '.$dir);