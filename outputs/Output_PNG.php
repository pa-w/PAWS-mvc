<?php
class Output_PNG
{
	function __construct($modName, $actionName, $submodName = '') {
	}
	function fetch ($res, $mod=array()) {
		if (array_key_exists("img", $res)) {
			$img = $res["img"];

			header("Content-type: image/png");
			imagepng($img);
			imagedestroy($img);
		}
	}
}
?>
