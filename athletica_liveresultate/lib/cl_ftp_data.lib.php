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


class FTP_data{
	
	var $ftpc;
    var $error;
	
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
			//AA_printErrorMsg($strErrFtpNoPut);
			return false;
		}else{ 
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
    
    function delete_file( $remote){
        global $strErrFtpNoDel;
        
        @ftp_pasv($this->ftpc, true);
        $delete = @ftp_delete($this->ftpc, $remote);  
        if(!$delete){
            $this->error = $strErrFtpNoDel;
            return false;
        }else{
            //ftp_chmod($this->ftpc, 0777, $remote); (php5 only)
            return true;
        }
        
    }
    
    function get_filelist( $remote_dir){    
        
        @ftp_pasv($this->ftpc, true);
        $fileList = @ftp_nlist($this->ftpc, $remote_dir);
        if(!$fileList){    
            return false;
        }else{
            //ftp_chmod($this->ftpc, 0777, $remote); (php5 only)
            return $fileList;
        }
        
    }
    // create directory through FTP connection
    function ftpMkdir($path, $newDir) { 
    
       ftp_chdir($this->ftpc, $path); // go to destination dir
       
       if(ftp_mkdir($this->ftpc,$newDir)) { // create directory
           return $newDir;
       } else {
           return false;       
       }  
} 
	
	function close_connection(){
		ftp_close($this->ftpc);
	}
}

?>

 