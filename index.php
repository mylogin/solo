<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

class i {

	static $i;
	public $root = '';
	public $host = '';
	public $conf = array();	
	public $argv = array();
	public $controller = array('class' => '', 'file' => '', 'method' => '');
	public $route = false;
	public $route_tree = array();
	public $modules = array();	
	public $not_installed = false;
	public $name = 'Solo CMS';

    public function __construct() {
    	$this->root = rtrim(str_replace('\\', '/', dirname(__FILE__)), '/');
		include($this->root.'/config.php');
		$this->conf = $config;
    	ini_set('display_errors', $this->conf['debug'] ? 1 : 0);
		ini_set('error_reporting', E_ALL);
		ini_set('date.timezone', $this->conf['timezone']);
		set_error_handler(array($this, 'error_handler'));
		session_start();

    	i::$i =& $this;		
		$this->conf['no_required'] = array_diff(array_keys($this->argv), $this->conf['required']);
		if($this->conf['scheme']) {
			$this->host = $this->conf['scheme'].'://'.$this->conf['base_domain'];
		} elseif((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
			$this->host = 'https://'.$this->conf['base_domain'];
		} else {
			$this->host = 'http://'.$this->conf['base_domain'];
		}
		if($this->conf['base_path']) {
			$this->host = $this->host.'/'.$this->conf['base_path'];
		}
		$this->argv = $this->query_string_to_way($this->create_url());		
		$this->argv['post'] = $_POST;
		if(isset($_SERVER['argv']) && count($_SERVER['argv'])) {
			unset($_SERVER['argv'][0]);
			foreach($_SERVER['argv'] as $k => $v) {
				if(substr($v, 0, 2) == '--') {
					$v = explode('=', substr($v, 2));
					$this->argv['shell'][$v[0]] = isset($v[1]) ? $v[1] : '';
				}
			}
		}		
		$this->module = new stdClass;
		if(file_exists($this->root.'/modules.php')) {
			include($this->root.'/modules.php');
		}
	}	

	/**
	 * Создает url, формируя его в соответствии с параметрами
	 * @param mixed $construct создать url на основе адреса текущей страницы, или взять из аргумента
	 * @param array $param параметры
	 * @return string
	 */
	public function create_url($construct = true, $param = array()) {
		$url = array();
		if($construct === true) {
			$t = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https:" : "http:";
			if(!empty($_SERVER['HTTP_HOST'])) {
				$t .= '//'.$_SERVER['HTTP_HOST'];
				if(!empty($_SERVER['REQUEST_URI'])) {
					$t .= $_SERVER['REQUEST_URI'];
				}
			}
			$url = parse_url($t);
		} elseif($construct) {
			$url = parse_url($construct);
		}
		$query = array();
		if(!empty($url['query'])) {
			parse_str($url['query'], $query);
		}	
		if(!empty($param['query_del'])) {
			$query = array_diff_key($query, array_flip($param['query_del']));
		}
		if(!empty($param['query_only'])) {
			$query = array_intersect_key($query, array_flip($param['query_only']));
		}
		if(!empty($param['query_add'])) {
			$query = array_merge($query, $param['query']);
		}
		if($query) {
			$url['query'] = http_build_query($query);
		}
		if(!empty($param['path'])) {
			$url['path'] = is_array($param['path']) ? implode('/', $param['path']) : $param['path'];
		}
		if(!empty($param['only'])) {
			$url = array_intersect_key($url, array_flip($param['only']));
		}
		return
			 ((isset($url['scheme'])) ? $url['scheme'] . '://' : '')
			.((isset($url['user'])) ? $url['user'] . ((isset($url['pass'])) ? ':' . $url['pass'] : '') .'@' : '')
			.((isset($url['host'])) ? $url['host'] : '')
			.((isset($url['port'])) ? ':' . $url['port'] : '')
			.((isset($url['path'])) ? '/'.trim($url['path'], '/') : '')
			.((isset($url['query'])) ? '?' . $url['query'] : '')
			.((isset($url['fragment'])) ? '#' . $url['fragment'] : '')
		;
	}

	/**
	 * Создает карту директории
	 * @param mixed $source_dir директория
	 * @param array $plain возвращать ли список в виде одномерного массива или вложенного
	 * @return string
	 */
	public function directory_map($source_dir, $plain = true) {
		$map = array();
		if ($fp = @opendir($source_dir)) {
			$source_dir = rtrim($source_dir, '/').'/';
			while (FALSE !== ($file = readdir($fp))) {
				if ($file == "." || $file == "..") {
					continue;
				}
				if (@is_dir($source_dir.$file)) {
					if($plain) {
						$m = $this->directory_map($source_dir.$file.'/');
						foreach($m as $v) {
							$map[] = $v;
						}
					} else {
						$map[$file] = $this->directory_map($source_dir.$file.'/');
					}
				} else {
					$map[] = $source_dir.$file;
				}
			}
			closedir($fp);
		}
		return $map;
	}

	/**
	 * Возвращает строку роутинга как массив
	 * @param string $s строка роутинга
	 * @return array
	 */
	public function string_to_way($s) {
		$ret = array();
		$s = explode(',', $s);
		foreach(array_keys($this->argv) as $k => $v) {
			$ret[$v] = !empty($s[$k]) ? explode(';', $s[$k]) : array();
		}
		return $ret;
	}

	/**
	 * Возвращает массив как строку роутинга
	 * @param array $r массив
	 * @return string
	 */
	public function way_to_string($r) {
		$ret = array();
		foreach(array_keys($this->argv) as $v) {
			if(!isset($r[$v])) {
				$r[$v] = array();
			}
			$ret[$v] = implode(';', array_filter($r[$v], 'strlen'));
		}		
		return implode(',', $ret);
	}	

	/**
	 * Возвращает url как массив
	 * @param string $s url
	 * @return array
	 */
	public function query_string_to_way($s) {
		$p = parse_url($s);
		$argv = array();
		$argv['domain'] = !empty($p['host']) ? explode('.', $p['host']) : array();		
		$argv['path'] = !empty($p['path']) ? array_values(array_filter(explode('/', $p['path']), 'strlen')) : array();
		if($this->conf['base_path']) {
			$argv['path'] = array_values(array_filter(explode('/', preg_replace('/^'.preg_quote($this->conf['base_path'], '/').'/', '', implode('/', $argv['path']))), 'strlen'));
		}
		$argv['shell'] = array();
		$argv['post'] = array();		
		$argv['get'] = array();
		if(!empty($p['query'])) {
			parse_str($p['query'], $argv['get']);
		}
		return $argv;
	}

	/**
	 * Возвращает элемент страктуры
	 * @param int $id id элемента структуры
	 * @param bool $prototype брать ли значения из прототипа
	 * @return array
	 */
	public function tree_item($id, $prototype = true) {
		$id = intval($id);
		$ret = array();
		$res = i()->db->query("SELECT *, IF(`way` IS NOT NULL, `way`, `route`) AS `way_route` FROM `base_tree` WHERE `id` = ".$id." OR (`id_parent` = ".$id." AND `tag` IS NOT NULL) ORDER BY `tag`");
		if(!$res->num_rows) {
			return $ret;
		}
		while($row = $res->fetch_assoc()) {
			if(!count($ret) && $row['tag']) {
				return $ret;				
			}
			$name = $row['tag'] ? $row['tag'] : 0;
			$row['proto'] = 0;
			$ret[$name] = $row;
		}
		if($prototype && $ret[0]['id_prototype']) {
			foreach($this->tree_item($ret[0]['id_prototype'], false) as $k => $v) {
				foreach($ret as $k1 => &$v1) {
					if($k === $k1) {
						foreach(array('way', 'file', 'actions', 'template') as $k2 => $v2) {
							if($v1['from_prototype'][$k2]) {
								$v1[$v2] = $v[$v2];
							}
						}
						if(!$k1 && !$v1['from_prototype'][3]) {
							break 2;
						}
						continue 2;
					} elseif(i()->is_level_child($k, $k1) && !$v1['from_prototype'][3]) {
						continue 2;
					}					
				}
				$v['from_prototype'] = $k ? '0111' : '1111';
				$v['proto'] = 1;
				$ret[$k] = $v;
			}			
		}		
		return $ret;
	}

	/**
	 * Возвращает строку роитинга в удобном для чтения виде
	 * @param string $s url
	 * @return array
	 */
	function route_string($v) {
		$ret = array();
		if(!empty($v['name'])) {
			return $v['name'];
		}
		$v = i()->string_to_way($v['way_route']);
		if(!empty($v['path'])) {
			$path = $v['path'];
			foreach($path as &$v1) {
				$e = explode(':', $v1);
				if(count($e) > 1) {
					switch($e[1]) {
						case structure::PRE_REGEXP:
							$v1 = '['.preg_replace('/^p:'.structure::PRE_REGEXP.':/', 're:', $v1).']';
						break;
						case structure::PRE_AUTOINC:
							$v1 = '[autoinc]';
						break;
						case structure::PRE_TIMESTAMP:
							$v1 = '[timestamp]';
						break;
					}
				}
			}
			$ret[] = '<b>Path</b>: '.implode('/', $path);
		}
		foreach(array('shell', 'domain', 'get', 'post') as $v1) {
			if(!empty($v[$v1])) {
				$ret[] = '<b>'.ucfirst($v1).'</b>: '.implode(', ', $v[$v1]);
			}
		}
		if(!count($ret)) {
			$ret[] = 'Empty';
		}
		return implode('; ', $ret);
	}
	
	/**
	 * Возвращает массив родителей, сортируется начиная с ближнего родителя
	 * @param int $id id элемента
	 * @return array
	 */
	public function get_parents($id) {
		$ret = array();
		if($row = i()->db->query("SELECT * FROM `base_tree` WHERE id = {$id}")->fetch_assoc()) {
			while($row['id_parent']) {
				if($row = i()->db->query("SELECT * FROM `base_tree` WHERE id = {$row['id_parent']}")->fetch_assoc()) {
					$ret[] = $row['id'];
				}
			}
		}
		return array_reverse($ret);
	}

	/**
	 * Возвращает массив дочерних элементов, сортируется начиная со старшего	 
	 * @param int $id id элемента
	 * @return array
	 */
	public function get_children($id) {
		$ret = array();
		$res = i()->db->query("SELECT `id` FROM `base_tree` WHERE `id_parent` = {$id} AND `tag` IS NULL");
		while($row = $res->fetch_assoc()) {
			$ret[] = $row['id'];
			foreach($this->get_children($row['id']) as $v) {
				$ret[] = $v;
			}
		}
		return $ret;
	}

	/**
	 * Редирект на указанный адрес и выход из скрипта
	 * @param string $uri адрес
	 * @param int $http_response_code код ответа
	 * @return void
	 */
	public function redirect($uri = '', $http_response_code = 302) {
		header("Location: ".$uri, TRUE, $http_response_code);
		exit;
	}

	#-----------------------------------------------------------------------

	public function route($argv) {
		
		$ret = false;

		$argv_route = array(
			'domain' => $argv['domain'],
			'path' => $argv['path'],
			'shell' => array_keys($argv['shell']),
			'get' => array_keys($argv['get']),
			'post' => array_keys($argv['post'])
		);
		if($this->conf['base_domain']) {
			$argv_route['domain'] = array_values(array_filter(explode('.', preg_replace('/'.preg_quote($this->conf['base_domain'], '/').'$/', '', implode('.', $argv_route['domain']))), 'strlen'));
			$argv_route['domain'] = array_values(array_filter(explode('.', preg_replace('/www$/', '', implode('.', $argv_route['domain']))), 'strlen'));
		}

		$way = $this->way_to_string($argv_route);
		$res = i()->db->query("SELECT * FROM `base_tree` WHERE `way` = '".i()->db->real_escape_string($way)."' AND `is_prototype` = 0");
		if(i()->db->errno == 1146) {			
			$this->not_installed = true;
			return false;
		}
		if($ret = $res->fetch_assoc()) {
			return $ret;
		}

		$way = $this->way_to_string(array_intersect_key($argv_route, array_flip($this->conf['required'])));
		if($ret = i()->db->query("SELECT * FROM `base_tree` WHERE `way` = '".$way."' AND `is_prototype` = 0")->fetch_assoc()) {
			return $ret;
		}

		$routes = array();
		$selected = array();
		$res = i()->db->query("SELECT * FROM `base_tree` WHERE `route` IS NOT NULL AND `is_prototype` = 0");
		while($row = $res->fetch_assoc()) {
			$routes[] = $row;
		}
		foreach($routes as $k => $vv) {						
			$v = i()->string_to_way($vv['route']);								
			foreach($this->conf['required'] as $k1 => $v1) {
				if(count($argv_route[$v1]) != count($v[$v1])) {					
					continue 2;
				}
			}			
			// либо все количество необязательных равно, либо все необязательные пустые
			$n = 0;
			foreach($this->conf['no_required'] as $v1) {
				$n += count($v[$v1]);
				if($n && count($argv_route[$v1]) != count($v[$v1])) {					
					continue 2;
				}
			}			
			foreach($argv as $k1 => $v1) {
				foreach($v[$k1] as $k2 => $v2) {
					if($k1 == 'path') {
						if(strpos($v2, 'p:'.structure::PRE_REGEXP.':') === 0) {
							$v2 = preg_replace('/^p:'.structure::PRE_REGEXP.':/', '', $v2);
							if(!preg_match($v2, $v1[$k2])) {
								continue 3;
							}
						}
						elseif($v1[$k2] != $v2) {
							continue 3;
						}
					} elseif(!isset($v1[$v2])) {
						continue 3;
					}
				}
				
			}			
			$selected[] = $vv;
		}
		if(count($selected)) {
			foreach($selected as $v) {
				if(strlen($v['route']) > strlen($this->way_to_string($this->route))) {
					$ret = $v;
				}
			}
		}
		
		return $ret;

	}	

	// уровень $a дочерний уровня $b
	public function is_level_child($a, $b) {
		$a = explode(':', $a);
		$b = explode(':', $b);
		if(count($b) < count($a) && $b == array_slice($a, 0, count($b))) {
			return true;
		}
		return false;
	}		

	public function module_map($write = false) {
		$this->modules = array();
		$map = $this->directory_map($this->root.'/module');
		$content = '';
		foreach($map as $v) {
			$mod_name = substr($v, strrpos($v, '/') + 1, (strlen($v) - strrpos($v, '/')) - (strlen($v) - strrpos($v, '.')) - 1);
			$mod_path = $v;
			$this->modules[$mod_name] = array('path' => $mod_path);
			if($write) {
				$content .= "i()->modules['{$mod_name}'] = array('path' => '{$mod_path}');\n";
			}
		}
		if($write) {
			file_put_contents($this->root.'/modules.php', "<?php\n".$content."?>");
		}
	}

 	function autoload($name) {
		$this->_autoload($name, false);
	}

    public function &__get($name) {
		return $this->_autoload($name, true);
    }

    private function &_autoload($module, $create = false) {
    	if(!isset($this->modules[$module]) || !file_exists($this->modules[$module]['path'])) {
    		$this->module_map(true);
		}
		if(!isset($this->modules[$module]) || !file_exists($this->modules[$module]['path'])) {
    		//echo 'Not module: '.$module;
    		return $this->module;
		}
		if(isset($this->modules[$module]['obj'])) {
			return $this->modules[$module]['obj'];
		}
		include_once($this->modules[$module]['path']);
		if($create && class_exists($module)) {
			$this->modules[$module]['obj'] = new $module;
			return $this->modules[$module]['obj'];
		}
	 	return $this->module;
    }

	public function init() {
		$this->route = $this->route($this->argv);		
		if(!$this->route) {
			if($this->not_installed) {
				$this->install('admin');							
				i()->admin->add_notice('suc', 'Система установлена', i()->host.'/adm');
			}
			$this->err->e404();
			return;
		}
		$this->route_tree = $this->tree_item($this->route['id']);
		if(!$this->load_controller($this->route_tree[0])) {
			$this->err->not_controller();
		}
	}

	public function install($name) {
		if(file_exists(i()->root.'/install/'.$name.'/install.php')) {
			include(i()->root.'/install/'.$name.'/install.php');
			return true;
		}
		return false;
	}

	public function uninstall($name) {
		if(file_exists(i()->root.'/install/'.$name.'/uninstall.php')) {
			include(i()->root.'/install/'.$name.'/uninstall.php');
			return true;
		}
		return false;
	}

	public function pr($data) {
		echo '<pre>';
		var_dump($data);
		echo '</pre>';
	}

	public function log($file, $data) {
		if(i()->conf['debug']) {
			$fp = fopen($this->root.'/log/'.$file, 'a+');
			fwrite($fp, $data."\n");
			fclose($fp);
		}
	}

	public function backtrace() {
		foreach(debug_backtrace() as $k => $v) {
			$err_str = array();
			if($k) {
				if(!empty($v['file'])) {
					$err_str[] = 'File: '.$v['file'];
				}
				if(!empty($v['line'])) {
					$err_str[] = 'Line: '.$v['line'];
				}
				if(!empty($v['class'])) {
					$err_str[] = 'Class: '.$v['class'];
				}
				if(!empty($v['function'])) {
					$err_str[] = 'Func: '.$v['function'];
				}
				echo implode(', ', $err_str).'<br />';
			}
		}
	}

	public function error_handler($errno, $errstr, $errfile, $errline) {
		if(!$this->conf['debug']) {
			return true;
		}
		switch($errno) {
			case 1: $errtype = 'E_ERROR'; break;
			case 2: $errtype = 'E_WARNING'; break;	 
			case 4: $errtype = 'E_PARSE'; break;
			case 8: $errtype = 'E_NOTICE'; break;
			case 16: $errtype = 'E_CORE_ERROR'; break; 
			case 32: $errtype = 'E_CORE_WARNING'; break;
			case 64: $errtype = 'E_COMPILE_ERROR'; break;
			case 128: $errtype = 'E_COMPILE_WARNING'; break;
			case 256: $errtype = 'E_USER_ERROR'; break;
			case 512: $errtype = 'E_USER_WARNING'; break;
			case 1024: $errtype = 'E_USER_NOTICE'; break;
			case 2048: $errtype = 'E_STRICT'; break;
			case 4096: $errtype = 'E_RECOVERABLE_ERROR'; break;
			case 8192: $errtype = 'E_DEPRECATED'; break;
			case 16384: $errtype = 'E_USER_DEPRECATED'; break;
			case 32767: $errtype = 'E_ALL'; break;
			default: $errtype = 'UNDEFINED';
		}		
		echo $errtype.': '.$errstr.'<br />';
		echo 'File: '.$errfile.'<br />';
		echo 'Line: '.$errline.'<br />';
		echo 'Backtrace: <br />';
		if(i()->conf['backtrace']) {
			i()->backtrace();
		}
		return true;
	}

	public function json_error() {
		switch (json_last_error()) {
	        case JSON_ERROR_NONE:
	            echo ' - Ошибок нет';
	        break;
	        case JSON_ERROR_DEPTH:
	            echo ' - Достигнута максимальная глубина стека';
	        break;
	        case JSON_ERROR_STATE_MISMATCH:
	            echo ' - Некорректные разряды или не совпадение режимов';
	        break;
	        case JSON_ERROR_CTRL_CHAR:
	            echo ' - Некорректный управляющий символ';
	        break;
	        case JSON_ERROR_SYNTAX:
	            echo ' - Синтаксическая ошибка, не корректный JSON';
	        break;
	        case JSON_ERROR_UTF8:
	            echo ' - Некорректные символы UTF-8, возможно неверная кодировка';
	        break;
	        default:
	            echo ' - Неизвестная ошибка';
	        break;
	    }
	}

	public function load_controller($route, $tag = false, $arg = array()) {
		$err = false;
		if(!empty($route['file'])) {
			include($this->root.'/'.(trim($route['file'], '/')));
		}
		if(isset($route['actions'])) {
			$route['actions'] = explode(';', $route['actions']);
			foreach($route['actions'] as $v) {
				list($class, $method) = explode(':', $v);
				if($this->$class->$method($route['id'], $tag, $arg) === false) {
					$err = true;
					break;
				}
			}
		}
		if(!$err && !empty($route['template'])) {
			include(i()->root.'/'.(trim($route['template'], '/')));			
		}
		return true;
	}
	
	function copy_dir($source, $dest) {	
		if (is_link($source)) {
			return symlink(readlink($source), $dest);
		}	
		if (is_file($source)) {
			return copy($source, $dest);
		}
		if (!is_dir($dest)) {
			mkdir($dest);
		}
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			$this->copy_dir("$source/$entry", "$dest/$entry");
		}
		$dir->close();
		return true;
	}

	function del_dir($dir) {
	    if (!file_exists($dir)) {
	        return true;
	    }
	    if (!is_dir($dir)) {
	        return unlink($dir);
	    }
	    foreach (scandir($dir) as $item) {
	        if ($item == '.' || $item == '..') {
	            continue;
	        }
	        if (!$this->del_dir($dir . DIRECTORY_SEPARATOR . $item)) {
	            return false;
	        }

	    }
	    return rmdir($dir);
	}

}

function &i() {
	return i::$i;
}

i::$i = new i();
spl_autoload_register(array(i::$i, 'autoload'));
ob_start();
i()->init();
ob_end_flush();

?>