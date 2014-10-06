<?php

	include('function.php');
	include('config.php');
	$db = new Database();
	$db->connect_to_db($host , $user , $pass , $db_name);
?>