<?php

require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

require('./lib/cl_xml_data.lib.php');
require('./lib/cl_http_data.lib.php');         
	   
if(AA_connectToDB() == FALSE)	{		// invalid DB connection
	return;
}

if(AA_checkMeetingID() == FALSE){		// no meeting selected
	return;		// abort
}



//
//	Display enrolement list
//

$page = new GUI_Page('admin_records');
$page->startPage();
$page->printPageTitle($strUpdateRecords);
?>
<p/>

<?php
           
$http = new HTTP_data();
$webserverDomain = $cfgSLVhost; // domain of swiss-athletics webserver

// handle arguments
$login = false;
$record_type = "";
$record_season = "";
$record_discipline = "";
$record_category = "";
$record_result = "";
$record_firstname = "";
$record_lastname = "";
$record_date = "";
$record_city = "";
$slvsid = "";
$list = false;
$reg = false;

if($_POST['arg'] == "login"){
	
	// sending login information and global last change date of the base_data
	$result = mysql_query("SELECT global_last_change FROM base_log where type like 'base_%' ORDER BY id_log DESC LIMIT 1");
	if(mysql_errno > 0){
		AA_printErrorMsg(mysql_errno().": ".mysql_error());
	}else{
		$post = "clubnr=".urlencode($_POST['clubnr'])."&pass=".urlencode($_POST['pass'])
			."&glc=".urlencode($glc)."&type=".urlencode($type);
		$result = $http->send_post($webserverDomain, '/meetings/athletica/login.php', $post, 'ini');
		if(!$result){
			AA_printErrorMsg($strErrLogin);
		}else{
			switch($result['login']){
				case "error":
				AA_printErrorMsg($result['error']);
				break;
				
				case "ok":
				$login = true;
				echo "<p>$strLoginTrue</p>";
				$slvsid = $result['sid']; // remember session id from slv server
				$basefiles = explode(":",$result['files']); // get files to download
				$filetype = $result['filetype']; // return complete or update, 
								//maybe the glc-date is too old, so the login script returned a complete set
				$newglc = substr($result["newglc"],0,4)."-".substr($result["newglc"],4,2)."-".substr($result["newglc"],6,2);
				
				$_POST['arg'] = 'update';
				$_POST['slvsid'] = $slvsid;
				
				break;
				
				case "denied":
				$login = false;
				echo "<p>$strLoginFalse</p>";
				break;
			}
		}
	}
}

if(!empty($_POST['slvsid'])){
	
	if($_POST['arg'] == "update"){
		// get meetinglist
		
		$post = "sid=".$_POST['slvsid'];
		$result = $http->send_post($webserverDomain, '/meetings/athletica/export_records.php', $post, 'ini');
		if(!$result){
            AA_printErrorMsg($strErrLogin);
        }else{
            switch($result['login']){
                
                case "ok":
                $login = true;
                
                $slvsid = $_POST['slvsid']; // remember session id from slv server 

                $record_type = explode("+",$result['record_type']);
                $record_season = explode("+",$result['season']);
                $record_discipline = explode("+",$result['discipline']);
                $record_category = explode("+",$result['category']);
                $record_result = explode("+",$result['result']);
                $record_firstname = explode("+",$result['firstname']);
                $record_lastname = explode("+",$result['lastname']);
                $record_date = explode("+",$result['date']);
                $record_city = explode("+",$result['city']);
                
                $update = true;
                
                break;
                
                case "denied":
                $login = false;
                echo "<p>$strLoginFalse</p>";
                break;
            }
        }
		
	}
   
       
}

if(!$login){
    
    // show login form

?>
<form action='admin_records.php' name='base' method='post' target='_self'>  
            
<table class='dialog'>
<tr>
    <th><?php echo $strLoginSlv; ?></th>
</tr>
<tr>
    <td>
        <table class='admin'>
        
        <input type="hidden" name="arg" value="login">
        <tr>
            <td>
                <?php echo $strClubNr ?>
            </td>
            <td>
                <input type="text" name="clubnr" value="">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo $strPassword ?>
            </td>
            <td>
                <input type="password" name="pass" value="">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="submit" name="submit" value="<?php echo $strLogin ?>">
            </td>
        </tr>
        </form>    
        </table>
    

<?php
} // end if login

if($update) {
    $i = 0;
    $sql = "TRUNCATE TABLE rekorde";
    $res = mysql_query($sql);
             if(mysql_errno() > 0){
                    AA_printErrorMsg(mysql_errno().": ".mysql_error());
             }
    
    foreach($record_type as $record){
        $sql = "INSERT INTO rekorde (record_type, season, discipline, category, result, firstname, lastname, date, city)
                    VALUES('$record_type[$i]', 
                            '$record_season[$i]',
                            '$record_discipline[$i]',
                            '$record_category[$i]',
                            '$record_result[$i]',
                            '$record_firstname[$i]',
                            '$record_lastname[$i]',
                            '$record_date[$i]',
                            '$record_city[$i]')";
                     $res = mysql_query($sql);
                     if(mysql_errno() > 0){
                            AA_printErrorMsg(mysql_errno().": ".mysql_error());
                     }
        
        
        $i++;
    }
    echo $strUpdateSuccess;
    
}



$page->endPage();
?>
