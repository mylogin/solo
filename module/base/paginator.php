<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

class paginator {

	public $count; // общее количество элементов
	public $href; // базовая ссылка
	public $name_param = 'page'; // имя параметра
	public $path_param = false; // устанавливать номер страницы в путь вместо get параметра
	public $page = 0; // текущая страница
	public $close_slash = false; // добавлять закрывающий слэш к ссылкам
	public $on_page = 10; // элементов на странице
	public $num = 5; // страниц слева и справа от активной
	public $class_active = ''; // класс активной ссылки
	public $class_back = ''; // класс активной ссылки назад
	public $class_next = ''; // класс активной ссылки вперед
	public $class_back_inactive = ''; // класс неактивной ссылки назад
	public $class_next_inactive = ''; // класс неактивной ссылки вперед
	public $text_back = '&laquo;'; // текст ссылки назад
	public $text_next = '&raquo;'; // текст ссылки вперед
	public $show_inactive = false; // показывать ли неактивные ссылки назад/вперед
	public $text_after_first = ''; // контент после первого элемента
	public $text_before_last = ''; // контент перед последним элементом
	public $text_before = '';  // контент перед списком
	public $text_after = ''; // контент после списка
	public $show_one_str = false; // показать неактивные ссылки даже если нет страниц
	public $first_with_num = true; // добавлять к первой ссылке параметр с номером страницы

	function link($num, $active = false, $class = '', $text = false) {
		$class = $class ? ' class="'.$class.'"' : '';
		if($active) {
			return '<span'.$class.'>'.($text ? $text : $num).'</span>';
		}
		$p = '';
		$h = array();
		if($this->path_param) {
			if($this->first_with_num || $num != 1) {
				$p = $this->name_param.$num.($this->close_slash ? '/' : '');
			}
		} else {
			if(isset($_GET[$this->name_param])) {
				unset($_GET[$this->name_param]);
			}
			if($this->first_with_num || $num != 1) {
				$h = array($this->name_param => $num);						
			}
		}
		$h = http_build_query(array_merge($_GET, $h));
		$h = $h ? '?'.$h : '';
		return '<a'.$class.' href="'.$this->href.$p.$h.'">'.($text ? $text : $num).'</a>';
	}
	
	function init() {
		$ret = '';
		$m_is_pages = $this->count > $this->on_page; // есть ли страницы для показа
		$m_sum = max(ceil($this->count / $this->on_page), 1); // количество страниц
		if($this->page) {
			$m_page = $this->page; // текущая страница
		} elseif(isset($_GET['page'])) {
			$m_page = intval($_GET['page']);
		} else {
			$m_page = 1;
		}
		$t1 = $this->num - $m_page;
		$t1 = $t1 > 0 ? $t1 : 0;
	 	$t2 = $this->num - ($m_sum - $m_page + 1);
	 	$t2 = $t2 > 0 ? $t2 : 0;
	 	$m_from = max($m_page - ($this->num) - $t2, 1); // от какой страницы показывать
		$m_to = min($m_page + ($this->num) + $t1, $m_sum); // по какую страницу показывать
		$m_back = $m_page > 1; // показывать ли ссылку назад
		$m_next = $m_page < $m_sum; // показывать ли ссылку вперед
		$m_first = $m_page > $this->num + 1 && $m_sum > $this->num * 2 + 1 ? 1 : 0;  // показывать ли ссылку на первую страницу (первая страница)
		$m_last = $m_sum - $m_page > $this->num && $m_sum > $this->num * 2 + 1 ? $m_sum : 0; // показывать ли ссылку на последнюю страницу (последняя страница)
		$url = parse_url($this->href);
		if(!empty($url['path'])) {
			if($this->path_param) {
				$path = array();
				foreach(array_filter(explode('/', $url['path']), 'strlen') as $v) {
					if(preg_match('/^'.preg_quote($this->name_param, '/').'\d+$/', $v)) {
						continue;
					}
					$path[] = $v;
				}
				$url['path'] = implode('/', $path);
			}
		}
		$this->href = '//'.$url['host'].(!empty($url['path']) && trim($url['path'], '/') ? '/'.trim($url['path'], '/') : '');
		if($this->path_param || $this->close_slash) {
			$this->href .= '/';
		}
		if($this->show_one_str || $m_is_pages) {
			$ret .= $this->text_before;
			if($m_first) {
				$ret .= $this->link(1);
				$ret .= $this->text_after_first;
			}			
			if($this->show_inactive || $m_back) {
				$class = $m_back && $this->class_back ? $this->class_back : '';
				$class = !$m_back && $this->class_back_inactive ? $this->class_back_inactive : $class;
				$ret .= $this->link($m_page - 1, !$m_back, $class, $this->text_back);
			}
			for($i = $m_from; $i <= $m_to; $i++) {
				$ret .= $this->link($i, ($i == $m_page), $this->class_active);
			}			
			if($this->show_inactive || $m_next) {
				$class = $m_next && $this->class_next ? $this->class_next : '';
				$class = !$m_next && $this->class_next_inactive ? $this->class_next_inactive : $class;
				$ret .= $this->link($m_page + 1, !$m_next, $class, $this->text_next);
			}			
			if($m_last) {
				$ret .= $this->text_before_last;
				$ret .= $this->link($m_last);
			}			
		}
		return $ret;
	}

}

?>