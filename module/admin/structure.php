<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

class structure {

	public $i;

	const PRE_REGEXP = 1;
	const PRE_AUTOINC = 2;
	const PRE_TIMESTAMP = 3;

	public $pre = array(
		self::PRE_REGEXP => 'regexp',
		self::PRE_AUTOINC => 'autoincrement',
		self::PRE_TIMESTAMP => 'timestamp'
	);	

	function save() {

		// init
		$fields = array();
		$add = i()->argv['path'][3] == 'add';
		$id_page = !$add ? intval(i()->argv['path'][3]) : 0;
		$id_tag = isset(i()->argv['path'][4]) ? i()->argv['path'][4] : 0;
		$page = i()->tree_item($id_page);
		if(!isset($page[$id_tag])) {
			$page[$id_tag] = array('id' => 0, 'way' => '', 'route' => '', 'tag' => '', 'actions' => '', 'file' => '', 'template' => '', 'id_parent' => 0, 'id_prototype' => 0, 'is_prototype' => 0, 'from_prototype' => '0000', 'mod' => '');
		}
		$fp_source = $page[$id_tag]['from_prototype'];
		$fp_dest = '0000';
		if((!$id_tag && !empty($_POST['id_prototype'])) || ($id_tag && isset($page[$this->tag_parent_name($id_tag)]) && $page[$this->tag_parent_name($id_tag)]['from_prototype'][3])) {
			foreach(array('way', 'file', 'actions', 'template') as $k => $v) {
				$fp_dest[$k] = !empty($_POST['from_prototype'][$v]) ? 1 : 0;
			}
		}
		$query_type = $add || ($id_tag && ($page[$id_tag]['id'] == 0 || $page[$id_tag]['proto']) && $fp_dest != '0111') ? 'insert' : 'update';
		$_POST['actions'] = isset($_POST['actions']) ? array_filter($_POST['actions'], 'strlen') : array();
		$_POST['is_prototype'] = !empty($_POST['is_prototype']) ? 1 : 0;
		$_POST['id_parent'] = isset($_POST['id_parent']) ? intval($_POST['id_parent']) : 0;
		$_POST['id_prototype'] = isset($_POST['id_prototype']) ? intval($_POST['id_prototype']) : 0;
		$_POST['file'] = isset($_POST['file']) ? $_POST['file'] : '';
		$_POST['template'] = !empty($_POST['template']) ? $_POST['template'] : '';
		$_POST['pre'] = isset($_POST['pre']) ? $_POST['pre'] : array();
		$_POST['regexp'] = isset($_POST['regexp']) ? $_POST['regexp'] : array();

		// check
		if($id_page && !isset($page[0])) {			
			i()->admin->add_notice('notice', 'Ошибка: Несуществующий элемент', $_SERVER['HTTP_REFERER']);
		}
		if($id_tag && !isset($page[$this->tag_parent_name($id_tag)])) {
			i()->admin->add_notice('notice', 'Ошибка: Нет предыдущей метки', $_SERVER['HTTP_REFERER']);
		}		
		if($page[$id_tag]['mod'] == 'admin') {
			i()->admin->add_notice('notice', 'Ошибка: Нельзя редактировать системные разделы', $_SERVER['HTTP_REFERER']);
		}
		if($id_tag && !preg_match('/^[a-zA-Z0-9_:]*$/', $id_tag)) {
			i()->admin->add_notice('notice', 'Ошибка: Корректное имя метки начинается с буквы или знака подчеркивания, за которым следует любое количество букв, цифр или знаков подчеркивания.', $_SERVER['HTTP_REFERER']);
		}
		foreach(array_keys(i()->argv) as $v) {
			$_POST[$v] = isset($_POST[$v]) ? $_POST[$v] : array();
			foreach($_POST[$v] as $k1 => $v1) {
				if(preg_match('/[;:]/', $v1)) {
					i()->admin->add_notice('notice', 'Ошибка: Корректное имя переменной маршрута не должно содержать символов ;:', $_SERVER['HTTP_REFERER']);
				}
			}
		}
		if((empty($page[0]['is_prototype']) && !$_POST['is_prototype']) && in_array(self::PRE_AUTOINC, $_POST['pre'])) {
			i()->admin->add_notice('notice', 'Ошибка: Нельзя выбрать autoincrement для непрототипа', $_SERVER['HTTP_REFERER']);
		}
		if((!empty($page[0]['is_prototype']) || $_POST['is_prototype']) && in_array(self::PRE_REGEXP, $_POST['pre'])) {
			i()->admin->add_notice('notice', 'Ошибка: Нельзя выбрать regexp для прототипа', $_SERVER['HTTP_REFERER']);
		}
		if(in_array(self::PRE_AUTOINC, $_POST['pre']) && in_array(self::PRE_REGEXP, $_POST['pre'])) {
			i()->admin->add_notice('notice', 'Ошибка: Нельзя одновременно выбрать autoincrement и regexp', $_SERVER['HTTP_REFERER']);
		}
	
			
		// редактирование прототипа
		if(!empty($page[0]['is_prototype'])) {

			if(!$id_tag) {
				// path
				$swap = array();
				$s_way = i()->string_to_way($page[$id_tag]['way']);
				foreach($s_way['path'] as $k => $v) {
					$w = explode(':', $v);
					if(count($w) > 1) {
						if(($s = array_search($w[1], $_POST['pre'])) === false) {
							i()->admin->add_notice('notice', 'Ошибка: Нельзя удалить существующий элемент автозаполнения из маршрута, это нарушит целостность дочерних маршрутов.', $_SERVER['HTTP_REFERER']);
						}
						$swap[$s] = $k;
						$_POST['path'][$s] = $v;
					}
				}
				$w_str = i()->way_to_string($_POST);
				if($page[$id_tag]['way'] != $w_str) {
					$fields['way'] = $w_str;
					$n_way = array_intersect_key($_POST, array('get' => 1, 'post' => 1, 'shell' => 1));
					$res = i()->db->query("SELECT * FROM `base_tree` WHERE `id_prototype` = ".$id_page);
					while($row = $res->fetch_assoc()) {
						if(!$row['from_prototype'][0]) {
							continue;
						}
						$w = i()->string_to_way($row['way']);
						$n_way['path'] = array();
						foreach($_POST['path'] as $k => $v) {
							if(isset($swap[$k])) {
								$n_way['path'][] = $w['path'][$swap[$k]];
							} elseif($v) {
								$n_way['path'][] = $v;
							}
						}
						$w = i()->db->real_escape_string(i()->way_to_string($n_way));
						$q = "UPDATE `base_tree` SET `way` = '{$w}' WHERE `id` = {$row['id']} LIMIT 1";						
						i()->db->query($q);
					}
				}
			}

			// file
			if($page[$id_tag]['file'] != $_POST['file']) {
				$fields['file'] = $_POST['file'] ? $_POST['file'] : NULL;
			}
			
			// actions
			if(array_filter(explode(';', $page[$id_tag]['actions']), 'strlen') != $_POST['actions']) {				
				if($diff = implode(';', array_filter(array_diff(explode(';', $page[$id_tag]['actions']), $_POST['actions']), 'strlen'))) {
					$res = i()->db->query("SELECT * FROM `base_tree` WHERE `id_prototype` = ".$id_page);
					while($row = $res->fetch_assoc()) {
						if($id_tag) {
							$row = i()->db->query("SELECT * FROM `base_tree` WHERE `id_parent` = {$row['id']} AND `tag` = '".i()->db->real_escape_string($id_tag)."'")->fetch_assoc();
						}
						if($row && $row['from_prototype'][2]) {
							$this->del_actions(array('id' => $row['id'], 'actions' => $diff));
						}
					}					
				}
				$fields['actions'] = implode(';', $_POST['actions']) ? implode(';', $_POST['actions']) : NULL;
			}

			// template
			if($page[$id_tag]['template'] != $_POST['template']) {
				// удалить все что ниже текущей метки
				foreach(array_reverse($page) as $k => $v) {
					if($k && (!$id_tag || i()->is_level_child($k, $id_tag))) {
						$this->del_node($v);
					}
				}				
				$res = i()->db->query("SELECT * FROM `base_tree` WHERE `id_prototype` = ".$id_page);
				while($row = $res->fetch_assoc()) {
					$items = i()->tree_item($row['id'], false);
					// проверить есть ли элементы выше или равны текущей метке, в которых отменяется наследование
					foreach($items as $k => $v) {
						if(!$v['from_prototype'][3] && (!$k || $k == $id_tag || i()->is_level_child($id_tag, $k))) {
							continue 2;
						}
					}
					// если таких элементов не найдено, удалить все что ниже текущей метки			
					foreach(array_reverse($items) as $k => $v) {
						if($k && (!$id_tag || i()->is_level_child($k, $id_tag))) {
							$this->del_node($v);
						}
					}
				}
				$fields['template'] = $_POST['template'] ? $_POST['template'] : NULL;
			}

		} else {

			// все остальное
			if(!$id_tag) {

				if($add && $_POST['is_prototype']) {
					$fields['is_prototype'] = 1;
				}

				// parent
				if($page[$id_tag]['id_parent'] != $_POST['id_parent']) {
					// проверим не наследуем ли мы от самих себя и элемент от которого наследуем существует
					$id_parent = $_POST['id_parent'];
					while($id_parent) {
						$row = i()->db->query("SELECT * FROM `base_tree` WHERE `id` = {$id_parent} AND `tag` IS NULL")->fetch_assoc();
						if(empty($row['id']) || $row['id'] == $id_page || $row['is_prototype']) {
							i()->admin->add_notice('notice', 'Ошибка: Недопустимый выбор родительского элемента.', $_SERVER['HTTP_REFERER']);								
						}
						$id_parent = !empty($row['id_parent']) ? $row['id_parent'] : 0;							
					}
					$fields['id_parent'] = $_POST['id_parent'];
				}

				// prototype
				if($page[$id_tag]['id_prototype'] != $_POST['id_prototype']) {
					$fields['id_prototype'] = $_POST['id_prototype'];
				}

				// path
				if($fp_dest[0]) {
					if(!$fp_source[0] || $page[$id_tag]['id_prototype'] != $_POST['id_prototype']) {
						$fields['way'] = i()->way_to_string($this->create_way_from_proto($_POST['id_prototype']));
					}
				} else {
					$d_way = $this->create_way($_POST);
					$d_way_str = i()->way_to_string($d_way['data']);
					if($d_way['type'] == 'route') {
						$fields['way'] = NULL;
						$fields['route'] = $d_way_str;
					} else {
						$fields['route'] = NULL;
						$fields['way'] = $d_way_str;
					}
				}					
			}

			// from_prototype
			$fields['from_prototype'] = $fp_dest;

			// file			
			if($fp_dest[1]) {
				$fields['file'] = NULL;
			} else {
				$fields['file'] = $_POST['file'] ? $_POST['file'] : NULL;
			}

			// actions
			$source_actions = $page[$id_tag]['actions'];
			if($fp_dest[2]) {
				$id_prototype = $id_tag ? $page[0]['id_prototype'] : $_POST['id_prototype'];
				$items = i()->tree_item($id_prototype, false);
				$dest_actions = !empty($items[$id_tag]) ? $items[$id_tag]['actions'] : '';
				$fields['actions'] = NULL;
			} else {
				$dest_actions = implode(';', $_POST['actions']);
				$fields['actions'] = $dest_actions ? $dest_actions : NULL;
			}
			if($source_actions && !$page[$id_tag]['proto']) {
				$diff = implode(';', array_filter(array_diff(explode(';', $source_actions), explode(';', $dest_actions)), 'strlen'));
				$this->del_actions(array('id' => $page[$id_tag]['id'], 'actions' => $diff));
			}

			// template
			$del_tags = false;
			if($fp_dest[3]) {
				if(!$fp_source[3] || (!$id_tag && $page[$id_tag]['id_prototype'] != $_POST['id_prototype'])) {
					$del_tags = true;
				}
				$fields['template'] = NULL;
			} else {
				if($fp_source[3] || $page[$id_tag]['template'] != $_POST['template']) {
					$del_tags = true;					
				}
				$fields['template'] = $_POST['template'] ? $_POST['template'] : NULL;
			}			
			if($del_tags) {
				foreach(array_reverse($page) as $k => $v) {
					if($k && !$v['proto'] && (!$id_tag || i()->is_level_child($v['tag'], $id_tag))) {
						$this->del_node($v);
					}
				}
			}
		}
		$fields = i()->db->escape($fields);		
		$set = array();
		foreach($fields as $k => $v) {
			$set[] = "`".$k."` = ".$v;
		}
		if($query_type == 'insert') {
			if($id_tag) {
				$set[] = "`tag` = '".i()->db->real_escape_string($id_tag)."', `id_parent` = {$id_page}";
			}
			$q = "INSERT INTO `base_tree` SET ".implode(', ', $set);
		} elseif(count($set)) {
			$q = "UPDATE `base_tree` SET ".implode(', ', $set)." WHERE `id` = {$page[$id_tag]['id']}";
		}
		i()->db->query($q);
		if(i()->db->affected_rows != -1) {
			$ret = $add ? i()->host.'/adm/structure/edit/'.i()->db->insert_id : $_SERVER['HTTP_REFERER'];
			i()->admin->add_notice('suc', 'Успешно сохранено', $ret);
		} else {
			if(i()->db->errno == 1062 && strpos(i()->db->error, 'UK_base_tree_way') !== false) {
				i()->admin->add_notice('notice', 'Ошибка: Такой маршрут уже существует', $_SERVER['HTTP_REFERER']);	
			} else {
				i()->admin->add_notice('err', 'System: '.i()->db->errno, $_SERVER['HTTP_REFERER']);
			}
		}		
	}

