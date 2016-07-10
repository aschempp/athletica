<?php

if (!defined('AA_CL_GUI_ENTRYPAGE_LIB_INCLUDED'))
{
	define('AA_CL_GUI_ENTRYPAGE_LIB_INCLUDED', 1);


 	include('./lib/cl_gui_page.lib.php');


/********************************************
 *
 * GUI_EntryPage
 *
 *	Class to print basic entry lists
 *
 *******************************************/


class GUI_EntryPage extends GUI_ListPage
{
	function printHeaderLine()
	{
?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strCategoryShort']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strDisciplines']; ?></th>
       <!-- <th class='dialog'><?php echo $GLOBALS['strFee']; ?></th>  -->
		<?php
		if(isset($_GET['payment']) && isset($_GET['discgroup'])){
			?>
		<th class='dialog'>&nbsp;</th>
			<?php
		}
       
      
       ?>  
         
	</tr>
<?php
	}


	function printLine($nbr, $name, $year, $cat, $club, $disc, $ioc, $paid = '', $perf,$mkcode)
	{  
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='forms_right'><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td class='forms_ctr'><?php echo $year; ?></td>
		<td><?php echo $cat; ?></td>
		<td><?php echo $club; ?></td>
		
    <?php    
        if ($mkcode > 0 && $perf != 0){
           
            ?>
            <td><?php echo $disc . " (" .$perf .")"; ?></td>  
           <?php 
        }
        else {
            ?>
            <td><?php echo $disc; ?></td>
           <?php 
            
        }
        ?>
        
     <!--   <td class='forms_right'><?php echo $fee; ?></td>   -->
		<?php 
		if(isset($_GET['payment']) && isset($_GET['discgroup'])){
			?>
		<td><?php echo $paid ?></td>
			<?php
		}
        ?>
	</tr>
<?php
		$this->switchRowClass();
	}


	function printSubTitle($title)
	{   
		?>
<h2><?php echo $title; ?></h2> 
		<?php
	}

} // end GUI_EntryPage


/********************************************
 *
 * GUI_CatEntryPage
 *
 *	Class to print entry lists per category
 *
 *******************************************/


class GUI_CatEntryPage extends GUI_EntryPage
{
	function printHeaderLine()
	{
?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strDisciplines']; ?></th>
	</tr>
<?php
	}


	function printLine($nbr, $name, $year, $club, $disc)
	{ 
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='forms_right'><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td class='forms_ctr'><?php echo $year; ?></td>
		<td><?php echo $club; ?></td>
		<td><?php echo $disc; ?></td>
	</tr>
<?php
		$this->switchRowClass();
	}

} // end GUI_CatEntryPage



/********************************************
 *
 * GUI_ClubEntryPage
 *
 *	Class to print entry lists per club
 *
 *******************************************/


class GUI_ClubEntryPage extends GUI_EntryPage
{
	function printHeaderLine()
	{
        
?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
        <th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
        <th class='dialog'><?php echo $GLOBALS['strYearShort']; ?></th>
        <th class='dialog'><?php echo $GLOBALS['strCategoryShort']; ?></th>
        <th class='dialog'><?php echo $GLOBALS['strDisciplines']; ?></th>
	</tr>
<?php
	}


	function printLine($nbr, $name, $year, $cat, $disc)
	{  
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='forms_right'><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td class='forms_ctr'><?php echo $year; ?></td>
		<td><?php echo $cat; ?></td>
		<td><?php echo $disc; ?></td>
       
	</tr>
<?php
		$this->switchRowClass();
	}

} // end GUI_ClubEntryPage


/********************************************
 *
 * GUI_CatDiscEntryPage
 *
 *	Class to print entry lists per category and discipline
 *
 *******************************************/


class GUI_CatDiscEntryPage extends GUI_EntryPage
{
	function printHeaderLine()
	{
?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php
	}


	function printLine($nbr, $name, $year, $club, $perf)
	{ 
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='forms_right'><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td class='forms_ctr'><?php echo $year; ?></td>
		<td><?php echo $club; ?></td>
		<td class='forms_right'><?php echo $perf; ?></td>
        
	</tr>
<?php
		$this->switchRowClass();
	}

} // end GUI_CatDiscEntryPage


/********************************************
 *
 * GUI_ClubCatEntryPage
 *
 *	Class to print entry lists per club and category
 *
 *******************************************/


class GUI_ClubCatEntryPage extends GUI_EntryPage
{
	function printHeaderLine()
	{
?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strDisciplines']; ?></th>
	</tr>
<?php
	}


	function printLine($nbr, $name, $year, $disc)
	{  
?>                     
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='forms_right'><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td class='forms_ctr'><?php echo $year; ?></td>
		<td><?php echo $disc; ?></td>
	</tr>
<?php
		$this->switchRowClass();
	}

} // end GUI_ClubCatEntryPage



/********************************************
 *
 * GUI_ClubCatDiscEntryPage
 *
 *	Class to print entry lists per club, category and discipline
 *
 *******************************************/


class GUI_ClubCatDiscEntryPage extends GUI_EntryPage
{
	function printHeaderLine()
	{
?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strYearShort']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php
	}


	function printLine($nbr, $name, $year, $perf)
	{ 
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td class='forms_right'><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td class='forms_ctr'><?php echo $year; ?></td>
		<td class='forms_ctr'><?php echo $perf; ?></td>
	</tr>
<?php
		$this->switchRowClass();
	}

} // end GUI_GUI_ClubCatDiscEntryPage


/********************************************
 *
 * GUI_ClubEntryPayedPage
 *
 *    Class to print entry lists per club
 *
 *******************************************/


class GUI_ClubEntryPayedPage extends GUI_EntryPage
{
    
    function printSubTitle($title, $ckb_club)
    {   
      
        ?>
<table><tr><td><h2><?php  echo  $title; ?> </h2> </td><td><?php echo   $ckb_club .  "( ". $GLOBALS['strPayedClub'] .")" ; ?></td></tr></table> 
        <?php
    }
    
    
    function printHeaderLine($max_count = 2)
    {
        $max_count = $max_count * 2;
?>
    <tr>
        <th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
        <th class='dialog'><?php echo $GLOBALS['strName']; ?></th>
        <th class='dialog'><?php echo $GLOBALS['strYearShort']; ?></th>
        <th class='dialog'><?php echo $GLOBALS['strCategoryShort']; ?></th>        
        <th class='dialog' colspan="<?php echo $max_count; ?>"><?php echo $GLOBALS['strPayedShort'] . " / " . $GLOBALS['strDisciplines']; ?></th>
    </tr>
<?php
    }


    function printLine($nbr, $name, $year, $cat, $disc)
    {  
       
?>
    <tr class='<?php echo $this->rowclass[0]; ?>'>
        <td class='forms_right'><?php echo $nbr; ?></td>
        <td><?php echo $name; ?></td>
        <td class='forms_ctr'><?php echo $year; ?></td>
        <td><?php echo $cat; ?></td>
        <?php echo $disc; ?>
       
    </tr>
<?php
        $this->switchRowClass();
    }

} // end GUI_ClubEntryPage


} // end AA_CL_GUI_ENTRYPAGE_LIB_INCLUDED
?>
