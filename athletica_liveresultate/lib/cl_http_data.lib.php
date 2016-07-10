<?php

if (!defined('AA_CL_HTTP_DATA_LIB_INCLUDED'))
{
	define('AA_CL_HTTP_DATA_LIB_INCLUDED', 1);
}else{
	return;
}

/************************************
 *
 * HTTP_data
 *
 * Can send post and get request for a http server.
 * We can also download files in background and show progress
 *
/************************************/

//require("common.lib.php");

/*if(AA_connectToDB() == FALSE){ // invalid db connection
	return;
}*/

//if(AA_checkMeetingID() == FALSE){		// no meeting selected
//	return;		// abort
//}


class HTTP_data{
	// sends a get request for (usually) downloading a file
	function send_get($host, $uri, $parseType, $fileName="", $showProcess=false){
		$sock = fsockopen($host, 80, $errno, $errstr, 3600); // important: timeout is 1h, else the download could cancel
		if(!$sock){
			AA_printErrorMsg($strErrHttpSock);
			return false;
		}
		fwrite($sock, "GET $uri HTTP/1.0\r\n");
		fwrite($sock, "Host: $host\r\n");
		fwrite($sock, "Connection: Close\r\n");
		fwrite($sock, "\r\n");
		
		$headers = "";
		$totalbytes = 0;
		while ($str = trim(fgets($sock, 4096))){
			$headers .= "$str\n";
			/*if(strpos($str, "400") !== false || strpos($str, "404") !== false || strpos($str, "403") !== false){
				fclose($sock); // server return error (not found / bad request)
				AA_printErrorMsg($strErrHttpBad);
				return false;
			}*/
			$a = explode(":", $str); // get total lenght of file
			if($a[0] == "Content-Length"){
				$totalbytes = trim($a[1]);
			}
		}
		
		$body = "";
		$currentbytes = 0;
		$count = 5;
		while (!feof($sock)){
			$str = fread($sock, 8192);
			$body .= $str;
			$currentbytes += strlen($str); // calculate state of download progress
			if($totalbytes != 0){ $percentage = (($currentbytes / $totalbytes)*100); }
			if($percentage >= $count){
				$width = $count*1.5; // width of progress bar (max 150px)
				if($showProcess){ // if progress bar is available
					?>
<script type="text/javascript">
document.getElementById("progress").width="<?php echo $width ?>";
</script>
					<?php
				}
				ob_flush();
				flush();
				$count += 5;
			}
		}
		fclose($sock);
		
		if($showProcess){
			?>
<script type="text/javascript">
document.getElementById("progress").width="150";
</script>
			<?php
		}
		
		// get temp directory
		$temppath = dirname($_SERVER['SCRIPT_FILENAME'])."/tmp/";
		
		if($parseType == "ini"){
			// parse content as ini and return parameters
			// first we have to save it temporary
			$filePath .= $temppath."athletica.ini";
			$fp = fopen($filePath, 'w');
			if (@fwrite($fp, $body) === FALSE) {
				fclose($fp);
				AA_printErrorMsg($strErrHttpWrite);
				return false;
			}else{
				fclose($fp);
				return parse_ini_file($filePath);
			}
		}elseif($parseType == "file" && !empty($fileName)){
			// save data into file
			$filePath .= $temppath.$fileName;
			$fp = fopen($filePath, 'wb');
			if (@fwrite($fp, $body) === FALSE) {
				fclose($fp);
				AA_printErrorMsg($strErrHttpWrite);
				return false;
			}else{
				fclose($fp);
				return $filePath;
			}
		}
		
	}
	          
	// function for sending a post request... usually for sending form data
	//  - host should return ini data
	function send_post($host, $uri, $post, $parseType="ini", $fileName=""){
		$sock = fsockopen($host, 80, $errno, $errstr, 30);
		if(!$sock){
			AA_printErrorMsg($strErrHttpSock);
			return false;
		}
		$data = $post;
		fwrite($sock, "POST $uri HTTP/1.0\r\n");
		fwrite($sock, "Host: $host\r\n");
		fwrite($sock, "Content-type: application/x-www-form-urlencoded\r\n");
		fwrite($sock, "Content-length: " . strlen($data) . "\r\n");
		fwrite($sock, "Accept: */*\r\n");
		fwrite($sock, "\r\n");
		fwrite($sock, "$data\r\n\r\n");  
		
		$headers = "";
		$totalbytes = 0;
		while ($str = trim(fgets($sock, 4096))){
			$headers .= "$str\n";
			if(strpos($str, "400") !== false || strpos($str, "404") !== false || strpos($str, "403") !== false || strpos($str, "503") !== false){
				fclose($sock); // server return error (not found / bad request)
				AA_printErrorMsg($GLOBALS['strErrHttpBad']);
				return false;
			}
		}     
		
		$body = "";
		while (!feof($sock)){
			$body .= fread($sock, 8192); 
		}      
		fclose($sock);
		
       
		// get temp directory
		$temppath = dirname($_SERVER['SCRIPT_FILENAME'])."/tmp/";
		if(!is_dir($temppath)){
			@mkdir($temppath, 0777);
		}
        
        if (empty($fileName)){
            return 1;
        }
		
		if($parseType == "ini"){
			// parse content as ini and return parameters
			// first we have to save it temporary
			$filePath .= $temppath."athletica.ini";
			$fp = fopen($filePath, 'w');
			if (@fwrite($fp, $body) === FALSE) {
				fclose($fp);
				AA_printErrorMsg($strErrHttpWrite);
				return false;
			}else{
				fclose($fp);
				return parse_ini_file($filePath);
			}
		}elseif($parseType == "file" && !empty($fileName)){
			// save data into file 	
            
            if (strpos($body, "\r\n\r\n") != false){      // separate body from header because of Fredy's PC   
                $body = substr($body, strpos($body, "\r\n\r\n") + 4);   
            }  
            		
            $filePath .= $temppath.$fileName;
			$fp = fopen($filePath, 'wb');   
            
			if (@fwrite($fp, $body) === FALSE) {
				fclose($fp);
				AA_printErrorMsg($strErrHttpWrite);
				return false;
			}else{
				fclose($fp);
				return $filePath;
			}
		}
	}
}

?>
