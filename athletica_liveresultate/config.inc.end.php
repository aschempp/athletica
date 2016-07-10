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
 
 include("./lang/german.inc.php"); // if an other language is set, no text will be missing (even if its in german)   
  
$dirUser = $GLOBALS['cfgDir'];  
$cfgUrl =  $GLOBALS['cfgUrl'];      
    
 
$cfgHtmlStart1 = " <?php ?><!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dt'> 
                    <html><head>
                   <link type='text/css' href='/css/screen.css' rel='stylesheet' media='screen' />
                   <link type='text/css' href='/css/stylesheet.css' rel='stylesheet' media='screen' />
                   <link type='text/css' href='/css/print.css' rel='stylesheet' media='print' />                    
                   
                    <script type='text/javascript' >
                    
                    var links=document.getElementsByTagName('link');        
                    var UserAgent = navigator.userAgent.toLowerCase();
                
                         
                          
                       
                    if (UserAgent.search(/(iphone|ipod|opera mini|fennec|palm|blackberry|android|symbian|series60)/)>-1){
                    
                      
                      
                             links[0].href='http://$cfgUrl/css/screen_pda.css';  
                             links[1].href='http://$cfgUrl/css/stylesheet_pda.css'; 
                             links[2].href='http://$cfgUrl/css/print_pda.css';     
                      
                      
                    }
                    else {  
                          
                              links[0].href='http://$cfgUrl/css/screen.css';  
                              links[1].href='http://$cfgUrl/css/stylesheet.css'; 
                              links[2].href='http://$cfgUrl/css/print.css';  
                                                     
                          
                    }
                     
                   </script>    
                   
                    <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />
                    <meta name='description' lang='de' content='Live Resultate Leichtathletik Meetings'>";      
                       
 
 $cfgHtmlStart2 =  "<meta http-equiv='expires' content='0'>
                    <meta http-equiv='cache-control' content='no-cache'>
                    <meta name='viewport' content='width=320, initial-scale=1.2, maximum-scale=3.0, user-scalable=1' />
                    <title>Live Resultate</title>                   
                    </head><body>                     
                    <div id='container'>
                    <div id='banner'><img id='banner_img' src='img/liveheader.gif' border='0'> 
                    <script type='text/javascript' >                   
                  
                    var UserAgent = navigator.userAgent.toLowerCase();
                
                    if (UserAgent.search(/(iphone|ipod|opera mini|fennec|palm|blackberry|android|symbian|series60)/)>-1){
                           document.getElementById('banner_img').src = 'img/liveheader_mobile.gif';
                    }  
                    else {
                        document.getElementById('banner_img').src = 'img/liveheader.gif';
                    }
                   
                   </script>
                    </div>"; 
                    
 $cfgHtmlStart3 = "<table class='info' ><tr><td id='info1' ><nobr> $strTNlist</nobr> </td><td class='space'></td><td id='info2'>$strHeatsDone </td><td class='space'></td><td id='info3'>$strHeatsInWork</td>
                    <td class='space'></td><td id='info4'>$strResultsRank</td></tr></table> 
                     <script type='text/javascript' >                   
                  
                    var UserAgent = navigator.userAgent.toLowerCase();
                
                    if (UserAgent.search(/(iphone|ipod|opera mini|fennec|palm|blackberry|android|symbian|series60)/)>-1){
                           document.getElementById('info1').innerHTML = '$strTNlist_pda';
                           document.getElementById('info2').innerHTML = '$strHeatsDone_pda';
                           document.getElementById('info3').innerHTML = '$strHeatsInWork_pda';
                           document.getElementById('info4').innerHTML = '$strResultsRank_pda';
                    }  
                    else {
                        document.getElementById('info1').innerHTML = '$strTNlist'; 
                        document.getElementById('info2').innerHTML = '$strHeatsDone'; 
                        document.getElementById('info3').innerHTML = '$strHeatsInWork'; 
                        document.getElementById('info4').innerHTML = '$strResultsRank'; 
                    }
                   
                   </script> 
                    
                    ";  
                                   
 
?>