	function add_edit() {
		$add = i()->argv['path'][2] == 'add';
		$id_page = !$add ? intval(i()->argv['path'][3]) : 0;
		$id_tag = isset(i()->argv['path'][4]) ? i()->argv['path'][4] : '';
		$items = $add ? array() : i()->tree_item($id_page);
		$name = $id_tag ? $id_tag : 0;
		$route = isset($items[$name]) ? $items[$name] : array();
		$way = isset($route['way_route']) ? i()->string_to_way($route['way_route']) : array();
		$disabled_from_proto = true;
		if(!$add) {
			if(!isset($items[0])) {
				i()->admin->add_notice('err', 'System: '.__FILE__.', '.__LINE__, $_SERVER['HTTP_REFERER']);
			}
			if($id_tag && !isset($items[$this->tag_parent_name($id_tag)])) {
				i()->admin->add_notice('notice', 'Ошибка: Нет предыдущей метки', $_SERVER['HTTP_REFERER']);
			}
			$disabled_from_proto = !$id_tag ? !$items[0]['id_prototype'] : !$items[$this->tag_parent_name($id_tag)]['from_prototype'][3];
		}
		$nest = array(
			array(i()->host.'/adm/structure', 'Структура'),
		);
		if($add) {
			$nest[] = 'Новая страница';
			i()->admin->nest($nest);
		} elseif($id_tag) {
			$nest[] = array(i()->host.'/adm/structure/edit/'.$id_page, i()->route_string($items[0]));
			$tags = explode(':', $id_tag);
			for($i = 0; $i < count($tags); $i++) {
				if($i < count($tags) - 1) {
					$nest[] = array(i()->host.'/adm/structure/edit/'.$id_page.'/'.implode(':', array_slice($tags, 0, $i + 1)), $tags[$i]);
				} else {
					$nest[] = $tags[$i];
				}
			}
			i()->admin->nest($nest);
		} else {
			$nest[] = i()->route_string($items[0]);
			i()->admin->nest($nest);
		}		
		echo '<script type="text/javascript">';
		echo "Global.struct_pos = ".json_encode($route).";\n";
		echo "Global.struct_id = {$id_page};\n";
		echo "Global.struct_tag = '{$id_tag}';\n";
		if(!empty($route['id_parent'])) {
			echo "Global.tree_struct_parent_open = ".json_encode(i()->get_parents($route['id_parent'])).";\n";
			echo "Global.tree_struct_parent_select = ['".intval($route['id_parent'])."'];\n";
		}
		if(!empty($route['id_prototype'])) {
			echo "Global.tree_struct_prototype_select = ['".intval($route['id_prototype'])."'];\n";
		}
		echo '</script>';
		echo '<form id="adm_structure_form" action="'.i()->host.'/adm/structure/save/'.($add ? 'add' : $id_page).($id_tag ? '/'.$id_tag : '').'" method="post">';
		if(!$id_tag) {
			if($add) {
				echo '<div style="padding:10px"><input type="checkbox" name="is_prototype" value="1" /> <b>Прототип</b></div>';
			}
			if(empty($route['is_prototype'])) {
			?>
			<div class="block" style="display:block; margin-bottom:15px;"><h2>Наследование</h2><div class="container"><div class="body">
			<div>			
				<a href="#" id="tree_struct_parent_link" class="js_link">Родитель</a>
				<div id="tree_struct_parent_block" class="tree_selected"></div>
			</div>
			<div style="padding-top:10px;">
				<a href="#" id="tree_struct_prototype_link" class="js_link">Прототип</a>
				<div id="tree_struct_prototype_block" class="tree_selected"></div>
			</div>
			</div></div></div>
			<?php
			}
			echo '<div id="st_wrap_way" class="block" style="display:block; margin-bottom:15px;"><h2>Маршрут <input type="checkbox" name="from_prototype[way]"'.($disabled_from_proto ? ' disabled="disabled"' : (!empty($route['from_prototype'][0]) ? ' checked="checked"' : '')).' /></h2><div class="container"><div class="body">';
			echo '<div class="new" style="display:none;"><div>';
			echo '<div><input class="input" style="width:500px;" name="" type="text" value="" /> ';
			echo '<select name="pre[]" disabled="disabled" class="input hidden">';
			echo '<option value="0"></option>';
			echo '<option value="1">regexp</option>';
			echo '<option value="2">autoincrement</option>';
			echo '<option value="3">timestamp</option>';
			echo '</select>';
			echo ' <input class="btn" type="button" value="&mdash;" onclick="$(this).closest(\'div\').remove();" /></div>';
			echo '</div></div>';
			foreach(array('domain', 'path', 'shell', 'get', 'post') as $v) {
				echo '<div id="st_wrap_'.$v.'">';
				echo '<div style="font-weight:bold; padding:10px 0 5px 0;">'.ucfirst($v).': <input class="btn" type="button" value="+" onclick="admin.st_input_one_field($(\'#st_wrap_'.$v.'\'), \''.$v.'\')" /></div>';
				if(isset($way[$v])) {
					echo '<script type="text/javascript">'."\n";
					foreach($way[$v] as $v1) {
						$v1 = htmlspecialchars(str_replace('\\', '\\\\', $v1));						
						echo "admin.st_input_one_field($('#st_wrap_".$v."'), '".$v."', '".$v1."');\n";
					}
					echo '</script>';
				}
				echo '</div>';
			}
			echo '</div></div></div>';
		}
		echo '<div class="block" style="display:block; margin-bottom:15px;"><h2>Файл <input type="checkbox" name="from_prototype[file]"'.($disabled_from_proto ? ' disabled="disabled"' : (!empty($route['from_prototype'][1]) ? ' checked="checked"' : '')).' /></h2><div class="container"><div class="body">';
		echo '<div style="padding-top:5px;"><input class="input" style="width:500px;" name="file" type="text" value="'.(!empty($route['file']) ? $route['file'] : '').'" /></div>';
		echo '</div></div></div>';
		
		i()->module_map();
		echo '<div class="block" style="display:block; margin-bottom:15px;"><h2>Действия: <input type="checkbox" name="from_prototype[actions]"'.($disabled_from_proto ? ' disabled="disabled"' : (!empty($route['from_prototype'][2]) ? ' checked="checked"' : '')).' /></h2><div class="container"><div class="body" id="st_wrap_actions">';
		echo '<div><div style="padding:10px 0 5px 0;">Добавить: <input class="btn" type="button" value="+" onclick="admin.st_actions_field($(\'#st_wrap_actions\'))" /></div>';
		echo '<div class="new" style="display:none;"><div>';
		echo '<select name="actions[]" disabled="disabled" class="input hidden">';
		echo '<option value="0">-</option>';
		foreach(i()->modules as $k => $v) {
			if(property_exists(i()->$k, 'export')) {
				$name = i()->$k->export['name'];
				foreach(i()->$k->export['actions'] as $k1 => $v1) {
					$param = $route && !$route['is_prototype'] && isset($v1['form']) && in_array($k.':'.$k1, array_filter(explode(';', $route['actions']), 'strlen')) ? ' class="param"' : '';
					echo '<option'.$param.' value="'.$k.':'.$k1.'">'.$name.' - '.$v1['name'].'</option>'."\n";
				}
			}
		}
		echo '</select> <input class="btn" type="button" value="-" onclick="$(this).closest(\'div\').remove();" />';
		echo ' &nbsp;<a href="'.i()->host.'/adm/structure/param/'.$id_page.($id_tag ? '/'.$id_tag.':' : '').'">Редактировать</a>';
		echo '</div></div>';
		echo '<script type="text/javascript">'."\n";
		if(isset($route['actions'])) {
			$actions = explode(';', $route['actions']);
			foreach($actions as $k => $v) {
				echo "admin.st_actions_field($('#st_wrap_actions'), '".$v."');\n";
			}
		}
		echo '</script>';
		echo '</div>';
		echo '</div></div></div>';
		echo '<div class="block" style="display:block; margin-bottom:15px;"><h2>Шаблон: <input type="checkbox" name="from_prototype[template]"'.($disabled_from_proto ? ' disabled="disabled"' : (!empty($route['from_prototype'][3]) ? ' checked="checked"' : '')).' /></h2><div class="container"><div class="body">';
		$m = i()->directory_map(i()->root.'/view');
		echo '<div style="padding-top:5px;"><select class="input" name="template">';
		echo '<option value="0">-</option>';
		foreach($m as $v) {
			$name = trim(str_replace(i()->root, '', $v), '/');
			$selected = isset($route['template']) && $route['template'] == $name ? ' selected="selected"' : '';
			echo '<option'.$selected.' value="'.$name.'">'.$name.'</option>';
		}
		echo '</select></div>';
		
		if(isset($route['template'])) {
			i()->tag->parse = true;
			ob_start();
			include(i()->root.'/'.$route['template']);
			ob_end_clean();
			i()->tag->parse = false;
			if(i()->tag->tags) {
				echo '<div style="font-weight:bold; padding-top:10px;">';
				echo '<table>';				
				echo '<div><b>Метки:</b></div>';
				foreach(i()->tag->tags as $v) {					
					echo '<div><a href="'.i()->host.'/adm/structure/edit/'.$id_page.'/'.($id_tag ? $id_tag.':' : '').$v.'">'.$v.'</a></div>';
				}
				echo '</table></div>';
			}
		}
		echo '</div></div></div>';
		echo '<div style="padding:10px 0 0 0;"><input type="submit" value="Сохранить" class="btn action" /></div>';
		echo '</form>';
		echo '<div class="tree popup" style="display:none" id="tree_struct_parent"></div>';
		echo '<div class="tree popup" style="display:none" id="tree_struct_prototype"></div>';
	}

