<?php
$cfgPhpInclude = "<?php	\$round =  \$_GET['round'];
				   if(\$round) {
				   include('live'.\$round.'.php');
				   }
				?>";			
                                                   

/**
 * Alphabeth 
*/
$cfgAlphabeth = array ("A","B","C","D","E","F","G","H","I","J","K","L","M",
                       "N","O","P","Q","R","S","T","U","V","W","X","Y","Z");


/**
 * Include language parameters
 */
include("./lang/german.inc.php"); // if an other language is set, no text will be missing (even if its in german)
if(!empty($_COOKIE['language_trans'])) {
	include ($_COOKIE['language_trans']);
}



/**
 * include user parameters
 */
require ('./parameters.inc.php');


/**
 *	Discipline type
 * 		Discipline types for reports and forms.
 */
$cfgDisciplineType = array($strDiscTypeNone=>0
								, $strDiscTypeTrack=>1
								, $strDiscTypeTrackNoWind=>2
								, $strDiscTypeRelay=>3
								, $strDiscTypeJump=>4
								, $strDiscTypeJumpNoWind=>5
								, $strDiscTypeHigh=>6
								, $strDiscTypeDistance=>7
								, $strDiscTypeThrow=>8
								, $strDiscCombined=>9);
								
/**
*
*	Number of attempts to be printed for default
*
**/
$cfgCountAttempts = array(
			$cfgDisciplineType[$strDiscTypeJump]=>3
			, $cfgDisciplineType[$strDiscTypeJumpNoWind]=>3
			, $cfgDisciplineType[$strDiscTypeThrow]=>6);

/**
 * Evaluation type
 *		Result evaluation strategies.
 */
$cfgEvalType = array($strEvalTypeHeat=>0
							, $strEvalTypeAll=>1
							, $strEvalTypeDiscDefault=>2);


/**
 *	Event type
 */
$cfgEventType = array(		$strEventTypeSingle=>0
							, $strEventTypeSingleCombined=>1
							, $strEventTypeTeamSM=>30
							, $strEventTypeSVMNL=>12
							/*, $strEventTypeClubMA=>2                    // old svm 
							, $strEventTypeClubMB=>3
							, $strEventTypeClubMC=>4
							, $strEventTypeClubFA=>5
							, $strEventTypeClubFB=>6  */
							, $strEventTypeClubBasic=>7
							, $strEventTypeClubAdvanced=>8
							, $strEventTypeClubTeam=>9
							, $strEventTypeClubCombined=>10
							, $strEventTypeClubMixedTeam=>11);


/**
 *	Combined Codes referenced with WO-combined contests
 */
$cfgCombinedDef = array(	410 => 'MAN'		// Stadion
				, 411 => 'MANU20'
				, 412 => 'MANU18'
				, 402 => 'U16M'
				, 400 => 'WOM'
				, 401 => 'U18W'
				, 399 => 'U16W'
				, 396 => 'HMAN'		// Halle
				, 397 => 'HMANU20'
				, 398 => 'HMANU18'
				, 394 => 'HWOM'		// 5Kampf Halle W
				, 3942 => 'H5MAN'	// 5Kampf Halle M
				, 395 => 'HWOMU18'  
                , 403 => 'ACup'     // Erdgas Athletic Cup
				);

/**	
 *	WO-combined contests, inclusive point table
 *		MAN => contests
 *		MAN_F => formula table
 */
