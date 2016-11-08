<?php
class wika_section {
	var $Children = array ();
	function addChild () { 
		$c = new wika_section ();
		array_push ($this->Children, $c);

		return $c;
	}
}
class wika_state {
	var $Children = array ();
	var $Section;
	function __construct () { 
		$this->Section = new wika_section ();
	}
}
class wika
{
	function parse ($input) { 
		$state = new wika_state ();
		$_callbacks = array (
			"/==== (.+?) ====/m" => function ($m) use (&$state) { 
				$state->Section = $state->Section->addChild (); 
				return "<h3>" . $m [1] . "</h3>"; 
			},
			"/=== (.+?) ===/m" => function ($m) use (&$state) { 
				$state->Section = $state->Section->addChild (); 
				return "<h2>" . $m [1] . "</h2>"; 
			},
			"/== (.+?) ==/m" => function ($m) use (&$state) { 
				$state->Section = $state->Section->addChild (); 
				return "<h1>" . $m [1] . "</h1>"; 
			}/*,
			"/[sunburst (.+?)]/m" => function ($m) use (&$state) {
				print_r ($m);
				return "<a></a>"; 
			}
			*/
		);
		if (!empty ($input)) { 
			foreach ($_callbacks as $pattern => $callback) { 
				$input = preg_replace_callback ($pattern, $callback, $input);
			}
			return $input;
		}
		return false;
		
	}
}
?>
