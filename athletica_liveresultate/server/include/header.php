<?php
    header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head> 
        <title>Live-Resultate</title> 
        
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <meta name="description" content="Live-Resultate">
        <meta content="text/html;charset=iso-8859-1" http-equiv="content-type">
        <meta http-equiv="content-type" content="text/html; charset=utf-8" >
        
        
        
        <script src="//code.jquery.com/jquery-1.11.0.js"></script>
        <script type="text/javascript">
            $(document).bind("mobileinit", function () {
                $.mobile.ajaxEnabled = false;    
            });
        </script>
        
        <script src="js/jqm_defaults.js"></script>
        <script src="//code.jquery.com/mobile/1.4.2/jquery.mobile-1.4.2.js"></script>
        <script src="js/stupidtable.js"></script>
        <script src="config/config.js" type="application/x-javascript" charset="iso-8859-1"></script>
        
        <link rel="shortcut icon" type="image/x-icon" href="img/favicon.gif">
        <link rel="apple-touch-icon-precomposed" href="img/favicon.gif">
        
        <link rel="stylesheet" href="//code.jquery.com/mobile/1.4.2/jquery.mobile-1.4.2.css">
        
        <script type="text/javascript">                            
            function refreshPage() {
                $.mobile.changePage(
                    window.location.href,
                    {
                    allowSamePageTransition : true,
                    transition              : 'fade',
                    showLoadMsg             : true,
                    reloadPage              : true
                    }
                );
            }
            
            var timeout = clearInterval(timer_refresh);
            var timer_refresh  = window.setInterval("refreshPage()", cfg_reload_time); 
            
             
        </script>
            <!--
        <script type="text/javascript">
$(document).ready(function(){
    $('#header_img').attr('src', 'img/header.gif');    
});

</script>
-->
        
        <style type="text/css">
            @import "config/style.css";
            @import "css/table-timetable.css";
            @import "css/table-results.css";
            @import "css/table-results-details.css";
            @import "css/table-results-combined.css";
            @import "css/table-startlist.css";
            @import "css/table-athletes.css";
            @import "css/style.css";
        </style>
    </head> 
    <body>