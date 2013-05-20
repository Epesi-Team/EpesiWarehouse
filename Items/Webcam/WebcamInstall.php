<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_WebcamInstall extends ModuleInstall {
    const version = '1.5.0';

	public function install() {
		$this->create_data_dir();
		Base_ThemeCommon::install_default_theme($this->get_type());
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items', array('Premium_Warehouse_Items_WebcamCommon', 'attach_webcam_button'));
		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items', array('Premium_Warehouse_Items_WebcamCommon', 'attach_webcam_button'));
		return true;
	}
	
	public function version() {
		return array(self::version);
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Premium/Warehouse/Items','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Warehouse Items Webcam Photo module - Premium Module',
			'Author'=>'pbukowski@telaxus.com',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
        return array('package'=>__('Inventory Management'), 'option'=>__('Webcam photos'));
	}
}

?>
