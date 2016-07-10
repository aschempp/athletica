<?php

/**********
 *
 *	functions to separate athletica into 2 parts: normal and speaker mode
 *	-------------------------
 *	providing login handling (low security, no ip check -> fishing possible)
 */

if (defined('AA_CL_PROTECT_LIB_INCLUDED'))
{
	return;
}
define('AA_CL_PROTECT_LIB_INCLUDED', 1);

require("./lib/common.lib.php");

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}


class Protect{
	
	function isLoggedIn($meeting){
		
		return $_SESSION["m".$meeting]['loggedin']; //$this->loggedin;
		
	}
	
	function isRestricted($meeting){
		
		$res = mysql_query("SELECT Passwort FROM
					meeting
				WHERE
					xMeeting = $meeting");
		if(mysql_errno() > 0){
			return false;
		}else{
			
			$row = mysql_fetch_array($res);
			if(empty($row[0])){
				return false;
			}else{
				return true;
			}
			
		}
		
	}
	
	function login($meeting, $password){
		$p = md5($password);
		$res = mysql_query("SELECT * FROM
					meeting
				WHERE
					xMeeting = $meeting
				AND	Passwort = '$p'");
		if(mysql_errno() > 0){
			return false;
		}else{
			if(mysql_num_rows($res) > 0){
				
				// successful login
				$_SESSION["m".$meeting]['loggedin'] = true;
				
				return true;
				
			}else{
				return false;
			}
		}
		
	}
	
	function secureMeeting($meeting, $password){
		$p = md5($password);
		mysql_query("UPDATE meeting SET
				Passwort = '$p'
			WHERE
				xMeeting = $meeting");
		if(mysql_errno() > 0){
			return false;
		}else{
			$_SESSION["m".$meeting]['loggedin'] = false;
			return true;
		}
		
	}
	
	function unsecureMeeting($meeting){
		$_SESSION["m".$meeting]['loggedin'] = true;
		
		mysql_query("UPDATE meeting SET
				Passwort = ''
			WHERE
				xMeeting = $meeting");
		if(mysql_errno() > 0){
			return false;
		}else{
			return true;
		}
		
	}
	
}



?>
