<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

class tag {

	private $data = array();
	public $level = array();
	public $parse = false;
	public $tags = array();

	public function __get($name) {
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}
	}
	
	public function __call($name, $arg) {
		if($this->parse) {
			$this->tags[] = $name;
			return;
		}
		$this->level[] = $name;
		if(isset(i()->route_tree[implode(':', $this->level)])) {
			if(!i()->load_controller(i()->route_tree[implode(':', $this->level)],  true, $arg)) {			
				// лог
			}
		}
		array_pop($this->level);
	}

}

?>