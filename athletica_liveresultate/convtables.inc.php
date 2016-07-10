<?php

/**
 * C O N V E R S I O N   T A B L E S
 * ---------------------------------
 */

include('./config.inc.php');

/**
 *	Conversion tables
 *		List of all implemented conversion tables.		
 *		The table name is used as key, its value matches a key in the formula
 *		table ($cvtFormulas).
 */
$cvtTable = array("-"=>0
					, $strConvtableSLV94Men=>1
					, $strConvtableSLV94Women=>2
					, $strConvtableIAAF85Men=>3
					, $strConvtableIAAF85Women=>4
					//, $strConvtableSVMMenNL=>5                // old formula
					//, $strConvtableSVMWomenNL=>6              // old formula
					, $strConvtableRankingPoints=>7
);

/**
 *	Formula table
 *		List of formulas to calculate points per performance. The key represents
 *		a conversion table as defined in $cvtTable.
 *		An array holds all formulas per discipline, with the discipline's
 *		short name as key and the formula parameters as value. The value is
 *		a list of blank-separated parameters. The first parameter describes the
 *		formula to be used, all following parameters are needed by this formula
 *		to calculate points for a given performance.
 *		(see also function AA_utils_calcPoints)
 */
$cvtFormulas = array(
0=>array ("-"=>"0")

// SLV 94 Men
, 1=>array ("50"=>"1 23.327251 1219 2.1"
			, "60"=>"1 17.686955 1397 2.1"
			, "80"=>"1 10.545960 1778 2.1"
			, "100"=>"1 7.080303 2150 2.1"
			, "200"=>"1 1.315320 4567 2.1"
			, "300"=>"1 0.492671 7295 2.1"
			, "400"=>"1 0.249724 10082 2.1"
			, "600"=>"1 0.086375 16833 2.1"
			, "800"=>"1 0.042083 23537 2.1"
			, "1000"=>"1 0.0068251 32581 2.3"
			, "1500"=>"1 0.0024384 50965 2.3"
			, "2000"=>"1 0.0011358 71036 2.3"
			, "3000"=>"1 0.00041504 110024 2.3"
			, "5000"=>"1 0.00011812 189996 2.3"
			, "10000"=>"1 0.000021844 395879 2.3"
			, "1500ST"=>"1 0.0018664 56163 2.3"
			, "2000ST"=>"1 0.00094366 77009 2.3"
			, "3000ST"=>"1 0.00035433 117893 2.3"
			, "50H"=>"1 14.460128 1459 2.1"
			, "60H"=>"1 10.294837 1715 2.1"
			, "80H"=>"1 5.925928 2231 2.1"
			, "100H"=>"1 3.828440 2747 2.1"
			, "110H"=>"1 3.174673 3003 2.1"
			, "200H"=>"1 0.796937 5460 2.1"
			, "300H"=>"1 0.364731 8190 2.1"
			, "400H"=>"1 0.211237 10921 2.1"
			, "4X100"=>"1 0.355982 8600 2.1"
			, "4X400"=>"1 0.013902 40328 2.1"
			, $strConvformulaHigh=>"2 690.05175 65 1.0"
			, $strConvformulaLong=>"2 180.85908 190 1.0"
			, $strConvformulaPole=>"2 234.78771 80 1.0"
			, $strConvformulaTrip=>"2 86.950221 395 1.0"
			, $strConvformulaShot=>"2 82.491673 178 0.9"
			, $strConvformulaDisc=>"2 28.891406 494 0.9"
			, $strConvformulaHamm=>"2 24.978132 581 0.9"
			, $strConvformulaJave=>"2 23.247477 602 0.9"
			, $strConvformulaBall=>"2 18.0 800 0.9"
			)

// SLV 94 Women
, 2=>array ("50"=>"1 26.011098 1236 2.1"
			, "60"=>"1 19.742424 1417 2.1"
			, "80"=>"1 11.754907 1803 2.1"
			, "100"=>"1 7.893050 2180 2.1"
			, "200"=>"1 1.435839 4649 2.1"
			, "300"=>"1 0.515644 7564 2.1"
			, "400"=>"1 0.261208 10454 2.1"
			, "600"=>"1 0.089752 17543 2.1"
			, "800"=>"1 0.043620 24531 2.1"
			, "1000"=>"1 0.0069140 34158 2.3"
			, "1500"=>"1 0.0024951 53216 2.3"
			, "2000"=>"1 0.0011486 74565 2.3"
			, "3000"=>"1 0.00042789 114561 2.3"
			, "5000"=>"1 0.00011545 202413 2.3"
			, "10000"=>"1 0.000021257 422397 2.3"
			, "50H"=>"1 16.638377 1448 2.1"
			, "60H"=>"1 12.060698 1688 2.1"
			, "80H"=>"1 7.107482 2171 2.1"
			, "100H"=>"1 4.674232 2650 2.1"
			, "200H"=>"1 0.795911 5712 2.1"
			, "300H"=>"1 0.371294 8570 2.1"
			, "400H"=>"1 0.217291 11424 2.1"
			, "4X100"=>"1 0.405548 8720 2.1"
			, "4X400"=>"1 0.014782 41816 2.1"
			, $strConvformulaHigh=>"2 855.310049 62 1.0"
			, $strConvformulaLong=>"2 220.628792 180 1.0"
			, $strConvformulaTrip=>"2 106.044538 374 1.0"
			, $strConvformulaPole=>"2 307.69230 70 1.0"
			, $strConvformulaShot=>"2 83.435373 130 0.9"
			, $strConvformulaDisc=>"2 27.928062 362 0.9"
			, $strConvformulaJave=>"2 28.058125 360 0.9"
			, $strConvformulaHamm=>"2 25.267696 405 0.9"
			, $strConvformulaBall=>"2 22.000 500 0.9"
			)

// IAAF 85 Men
, 3=>array ("60"=>"1 58.015 1150 1.81"
				, "100"=>"1 25.43470 1800 1.81"
				, "200"=>"1 5.84250 3800 1.81"
				, "300"=>"1 2.58503 6010 1.81"
				, "400"=>"1 1.53775 8200 1.81"
				, "800"=>"1 0.13279 23500 1.85"
				, "1000"=>"1 0.08713 30550 1.85"
				, "1500"=>"1 0.03768 48000 1.85"
				, "3000"=>"1 0.0105 100500 1.85"
				, "5000"=>"1 0.00419 168000 1.85"
				, "10000"=>"1 0.000415 424500 1.9"
				, "60H"=>"1 20.5173 1550 1.92"
				, "110H"=>"1 5.74352 2850 1.92"
				, "200H"=>"1 3.495 4550 1.81"
				, "400H"=>"1 1.1466 9200 1.81"
				, "3000ST"=>"1 0.00511 115500 1.9"
				, $strConvformulaHigh=>"3 0.84650 75 1.42"
				, $strConvformulaPole=>"3 0.27970 100 1.35"
				, $strConvformulaLong=>"3 0.14354 220 1.40"
				, $strConvformulaTrip=>"3 0.06533 640 1.40"
				, $strConvformulaShot=>"2 51.39 150 1.05"
				, $strConvformulaDisc=>"2 12.91 400 1.10"
				, $strConvformulaHamm=>"2 13.0449 700 1.05"
				, $strConvformulaJave=>"2 10.14 700 1.08"
				)

// IAAF 85 Women
, 4=>array ("60"=>"1 46.0849 1300 1.81"
				, "100"=>"1 17.857 2100 1.81"
				, "200"=>"1 4.99087 4250 1.81"
				, "400"=>"1 1.34285 9170 1.81"
				, "800"=>"1 0.11193 25400 1.88"
				, "1000"=>"1 0.07068 33700 1.88"
				, "1500"=>"1 0.02883 53500 1.88"
				, "3000"=>"1 0.00683 115000 1.88"
				, "5000"=>"1 0.00272 192000 1.88"
				, "10000"=>"1 0.000396 492000 1.88"
				, "60H"=>"1 20.0479 1700 1.835"
				, "100H"=>"1 9.23076 2670 1.835"
				, "200H"=>"1 2.975 5200 1.81"
				, "400H"=>"1 0.99674 10300 1.81"
				, "3000ST"=>"1 0.00408 132000 1.9"
				, $strConvformulaHigh=>"3 1.845230 75 1.348"
				, $strConvformulaPole=>"3 0.44125 100 1.35"
				, $strConvformulaLong=>"3 0.188807 210 1.41"
				, $strConvformulaTrip=>"3 0.08559 600 1.41"
				, $strConvformulaShot=>"2 56.0211 150 1.05"
				, $strConvformulaDisc=>"2 12.3311 300 1.1"
				, $strConvformulaHamm=>"2 17.5458 600 1.05"
				, $strConvformulaJave=>"2 15.9803 380 1.04"
				)

// SVM NLA-C Men, 2005
, 5=>array ("100"=>"4 27.9 16.6 0"
			, "200"=>"4 4.799 36 0"
			, "400"=>"4 0.863 82 0"
			, "800"=>"4 0.18778 184 0"
			, "1500"=>"4 0.04066 385 0"
			, "5000"=>"4 0.0028 1440 0"
			, "110H"=>"4 7.413 26 0"
			, "400H"=>"4 0.509297 97 0"
			, "4X100"=>"4 1.2019 70 0"
			, $strConvformulaHigh=>"5 39.4106 10.2 5000"
			, $strConvformulaLong=>"5 1.82116 50 5000"
			, $strConvformulaTrip=>"5 0.48028 96.2128 5000"
			, $strConvformulaPole=>"5 3.4426 36.63 5000"
			, $strConvformulaShot=>"5 0.04298 681 20000"
			, $strConvformulaDisc=>"5 0.004233 2170 20000"
			, $strConvformulaHamm=>"5 0.0029488 2600 20000"
			, $strConvformulaJave=>"5 0.0025036 2821.5 20000"
			)

// SVM NLA-B Women, 2005
, 6=>array ("100"=>"4 6.5713 24.5 0"
			, "200"=>"4 1.281 53 0"
			, "400"=>"4 0.2453 120 0"
			, "800"=>"4 0.06826 250 0"
			, "3000"=>"4 0.002539 1200 0" // updated
			, "100H"=>"4 3.406 31.4 0"
			, "400H"=>"4 0.208567 130 0"
			, "4X100"=>"4 0.3954 98 0"
			, $strConvformulaHigh=>"5 48.28 9.3267 5000"
			, $strConvformulaLong=>"5 1.90302 50.0582 5000"
			, $strConvformulaTrip=>"5 0.449264 102.4956 5000"
			, $strConvformulaPole=>"5 4.8722 30.845 5000"
			, $strConvformulaShot=>"5 0.04631 656 20000"
			, $strConvformulaDisc=>"5 0.004156 2190 20000"
			, $strConvformulaHamm=>"5 0.0033788 2428.9 20000"
			, $strConvformulaJave=>"5 0.004195 2180 20000"
			)


// SVM Ranking Points (National League only)
, 7=>array	(	"16 -1"=>"16 1"
			, "16 -2"=>"16 2"
		)

);

?>