	public function create_way($r) {
		$ret = array_intersect_key($r, i()->argv);
		$pre_unit = array();
		$is_proto = !empty($r['is_prototype']) ? 1 : 0;
		$pre = isset($r['pre']) ? $r['pre'] : array();		
		if(!isset($r['path'])) {
			return array('type' => (in_array(1, $pre) !== false ? 'route' : 'way'), 'data' => $ret);
		}
		foreach($ret['path'] as $k => &$v) {
			if(!empty($pre[$k])) {				
				if(!isset(i()->structure->pre[$pre[$k]])) {
					unset($ret['path'][$k]);
					continue;
				}
				if($pre[$k] > 1 && in_array($pre[$k], $pre_unit)) {
					unset($ret['path'][$k]);
					continue;
				}
				$pre_unit[] = $pre[$k];
				switch($pre[$k]) {
					case 1:
						if(!$v) {
							unset($ret['path'][$k]);
							continue 2;
						}
						$v = 'p:'.structure::PRE_REGEXP.':'.$v;
					break;
					case 2:
						$v = 'p:'.structure::PRE_AUTOINC.':0';	
					break;
					case 3:						
						$v = $is_proto ? 'p:'.structure::PRE_TIMESTAMP : time();
					break;
				}
			} elseif(!$v) {
				unset($ret['path'][$k]);
			}
		}
		return array('type' => (in_array(1, $pre) !== false ? 'route' : 'way'), 'data' => $ret);
	}

