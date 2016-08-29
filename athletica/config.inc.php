<?php
/**
 * C O N F I G U R A T I O N
 * -------------------------
 */

/* 
 *	ATTENTION:
 *	Do not change the following options without knowing what you're doing.
 *	These options steer the program flow, therefore changes almost certainly
 *	also require changes to the affected functions.
 *	
 */
 
/**
 * Application Info
 */
$cfgApplicationName = 'Athletica';
$cfgApplicationVersion = '7.1';
$cfgInstallDir = '[ATHLETICA]';

/**
 * Alphabeth 
*/
$cfgAlphabeth = array ("A","B","C","D","E","F","G","H","I","J","K","L","M",
                       "N","O","P","Q","R","S","T","U","V","W","X","Y","Z");

/**
 * Backup Info
*/
$cfgBackupCompatibles = array(
	// SLV
	'SLV_1.4',
	'SLV_1.5',
	'SLV_1.6',
	'SLV_1.7',
	'SLV_1.7.1',
	'SLV_1.7.2',
	'SLV_1.8',
	'SLV_1.8.1',
	'SLV_1.8.2',
	'SLV_1.9',
	// Athletica
	'3.0',
	'3.0.1',
	'3.1',
	'3.1.1',
	'3.1.2',
	'3.2',
	'3.2.1',
	'3.2.2',
	'3.2.3',
	'3.3',
	'3.3.1',
	'3.3.2',
	'3.3.3',
	'3.3.4',
	'3.3.5',
	'3.3.6',
	'3.3.7', 
	'3.3.8',
	'3.3.9',
	'3.3.10',
	'3.3.11',
	'3.3.12', 
	'3.3.13', 
	'3.3.14', 
	'3.3.15', 
    '3.3.16',    
    '3.3.17_Beta',     
    '3.4',
    '3.4.1', 
    '3.4.2',   
    '3.4.3', 
    '3.4.4',   
    '3.4.5', 
    '3.4.6', 
    '3.5_Beta',
    '3.5',
    '4.0', 
    '4.1',  
    '5.0',  
    '5.1',
    '5.2',  
    '5.3',    
    '6.0',
    '6.1',
    '6.2',
    '6.3',                                                                                           
    '7.0',                                                                                           
    '7.1'                                                                                           
);


/**
 * Include language parameters
 */
include("./lang/german.inc.php"); // if an other language is set, no text will be missing (even if its in german)
if(!empty($_COOKIE['language_trans'])) {
	include ($_COOKIE['language_trans']);
}
$cfgURLDocumentation = $_COOKIE['language_doc'];


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
			$cfgDisciplineType[$strDiscTypeJump]=>6
			, $cfgDisciplineType[$strDiscTypeJumpNoWind]=>6
			, $cfgDisciplineType[$strDiscTypeThrow]=>6);
            
