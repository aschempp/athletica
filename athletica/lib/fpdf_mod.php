<?php
require("./lib/fpdf/fpdf.php");
class fpdf_mod extends FPDF 
{
	var $Print_on_open;
	
	/**
	 * _print_open
	 * ---------
	 * Lets the print-dialog appear on opening the document
	 */
	function _print_open()
	{
		$this->_out('/Type/Action');
		$this->_out('/S/Named');
		$this->_out('/N/Print');
		$this->_out('>>');
		$this->_out('endobj');
		$this->_newobj();
		$this->_out('<<');
		return ($this->n-1);
	}
	
	/**
	 * _putcatalog
	 * ---------
	 * same as in fpdf, but with print_open extension
	 */
	function _putcatalog()
	{
		if ($this->Print_on_open)
		{
			$obj_nr = $this->_print_open();
		} 
		$this->_out('/Type /Catalog');
		$this->_out('/Pages 1 0 R');
		if($this->ZoomMode=='fullpage')
			$this->_out('/OpenAction [3 0 R /Fit]');
		elseif($this->ZoomMode=='fullwidth')
			$this->_out('/OpenAction [3 0 R /FitH null]');
		elseif($this->ZoomMode=='real')
			$this->_out('/OpenAction [3 0 R /XYZ null null 1]');
		elseif(!is_string($this->ZoomMode))
			$this->_out('/OpenAction [3 0 R /XYZ null null '.sprintf('%.2F',$this->ZoomMode/100).']');
		if($this->LayoutMode=='single')
			$this->_out('/PageLayout /SinglePage');
		elseif($this->LayoutMode=='continuous')
			$this->_out('/PageLayout /OneColumn');
		elseif($this->LayoutMode=='two')
			$this->_out('/PageLayout /TwoColumnLeft');
		if ($this->Print_on_open) {
			$this->_out('/OpenAction '.$obj_nr.' 0 R');	
			$this->Print_on_open=False;
		}
	}
	/*
	* fit_textline
	* ------------
	* show text in a box, shrink txt when necessary to fit in the box
	* x,y: lower left corner of box, maesured from lower left corner of paper
	* pos_hor: horizintal position in box, {left, right, center}
	* pos_ver: vertical position in box, {top, bottom, center}
	*/
	function fit_textline($txt, $x, $y, $w, $h, $pos_hor, $pos_ver)
	{
		$shrinklimit = 0.7; //shrinklimit=1 means no shrink
		$shrink = 1;
		// Output a cell
		$k = $this->k;
		if($txt!=='')
		{ 
		//reference-point is the lower left corner
			while (True) { //if Text is too long: shrink horizontal first to max of $shrinklimit, then shrink fontSize
				$len=$this->GetStringWidth($txt);
				if ($len*$shrinklimit>$w) {
					$this->SetFont($this->FontFamily, $this->FontStyle, $this->FontSizePt-1);
				} elseif ($len>$w){
					$shrink=$w/$len;
					break;
				} else {
					break;
				}
			}
			
			if ($pos_hor=="right") {
				$x+=$w-$len*$shrink;
			} 
			elseif ($pos_hor=="center") {
				$x+=($w-$len*$shrink)/2;
			}
			if ($pos_ver=="top") {
				$y+=$h-$this->FontSizePt;
			}
			elseif ($pos_ver=="center") {
				$y+=($h-$this->FontSizePt)/2;
			}

			$txt2 = str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));//these symbols must be changed for pdf
			$s .= sprintf('BT %.2F %.2F Td %s Tz (%s) Tj 100 Tz ET',$x*$k,$y*$k,$shrink*100,$txt2); 
		}
		$this->SetXY($x,$y);
		if($s)
			$this->_out($s);
		$this->lasth = $h;

	}
	
	function Line($x, $y, $x2, $y2) {
		parent::Line($x, $this->h-$y, $x2, $this->h-$y2);
	}
}

?>