	public function create_way_from_proto($id) {
		$ret = array();		
		$row = i()->db->query("SELECT `way` FROM `base_tree` WHERE `id` = ".intval($id))->fetch_assoc();
		$a = i()->string_to_way($row['way']);
		$ret = $a;
		$ret['path'] = array();
		foreach($a['path'] as $k => &$v) {
			$v1 = explode(':', $v);
			if(count($v1) > 1) {
				switch($v1[1]) {
					case 2:
						$autoinc = intval($v1[2]) + 1;
						$v = 'p:2:'.$autoinc;
						$ret['path'][] = $autoinc;
					break;
					case 3:
						$ret['path'][] = time();
					break;
				}
			} else {
				$ret['path'][] = $v;
			}
		}
		$new = i()->way_to_string($a);
		if($row['way'] != $new) {
			 i()->db->query("UPDATE `base_tree` SET `way` = '".i()->db->real_escape_string($new)."' WHERE `id` = {$id} LIMIT 1");
		}
		return $ret;
	}

	function branch() {
		$id = intval(i()->argv['post']['id']);
		$ret = array();
		switch(i()->argv['post']['type']) {
			case 'structure':
				$q = "SELECT *, IF(`way` IS NOT NULL, `way`, `route`) AS `way_route` FROM `base_tree` WHERE `id_parent` = {$id} AND `tag` IS NULL AND `mod` != 'admin' ORDER BY `is_prototype` DESC, IF(`way` IS NOT NULL, `way`, `route`) ASC";
			break;
			case 'page':
				$q = "SELECT *, IF(`way` IS NOT NULL, `way`, `route`) AS `way_route` FROM `base_tree` WHERE `id_parent` = {$id} AND `tag` IS NULL AND `is_prototype` = 0 ORDER BY IF(`way` IS NOT NULL, `way`, `route`) ASC";
			break;
			case 'prototype':
				$q = "SELECT *, IF(`way` IS NOT NULL, `way`, `route`) AS `way_route` FROM `base_tree` WHERE `id_parent` = {$id} AND `tag` IS NULL AND `is_prototype` = 1 ORDER BY IF(`way` IS NOT NULL, `way`, `route`) ASC";
			break;
		}
		$res = i()->db->query($q);
		while($row = $res->fetch_assoc()) {
			$count = i()->db->query("SELECT COUNT(*) AS `count` FROM `base_tree` WHERE `id_parent` = {$row['id']} AND `tag` IS NULL AND `is_prototype` = 0")->fetch_assoc();
			$ret[] = array(
				'id' => $row['id'],
				'is_prototype' => $row['is_prototype'],
				'name' => (!empty($row['is_prototype']) ? 'Proto: ' : '').i()->route_string($row),
				'count' => $count['count']
			);
		}
		echo json_encode($ret);
	}