$cfgFinalist = 8;

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
                , 413 => '10WOM'    // Zehnkampf W
                , 414 => '10MASM'   // Zehnkampf MM
				, 402 => '6U16M'    // Sechskampf U16M
				, 400 => 'WOM'
				, 401 => 'U18W'     // Siebenkamp U18W
                , 406 => '5U16M'     // F?nfkampf U16 M   
                , 425 => '5U16MI'    // F?nfkampf U16 M Indoor  
                , 407 => 'U23M'     // F?nfkampf U23 M
                , 424 => 'HU18M'    // F?nfkampf Halle U18 M
                , 392 => '5MAN'     // F?nfkampf M
                , 393 => '5MANU20'  // F?nfkampf U20 M
                , 427 => '8MANU18'  // Achtkampf U18 M
				, 399 => 'U16W'     // F?nfkampf U16 W
                , 426 => '5U16WI'    // F?nfkampf U16 W Indoor  
				, 396 => 'HMAN'		// Halle
				, 397 => 'HMANU20'
				, 398 => 'HMANU18'
				, 394 => 'HWOM'		// 5Kampf Halle W
				, 3942 => 'H5MAN'	// 5Kampf Halle M
				, 395 => 'HWOMU18'  // 5Kampf Halle U18 W
                , 408 => 'UKC'      // UBS Kids Cup
                , 405 => '5MANU18'  // F?nfkampf U18 M
                , 416 => '5WOM'     // F?nfkampf W
                , 417 => '5WOMU20'  // F?nfkampf U20 W
                , 418 => '5WOMU18'  // F?nfkampf U18 W  
                , 799 => '..Ka'     // ...kampf
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
			, '6U16M' => array(261,330,349,310,357,100)
			, '6U16M_F' => 1                
			, 'WOM' => array(261,310,349,50,330,388,90)
			, 'WOM_F' => 4
			, 'U18W' => array(259,330,390,50,310,352,90)
			, 'U18W_F' => 4               
			, 'U16W' => array(35,330,352,310,100)
			, 'U16W_F' => 2  
            
            , '5U16WI' => array(30,330,352,310,100)
            , '5U16WI_F' => 2  
                                      
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
            
            , '5MAN' => array(40,330,351,310,100)
            , '5MAN_F' => 3
             , '5MANU20' => array(40,330,348,310,100) 
            , '5MANU20_F' => 3 
             , '5MANU18' => array(40,330,347,310,100) 
            , '5MANU18_F' => 3
             , '5WOM' => array(40,310,349,330,100) 
            , '5WOM_F' => 3
             , '5WOMU20' => array(40,330,349,310,100) 
            , '5WOMU20_F' => 3
             , '5WOMU18' => array(40,310,352,330,100) 
            , '5WOMU18_F' => 3
            
			, 'HWOMU18' => array(256,310,352,330,90)
			, 'HWOMU18_F' => 4  
            
            , 'UKC' => array(30,331,386)
            , 'UKC_F_m' => 1  
            , 'UKC_F_w' => 2             
            
            , 'HU18M' => array(30,330,347,310,100) 
            , 'HU18M_F' => 1   
            , 'U23M' => array(40,330,351,310,100) 
            , 'U23M_F' => 1   
            , '5U16M' => array(35,330,349,310,100) 
            , '5U16M_F' => 1   
            , '5U16MI' => array(30,330,349,310,100) 
            , '5U16MI_F' => 1   
            , '10WOM' =>  array(40,330,349,310,70, 261,357,320,388,110)
            , '10WOM_F' => 4   
            , '10MASM' => array(40,330,351,310,70,271,361,320,391,110)            
            , '10MASM_F' => 1  
            
            , '8MANU18' => array(40,330,347,70,268,310,389,100)             
            , '8MANU18_F' => 3
           
           
			);

            /**    
 *    SVM contests, inclusive point table
 *        MAN => contests
 *        MAN_F => formula table
 *        MAN_T =>  fix times 
 *        MAN_ET => event type 
 *        MAN_NT => nulltime 
 * *      MAN_C => category corresponding to svm category
 */
