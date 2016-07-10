<?php
setlocale(LC_ALL, 'de_DE');
/**
 * C O N F I G U R A T I O N    P A R A M E T E R S 
 * ------------------------------------------------
 */

/**
 * MySQL-Database
 */
$cfgDBhost = '127.0.0.1'; // MySQL hostname
$cfgDBport = '';          // MySQL port - leave blank for default port
$cfgDBname = 'athletica';   // database
$cfgDBuser = 'athletica';   // user
$cfgDBpass = 'athletica';   // password
$cfgDBdateFormat = '%d.%m.%y';   // date format string
$cfgDBtimeFormat = '%H:%i';   // time format string
$cfgDBerrorDuplicate = '1062';   // error code, unique key constraints


/**
 * Start number distribution
 */
$cfgNbrStartWith	 = '1';		// default nbr to start with
$cfgNbrCategoryGap = '5'; 	// planned nbr gap between categories
$cfgNbrClubGap		 = '5'; 	// planned nbr gap between categories


/**
 * Result presentation
 */
$cfgResultsSeparator = ",";		// token separating performance values
$cfgResultsHourSeparator = ":";	// token separating hours
$cfgResultsMinSeparator = ":";	// token separating minutes
$cfgResultsSecSeparator = ".";	// token separating seconds
$cfgResultsMeterSeparator = ".";	// token separating meters
$cfgResultsWindSeparator = ".";	// token separating wind
$cfgResultsInfoFill = "-";			// token to fill unused info fields
$cfgResultsPointsPrecision = '1';	// precision when calc. team event points
$cfgResultsHigh = array(			// valid high-results
							// 1st attempt
							  "O"	// OK
							, "-"	// nicht geschafft
							, "X"	// nicht geschafft
							// 2nd attempt
							, "--"
							, "-O"
							, "-X"
							, "X-"
							, "XO"
							, "XX"
							// 3rd attempt
							, "---"
							, "--O"
							, "--X"
							, "-X-"
							, "-XO"
							, "-XX"
							, "X--"
							, "X-O"
							, "X-X"
							, "XX-"
							, "XXO"
							, "XXX"
						);
$cfgResultsHighStay = array(		// stay on same height
								//  "-"
								 "X"
								, "--"
								, "-X"
								, "X-"
								, "XX"                                  
							);
$cfgResultsHighStayDecentral = array(        // stay on same height
                                 "-"
                                , "X"
                                , "--"
                                , "-X"
                                , "X-"
                                , "XX" 
                                , "XXX"                                 
                            );
$cfgResultsHighStayDecentralNotPassed = array(        // stay on same height                                     
                                 "X" 
                                , "XX" 
                               
                            );
$cfgResultsHighStayDecentralEnd = array(        // to skip and finished                                   
                                 "-" 
                                 ,"X-"
                                 ,"XX-" 
                                , "O" 
                                , "XO"  
                                , "XXO"  
                                , "XXX"                                                                                                                              
                            );
                            
$cfgResultsHighStayDecentralEnd2 = array(        // to skip
                                 "-"                                      
                                , "O" 
                                , "XO"  
                                , "XXO"  
                                
                            );
                            
$cfgCountHeight = 15;
$cfgHeightDiffJump = 0.05;
$cfgHeightStartJump = 1.50;
$cfgHeightDiffPole = 0.20;
$cfgHeightStartPole = 3.50;

$cfgResultsHighOut = "XXX";		// last high result
$cfgResultsHighOut1 = "XX";  
$cfgResultsHighOut2 = "XX-";              
$cfgResultsHighOut3 = "X--";            
$cfgResultsHighOut4 = "X-";  
$cfgResultsHighOut5 = "X";                      
$cfgResultsHighOut6 = "-";       
$cfgResultsHighOut7 = "--";  
$cfgResultsHighOut8 = "---";           
// separator transformation mask	(valid separators in user entries)
$cfgResultsSepTrans = array("."=>"$cfgResultsSeparator"
								,	":"=>"$cfgResultsSeparator"
								, ";"=>"$cfgResultsSeparator");
                                
$cfgMaxAthlete = 8;        // decentral ranking 8 athletes
$cfgAfterAttempts1 = 3;    // after 3 attempts --> new position for 8 athletes
$cfgAfterAttempts2 = 5;    // after 5 attempts --> new position for 8 athletes
$cfgEightRank = 8;

/**
 * Ranking lists
 */
$cfgRankingOrganizer = "Organisation";	// Organizing body
$cfgRankingTiming = "OMEGA";	// Style sheet


/**
 *	Track distribution
 *		order of best tracks for different nbr of track
 */
$cfgTrackOrder = array(		3=>array (1=>3
                                         , 2=>1
                                         , 3=>2  
                                         )
                    		,	4=>array (1=>3
										 , 2=>2
										 , 3=>4
										 , 4=>1
										 )
							, 5=>array (1=>3
										 , 2=>4
										 , 3=>2
										 , 4=>5
										 , 5=>1
										 )
							, 6=>array (1=>4
										 , 2=>3
										 , 3=>5
										 , 4=>2
										 , 5=>6
										 , 6=>1
										 )
							, 7=>array (1=>4
										 , 2=>5
										 , 3=>3
										 , 4=>6
										 , 5=>2
										 , 6=>7
										 , 7=>1
										 )
							, 8=>array (1=>5
										 , 2=>4
										 , 3=>6
										 , 4=>3
										 , 5=>7
										 , 6=>2
										 , 7=>8
										 , 8=>1
										 )
							, 9=>array (1=>5
										 , 2=>6
										 , 3=>4
										 , 4=>7
										 , 5=>3
										 , 6=>8
										 , 7=>2
										 , 8=>9
										 , 9=>1
										 )
							, 10=>array (1=>6
										 , 2=>5
										 , 3=>7
										 , 4=>4
										 , 5=>8
										 , 6=>3
										 , 7=>9
										 , 8=>2
										 , 9=>10
										 , 10=>1
										 )
								);
								
/**
 *	Tracktype
 */
//$cfgTrackType = array($strStraight=>'g', $strRoundly=>'r');

/**
 *	Various other options
 */     
                                 
//$cfgPrtLinesPerPage = 57;		// printer dependent  
//$cfgPageContentHeight = 285;    // content layer height in mm, will position header an footer on printings
   
 if (preg_match('/firefox/i', $_SERVER['HTTP_USER_AGENT'])) {    
     $cfgPageContentHeight = 275;    // content layer height in mm, will position header an footer on printings 
     $cfgPrtLinesPerPage = 57;        // printer dependent
}
else 
   if (preg_match('/msie 6/i', $_SERVER['HTTP_USER_AGENT'])) {   
         $cfgPageContentHeight = 269;    // content layer height in mm, will position header an footer on printings 
         $cfgPrtLinesPerPage = 57;        // printer dependent  
}
else {    
      $cfgPageContentHeight = 275;    // content layer height in mm, will position header an footer on printings         
      $cfgPrtLinesPerPage = 60;        // printer dependent  
          

}                                      
$cfgCookieExpires = 31536000;	// Secs, after which cookies will expire
$cfgMonitorReload = 60;			// Secs, after which event and speaker monitor
										// page will be reloaded

?>
