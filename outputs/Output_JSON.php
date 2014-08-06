<?php
class Output_JSON
{
	var $_Module;
	var $_Action;
	var $_Submodule;
	function __Construct($modName, $actionName, $submodName = '') {
		$this->_Module = $modName;
		$this->_Action = $actionName;
		$this->_Submodule = $submodName;
	}
	function fetch($res, $mod=array()) {
		$returnCode = 501;
		$returnMessage = "Not implemented/No implementado";
		if (is_array($res)) {
			$returnCode = 200;
			$returnMessage = "OK";
		}
		$k = $this->_Action;
		//$arr = array("return_code" => $returnCode, "return_message" => $returnMessage, $k => $res); //TODO: Envelope or not?
		$arr = $res; 
		$x = json_encode($arr);
		header("Content-Type: text/json");
		if (array_key_exists("jsonp", $_REQUEST) || array_key_exists("callback", $_REQUEST)) {
			echo (!empty($_REQUEST['jsonp']) ? $_REQUEST['jsonp'] : $_REQUEST['callback'])."(".$x.")";
		} else {
			echo $x;
		}
	}
}
?>
