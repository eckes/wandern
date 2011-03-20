<?php
	require_once('common.php');
	logoutUser();
	header('Location: ' . $_SERVER['HTTP_REFERER']);
?>	
