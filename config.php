<?php
/*
The MIT License (MIT)

Copyright (c) 2013, Paola Villarreal (paola@labplc.mx)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
$configDir = "../config/";
define('MAIN_DIR', dirname($_SERVER['DOCUMENT_ROOT']).'/');
define('SMARTY_DIR', MAIN_DIR.'libs/smarty/libs/');
define('DEFAULT_MODULE', 'home');
define('DEFAULT_TPLDIR', MAIN_DIR."/modules/");
define('DEFAULT_OUTPUT_FORMAT', 'html');
date_default_timezone_set('America/Mexico_City');
/**
* @name RequestConfig
* @description It gets an HTTP and analyzes it
* @author Paola Villarreal (paola@labplc.mx)
* @version 1.0
*/
class RequestConfig {
	var $_Module; 
	var $_Action;
	var $_Submodule;
	function __construct() {
	}
	function setModule($module) {
		$this->_Module = $module;
	}
	function setSubmodule($submodule) {
		$this->_Submodule = $submodule;
	}
	function setAction($action) {
		$this->_Action = $action;
	}
	function exploreModule ($class = '') {
		if (empty($class)) {
			$class = (empty($this->_Submodule)) ? $this->_Module : $this->_Submodule;
		}
		if (class_Exists($class)) {
			$mod = new ReflectionClass ($class);
			$comment = $mod->getDocComment ();
			$lines = $this->getDocLines ($comment);
			return array("document" => $this->parseDocComment($lines), "comment" => $lines, "methods" => $mod->getMethods(ReflectionMethod::IS_PUBLIC));
		} else {
			echo "NO EXISTE: $class <br>";
		}
	}
	function analyze () {
		$submod = null;
		$act = null;
		$mod = $this->exploreModule($this->_Module);	
		if (!empty($_this->_Submodule)) {
			$submod = $this->exploreModule($this->_SubModule);
		}
		if (!empty($this->_Action)) {
			$act = $this->exploreMethod($this->_Action);
		}
		if ( !is_null($act) && array_key_exists("document", $act)) { 
			//$doc = array_merge($mod["document"], $act["document"]); 
			$doc = $act["document"];
			$allowedMethods = $this->getAllowedMethods ($doc);
			$lines = $act["comment"];
			//$lines = array_merge($mod["comment"], $act["comment"]);
			$methodsConfig = array();
			$restSignatures = array();
			$varsByParameters = array("param_example", "param_source", "param_desc", "param_note", "param_warning", "param_ifempty", "param_values", "request_param", "request_required_param", "param_dependency", "param_options");
			$implodeParams = array();
			$resetParams = array("short_desc", "description", "name", "returns", "auth_required", "auth_type", "rate_limit");
			$multiDimentionalParams = array("return_value", "return_value_notes");
			$restParams = array();
			$parameters = array();
			$minRestParamCount = 0;
			foreach ($act["parameters"] as $param) {
				$optional = $param->isOptional ();
				$name = $param->getName ();
				if (!$optional) $minRestParamCount++;
				$values = array("default_available" => $param->isDefaultValueAvailable());
				if ($values["default_available"]) {
					$values["default"] = $param->getDefaultValue();
				}
				$par = array("name" => $name, "required" => !$optional, "isrest" => true ) + $values;
				$restParams[] = $par;
			}
			$inMethods = $allowedMethods;
			$restParamCount = count($restParams)+1;
			$ignoredSignatures = array();
			if (array_key_exists("ignore_signature", $act["document"])) {
				foreach ($act["document"]["ignore_signature"] as $signature) {
					@list($m, $c) = @explode(" ", $signature);
					$ignoredSignatures[$m][] = $c;
				}
			}
			foreach ($inMethods as $m) {
				for ($i = $minRestParamCount; $i < $restParamCount; $i++) {
					if (!@in_array($i, $ignoredSignatures[$m])) {
						$methodsConfig[$m][$i] = array();
						for ($e = 0; $e < $i; $e++) {
							if ($e <= $i) $restParams[$e]['required'] = true;
							$methodsConfig[$m][$i]["rest_parameters"][$e] = $restParams[$e];
							$methodsConfig[$m][$i]["parameters"][$restParams[$e]["name"]] = $restParams[$e];
						}
					}
				}
			}
			$paramTarget = null;
			foreach ($lines as $line) {
				$txt = trim(str_replace("*", "", $line));
				$wd = explode(" ", $txt);
				if (!empty($wd[0]) && $wd[0][0] == "@") {
					$key = trim(str_replace("@", "", $wd[0]));
					$value = trim(implode(" ", array_slice($wd, 1)));
					if ($key == "ifmethod") { $inMethods = array($value);  continue; } 
					if ($key == "endifmethod") { $inMethods = $allowedMethods; continue; }
					if ($key == "ifrest_param_count") { $restParamCount = $value; $paramTarget = $value; continue; }
					if ($key == "endifrest_param_count") { $restParamCount = count($restParams) + 1; $paramTarget = null; continue; }
					foreach($inMethods as $m) {
						$start = (!is_null($paramTarget)) ? $paramTarget : $minRestParamCount;
						$end = (!is_null($paramTarget)) ? $paramTarget + 1 : $restParamCount;
						for ($i = $start; $i < $end; $i++) {
							if (!@in_array($i, $ignoredSignatures[$m])) {
								$normal = true;
								if (in_array($key, $varsByParameters)) {
									$pm = explode(" ", $value);
									$pk = $pm[0];
									$pv = implode(" ", array_slice($pm, 1));
									if (!array_key_exists("request_parameters", $methodsConfig[$m][$i])) {
										$methodsConfig[$m][$i]["request_parameters"] = array();
									}
									if (!array_key_exists($pk, $methodsConfig[$m][$i]["request_parameters"])) {
										$methodsConfig[$m][$i]["request_parameters"][$pk] = array();
									}
									$methodsConfig[$m][$i]["request_parameters"][$pk][$key][] = $pv;
									$methodsConfig[$m][$i]["parameters"][$pk][$key][] = $pv;
									$normal = false;
								}
								if (in_array($key, $implodeParams)) {
									if (!isset($methodsConfig[$m][$i][$key])) $methodsConfig[$m][$i][$key] = "";
									$methodsConfig[$m][$i][$key] .= " ".$value;
									$normal = false;
								}
								if (in_array($key, $resetParams)) {
									if (!isset($methodsConfig[$m][$i][$key])) $methodsConfig[$m][$i][$key] = "";
									$methodsConfig[$m][$i][$key] = $value;
									$normal = false;
								}
								if ($key == "rest_param_rename") {
									@list($who, $new) = explode(" ", $value);
									foreach ($methodsConfig[$m][$i]["rest_parameters"] as $idx => $rparam) {
										if ($rparam['name'] == $who) {
											unset($methodsConfig[$m][$i]["parameters"][$rparam['name']]);
											unset($methodsConfig[$m][$i]["rest_parameters"][$idx]);
											$rparam['name'] = $new;
											$methodsConfig[$m][$i]["rest_parameters"][$idx] = $rparam;
											$methodsConfig[$m][$i]["parameters"][$new] = $rparam;
										}
									}
									$normal = false;
								}
								if (in_array($key, $multiDimentionalParams)) {
									$ex = explode("|", $value);
									$prs = $ex[0]; $val = $ex[1];
									$route = explode(" ", $prs);
									$examples = array();
									foreach (array_slice($ex, 2) as $exp) {
										$examples[] = trim($exp);
									}

									$arr = array();
									if (!array_key_exists($key."_values", $methodsConfig[$m][$i])) {
										$methodsConfig[$m][$i][$key."_values"] = array();
									}
									if (!array_key_exists($key, $methodsConfig[$m][$i])) {
										$methodsConfig[$m][$i][$key] = array();
									}
									$ref = &$methodsConfig[$m][$i][$key];
									$refv = &$methodsConfig[$m][$i][$key."_values"];

									$cnt = count($route);	
									for($z = 0; $z < $cnt; $z++) {
										$ky = trim($route[$z]);
										if (!empty($ky)) {
											if (!array_key_exists($ky, $refv)) {
												if ($z < $cnt - 2) {
													$refv[$ky] = array();
												}
											}
											if (!array_key_exists($ky, $ref)) {
												$ref[$ky] = array();
											}
											if ($z == $cnt -2) {
												for ($a = 0; $a < count($examples); $a++) {
													if ($z > 1) {
														if (!array_key_exists($a, $refv) ){
															$refv[$a] = array(); 
														}
														if (!array_key_exists($ky, $refv[$a])) {
															$refv[$a][$ky] = $examples[$a];
														}
													} else {
														if (!array_key_exists($ky, $refv)) {
															$refv[$ky] = $examples[$a];
														}
													}
												}
												$refv = &$refv;
											} 
											if ($z < $cnt - 2) {
												$refv = &$refv[$ky];
											}
											$ref = &$ref[$ky];
										}
										if ($z == $cnt-1) {
											//$refv = $examples;
											$ref = trim($val);
										}
									}
									$normal = false;
								}
								if ($normal) {
									$methodsConfig[$m][$i][$key][] = $value;
								}
							}
						}
						// End of rest params loop
					}
					// End of authorized methods loop
				}
			}
			foreach ($methodsConfig as $m => $a) {
				foreach ($a as $i => $d) {
					foreach ($multiDimentionalParams as $key) {
						if (array_key_exists($key."_values", $methodsConfig[$m][$i])) {
							require_once MAIN_DIR."libs/geshi.php";
							$json = json_encode($methodsConfig[$m][$i][$key."_values"], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
							$geshi = new Geshi($json, 'java');
							$geshi->set_header_type(GESHI_HEADER_PRE);
							$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
							$methodsConfig[$m][$i][$key."_json"] = $geshi->parse_code(); 
							$root = array_keys($methodsConfig[$m][$i][$key])[0];
							try { 
								if (!empty ($root) ) {
									require_once MAIN_DIR."/libs/Array2XML.php";
									$xml = Array2XML::createXML($root, $methodsConfig[$m][$i][$key."_values"][$root]);
									$xmlOut = $xml->saveXML ();
									$geshi = new Geshi($xmlOut, 'xml');
									$geshi->set_header_type(GESHI_HEADER_PRE);
									$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
									$methodsConfig[$m][$i][$key."_xml"] = $geshi->parse_code ();
								}
							} catch(Exception $ex) { 
								//echo "$root<pre>".print_r($methodsConfig[$m][$i][$key."_values"], true)."<br>".print_r($ex, true)."</pre>";
							} 
						}
					}
				}
			}
			return $methodsConfig;
		} else { 
			//echo "NOT DOCUMENTED {$this->_Module} -> {$this->_Action} <br>"; 
		}
	}
	function getAllowedMethods ($doc) {
		$allowedMethods = array("GET");
		if (!empty($doc["allow_method"])) {
			foreach($doc["allow_method"] as $allow_method) {
				if (!in_Array($allow_method, $allowedMethods)) {
					$allowedMethods[] = $allow_method;
				}
			}
		}
		if (!empty($doc["deny_method"])) {
			foreach($doc["deny_method"] as $deny_method) {
				if (in_array($deny_method, $allowedMethods)) {
					if (($key = array_search($deny_method, $allowedMethods)) !== false) {
						unset($allowedMethods[$key]);
					}
				}
			}
		}
		return $allowedMethods;
	}
	var $_endpoints = array();
	function exploreMethod () {
		$class = (empty($this->_Submodule)) ? $this->_Module : $this->_Module."_".$this->_Submodule;
		$action = $this->_Action;
		if (method_exists($class, $action)) {
			$met = new ReflectionMethod($class, $action);
			$comment = $met->getDocComment ();
			$lines = $this->getDocLines ($comment);
			return array("document" => $this->parseDocComment($lines), "comment" => $lines, "parameters" => $met->getParameters());
		} else {
			echo " NO EXISTE $class -> $action <br>";
		}
		

	}
	function getMethodArguments ($module, $action) {
		$parms = array();
		if (method_exists($module, $action)) {
			$met = new ReflectionMethod($module, $action);
			$methodParams = $met->getParameters();
			return $methodParams; 
		}
		return $parms;
	}
	function getDocLines ($comment) {
		$lines = explode("\n", $comment);
		return $lines;
	}
	function parseDocComment ($lines) {
		if (is_Array($lines)) {
			$keys = array();
			foreach ($lines as $line) {
				$line = trim(str_replace(array("*"), "", $line));
				$words = explode(" ", $line);
				if (count($words) > 0) {
					if (!empty($words[0]) && $words[0][0] == "@") {
						$key = str_replace("@", "", $words[0]);
						$keys[$key][] = implode(" ",array_slice($words, 1));
					}
				}
			}
			return $keys;
		}
	}
}
/**
* @name HTTPController
* @description 
* @author Paola Villarreal (paola@labplc.mx)
* @version 1.0
*/
class HTTPController {
	function __construct () {
		$this->loadDependencies ();
		$this->parseArguments ();
		if (!$this->rateLimited ()) {
			date_default_timezone_set('America/Mexico_City');
			if ($this->_output_requested != "info") {
				if ($this->loadModule ()) {
					if ($this->isAuthorized ()) {
						if ($this->methodHasRequiredParameters ()) {
							if ($this->initializeObjects ()) {
								if ($this->callMethod () !== false) {
									$this->renderOutput ();
								} else {
									$this->pageNotFound ();
								}
							} else {
								$this->pageNotFound ();
							}
						} else {
							$this->pageNotAuthorized ("No tiene los parametros requeridos...");
						}
					} else {
						$this->pageNotAuthorized ("No fue autorizada");
					}
				} else {
					$this->pageNotFound ();
				}
			} else {
				$this->loadModule ();
				$this->prepareDocumentation ();
			}
		} else {
		  $this->pageNotAuthorized ("Demasiadas solicitudes");		  
		}
	}
	var $requestsPerMinute = 6000;
	function rateLimited () {
		if ($this->_output_requested == "xml" || $this->_output_requested == "json") {
			$p = parse_url($_SERVER['REQUEST_URI']);
			$baseDir = MAIN_DIR."/config/rate_limit/";
			$rateFile = $baseDir.$p['path']."/rateLimit.txt";
			if (!file_exists($rateFile)) {
				if (!is_dir($baseDir.$p['path'])) {
					mkdir($baseDir.$p['path'], 0777, true);
				}
				file_put_contents($rateFile, strftime("%H%M")." 0");
			}
			$rate = file_get_contents ($rateFile);
			@list($time, $cnt) = explode(" ", $rate);
			if (empty($cnt)) $cnt = 0;
			if (strftime("%H%M") == $time && $cnt > $this->requestsPerMinute) {
				return true;
			}
			file_put_contents($rateFile, strftime("%H%M")." ".($cnt + 1));
		}
		return false;
			
	}
	function prepareDocumentation () {
		require_once MAIN_DIR."/documentator/documentator.php";
		$doc = new Documentator ($this->_module_name, $this->_action_name, $this->_submodule_name);
		$this->_returned = array("modules" => $doc->_Modules);
		$this->_module_vars = get_object_vars($doc);
		$this->renderOutput ();
	}

