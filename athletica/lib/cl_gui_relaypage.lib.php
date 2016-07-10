<?php

if (!defined('AA_CL_GUI_RELAYPAGE_LIB_INCLUDED'))
{
	define('AA_CL_GUI_RELAYPAGE_LIB_INCLUDED', 1);


 	include('./lib/cl_gui_page.lib.php');


/********************************************
 *
 * GUI_RelayPage
 *
 *	Class to print relay lists
 *
 *******************************************/

class GUI_RelayPage extends GUI_ListPage
{
	function printHeaderLine()
	{
?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strCategoryShort']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strDiscipline']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
<?php
	}


	function printLine($name, $cat, $club, $disc, $perf, $nbr)
	{
		$this->switchRowClass();
?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td><?php echo $cat; ?></td>
		<td><?php echo $club; ?></td>
		<td><?php echo $disc; ?></td>
		<td class='forms_right'><?php echo $perf; ?></td>
	</tr>
<?php
	}


	function printAthletes($athletes, $teamsm = false)
	{
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
    <?php
        if ($teamsm) {
            ?>
                <td></td>
                <td class='relay_athletes' colspan='6'><?php echo $athletes; ?></td>
         <?php
        }
        else {
              ?>                   
                <td class='relay_athletes' colspan='5'><?php echo $athletes; ?></td>
         <?php
        }
       ?> 
	</tr>
		<?php
	}



	function printSubTitle($title)
	{
		?>
		<h2><?php echo $title; ?></h2>
		<?php
	}

} // end GUI_RelayPage


/********************************************
 *
 * GUI_CatRelayPage
 *
 *	Class to print relay lists per categroy
 *
 *******************************************/

class GUI_CatRelayPage extends GUI_RelayPage
{
	function printHeaderLine()
	{
		?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strDiscipline']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
		<?php
	}


	function printLine($name, $club, $disc, $perf, $nbr)
	{
		$this->switchRowClass();
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td><?php echo $club; ?></td>
		<td><?php echo $disc; ?></td>
		<td><?php echo $perf; ?></td>
	</tr>
		<?php
	}

} // end GUI_CatRelayPage



/********************************************
 *
 * GUI_ClubRelayPage
 *
 *	Class to print relay lists per club
 *
 *******************************************/

class GUI_ClubRelayPage extends GUI_RelayPage
{
	function printHeaderLine()
	{
		?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strCategoryShort']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strDiscipline']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
		<?php
	}


	function printLine($name, $cat, $disc, $perf, $nbr)
	{
		$this->switchRowClass();
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td><?php echo $cat; ?></td>
		<td><?php echo $disc; ?></td>
		<td class='forms_right'><?php echo $perf; ?></td>
	</tr>
		<?php
	}

} // end GUI_ClubRelayPage


/********************************************
 *
 * GUI_CatDiscRelayPage
 *
 *	Class to print relay lists per categroy and discipline
 *
 *******************************************/

class GUI_CatDiscRelayPage extends GUI_RelayPage
{
	function printHeaderLine()
	{
		?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strClub']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
		<?php
	}


	function printLine($name, $club, $perf, $nbr)
	{
		$this->switchRowClass();
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td><?php echo $club; ?></td>
		<td class='forms_right'><?php echo $perf; ?></td>
	</tr>
		<?php
	}

} // end GUI_CatDiscRelayPage



/********************************************
 *
 * GUI_ClubCatRelayPage
 *
 *	Class to print relay lists per categroy and discipline
 *
 *******************************************/

class GUI_ClubCatRelayPage extends GUI_RelayPage
{
	function printHeaderLine()
	{
		?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strDiscipline']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
		<?php
	}


	function printLine($name, $disc, $perf, $nbr)
	{
		$this->switchRowClass();
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td><?php echo $disc; ?></td>
		<td class='forms_right'><?php echo $perf; ?></td>
	</tr>
		<?php
	}

} // end GUI_ClubCatRelayPage



/********************************************
 *
 * GUI_ClubCatDiscRelayPage
 *
 *	Class to print relay lists per club, categroy and discipline
 *
 *******************************************/

class GUI_ClubCatDiscRelayPage extends GUI_RelayPage
{
	function printHeaderLine()
	{
		?>
	<tr>
		<th class='dialog'><?php echo $GLOBALS['strStartnumber']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strRelay']; ?></th>
		<th class='dialog'><?php echo $GLOBALS['strTopPerformance']; ?></th>
	</tr>
		<?php
	}


	function printLine($name, $perf, $nbr)
	{
		$this->switchRowClass();
		?>
	<tr class='<?php echo $this->rowclass[0]; ?>'>
		<td><?php echo $nbr; ?></td>
		<td><?php echo $name; ?></td>
		<td class='forms_right'><?php echo $perf; ?></td>
	</tr>
		<?php
	}

} // end GUI_ClubCatDiscRelayPage

} // end AA_CL_GUI_RELAYPAGE_LIB_INCLUDED
?>
