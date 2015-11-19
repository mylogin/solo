<?php

class content {

	// переменная $export описывает свойства модуля
	public $export = array(
		'name' => 'Контент', // Имя модуля
		'nav' => array( // Структура подменю в разделе навигации "Модули"
			array('level' => 0, 'name' => 'Контент', 'href' => 'mod/content')
		),
		'actions' => array( // Описывает действия модуля, ключ - имя функции
			'varchar255' => array(
				'name' => 'VARCHAR 255', // Имя действия
				'form' => 'varchar255_form', // Функция вывода формы редактирования параметров действия
				'save' => 'varchar255_save', // Функция сохранения параметров действия
				'del' => 'del' // Функция удаления параметров действия
			),
			'text' => array('name' => 'TEXT', 'form' => 'text_form', 'save' => 'text_save', 'del' => 'del'),
			'last' => array('name' => 'Последние'),
			'list_content' => array('name' => 'Список')
		)
	);

	/**
	* Получение новости
	* @param $id int идентификатор страницы или метки	
	* @return void
	*/
	function text($id, $tag, $arg) {
		$this->show($id, 'text', $tag, $arg);
	}	

	function varchar255($id, $tag, $arg) {
		$this->show($id, 'varchar255', $tag, $arg);
	}

	function show($id, $field, $tag, $arg) {
		$row = i()->db->query("SELECT * FROM `mod_content` WHERE `id_tree` = {$id}")->fetch_assoc();
		if(!empty($row[$field])) {
			if($tag) {
				echo $row[$field]; 
			} else {
				i()->tag->content = $row[$field];
				i()->tag->title = $row['title'];
				if(!empty($row['date'])) {
					i()->tag->title .= ' '.date('Y.m.d', $row['date']);
				}
			}
		}
	}

	function text_form($id, $action) {
		$this->form($id, $action, 'text');
	}

	function varchar255_form($id, $action) {
		$this->form($id, $action, 'varchar255');
	}

	/**
	* Функция вывода формы редактирования новости
	* @param $id int идентификатор страницы или метки
	* @param $action string адрес страницы сохранения
	* @return void
	*/
	function form($id, $action, $field) {
		$row = i()->db->query("SELECT * FROM `mod_content` WHERE `id_tree` = {$id}")->fetch_assoc();
		$content = !empty($row[$field]) ? $row[$field] : '';
		$title = !empty($row['title']) ? $row['title'] : '';
		$date = !empty($row['date']) ? date('d.m.Y', $row['date']) : date('d.m.Y');		
?>
<link href="<?php echo i()->host; ?>/resources/content/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="<?php echo i()->host; ?>/resources/content/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php echo i()->host; ?>/resources/content/jquery-ui/datepicker-ru.js"></script>
<script type="text/javascript" src="<?php echo i()->host; ?>/resources/content/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
$(function() {
	$(".datepicker").datepicker({dateFormat: 'dd.mm.yy'});
});
</script>
<form action="<?php echo $action; ?>" method="post">	
	<table class="form">
	<tr><td>Заголовок: </td><td><input type="text" name="title" class="input" value="<?php echo htmlspecialchars($title); ?>" /></td></tr>
	<tr><td>Дата: </td><td><input type="text" name="date" class="input datepicker" value="<?php echo $date; ?>" /></td></tr>
	<tr><td>Тип: </td><td><select name="sec" class="input"><option value="0">-</option>
<?php
		$res = i()->db->query("SELECT * FROM `mod_content_sec`");
		while($row1 = $res->fetch_assoc()) {
			$selected = $row['sec'] == $row1['id'] ? ' selected="selected"' : '';
			echo '<option value="'.$row1['id'].'"'.$selected.'>'.$row1['name'].'</option>';
		}
?>
	</select></td></tr>
	<tr><td colspan="2">Контент:</td></tr>
	<tr><td colspan="2"><textarea class="ckeditor" name="content"><?php echo htmlspecialchars($content); ?></textarea></td></tr>
	<tr><td colspan="2"><input type="submit" value="Сохранить" class="btn action" /></td></tr>
	</table>
</form>
<?php		
	}