	function tree_main_state() {
		$id = intval(i()->argv['post']['id']);
		if(!$id) {
			return;
		}
		if(!isset($_SESSION['admin_tree'])) {
			$_SESSION['admin_tree'] = array();
		}
		switch(i()->argv['post']['type']) {
			case 'open':
				$_SESSION['admin_tree'][$id] = 1;
			break;
			case 'close':
				foreach(i()->get_children($id) as $v) {
					if(isset($_SESSION['admin_tree'][$v])) {
						unset($_SESSION['admin_tree'][$v]);
					}
				}
				if(isset($_SESSION['admin_tree'][$id])) {
					unset($_SESSION['admin_tree'][$id]);
				}
			break;
		}
	}

	function content() {
		if(!isset($_SESSION['admin_tree'])) {
			$_SESSION['admin_tree'] = array();
		}
		i()->admin->nest(array('Структура'));
		echo '<script type="text/javascript">';
		echo "Global.tree_struct_main_open = ".json_encode(array_keys($_SESSION['admin_tree'])).";\n";
		echo '</script>';
		echo '<div class="tree" id="tree_main"></div>';
	}
	
	function del_recursion($id) {
		$res = i()->db->query("SELECT `id` FROM `base_tree` WHERE `id_parent` = {$id} AND `tag` IS NULL");
		while($row = $res->fetch_assoc()) {
			$this->del_recursion($row['id']);
		}
		foreach(array_reverse(i()->tree_item($id)) as $v) {
			if(!$v['proto']) {
				$this->del_node($v);
			}
		}
	}
	
