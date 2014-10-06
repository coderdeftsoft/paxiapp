<?php

class Database
{
	public function connect_to_db($host , $user , $pass , $db_name)
	{
		
		 $conn = mysql_connect($host , $user , $pass);
		 if($conn)
		 {
			mysql_select_db($db_name) or die(mysql_error);
		 }
			
	}
/*****************Select Record From the data base**************************************/
	public function select($table,$array,$condtion)
	{
			$fields = array_keys($array);
			$values = array_values($array);
			$n      = count($array)-1;
			$qry    =  "SELECT * FROM $table WHERE";
			for($i=0;$i<count($array);$i++)
			{
				$qry.=" $fields[$i]='$values[$i]'" ;
				if($i<$n)
				{
					$qry.=" ".$condtion;
			
				}
			}
			
			$res = mysql_query($qry) or die(mysql_error());
			return $res;
	}
/*******************Insert Record into the database*************************************/
	public function insert($table,$array)
	{
		if(empty($array))
		{
			echo "Array should be passed";
		}
		else
		{
			$fields = implode(',',array_keys($array));
			$values = implode("','",array_values($array));
			 $qry    = "INSERT INTO $table ($fields) VALUES('$values')";
			$res    = mysql_query($qry);
			return $res;
		}
	}
/********************** Update Record **************************************************/
	public function update($table,$array,$id,$column)
	{
			$fields = array_keys($array);
			$values = array_values($array);
			$n      = count($array)-1;
			$qry    = "UPDATE $table SET ";
				for($i=0;$i<count($array);$i++)
				{
					$qry.=" $fields[$i]='$values[$i]' " ;
					if($i<$n)
					{
						$qry.=" ,";
					}
				}
				 
				 $qry.= "WHERE $column='$id'";
				$res  = mysql_query($qry);
				return $res;
	}    
	public function get_user_id($auth_key){
		
		$condition = 'AND';
		
		$array = array('id'=>$auth_key);
		
		$res = $this->select('users',$array,$condition);
		
		$userid = mysql_fetch_assoc($res);
		
		return $userid['id'];
	}
	
	public function getPayment(){
	
		$qry = "SELECT * FROM payment";
		$res = mysql_query($qry);
		$result = mysql_fetch_assoc($res);
		$payment  = array($result['basic_fee'],$result['airport_service']);
		return $payment;
	}
	
	public function getImage($userid){
	
		$qry = "SELECT * FROM users WHERE id = '".$userid."'";
		$res = mysql_query($qry);
		$result = mysql_fetch_assoc($res);
		
		return $result['profile_Image'];
	}
	public function getUsername($userid){
	
		$qry = "SELECT * FROM users WHERE id = '".$userid."'";
		$res = mysql_query($qry);
		$result = mysql_fetch_assoc($res);
		
		return $result['username'];
	}
	public function getPhone($userid){
	
		$qry = "SELECT * FROM users WHERE id = '".$userid."'";
		$res = mysql_query($qry);
		$result = mysql_fetch_assoc($res);
		
		return $result['phone'];
	}
	public function getDriverName($userid){
	
		$qry = "SELECT * FROM drivers WHERE id = '".$userid."'";
		$res = mysql_query($qry);
		$result = mysql_fetch_assoc($res);
		
		return !empty($result['username']) ? $result['username'] : '';
	}
	public function sendNotification($deviceToken,$message,$passphrase='iphonedev'){
  	// Put your device token here (without spaces):
     //iPad Device Token
     //$deviceToken = 'f0615a70edc80b430f1f59c2756fe34b932e92e105cfcb682d9a97e657364de5';

	//iPod Device Token
	//$deviceToken = 'e4efb8ded030f1033f5dd4edab1813c08c60086b5e63f033bc6d4f021e3f8c0a';
	
	// Put your private key's passphrase here:
	//$passphrase = 'pushchat';
	
	// Put your alert message here:
	//$message = 'Check out the new questions for survey';
	$badge = 1;
	
	////////////////////////////////////////////////////////////////////////////////
	$ctx = stream_context_create();
	//$url = 'deftsoft.info/artravels/ck.pem';
	stream_context_set_option($ctx, 'ssl', 'local_cert',dirname(__FILE__).'/FitcoDeveloper.pem');
	stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
	
	// Open a connection to the APNS server
	$fp = stream_socket_client(
		'ssl://gateway.sandbox.push.apple.com:2195', $err,
		$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
	
	if (!$fp)
		exit("Failed to connect: $err $errstr" . PHP_EOL);
	
	//echo 'Connected to APNS' . PHP_EOL;
	
	// Create the payload body
	$body['aps'] = array(
		'alert' => $message,
		'sound' => 'default',
		'badge' => $badge
		);
				
	// Encode the payload as JSON
	$payload = json_encode($body);
	
	// Build the binary notification
	$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
	
	// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
	
	if ($result)
		return true;
		//echo 'Message not delivered' . PHP_EOL;
	//else
		//print_r($body);
	//	echo 'Message successfully delivered1' . PHP_EOL;
	
	// Close the connection to the server
	
	fclose($fp);
  }
  public function getDeviceToken($userid){
  	
	 $qry = "SELECT device_token FROM users WHERE id = '$userid'";
	$res = mysql_query($qry);
	if(mysql_num_rows($res)>0){
		$userid = mysql_fetch_assoc($res);
		$token = $userid['device_token'];
		return $token ;
	}else{
		return false;
	}
  }
  
  public function startTime($id){
  
  	$qry = "SELECT start_time FROM departure_taxi WHERE requestid = '$id'";
	$res = mysql_query($qry);
	
	$userid = mysql_fetch_assoc($res);
	$token = $userid['start_time'];
	return $token ;
  }
   public function endTime($id){
  
  	$qry = "SELECT end_time FROM arrival_taxi WHERE requestid = '$id'";
	$res = mysql_query($qry);
	
	$userid = mysql_fetch_assoc($res);
	$token = $userid['end_time'];
	return $token ;
  }
	public function rating($userid){
		
		$qry = "SELECT avg(rating) as total FROM 
			    `add_to_favourite` WHERE userid = '$userid'";
		$res = mysql_query($qry);
		
		$avg = mysql_fetch_assoc($res);
		
		return !empty($avg['total']) ? $avg['total'] : '';
	}
}
?>