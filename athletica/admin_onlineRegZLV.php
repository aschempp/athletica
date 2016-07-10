<?php

/********************
 *
 *	admin_onlineRegZLV.php
 *	----------------------
 *	get online registrations from athletes (only for ZLV)
 *
 *************************/
                            

require('./lib/common.lib.php');

require('./lib/cl_xml_data.lib.php');
require('./lib/cl_http_data.lib.php');         
       
if(AA_connectToDB() == FALSE)	{		// invalid DB connection
	return;
}

if(AA_checkMeetingID() == FALSE){		// no meeting selected
	return;		// abort
}     
	
	// get xml file for registrations from athletes   
    // get uploaded XML file and read its content
    $fd = fopen($_FILES['xmlfile']['tmp_name'], 'rb');
    $content = fread($fd, filesize($_FILES['xmlfile']['tmp_name']));
     
	if(!$fd){
			AA_printErrorMsg($strErrFtpNoGet);
	}else{  
			$xml = new XML_data();
			$arr_noCat = $xml->load_xml($_FILES['xmlfile']['tmp_name'], 'regZLV', ''); 
            if ($arr_noCat['cat'][0] != ''){    
                                  
                foreach ($arr_noCat['cat'] as $key => $val){
                      $mess = str_replace('%NAME%', $val, $strXmlNoCat);   
                      echo $mess; 
                }
            }
            if ($arr_noCat['lic'][0] != ''){    
                                  
                foreach ($arr_noCat['lic'] as $key => $val){
                      $mess = str_replace('%NAME%', $val, $strXmlNoLic);   
                      echo $mess; 
                }
            }
             if ($arr_noCat['club'][0] != ''){    
                                  
                foreach ($arr_noCat['club'] as $key => $val){
                      $mess = str_replace('%NAME%', $val, $strXmlNoClub);   
                      echo $mess; 
                }
            }
            
            
            
            
            echo "<br><br>&nbsp;&nbsp; Anmeldedaten wurden erfolgreich importiert.";
           
	}
	
    fclose($fd);
                    


?>
