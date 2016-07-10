<?php
/**
* class: provides functions for the management of languages
* outside: checks the currently selected language and loads the right files
*
* @package Athletica Technical Client
* @subpackage Classes
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

class language {

	/**
	* holds all strings for the current language
	* @var array
	*/
	public $lg = array();

	/**
	* holds all languages
	* @var array
	*/
	public $lg_languages = array();

	/**
	* holds all strings for all translations
	* @var array
	*/
	public $lg_translation = array();

	/**
	* stores the information about complete and incomplete translations
	* @var array
	*/
	public $lg_translation_complete = array();

	/**
	* language whitelist
	* @var array()
	*/
	public $whl_language = array();

	/**
	* class constructor, gets languages and translation strings
	*
	* @return NULL
	*/
	function __construct(){
		global $glb_connection;

		if(!is_null($glb_connection)){
			try {
				$sql_lang = "SELECT *
							   FROM t_language
						   ORDER BY language_name ASC;";
				$query_lang = $glb_connection->query($sql_lang);

				$languages = $query_lang->fetchAll(PDO::FETCH_ASSOC);
				foreach($languages as $language){
					$this->lg_languages[$language['language_code']] = $language;
				}

				// +++ prepare strings query
				$sql_string = "SELECT *
								 FROM t_translation
								WHERE language_code = :language_code
							 ORDER BY translation_key ASC;";
				$query_string = $glb_connection->prepare($sql_string);
				// --- prepare strings query

				foreach($this->lg_languages as $language){
					// +++ update whitelist
					if(!in_array($language['language_code'], $this->whl_language)){
						$this->whl_language[] = $language['language_code'];
					}
					// --- update whitelist

					// +++ bind parameters
					$query_string->bindValue(':language_code', $language['language_code']);
					// --- bind parameters

					$query_string->execute();

					$translations = $query_string->fetchAll(PDO::FETCH_ASSOC);
					foreach($translations as $translation){
						$this->lg_translation[$language['language_code']][$translation['translation_key']] = $translation['translation_text'];
					}
				}

				// +++ check if translations are complete
				foreach($this->lg_languages as $language_code => $language){
					$this->lg_translation_complete[$language_code] = TRUE;

					foreach($this->lg_translation[CFG_DEFAULT_LANGUAGE] as $translation_key_original => $translation_value_original){
						if(!isset($this->lg_translation[$language_code][$translation_key_original]) || $this->lg_translation[$language_code][$translation_key_original]==''){
							$this->lg_translation_complete[$language_code] = FALSE;
							break;
						}
					}
				}
				// --- check if translations are complete

				// +++ get current language
				$current_language = CFG_DEFAULT_LANGUAGE;

				if(isset($_GET['lang']) && ctype_alpha($_GET['lang']) && in_array($_GET['lang'], $this->whl_language)){
					$current_language = $_GET['lang'];
				} elseif(isset($_SESSION[CFG_SESSION]['lang']) && ctype_alpha($_SESSION[CFG_SESSION]['lang']) && in_array($_SESSION[CFG_SESSION]['lang'], $this->whl_language)){
					$current_language = $_SESSION[CFG_SESSION]['lang'];
				} elseif(isset($_COOKIE[CFG_COOKIE])){
					$cookie = unserialize($_COOKIE[CFG_COOKIE]);

					if(isset($cookie['lang']) && ctype_alpha($cookie['lang']) && in_array($cookie['lang'], $this->whl_language)){
						$current_language = $cookie['lang'];
					}
				}

				define('CFG_CURRENT_LANGUAGE', $current_language);
				// --- get current language

				// +++ set strings for the current language, replace empty strings with the default language
				if(isset($this->lg_translation[CFG_DEFAULT_LANGUAGE])){
					foreach($this->lg_translation[CFG_DEFAULT_LANGUAGE] as $translation_key => $translation_text){
						$this->lg[$translation_key] = (CFG_CURRENT_LANGUAGE!=CFG_DEFAULT_LANGUAGE && isset($this->lg_translation[CFG_CURRENT_LANGUAGE][$translation_key]) && $this->lg_translation[CFG_CURRENT_LANGUAGE][$translation_key]!='') ? $this->lg_translation[CFG_CURRENT_LANGUAGE][$translation_key] : $translation_text;
					}
				}
				// --- set strings for the current language, replace empty strings with the default language
			} catch(PDOException $e){
				trigger_error($e->getMessage(), E_USER_ERROR);
			}
		}

	}

	/**
	* adds a translation
	*
	* @param array $data the language's information
	* @return string returns ok if the translation-file was created, otherwise the error message
	*/
	static function add($data){
		global $glb_connection;

		$return = 'error';

		try {
			$sql_add = "INSERT INTO t_language
								SET language_code = :language_code,
									language_name = :language_name;";
			$query_add = $glb_connection->prepare($sql_add);

			// +++ bind parameters
			$query_add->bindValue(':language_code', $data['language_code']);
			$query_add->bindValue(':language_name', $data['language_name']);
			// --- bind parameters

			$query_add->execute();
			$language_id = $glb_connection->lastInsertId();

			$return = 'ok';
		} catch(PDOException $e){
			if(preg_match('/Duplicate entry (.*?) for key \'PRIMARY\'/i', $e->getMessage())){
				$return = 'language_code';
			}

			if($return=='error'){
				trigger_error($e->getMessage());
			}
		}

		return $return;
	}

	/**
	* modifies a translation
	*
	* @param array $data the translation's information
	* @return string returns ok if the translation was modified, otherwise the error message
	*/
	static function edit($data){
		global $glb_connection;
		global $lg_translation;

		$return = 'error';

		try {
			$glb_connection->beginTransaction();

			// +++ edit language
			$sql_edit = "UPDATE t_language
							SET language_name = :language_name
						  WHERE language_code = :language_code;";
			$query_edit = $glb_connection->prepare($sql_edit);

			// +++ bind parameters
			$query_edit->bindValue(':language_name', $data['language_name']);
			$query_edit->bindValue(':language_code', $data['language_code']);
			// --- bind parameters

			$query_edit->execute();
			// --- edit language

			// +++ delete translation strings
			$sql_delete = "DELETE FROM t_translation
								 WHERE language_code = :language_code;";
			$query_delete = $glb_connection->prepare($sql_delete);

			// +++ bind parameters
			$query_delete->bindValue(':language_code', $data['language_code']);
			// --- bind parameters

			$query_delete->execute();
			// --- delete translation strings

			// +++ add translation strings
			$sql_add = "INSERT INTO t_translation
								SET translation_key = :translation_key,
									language_code = :language_code,
									translation_text = :translation_text;";
			$query_add = $glb_connection->prepare($sql_add);

			foreach($data['translation'] as $translation_key => $translation_text){
				if(isset($lg_translation[CFG_DEFAULT_LANGUAGE][$translation_key]) && trim($translation_text)!=''){
					// +++ bind parameters
					$query_add->bindValue(':translation_key', $translation_key);
					$query_add->bindValue(':language_code', $data['language_code']);
					$query_add->bindValue(':translation_text', trim($translation_text));
					// --- bind parameters

					$query_add->execute();
				}
			}
			// --- add translation strings

			$glb_connection->commit();
			$return = 'ok';
		} catch(PDOException $e){
			$glb_connection->rollBack();

			trigger_error($e->getMessage());
		}

		return $return;
	}

	/**
	* deletes a translation
	*
	* @param integer $language_code the language to be deleted
	* @return string returns ok if the translation was deleted, otherwise the error message
	*/
	static function delete($language_code){
		global $glb_connection;

		$return = 'error';

		try {
			$sql_delete = "DELETE FROM t_language
								 WHERE language_code = :language_code;";
			$query_delete = $glb_connection->prepare($sql_delete);

			// +++ bind parameters
			$query_delete->bindValue(':language_code', $language_code);
			// --- bind parameters

			$query_delete->execute();

			$return = 'ok';
		} catch(PDOException $e){
			trigger_error($e->getMessage());
		}

		return $return;
	}

}

// instantiate the language class
$cls_language = new language();
$lg = $cls_language->lg;
$lg_languages = $cls_language->lg_languages;
$lg_translation = $cls_language->lg_translation;
$lg_translation_complete = $cls_language->lg_translation_complete;
$whl_language = $cls_language->whl_language;

// +++ set session and cookie
if(!defined('CFG_CURRENT_LANGUAGE')){
	define('CFG_CURRENT_LANGUAGE', CFG_DEFAULT_LANGUAGE);
}

$_SESSION[CFG_SESSION]['lang'] = CFG_CURRENT_LANGUAGE;
set_cookie('lang', CFG_CURRENT_LANGUAGE);
// --- set session and cookie
?>