	function text_save($id) {
		$this->save($id, 'text');
	}
	
	function varchar255_save($id) {
		$this->save($id, 'varchar255');
	}				
	
	/**
	* Функция сохранения новости
	* @param $id int идентификатор страницы или метки
	* @return void
	*/
	function save($id, $field) {
		if(empty($_POST['content']) && empty($_POST['title'])) {
			return;
		}
		$content = i()->db->real_escape_string($_POST['content']);
		$title = i()->db->real_escape_string($_POST['title']);
		$date = 0;
		if(!empty($_POST['date'])) {
			$dt = DateTime::createFromFormat('d.m.Y', $_POST['date']);
			$date = $dt->getTimestamp();
		}
		$sec = intval($_POST['sec']);
		i()->db->query("INSERT INTO `mod_content` (id_tree, $field, title, date, sec, type) VALUES ({$id}, '{$content}', '{$title}', {$date}, {$sec}, '{$field}') ON DUPLICATE KEY UPDATE `{$field}` = '{$content}', `title` = '{$title}', `date` = {$date}, `sec` = {$sec}, `type` = '{$field}'");
	}

	/**
	* Функция удаления новости
	* @param $id int идентификатор страницы или метки
	* @return void
	*/
	function del($id) {
 		i()->db->query("DELETE FROM `mod_content` WHERE id_tree = {$id}");
	}	
	
	/**
	* Функция формирования списка ссылок на последние статьи
	* @param $id int идентификатор страницы или метки
	* @return void
	*/
	public function last_page($id, $tag, $arg) {
		$res = i()->db->query("
			SELECT IF(b.tag IS NOT NULL, c.way, b.way) AS way, IF(c.id, c.id, a.id_tree) AS id_tree, a.type, a.date, a.title, b.tag
			FROM `mod_content` AS a
			INNER JOIN base_tree AS b ON a.id_tree = b.id
			LEFT JOIN base_tree AS c ON b.id_parent = c.id AND b.tag IS NOT NULL
			ORDER BY a.date DESC");
		i()->tag->last_page = array();		
		while ($row = $res->fetch_assoc()) {
			$href = i()->create_url(i()->host, i()->string_to_way($row['way']));
			if($tag) {
				echo '<a href="'.$href.'">'.$href.'</a><br />';
			} else {
				i()->tag->last_page[] = array(
					'data' => $row,
					'href' => $href
				);
			}			
		}
	}

	/**
	* Функция формирования списка ссылок на редактирование последних статей
	* @param $id int идентификатор страницы или метки
	* @return void
	*/
	public function last_edit($id, $tag, $arg) {
		$res = i()->db->query("
			SELECT IF(b.tag IS NOT NULL, c.way, b.way) AS way, IF(c.id, c.id, a.id_tree) AS id_tree, a.type, a.date, a.title, b.tag
			FROM `mod_content` AS a
			INNER JOIN base_tree AS b ON a.id_tree = b.id
			LEFT JOIN base_tree AS c ON b.id_parent = c.id AND b.tag IS NOT NULL
			ORDER BY a.date DESC");
		i()->tag->last_edit = array();
		while ($row = $res->fetch_assoc()) {
			$path = array('adm', 'structure', 'param', $row['id_tree']);
			if($row['tag']) {
				$path[] = $row['tag'];
			}
			$path[] = 'content:'.$row['type'];
			$text = i()->create_url(i()->host, i()->string_to_way($row['way'])).($row['tag'] ? ', tag: '.$row['tag'] : '');
			$href = i()->create_url(true, array('path' => $path));
			if($tag) {
				echo '<a href="'.$href.'">'.$text.'</a><br />';
			} else {
				i()->tag->last_edit[] = array(
					'data' => $row,
					'href' => $href,
					'text' => $text
				);
			}
		}
	}

}

?>