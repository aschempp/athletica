<?php

/********************
 *
 *	admin_onlineRegUKC.php
 *	----------------------
 *	get online registrations from UBS Kids Cup 
 *
 *************************/
                            

require('./lib/common.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/cl_xml_simple_data.lib.php');   
require('./lib/cl_http_data.lib.php');         
       
if(AA_connectToDB() == FALSE)	{		// invalid DB connection
	return;
}

if(AA_checkMeetingID() == FALSE){		// no meeting selected
	return;		// abort
}    

//
//    Display enrolement list
//

if (isset($_POST['ukc_meeting'])){
    $ukc_meeting = $_POST['ukc_meeting'];
}
$page = new GUI_Page('admin_onlineRegUKC.php');
$page->startPage();
$page->printPageTitle($strImportUKC_Title);     

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/administration/base.html', $strHelp, '_blank');
$menu->printMenu();

 
	
	// get xml file for registrations from athletes   
    // get uploaded XML file and read its content
    $fd = fopen($_FILES['xmlfile']['tmp_name'], 'rb');
    $content = fread($fd, filesize($_FILES['xmlfile']['tmp_name']));
     
	if(!$fd){
			AA_printErrorMsg($strErrFtpNoGet);
	}else{  
			$xml = new XML_simple_data();                 
			$arr_noCat = $xml->load_xml_simple($_FILES['xmlfile']['tmp_name'], 'regUKC', '', $ukc_meeting);        
             
            if ($arr_noCat['cat'][0] != ''){    
                                  
                foreach ($arr_noCat['cat'] as $key => $val){
                      $mess = str_replace('%NAME%', $val, $strXmlNoCatAge);   
                      echo $mess; 
                }
            }
            
             if ($arr_noCat['club'][0] != ''){    
                                  
                foreach ($arr_noCat['club'] as $key => $val){
                      $mess = str_replace('%NAME%', $val, $strXmlNoClub);   
                      echo $mess; 
                }
            }
            if ($arr_noCat['athClub'][0] != ''){    
                                  
                foreach ($arr_noCat['athClub'] as $key => $val){
                      $mess = str_replace('%NAME%', $val, $strXmlNoAthClub);   
                      echo $mess; 
                }
            }
                            
           echo "<p> $strImportUKC</p>";  
           
	}
	
    fclose($fd);   

?>         