$cfgSVM = array(    '29_01' => array(40,50,70,90,140,271,301,560,310,320,330,340,351,361,381,391) 
            , '29_01_F' => 7
            , '29_01_ET' => 12  
            , '29_01_T' => array(1515,1635,1410,1200,1320,1230,1610,1140,1430,1100,1200,1500,1230,1340,1100,1140) 
            , '29_01_NT' => array('0415','0535','0310','0100','0220','0130','0510','0040','0330','0000','0100','0400','0130','0240','0000','0040') 
            , '29_01_C' => 'MAN_' 
            , '29_01_D' => 'Nat.A' 
            
            , '29_02' => array(40,50,70,90,140,261,298,560,310,320,330,340,349,357,376,388)  
            , '29_02_F' => 7
            , '29_02_ET' => 12 
            , '29_02_T' => array(1530,1650,1430,1215,1340,1255,1550,1130,1140,1430,1500,1215,1430,1130,1410,1500) 
            , '29_02_NT' => array('0400','0525','0300','0045','0210','0125','0420','0000','0010','0300','0330','0045','0300','0000','0240','0330') 
            , '29_02_C' => 'WOM_' 
            , '29_02_D' => 'Nat.A'        
            
            , '30_01' => array(40,50,70,90,140,271,301,560,310,320,330,340,351,361,391) 
            , '30_01_F' => 7
            , '30_01_ET' => 12   
            , '30_01_T' => array(1515,1630,1410,1200,1320,1230,1600,1145,1430,1130,1200,1500,1230,1340,1145) 
            , '30_01_NT' => array('0345','0500','0240','0030','0150','0100','0430','0015','0300','0000','0030','0330','0100','0210','0015') 
            , '30_01_C' => 'MAN_' 
            , '30_01_D' => 'Nat.B'          
            
            , '30_02' => array(40,50,70,90,140,261,560,310,320,330,340,349,357,388)   
            , '30_02_F' => 7 
            , '30_02_ET' => 12  
            , '30_02_T' => array(1530,1645,1430,1215,1340,1255,1130,1145,1445,1500,1215,1430,1130,1500) 
            , '30_02_NT' => array('0400','0515','0300','0045','0210','0125','0000','0015','0315','0330','0045','0300','0000','0330') 
            , '30_02_C' => 'WOM_' 
            , '30_02_D' => 'Nat.B'               
            
            , '31_01' => array(40,50,70,90,140,271,301,560,310,320,330,351,361,391) 
            , '31_01_F' => 7
            , '31_01_ET' => 12 
            , '31_01_T' => array(1500,1500,1420,1230,1355,1310,1600,1215,1420,1200,1230,1310,1400,1215) 
            , '31_01_NT' => array('0300','0300','0220','0030','0155','0110','0400','0015','0220','0000','0030','0110','0200','0015') 
            , '31_01_C' => 'MAN_'  
            , '31_01_D' => 'Nat.C'        
            
            , '31_02' => array(40,50,90,261, 560,310, 330,340,349,357,388)  
            , '31_02_F' => 7
            , '31_02_ET' => 12 
            , '31_02_T' => array(1530,1530,1245,1330,1200,1215,1420,1245,1445,1200,1445) 
            , '31_02_NT' => array('0330','0330','0045','0130','0000','0015','0220','0045','0245','0000','0245')  
            , '31_02_C' => 'WOM_'  
            , '31_02_D' => 'Nat.C'                 
             
            , '32_01' => array(40,50,70,90,140,271,560,310,330,351,391) 
            , '32_01_F' => 7
            , '32_01_ET' => 12     
            , '32_01_T' => array(1500,1500,1430,1230,1400,1315,1215,1430,1230,1315,1215)  
            , '32_01_NT' => array('0245','0245','0215','0015','0145','0100','0000','0215','0015','0100','0000')  
            , '32_01_C' => 'MAN_'  
            , '32_01_D' => 'Reg.A'                                   
             
            , '32_03' => array(40,50,90,261,560,310,330,340,349,357,388) 
            , '32_03_F' => 7
            , '32_03_ET' => 12     
            , '32_03_T' => array(1530,1530,1245,1330,1200,1215,1430,1245,1430,1200,1415)  
            , '32_03_NT' => array('0330','0330','0045','0130','0000','0015','0230','0045','0230','0000','0215') 
            , '32_03_C' => 'WOM_'    
            , '32_03_D' => 'Reg.A'                 
            
            , '32_07' => array( 40,90,140,560,310,330,351,391)  
            , '32_07_F' => 7
            , '32_07_ET' => 12 
            , '32_07_C' => 'MAN_'   
            , '32_07_D' => 'Reg.B'    
            
            , '32_08' => array(40,90,261,560,310,330,349,388) 
            , '32_08_F' => 7
            , '32_08_ET' => 12   
            , '32_08_C' => 'WOM_'
            , '32_08_D' => 'Reg.B'     
           
            , '33_01' => array(40,70,90,140,269,560,310,320,330,348,359, 391)    
            , '33_01_F' => 8
            , '33_01_ET' => 7     
            , '33_01_T' => array(1500,1430,1230,1400,1315,1215,1430,1200,1230,1315,1430,1215)  
            , '33_01_NT' => array('0300','0230','0030','0200','0115','0015','0230','0000','0030','0115','0230','0015')    
            , '33_01_C' => 'U20M'   
            , '33_01_D' => 'Jun'                    
           
            , '33_03' => array(50,90,261,560,310,330,349,388)  
            , '33_03_F' => 8
            , '33_03_ET' => 7     
            , '33_03_T' => array(1530,1245,1330,1200,1215,1430,1445,1345)  
            , '33_03_NT' => array('0330','0045','0130','0000','0015','0230','0245','0145') 
            , '33_03_C' => 'U20W'
            , '33_03_D' => 'Jun'
             
            , '35_01' => array(40,100,140,160,560,310,320,330,340,351,361,381,391)    
            , '35_01_F' => 1
            , '35_01_ET' => 7  
            , '35_01_C' => 'MASM'
            , '35_01_D' => 'M30' 
            
            , '35_02' => array(40,70,110,140,268,560,310,320,330,340,347,358,377,389)  
            , '35_02_F' => 1
            , '35_02_ET' => 7             
            , '35_02_C' => 'U18M'  
            , '35_02_D' => 'E'             
            
            , '35_03' => array(40,100,268,310,330,347)   
            , '35_03_F' => 1
            , '35_03_ET' => 10  
            , '35_03_C' => 'U18M' 
            , '35_03_D' => 'MK'                      
            
            , '35_04' => array(35,100,261,498,310,320,330,340,349,357,376,388)  
            , '35_04_F' => 1
            , '35_04_ET' => 7 
            , '35_04_C' => 'U16M'  
            , '35_04_D' => 'E'                        
            
            
            , '35_05' => array(35,100,261,310,330,349)   
            , '35_05_F' => 1
            , '35_05_ET' => 10    
            , '35_05_C' => 'U16M'   
            , '35_05_D' => 'MK'             
            
            , '35_06' => array(30,100,258,497,310,331,352,387) 
            , '35_06_F' => 1
            , '35_06_ET' => 7   
            , '35_06_C' => 'U14M'  
            , '35_06_D' => 'E'                
            
            , '35_07' => array(30,100,497,310,352,387)   
            , '35_07_F' => 1
            , '35_07_ET' => 9  
            , '35_07_C' => 'U14M' 
            , '35_07_D' => 'MW'             
            
            , '35_08' => array(30,100,499,331,386)  
            , '35_08_F' => 1   
            , '35_08_ET' => 9   
            , '35_08_C' => 'U12M'  
            , '35_08_D' => 'MW'             
            
            , '36_01' => array(40,100,110,140,560,310,330,340,349,357,388)   
            , '36_01_F' => 2
            , '36_01_ET' => 7  
            , '36_01_C' => 'MASW'
            , '36_01_D' => 'W30'             
                  
            , '36_02' => array(40,90,110,259,560,310,320,330,340,352,357,375,390)   
            , '36_02_F' => 2
            , '36_02_ET' => 7 
            , '36_02_C' => 'U18W' 
            , '36_02_D' => 'E'             
            
            , '36_03' => array(40,100,259,310,330,352)  
            , '36_03_F' => 2
            , '36_03_ET' => 10  
            , '36_03_C' => 'U18W'  
            , '36_03_D' => 'MK'                   
            
            , '36_04' => array(35,100,258,498,310,320,330,340,352,356,375,387)  
            , '36_04_F' => 2
            , '36_04_ET' => 7 
            , '36_04_C' => 'U16W'  
            , '36_04_D' => 'E'             
            
            , '36_05' => array(35,100,258,310,330,352)  
            , '36_05_F' => 2
            , '36_05_ET' => 10 
            , '36_05_C' => 'U16W'  
            , '36_05_D' => 'MK'             
             
            , '36_06' => array(30,100,256,497,310,331,352,387)  
            , '36_06_F' => 2
            , '36_06_ET' => 7   
            , '36_06_C' => 'U14W'
            , '36_06_D' => 'E'             
             
            , '36_07' => array(30,100,497,310,352,387)  
            , '36_07_F' => 2
            , '36_07_ET' => 9  
            , '36_07_C' => 'U14W'  
            , '36_07_D' => 'MW'             
            
            , '36_08' => array(30,100,499,331,386)  
            , '36_08_F' => 2   
            , '36_08_ET' => 9 
            , '36_08_C' => 'U12W'
            , '36_08_D' => 'MW'             
             
            , '36_09' => array(30,100,499,331,386)   
            , '36_09_F' => 1
            , '36_09_ET' => 11 
            , '36_09_C' => 'U12M'   
            , '36_09_D' => 'MIX'             
           
            );
            
