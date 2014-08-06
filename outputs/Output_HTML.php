<?php
class Output_HTML
{
	var $_MainTPL = 'main.tpl';
	var $_ModuleTPL;
	var $_SubmoduleTPL;
	var $_ActionTPL;
	function __construct($modName, $actionName, $submodName = '') {
		$this->_MainTPL = "main.tpl";
		$this->_ModuleTPL = "{$modName}/main.tpl";
		if (!empty($submodName)) {
			$this->_ActionTPL = "{$modName}/{$submodName}/{$actionName}.tpl";
			$this->_SubmoduleTPL = "{$modName}/{$submodName}/main.tpl";
		} else {
			$this->_ActionTPL = "{$modName}/{$actionName}.tpl";
		}
	}
	function fetch ($res, $mod=array()) {
		$actionTPLFile = MAIN_DIR."views/".$this->_ActionTPL;
		$submoduleTPLFile = MAIN_DIR."views/".$this->_SubmoduleTPL;
		$moduleTPLFile = MAIN_DIR."views/".$this->_ModuleTPL;
		$ret = "";
		if (!empty($this->_ActionTPL) && file_exists ($actionTPLFile)) {
			$actionTPL =& $this->init_smarty();
			$this->smarty_assign_array($actionTPL, $res); 
			$this->smarty_assign_array($actionTPL, $mod); 
			$actionTPL->assign("content", $res);
			$actionContent = $actionTPL->fetch($actionTPLFile);
			$ret = $actionContent;
		}
		if (!empty($this->_SubmoduleTPL) && file_exists($submoduleTPLFile)) {
			$submoduleTPL =& $this->init_smarty();
			$this->smarty_assign_array($submoduleTPL, $res);
			$this->smarty_assign_array($submoduleTPL, $mod);
			if (!empty($actionContent)) {
				$submoduleTPL->assign("content", $actionContent);
			}
			$submoduleContent = $submoduleTPL->fetch($submoduleTPLFile);
			$ret = $submoduleContent;
		}
		if (!empty($this->_ModuleTPL) && file_exists($moduleTPLFile)) {
			$moduleTPL =& $this->init_smarty ();
			$this->smarty_assign_array($moduleTPL, $res);
			$this->smarty_assign_array($moduleTPL, $mod);
			if (!empty($ret)) {
				$moduleTPL->assign("content", $ret);
			}

			$moduleContent = $moduleTPL->fetch($moduleTPLFile);
			$ret = $moduleContent;
		}
		$mainTPLFile = MAIN_DIR."views/".$this->_MainTPL;
		if (!empty($this->_MainTPL) && file_exists($mainTPLFile)) {
			$mainTPL =& $this->init_smarty();
			$this->smarty_assign_array($mainTPL, $res);
			$this->smarty_assign_array($mainTPL, $mod);
			if (!empty($ret)) {
				$mainTPL->assign("content", $ret);
			}
			$mainContent = $mainTPL->fetch($mainTPLFile);
			$ret = $mainContent;
		}
		return $ret;
	}
	function &init_smarty () {
		$smarty = new Smarty();
		$smarty->template_dir = MAIN_DIR."views/";
		$smarty->compile_dir = MAIN_DIR."views/_compile/";

		return $smarty;
	}
	function smarty_assign_array(&$smarty, $ar)
	{
		if (is_array($ar)) {
			foreach($ar as $k => $v)
			{
				@list($m, $d) = explode("-", $k);
				if ($d) {
					$smarty->append($m, array($d => $v), true);
				}
				else {
					$smarty->assign($k, $v);
				}
			}
		}
	}
}
?>
