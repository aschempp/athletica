<?php

/********************
 *
 *	admin.php
 *	---------
 *	
 *******************/

$noMeetingCheck = true;
 
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');
include('./lib/cl_gui_select.lib.php');
include('./lib/cl_protect.lib.php');
require('./lib/cl_http_data.lib.php');

require('./lib/common.lib.php');
require('./lib/meeting.lib.php');

if(AA_connectToDB() == FALSE)	{		// invalid DB connection
	return;
}

if($_POST['arg'] == "set_password"){
	
	$p = new Protect();
	
	if(!empty($_POST['password'])){
		$p->secureMeeting($_COOKIE['meeting_id'], $_POST['password']);
	}
	
}

if($_POST['arg'] == "del_password"){
	
	$p = new Protect();
	
	$p->unsecureMeeting($_COOKIE['meeting_id']);
	
}


$ukc_meeting = AA_checkMeeting_UKC() ; 

//
//	Display administration
//

$page = new GUI_Page('admin');
$page->startPage();
$page->printPageTitle($strAdministration);

$menu = new GUI_Menulist();
$menu->addButton($cfgURLDocumentation . 'help/administration/index.html', $strHelp, '_blank');
$menu->printMenu();
?>
<p/>

<script type="text/javascript">

function checkForm(){
	var obj = document.meetingpw;
	if(obj.password.value==''){
		alert('<?php echo $strProtectPasswordEmpty ?>');
		obj.password.focus();
		return false;
	}
}

function checkForm2(){       
    
    var obj2 = document.meetingnr;
    
    
    if(obj2.meeting_nr.value==''){
        alert('<?php echo $strErrEventNr ?>');
        obj2.meeting_nr.focus();
        return false;
    }
    else {
        document.location.href='admin_results_UKC.php?ukc_meeting=<?php echo $ukc_meeting; ?>&meeting_nr='+obj2.meeting_nr.value;
        return false;
    }
    
    
   
}

function removePassword(){
	
	document.meetingpw.arg.value = "del_password";
	document.meetingpw.submit();
	
}

