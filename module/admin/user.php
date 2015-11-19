<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

class user {

	public $export = array(
		'name' => 'Админ',
		'actions' => array(
			'auth' => array('name' => 'Авторизация')			
		)		
	);

	public $access_type = array(
		'allow' => 'Запретить все разделы кроме разрешенных',
		'disallow' => 'Разрешить все разделы кроме запрещенных'
	);

	public $access = false;
	public $load = array();

	public $admin = 1;

	function __construct() {
	}

	function group() {
		$page = !empty(i()->argv['path'][1]) ? intval(str_replace('p', '', i()->argv['path'][1])) : 1;
		$page = $page < 1 ? 1 : $page;
		$limit = 10;
		$from = ($page - 1) * $limit;
		$row = i()->db->query("
			SELECT COUNT(*) AS `count`
			FROM `base_group`
		")->fetch_assoc();
		$count = !empty($row['count']) ? $row['count'] : 0;
		$res = i()->db->query("
			SELECT name, id
			FROM `base_group`
			ORDER BY `name`
			LIMIT {$from}, {$limit}
		");
		i()->admin->nest(array('Группы'));		
		echo '<div class="block"><h2>Группы</h2><div class="container"><div class="body">';
		echo '<table class="table1">';
		echo '<thead><tr><td>Имя</td><td>Действия</td></tr></thead>';
		while($row = $res->fetch_assoc()) {
			echo '<tr><td><a href="'.i()->host.'/adm/access/user_group/'.$row['id'].'">'.$row['name'].'</a></td><td><a href="'.i()->host.'/adm/access/group/edit/'.$row['id'].'">Редактировать</a> <a href="'.i()->host.'/adm/access/group/del/'.$row['id'].'">Удалить</a></td></tr>';
		}		
		echo '</table>';
		i()->paginator->href = i()->host.'/group';
		i()->paginator->count = $count;
		i()->paginator->page = $page;
		i()->paginator->name_param = 'p';
		i()->paginator->path_param = true;
		i()->paginator->on_page = $limit;
		i()->paginator->class_name = 'btn';
		if($pages = i()->paginator->init()) {
			echo '<div class="pagination" style="padding-top:20px;">'.$pages.'</div>';
		}
		echo '</div></div></div>';
	}

	function user() {
		$group = false;
		$where_group = "";
		if(i()->argv['path'][2] == 'user_group') {
			$page = !empty(i()->argv['path'][4]) ? intval(str_replace('p', '', i()->argv['path'][4])) : 1;
			$id_group = intval(i()->argv['path'][3]);
			$group = i()->db->query("SELECT * FROM `base_group` WHERE `id` = {$id_group}")->fetch_assoc();
			$where_group = " WHERE a.`id_group` = {$id_group}";
			i()->admin->nest(array(array(i()->host.'/adm/access/group', 'Группы'), 'Пользователи группы '.$group['name']));
		} else {
			$page = !empty(i()->argv['path'][3]) ? intval(str_replace('p', '', i()->argv['path'][3])) : 1;
			i()->admin->nest(array('Пользователи'));
		}
		$page = $page < 1 ? 1 : $page;
		$limit = 10;
		$from = ($page - 1) * $limit;
		$row = i()->db->query("
			SELECT COUNT(*) AS `count`
			FROM `base_user` AS a
			{$where_group}
		")->fetch_assoc();
		$count = !empty($row['count']) ? $row['count'] : 0;
		$res = i()->db->query("
			SELECT a.name AS user_name, a.id AS user_id, b.name AS group_name, b.id AS group_id
			FROM `base_user` AS a 
			LEFT JOIN `base_group` AS b ON a.id_group = b.id
			{$where_group}
			ORDER BY a.`name`
			LIMIT {$from}, {$limit}
		");
		echo '<div class="block"><h2>Пользователи'.($group ? ', группа '.$group['name'] : '').'</h2><div class="container"><div class="body">';
		echo '<table class="table1">';
		echo '<thead><tr><td>Имя</td><td>Группа</td><td>Действия</td></tr></thead>';
		while($row = $res->fetch_assoc()) {			
			echo '<tr><td>'.$row['user_name'].'</td><td><a href="'.i()->host.'/adm/access/user_group/'.$row['group_id'].'">'.$row['group_name'].'</a></td><td><a href="'.i()->host.'/adm/access/user/edit/'.$row['user_id'].'">Редактировать</a>'.($row['user_id'] != $this->admin ? ' <a href="'.i()->host.'/adm/access/user/del/'.$row['user_id'].'">Удалить</a>' : '').'</td></tr>';
		}		
		echo '</table>';
		i()->paginator->href = $group ? i()->host.'/'.implode('/', array_slice(i()->argv['path'], 0, 4)) : i()->host.'/'.implode('/', array_slice(i()->argv['path'], 0, 3));
		i()->paginator->count = $count;
		i()->paginator->page = $page;
		i()->paginator->name_param = 'p';
		i()->paginator->path_param = true;
		i()->paginator->on_page = $limit;
		i()->paginator->class_name = 'btn';
		if($pages = i()->paginator->init()) {
			echo '<div class="pagination" style="padding-top:20px;">'.$pages.'</div>';
		}
		echo '</div></div></div>';
	}

	function user_group_save() {
		$group = i()->argv['path'][2] == 'group';
		$new = i()->argv['path'][4] == 'new';
		$id = !$new ? intval(i()->argv['path'][4]) : 0;
		$table = $group ? 'base_group' : 'base_user';
		$field = $group ? 'id_group' : 'id_user';
		$items = array();
		if(empty($_POST['name'])) {
			i()->admin->add_notice('err', 'Имя не может быть пустым', $_SERVER['HTTP_REFERER']);
		}
		if($new && !$group && empty($_POST['pass'])) {
			i()->admin->add_notice('err', 'Пароль не может быть пустым', $_SERVER['HTTP_REFERER']);
		}
		if($new && i()->db->query("SELECT * FROM {$table} WHERE `name` = '".i()->db->real_escape_string($_POST['name'])."'")->num_rows) {
			i()->admin->add_notice('err', 'Имя существует', $_SERVER['HTTP_REFERER']);
		}
		if(!empty($_POST['pass'])) {
			$_POST['pass'] = md5($_POST['pass']);
		} else {
			unset($_POST['pass']);
		}		
		if(isset($_POST['items'])) {
			if($_POST['type'] != 0) {
				foreach($_POST['items'] as $v) {
					$items[] = intval($v);
				}
			}
			unset($_POST['items']);
		}
		$fields = i()->db->escape($_POST);
		foreach($fields as $k => $v) {
			$set[] = "`".$k."` = ".$v;
		}
		if($new) {
			i()->db->query("INSERT INTO `{$table}` SET ".implode(', ', $set));
			$id = i()->db->insert_id;
		} elseif($id) {
			i()->db->query("UPDATE `{$table}` SET ".implode(', ', $set)." WHERE `id` = ".$id);
		}
		i()->db->query("DELETE FROM `base_rule` WHERE {$field} = ".$id);		
		foreach($items as $v) {
			i()->db->query("INSERT INTO `base_rule` SET {$field} = ".$id.", id_page = ".$v);
		}
		i()->admin->add_notice('suc', 'Успешно сохранено', $_SERVER['HTTP_REFERER']);
	}


	function user_group_edit() {
		$group = i()->argv['path'][2] == 'group';
		$new = i()->argv['path'][3] == 'new';
		$id = !$new ? intval(i()->argv['path'][4]) : 0;
		$table = $group ? 'base_group' : 'base_user';
		$field = $group ? 'id_group' : 'id_user';
		$to_open = array();
		$to_select = array();
		$action = i()->argv['path'];
		$action[3] = 'save';
		if($new) {
			$action[4] = 'new';
		}
		$action = i()->host.'/'.implode('/', $action);
		$r = $new ? array() : i()->db->query("SELECT * FROM `{$table}` WHERE id = {$id}")->fetch_assoc();
		if($id) {
			$res = i()->db->query("SELECT b.id_page FROM `{$table}` AS a INNER JOIN `base_rule` AS b ON b.{$field} = a.id WHERE a.id = {$id}");
			while($row = $res->fetch_assoc()) {				
				$to_select[] = $row['id_page'];
				foreach(i()->get_parents($row['id_page']) as $v1) {
					if(!in_array($v1, $to_open)) {
						$to_open[] = $v1;
					}
				}				
			}
		}
		if($group) {
			i()->admin->nest(array(array(i()->host.'/adm/access/group', 'Группы'), ($new ? 'Новая группа' : 'Редактирование группы '.$r['name']))); 
		} else {
			i()->admin->nest(array(array(i()->host.'/adm/access/user', 'Пользователи'), ($new ? 'Новый пользователь' : 'Редактирование пользователя '.$r['name']))); 
		}
?>
		<script type="text/javascript">
		Global.tree_user_access_open = <?php echo json_encode($to_open); ?>;
		Global.tree_user_access_select = <?php echo json_encode($to_select); ?>;
		</script>
		<div class="block"><h2><?php echo ($group ? ($new ? 'Новая группа' : 'Редактирование группы '.$r['name']) : ($new ? 'Новый пользователь' : 'Редактирование пользователя '.$r['name'])); ?></h2><div class="container"><div class="body">		
		<form method="post" action="<?php echo $action; ?>">
		<table class="form">
		<tr>
		<td>Имя: </td><td><input class="input" type="text" name="name" value="<?php if(isset($r['name'])) echo $r['name']; ?>" /></td></tr>
<?php
		if(!$group) {
			if($new) {
				echo '<tr><td>Пароль:</td><td><input class="input" type="password" name="pass" /></td></tr>';
			} else {
				echo '<tr><td><a href="#" class="js_link" onclick="$(\'#access_pass_input\').prop(\'disabled\', !$(\'#access_pass_input\').prop(\'disabled\'));">Сменить пароль:</a></td><td> <input class="input" id="access_pass_input" disabled="disabled" type="password" name="pass" /></td></tr>';
			}
			$res = i()->db->query("SELECT * FROM `base_group` ORDER BY `name`");
			if($res->num_rows) {
				echo '<tr><td>Группа:</td><td><select class="input" name="id_group">';
				echo '<option value="0">-</option>';
				while($row = $res->fetch_assoc()) {
					$selected = isset($r['id_group']) && $r['id_group'] == $row['id'] ? ' selected="selected"' : '';
					echo '<option value="'.$row['id'].'"'.$selected.'>'.$row['name'].'</option>';
				}
				echo '</select></tr>';
			}
		}
?>
		<tr><td>Тип:</td><td>
		<select class="input" name="type" id="access_type">
			<option value="0"<?php if(isset($r['type']) && $r['type'] == 0) echo ' selected="selected"'; ?>>Не выбран</option>
			<option value="1"<?php if(isset($r['type']) && $r['type'] == 1) echo ' selected="selected"'; ?>>Разрешить все кроме</option>
			<option value="2"<?php if(isset($r['type']) && $r['type'] == 2) echo ' selected="selected"'; ?>>Запретить все кроме</option>
		</select>
		</td></tr>
		<tr><td colspan="2"><a href="#" id="tree_user_access_link" class="js_link">Разрешенные элементы</a></td></tr>
		<tr><td colspan="2"><div id="tree_user_access_block" class="tree_selected"></div></td></tr>
		<tr><td colspan="2"><input class="btn action" type="submit" value="Сохранить" /></td></tr>
		</table>
		</form>
		</div></div></div>
		<div class="tree popup" style="display:none" id="tree_user_access"></div>
<?php
	}

	function user_del() {		
		$id = intval(i()->argv['path'][4]);
		$q = "
			DELETE a, b
			FROM `base_user` AS a
			LEFT JOIN `base_rule` AS b ON a.id = b.id_user
			WHERE a.id = {$id}
		";		
		i()->db->query($q);
		i()->admin->add_notice('suc', 'Удалено успешно', $_SERVER['HTTP_REFERER']);
	}

	function group_del() {
		$id = intval(i()->argv['path'][4]);
		$q = "UPDATE `base_user` SET `id_group` = 0 WHERE `id_group` = {$id}";
		i()->db->query($q);
		$q = "
			DELETE a, b
			FROM `base_group` AS a
			LEFT JOIN `base_rule` AS b ON a.id = b.id_group
			WHERE a.id = {$id}
		";		
		i()->db->query($q);
		i()->admin->add_notice('suc', 'Удалено успешно', $_SERVER['HTTP_REFERER']);
	}

	function auth() {
		if(!isset($_SESSION['admin'])) {
			if(isset($_POST['login'], $_POST['pass'], $_SERVER['REMOTE_ADDR'])) {
				$err = false;
				$time = time();
				$timei = $time + i()->conf['auth_intarval'];
				$ip = ip2long($_SERVER['REMOTE_ADDR']);
				$login = i()->db->real_escape_string($_POST['login']);
				$pass = md5($_POST['pass']);				
				if($row = i()->db->query("SELECT * FROM base_ip WHERE `time` > {$time} AND `ip` = '{$ip}'")->fetch_assoc()) {
					i()->db->query("DELETE FROM base_ip WHERE `time` < {$time}");
					i()->admin->add_notice('err', 'Попробуйте зайти через '.($row['time'] - $time).' секунд', i()->host.'/adm', false);
					return false;
				}
				if(!($row = i()->db->query("SELECT id, name FROM base_user WHERE `name` = '".$login."' AND `pass` = '".$pass."'")->fetch_assoc())) {
					i()->db->query("INSERT INTO base_ip SET `time` = {$timei}, ip = '{$ip}' ON DUPLICATE KEY UPDATE `time` = {$timei}");
					i()->db->query("DELETE FROM base_ip WHERE `time` < {$time}");
					i()->admin->add_notice('err', 'Неверный логин или пароль', i()->host.'/adm', false);
					return false;
				}
				$_SESSION['admin'] = $row;
				i()->db->query("DELETE FROM base_ip WHERE `time` <= {$time}");
				header("Location: ".i()->host.'/adm');
			} elseif(!count(i()->tag->level)) {
				include_once(i()->root.'/view/admin/auth.php');
			}
			return false;
		}
		if(!$this->access(i()->route['id'])) {
			if(!count(i()->tag->level)) {
				i()->err->forbidden_admin();
			}
			return false;
		}
		return true;
	}

	/**
	 * Возвращает true если у пользователя есть доступ к разделу
	 * @param int $id id раздела
	 * @return bool
	 */	
	function access($id) {
		if(empty($_SESSION['admin'])) {
			return false;
		}
		$user_id = intval($_SESSION['admin']['id']);
		$id = intval($id);
		// если у пользователя установлен тип прав берем его права, если нет берем права группы
		// если тип прав не указан возвращаем false
		$ids = i()->get_parents($id);
		$ids[] = $id;
		$ids = implode(', ', $ids);
		if(!$user = i()->db->query("
			SELECT IF(a.type > 0, a.type, IF(b.type > 0, b.type, 0)) AS type, c.id AS `rule`
			FROM base_user AS a
			LEFT JOIN base_group AS b ON a.id_group = b.id
			LEFT JOIN base_rule AS c ON ((a.type > 0 AND c.id_user = a.id) OR (a.type = 0 AND b.type > 0 AND c.id_group = a.id_group)) AND c.id_page IN({$ids})
			WHERE (a.type > 0 OR b.type > 0) AND a.id = {$user_id}
			GROUP BY a.id
		")->fetch_assoc()) {
			return false;
		}		
		if(($user['type'] == 1 && empty($user['rule'])) || ($user['type'] == 2 && !empty($user['rule']))) {
			return true;
		}
		return false;
	}
	


}

?>