    function renderOutput () {
        if ($this->_output_requested != "info") {
            $output = $this->buildOutput();
        } else {
            $moduleDir  = $this->_module_name;
            $moduleName = $this->_submodule_name ? $this->_submodule_name : $this->_module_name;
            $modulePath = MAIN_DIR."modules/{$moduleDir}/{$moduleName}.php";
            $fileName   = substr(str_replace('/', '_', parse_url($_SERVER['REQUEST_URI'])['path']), 1);
            $filePath   = MAIN_DIR."/cache/info/$fileName";

            if (!file_exists($filePath) || filemtime($filePath) < filemtime($modulePath)) {
                $file = fopen($filePath, 'w');
                $output = $this->buildOutput();
                fwrite($file, $output);
            } else {
                $file = fopen($filePath, 'r');
                $output = fread($file, filesize($filePath));
            }

            fclose($file);
        }

        echo $output;
    }

	function buildOutput() {
		$className = "Output_".strtoupper($this->_output_requested);
		$outputFile = MAIN_DIR."/outputs/{$className}.php";
		if (file_exists($outputFile)) {
			require_once $outputFile;
			if (class_exists($className)) {
				$out = new $className($this->_module_name, $this->_action_name, $this->_submodule_name);
				return $out->fetch($this->_returned, $this->_module_vars);
			}
		}
	}
	private $_default_module_name = 'home';
	private $_default_action_name = 'init';
	private $_module_name;
	private $_submodule_name;
	private $_action_name;
	private $_subaction_name;
	private $_module_instance;
	private $_submodule_instance;

