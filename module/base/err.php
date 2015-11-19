<?php

/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

class err {
	
	public function e404() {
		header("HTTP/1.0 404 Not Found");
		$this->error_page('404 Page Not Found');
	}
	
	public function forbidden_admin() {
		header('HTTP/1.0 403 Forbidden');
		$this->error_page('Доступ к этой странице запрещен. <a href="'.i()->host.'/adm">Главная страница</a>');		
	}
	
	public function not_controller() {
		$this->error_page('Возникли проблемы при загрузке контроллера');
	}
	
	public function not_module() {
		$this->error_page('Возникли проблемы при загрузке модуля');
	}
	
	public function other() {
		$this->error_page('Произошла ошибка');
	}
	
	public function error_page($error) {
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />		
	<title>Ошибка</title>
	<link rel="stylesheet" type="text/css" href="<?php echo i()->host; ?>/css/admin/adm_reset.css" />	
	<link rel="stylesheet" type="text/css" href="<?php echo i()->host; ?>/css/admin/adm_styles.css" />	

</head>

<body>


<div class="center" id="page" style="opacity: 1; margin-top: 10%;">
<div class="msg err" style="display: block;">
<strong>Ошибка!</strong>
<?php	
	echo $error;		
?>
</div>
</div>

</body>	
<?php
		exit;
	}
	
}

?>