	function del() {
		$id = intval(i()->argv['path'][3]);
		$items = i()->tree_item($id, false);
		if(!isset($items[0])) {
			i()->admin->add_notice('err', 'System: '.__FILE__.', '.__LINE__, $_SERVER['HTTP_REFERER']);
		}
		if($items[0]['mod'] == 'admin') {
			i()->admin->add_notice('notice', 'Ошибка: Нельзя удалять системные разделы', $_SERVER['HTTP_REFERER']);
		}
		if($items[0]['is_prototype']) {
			$row = i()->db->query("SELECT COUNT(*) as `count` FROM `base_tree` WHERE `id_prototype` = {$items[0]['id']}")->fetch_assoc();
			if(!empty($row['count'])) {
				i()->admin->add_notice('notice', 'Ошибка: Сначало удалите страницы, наследующие этот прототип', $_SERVER['HTTP_REFERER']);
			}
		}
		$this->del_recursion($id);
		i()->admin->add_notice('suc', 'Удалено успешно', $_SERVER['HTTP_REFERER']);
	}

	function del_node($row) {
		$this->del_actions($row);
		$q = "DELETE FROM `base_tree` WHERE `id` = {$row['id']} LIMIT 1";		
		i()->db->query($q);
	}

	function del_actions($row) {
		if(!empty($row['actions'])) {			
			$row['actions'] = explode(';', $row['actions']);
			foreach($row['actions'] as $v) {
				list($class, $method) = explode(':', $v);
				if(isset(i()->$class->export['actions'][$method]['del'])) {					
					i()->$class->{i()->$class->export['actions'][$method]['del']}($row['id']);
				}
			}
		}
	}
	

