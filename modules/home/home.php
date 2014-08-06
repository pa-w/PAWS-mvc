<?php
class home
{
	function __construct () {
	}
	/**
	* This ugly methods allows you to have a 'managed' catch-all html template system.
	*/
	function init ($p1 = '', $p2 = '', $p3 = '', $p4 = '', $p5 = '', $p6 = '') {
		$parts = array($p1, $p2, $p3, $p4, $p5, $p6);
		$dir = implode("/", $parts);
		$path = pathinfo($dir);
		$file = MAIN_DIR."/content/".$path['dirname']."/".$path['filename'].".html";
		if (file_Exists($file)) {
			$content = file_get_contents ($file);

			return $content;
		}
	}
	/**
	* @oauth_level user 
	*/
}
?>
