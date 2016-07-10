<?php
/**
* class for pagination output
*
* @package Swiss Athletics Events
* @subpackage Event Management
*
* @author mediasprint gmbh, Davide De Santis <ddesantis@mediasprint.ch>
* @copyright Copyright (c) 2010, mediasprint gmbh
*/

// +++ make sure that the file was not loaded directly
if(!defined('CTRL_DIRECT_ACCESS')){
	header('Location: index.php');
	exit();
}
// --- make sure that the file was not loaded directly

class pagination {

	/**
	* an action to call by clicking on a page
	* @var string
	*/
	public $action = 'page.php?page=%PAGE%;';
	/**
	* additional classes for page links
	* @var string
	*/
	public $additional_class_page = '';
	/**
	* number of adjacent pages per side
	* @var integer
	*/
	public $adjacent_pages = 5;
	/**
	* current page
	* @var integer
	*/
	public $current_page = 1;
	/**
	* maximum pages to show
	* @var integer
	*/
	public $max_pages = 10;
	/**
	* total number of pages
	* @var integer
	*/
	public $total_pages = 1;

	private function _parseAction($page){
		return str_replace('%PAGE%', $page, $this->action);
	}

	public function output(){
		if($this->total_pages>1){
			$prev_page = ($this->current_page - 1);
			$next_page = ($this->current_page + 1);
			$penultimate_page = ($this->total_pages - 1);
			?>
			<div class="pagination">
				<?php
				if($this->current_page>1){
					?>
					<a href="<?=$this->_parseAction($prev_page)?>" class="<?=$this->additional_class_page?>">&laquo;</a>
					<?php
				} else {
					?>
					<span class="disabled <?=$this->additional_class_page?>">&laquo;</span>
					<?php
				}

				if($this->total_pages < ($this->max_pages + ($this->adjacent_pages * 2))){
					// enough place for all pages
					for($counter_page=1; $counter_page<=$this->total_pages; $counter_page++){
						if($counter_page==$this->current_page){
							?>
							<span class="current <?=$this->additional_class_page?>"><?=$counter_page?></span>
							<?php
						} else {
							?>
							<a href="<?=$this->_parseAction($counter_page)?>" class="<?=$this->additional_class_page?>"><?=$counter_page?></a>
							<?php
						}
					}
				} elseif($this->total_pages > (($this->max_pages - 2) + ($this->adjacent_pages * 2))){
					// not enough place for all pages, hide some

					if($this->current_page < (1 + ($this->adjacent_pages * 2))){
						// close to beginning, hide later pages only
						for($counter_page=1; $counter_page<=(4 + ($this->adjacent_pages * 2)); $counter_page++){
							if($counter_page==$this->current_page){
								?>
								<span class="current <?=$this->additional_class_page?>"><?=$counter_page?></span>
								<?php
							} else {
								?>
								<a href="<?=$this->_parseAction($counter_page)?>" class="<?=$this->additional_class_page?>"><?=$counter_page?></a>
								<?php
							}
						}
						?>
						...
						<a href="<?=$this->_parseAction($penultimate_page)?>" class="<?=$this->additional_class_page?>"><?=$penultimate_page?></a>
						<a href="<?=$this->_parseAction($this->total_pages)?>" class="<?=$this->additional_class_page?>"><?=$this->total_pages?></a>
						<?php
					} elseif((($this->total_pages - ($this->adjacent_pages * 2)) > $this->current_page) && ($this->current_page > ($this->adjacent_pages * 2))){
						// in the middle, hide some front and some back pages
						?>
						<a href="<?=$this->_parseAction(1)?>" class="<?=$this->additional_class_page?>">1</a>
						<a href="<?=$this->_parseAction(2)?>" class="<?=$this->additional_class_page?>">2</a>
						...
						<?php
						for($counter_page=($this->current_page - $this->adjacent_pages); $counter_page<=($this->current_page + $this->adjacent_pages); $counter_page++){
							if($counter_page==$this->current_page){
								?>
								<span class="current <?=$this->additional_class_page?>"><?=$counter_page?></span>
								<?php
							} else {
								?>
								<a href="<?=$this->_parseAction($counter_page)?>" class="<?=$this->additional_class_page?>"><?=$counter_page?></a>
								<?php
							}
						}
					} else {
						// in the middle, hide some front and some back pages
						?>
						<a href="<?=$this->_parseAction(1)?>" class="<?=$this->additional_class_page?>">1</a>
						<a href="<?=$this->_parseAction(2)?>" class="<?=$this->additional_class_page?>">2</a>
						...
						<?php
						for($counter_page=($this->total_pages - (2 + ($this->adjacent_pages * 2))); $counter_page<=$this->total_pages; $counter_page++){
							if($counter_page==$this->current_page){
								?>
								<span class="current <?=$this->additional_class_page?>"><?=$counter_page?></span>
								<?php
							} else {
								?>
								<a href="<?=$this->_parseAction($counter_page)?>" class="<?=$this->additional_class_page?>"><?=$counter_page?></a>
								<?php
							}
						}
					}
				}

				if($this->current_page<$this->total_pages){
					?>
					<a href="<?=$this->_parseAction($next_page)?>" class="<?=$this->additional_class_page?>">&raquo;</a>
					<?php
				} else {
					?>
					<span class="disabled <?=$this->additional_class_page?>">&raquo;</span>
					<?php
				}
				?>
			</div>
			<?php
		}
	}

}
?>