	function tag_parent_name($id_tag) {
		return $id_tag && strrpos($id_tag, ':') ? preg_replace('/\:[^\:]+$/', '', $id_tag) : 0;
	}
	
	function save_param() {
		$id_page = intval(i()->argv['path'][3]);
		$id_tag = isset(i()->argv['path'][5]) ? i()->argv['path'][4] : '';
		$action = isset(i()->argv['path'][5]) ? i()->argv['path'][5] : i()->argv['path'][4];
		$items = i()->tree_item($id_page);
		if(!isset($items[0]) || ($id_tag && !isset($items[$id_tag]))) {
			i()->admin->add_notice('err', 'System: '.__FILE__.', '.__LINE__, $_SERVER['HTTP_REFERER']);
		}
		if($id_tag) {
			if($items[$id_tag]['proto']) {
				$fields = array();
				$fields['id_parent'] = $id_page;
				$fields['tag'] = $id_tag;
				$fields['from_prototype'] = '0111';
				$fields = i()->db->escape($fields);
				$set = array();
				foreach($fields as $k => $v) {
					$set[] = "`".$k."` = ".$v;
				}
				i()->db->query("INSERT INTO `base_tree` SET ".implode(', ', $set));
				$id = i()->db->insert_id;
			} else {
				$id = $items[$id_tag]['id'];
			}
		} else {
			$id = $items[0]['id'];
		}
		list($class, $method) = explode(':', $action);
		if(isset(i()->{$class}->export['actions'][$method]['save'])) {
			i()->{$class}->{i()->{$class}->export['actions'][$method]['save']}($id);
		}
		i()->admin->add_notice('suc', 'Успешно сохранено', $_SERVER['HTTP_REFERER']);
	}
	
