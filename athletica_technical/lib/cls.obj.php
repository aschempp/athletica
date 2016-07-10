<?php
/**
* provides generic functions for the management of objects
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

class obj {
	
	/**
	* @var array an array containing the filters
	*/
	protected $filter = array();
	
	/**
	* @var array an array containing the filters using LIKE instead of =
	*/
	protected $filter_like = array();
	
	/**
	* @var array an array containing static filters
	*/
	protected $filter_special = array();
	
	/**
	* @var array an array containing the order and order directions
	*/
	protected $order = array();
	
	/**
	* @var array defines the order for each sort field
	*/
	protected $order_setting = array();
	
	/**
	* @var integer defines with which row the result begins
	*/
	protected $limit_start = 0;
	
	/**
	* @var integer defines how much rows are returned
	*/
	protected $limit_end = 0;
	
	/**
	* @var boolean defines if only a single row is returned if only one row was found. Otherwise returns NULL
	*/
	protected $get_single_row = false;
	
	/**
	* @var boolean defines if only the number of total rows is returned
	*/
	protected $get_total = false;
	
	/**
	* sets the filter
	* 
	* @param array $filter filtering fields and values
	* @param string $type filter to be set
	* @return NULL
	*/
	function _set_filter($filter = NULL, $type = ''){
		if($type=='like'){
			$this->filter_like = array();
		} else {
			$this->filter = array();
		}
		
		if(is_array($filter)){
			foreach($filter as $field => $value){
				if(is_null($value) || ($value!='' && $value!='0')){
					if($type=='like'){
						$this->filter_like[$field] = $value;
					} else {
						$this->filter[$field] = $value;
					}
				}
			}
		}
		
		return;
	}
	
	/**
	* sets special filters
	* 
	* @param array $filter filtering fields and values
	* @return NULL
	*/
	function _set_filter_special($filter = NULL){
		$this->filter_special = array();
		
		if(is_array($filter)){
			$this->filter_special = $filter;
		}
		
		return;
	}
	
	/**
	* sets the ordering
	* 
	* @param array $order ordering fields and directions
	* @return NULL
	*/
	function _set_order($order = NULL){
		global $glb_lang;
		
		$this->order = array();
		
		if(is_array($order) && isset($order[0], $order[1])){
			$search = array();
			$replace = array();
			
			$order[0] = str_replace($search, $replace, $order[0]);
			
			$this->order[] = array(
				$order[0], 
				$order[1], 
			);
			
			if(isset($this->order_setting[$order[0]])){
				foreach($this->order_setting[$order[0]] as $setting){
					$this->order[] = $setting;
				}
			}
		}
		
		return;
	}
	
	/**
	* sets the limits
	* 
	* @param integer $limit_start defines with which row the result begins
	* @param integer $limit_end defines how much rows are returned
	* @return NULL
	*/
	function _set_limit($limit_start, $limit_end = 0){
		if(intval($limit_start)>=0){
			$this->limit_start = intval($limit_start);
		}
		
		if(intval($limit_end)>=0){
			$this->limit_end = intval($limit_end);
		}
		
		return;
	}
	
	/**
	* sets the single_row parameter
	* 
	* @param boolean $single_row defines if only a single row is returned
	* @return NULL
	*/
	function _set_single_row($single_row){
		if(is_bool($single_row)){
			$this->get_single_row = $single_row;
			
			// set get_total to false if single_row is true
			if($single_row){
				$this->get_total = false;
			}
		}
		
		return;
	}
	
	/**
	* sets the get_total parameter
	* 
	* @param boolean $get_total defines if only the number of total rows is returned
	* @return NULL
	*/
	function _set_total($get_total){
		if(is_bool($get_total)){
			$this->get_total = $get_total;
			
			// set single_row to false if get_total is true
			if($get_total){
				$this->get_single_row = false;
			}
		}
		
		return;
	}
	
	/**
	* generates the filter string
	* 
	*/
	protected function _get_filter(){
		$return = "";
		
		// +++ check filter
		foreach($this->filter as $field => $value){
			$param = str_replace('.', '_', $field);
			
			$return .= "AND ".$field." ".((is_null($value)) ? "IS NULL " : "= :".$param." ");
		}
		// --- check filter
		
		// +++ check filter LIKE
		foreach($this->filter_like as $field => $value){
			$param = str_replace('.', '_', $field);
			
			$return .= "AND ".$field." LIKE :".$param." ";
		}
		// --- check filter LIKE
		
		// +++ check special filter
		foreach($this->filter_special as $filter_element){
			$return .= "AND ".$filter_element['field']." ";
		}
		// --- check filter
		
		return $return;
	}
	
	/**
	* generates the order string
	* 
	*/
	protected function _get_order(){
		$return = "";
		
		foreach($this->order as $order){
			$field = (isset($order[0])) ? $order[0] : '';
			$direction = (isset($order[1]) && $order[1]=='ASC' || $order[1]=='DESC') ? $order[1] : 'ASC';
			
			if($field!=''){
				$return .= (($return=='') ? "ORDER BY " : ", ").$field." ".$direction;
			}
		}
		
		return $return;
	}
	
	/**
	* generates the limit string
	* 
	*/
	protected function _get_limit(){
		$return = "";
		
		if($this->limit_start>0 || $this->limit_end>0){
			$return = "LIMIT ".$this->limit_start.(($this->limit_end>0) ? ",".$this->limit_end : "");
		}
		
		return $return;
	}
	
	/**
	* gets an array holding the order-information
	* 
	* @param string $order_by the field to be ordered
	* @param string $order_dir the direction to be ordered
	* @param array $whl_by whitelist of allowed order fields
	* @return array order-array containing the field and the direction
	*/
	static function get_order($order_by, $order_dir = 'ASC', $whl_by = NULL){
		$whl_dir = array('ASC', 'DESC');
		
		$return = array(
			(isset($_GET['order_by'])) ? $_GET['order_by'] : ((isset($_SESSION[CFG_SESSION]['order'][CURRENT_CATEGORY.'_'.CURRENT_PAGE][1])) ? $_SESSION[CFG_SESSION]['order'][CURRENT_CATEGORY.'_'.CURRENT_PAGE][0] : $order_by),
			(isset($_GET['order_dir'])) ? $_GET['order_dir'] : ((isset($_SESSION[CFG_SESSION]['order'][CURRENT_CATEGORY.'_'.CURRENT_PAGE][0])) ? $_SESSION[CFG_SESSION]['order'][CURRENT_CATEGORY.'_'.CURRENT_PAGE][1] : $order_dir),
		);
		
		$return[0] = (in_array($return[0], $whl_by)) ? $return[0] : $order_by;
		$return[1] = (in_array($return[1], $whl_dir)) ? $return[1] : 'ASC';
		
		$_SESSION[CFG_SESSION]['order'][CURRENT_CATEGORY.'_'.CURRENT_PAGE] = $return;
		
		return $return;
	}

	/**
	* gets an array holding the filter-information
	* 
	* @param string $frm_action the frm_action value to listen to
	* @param array $default_values the filter's default fields/values
	* @param string $location the page to load if a filter was set
	* @return array filter-array
	*/
	static function get_filter($frm_action, $default_values, $location){
		$return = array(
			'default' => array(),
			'like' => array(),
			'all' => array(),
		);
		
		if(isset($_POST['frm_action']) && $_POST['frm_action']==$frm_action){
			unset($_POST['frm_action']);
			unset($_POST['btn_filter']);
			
			foreach($_POST as $key => $value){
				$key_new = str_replace('::', '.', $key);
				
				unset($_POST[$key]);
				$_POST[$key_new] = $value;
			}
			
			$add_filter = false;
			
			foreach($default_values as $key => $value){
				if((isset($_POST[$key]) && $_POST[$key]!=$value) || (isset($_POST['L_'.$key]) && $_POST['L_'.$key]!=$value)){
					$add_filter = true;
					break;
				}
			}
			
			if($add_filter){
				$filter_corrected = array(
					'default' => array(),
					'like' => array(),
					'all' => array(),
				);
				
				foreach($_POST as $key => $value){
					$index = (substr($key, 0, 2)=='L_') ? 'like' : 'default';
					$key = str_ireplace('L_', '', $key);
					
					$filter_corrected[$index][$key] = $value;
					$filter_corrected['all'][$key] = $value;
				}
				
				$_SESSION[CFG_SESSION]['filter'][CURRENT_CATEGORY.'_'.CURRENT_PAGE] = $filter_corrected;
			} else {
				obj::reset_filter('', true);
			}
			
			// +++ delete page numbers if set
			if(isset($_SESSION[CFG_SESSION]['limit'][CURRENT_CATEGORY.'_'.CURRENT_PAGE])){
				unset($_SESSION[CFG_SESSION]['limit'][CURRENT_CATEGORY.'_'.CURRENT_PAGE]);
			}
			// --- delete page numbers if set
			
			location($location);
		}

		if(isset($_SESSION[CFG_SESSION]['filter'][CURRENT_CATEGORY.'_'.CURRENT_PAGE])){
			$return = $_SESSION[CFG_SESSION]['filter'][CURRENT_CATEGORY.'_'.CURRENT_PAGE];
		}
		
		return $return;
	}

	/**
	* resets an existing filter
	* 
	* @param string $location the page to load, empty if no forwarding should be done
	* @param boolean $force force a reset even if no GET-Variable was set
	* @return NULL
	*/
	static function reset_filter($location = '', $force = false){
		$forward = false;
		
		if(isset($_GET['reset_filter']) || $force){
			if(isset($_SESSION[CFG_SESSION]['filter'][CURRENT_CATEGORY.'_'.CURRENT_PAGE])){
				unset($_SESSION[CFG_SESSION]['filter'][CURRENT_CATEGORY.'_'.CURRENT_PAGE]);
			}
			
			// +++ delete page numbers if set
			if(isset($_SESSION[CFG_SESSION]['limit'][CURRENT_CATEGORY.'_'.CURRENT_PAGE])){
				unset($_SESSION[CFG_SESSION]['limit'][CURRENT_CATEGORY.'_'.CURRENT_PAGE]);
			}
			// --- delete page numbers if set
			
			$forward = true;
		}
		
		if($forward && $location!=''){
			location($location);
		}
		
		return;
	}
}
?>
