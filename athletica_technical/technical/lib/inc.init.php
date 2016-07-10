<?php
/**
* initializes common settings
*
* @package Athletica Technical Client
* @subpackage Global
* @category Init
*
* @author mediasprint gmbh, Domink Hadorn <dhadorn@mediasprint.ch>
* @copyright Copyright (c) 2012, mediasprint gmbh
*/

// +++ include global init file
require_once(GLOBAL_PATH.'lib/inc.init.php');
// --- include global init file

// +++ include all needed classes
require_once(ROOT_PATH.'lib/cls.session.php');
require_once(ROOT_PATH.'lib/cls.pagination.php');
// --- include all needed classes

// +++ needed global variables and constants
define('LOGGED_IN', session::check_login());
$glb_login = (LOGGED_IN && isset($_SESSION[CFG_SESSION]['login'])) ? $_SESSION[CFG_SESSION]['login'] : NULL;
// --- needed global variables and constants

// forward the user to the PHP configuration page if a fatal error occurred
if(!$cls_php_ini->valid){
	if(CURRENT_CATEGORY!='admin' || CURRENT_PAGE!='php'){
		location(ROOT_PATH.'content/admin/php.php');
	}
} else {
	if(CURRENT_CATEGORY!='error'){
		// forward the user to db.php if the database connection did not work
		if(is_null($glb_connection) && CURRENT_PAGE!='db'){
			location(ROOT_PATH.'db.php');
		}
        
        // forward the user to settings.php if the server database connection did not work
        if(CFG_CURRENT_MODE=='live' && is_null($glb_connection_server) && CURRENT_PAGE!='server' && CURRENT_PAGE!='mode' && CURRENT_PAGE!='db'){
            location(ROOT_PATH.'content/settings/server.php');
        }

		// forward the user to index.php if the database connection is successfull and he's on db.php
		if(!is_null($glb_connection) && !is_null($glb_connection_server) && CURRENT_CATEGORY=='start' && CURRENT_PAGE=='db'){
			location(ROOT_PATH.'index.php');
		}

		// forward the user to index.php if he is on a page he is not allowed to see
		if(CURRENT_CATEGORY!='nologin'){
			if(!is_null($glb_connection)){
				if(
					(
						LOGGED_IN &&
						(
							(
								(
									CURRENT_CATEGORY=='admin'
								) &&
								!$glb_login['ADMIN'] &&
								!$glb_login['SUPERADMIN']
							) ||
							(
								CURRENT_CATEGORY=='admin'
								&&
								(
									CURRENT_PAGE=='log_error' ||
									CURRENT_PAGE=='php'
								)
								&& !$glb_login['SUPERADMIN']
							)
						)
					) ||
					(
						!LOGGED_IN &&
						(
							CURRENT_CATEGORY!='start'
                            &&
                            CURRENT_CATEGORY!='cronjob'
							&&
							(
								CURRENT_CATEGORY!='ubskidscup'
								||
								CURRENT_PAGE!='admin'
							)
							
						)
					)
				){
					if(CURRENT_CATEGORY!='ajax'){
						//location(ROOT_PATH.'index.php');
					} else {
						echo 'not logged in!';
					}
				}
			}
		}
	}
}

// +++ extend max execution time for AJAX scripts
if(CURRENT_CATEGORY=='ajax'){
	set_time_limit(3600);
}
// --- extend max execution time for AJAX scripts

$current_event = NULL;
if(isset($_SESSION[CFG_SESSION]['xSerie'])){
        $current_event = $_SESSION[CFG_SESSION]['xSerie'];
    } elseif(isset($_COOKIE[CFG_COOKIE])){
        $cookie = unserialize($_COOKIE[CFG_COOKIE]);

        if(isset($cookie['xSerie'])){
            $current_event = $cookie['xSerie'];
        }
    }
define('CFG_CURRENT_EVENT', $current_event);

$current_meeting = NULL;
if(isset($_SESSION[CFG_SESSION]['xMeeting'])){
        $current_meeting = $_SESSION[CFG_SESSION]['xMeeting'];
    } elseif(isset($_COOKIE[CFG_COOKIE])){
        $cookie = unserialize($_COOKIE[CFG_COOKIE]);

        if(isset($cookie['xMeeting'])){
            $current_meeting = $cookie['xMeeting'];
        }
    }
define('CFG_CURRENT_MEETING', $current_meeting);
?>