<?php
class docs
{
	var $_Modules = array();
	/**
	* @
	*/
	function __construct() {
		require_once MAIN_DIR."/documentator/documentator.php";
		$doc = new Documentator();
		$this->_Modules = $doc->_Modules;
	}
	function init () {
	}
}
?>