$cfgCombinedWO = array(	'MAN' => array(40,330,351,310,70,271,361,320,391,110)
			, 'MAN_F' => 3
			, 'MANU20' => array(40,330,348,310,70,269,359,320,391,110)
			, 'MANU20_F' => 3
			, 'MANU18' => array(40,330,347,310,70,268,358,320,389,110)
			, 'MANU18_F' => 3
			, 'U16M' => array(261,330,349,310,357,100)
			, 'U16M_F' => 1
			, 'WOM' => array(261,310,349,50,330,388,90)
			, 'WOM_F' => 4
			, 'U18W' => array(259,330,388,50,310,352,90)
			, 'U18W_F' => 4
			, 'U16W' => array(35,330,352,310,100)
			, 'U16W_F' => 2
			, 'HMAN' => array(30,330,351,310,252,320,100)
			, 'HMAN_F' => 3
			, 'HMANU20' => array(30,330,348,310,253,320,100)
			, 'HMANU20_F' => 3
			, 'HMANU18' => array(30,330,347,310,254,320,100)
			, 'HMANU18_F' => 3
			, 'HWOM' => array(255,310,349,330,90)
			, 'HWOM_F' => 4
			, 'H5MAN' => array(252,310,351,330,90)
			, 'H5MAN_F' => 3
			, 'HWOMU18' => array(256,310,352,330,90)
			, 'HWOMU18_F' => 4              
            , 'ACup_U18M' => array(40,330,310,347)
            , 'ACup_U18M_F' => 1
            , 'ACup_U16M' => array(35,330,310,349)
            , 'ACup_U16M_F' => 1
            , 'ACup_U14M' => array(30,330,310,352,386)
            , 'ACup_U14M_F' => 1
            , 'ACup_U12M' => array(30,330,310,353,386)
            , 'ACup_U12M_F' => 1
            , 'ACup_U10M' => array(10,330,385)
            , 'ACup_U10M_F' => 1     
            , 'ACup_U18W' => array(40,330,310,352)
            , 'ACup_U18W_F' => 2
            , 'ACup_U16W' => array(35,330,310,352)
            , 'ACup_U16W_F' => 2
            , 'ACup_U14W' => array(30,330,310,352,386)
            , 'ACup_U14W_F' => 2
            , 'ACup_U12W' => array(30,330,310,353,386)
            , 'ACup_U12W_F' => 2
            , 'ACup_U10W' => array(10,330,385)
            , 'ACup_U10W_F' => 2  
			);

            /**    
 *    SVM contests, inclusive point table
 *        MAN => contests
 *        MAN_F => formula table
 *        MAN_T =>  fix times 
 *        MAN_ET => event type 
 *        MAN_NT => nulltime 
 */
