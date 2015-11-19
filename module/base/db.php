<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

class db {

	public $i;
	
	function __construct() {
 		$this->db = new mysqli(i()->conf['db_host'], i()->conf['db_user'], i()->conf['db_pass'], i()->conf['db_db'], i()->conf['db_port']);
		$this->db->set_charset(i()->conf['db_charset']);
	}
	
	function __get($name) {
		return property_exists('mysqli', $name) ? $this->db->$name : null;
	}
	
	function __call($name, $arguments) {
		if($name == 'query' && i()->conf['db_save_query']) {
			i()->log(i()->conf['db_save_query'], $arguments[0]);
		}
  		return call_user_func_array(array($this->db, $name), $arguments);
	}

	public function escape($data) {
		if(is_array($data)) {
			$ret = array();
			foreach($data as $k => $v) {				
				$ret[$k] = $this->escape($v);
			}
			return $ret;
		}		
		if(is_null($data)) {
			return 'NULL';
		} elseif (is_bool($data)) {
			return ($data === FALSE) ? 0 : 1;
		} else {
			return "'".$this->db->real_escape_string($data)."'";
		}
	}
		
}

?>