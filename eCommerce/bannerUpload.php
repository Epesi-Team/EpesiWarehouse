<?php
/**
 * Uploads file
 *
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2006, Telaxus LLC
 * @version 1.0
 * @license MIT
 * @package epesi-utils
 * @subpackage file-uploader
 */
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // date in the past

define('CID',false);
require_once('../../../../include.php');
$doc = $_FILES['file'];
$dest_filename  = microtime(true);
$dest_path  = DATA_DIR.'/Premium_Warehouse_eCommerce/banners/tmp/'.$dest_filename;

if($doc['error']==UPLOAD_ERR_INI_SIZE || $doc['error']==UPLOAD_ERR_FORM_SIZE) {
	?>
	<script type="text/javascript">
	<!--
	alert('Specified file too big');
	-->
	</script>
	<?php
} elseif($doc['error']==UPLOAD_ERR_PARTIAL || $doc['error']==UPLOAD_ERR_EXTENSION) {
	?>
	<script type="text/javascript">
	<!--
	alert('Upload failed');
	-->
	</script>
	<?php
} elseif($doc['error']==UPLOAD_ERR_NO_TMP_DIR || $doc['error']==UPLOAD_ERR_CANT_WRITE) {
	?>
	<script type="text/javascript">
	<!--
	alert('Invalid server setup: cannot write to temporary directory');
	-->
	</script>
	<?php
} elseif($doc['error']==UPLOAD_ERR_NO_FILE) {
	?>
	<script type="text/javascript">
	<!--
	alert('Please specify file to upload');
	-->
	</script>
	<?php
} elseif(!eregi('\.(png|gif|swf|jpg|jpeg)$',$doc['name'],$reqs)) {
	?>
	<script type="text/javascript">
	<!--
	alert('Invalid file extension.');
	-->
	</script>
	<?php
} else {
    $dest_path .= '.'.$reqs[1];
	move_uploaded_file($doc['tmp_name'], $dest_path);
	?>
	<script type="text/javascript">
	<!--
	parent.$('banner_upload_file').value='<?php print($dest_path); ?>';
	parent.$('banner_upload_info').innerHTML='<img src="<?php print($dest_path); ?>" style="max-width:300px;max-height:120px"><br><?php print($doc['name']); ?>';
	-->
	</script>
	<?php
}

?>
<script type="text/javascript">
	<!--
    parent.$('banner_upload_field').value='';
	-->
</script>