$cfgSVM = array(    '20_01' => array(40,50,70,90,110,160,271,301,560,310,320,330,340,351,361,381,391) 
            , '20_01_F' => 7
            , '20_01_ET' => 12  
            , '20_01_T' => array(1445,1615,1715,1515,1330,1645,1415,1530,1315,1330,1330,1500,1615,1615,1445,1330,1615) 
            , '20_01_NT' => array('0130','0300','0400','0200','0015','0330','0100','0215','0000','0015','0015','0145','0300','0300','0130','0015','0300') 
            , '21_01' => array(40,50,70,90,110,160,271,301,560,310,320,330,340,351,361,381,391)
            , '21_01_F' => 7
            , '21_01_ET' => 12   
            , '21_01_T' => array(1445,1615,1715,1515,1330,1645,1415,1530,1315,1330,1330,1500,1615,1615,1445,1330,1615) 
            , '21_01_NT' => array('0130','0300','0400','0200','0015','0330','0100','0215','0000','0015','0015','0145','0300','0300','0130','0015','0300') 
            , '22_01' => array(40,50,70,90,110,160,271,301,560,310,320,330,340,351,361,391)
            , '22_01_F' => 7
            , '22_01_ET' => 12 
            , '22_01_T' => array(1445,1615,1715,1515,1330,1645,1415,1530,1315,1330,1330,1500,1615,1615,1445,1615) 
            , '22_01_NT' => array('0130','0300','0400','0200','0015','0330','0100','0215','0000','0015','0015','0145','0300','0300','0130','0300')  
            , '23_01' => array(40,50,70,90,110,160,271,560,310,320,330,340,351,361,381,391)
            , '23_01_F' => 7
            , '23_01_NT' => array('0200','0300','0400','0230','0000','0330','0045','0015','0145','0015','0100','0300','0145','0300','0000','0045') 
            , '23_01_ET' => 12   
            , '26_01' => array(40,70,90,140,560,310,320,330,340,351,361,381,391)
            , '26_01_F' => 1
            , '26_01_ET' => 8   
            , '26_02' => array(40,90,140,560,310,320,330,340,351,361,381,391)
            , '26_02_F' => 1
            , '26_02_ET' => 7  
            , '26_03' => array(40,100,560,310,330,351,361,391)
            , '26_03_F' => 1
            , '26_03_ET' => 7  
            , '26_04' => array(40,100,140,160,560,310,320,330,340,351,361,381,391)
            , '26_04_F' => 1
            , '26_04_ET' => 7   
            , '24_01' => array(40,70,110,140,269,560,310,320,330,340,348,359,378,391)
            , '24_01_F' => 1
            , '24_01_ET' => 7 
            , '24_01_NT' => array('0200','0400','0000','0330','0045','0015','0145','0015','0100','0300','0145','0300','0000','0045')    
            , '26_05' => array(40,70,110,560,310,320,330,340,348,359,378,391)
            , '26_05_F' => 1
            , '26_05_ET' => 7  
            , '26_06' => array(40,70,110,140,268,560,310,320,330,340,347,358,377,389)
            , '26_06_F' => 1
            , '26_06_ET' => 7   
            , '26_07' => array(40,100,268,310,330,347) 
            , '26_07_F' => 1
            , '26_07_ET' => 10   
            , '26_08' => array(35,100,261,498,310,320,330,340,349,357,376,388)
            , '26_08_F' => 1
            , '26_08_ET' => 7   
            , '26_09' => array(35,100,261,310,330,349)  
            , '26_09_F' => 1
            , '26_09_ET' => 10   
            , '26_10' => array(30,100,258,497,310,330,352,387)
            , '26_10_F' => 1
            , '26_10_ET' => 7   
            , '26_11' => array(30,100,497,310,352,387)
            , '26_11_F' => 1
            , '26_11_ET' => 9 
            , '26_12' => array(30,100,497,330,386)
            , '26_12_F' => 1   
            , '26_12_ET' => 9              
            , '20_02' => array(40,50,70,90,140,261,298,560,310,320,330,340,349,357,376,388)
            , '20_02_F' => 7
            , '20_02_ET' => 12 
            , '20_02_T' => array(1445,1615,1715,1515,1645,1415,1530,1315,1330,1330,1500,1615,1615,1445,1330,1615) 
            , '20_02_NT' => array('0130','0300','0400','0200','0330','0100','0215','0000','0015','0015','0145','0300','0300','0130','0015','0300')   
            , '21_02' => array(40,50,70,90,140,261,560,310,320,330,340,349,357,388)
            , '21_02_F' => 7 
            , '21_02_ET' => 12  
            , '21_02_T' => array(1445,1615,1715,1515,1645,1415,1315,1330,1330,1500,1615,1615,1445,1615) 
            , '21_02_NT' => array('0130','0300','0400','0200','0330','0100','0000','0015','0015','0145','0300','0300','0130','0300')            
            , '23_02' => array(40,50,90,110,259,560,310,330,340,351,361,391)
            , '23_02_F' => 7
            , '23_02_ET' => 12  
            , '23_02_NT' => array('0130','0230','0330','0315','0045','0000','0015','0230','0115','0115','0000','0230')      
            , '27_01' => array(40,50,90,110,261,560,310,320,330,340,351,361,381,391)
            , '27_01_F' => 2
            , '27_01_ET' => 7  
            , '27_02' => array(40,100,110,140,560,310,330,340,351,361,391)
            , '27_02_F' => 2
            , '27_02_ET' => 7   
            , '24_02' => array(40,50,90,110,261,560,310,320,330,340,349,357,376,388)
            , '24_02_F' => 2
            , '24_02_ET' => 7 
             , '24_02_NT' => array('0130','0230','0330','0315','0045','0000','0015','0330','0230','0115','0115','0000','0330','0230') 
            , '27_03' => array(40,90,110,259,560,310,320,330,340,352,357,375,388)
            , '27_03_F' => 20
            , '27_03_ET' => 7   
            , '27_04' => array(40,100,259,310,330,352)
            , '27_04_F' => 2 
            , '27_04_ET' => 10             
            , '27_05' => array(35,100,258,498,310,320,330,340,352,356,375,387)
            , '27_05_F' => 2
            , '27_05_ET' => 7  
            , '27_06' => array(35,100,258,310,330,352)
            , '27_06_F' => 2
            , '27_06_ET' => 10   
            , '27_07' => array(30,100,256,497,310,330,352,387)
            , '27_07_F' => 2
            , '27_07_ET' => 7   
            , '27_08' => array(30,100,497,310,352,387)
            , '27_08_F' => 2
            , '27_08_ET' => 9  
            , '27_09' => array(30,100,499,330,386)
            , '27_09_F' => 2
            , '27_09_ET' => 9  
            , '27_10' => array(30,100,499,330,386)
            , '27_10_F' => 1
            , '27_10_ET' => 11   
            , '28_01' => array(40,70,110,560,310,330,347,358,388)
            , '28_01_F' => 1
            , '28_01_ET' => 7   
            , '28_02' => array(40,50,90,261,560,310,330,349,357,388)
            , '28_02_F' => 2
            , '28_02_ET' => 7   
            , '29_01' => array(40,100,560,310,330,347,361,391)
            , '29_01_F' => 1
            , '29_01_ET' => 7   
            , '29_02' => array(40,80,560,310,330,349)
            , '29_02_F' => 2
            , '29_02_ET' => 7   
            );
  
