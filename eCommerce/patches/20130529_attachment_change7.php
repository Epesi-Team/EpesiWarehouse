<?php

	$ret = DB::Execute('SELECT uaf.id as id, uaf.original as original, uaf.attach_id as attach_id, uaf.revision as revision, ual.local as local FROM utils_attachment_file uaf LEFT JOIN utils_attachment_link ual ON ual.id=uaf.attach_id ORDER BY revision DESC');
	$max_rev = array();

	$ids = array();
	while ($row = $ret->FetchRow()) {
        $ext = strrchr($row['original'],'.');
        $row['revision'] = 0;

		$old_filename = 'modules/Premium/Warehouse/eCommerce/quickcart33pro/files/epesi/'.$row['id'].'_'.$row['revision'].$ext;
		if(!file_exists($old_filename)) continue;
		$new_filename = 'modules/Premium/Warehouse/eCommerce/quickcart33pro/files/epesi/'.$row['id'].$ext;
		@rename($old_filename, $new_filename);

		$old_filename = 'modules/Premium/Warehouse/eCommerce/quickcart33pro/files/100/epesi/'.$row['id'].'_'.$row['revision'].$ext;
		$new_filename = 'modules/Premium/Warehouse/eCommerce/quickcart33pro/files/100/epesi/'.$row['id'].$ext;
		@rename($old_filename, $new_filename);

		$old_filename = 'modules/Premium/Warehouse/eCommerce/quickcart33pro/files/200/epesi/'.$row['id'].'_'.$row['revision'].$ext;
		$new_filename = 'modules/Premium/Warehouse/eCommerce/quickcart33pro/files/200/epesi/'.$row['id'].$ext;
		@rename($old_filename, $new_filename);
	}

?>
