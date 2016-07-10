<?php
//settings from athletica (config.inc.php)

$cfgEvalType = array(         $strEvalTypeHeat=>0
                            , $strEvalTypeAll=>1
                            , $strEvalTypeDiscDefault=>2);
                                
$cfgEventType = array(        $strEventTypeSingle=>0
                            , $strEventTypeSingleCombined=>1
                            , $strEventTypeTeamSM=>30
                            , $strEventTypeSVMNL=>12
                            , $strEventTypeClubBasic=>7
                            , $strEventTypeClubAdvanced=>8
                            , $strEventTypeClubTeam=>9
                            , $strEventTypeClubCombined=>10
                            , $strEventTypeClubMixedTeam=>11);
                            
$cfgQualificationType = array("top"=>array ("code"=>1
                            , "class"=>"qual_top"
                            , "token"=>"Q"
                            )
    , "top_rand"=>array ("code"=>2
                            , "class"=>"qual_top_rand"
                            , "token"=>"Q*"
                            )
    , "perf"=>array ("code"=>3
                            , "class"=>"qual_perf"
                            , "token"=>"q"
                            )
    , "perf_rand"=>array ("code"=>4
                            , "class"=>"qual_perf_rand"
                            , "token"=>"q*"
                            )
    , "waived"=>array ("code"=>9
                            , "class"=>"qual_waived"
                            , "token"=>"vQ"
                            )
    );
$cfgResultsSeparator = ",";
$cfgResultsWindSeparator = ".";
    
$cfgResultsSepTrans = array("."=>"$cfgResultsSeparator"
                            ,    ":"=>"$cfgResultsSeparator"
                            , ";"=>"$cfgResultsSeparator");
                            
$cfgResultsWindDefault = "-";
                            
?>