/**
 * Heat status
 *		Status of result announcements per heat.
 */
$cfgHeatStatus = array("open"=>0
							, "announced"=>1
							);

/**
 *	Invalid Results
 *		Codes to be used for invalid results.
 */
$cfgInvalidResult = array("DNS"=>array ("code"=>-1
													, "short"=>$strDidNotStartShort
													, "long"=>$strDidNotStart
													)
								, "DNF"=>array ("code"=>-2
													, "short"=>$strDidNotFinishShort
													, "long"=>$strDidNotFinish
													)
								, "DSQ"=>array ("code"=>-3
													, "short"=>$strDisqualifiedShort
													, "long"=>$strDisqualified
													)
								, "NRS"=>array ("code"=>-4
													, "short"=>$strNoResultShort
													, "long"=>$strNoResult
													)
								, "WAI"=>array ("code"=>'-'
													, "short"=>$strQualifyWaivedShort
													, "long"=>$strQualifyWaived
													)  
                                , "NAA"=>array ("code"=>'X'
                                                    , "short"=>$strNoAccessAttemptShort
                                                    , "long"=>$strNoAccessAttempt
                                                    ) 
								, "NAA2"=>array ("code"=>'-98'
                                                    , "short"=>$strNoAccessAttemptShort
                                                    , "long"=>$strNoAccessAttempt
                                                    )
								, "WAI2"=>array ("code"=>'-99'
													, "short"=>$strQualifyWaivedShort
													, "long"=>$strQualifyWaived	
													)													
                                );

/**
 *	Missed Attempt
 *		Codes to be used for missed attempts in technical disciplines.
 */
$cfgMissedAttempt = array("code"=>'-'
                                    , "db"=>-99
                                ,
                          "codeX"=>'X'
                                    , "dbx"=>-98
                                );      

/**
 * Program Mode
 *		Mode may be defined per meeting. Used to define nbr of result fields
 *		that are displayed on the result form for technical disciplines.
 */
$cfgProgramMode = array(0 => array	("tech_res"=>1
												, "name"=>$strProgramModeBackoffice
												)
								,1 => array	("tech_res"=>6
												, "name"=>$strProgramModeField
												)
								);


/**
 *	Qualification type
 *		Qualification type for next round		
 */
$cfgQualificationType = array("top"=>array ("code"=>1
														, "class"=>"qual_top"
														, "token"=>"Q"
														, "text"=>$strQualifyTop
														)
								, "top_rand"=>array ("code"=>2
														, "class"=>"qual_top_rand"
														, "token"=>"Q*"
														, "text"=>"$strQualifyTop $strRandom"
														)
								, "perf"=>array ("code"=>3
														, "class"=>"qual_perf"
														, "token"=>"q"
														, "text"=>$strQualifyPerformance
														)
								, "perf_rand"=>array ("code"=>4
														, "class"=>"qual_perf_rand"
														, "token"=>"q*"
														, "text"=>"$strQualifyPerformance $strRandom"
														)
								, "waived"=>array ("code"=>9
														, "class"=>"qual_waived"
														, "token"=>"vQ"
														, "text"=>"$strQualifyWaived"
														)
								);


