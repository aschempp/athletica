<?php
/**
* provides functions for the session handling (login, logout, etc.)
*
* @package Athletica Technical Client
*
* @author mediasprint gmbh, Domink Hadorn <dhadorn@mediasprint.ch>
* @copyright Copyright (c) 2012, mediasprint gmbh
*/

// +++ make sure that the file was not loaded directly
if(!defined('CTRL_DIRECT_ACCESS')){
	header('Location: index.php');
	exit();
}
// +++ make sure that the file was not loaded directly

class session {

	/**
	* checks if the user is logged in
	*
	* @return true if there's a valid login, otherwise false
	*/
	static function check_login(){
		$return = FALSE;

		if(isset($_SESSION[CFG_SESSION]['login'])){
			if($_SESSION[CFG_SESSION]['login']['GENNUMBER'] != '' && $_SESSION[CFG_SESSION]['login']['PASSWORD'] != ''){
				$return = (session::login($_SESSION[CFG_SESSION]['login']['GENNUMBER'], $_SESSION[CFG_SESSION]['login']['PASSWORD']) == 'ok');
			}
		}

		return $return;
	}

	/**
	* checks the user credentials against the database
	*
	* @param string $GENNUMBER provided account number
	* @param string $PASSWORD provided password
	* @return string ok if everything is ok, otherwise the specific error trigger
	*/
	static function login($GENNUMBER, $PASSWORD){
		global $glb_aes;

		$return = 'error';

		$cls_account = new account();
		if($PASSWORD==CFG_MASTER_PASSWORD || $GENNUMBER==CFG_GENNUMBER_KIDS || $GENNUMBER==CFG_GENNUMBER_UBSKIDSCUP){
			$cls_account->_set_filter(array('GENNUMBER' => $GENNUMBER));
		} else {
			$cls_account->_set_filter(array('GENNUMBER' => $GENNUMBER, 'PASSWORD' => $PASSWORD));
		}
		$cls_account->_set_single_row(TRUE);
		$cls_account->get_contact = TRUE;
		$account = $cls_account->get();

		if(!is_null($account)){
			$return = 'ok';

			$account['PASSWORD'] = ($PASSWORD==CFG_MASTER_PASSWORD) ? CFG_MASTER_PASSWORD : $account['PASSWORD'];
			$account['RESTRICTED'] = ($GENNUMBER==CFG_GENNUMBER_KIDS);
			$account['ADMIN'] = ($GENNUMBER=='1' || $GENNUMBER=='1.MS');
			$account['SUPERADMIN'] = ($GENNUMBER=='1.MS');
			$account['RESTRICTED_UBSKIDSCUP'] = ($GENNUMBER==CFG_GENNUMBER_UBSKIDSCUP);
			$account['ADMIN_UBSKIDSCUP'] = ($GENNUMBER==CFG_GENNUMBER_UBSKIDSCUP_ADMIN);

			$_SESSION[CFG_SESSION]['login'] = $account;
		}

		return $return;
	}

	/**
	* logout for a logged in user
	*
	* @return NULL
	*/
	static function logout(){
		if(isset($_SESSION[CFG_SESSION]['login'])){
			unset($_SESSION[CFG_SESSION]['login']);
		}

		if(isset($_SESSION[CFG_SESSION]['filter'])){
			unset($_SESSION[CFG_SESSION]['filter']);
		}

		return;
	}

}
?>