<?php
class Output_CSV
{
	var $_Module;
	var $_Action;
	var $_Submodule;
	function __construct ($modName, $actionName, $submodName = '') {
		$this->_Module = $modName;
		$this->_Action = $actionName;
		$this->_Submodule = $submodName;
	}
	function fetch ($res) {
		$cols = false;
		//header("Content-Type: text/csv");
		$out = fopen ("php://output", "w");
		foreach ($res as $r) {
			if (!$cols) {
				$cols = true;
				fputcsv ($out, array_keys ($r));
			}
			fputcsv ($out, array_values ($r));
		}
		fclose ($out);
	}
}
?>