/**
 * Round status
 *		Round status to steer meeting workflow.
 */
$cfgRoundStatus = array("open"=>0
							, "heats_in_progress"=>1
							, "heats_done"=>2
							, "results_in_progress"=>3
                            , "results_live"=>30   
							, "results_done"=>4
							, "enrolement_pending"=>5
							, "enrolement_done"=>6
							, "results_sent"=>99
						);

$cfgRoundStatusTranslation = array(0=>$strOpen
											, 1=>$strHeatsInWork
											, 2=>$strHeatsDone
											, 3=>$strResultsInWork
											, 4=>$strResultsDone
											, 5=>$strEnrolementPending
											, 6=>$strEnrolementDone
										);

/**
 * Speaker status
 *		Speaker status per round to steer speaker monitor.
 */
$cfgSpeakerStatus = array("open"=>0
							, "announcement_pend"=>1
							, "announcement_done"=>2
							, "ceremony_done"=>3
						);

/**
 *
 * option list for page header and footer
 *
**/
$cfgPageLayout = array( $strPageNumbers => 0
			, $strMeetingName => 1
			, $strOrganizer => 2
			, $strDateAndTime => 3
			, $strCreatedBy => 4
			, $strOwnText => 5
			, $strNoText => 6
			);

/**
 *
 * option list for timing type
 *
**/
$cfgTimingType = array( $strNoTiming => 'no'
			, $strTimingOmega => 'omega'
			, $strTimingAlge => 'alge'
		);

/**
 * defines content types for creating export files
 *
 */
$cfgContentTypes = array(	'txt' => array('mt' => "text" // mime type
						, 'lb' => "\r\n" // line break
						, 'td' => "" // text delimiter
						, 'fd' => ",") // field delimiter
				, 'csv' => array('mt' => "application/ms-excel"
						, 'lb' => "\r\n"
						, 'td' => "\""
						, 'fd' => ";")
				, 'xls' => array('mt' => "application/ms-excel"
						, 'lb' => "\r\n"
						, 'td' => "\""
						, 'fd' => ";")
			);

/**
 *
 * License types for athletes
 *
**/
$cfgLicenseType = array(	$strLicenseTypeNormal => 1
				,$strLicenseTypeDayLicense => 2
				,$strLicenseTypeNoLicense => 3
			);

/**
 *
 * pages that can be accessed with out login
 *
 */
$cfgOpenPages = array(	"speaker"
			, "speaker_entries"
			, "speaker_entry"
			, "speaker_rankinglists"
			, "speaker_results"
			, "meeting"
			, $_COOKIE['meeting']
			, "login"
			, "admin_service");

