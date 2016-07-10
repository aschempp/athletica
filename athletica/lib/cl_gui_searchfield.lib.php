<?php

if (!defined('AA_CL_GUI_SEARCHFIELD_LIB_INCLUDED'))
{
	define('AA_CL_GUI_SEARCHFIELD_LIB_INCLUDED', 1);


/********************************************
 *
 * CLASS GUI_Searchfield
 *
 * Prints an athlete search field.
 *
 *******************************************/

class GUI_Searchfield
{
	var $action;
	var $target;
	var $method;
	var $back;
	var $hide;

	/*
	 *	Constructor
	 *	-----------
	 * To create a searchfield, provide the onChange-action, its form name
	 * the target window and the http-method.
	 *		action:	link href-value
	 *		target:	the target window (if not provided, the same window will
	 *					be used; otherwise say e.g. '_blank')
	 *		method:	POST (default) or GET
	 *		back:		if set, print a button with a link back
	 */

	function GUI_Searchfield($action, $target='_self', $method='post', $back='', $hide = false)
	{
		$this->set($action, $target, $method, $back, $hide);
	}

	/*
	 *	set()
	 *	-----------
	 * set new properties
	 */

	function set($action, $target='_self', $method='post', $back='', $hide = false)
	{
		$this->action = $action;
		$this->target = $target;
		$this->method = $method;
		$this->back = $back;
		$this->hide = $hide;
	}

	/*
	 *	printSearchfield()
	 *	------------------
	 * Finally, print the new button
	 */

	function printSearchfield()
	{
		?>
		<script type="text/javascript">
			function doSuche(hide){
				var obj = document.lookup;
				
				obj.submit();
				
				if(hide){
					obj.searchfield.value = '';
				}
			}
		</script>
<table>
	<tr>
<form action='<?php echo $this->action; ?>' method='<?php echo $this->method; ?>' target='<?php echo $this->target; ?>' name='lookup'>
		<th class='dialog'><?php echo $GLOBALS['strSearch']; ?></th>
		<td class='forms'>
			<input name='arg' type='hidden' value='search' />
			<input name='back' type='hidden' value='<?php echo $this->back; ?>' />
			<input class='text' name='searchfield' type='text' maxlength='25' value=''
				onChange='doSuche(<?php echo (($this->hide) ? 'true' : 'false'); ?>);' />
		</td>
</form>
	</tr>
</table>
<?php
	}

} // END CLASS Gui_Searchfield

} // end AA_CL_GUI_SEARCHFIELD_LIB_INCLUDED
?>
