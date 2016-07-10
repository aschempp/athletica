<?php
/**
* the application's name
* @var string
*/
define('CFG_APPLICATION_NAME', 'Athletica - Technical Client');

/**
* cookie name
* @var string
*/
define('CFG_COOKIE', 'athtech');

/**
* session name
* @var string
*/
define('CFG_SESSION', 'athtech');

/**
* default language
* @var string
*/
define('CFG_DEFAULT_LANGUAGE', 'de');

/**
* default mode
* @var string
*/
//define('CFG_DEFAULT_MODE', 'local');
define('CFG_DEFAULT_MODE', 'live');

$glb_types_results = array(
    "4"=>'tech' // jump
    , "5"=>'tech' // jump without wind
    , "6"=>'high' // high/pole vault    
    , "8"=>'tech' // throw   
);

$glb_status_results = array(
    2 // heats done
    , 3 // results in work
    , 30 // live results in work    
);
$glb_status_live = 30;
$glb_status_quit = 3;

$glb_height_next = 5;
$glb_high_diff_1_until_default = 160;
$glb_high_diff_2_until_default = 180;
$glb_high_diff_1_value_default = 5;
$glb_high_diff_2_value_default = 3;
$glb_high_diff_3_value_default = 2;

$glb_results_meter_separator = ".";    // token separating meters
$glb_results_wind_separator = ".";    // token separating wind

$glb_invalid_attempt = array("-98"=>'NAA' 
                            , "-99"=>'WAI'
                            , "-1"=>'DNS'
                            , "-3"=>'DSQ'
                            );     
                            
$glb_invalid_attempt_input = array("-"=>'-99' 
                                , "X"=>'-98'
                                );
                                
$glb_invalid_attempt_button = array("NAA"=>'-98'
                                , "WAI"=>'-99'
                                , "DNS"=>'-1'
                                , "DSQ"=>'-3'
                                );
                                
$glb_results_skip = array("DNS" => '-1'
                        ,"DSQ" => '-3');
                        
$glb_high_attempt_passed = "O";   
$glb_high_attempt_failed = "X";
$glb_high_attempt_waived = "-";    

$glb_high_attempt_input = array("O"=>'O'
                                , '0'=>'O'
                                , "X"=>'X'
                                , "-"=>'-');              

//$modes = array('local', 'live');
$modes = array('live');
?>
