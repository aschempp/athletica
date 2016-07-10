<?php

require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

require('./lib/cl_xml_data.lib.php');
require('./lib/cl_http_data.lib.php');         
       
if(AA_connectToDB() == FALSE)    {        // invalid DB connection
    return;
}

if(AA_checkMeetingID() == FALSE){        // no meeting selected
    return;        // abort
}



//
//    Display enrolement list
//

$page = new GUI_Page('admin_palmares');
$page->startPage();
$page->printPageTitle($strPalmaresUpdate);
?>
   
	<form name="import" method="post" enctype="multipart/form-data">
    	<input type="file" name="file" />
        <br />
        <br />
        <input type="submit" name="submit" value="<? echo $strSave;?>" />
    </form>
<?php

	if(isset($_POST["submit"]))
	{
		$file = $_FILES['file']['tmp_name'];
		$handle = fopen($file, "r");
		$c = 0;
        $sql = mysql_query("TRUNCATE TABLE palmares");
		while(($filesop = fgetcsv($handle, 10000, ";")) !== false)
		{
            if ($c ==0) {
                $c = $c + 1;
                continue;
            }
			$license = $filesop[0];
            $international = $filesop[1];
			$national = $filesop[2];
			$c = $c + 1;
            //echo $palmares;
			$sql = mysql_query("INSERT INTO palmares (license, palmares_international, palmares_national) VALUES ('$license','$international','$national')");
		}
		
			if($sql){
                $c = $c -1;
				echo $strPalmaresUpdated;
			}else{
				echo $strError;
			}

	}
?>
    
