<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

// создание таблиц
if(file_exists(i()->root.'/install/content/dump.sql')) {
	i()->db->multi_query(file_get_contents(i()->root.'/install/content/dump.sql'));
	do {
		if ($result = i()->db->store_result()) {
			$result->free();
		}
	} while (i()->db->more_results() && i()->db->next_result());
}
i()->copy_dir(i()->root.'/install/content/copy', i()->root);
i()->module_map(true);
?>