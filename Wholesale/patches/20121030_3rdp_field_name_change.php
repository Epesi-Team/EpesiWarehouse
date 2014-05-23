<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

PatchUtil::db_rename_column('premium_warehouse_wholesale_items', '3rdp', 'thirdp', 'C(255)');

?>