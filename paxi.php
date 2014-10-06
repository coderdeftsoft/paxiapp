<?php
 date_default_timezone_set('America/New_York');
	include('header.php');
	$method = $_REQUEST['method'];
	ini_set('display_errors',1);
	
	if(function_exists($method))
	{
		call_user_func($method,$_REQUEST);
	}
	else
	{
			method_error();
	}
/***************************** JSON ****************************************************/
function jsonreturn($data){
	
	if(is_array($data)){
		
		return stripslashes(json_encode($data));
	}
	else{
		return false;
	}

}
/******************************Method Error *****************************************/
function method_error(){
	
	echo "<b>You must specify an action in the url OR your specified action is not exist.
		</b>";
		die;

}
/*************************** Check Login ***********************************************/
function register($data = NULL){
	global $db;
	$msg = array();
	$user = array();
	if(!empty($data['phone']) && !empty($data['username']))
	{
		$comndtion = "OR";
	    $array = array('phone'=>$data['phone'],'username'=>$data['username']);
		$res = $db->select('users',$array,$comndtion);	
		if(mysql_num_rows($res)){
			
			$msg['return'] = 0;
			$msg['result'] = 'error';
			$msg['data'] = 'Your username or phone number already exist with us.please another username or phone number';
		} else {
			unset($data['method']);
			
			$data['created_date'] = date('Y-m-d H:i:s');
			$res = $db->insert('users',$data);
			if($res > 0){
			
				$comndtion = "AND";
				$array = array('phone'=>$data['phone'],'username'=>$data['username']);
				$res = $db->select('users',$array,$comndtion);
				while($row = mysql_fetch_assoc($res)){
				
					unset($row['status']);
					unset($row['created_date']);
					unset($row['modified_date']);
					
					$row['userid'] = $row['id']; 
					unset($row['id']);
					$user = $row;
				}
				$msg['return'] = 1;
				$msg['result'] = 'success';
				$msg['data'] = $user;
			} else {
			
				$msg['return'] = 0;
				$msg['result'] = 'error';
				$msg['data'] = 'Some error occured.please try again later';
			}
		}
	}
	else
	{
		$msg['return'] = 0;
		$msg['result'] = 'Fields are requireds';
	}
	echo jsonreturn($msg);
}
/****************************************************************************************
*	Function for login
****************************************************************************************/
function login($data){
	global $db;
	$msg = array();
	$users = array();
	if(!empty($data['email']) && !empty($data['mode']) || !empty($data['phone']) && !empty($data['mode']))
	{
		if($data['mode'] == 'user'){
		
		//$con = "AND";
		
		//$arr = array('email'=>$data['email'],'email_active'=>'yes');	
		
		//$check_email = $db->select('users',$arr,$con);
		//if(mysql_num_rows($check_email)){
		
		$condition = "AND";
	    $sql = "SELECT * FROM users 
				WHERE email = '".$data['email']."' AND password = '".$data['password']."'
				 
				OR phone = '".@$data['phone']."' AND password = '".$data['password']."'
				 ";
				
		$res = mysql_query($sql);	
		if(mysql_num_rows($res)){
		
			while($row = mysql_fetch_assoc($res)){
				
					unset($row['status']);
					unset($row['created_date']);
					unset($row['modified_date']);
					$row['userid'] = $row['id']; 
					$user = $row;
				}
				$msg['return'] = 1;
				$msg['result'] = 'success';
				$msg['data'] = $user;
		} else {
		
			$msg['return'] = 0;
			$msg['result'] = 'error';
			$msg['data'] = 'Invalid email or password.please try again later';
		}
	  // } else {
	   		//$msg['return'] = 0;
			////$msg['result'] = 'error';
			//$msg['data'] = 'You have not verified email yet.please verify the email';
	  // }
	  } else {
	  
	  	$condition = "AND";
	    $sql = "SELECT * FROM drivers 
				WHERE email = '".$data['email']."' AND password = '".$data['password']."'
				OR phone = '".@$data['phone']."' AND password = '".$data['password']."'";
				
		$res = mysql_query($sql);	
		if(mysql_num_rows($res)){
		
			while($row = mysql_fetch_assoc($res)){
				
					unset($row['status']);
					unset($row['created_date']);
					unset($row['modified_date']);
					$row['userid'] = $row['id']; 
					$user = $row;
				}
				$msg['return'] = 1;
				$msg['result'] = 'success';
				$msg['data'] = $user;
		} else {
		
			$msg['return'] = 0;
			$msg['result'] = 'error';
			$msg['data'] = 'Invalid email or password.please try again later';
		}

	  }
	}
	else
	{
		$msg['return'] = 0;
		$msg['result'] = 'Fields are requireds';
	}
	echo jsonreturn($msg);
}
/****************************************************************************************
*	Function for forgot password
*****************************************************************************************/
function forgotpassword($data = NULL){
	global $db;
	$msg = array();
	if(!empty($data['email'])){
		$condtion = "AND";
		$userArray = array('email'=>$data['email']);
		$res = $db->select('users',$userArray,$condtion);
		if($res > 0){
			while($row = mysql_fetch_assoc($res)){
				$password = $row['password'];
				$username = $row['username'];
			}
			$to = $data['email'];
			$subject = "Password Recovery";
			$message = "Your password. \n \n";
			$message .= 'password:'.$password."\n";
			$from = "Paxi";
			$headers = "From:" . $from;
			$send_mail = mail($to,$subject,$message,$headers);
			if($send_mail > 0){
				$msg['return'] = 1;
				$msg['result'] = 'suucess';
				$msg['data']   = 'Your password has been reset';
			}else{
				$msg['return'] = 0;
				$msg['result'] = 'error';
				$msg['data']   = 'Some error occurred.Please try again later';
			}
		}
		else{
			$msg['return'] = 0;
			$msg['result'] = 'error';
			$msg['data']   = 'Wrong email id.please try again later';
		}
	}else{
		$msg['return'] = 0;
		$msg['result'] = 'error';
	    $msg['data']   = 'fields are required';
	}
	echo jsonreturn($msg);
}
/***************************************************************************************
*	Function for edit Profile
***************************************************************************************/
function editProfile($data = NULL){
	global $db;
	$msg = array();
	if(!empty($data['userid']) &&  !empty($data['mode'])){
		$userid = $data['userid']; 
		
		if($data['mode'] == 'user'){
		
			$column = 'id';
			unset($data['method']);
			unset($data['auth_key']);
			$condtion = array('userid'=>$userid);
			if(isset($data['username'])){
				$userArray['username'] = $data['username']; 
			}
			if(isset($data['password'])){
				$userArray['password'] = $data['password'];	
			}
			if(isset($data['email'])){
				$userArray['email'] = $data['email'];	
			}
			if(isset($data['phone'])){
				$userArray['phone'] = $data['phone'];	
			}
			if(isset($data['image'])){
				$userArray['profile_Image'] = $data['image'];	
			}
			$userArray['modified_date'] =  date('y-m-d h:i:s'); 
			$res = $db->update('users',$userArray,$userid,$column);
			if($res >0){
					$msg['return'] = 1;
					$msg['result'] = 'success';
					$msg['data']   = 'Profile updated successfully';
			}else{
				  $msg['return'] = 0;
				  $msg['result'] = 'error';
				  $msg['data']   = 'Some  error occurred.Please try again later';
			}
	  } else {
	  		$column = 'id';
			unset($data['method']);
			unset($data['auth_key']);
			$condtion = array('userid'=>$userid);
			if(isset($data['username'])){
				$userArray['username'] = $data['username']; 
			}
			if(isset($data['password'])){
				$userArray['password'] = $data['password'];	
			}
			if(isset($data['email'])){
				$userArray['email'] = $data['email'];	
			}
			if(isset($data['phone'])){
				$userArray['phone'] = $data['phone'];	
			}
			if(isset($data['image'])){
				$userArray['profile_Image'] = $data['image'];	
			}
			$userArray['modified_date'] =  date('y-m-d h:i:s'); 
			$res = $db->update('drivers',$userArray,$userid,$column);
			if($res >0){
					$msg['return'] = 1;
					$msg['result'] = 'success';
					$msg['data']   = 'Profile updated successfully';
			}else{
				  $msg['return'] = 0;
				  $msg['result'] = 'error';
				  $msg['data']   = 'Some  error occurred.Please try again later';
			}
	  }
    }else{
   		$msg['return'] = 0;
		$msg['result'] = 'error';
	    $msg['data']   = 'fields are required';
   }
   echo jsonreturn($msg);
}
/**************************************************************************
*	Function for show profile
***************************************************************************/
function showProfile($data = NULL){

	global $db;
	$msg = array();
	$user = array();
	if(!empty($data['userid'])&& !empty($data['mode'])){
		
		if($data['mode'] == 'user'){
		
			$qry = "SELECT * FROM users WHERE id = '".$data['userid']."'";
			$res = mysql_query($qry);
			$row = mysql_fetch_assoc($res);
			$user = array(
						 'userid'=>$row['id'],
						 'username'=>$row['username'],
						 'userimage'=>$row['profile_Image'],
						 'email'=>$row['email'],
						 'mobile'=>$row['phone'],
						 
						 );
			
			$msg['return'] = 1;
			$msg['result'] = 'success';
			$msg['data']   = !empty($user) ? $user:$user;
	  
	  } else {
	  
	  		$qry = "SELECT * FROM drivers WHERE id = '".$data['userid']."'";
			$res = mysql_query($qry);
			$row = mysql_fetch_assoc($res);
			$user = array(
						 'userid'=>$row['id'],
						 'username'=>$row['username'],
						 'userimage'=>$row['profile_Image'],
						 'email'=>$row['email'],
						 'mobile'=>$row['phone'],
						 'rating'=>$db->rating($row['id'])
						 );
			
			$msg['return'] = 1;
			$msg['result'] = 'success';
			$msg['data']   = !empty($user) ? $user:$user;
	  
	  }
	}else{
   		$msg['return'] = 0;
		$msg['result'] = 'error';
	    $msg['data']   = 'fields are required';
   }
   echo jsonreturn($msg);
}
/****************************************************************************************
*	Function for getign all cars
*****************************************************************************************/
function getCars(){

	global $db;
	$msg = array();
	$car = array();
	
		$qry = "SELECT * FROM cars";
		$res = mysql_query($qry);
		while($row = mysql_fetch_assoc($res)){
			
			$car[] = array(
						 'carid'=>$row['id'],
						 'car_name'=>$row['total_seats'].'-'.'Seat '.$row['car_name'],
						 'car_image'=>$row['car_image'],
						 'total_seats'=>$row['total_seats'],
						 'passenger_seats'=>$row['passenger_seats'].' '.'Passengers',
						 'luggages'=>$row['luggages'].' '.'Luggages'
					 );
		}
		
		$msg['return'] = 1;
		$msg['result'] = 'success';
	    $msg['data']   = !empty($car) ? $car:$car;
   echo jsonreturn($msg);
}
/***************************************************************************************
*	Function for get all Location 
**************************************************************************************/
function getLocations($data = NULL){
	global $db;
	$msg = array();
	$loc = array();
		
	  if(!empty($data['keyword'])){
		$qry = "SELECT * FROM location WHERE location_name LIKE '".$data['keyword']."%'";
		$res = mysql_query($qry);
		while($row = mysql_fetch_assoc($res)){
		  $loc[] = array(
						 'locationid'=>$row['id'],
						 'locationname'=>$row['location_name'],
					 );
		 }
		
		$msg['return'] = 1;
		$msg['result'] = 'success';
	    $msg['data']   = !empty($loc) ? $loc:$loc;
	 
	 } else {
	 
	 	$msg['return'] = 0;
		$msg['result'] = 'error';
	    $msg['data'][]   = 'fields are required';
	 }
   echo jsonreturn($msg);

}
/***********************************************************************************
* function for travel flight
***********************************************************************************/
function travel($data = NULL){
 	
	global $db;
	$msg = array();
	$loc = array();
	$response=array();
	  if(!empty($data['userid']) && !empty($data['dept_date']) && 
	  	 !empty($data['city_destination']) && !empty($data['passenger_name'])){
		
		    unset($data['method']);
			
			$data['created_date'] = date('Y-m-d H:i:s');
			
			$condition = "AND";
			
			$array = array(
						   'userid'=>$data['userid'],
						   'flight_number'=>$data['flight_number']
						   );
			$res = $db->select('airport_services',$array,$condition);
			
				
			if(mysql_num_rows($res) == 0){
			
				$res = $db->insert('airport_services',$data);
				
				if($res > 0){	
				
					$id = mysql_insert_id();
                    $arr = array(
						   		  'id'=> $id                        
						        );
			$res1 = $db->select('airport_services',$arr,$condition);
			
            $result = mysql_fetch_assoc($res1);
			
			$payment = $db->getPayment();
			
			$first_ammount = explode('-',$payment[0]);
			
			$second_ammount = explode('-',$payment[1]);
			
			$first_total =  (int)$first_ammount[0] + (int)$second_ammount[0];
			
			$second_total = (int)$first_ammount[1] + (int)$second_ammount[1];
			
			$response = array( 
							'id'=>$result['id'],
							'flightno'=>$result['flight_number'],
							'date'=>$result['dept_date'],
							'sourseAddress'=>$result['city_destination'],
							'destinationAddress'=>$result['drop_location'],
							'basic_fee'=>$payment[0],
							'airport_service'=>$payment[1],
							'total'=>$first_total.'-'.$second_total
							
						 );
			 $msg['return'] = 1;
					
			 $msg['result'] = 'success';
					
			 $msg['data'] = !empty($response) ? $response : $response;
					
				} else {
				
					$msg['return'] = 0;
					
					$msg['result'] = 'error';
					
					$msg['data'] = 'Some error occured.please try again later';
				} 
		 } else {
		 	
			
			$id = $data['flight_number'];	
			$column = 'flight_number';	  
			$rs =  $db->update('airport_services',$data,$id,$column);
			
			if($rs){
					
                    $arr = array(
						   		  'flight_number'=> $data['flight_number']                        
						        );
			$res1 = $db->select('airport_services',$arr,$condition);
			
            $result = mysql_fetch_assoc($res1);
			
			$payment = $db->getPayment();
			
			$first_ammount = explode('-',$payment[0]);
			
			$second_ammount = explode('-',$payment[1]);
			
			$first_total =  (int)$first_ammount[0] + (int)$second_ammount[0];
			
			$second_total = (int)$first_ammount[1] + (int)$second_ammount[1];
			
			$response = array( 
							'id'=>$result['id'],
							'flightno'=>$result['flight_number'],
							'date'=>$result['dept_date'],
							'sourseAddress'=>$result['city_destination'],
							'destinationAddress'=>$result['drop_location'],
							'basic_fee'=>$payment[0],
							'airport_service'=>$payment[1],
							'total'=>$first_total.'-'.$second_total
							
						 );
			 $msg['return'] = 1;
					
			 $msg['result'] = 'success';
					
			 $msg['data'] = !empty($response) ? $response : $response;
					
				
			} else {
				 $msg['return'] = 0;
					
				 $msg['result'] = 'error';
						
				 $msg['data'] = 'Some  error occurred.Please try again later';
			}
		 	
		 }

	 } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/**************************************************************************************
*	Send request to the Driver
**************************************************************************************/
function setLatLon($data = NULL){
	
	$msg = array();
	global $db;
	
	if(!empty($data['userid'])){
		
		$condition = "AND";
		
		$array = array('userid'=>$data['userid']);
		
		$res = $db->select('drivers_location',$array,$condition);
		
		if(mysql_num_rows($res)){
			
			$column = 'userid';
			
			$userArray = array(
						 'userid'=>$data['userid'],
						 'lat'=>$data['lat'],
						 'lon'=>$data['lon']
						 );
						 
			$res = $db->update('drivers_location',$userArray,$data['userid'],$column);
			
			if($res > 0){
			
				$msg['return'] = 1;
		
				$msg['result'] = 'success';
				
				$msg['data']   = 'Lat and lon added successfully';
				
			} else {
				
				$msg['return'] = 0;
					
				$msg['result'] = 'error';
					
				$msg['data'] = 'Some error occured.please try again later';
			}
			
		} else  {
		
			$arr = array(
						 'userid'=>$data['userid'],
						 'lat'=>$data['lat'],
						 'lon'=>$data['lon'],
						 'created_date'=>date('Y-m-d H:i:s')
						 );
			
			$res = $db->insert('drivers_location',$arr);
			
			if($res > 0){
			
				$msg['return'] = 1;
		
				$msg['result'] = 'success';
				
				$msg['data']   = 'Lat and lon added successfully';
				
			} else {
				
				$msg['return'] = 0;
					
				$msg['result'] = 'error';
					
				$msg['data'] = 'Some error occured.please try again later';
			}
		}
	} else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/****************************************************************************************
*	Function for send request to the drivers near by
****************************************************************************************/
function sendRequest($data = NULL){

	
	$msg = array();
	global $db;
	
	if(!empty($data['requestid']) && !empty($data['img'])){
		
		$condition = 'AND';
		$arr = array(
					'id'=>$data['requestid'],
					'request_status'=>'pending'
					);
		$res = $db->select('airport_services',$arr,$condition);
		
		if(mysql_num_rows($res) == 0){
		
		$qry = "UPDATE `airport_services` SET 
				`signature_Image` = '".$data['img']."',
				`request_status` = 'pending'
				WHERE id = '".$data['requestid']."'";
				
		$result = mysql_query($qry);
		
		if($result){
			
			
			$condition = "AND";
			
			$array = array('id'=>$data['requestid']);
			
			$res = $db->select('airport_services',$array,$condition);
			
			
			$condi = "AND";
		 
			$arr = array('id'=>$data['requestid']);
			 
			$rs = $db->select('taxi_services',$arr,$condi);
			
			$userid = mysql_fetch_assoc($rs);
			
		 	$device_token = $db->getDeviceToken($userid['userid']);
			
			//$msg = 'testig message'; 
			//$send_notification = $db->sendNotification($device_token,$msg);
			
			
			$msg['return'] = 1;
		
			$msg['result'] = 'success';
			
			$msg['data']   = 'Request send successfully';
	
	  } else {
	  
	  			$msg['return'] = 0;
					
				$msg['result'] = 'error';
					
				$msg['data'] = 'Some error occured.please try again later';
	  }
	  
	 } else {
	 
	 		$qry = "UPDATE `airport_services` SET 
				`signature_Image` = '".$data['img']."',
				`request_status` = 'pending'
				WHERE id = '".$data['requestid']."'";
				
			$result = mysql_query($qry);	
			
			$msg['return'] = 1;
		
			$msg['result'] = 'success';
			
			$msg['data']   = 'Matching in progress now';
	 }
	} else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);

}
/************************************************************************************
*	Function for users to drivers
*************************************************************************************/
function usersToDrivers($data = NULL){

	$msg = array();
	global $db;
	
	if(!empty($data['requestid']) && !empty($data['driverid'])){
		
		$qry = "UPDATE `airport_services` SET
				`request_status` = 'approved'
				WHERE id = '".$data['requestid']."'";
				
		$result = mysql_query($qry);
		
		if($result){
			
			$condi = "AND";
		 
			$arr = array('id'=>$data['requestid']);
			 
			$rs = $db->select('taxi_services',$arr,$condi);
			
			$userid = mysql_fetch_assoc($rs);
			
		 	$device_token = $db->getDeviceToken($userid['userid']);
			
			$msg = 'testig message'; 
			//$send_notification = $db->sendNotification($device_token,$msg);
			
			$condition = "AND";
			$arr = array(
						'requestid'=>$data['requestid'],
					    'driverid'=>$data['driverid']
						);
			$res = $db->select('user_to_driver',$arr,$condition);
			
			if(mysql_num_rows($res) == 0){
			
				$userRequest = array(
									 'requestid'=>$data['requestid'],
									 'driverid'=>$data['driverid'],
									 'created_date'=>date('Y-m-d H:i:s')
									);
				$result = $db->insert('user_to_driver',$userRequest);					
									
									
				if($result > 0){
				
					$msg['return'] = 1;
			
					$msg['result'] = 'success';
					
					$msg['data']   = 'Request assigned Successfully.';
					
				} else {
					
					$msg['return'] = 0;
						
					$msg['result'] = 'error';
						
					$msg['data'] = 'Some error occured.please try again later';
				}
	
		} else {
			
				$msg['return'] = 0;
					
				$msg['result'] = 'error';
					
				$msg['data'] = 'The request already assigned to other driver.';
		}  
	  
	  } else {
	  
	  			$msg['return'] = 0;
					
				$msg['result'] = 'error';
					
				$msg['data'] = 'Some error occured.please try again later';
	  }
	} else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/***************************************************************************************
*	Function for get Request
****************************************************************************************/
function getRequest($data = NULL){

	$msg = array();
	global $db;
	$users = array();
	if(!empty($data['driverid'])){
	
	
			
			$qry = "SELECT * FROM `airport_services` WHERE request_status = 'pending'
					OR request_status = 'approved'";
			
			$result = mysql_query($qry);
			
				$basic_fee = $db->getPayment();
				
				$first_ammount = explode('-',$basic_fee[0]);
			
				$second_ammount = explode('-',$basic_fee[1]);
				
				$first_total =  (int)$first_ammount[0] + (int)$second_ammount[0];
				
				$second_total = (int)$first_ammount[1] + (int)$second_ammount[1];
			while($row_result = mysql_fetch_assoc($result)){
				
				
				
				$query = "SELECT * FRom user_to_driver WHERE 
						requestid ='".$row_result['id']."' AND 
						driverid = '".$data['driverid']."'";
				
				$res = mysql_query($query);
				
				if(mysql_num_rows($res) > 0){
					
					$driver_request = 'yes';
				} else {
					$driver_request = 'no';
				}
				
				$users[] = array(
								 'requestid'=>$row_result['id'],
								 
								 'userid'=>$row_result['userid'],
								 
								 'userimage'=>$db->getImage($row_result['userid']),
								 
								 'username'=>$db->getUsername($row_result['userid']),
								 
								 'phone_number'=>$db->getPhone($row_result['userid']),
								 
								 'flight_number'=>$row_result['flight_number'],
								 
								 'dept_date'=>$row_result['dept_date'],
								 
								 'signature'=>$row_result['signature_Image'],
								 
								 'basic_fee'=>$basic_fee[0],
								 
								 'airport_services'=>$basic_fee[1],
								 
								 'city_destination'=>$row_result['city_destination'],
								 
								 'total'=>$first_total.'-'.$second_total,
								 
								 'is_Accepted'=>$driver_request,
								 
								 'drop_location'=>$row_result['drop_location']
								);
			}
			
			$msg['return'] = 1;
		
			$msg['result'] = 'success';
			
			$msg['data']   = $users;
		
		
	} else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/***************************************************************************************
*	Function for Taxi Request
****************************************************************************************/
function taxiRequest($data = NULL){

	global $db;
	$msg = array();
	$loc = array();
	$response=array();
	  if(!empty($data['userid']) && !empty($data['souress_address'])){
		
		    unset($data['method']);
			
			$data['created_date'] = date('Y-m-d H:i:s');
			
			$condition = "AND";
			
			$array = array(
						   'userid'=>$data['userid'],
						   'souress_address'=>$data['souress_address']
						   );
			$res = $db->select('taxi_services',$array,$condition);
			
				
			if(mysql_num_rows($res) == 0){
			
				$data['status'] = 'pending';
				
				$res = $db->insert('taxi_services',$data);
				
				if($res > 0){	
				
					$id = mysql_insert_id();
                    $arr = array(
						   		  'id'=> $id                        
						        );
			$res1 = $db->select('taxi_services',$arr,$condition);
			
            $result = mysql_fetch_assoc($res1);
			
			$response = array( 
			
							'id'=>$result['id'],
							'soures_add'=>$result['souress_address'],
							'destination_add'=>$result['destination_address']	
						   );
			 $msg['return'] = 1;
					
			 $msg['result'] = 'success';
					
			 $msg['data'] = !empty($response) ? $response : $response;
					
				} else {
				
					$msg['return'] = 0;
					
					$msg['result'] = 'error';
					
					$msg['data'] = 'Some error occured.please try again later';
				} 
		 } else {
		 
		 			$msg['return'] = 0;
					
					$msg['result'] = 'error';
					
					$msg['data'] = 'Matching in progress now';
		 }

	 } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/***************************************************************************************
*	Function for get Taxi Request 
****************************************************************************************/
function getTaxiRequest($data = NULL){

	global $db;
	$msg = array();
	$response=array();
	  if(!empty($data['driverid'])){
	  
	  		$condition = "AND";
			
			$qry = "SELECT * FROM `taxi_services` WHERE status = 'pending'";
			
			$res = mysql_query($qry);
			
			while($row = mysql_fetch_assoc($res)){
			
			$taxi[] = array(
							'requestid'=>$row['id'],
									 
							'userimage'=>$db->getImage($row['userid']),
										 
							'username'=>$db->getUsername($row['userid']),
										 
							'phone_number'=>$db->getPhone($row['userid']),
									 
							'date'=>date("F-j-Y, g:i a",strtotime($row['created_date'])), 
									 
							'sourse_add'=>$row['souress_address'],
									 
							'destination_add'=>$row['destination_address'],
							
							'status'=>$row['status'],
									 
							'drivername'=>$db->getDriverName($data['driverid'])
				   );
		  }
			
			$msg['return'] = 1;
			
			$msg['result'] = 'success';
			
			$msg['data']   = !empty($taxi) ? $taxi : $taxi;	
	  
	  } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);

}
/***********************************************************************************
*	Function for Departure Taxi
***********************************************************************************/
function departureTaxi($data){

	global $db;
	$msg = array();
	$response=array();
	  if(!empty($data['driverid']) && !empty($data['requestid']) && 
	     !empty($data['source_address'])){
	  
	  	unset($data['method']);
		
		$condition = "AND";
		
		$array = array(
						'requestid'=>$data['requestid'],
						'driverid'=>$data['driverid'],
						'status'=>1
					  );
		$res = $db->select('departure_taxi',$array,$condition);
		
				
			$data['start_time'] = date('Y-m-d H:i:s');
			
			$data['created_date'] = date('Y-m-d H:i:s');
			
			$result = $db->insert('departure_taxi',$data);
			
			if($result){
			
				$msg['return'] = 1;
					
				$msg['result'] = 'success';
					
				$msg['data'] = 'Taxi time start successfully';
			
			} else {
			
				$msg['return'] = 0;
					
				$msg['result'] = 'error';
					
				$msg['data'] = 'Some error occured.please try again later';
			}
		
	  
	  } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/***************************************************************************************
*	Function for accept request
****************************************************************************************/
function acceptRequest($data = NULL){

	global $db;
	$msg = array();
	$response=array();
	  if(!empty($data['driverid']) && !empty($data['requestid'])){
	  
	  	unset($data['method']);
		
		$condition = "AND";
		
		$array = array(
						'requestid'=>$data['requestid'],
						'driverid'=>$data['driverid'],
						'status'=>'accept'
					  );
		$res = $db->select('accept_request',$array,$condition);
		
		$qry = "UPDATE `taxi_services` SET
				`status` = 'accept'
				WHERE id = '".$data['requestid']."'";
				
		 $r =  mysql_query($qry);
		
		if(mysql_num_rows($res) == 0){	
			
			$data['created_date'] = date('Y-m-d H:i:s');
			
			$data['status'] = 'accept';
			
			$result = $db->insert('accept_request',$data);
			
			if($result){
			
				$msg['return'] = 1;
					
				$msg['result'] = 'success';
					
				$msg['data'] = 'Accept Request  successfully';
			
			} else {
			
				$msg['return'] = 0;
					
				$msg['result'] = 'error';
					
				$msg['data'] = 'Some error occured.please try again later';
			}
		} else {
		
				$msg['return'] = 0;
					
				$msg['result'] = 'error';
					
				$msg['data'] = 'You have already accept this request';
		}
	  
	  } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/*************************************************************************************
*	For arrivel time
***************************************************************************************/
function arrivalTaxi($data){

	global $db;
	$msg = array();
	$response=array();
	$favorite = array();
	  if(!empty($data['driverid']) && !empty($data['requestid']) && 
	     !empty($data['destination_address'])){
	  
	  	unset($data['method']);
		
		$condition = "AND";
		
		$array = array(
						'requestid'=>$data['requestid'],
						'driverid'=>$data['driverid'],
						'status'=>1
					  );
		$res = $db->select('arrival_taxi',$array,$condition);
		
				
			$data['end_time'] = date('Y-m-d H:i:s');
			
			$data['created_date'] = date('Y-m-d H:i:s');
			
			$result = $db->insert('arrival_taxi',$data);
			
			if($result){
				
				$condition = "AND";
				
				$array = array('requestid'=>$data['requestid']);
				
				$result = $db->select('accept_request',$array,$condition);
				
				$driver_id = mysql_fetch_assoc($result);
				
				$start_time = $db->startTime($data['requestid']);
				
				$end_time = $db->endTime($data['requestid']);
				
				$to_time = strtotime($start_time);
				
				$from_time = strtotime($end_time);
				
				$total_time =  round(abs($to_time - $from_time) / 60,2);
				
				 
				
				$res = $db->select('add_to_favourite',$array,$condition);
				
				while($row = mysql_fetch_assoc($res)){
					
					$favorite = array(				
									'source_address'=>$row['source_address'],
									'destination_address'=>$row['destination_address'],
									'price'=>$total_time,
									'driver_nmae'=>$db->getDriverName($driver_id['driverid'])
									 );
				}
				$msg['return'] = 1;
					
				$msg['result'] = 'success';
					
				$msg['data'] = !empty($favorite) ? $favorite : $favorite;
			
			} else {
			
				$msg['return'] = 0;
					
				$msg['result'] = 'error';
					
				$msg['data'] = 'Some error occured.please try again later';
			}
		
	  
	  } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/**************************************************************************************
*	Function for Finished tour
***************************************************************************************/
function finishedTaxiRequest($data){

	global $db;
	$msg = array();
	$response=array();
	  if(!empty($data['requestid']) && !empty($data['driverid'])){
		 
		 $condition = "AND";
		 
		 $array = array(
		 				'requestid'=>$data['requestid'],
						'driverid'=>$data['driverid'],
		 				'status'=>'accept'
						);
		 
		 $res = $db->select('accept_request',$array,$condition);
		 
		 while($row = mysql_fetch_assoc($res)){
		 	
			$condi = "AND";
		 
			$arr = array('id'=>$data['requestid']);
			 
			$rs = $db->select('taxi_services',$arr,$condi);
			
			$userid = mysql_fetch_assoc($rs);
			
		 	$device_token = $db->getDeviceToken($userid['userid']);
			
			//$msg = 'testig message'; 
			//$send_notification = $db->sendNotification($device_token,$msg);
			
			$qry = "UPDATE `taxi_services` SET
				    `status` = 'complet'
					WHERE id = '".$data['requestid']."'";
				
		    $r =  mysql_query($qry);
			
			$qry = "UPDATE `accept_request` SET
				    `status` = 'complet'
					WHERE id = '".$data['requestid']."'";
				
		    $r =  mysql_query($qry);
		 }
		 
		 if($r){
		 
		 	$msg['return'] = 1;
		
			$msg['result'] = 'success';
			
			$msg['data']   = 'Request Finished successfully';
			
		 } else {
		 
		 		$msg['return'] = 0;
					
				$msg['result'] = 'error';
					
				$msg['data'] = 'Some error occured.please try again later';
		 }
	  } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/***************************************************************************************
*	Function for add to favourite
****************************************************************************************/
function addToFavourite($data = NULL){

	global $db;
	
	$msg = array();
	
	  if(!empty($data['requestid'])){
	  
	  	unset($data['method']);
		
		$condi = "AND";
		
		$array = array(
						'requestid'=>$data['requestid'],
						'source_address'=>$data['source_address'],
						'destination_address'=>$data['destination_address']
					   );
		
		$res = $db->select('add_to_favourite',$array,$condi);
		
		if(mysql_num_rows($res)){
		
			$msg['return'] = 0;
		
			$msg['result'] = 'error';
			
			$msg['data']   = 'You have alredy added into faviourite list';
			
		} else {
			
			$data['created_date'] = date('Y-d-m H:i:s');
			
			$result = $db->insert('add_to_favourite',$data);
			
			if($result){
				
				$msg['return'] = 1;
		
				$msg['result'] = 'success';
				
				$msg['data']   = 'Location added into the faviourite list.';
				
			} else {
				
				$msg['return'] = 0;
					
				$msg['result'] = 'error';
					
				$msg['data'] = 'Some error occured.please try again later';
			}
		}
	  
	  } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);

}
/************************************************************************************
*	Function for get Favorite lest of the users
*************************************************************************************/
function requestDetail($data = NULL){


	global $db;
	
	$msg = array();
	
	$favorite = array();
	  if(!empty($data['requestid'])){
	  
	  	
		if($data['request_type'] == 'taxi'){
		
			$condition = "AND";
			
			$array = array('requestid'=>$data['requestid']);
			
			$result = $db->select('accept_request',$array,$condition);
			
			$driver_id = mysql_fetch_assoc($result);
			
			$start_time = $db->startTime($data['requestid']);
			
			$end_time = $db->endTime($data['requestid']);
			
			$to_time = strtotime($start_time);
			
			$from_time = strtotime($end_time);
			
			$total_time =  round(abs($to_time - $from_time) / 60,2);
	
	
			
			$res = $db->select('add_to_favourite',$array,$condition);
			
			while($row = mysql_fetch_assoc($res)){
				
				$favorite = array(				
								'source_address'=>$row['source_address'],
								'destination_address'=>$row['destination_address'],
								'price'=>$total_time,
								'driver_nmae'=>$db->getDriverName($driver_id['driverid'])
								 );
			}
			
			$msg['return'] = 1;
			
			$msg['result'] = 'success';
			
			$msg['data']   = !empty($favorite) ? $favorite : $favorite;
		
		} else {
		
			$condition = "AND";
			
			$array = array('requestid'=>$data['requestid']);
			
			$result = $db->select('accept_airport_request',$array,$condition);
			
			$driver_id = mysql_fetch_assoc($result);
			
			$start_time = $db->startTime($data['requestid']);
			
			$end_time = $db->endTime($data['requestid']);
			
			$to_time = strtotime($start_time);
			
			$from_time = strtotime($end_time);
			
			$total_time =  round(abs($to_time - $from_time) / 60,2);
	
	
			
			$res = $db->select('add_to_favourite',$array,$condition);
			
			while($row = mysql_fetch_assoc($res)){
				
				$favorite[] = array(
								'source_address'=>$row['source_address'],
								'destination_address'=>$row['destination_address'],
								'price'=>$total_time,
								'driver_nmae'=>$db->getDriverName($driver_id['driverid'])
								 );
			}
			
			$msg['return'] = 1;
			
			$msg['result'] = 'success';
			
			$msg['data']   = !empty($favorite) ? $favorite : $favorite;
		}
			
	  } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/*************************************************************************************
*	Function for get favorite
*************************************************************************************/
function getFavoriteDrivers($data =  NULL){


	global $db;
	
	$msg = array();
	$driver = array();
	
	  if(!empty($data['userid'])){
	  
	  	$condi = "AND";
		
		$array = array('userid'=>$data['userid']);
		
		$res = $db->select('add_to_favourite',$array,$condi);
		
		while($row = mysql_fetch_assoc($res)){
		
			$requestids[] = $row['requestid'];
		}
		
		if(!empty($requestids)){
			
			$req_str = implode(',',$requestids);
			
			$qry = "SELECT * FROM accept_request WHERE requestid IN ($req_str)";
			
			$result = mysql_query($qry);
			
			while($row_result = mysql_fetch_assoc($result)){
		
				$driver_ids[] = $row_result['driverid'];	
			}
			
			if(!empty($driver_ids)){
				$driver = array();
				
				$driverids = implode(',',$driver_ids);
			
			 $qry = "SELECT * FROM drivers WHERE id IN ($driverids)";
				
				$result = mysql_query($qry);
				
				while($row_result = mysql_fetch_assoc($result)){
			
					$driver[] = array(
									 	'driverid'=>$row_result['id'],
										'username'=>$row_result['username'],
										'phone'=>$row_result['phone'],
										'profile_Image'=>$row_result['profile_Image'],
										'rating'=>$db->rating($row['id'])
										
									 );	
				}
				
				$msg['return'] = 1;
		
				$msg['result'] = 'success';
				
				$msg['data']   = !empty($driver) ? $driver : $driver;
			} else {
				
				$msg['return'] = 1;
		
				$msg['result'] = 'success';
				
				$msg['data']   = !empty($driver) ? $driver : $driver;
			}
		} else {
		
				$msg['return'] = 1;
		
				$msg['result'] = 'success';
				
				$msg['data']   = !empty($driver) ? $driver : $driver;
		}
		
	  } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/**************************************************************************************
*	Function for get favorite Address
***************************************************************************************/
function getFavoriteAddress($data = NULL){

	global $db;
	
	$msg = array();
	
	$driver = array();
	$sor = array();
	  if(!empty($data['userid'])){
	  
	  	$condition = "AND";
		
		$array = array('userid'=>$data['userid']);
		
		$res = $db->select('add_to_favourite',$array,$condition);
		
		while($row = mysql_fetch_assoc($res)){
			
			if(!empty($row['source_address'])){
				
				$sor[] = $row['source_address'];
			}
			if(!empty($row['destination_address'])){
				
				$sor[] = $row['destination_address'];
			}	
			
			
		}
								
				$msg['return'] = 1;
		
				$msg['result'] = 'success';
				
				$msg['data']   = !empty($sor) ? $sor : $sor;
	  } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/***************************************************************************************
*	Function for get All Reuest to spacefic driver
***************************************************************************************/
function getTotalDriverRequest($data = NULL){

	global $db;
	
	$msg = array();
	
	$driver = array();
	  
	  if(!empty($data['driverid']) ){
	  
	  	
		
	  		$qry = "SELECT COUNT(*) as total from taxi_services 
					WHERE status = 'pending'";
					
			$res = mysql_query($qry);
			
			$total = mysql_fetch_assoc($res);
			
			$total_request = $total['total'];
			
			$qry1 = "SELECT COUNT(*) as total from accept_airport_request 
					WHERE driverid = '".$data['driverid']."'";
					
			$res1 = mysql_query($qry1);
			
			$total1 = mysql_fetch_assoc($res1);
			
			$total_request1 = $total1['total'];
			
			$array = array(
							'total_taxi_request'=>$total_request,
							'total_airport_request'=>$total_request1
						   );
			
			$msg['return'] = 1;
			
			$msg['result'] = 'success';
					
			$msg['data']   = $array;
		
		
	  } else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/****************************************************************************************
*	Function for email verificaton 
*****************************************************************************************/
function emailVerfication($data = NULL){


	global $db;
	
	$msg = array();
	
	  if(!empty($data['userid']) && !empty($data['email'])){
	  
	  	
		$con = 'AND';
		
		$arr = array('email'=>$data['email']);
		
		$mail_check = $db->select('users',$arr,$con);
		
		if(mysql_num_rows($mail_check)){
		
			$msg['return'] = 0;
			
			$msg['result'] = 'error';
					
			$msg['data']   = 'Email already exist with us.please try another email';
		} else {
		
		$hash_code = md5($data['email']);
		
		$url = 'http://deftsoft.info/paxiapp/verification.php?token='.$hash_code;
	  	
		$to = $data['email'];
		
		$subject = "Email Verificaion";
		
		$message = "Please Click below link for email verification. \n \n";
		
		$message .= 'Link: '.$url."\n";
		
		$from = "Paxi";
		
		$headers = "From:" . $from;
		
		$send_mail = mail($to,$subject,$message,$headers);
		
		//if($send_mail){
			
			$arr = array('hash_code'=>$hash_code);
			$db->update('users',$arr,$data['email'],'email');
			;
			$userArray['modified_date'] =  date('y-m-d h:i:s');
			
			$userArray['email'] =  $data['email'];  
			
			$userArray['password'] =  $data['password'];
			
			$userArray['email_active'] =  'yes'; 
			
			$column = 'id';
			
			$userid = $data['userid'];
			
			$res = $db->update('users',$userArray,$userid,$column);
			
			if($res){
				
				$condition = 'AND';
				
				$array = array('email'=>$data['email'],'email_active'=>'yes');
				
				$result = $db->select('users',$array,$condition);
				
				if(mysql_num_rows($result)){
					
					$email = array('active' => 'yes');
					
				} else {
					
					$email = array('active' => 'no');
				}
				
				$msg['return'] = 1;
		
				$msg['result'] = 'success';
				
				$msg['data']   = $email;
				
			} else {
				
				$msg['return'] = 0;
		
				$msg['result'] = 'error';
				
				$msg['data']   = 'Some error occured.please try again later';
			}
		//} else {
		
				//$msg['return'] = 0;
		
				//$msg['result'] = 'error';
				
				//$msg['data']   = 'Some error occured.While sending the email';
	 // } 
	 }
	  }else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);

}
/***************************************************************************************
*	Function for entering the credit details
****************************************************************************************/
function cardDetails($data = NULL){
	


	global $db;
	
	$msg = array();
	
	  if(!empty($data['userid']) && !empty($data['card_type'])){
	  	
		
		$array = array(
						   'card_type'=>$data['card_type'],
						   'account_number'=>$data['account_number'],
						   'credit_card_name'=>$data['credit_card_name'],
						   'exp_date'=>$data['exp_date'],
						   'cvc'=>$data['cvc']
					   );
			
			$colunm = 'id';
			
			$result = $db->update('users',$array,$data['userid'],$colunm);
			
			if($result){
				
				$msg['return'] = 1;
		
				$msg['result'] = 'success';
				
				$msg['result'] = 'Card Details enterd successfully';
			
			} else {
				
				$msg['return'] = 0;
		
				$msg['result'] = 'error';
				
				$msg['data']   = 'Some error occured.Please try again later';
		 }
	  
	  }else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);


}
/***************************************************************************************
*	Function for pick up location of the users
*************************************************************************************/
function pickUpLocation($data = NULL){

	global $db;
	
	$msg = array();
	
	$drivers = array();
	  if(!empty($data['userid']) && !empty($data['lat']) && !empty($data['lon'])){
	  
	  	 $string = "SELECT userid,lat,lon,
		 		  ( 3959 * acos( cos( radians( ".$data['lat']." ) ) *
				   cos( radians( lat ) ) * cos( radians( lon ) 
				   - radians( ".$data['lon']." ) ) + 
				   sin( radians( ".$data['lat']." ) ) * sin( 					                   radians( lat ) ) ) ) AS distance
                   FROM   drivers_location  
				   HAVING (distance <= '20') ORDER BY distance ASC";
		
		$res = mysql_query($string);
		
		while($row = mysql_fetch_assoc($res)){
			
				$drivers[] = array(
								   	'driverid' =>$row['userid'],
									'drivername'=>$db->getDriverName($row['userid']),
									'lat'=>$row['lat'],
									'lon'=>$row['lon']
								   );
		}
		
	  			$msg['return'] = 1;
		
				$msg['result'] = 'success';
			
				$msg['result'] = !empty($drivers) ? $drivers : $drivers;
	  }else {
	 
	 	$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
}
/*************************************************************************************
*	Function for  
**************************************************************************************/
function setCardDetails($data = NULL){
	global $db;
	
	$msg = array();
	
	$drivers = array();
	  if(!empty($data['userid'])){
	  	
		unset($data['method']);
		
		
		$array = array(
						'card_type'=>$data['card_type'],
						'account_number'=>$data['account_number'],
						'credit_card_name'=>$data['credit_card_name'],
						'exp_date'=>$data['exp_date']
					   );
		$res = $db->update('users',$array,$data['userid'],'id');
		
		if($res){
				
				$msg['return'] = 1;
		
				$msg['result'] = 'success';
			
				$msg['result'] = 'Registration successfully';
		} else {
			
				$msg['return'] = 0;
		
				$msg['result'] = 'error';
				
				$msg['data']   = 'Some error occured.Please try again later';
		}
	  } else {
	  	
		$msg['return'] = 0;
		
		$msg['result'] = 'error';
		
	    $msg['data']   = 'fields are required';
	 }
   echo jsonreturn($msg);
	  
}
?>