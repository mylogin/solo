<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

class template {

	private $data = array();

	function __get($name) {
		if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
	}

}

?>