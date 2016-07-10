<?php

/**********
 *
 *	meeting_entries_print.php
 *	-------------------------
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

$page = new GUI_Page('meeting_entries_print');
$page->startPage();
$page->printPageTitle($strPrint);

?>
<script type="text/javascript">
<!--
	function setPrint()
	{
		document.printdialog.formaction.value = 'print'
		document.printdialog.target = '_blank';
	}

	function setView()
	{
		document.printdialog.formaction.value = 'view'
		document.printdialog.target = '';
	}
	
	function setExport()
	{
		document.printdialog.formaction.value = 'export'
		document.printdialog.target = '';
	}
//-->
</script>

<form action='print_meeting_entries.php' method='get' name='printdialog'>
<input type='hidden' name='formaction' value=''>

<table class='dialog'>
<tr>
	<th class='dialog'><?php echo $strGroupBy; ?></th>
	<th class='dialog' colspan='2'><?php echo $strPageBreak; ?></th>
</tr>
<tr>
	<td class='dialog'>
		<input type='checkbox' name='clubgroup' value='yes'>
			<?php echo $strClubs; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='clubbreak' value='yes'>
			<?php echo $strYes; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='clubbreak' value='no' checked>
			<?php echo $strNo; ?></input>
	</td>
</tr>
<tr>
	<td class='dialog'>
		<input type='checkbox' name='catgroup' value='yes'>
			<?php echo $strCategories; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='catbreak' value='yes'>
			<?php echo $strYes; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='catbreak' value='no' checked>
			<?php echo $strNo; ?></input>
	</td>
</tr>
<tr>
	<td class='dialog'>
		<input type='checkbox' name='contestcatgroup' value='yes'>
			<?php echo $strContestCategory; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='contestcatbreak' value='yes'>
			<?php echo $strYes; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='contestcatbreak' value='no' checked>
			<?php echo $strNo; ?></input>
	</td>
</tr>
<tr>
	<td class='dialog'>
		<input type='checkbox' name='discgroup' value='yes'>
			<?php echo $strDisciplines; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='discbreak' value='yes'>
			<?php echo $strYes; ?></input>
	</td>
	<td class='dialog'>
		<input type='radio' name='discbreak' value='no' checked>
			<?php echo $strNo; ?></input>
	</td>
</tr>

<tr>
	<th class='dialog' colspan='3'><?php echo $strSortBy; ?></th>
</tr>
<tr>
	<td class='dialog' colspan='3'>
		<input type='radio' name='sort' value='name' checked>
			<?php echo $strName; ?></input>
	</td>
</tr>
<tr>
	<td class='dialog' colspan='3'>
		<input type='radio' name='sort' value='nbr'>
			<?php echo $strStartnumbers; ?></input>
	</td>
</tr>

<tr>
    <td class='dialog' colspan='3'>
        <input type='radio' name='sort' value='bestperf'>
            <?php echo $strEfforts; ?></input>
    </td>
</tr>

<tr>
	<th class='dialog' colspan='3'><?php echo $strLimitSelection; ?></th>
</tr>
<tr>
	<td colspan='3'>
		<table>
			<tr>
				<td class='dialog'><?php echo $strCategory; ?>:</td>
				<?php
				$dd = new GUI_CategoryDropDown(0,'',false,true);
				?>
			</tr>
			<tr>
				<td class='dialog'><?php echo $strContestCategory; ?>:</td>
				<?php
				$dd = new GUI_CategoryDropDown(0);
				?>
			</tr>
			<tr>
				<td class='dialog'><?php echo $strDiscipline; ?>:</td>
				<?php
				$dd = new GUI_DisciplineDropDown();
				?>
			</tr>
            <tr>
                <td class='dialog'><?php echo $strClub; ?></td>
                <?php
               $dd = new GUI_ClubDropDown(0);
                
                ?>
            </tr>
            <tr>
	                <?php  	                
	              
	                $sql = "SELECT 
	                            DISTINCT(Lizenztyp)  
	                        FROM                
	                            athlet as at
	                            LEFT JOIN anmeldung AS a ON (a.xAthlet = at.xAthlet)                             
	                        WHERE xMeeting = ".$_COOKIE['meeting_id'];      
	                        
	                $query = mysql_query($sql);    
	               
	                    ?>   
	                    <td class='dialog'>
	                    <?php echo $strLicenseType; ?></input>
	                    </td>    
	                    <td class='forms'>   
	                         <select name='licenseType'> 
	                          <option value="-">-</option>   
	                    <?php  
	                       while($row = mysql_fetch_array($query)){    
	                             foreach ($cfgLicenseType as $key => $value) {
	                                  if ($value==$row[0]){   
	                                      ?>
	                            		  <option value="<?php echo $row[0]?>"><?php echo $key;  ?></option>   
	                    				  <?php
	                                   }
	                             } 
	                       }
	                    ?>
	                    		<option value="4"><?php echo $strLicenseTypeNormalNotPayed;?></option
                                </select>      
	                    </td>
	                 </tr>
                     
                    <tr>
                    <?php                      
                  
                                       
                        ?>   
                        <td class='dialog'>
                        <?php echo $strPaymentStatus; ?></input>
                        </td>    
                        <td class='forms'>   
                             <select name='paymentStatus'> 
                              <option value="-">-</option>                                
                                <option value="1"><?php echo $strPayedAll;?></option>
                                <option value="2"><?php echo $strPayedPart;?></option>
                                <option value="3"><?php echo $strPayedNo;?></option>
                                
                                
                                </select>      
                        </td>
                     </tr>  
                     

            <tr>
                <?php 
                
                $tage = 1;
                $sql = "SELECT 
                            DISTINCT(Datum) AS Datum 
                        FROM 
                            runde 
                        LEFT JOIN wettkampf USING(xWettkampf) 
                        WHERE xMeeting = ".$_COOKIE['meeting_id']." 
                        ORDER BY Datum ASC;";
                        
                $query = mysql_query($sql);

                $tage = mysql_num_rows($query);
                if($tage>1){
                    ?> 
                 
                    <td class='dialog'>
                    <?php echo $strDay; ?></input>
                    </td>
                 
                    <td class='forms'>
                        <select name='date'>
                        <option value="%">- <?php echo $strAll?> -</option>
                    <?php
                        while($row = mysql_fetch_assoc($query)){
                    ?>
                            <option value="<?php echo $row['Datum']; ?>"><?php echo date('d.m.Y', strtotime($row['Datum']))?></option>
                    <?php
                       }
                    ?>
                            </select>
                    </td>
                 </tr>
                    
                <?php
                }   
                ?> 
                   
        
		</table>
	</td>
</tr>

<tr>
	<td class='dialog' colspan='2'>
		<input type='checkbox' name='cover' value='cover'>
			<?php echo $strCover; ?></input>
	</td>
</tr>

<tr>
	<td class='dialog' colspan='2'>
		<input type='checkbox' name='payment' value='payment'>
			<?php echo $strPaymentStatus; ?></input>
	</td>
</tr>

</table>

<p />

<table>
<tr>
	<td>
		<button name='print' type='submit' onClick='setPrint()'>
			<?php echo $strPrint; ?>
		</button>
	</td>
	<td>
		<button name='view' type='submit' onClick='setView()'>
			<?php echo $strShow; ?>
		</button>
	</td>
</tr>
</table>

<br>

<table class="dialog">
<tr>
	<th class="dialog"><?php echo $strExport ?></th>
</tr>
<tr>
	<td class="forms">
		<input type="radio" name="limitNr" value="yes" id="limitnr">
		<?php echo $strExportNbrs ?> <input type="text" size="2" name="limitNrFrom" onfocus="o = document.getElementById('limitnr'); o.checked='checked'">
		<?php echo strtolower($strTo) ?> <input type="text" size="2" name="limitNrTo" onfocus="o = document.getElementById('limitnr'); o.checked='checked'">
	</td>
</tr>
<tr>
	<td class="forms">
		<input type="radio" name="limitNr" value="no" checked><?php echo $strExportAllNbrs ?>
	</td>
</tr>
<tr>
	<td class="forms" align="right">
		<button name='export' type='submit' onClick='setExport()'>
			<?php echo $strExportNumbers; ?>
		</button>
	</td>
</tr>
</table>

</form>

<?php
$page->endPage();
?>