/**
 *
 * char width table for Arial
 * used to determine line height on prints (if text is too long for cell width)
 *
**/
$cfgCharWidth = array(
	chr(0)=>278,chr(1)=>278,chr(2)=>278,chr(3)=>278,chr(4)=>278,chr(5)=>278,chr(6)=>278,chr(7)=>278,chr(8)=>278,chr(9)=>278,chr(10)=>278,chr(11)=>278,chr(12)=>278,chr(13)=>278,chr(14)=>278,chr(15)=>278,chr(16)=>278,chr(17)=>278,chr(18)=>278,chr(19)=>278,chr(20)=>278,chr(21)=>278,
	chr(22)=>278,chr(23)=>278,chr(24)=>278,chr(25)=>278,chr(26)=>278,chr(27)=>278,chr(28)=>278,chr(29)=>278,chr(30)=>278,chr(31)=>278,' '=>278,'!'=>278,'"'=>355,'#'=>556,'$'=>556,'%'=>889,'&'=>667,'\''=>191,'('=>333,')'=>333,'*'=>389,'+'=>584,
	','=>278,'-'=>333,'.'=>278,'/'=>278,'0'=>556,'1'=>556,'2'=>556,'3'=>556,'4'=>556,'5'=>556,'6'=>556,'7'=>556,'8'=>556,'9'=>556,':'=>278,';'=>278,'<'=>584,'='=>584,'>'=>584,'?'=>556,'@'=>1015,'A'=>667,
	'B'=>667,'C'=>722,'D'=>722,'E'=>667,'F'=>611,'G'=>778,'H'=>722,'I'=>278,'J'=>500,'K'=>667,'L'=>556,'M'=>833,'N'=>722,'O'=>778,'P'=>667,'Q'=>778,'R'=>722,'S'=>667,'T'=>611,'U'=>722,'V'=>667,'W'=>944,
	'X'=>667,'Y'=>667,'Z'=>611,'['=>278,'\\'=>278,']'=>278,'^'=>469,'_'=>556,'`'=>333,'a'=>556,'b'=>556,'c'=>500,'d'=>556,'e'=>556,'f'=>278,'g'=>556,'h'=>556,'i'=>222,'j'=>222,'k'=>500,'l'=>222,'m'=>833,
	'n'=>556,'o'=>556,'p'=>556,'q'=>556,'r'=>333,'s'=>500,'t'=>278,'u'=>556,'v'=>500,'w'=>722,'x'=>500,'y'=>500,'z'=>500,'{'=>334,'|'=>260,'}'=>334,'~'=>584,chr(127)=>350,chr(128)=>556,chr(129)=>350,chr(130)=>222,chr(131)=>556,
	chr(132)=>333,chr(133)=>1000,chr(134)=>556,chr(135)=>556,chr(136)=>333,chr(137)=>1000,chr(138)=>667,chr(139)=>333,chr(140)=>1000,chr(141)=>350,chr(142)=>611,chr(143)=>350,chr(144)=>350,chr(145)=>222,chr(146)=>222,chr(147)=>333,chr(148)=>333,chr(149)=>350,chr(150)=>556,chr(151)=>1000,chr(152)=>333,chr(153)=>1000,
	chr(154)=>500,chr(155)=>333,chr(156)=>944,chr(157)=>350,chr(158)=>500,chr(159)=>667,chr(160)=>278,chr(161)=>333,chr(162)=>556,chr(163)=>556,chr(164)=>556,chr(165)=>556,chr(166)=>260,chr(167)=>556,chr(168)=>333,chr(169)=>737,chr(170)=>370,chr(171)=>556,chr(172)=>584,chr(173)=>333,chr(174)=>737,chr(175)=>333,
	chr(176)=>400,chr(177)=>584,chr(178)=>333,chr(179)=>333,chr(180)=>333,chr(181)=>556,chr(182)=>537,chr(183)=>278,chr(184)=>333,chr(185)=>333,chr(186)=>365,chr(187)=>556,chr(188)=>834,chr(189)=>834,chr(190)=>834,chr(191)=>611,chr(192)=>667,chr(193)=>667,chr(194)=>667,chr(195)=>667,chr(196)=>667,chr(197)=>667,
	chr(198)=>1000,chr(199)=>722,chr(200)=>667,chr(201)=>667,chr(202)=>667,chr(203)=>667,chr(204)=>278,chr(205)=>278,chr(206)=>278,chr(207)=>278,chr(208)=>722,chr(209)=>722,chr(210)=>778,chr(211)=>778,chr(212)=>778,chr(213)=>778,chr(214)=>778,chr(215)=>584,chr(216)=>778,chr(217)=>722,chr(218)=>722,chr(219)=>722,
	chr(220)=>722,chr(221)=>667,chr(222)=>667,chr(223)=>611,chr(224)=>556,chr(225)=>556,chr(226)=>556,chr(227)=>556,chr(228)=>556,chr(229)=>556,chr(230)=>889,chr(231)=>500,chr(232)=>556,chr(233)=>556,chr(234)=>556,chr(235)=>556,chr(236)=>278,chr(237)=>278,chr(238)=>278,chr(239)=>278,chr(240)=>556,chr(241)=>556,
	chr(242)=>556,chr(243)=>556,chr(244)=>556,chr(245)=>556,chr(246)=>556,chr(247)=>584,chr(248)=>611,chr(249)=>556,chr(250)=>556,chr(251)=>556,chr(252)=>556,chr(253)=>500,chr(254)=>556,chr(255)=>500);





                                                                     
$cfgSrvHashU = "f3e99337796d868e3ae43ff87196fa92";
$cfgSrvHashP = "93d4ef379a7d3360db0e612e8021e642";

?>