</script>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="475" style="vertical-align: top;">
			<table class='dialog' width="475">
				<tr>
					<th><?php echo $strConfiguration; ?></th>
				</tr>
				<tr>
					<td>
						<form action='index.php' name='lang' method='get' target='_top'>
							<input name='language' type='hidden' value='change'>
							<table class='admin'>
								<tr class='odd'>
									<td width="80"><?php echo $strLanguage; ?></td>
									<td class='forms'>
										<?php
										$dropdown = new GUI_Select('lang', 1, 'document.lang.submit()');

										foreach($cfgLanguage as $key=>$value)
										{
											$dropdown->addOption($key, $key);
											if($_COOKIE['language_trans'] == $cfgLanguage[$key]['file']) {
												$dropdown->selectOption($key);
											}
										}
										$dropdown->printList();
										?>
									</td>
									<td class="forms"><input type="button" value="<?php echo $strVersionCheck;?>" onclick="document.location.href = 'admin_version.php';"/></td>
								</tr>
							</table>
						</form>
					</td>
				</tr>
			</table><br/>
			<?php
			if(isset($_SESSION['meeting_infos']) && count($_SESSION['meeting_infos'])>0 && ($_SESSION['meeting_infos']!='meetingNotChosen' &&  $_SESSION['meeting_infos']!='noMeeting')){
				?>
				<table class='dialog' width="475">
					<tr>
						<th class='insecure'><?php echo $strProtectMeeting; ?></th>
					</tr>
					<tr>
						<td>
							<table class='admin'>
								<tr class="odd">
									<td width="70" rowspan="2" style="text-align: center;"><img src="img/password.gif" border="0" alt="<?php echo $strProtectMeeting; ?>" title="<?php echo $strProtectMeeting; ?>"/></td>
									<td><?php echo $strProtectMeetingInfo ?></td>
								</tr>
								<tr class='even'>
									<form name="meetingpw" method="post" action="admin.php" onsubmit="return checkForm();">
									<input type="hidden" name="arg" value="set_password">
									<td>
									<input type="password" name="password" size="30"/>&nbsp;&nbsp;
									<input type="submit" value="<?php echo $strSetPassword; ?>"/>
									<?php
									$p = new Protect();
									if($p->isRestricted($_COOKIE['meeting_id']))
									{
										?>
										<br/><br/><input type="button" value="<?php echo $strDeletePassword; ?>" onclick="removePassword()">
										<?php
									}
									?>
									</td>
									</form>
								</tr>
							</table>
						</td>
					</tr>
				</table><br/>
			<?php  
			}
			if(isset($_SESSION['meeting_infos']) && count($_SESSION['meeting_infos'])>0 &&  $_SESSION['meeting_infos']!='noMeeting'){
               
				?>
				<table class='dialog' width="475">			
					<tr>
						<th class='dialog'><?php echo $strDatabase; ?> - <?php echo $strBackup; ?></th>
					</tr>
					<tr>
						<td>
							<table class='admin'>
								<form action='admin_backup.php' method='get' name='db'
									enctype='multipart/form-data'>
									<input type='hidden' name='arg' value='backup'/>
								<tr>
									<td width="70" rowspan="4" style="text-align: center;">
										<img src="img/db_save.gif" border="0" alt="<?php echo $strBackupOK; ?>" title="<?php echo $strBackupOK;?>"/>
									</td>
									<td><?php echo $strBackupInfo; ?></tr>
								</tr>
								<tr>
									<td>								
									<?php
									$dropdown = new GUI_Select('xMeeting', 1, '');
									$dropdown->addOption( $strAllMeetings, "-");
									$dropdown->addOptionsFromDB("select xMeeting, Name from meeting order by DatumVon, DatumBis");
									$dropdown->selectOption("-");
									$dropdown->printList();
									?>
									</td>
								</tr>
								<tr>
									<td><input type="checkbox" value="included"  name="base"/><?php echo $strBasdataIncluded; ?></tr>
								</tr>
								<tr class="even">
									<td>
										<button name='backup' type='submit'><?php echo $strBackupOK; ?>
										</button>
									</td>
								</tr>
							</form>	
							</table>
						</td>
					</tr>
				</table><br/>
				<?php
                
			}  
			?>
			<table class='dialog' width="475">			
				<tr>
					<th class='dialog'><?php echo $strDatabase; ?> - <?php echo $strRestore; ?></th>
				</tr>
				<tr>
					<td>
						<table class='admin'>
							<form action='admin_backup.php' method='post' name='db2' enctype='multipart/form-data'>
							<tr class='odd'>
								<td width="70" rowspan="3" style="padding-left: 0px; text-align: center;"><img src="img/db_restore.gif" border="0" alt="<?php echo $strRestore; ?>" title="<?php echo $strRestore; ?>"/></td>
								<td><?php echo $strBackupRestoreInfo; ?></td>
							</tr>
							<tr class="even">
								<td>
									<input type='hidden' name='arg' value='restore'/>
										<?php echo $strBackupFile; ?>:&nbsp;
									</input>
									<input type="hidden" name="MAX_FILE_SIZE" value="619430400" />
									<input name='bkupfile' type='file' accept='text/sql' maxlength="619430400">
								</td>
							</tr>
							<tr class="even">
								<td>
									<button name='backup' onclick="return warn()" type='button'><?php echo $strRestore; ?>
									</button>
								</td>
							</tr>
						</form>	
						</table>
					</td>
				</tr>
			</table><br/>
            
           <table class='dialog' width="475">            
                <tr>
                    <th class='dialog_zlv'><?php echo $strOnlineReg; ?> </th>
                </tr>
                <tr>
                    <td>
                        <table class='admin'>
                            <form action='admin_onlineRegZLV.php' method='post' name='onlineReg' enctype='multipart/form-data'>
                            <tr class='odd'>
                                <td width="70" rowspan="3" style="padding-left: 0px; text-align: center;"><img src="img/db_restore.gif" border="0" alt="<?php echo $strRestore; ?>" title="<?php echo $strRestore; ?>"/></td>
                                <td><?php echo $strOnlineRegInfo; ?></td>
                            </tr>
                            <tr class="even">
                                <td>
                                    <input type='hidden' name='arg' value='restore'/>
                                        <?php echo $strXmlFile; ?>:&nbsp;
                                    </input>
                                    <input type="hidden" name="MAX_FILE_SIZE" value="619430400" />
                                    <input name='xmlfile' type='file' accept='*.xml' maxlength="619430400">
                                </td>
                            </tr>
                            <tr class="even">
                                <td>
                                    <button name='backup' type='submit' class="dialog_zlv"><?php echo $strXmlRestore; ?>

                                    </button>
                                </td>
                            </tr>
                        </form>    
                        </table>
                    </td>
                </tr>
            </table><br/>
            
             <table class='dialog' width="475">
                <tr>
                    <th class='dialog'><?php echo $strLinks; ?></th>
                </tr>
                <tr>
                    <td>
                        <table class='admin'>
                            <tr class='odd'>
                                <td>&bull;
                                    <a href='<?php echo $cfgURLDocumentation; ?>index.html' target='_blank'>
                                    <?php echo $strDocumentation; ?> (HTML)</a>
                                </td>
                            <tr class='even'>
                                <td>&bull;
                                    <a href='<?php echo $cfgURLDocumentation; ?>athletica.pdf' target='_blank'>
                                    <?php echo $strDocumentation; ?> (PDF)</a>
                                </td>
                            </tr>
                            <tr class='odd'>
                                <td>&bull;
                                    <a href='LICENSE.txt' target='_blank'><?php echo $strLicense; ?></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>       

			
		</td>
		<td width="10"></td>
		<td style="vertical-align: top;">
			<table class='dialog' width="475">
				<tr>
					<th class='bestlistupdate'><?php echo $strEmptyCache; ?></th>
				</tr>
				<tr>
					<td>
						<table class='admin'>
							<colgroup>
								<col width="50%"/>
								<col width="50%"/>
							</colgroup>
							<tr class='odd'>
								<td colspan="2"><?php echo $strEmptyCacheInfo; ?></td>
							</tr>
							<tr class='even'>
								<td>
									<input type="button" value="<?php echo $strEmptyCache; ?>" class="uploadbutton" onclick="javascript:document.location.href='admin_base.php?arg=empty'">
								</td>
								<td style="text-align: right;">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
			</table><br/>
            
            <table class='dialog' width="475">
                <tr>
                    <th class='bestlistupdate'><?php echo $strEmptyEntries; ?></th>
                </tr>
                <tr>
                    <td>
                        <table class='admin'>
                            <colgroup>
                                <col width="50%"/>
                                <col width="50%"/>
                            </colgroup>
                            <tr class='odd'>
                                <td colspan="2"><?php echo $strEmptyEntriesInfo; ?></td>
                            </tr>
                            <tr class='even'>
                                <td>
                                    <input type="button" value="<?php echo $strEmptyEntries; ?>" class="uploadbutton" onclick="javascript:document.location.href='admin_base.php?arg=empty_entries'">
                                </td>
                                <td style="text-align: right;">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table><br/>

			<table class='dialog' width="475">
				<tr>
					<th class='baseupdate'><?php echo $strBaseUpdate; ?></th>
				</tr>
				<tr>
					<td>
						<table class='admin'>
							<colgroup>
								<col width="50%"/>
								<col width="50%"/>
							</colgroup>
							<tr class='odd'>
								<td colspan="2"><?php echo $strBaseRemark; ?></td>
							</tr>
							<tr class='even'>
								<td>
									<input type="button" value="<?php echo $strNext; ?>" class="baseupdatebutton" onclick="javascript:document.location.href='admin_base.php'">
								</td>
								<td style="text-align: right;">
									<input type="button" value="<?php echo $strReset; ?>" class="baseresetbutton" onclick="javascript:document.location.href='admin_base.php?arg=reset'">
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
            <br/>
            <table class='dialog' width="475">
                <tr>
                    <th class='baseupdate'><?php echo $strUpdateRecords; ?></th>
                </tr>
                <tr>
                    <td>
                        <table class='admin'>
                            <tr class='odd'>
                                <td>
                                    <input type="button" value="<?php echo $strNext; ?>" class="baseupdatebutton" onclick="javascript:document.location.href='admin_records.php'">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <br/>
            <!--
            <table class='dialog' width="475">
                <tr>
                    <th class='baseupdate'><?php echo $strPalmaresUpdate; ?></th>
                </tr>
                <tr>
                    <td>
                        <table class='admin'>
                            <tr class='odd'>
                                <td><?php echo $strPalmaresRemark; ?></td>
                            </tr>
                            <tr class='odd'>
                                <td>
                                    <input type="button" value="<?php echo $strNext; ?>" class="baseupdatebutton" onclick="javascript:document.location.href='admin_palmares.php'">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            -->
			 <?php
             
                       
            
            if ($ukc_meeting == 'n'){
            
			    if(isset($_SESSION['meeting_infos']) && count($_SESSION['meeting_infos'])>0 && ($_SESSION['meeting_infos']!='meetingNotChosen' &&  $_SESSION['meeting_infos']!='noMeeting')){
				    ?>
				    <br/>
				    <table class='dialog' width="475">
					    <tr>
						    <th class='sync'><?php echo $strMeetingSync; ?></th>
					    </tr>
					    <tr>
						    <td>
							    <table class='admin'>
								    <tr class='odd'>
									    <td><?php echo $strMeetingSyncRemark; ?></td>
								    </tr>
								    <tr class='even'>
									    <td>
										    <input type="button" value="<?php echo $strNext; ?>" class="syncbutton" onclick="javascript:document.location.href='admin_registration.php'">
									    </td>
								    </tr>
							    </table>
						    </td>
					    </tr>
				    </table><br/>
				    <table class='dialog' width="475">
					    <tr>
						    <th class='bestlistupdate'><?php echo $strBestlistUpdate; ?></th>
					    </tr>
					    <tr>
						    <td>
							    <table class='admin'>
								    <tr class='odd'>
									    <td><?php echo $strBestlistRemark; ?></td>
								    </tr>
								    <tr class='even'>
									    <td><input type="button" value="<?php echo $strNext; ?>" class="uploadbutton" onclick="javascript:document.location.href='admin_results.php'"></td>
								    </tr>
							    </table>
						    </td>
					    </tr>
				    </table><br/>
				    <?php
			    }
            }                 
         
			?>   
            
               <table class='dialog' width="475">            
                <tr>
                    <th class='dialog_ukc'><?php echo $strOnlineRegUKC; ?> </th>
                </tr>
                <tr>
                    <td>
                        <table class='admin'>
                            <form action='admin_onlineRegUKC.php' method='post' name='onlineReg' enctype='multipart/form-data'>
                            <tr class='odd'>
                                <td width="70" rowspan="3" style="padding-left: 0px; text-align: center;"><img src="img/db_restore.gif" border="0" alt="<?php echo $strRestore; ?>" title="<?php echo $strRestore; ?>"/></td>
                                <td><?php echo $strOnlineRegInfo; ?><br/><?php echo $strOnlineRegInfo2; ?></td>
                            </tr>
                            <tr class="even">
                                <td>
                                    <input type='hidden' name='arg' value='restore'/>
                                        <?php echo $strXmlFile; ?>:&nbsp;
                                    </input>
                                    <input type="hidden" name="MAX_FILE_SIZE" value="619430400" />
                                    <input name='xmlfile' type='file' accept='*.xml' maxlength="619430400">
                                    <input type="hidden" name="ukc_meeting" value="<?php echo $ukc_meeting; ?>" />  
                                </td>
                            </tr>
                            <tr class="even">
                                <td>
                                    <button name='backup' type='submit' class="dialog_ukc"><?php echo $strXmlRestore; ?>

                                    </button>
                                </td>
                            </tr>
                        </form>    
                        </table>
                    </td>
                </tr>
            </table>
               
                <br/>
           <table class='dialog' width="475">
                    <tr>
                        <th class='dialog_ukc'><?php echo $strUploadUKC; ?></th>
                    </tr>
                    <tr>
                        <td>
                            <table class='admin'>
                            <?php
                             if ($ukc_meeting == 'n'){  
                                 ?>
                                 <form name="meetingnr" method="post" action="admin.php" onsubmit="return checkForm2();">
                                <tr class='odd'>
                                    <td><?php echo $strBestlistRemark; ?></td>
                                </tr>
                                 <tr class='even'>
                                    <td> 
                                    <?php                                   
                                        echo $strMeetingNbr; ?> 
                                        <input type="text" name="meeting_nr" size="10" />
                                        <?php
                                       
                                        ?>
                                        <input type="submit" value="<?php echo $strNext; ?>"/>   
                                        
                                </tr>
                                </form>
                             
                                <?php
                             }
                             else {
                                 ?>
                            
                                <tr class='odd'>
                                    <td><?php echo $strBestlistRemark; ?></td>
                                </tr>
                                 <tr class='even'>    
                                    <td><input type="button" value="<?php echo $strNext; ?>" class="dialog_ukc" onclick="javascript:document.location.href='admin_results_UKC.php?ukc_meeting=<?php echo $ukc_meeting; ?>'"></td>                                
                                </tr>   
                                <?php
                                }
                                ?>
                            </table>
                        </td>
                    </tr>
                </table>
                  <br/>  
              <?php
                   
           if ($ukc_meeting == 'y'){    
               
               ?>  
             <table class='dialog' width="475">
                <tr>
                    <th class='dialog'><?php echo $strLinks; ?></th>
                </tr>
                <tr>
                    <td>
                        <table class='admin'>
                            <tr class='odd'>
                                <td>&bull;
                                    <a href='<?php echo $cfgURLDocumentation; ?>index.html' target='_blank'>
                                    <?php echo $strDocumentation; ?> (HTML)</a>
                                </td>
                            <tr class='even'>
                                <td>&bull;
                                    <a href='<?php echo $cfgURLDocumentation; ?>athletica.pdf' target='_blank'>
                                    <?php echo $strDocumentation; ?> (PDF)</a>
                                </td>
                            </tr>
                            <tr class='odd'>
                                <td>&bull;
                                    <a href='LICENSE.txt' target='_blank'><?php echo $strLicense; ?></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>       
           
          
              <?php
           }
            ?>
          
            
		</td>
	</tr>
</table>

<?php
$page->endPage();
?>

 <script type="text/javascript">
<!--
  function warn() {  
     check = confirm("<?php echo $strWarnRestore; ?>");
     if (check == false){
         return false;
     }
     else {
         document.db2.submit();        
         return true;  
     }
  }
</script>