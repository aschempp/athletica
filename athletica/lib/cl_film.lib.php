<?php

if (!defined('AA_CL_FILM_LIB_INCLUDED'))
{
	define('AA_CL_FILM_LIB_INCLUDED', 1);

/* Class Constants */

/********************************************
 *
 * CLASS Film
 *
 * Provides functionality to update a film description
 * Usage: After object creation, the user may call save function.
 *
 * Return:	-
 *
 *******************************************/

class Film
{
	var $heatID;
	var $film;

	function Film($heatID=0)
	{
		$this->heatID = $heatID;
		$this->film = '';
	}
	

	function save($film = '')
	{
		global $strErrFilmExists;
		$GLOBALS['AA_ERROR'] = '';

		$this->film = $film;

		/*mysql_query("
			LOCK TABLES
				serie WRITE
		");

		mysql_query("
			UPDATE serie SET
				Film = '" . $this->film . "'
			WHERE xSerie = " . $this->heatID
		);*/
		
		mysql_query("LOCK TABLES runde READ, wettkampf READ, meeting READ, serie WRITE");
		
		// check if filmnummer already exists in context of current meeting
		$res = mysql_query("
				SELECT * FROM 
					serie
					LEFT JOIN runde  USING(xRunde) 
					LEFT JOIN wettkampf USING(xWettkampf) 
					LEFT JOIN meeting USING(xMeeting)
				WHERE meeting.xMeeting = ".$_COOKIE['meeting_id']."
				AND serie.Film = ".$_POST['film']);
		if(mysql_errno() > 0) {
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}else{
			if(mysql_num_rows($res) == 0){
				// no results --> update film nummer
				mysql_query("update 
						serie
					set Film = ".$_POST['film']."
					where xSerie = ".$_POST['item']);
				
				if(mysql_errno() > 0) {
					$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
				}
			}else{
				// film already exists
				$GLOBALS['AA_ERROR'] = $strErrFilmExists;
			}
		}
		
		//mysql_query("UNLOCK TABLES");

		if(mysql_affected_rows() == 0) {
			$GLOBALS['AA_ERROR'] = $GLOBALS['strFilm'] . $GLOBALS['strErrNotValid'];
		}
		if(mysql_errno() > 0) {
			$GLOBALS['AA_ERROR'] = mysql_errno() . ": " . mysql_error();
		}

		mysql_query("UNLOCK TABLES");
	}
} // end class Film


} // end AA_CL_FILM_LIB_INCLUDED

?>
