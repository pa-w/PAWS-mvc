<?php
class Output_XML
{
	var $_Module;
	var $_Action;
	var $_Submodule;
	function __construct($modName, $actionName, $submodName = '') {
		$this->_Module = $modName;
		$this->_Action = $actionName;
		$this->_Submodule = $submodName;
	}
	function fetch($res, $mod=array()) {
		try {
			require_once MAIN_DIR."/libs/Array2XML.php";
			$keys = array_keys($res);
			$xml = Array2XML::createXML($keys[0], $res[$keys[0]]);
			header ('Content-Type: text/xml');
			echo $xml->saveXML ();
		} catch(Exception $e) {
			print_r($res);
		}
	}
}
?>
