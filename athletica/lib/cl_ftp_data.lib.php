<?php

if (!defined('AA_CL_FTP_DATA_LIB_INCLUDED'))
{
	define('AA_CL_FTP_DATA_LIB_INCLUDED', 1);
}else{
	return;
}

/************************************
 *
 * FTP data
 * 
 * puts the result file on the SLV server
 *
/************************************/

//require("common.lib.php");

/*if(AA_connectToDB() == FALSE){ // invalid db connection
	return;
}*/

if(AA_checkMeetingID() == FALSE){		// no meeting selected
	return;		// abort
}


class FTP_data{
	
	var $ftpc;
	
	function open_connection($host, $user, $pass){
		global $strErrFtpNoConn, $strErrFtpNoLogin;
		
		if($this->ftpc){ // connection already opened
			return true;
		}
		
		$this->ftpc = @ftp_connect($host);
		if(!$this->ftpc){
			AA_printErrorMsg($strErrFtpNoConn);
			return false;
		}else{
			$ftpl = @ftp_login($this->ftpc, $user, $pass);
			if(!$ftpl){
				AA_printErrorMsg($strErrFtpNoLogin);
				return false;
			}else{
				return true;
			}
		}
	}
	
	function put_file($local, $remote){
		global $strErrFtpNoPut;
		
		@ftp_pasv($this->ftpc, true);
		$upload = @ftp_put($this->ftpc, $remote, $local, FTP_BINARY);
		if(!$upload){
			AA_printErrorMsg($strErrFtpNoPut);
			return false;
		}else{
			//ftp_chmod($this->ftpc, 0777, $remote); (php5 only)
			return true;
		}
		
	}
	
	function get_file($local, $remote){
		global $strErrFtpNoGet;
		
		@ftp_pasv($this->ftpc, true);
		$download = @ftp_get($this->ftpc, $local, $remote, FTP_BINARY);
		if(!$download){
			AA_printErrorMsg($strErrFtpNoGet);
			return false;
		}else{
			return true;
		}
		
	}
	
	function close_connection(){
		ftp_close($this->ftpc);
	}
}

?>
