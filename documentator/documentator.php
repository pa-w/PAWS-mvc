<?php
class Documentator
{
	var $_Module;
	var $_Submodule;
	var $_Action;

	var $_Modules = array();

	function __construct ($module_name = '', $action_name = '', $submodule_name = '') {
		$this->_Module = $module_name;
		$this->_Submodule = $submodule_name;
		$this->_Action = $action_name;
		
		$this->_request_config = new RequestConfig ();
		$mods = scandir(MAIN_DIR."modules");	
		foreach ($mods as $mod) {
			if (($mod != "." && $mod != "..") && is_dir(MAIN_DIR."modules/$mod")) {
				$modFile = MAIN_DIR."modules/{$mod}/{$mod}.php";
				if (file_exists($modFile)) {
					require_once $modFile;	
					if (class_exists($mod)) {
						$modInfo = $this->module_info ($mod);
						if (!empty($modInfo)) {
							$this->_Modules[$mod] = $modInfo;	
						}
					}
				}
			}
		}
	}
	function list_modules () {
		echo "Lista de modulos<br>";
	}
	function get_submodules ($mod) {
		if (is_dir(MAIN_DIR."modules/{$mod}/")) {
			$submods = scandir(MAIN_DIR."modules/{$mod}/");
			$submodules = array();
			foreach ($submods as $submod) {
				$submodFile = MAIN_DIR."modules/{$mod}/".$submod; 
				if ($submod !== $mod && !is_dir($submodFile)) {
					$path = pathinfo($submodFile);
					if ($path['extension'] == "php") {
						require_once $submodFile;
						if (class_exists($mod."_".$path['filename'])) {
							$submodules[$path['filename']] = $this->module_info($mod."_".$path['filename']); 
						}
					}
				}
			}
			return $submodules;
		}
		return false;
	}
	var $_request_config; 
	function module_info ($name) {
		$this->_request_config->setModule ($name);
		$module = $this->_request_config->exploreModule();
		$info = $module["document"];

		if (!empty($info)) {
			if (array_key_exists("document", $info) && in_array("false", $info['document'])) return;
			$info['actions'] = array();
			$actions = $module["methods"];
			foreach ($actions as $action) {
				if ($action->class == $name && $action->name != "__construct") {
					$info['actions'][$action->name] = $this->action_info ($name, $action->name);
				}
			}
			$info['submodules'] = $this->get_submodules($name);
		}
		return $info;
	}
	function action_info ($module, $ac) {
		$this->_request_config->setAction($ac);
		$action = $this->_request_config->analyze ();
		return $action;

	}
	function action_info_old ($module, $ac) {
		$this->_request_config->setAction($ac);
		//	$action = $this->_request_config->exploreMethod ();
		$action = $this->_request_config->analyze ();
		$doc = $action['document'];
		$params = (is_array($action['parameters'])) ? $action['parameters'] : array();	
		$examples = array();
		if (isset($doc['param_example'])) {
			foreach ($doc['param_example'] as $example) {
				$rd = explode(" ", $example);
				if (!array_key_exists($rd[0], $examples)) {
					$examples[$rd[0]] = array();
				}
				@list($ex, $desc, $def) = @explode("|", @implode (" ", @array_slice($rd, 1)));
				$examples[$rd[0]][] =  array("example" => $ex, "desc" => $desc, "default" => !empty($def));
			}
		}
		$ifempty = array();
		if (isset($doc['param_ifempty'])) {
			foreach ($doc['param_ifempty'] as $pm) {
				$rd = explode (" ", $pm);
				$ifempty[$rd[0]] = @implode(" ", @array_slice($rd, 1));
			}
		}
		$descs = array();
		if (isset($doc['param_desc'])) {
			foreach ($doc['param_desc'] as $pd) {
				$rd = explode(" ", $pd);
				$descs[$rd[0]] = @implode(" ", @array_slice($rd, 1));
			}
		}
		$notes = array();
		if (isset($doc['param_note'])) {
			foreach ($doc['param_note'] as $pn) {
				$rd = explode (" ", $pn);
				$notes[$rd[0]] = @implode(" ", @array_slice($rd, 1));
			}
		}
		$source = array();
		if (isset($doc['param_source'])) {
			foreach ($doc['param_source'] as $sr) {
				$rd = explode(" ", $sr);
				$source[$rd[0]] = @implode(" ", @array_slice($rd, 1));
			}
		}
		$values = array();
		if (isset($doc['param_values'])) {
			foreach ($doc['param_values'] as $sv) {
				$rd = explode(" ", $sv);
				$values[$rd[0]] = @implode(" ", @array_slice($rd, 1));
			}
		}
		$arguments = array();
	
		$baseUrl = $ac;
		$urls = array();
		$baseDone = false;
		
		foreach ($params as $param) {
			$name = $param->getName ();
			$optional = $param->isOptional();
			$defaultValueAvailable = $param->isDefaultValueAvailable();
			$position = $param->getPosition ();
			$arguments[] = array("name" => $name, "required" => !$optional, "examples" => @$examples[$name], "type" => "rest", "ifempty" => @$ifempty[$name], "source" => @$source[$name], "values" => @$values[$name], "desc" => @$descs[$name], "note" => @$notes[$name] );
			if (!$optional) {
				$baseUrl.="/:".$name.":";
			} else {
				if (!$baseDone) {
					$urls[] = $baseUrl;
					$baseDone = true;
				}
				$url = $baseUrl.="/:".$name.":";
				$urls[] = $url;
			}
		}
		if (isset($doc['request_required_param'])) {
			foreach($doc['request_required_param'] as $param) {
				$name = trim($param);
				$arguments[] = array("name" => $name, "required" => true, "examples" => @$examples[$name], "type" => "request", "ifempty" => @$ifempty[$name], "source" => @$source[$name], "values" => @$values[$name], "desc" => @$descs[$name], "note" => @$notes[$name]);
			}
		}
		if (isset($doc['request_param'])) {
			foreach($doc['request_param'] as $rparam) {
				$name = trim($rparam);
				$arguments[] = array("name" => $name, "required" => false, "examples" => @$examples[$name], "type" => "request", "ifempty" => @$ifempty[$name], "source" => @$source[$name], "values" => @$values[$name]);
			}
		}
		return array("arguments" => $arguments, "urls" => $urls, "doc" => $doc);
	}
	function submodule_info () {
		echo "Will get submodule_info<br>";
	}
	function fetch () {
	}
}
?>
