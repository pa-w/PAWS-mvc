<?php
require_once MAIN_DIR."/outputs/Output_HTML.php";
class Output_INFO extends Output_HTML
{
	function __construct($module_name, $action_name, $submodule_name = '')
	{
		$tpl = 'module';
		if (!empty($submodule_name)) { $tpl = "submodule"; }
		if (!empty($action_name) && !array_key_exists("submodule", $_REQUEST)) { 
			$mod = empty($submodule_name) ? $module_name : $module_name."_".$submodule_name;
			if (method_exists($mod, $action_name)) {
				$tpl = "action"; 
			}
		}
		
		parent::__construct("docs", $tpl);		
	}
	function fetch($res, $mod=array()) {
		return parent::fetch($res, $mod);
	}
}
?>
