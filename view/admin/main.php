<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo i()->name; ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo i()->host; ?>/css/admin/adm_reset.css" />	
	<link rel="stylesheet" type="text/css" href="<?php echo i()->host; ?>/css/admin/adm_styles.css" />
	<script type="text/javascript">
	var host = '<?php echo i()->host; ?>';
	</script>	
	<script type="text/javascript" src="<?php echo i()->host; ?>/resources/jquery-2.2.3.min.js"></script>
	<script type="text/javascript" src="<?php echo i()->host; ?>/js/admin/popup.js"></script>
	<script type="text/javascript" src="<?php echo i()->host; ?>/js/admin/admin.js"></script>
</head>

<body>

<div class="wrap center">

<div id="top">
<span style="float:left;"><?php echo i()->name; ?></span>
<span style="float:right;"><?php echo $_SESSION['admin']['name']; ?></span>
</div>
<div class="wrap_center">
<?php	
	i()->tag->nav();
?>
	<div class="clear" style="height:20px;"></div>
<?php
	i()->admin->notice();
?>	
	<div class="content">
<?php
	i()->tag->content();	
?>
	</div>
</div>

<div id="popup"></div>

</body>
</html>