<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

$config = array(	
	'debug' => true, // включает вывод ошибок и возможность отладки
	'backtrace' => true, // выводить ли трассировку ошибок
	'base_domain' => 'solo.loc', // доменное имя
	'base_path' => '', // если сайт располагается в директории (Например: path/to/site)
	'scheme' => 'http', // протокол, нужно указать явно (Например: http, https)
	'db_host' => 'localhost', // параметры соединения с базой данных
	'db_port' => 3306,
	'db_db' => 'solo',
	'db_user' => 'root',
	'db_pass' => 'root',
	'db_charset' => 'utf-8',
	'db_save_query' => '', // имя файла в папке log, в который сохраняются все запросы, для отладки (Например: my_query.txt)
	'required' => array('path', 'shell'), // обязательные параметры роутинга
	'timezone' => 'Europe/Moscow', // временная зона
	'auth_intarval' => 30 // секунд ожидания после неправильного ввода пароля
);
?>
