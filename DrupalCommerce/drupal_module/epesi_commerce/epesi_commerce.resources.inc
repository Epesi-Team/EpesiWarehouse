<?php

function _epesi_commerce_resource_get_product_fields() {
	$fields = field_info_instances("commerce_product", "epesi_products");
	return array_keys($fields);
}

function _epesi_commerce_resource_get_node_fields() {
	$fields = field_info_instances("node", "epesi_products");
	return array_keys($fields);
}