	function param() {
		$id_page = intval(i()->argv['path'][3]);
		$id_tag = isset(i()->argv['path'][5]) ? i()->argv['path'][4] : '';
		$action = isset(i()->argv['path'][5]) ? i()->argv['path'][5] : i()->argv['path'][4];		
		$items = i()->tree_item($id_page);
		if(!isset($items[0]) || ($id_tag && !isset($items[$id_tag]))) {
			i()->admin->add_notice('err', 'System: '.__FILE__.', '.__LINE__, $_SERVER['HTTP_REFERER']);
		}
		if($id_tag) {
			$id = $items[$id_tag]['proto'] ? 0 : $items[$id_tag]['id'];
		} else {
			$id = $id_page;
		}
		list($class, $method) = explode(':', $action);
		$nest = array(
			array(i()->host.'/adm/structure', 'Структура'),
		);
		if($id_tag) {
			$nest[] = array(i()->host.'/adm/structure/edit/'.$id_page, i()->route_string($items[0]));
			$tags = explode(':', $id_tag);
			for($i = 0; $i < count($tags); $i++) {
				$nest[] = array(i()->host.'/adm/structure/edit/'.$id_page.'/'.implode(':', array_slice($tags, 0, $i + 1)), $tags[$i]);				
			}
			$nest[] = 'Редактирование действия';
			i()->admin->nest($nest);
		} else {
			$nest[] = array(i()->host.'/adm/structure/edit/'.$id_page, i()->route_string($items[0]));
			$nest[] = 'Редактирование действия';
			i()->admin->nest($nest);
		}
		if(isset(i()->{$class}->export['actions'][$method]['form'])) {
			i()->{$class}->{i()->{$class}->export['actions'][$method]['form']}($id, i()->host.'/adm/structure/save_param/'.$id_page.'/'.($id_tag ? $id_tag.'/' : '').$action);
		}
	}

}

?>