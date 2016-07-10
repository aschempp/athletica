<?php

if (!defined('AA_CL_GUI_FAQ_LIB_INCLUDED'))
{
	define('AA_CL_GUI_FAQ_LIB_INCLUDED', 1);


/********************************************
 *
 * CLASS GUI_Faq
 *
 * Prints on each page the FAQs
 *
 *******************************************/

class GUI_Faq{
	
	function showFaq($title){
		
		// get faq from database an print
		$sql = "SELECT * 
				  FROM faq 
				 WHERE Zeigen = 'y' 
				   AND (Seite LIKE '".$title."' 
					OR Seite LIKE '".$title.",%' 
					OR Seite LIKE '%,".$title."' 
					OR Seite LIKE '%,".$title.",%') 
				   AND Sprache = '".$_COOKIE['language']."';";
		$res = mysql_query($sql);
		if(mysql_errno() > 0){
			
		}else{
			while($row = mysql_fetch_assoc($res)){
				$this->generateFaq($row);
				
			}
		}
		
	}
	
	function generateFaq($faq){
		global $strDontShowAgain;
		
		$height = ($faq['height']>0) ? ' style="height: '.$faq['height'].'px;"' : '';
		$height2= ($faq['height']>0) ? ' height: '.($faq['height']-5).'px;' : '';
		$width = ($faq['width']>0) ? $faq['width'] : 400;
		$width2 = $width-18;
		
		$farbetitel = $faq['FarbeTitel'];
		$farbehg = $faq['FarbeHG'];
		?>
		<div style="position:absolute; top:<?php echo $faq['PosTop'] ?>px; left:<?php echo $faq['PosLeft'] ?>px; width: <?php echo $width; ?>px; z-index: 1000;" id="faqdiv<?php echo $faq['xFaq'] ?>">
			<div class="faq" style="background-color: #<?php echo $farbehg; ?>;">
				<table>
					<tr><th class="faq" style="background-color: #<?php echo $farbetitel?>;"><?php echo $faq['Frage'] ?></th></tr>
					<tr><td><?php echo $faq['Antwort'] ?></td></tr>
					<tr><td height="5px"></td></tr>
					<tr><td><?php echo $strDontShowAgain; ?> <input style="padding:0px; margin:0px;" type="checkbox" name="faq" id="faq<?php echo $faq['xFaq'] ?>" value="" checked></td></tr>
				</table>
			</div>
			<div style="position:absolute; top:2px; left:<?php echo $width2;?>px;">
				<a href='javascript:closeFaq(<?php echo $faq['xFaq'] ?>)'><img src='img/closebutton.png' alt='closebutton' title='close'></a>
			</div>
		</div>
		<?php
	}
	
	function deactivateFaq($id){
		
		mysql_query("UPDATE faq SET Zeigen = 'n' WHERE xFaq = $id");
		if(mysql_errno() > 0){
			echo mysql_error();
		}
	}
	
	function activateFaq($id){
		
		mysql_query("UPDATE faq SET Zeigen = 'y' WHERE xFaq = $id");
		if(mysql_errno() > 0){
			echo mysql_error();
		}
	}
	
	function deactivateAll(){
		
		mysql_query("UPDATE faq SET Zeigen = 'n'");
		if(mysql_errno() > 0){
			echo mysql_error();
		}
		
	}
	
	function activateAll(){
		
		mysql_query("UPDATE faq SET Zeigen = 'y'");
		if(mysql_errno() > 0){
			echo mysql_error();
		}
		
	}
}


}
?>
