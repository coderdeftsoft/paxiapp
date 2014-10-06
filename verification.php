<?php include('header.php');
   
   $id = $_REQUEST['token'];
   
   $qry = "UPDATE `users`
			SET `email_active` = 'yes'
			WHERE `hash_code` = '$id'";
    $res = mysql_query($qry);
	
	if($res > 0){
	
			echo 'Your Email id successfully veryfied';
	 	}
	else{
		echo 'You have enter wrong request id';
	}
?>