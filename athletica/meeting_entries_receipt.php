<?php

/**********
 *
 *	meeting_entries_receipt.php
 *	-----------------------------
 *	
 */   
     
require('./lib/cl_gui_dropdown.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)	{				// invalid DB connection
	return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
	return;		// abort
}   

$athleteSearch = 0;
if(!empty($_POST['athleteSearch'])) {
    $athleteSearch = $_POST['athleteSearch'];    // store selected athlete enrolement from search 
}  
   
$club = 0;
if(!empty($_POST['club'])) {
    $club = $_POST['club'];                             // store selected club
    $club_clause=" AND  at.xVerein = " . $club;        // athlete limit of club
}

if ($_POST['arg'] == 'change_clubSearch'){    
 $athleteSearch = 0; 
}  

             
 
$page = new GUI_Page('meeting_entries_receipt');
$page->startPage();
$page->printPageTitle($strReceipt);

?>
<script type="text/javascript">
<!--
	function setPrint()
	{     
		document.printdialog.formaction.value = 'print'
		document.printdialog.target = '_blank';
	}             	
//-->
</script>    

<table class='dialog'>   
    <tr>
	    <th class='dialog' colspan='3'><?php echo $strSelection; ?></th>
    </tr>
    <tr>
	    <td colspan='3'>
		    <table>  
             <tr>          
                <td class='dialog'><?php echo $strClub; ?></td>     
                    <form action='meeting_entries_receipt.php' method='post' name='clubSearch' > 
                    <input name='arg' type='hidden' value='change_clubSearch' /> 
                    <input name='club' type='hidden' value='<?php echo $club; ?>' />  
                    <input name='athleteSearch' type='hidden' value='<?php echo $athleteSearch; ?>' />      
                    <?php
               
                if (!empty($club))  
                    $dd = new GUI_ClubDropDown($club, false, 'document.clubSearch.submit()', false); 
                else 
                    $dd = new GUI_ClubDropDown(0, false, 'document.clubSearch.submit()', false);  
               
                ?>
                    </form>       
               </td>     
            </tr>
            
            <tr>          
                <td class="dialog"><?php echo $strAthlete ?></td> 
                <td class="forms">   
                    <form action='meeting_entries_receipt.php' method='post' name='athleteSearch'>   
                    <input name='arg' type='hidden' value='change_athlete' /> 
                    <input name='club' type='hidden' value='<?php echo $club; ?>' />  
                    <input name='athleteSearch' type='hidden' value='<?php echo $athleteSearch; ?>' />                 
                    <?php  
              
                $dropdown = new GUI_Select('athleteSearch', 1, "document.athleteSearch.submit()");    
                                                                                      
                $sql_athlets = "SELECT    
                                at.Vorname, 
                                at.Name, 
                                a.xAnmeldung 
                         FROM 
                                anmeldung AS a
                                LEFT JOIN athlet AS at USING (xAthlet)
                                
                         WHERE 
                                a.xMeeting = ".$_COOKIE['meeting_id'] . " 
                                " . $club_clause . "
                         ORDER BY at.Name, at.Vorname";    
                                                             
                $result_a=mysql_query($sql_athlets);
                if(mysql_errno() > 0) {
                    AA_printErrorMsg("Line " . __LINE__ . ": ". mysql_errno() . ": " . mysql_error());
                }else{
                     if(mysql_num_rows($result_a) > 0)  {   
                        while( $row_athlets=mysql_fetch_row($result_a)) {
                            $name_athlete=$row_athlets[1] . " " . $row_athlets[0];
                            $dropdown->addOption($name_athlete, $row_athlets[2]); 
                        }
                        $dropdown->addOption("[".$strAll."]", "-1");
                        $dropdown->selectOption($athleteSearch);
                        $dropdown->addOptionNone();
                        $dropdown->printList();  
                     }
                     else
                        {$search_occurred=true;
                        $search_match;   
                     } 
                }
              ?> 
                    </form>   
                </td> 
            </tr>
		</table>
	</td>
</tr>    

</table>     
<br>      
<table>
<tr>
	<td>
        <form action='print_meeting_receipt.php' method='get' name='printdialog'>   
            <input type='hidden' name='formaction' value=''>
            <input name='club' type='hidden' value='<?php echo $club; ?>' />  
            <input name='athleteSearch' type='hidden' value='<?php echo $athleteSearch; ?>' />   
		    <button name='print' type='submit' onClick='setPrint()'>
			<?php echo $strPrint; ?>
		    </button>          
        </form>    
	</td>   	
</tr>
</table>


<?php
$page->endPage();
?>
