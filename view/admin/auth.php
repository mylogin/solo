<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />	
	<link rel="stylesheet" type="text/css" href="<?php echo i()->host; ?>/css/admin/adm_reset.css" />	
	<link rel="stylesheet" type="text/css" href="<?php echo i()->host; ?>/css/admin/adm_styles.css" />			
	<title><?php echo i()->name; ?></title>
	
</head>

<body>


<div class="center" id="page" style="opacity: 1; margin-top: 10%;">
<?php
i()->admin->notice();
?>

<form method="post" class="form">

<div class="field">
	<label for="username">
	Логин
	</label>
	<div class="controls">
		<input name="login" id="username" type="text" class="input" />
	</div>
</div>
<div class="field">
	<label for="password">
	Пароль
	</label>
	<div class="controls">
		<input name="pass" id="password" type="password" class="input" />
	</div>
</div>
<div class="actions">
<div class="controls">
<input class="btn action" type="submit" value="Войти">
&nbsp;
<input class="btn" type="reset" value="Сбросить">
</div>
</div>

</form>
</div>


</body>
</html>