	private $_returned;
	private $_output_requested;
	private $_module_vars;
	private $_method_keys = array();
	private $_method_arguments = array();
	private $_method_values;
	function parseArguments () {
		$req = pathinfo(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));	
		$this->_output_requested = isset($req['extension']) ? $req['extension'] : DEFAULT_OUTPUT_FORMAT;
		$pathTxt = ($req['dirname'] == "/" ? "" : $req['dirname'])."/".$req['filename'];
		if ($pathTxt == "/") $pathTxt = "";
		$path = explode ("/", $pathTxt);
		@list($module, $submodule, $action) = array_slice($path, 1);
		$index = 0;
		if ($this->is_valid_module ($module)) {
			$this->_module_name = $module;
			$index = 1;
		} else {
			$this->_module_name = $this->_default_module_name;
			$submodule = $module;
			$index = 0;
		}
		if ($this->is_valid_submodule($this->_module_name, $submodule)) {
			$this->_submodule_name = $submodule;
			$index++;
			if ($this->is_valid_action($this->_module_name."_".$submodule, $action)) {
				$this->_action_name = $action;
				$index++;
			} else {
				$this->_action_name = $this->_default_action_name;
			}
		} else {
			if ($this->is_valid_action($this->_module_name, $submodule)) {
				$index++;
				$this->_action_name = $submodule;
			} else {
				$this->_action_name = $this->_default_action_name;
			}
		}
		$this->_method_values  = array_slice($path, $index+1);
		$config = implode("/", $this->_method_values);
	}
	private function is_valid_module ($name) {
		$moduleFile = MAIN_DIR.'modules/'.$name.'/'.$name.'.php';
		if (file_exists($moduleFile)) {
			require_once $moduleFile;
			if (class_exists($name)) {
				return true;
			}
		}
		return false;
	}
	private function is_valid_submodule ($module, $name) {
		$submoduleFile = MAIN_DIR.'modules/'.$module.'/'.$name.'.php';
		if (file_Exists($submoduleFile)) {
			require_once $submoduleFile;
			if (class_exists($module."_".$name)) {
				return true;
			}
		}
		return false;
	}
	private function is_valid_action ($module, $name) {
		return method_exists($module, $name);
	}
	function loadDependencies () {
		require_once SMARTY_DIR.'Smarty.class.php';
		//require_once 'DB/DataObject.php';
		//require_once 'DB/DataObject/Cast.php';
		//require_once MAIN_DIR."libs/paws.php";
	}
	function loadModule () {
		$moduleFile = MAIN_DIR.'modules/'.$this->_module_name.'/'.$this->_module_name.'.php';
		if (file_exists($moduleFile)) {
			require_once $moduleFile;
			if (!empty($this->_submodule_name)) {
				$submodule = MAIN_DIR.'modules/'.$this->_module_name.'/'.$this->_submodule_name.'.php';
				if (file_exists ($submodule)) {
					require_once $submodule;
				}
			}
			return true;
		}
		return false;
	}
	function getMethodKeys($module, $action)  {
		$keys = array();
		if (method_exists($module, $action)) {
			$met = new ReflectionMethod($module, $action);
			$methodComment = $met->getDocComment ();
			$keys = ParseDocComment ($methodComment);
		}
		return $keys;
	}
	function getMethodArguments ($module, $action) {
		$parms = array();
		if (method_exists($module, $action)) {
			$met = new ReflectionMethod($module, $action);
			$methodParams = $met->getParameters();
			return $methodParams; 
		}
		return $parms;
	}
	var $_request_config; 
	var $_call_config = array();
	function isAuthorized () {
		$this->_request_config = new RequestConfig();
		$mod = empty($this->_submodule) ? $this->_module_name : $this->_module_name."_".$this->_submodule_name;
		$this->_request_config->setModule($this->_module_name);
		if (!empty($this->_submodule_name)) {
			$this->_request_config->setSubmodule($this->_submodule_name);
		}

		$this->_request_config->setAction( $this->_action_name);
		$this->_call_config = $this->_request_config->analyze();
		$requestMethod = $_SERVER['REQUEST_METHOD'];
		$restParams = count($this->_method_values);
		@$methodsConfig = $this->_call_config[$requestMethod][$restParams];
		if (is_array($methodsConfig) && array_key_exists("auth_required", $methodsConfig)) {
			$authObj = null;
			$auth_name = trim($methodsConfig['auth_required']);
			require_once MAIN_DIR."authenticators/".$auth_name.".php";
			switch($auth_name) {
				case "oauth": $authObj = new Auth_OAuth($methodsConfig); break;
				case "captcha": $authObj = new Auth_Captcha($methodsConfig); break;
				default: break;
			}
			if (!is_null($authObj)) {
				$res = $authObj->Authorize ();
				$this->_AuthObject = $authObj;
				return $res;
			}
		}
	
		return true;
		/*
		$mod = empty($this->_submodule_name) ? $this->_module_name : $this->_module_name."_".$this->_submodule_name;
		$com = new ReflectionClass($mod);
		if ($com->hasMethod ($this->_action_name)) {
			$this->_method_keys = $this->getMethodKeys ($mod, $this->_action_name);
			$this->_method_arguments = $this->getMethodArguments($mod, $this->_action_name);
			if (array_key_exists("auth_method", $this->_method_keys) && !$this->authRequest($this->_method_keys)) {
				return false;
			}
		}
		return true;
		*/
	}
	var $_AuthObject;
	function authRequest ($keys) {
		require_once MAIN_DIR."/auth.php";
		$authObj = null;
		switch (trim($keys["auth_method"][0])) {
			case "oauth": $authObj = new Auth_OAuth($keys); break; 
		}
		if (!is_null($authObj)) {
			if ($authObj->Authorize() === true) {
				return true;
			}
		}
		return false;
	}
	function methodHasRequiredParameters () {
		$modulesConfig = $this->_call_config;
		$requestMethod = $_SERVER['REQUEST_METHOD'];
		$restParams = count($this->_method_values);
		if (!array_key_exists($requestMethod, $modulesConfig)){ return false; }
		if (!array_key_exists($restParams, $modulesConfig[$requestMethod])) {  return false; } 
			$cnf = $modulesConfig[$requestMethod][$restParams];
		if (!empty($cnf['parameters'])) {
			foreach ($cnf['parameters'] as $param_name => $param) {
				if (array_key_exists('request_required_param', $param) && !array_key_exists($param_name, $_REQUEST)) {
					echo $param_name."\n";
					return false;
				}
				if (array_key_exists('param_dependency', $param) && !empty($param['param_dependency']) && isset($_REQUEST[$param_name])) {
					foreach ($param['param_dependency'] as $depen) {
						if (!array_key_exists($depen, $_REQUEST)) { 
							return false;
						}
					}
				}
				if (array_key_exists('param_options', $param) && array_key_exists($param_name, $_REQUEST)) {
					$x = implode(" ", $param['param_options']);
					$vals = explode(" ", $x);
					if (!in_array($_REQUEST[$param_name], $vals)) {
						return false;
					}
				}
			}
		}
		//return false;
		return true;
	}
	function pageNotAuthorized ($message = '') {
		echo "NOT AUTHORIZED \n $message";
	}
	function callMethod () {
		$obj = null;
		if (is_object($this->_submodule_instance)) {
			$obj = $this->_submodule_instance;
		} elseif (is_object($this->_module_instance)) {
			$obj = $this->_module_instance;
		} else {
		}
		if (method_exists($obj, $this->_action_name)) {
			$obj->_AuthObject = $this->_AuthObject;
			$obj->_Output = $this->_output_requested; 
			$obj->_RequestMethod = $_SERVER['REQUEST_METHOD'];
			$pUrl = parse_url($_SERVER['REQUEST_URI']);
			$path = explode("/", $pUrl['path']);

			$obj->_URL = $path; 
			$this->_module_vars = get_object_vars($obj);
			$cname = $this->_action_name;
			$ret = call_user_func_array(array(&$obj, $cname), $this->_method_values);
			$this->_returned = $ret;
			return $ret;
		}
		return false;
	}
	function initializeObjects () {
		if (!empty($this->_module_name)) {
			if ($this->_module_instance =& $this->initializeObject ($this->_module_name) !== false) {
				if (!empty($this->_submodule_name)) {
					if ($this->_submodule_instance =& $this->initializeObject($this->_module_name."_".$this->_submodule_name) === false) {
						return false;
					}
				}
				return true;
			}
		}
		return false;
	}
	function &initializeObject ($className) {
		if (class_exists($className)) {
			$obj = new $className ();	
			return  $obj;
		}
		$ret = false;
		return $ret;
	}
	function pageNotFound () {
		echo "Not Found... :(";
	}
	function parsePageArguments ($config) {
		$vars = explode("/", $config);
		$ret = array();
		for ($i=0; $i < count($vars); $i++)
		{
			$val = array();
			$arr = array();
			$keyval = explode("_", $vars[$i]);
			if (count($keyval) >= 2)
			{
				if (count($keyval) > 2)
				{
					for ($e=1; $e < count($keyval); $e++)
					{
						$keyval2 = explode("-", $keyval[$e]);
						if (count($keyval2) == 2)
						{
							$arr[$keyval2[0]] = $keyval2[1];
						}
						else
						{
							$arr[] = $keyval[$e];
						}
					}
					$key = $keyval[0];
					$val = $arr;
				}
				else
				{
					$key = $keyval[0];
					$val = $keyval[1];
					$subval = explode("-", $val);
					if (count($subval) == 2)
					{
						$val = array($subval[0] => $subval[1]);
					}
				}
			}
			else
			{
				$key = $vars[$i];
				$val = true;
			}
			$ret[$key] = $val;
		}
		return $ret;
	}
}
$http = new HTTPController ();
?>