/**
 *    TV Name Disciplines
 */
$cfgTVDef = array("de" => array (232 => '50m Hurdles'
                                , 252 => '60m Hurdles'  
                                , 258 => '80m Hurdles' 
                                , 259 => '100m Hurdles'  
                                , 268 => '110m Hurdles' 
                                , 280 => '200m Hurdles' 
                                , 289 => '300m Hurdles'  
                                , 298 => '400m Hurdles' 
                                , 347 => 'Kugel'
                                , 356 => 'Discus'
                                , 375 => 'Hammer'
                                , 387 => 'Javelin'
                                , 385 => 'Ball'                                 
                                , 'm' => 'Men' 
                                , 'w'=> 'Women'                                                    
                                ),
                  "fr" => array (259 => 'Haies' 
                                , 268 => '110m Hurdles' 
                                , 298 => '400m Hurdles'        
                                , 347 => 'Poids'
                                , 356 => 'Disque'
                                , 375 => 'Marteau'
                                , 387 => 'Javelot'
                                , 385 => 'Balle' 
                                , 'm' => 'Hommes' 
                                , 'w'=> 'Femmes'                                                                     
                                ),
                  "it" => array (259 => 'Ostacoli' 
                                , 268 => '110m Hurdles' 
                                , 298 => '400m Hurdles'               
                                , 347 => 'Peso'
                                , 356 => 'Disco'
                                , 375 => 'Martello'
                                , 387 => 'Giavellotto'
                                , 385 => 'Pallina' 
                                , 'm' => 'Uomini' 
                                , 'w'=> 'Donne'                                                                     
                                ));  
    
 // UBS Kids Cup 
 $cfgUKC_disc = array("30","331","386");
 $cfgUKC_disc_F = array("de" => array('60','WEIT','BALL'),
                        'fr' => array('60','LONGUEUR','BALLE'),
                        'it' => array("60","LUNGO","PALLINA"));  
 
 $cfgUKC_Name = "UBS Kids Cup";
                                
// SVM reserve discipline                               
$cfgResDisc = 811;   
// SVM discipline (don't exist in athletica)    
$cfgSvmDiscFirst = 812;
$cfgSvmDiscLast = 819;

  
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
                                                
                                ,2 => array ("tech_res"=>6
                                                , "name"=>$strProgramModeFieldRank
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
                                            , 30=>$strResultsLive
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
            , "regie"
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


/**
 * Connection information for the slv webserver
 *
 *
 */
$cfgSLVhost = "www.swiss-athletics.ch";
$cfgSLVhostUKC = "ubs-kidscup.ch"; 
$cfgSLVuser = "athletica";
$cfgSLVpass = "impBOSS";
$cfgSrvHashU = "f3e99337796d868e3ae43ff87196fa92";
$cfgSrvHashP = "93d4ef379a7d3360db0e612e8021e642";

?>
