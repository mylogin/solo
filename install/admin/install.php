<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

if(file_exists(i()->root.'/install/admin/dump.sql')) {
	i()->db->multi_query(file_get_contents(i()->root.'/install/admin/dump.sql'));
	do {
		if ($result = i()->db->store_result()) {
			$result->free();
		}
	} while (i()->db->more_results() && i()->db->next_result());
}

?>