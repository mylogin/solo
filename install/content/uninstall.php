<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

$res = i()->db->query("SELECT * FROM `base_tree` WHERE `actions` LIKE '%content:text%'");
if($res->num_rows) {
	i()->admin->add_notice('notice', 'Модуль content используется', $_SERVER['HTTP_REFERER']);	
}
i()->db->query("DROP TABLE IF EXISTS `mod_content`");
i()->db->query("DROP TABLE IF EXISTS `mod_content_sec`");
i()->db->query("DELETE FROM `base_tree` WHERE `mod` = 'content'");
i()->del_dir(i()->root.'/resources/content');
i()->del_dir(i()->root.'/module/user/content');
?>