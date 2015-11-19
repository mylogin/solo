<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

class admin {

	public $test = 0;

	public $export = array(
		'name' => 'Админ',
		'nav' => array(
			array('level' => 0, 'name' => 'Структура', 'href' => 'adm/structure'),
			array('level' => 1, 'name' => 'Добавить раздел', 'href' => 'adm/structure/add'),
			array('level' => 0, 'name' => 'Доступ', 'href' => 'adm/access/user'),
			array('level' => 1, 'name' => 'Пользователи', 'href' => 'adm/access/user'),
			array('level' => 2, 'name' => 'Новый пользователь', 'href' => 'adm/access/user/new'),
			array('level' => 1, 'name' => 'Группы', 'href' => 'adm/access/group'),
			array('level' => 2, 'name' => 'Новая группа', 'href' => 'adm/access/group/new'),
			array('level' => 0, 'name' => 'Модули', 'href' => 'adm/mod', 'mod' => 1),
			array('level' => 0, 'name' => 'Выход', 'href' => 'adm/logout'),
		)
	);

	function logout() {
		if(isset($_SESSION['admin'])) {
			unset($_SESSION['admin']);
		}
		header("Location: ".i()->host.'/adm');
	}

	function get_class_name($class) {
		if(property_exists($class, 'export')) {
			return i()->$class->export['name'];
		}
		return $class;
	}

	function nav($mod = false) {
		$level = -1;
		$real_level = 0;
		foreach(i()->modules as $k => $v) {
			if(!isset(i()->$k->export['nav']) || ($mod !== true && $k != 'admin') || ($mod === true && $k == 'admin')) {
				continue;
			}
			foreach(i()->$k->export['nav'] as $v1) {
				for(; $level > $v1['level'] && $real_level > 1; $level--, $real_level--) {
					echo '</li></ul>'."\n";
				}				
				$route = i()->route(i()->query_string_to_way(i()->host.'/'.$v1['href']));
				if(empty($v1['mod']) && (!$route || !i()->user->access($route['id']))) {
					continue;
				}
				if($level < $v1['level']) {
					$class = $v1['level'] == 0 && $mod !== true ? ' class="_nav"' : '';
					echo '<ul'.$class.'><li>'."\n";
					$real_level++;
				} else {
					echo '</li><li>'."\n";
				}
				$level = $v1['level'];
				echo '<a href="'.i()->host.'/'.$v1['href'].'">'.$v1['name'].'</a>'."\n";
				if(!empty($v1['mod'])) {
					$this->nav(true);
				}
			}
		}
		for(; $real_level > 0; $real_level--) {
			echo '</li></ul>'."\n";
		}
	}

	function add_notice($type, $text, $redirect = false, $exit = true) {
		if($this->test) {
			echo $text;
			if($exit) {
				exit();
			}
		} else {
			if(!isset($_SESSION['admin_notice'])) {
				$_SESSION['admin_notice'] = array();
			}
			$_SESSION['admin_notice'][] = array('type' => $type, 'text' => $text);
			if($redirect) {
				i()->redirect($redirect);
			}
			if($exit) {
				exit();
			}
		}
	}

	function notice() {
		if(isset($_SESSION['admin_notice'])) {
			foreach($_SESSION['admin_notice'] as $k => $v) {
				echo '<div class="msg '.$v['type'].'">'.$v['text'].'</div><div style="height:20px;"></div>';
				unset($_SESSION['admin_notice'][$k]);
			}
		}
	}

	function nest($items) {		
		echo '<div class="nest">';
		for($i = 0; $i < count($items); $i++) {
			$items[$i] = is_array($items[$i]) ? '<a href="'.$items[$i][0].'">'.$items[$i][1].'</a>' : '<span>'.$items[$i].'</span>';
		}
		echo implode(' / ', $items);
		echo '</div>';
	}

	function mod() {		
		$mods = i()->directory_map(i()->root.'/install', false);
		i()->admin->nest(array('Модули'));
		echo '<div class="block"><h2>Доступные модули</h2><div class="container"><div class="body">';
		if(count($mods)) {			
			echo '<table class="table1">';
			echo '<thead><tr><td>Название</td><td>Действия</td></tr></thead>';
			foreach($mods as $k => $v) {
				if($k == 'admin') {
					continue;
				}
				echo '<tr><td>'.$k.'</td><td>';
				if(is_dir(i()->root.'/module/user/'.$k)) {
					echo ' <a href="'.i()->host.'/adm/mod/uninstall/'.$k.'">Удалить</a></td></tr>';
				} else {
					echo '<a href="'.i()->host.'/adm/mod/install/'.$k.'">Установить</a>';
				}				
			}
			echo '</table>';
		} else {
			echo  'Нет доступных модулей';
		}
		echo '</div></div></div>';
	}

	function install() {
		if(i()->install(i()->argv['path'][3])) {
			i()->admin->add_notice('suc', 'Установка завершена', $_SERVER['HTTP_REFERER']);
		} else {
			i()->admin->add_notice('notice', 'Нет установочного файла', $_SERVER['HTTP_REFERER']);
		}
	}

	function uninstall() {
		if(i()->uninstall(i()->argv['path'][3])) {
			i()->admin->add_notice('suc', 'Удаление завершено', $_SERVER['HTTP_REFERER']);
		} else {
			i()->admin->add_notice('notice', 'Нет файла удаления', $_SERVER['HTTP_REFERER']);
		}		
	}

}

?>