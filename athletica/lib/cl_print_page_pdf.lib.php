<?php

if (!defined('AA_CL_PRINT_PAGE_PDF_LIB_INCLUDED'))
{           
  define('AA_CL_PRINT_PAGE_PDF_LIB_INCLUDED' ,1);


/********************************************
	Important information about the pdf functions:
	- all the files base on the original html-versions
	- FPDF is used as base-class for the pdf-stuff
	- the non-pdf functions seem to be out of use, but are not deleted for error searching reason
	- distance between two lines should be fontheight + 2
	- most spaces (between columns, lines...) are hardcoded
	- unit is points
	- for available fonts see fpdf manual
	- the fpdf_mod adds fitting a text into box and auto-print-dialog on open possibility to fpdf
	- if something else than the pdf is sent to the browser (like html) FPDF will send an error
	
	Future Versions:
	- make all sizes, lineheights... accessible by reading them from a config-file
	- make printing the timetable possible
	- by giving up the html-compatibility all the print-contest and print-relay classes 
	  could be replaced by one (possible through header-dependant col-width)
	- print-contest: some classes could look better (especially in non-german languages)
	- to show print-dialog on opening the pdf-file set auto_print in PRINT_Page_pdf
	
********************************************/


/********************************************
 *
 * PRINT_Page_pdf
 *
 *	Base class for printing documents as pdf
 *  unit is point (pt)
 *
 *******************************************/

include("fpdf_mod.php");

class PRINT_Page_pdf                                                       
{
	var $pdf; //for object pdflib
	var $lp; //actual position of printing (beginning at pageheight going back to 0 (cause reference point is left bottom!)
	var $posx; //actual position to print direction-x
	var $pageheight; //height in points
	var $pagewidth; //width in points
	var $font; //font
	// vars for header and footer standard
	var $stdHeaderBig, $stdHeaderRight, $stdHeaderLeft, $stdHeaderCenter;
	var $stdFooterBig, $stdFooterLeft, $stdFooterCenter, $stdFooterRight;
	var $picHeaderBig, $picHeaderLeft, $picHeaderCenter, $picHeaderRight;
	var $picFooterBig, $picFooterLeft, $picFooterCenter, $picFooterRight;
	var $orga; // organiser
    var $landscape;
    var $printHeader_bool;
    var $printFooter_bool;
    var $footerheight;
    var $marginLeft;
    var $marginRight;

	// public functions
	// ----------------

	function PRINT_Page_pdf($title='Defaulttitle', $auto_print=False, $landscape = False, $printHeader_bool = true, $printFooter_bool = true)
	{         
        $this->landscape = $landscape;
        $this->printHeader_bool = $printHeader_bool;
        $this->printFooter_bool = $printFooter_bool; 
		$this->title = $title;
		$this->stylesheet = "printing.css";
        if ($this->landscape) {
            $this->pagewidth = 842;
            $this->pageheight = 595;
            $this->marginLeft = 20;    
            $this->marginRight = 20;    
        } else {
            $this->pagewidth = 597;
            $this->pageheight = 842;
            $this->marginLeft = 20;
            $this->marginRight = 20;
        }
		$this->setHeaderAndFooter();
		$this->startPage($auto_print);   
	}

	/**
	 * startPage
	 * ---------
	 * Sets up the basic pdf-page.
	 */
	function startPage($auto_print=False)
	{
		$this->font="Helvetica";  //only few fonts available, default=Helvetica
		if($this->landscape) {
            $this->pdf = new fpdf_mod("L","pt","A4");    
        } else {
            $this->pdf = new fpdf_mod("P","pt","A4");
        }
        
		$this->pdf->SetAutoPageBreak(False); //we do manual pagebreak
		$this->pdf->Print_on_open = $auto_print; //show print-dialog on open
		$this->lp=$this->pageheight;  //actual position of the line to print

		$this->pdf->SetCreator("athletica with fpdf");//pdf info
		$this->pdf->SetTitle($this->title);
		$this->pdf->SetAuthor("athletica");
		$this->pdf->AddPage();//begin of the page, with width and height of page in coordsystem of document
        $this->printPageHeader(); 
		$this->pdf->AliasNbPages("{pgn}");
	}

	/**
	 * printCover 
	 * ----------
	 * Sets up a cover page with basic meeting data.
	 */

	function printCover($type, $timing=true)
	{
		$sql = "SELECT m.Name, 
					   m.Ort, 
					   m.DatumVon, 
					   m.DatumBis, 
					   m.Nummer, 
					   s.Name As StadionName, 
					   DATE_FORMAT(m.DatumVon, '". $GLOBALS['cfgDBdateFormat'] . "') AS von, 
					   DATE_FORMAT(m.DatumBis, '". $GLOBALS['cfgDBdateFormat'] . "') AS bis, 
					   m.Organisator, 
					   m.Zeitmessung, 
					   z.OMEGA_Sponsor, 
					   z.ALGE_Typ
				  FROM meeting AS m 
			 LEFT JOIN stadion AS s USING(xStadion) 
			 LEFT JOIN zeitmessung AS z ON(m.xMeeting = z.xMeeting) 
				 WHERE m.xMeeting = ".$_COOKIE['meeting_id'].";";
		$result = mysql_query($sql);

		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else {
    
			$row = mysql_fetch_assoc($result);
			$date = $row['von'];
			if($row['DatumVon'] != $row['DatumBis']) {		// more than one day
				$date = $date . " " . $GLOBALS['strDateTo'] . " ". $row['bis'];
			}
      $this->printTextLineBox($row['Name'],$this->font,"B",26,32,700,535,30,'left bottom',0,0); 
      $this->printTextLineBox($type,$this->font,"B",26,32,560,535,30,'left bottom',0,0);
      $this->lp=530;
      
          
      $this->printTextLineBox($GLOBALS['strStadium'],$this->font,"",12,48,$this->lp,136,30,'left bottom',0,0);
      $this->printTextLineBox($row['StadionName'].", ".$row['Ort'],$this->font,"B",12,200,$this->lp,136,30,'left bottom',0,18);
      $this->printTextLineBox($GLOBALS['strOrganizer'],$this->font,"",12,48,$this->lp,136,30,'left bottom',0,0);
      $this->printTextLineBox($row['Organisator'],$this->font,"B",12,200,$this->lp,136,30,'left bottom',0,18);
      $this->printTextLineBox($GLOBALS['strDate'],$this->font,"",12,48,$this->lp,136,30,'left bottom',0,0);
      $this->printTextLineBox($date,$this->font,"B",12,200,$this->lp,136,30,'left bottom',0,18);
      $this->printTextLineBox($GLOBALS['strMeetingNbr'],$this->font,"",12,48,$this->lp,136,30,'left bottom',0,0);
      $this->printTextLineBox($row['Nummer'],$this->font,"B",12,200,$this->lp,136,30,'left bottom',0,18);
      if($timing){
			$str = "";
			if($row['Zeitmessung']=='alge'){
				$str = 'ALGE '.$row['ALGE_Typ'];
			} 
			elseif ($row['Zeitmessung']=='omega'){
				$str = 'OMEGA ('.$row['OMEGA_Sponsor'].')';
			}
            else {
                $str = $GLOBALS['strNoTiming'];   
            }
      $this->printTextLineBox($GLOBALS['strTiming'],$this->font,"",12,48,$this->lp,136,30,'left bottom',0,0);
      $this->printTextLineBox($str,$this->font,"B",12,200,$this->lp,136,30,'left bottom',0,14);
			}
      $this->printTextLineBox($GLOBALS['strSlogan'] . " " . $GLOBALS['cfgApplicationName'] . " " . $GLOBALS['cfgApplicationVersion'], $this->font,"",10,48,$this->lp,300,30,'left bottom',15,14);

			mysql_free_result($result);
			$this->insertPageBreak();
		}
	}
  
  

	/**
	 * endPage
	 * -------
	 * Terminates the basic HTML-page frame.
	 */
	function endPage()
	{ 
        $this->printPageFooter();
		$this->pdf->Output($this->title,"I");
	}
	
  
	/**
	 * insertPageBreak
	 * ---------------
	 * Terminates layout table and inserts a page break (printing).
	 */
	function insertPageBreak()
	{ 
		global $cfgPageContentHeight;           
		$this->printPageFooter();
		$this->pdf->AddPage();
        $this->printPageHeader();       		  		
	}
  
  /**
	 * printDocTitle
	 * -------------
	 * Print the document title.
	 */
	function printPageTitle($title)
	{   
    $this->printTextLineBox($title,$this->font,"B",13,$this->marginLeft,$this->lp,515,30,'left bottom',15,8);
	}


	function printSubTitle($title)
	{
		// page break check (must be enough for different uses)
		if($this->lp < 120)		
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($title,$this->font,"B",11,$this->marginLeft,$this->lp,515,30,'left bottom',26,4);
	}

	function startList() //not needed with pdf, do not delete!
	{   
		return;   
	}



	function endList()  //not needed with pdf, do not delete!
	{
		return;
	}


	function printHeaderLine($text)  //TODO
	{
		if(($this->lpp - $this->linecnt) < 1)		// page break check
		{
			$this->insertPageBreak();

		}
		$this->linecnt++;			// increment line count
?>
		<tr>
			<th><?php echo $text; ?></th>
		</tr>
		<tr>
			<td><hr /></td>
		</tr>
<?php
	}
	
	function printLine($text) //TODO
	{
		if(($this->lpp - $this->linecnt) < 1)		// page break check
		{
			$this->insertPageBreak();

		}
		$this->linecnt++;			// increment line count

		printf("<tr>\n");
		printf("<td>$text</td>");
		printf("</tr>\n");
	}
	
	  /**
	 * delformat
	 * -------------
	 * delete html-formatting in string and set font and set pagenumber
	 */
	function delformat($text,$font,$fontstyle,$fontheight) {
  
		$quantity=0;
		$text=str_replace('&nbsp;',' ',$text); //delete predefined formattings for html
		$text=str_replace('<b>','',$text,$quantity);
		$text=str_replace('</b>','',$text);
		$text=str_replace('{pg}',$this->pdf->PageNo(),$text); //pagenumber
		
		if ($quantity>=1){
		$this->pdf->SetFont($font,"B",$fontheight);
		}else{
		$this->pdf->SetFont($font,$fontstyle,$fontheight);
		}
		return ($text);
	}
	
	/**
	 * printTextLineBox
	 * -------------
	 * print text in a box, set position in box, font and y-pos change before and after box ($lpchangetop/$lpchangebottom)
	 * position="horizontal vertical" where horizontal can be: right/left/center and vertical: top/bottom/center
	 * fontstyle can be "" for normal or "B" for bold
	 * reference point is the lower left corner of the box
	 */
	function printTextLineBox($text,$font,$fontstyle,$fontheight,$x,$y,$width,$height,$position,$lpchangetop,$lpchangebottom) {
		if ($text!=''){
			$arr=explode(" ",$position);
			$pos_hor=$arr[0];
			$pos_ver=$arr[1];
		  $text = $this->delformat($text,$font,$fontstyle,$fontheight);
				$this->pdf->fit_textline($text,$x,$y-$lpchangetop,$width,$height,$pos_hor,$pos_ver);
		}
		$this->lp-=($lpchangebottom + $lpchangetop);
		$this->posx=($x+$width);
	}
	
	//returns two arrays, first with the words, second with the delimiter (wordarray has one item more!)
	function splittext($text) { 
		$text=trim($text);
		$len=strlen($text);
		$arr_text=array();
		$arr_del=array();

		//splits after occurence of the delimiter
		$pos=array();
		$delimiter=array(" ","-"); //append some more if needed
		while (True) {
			foreach ($delimiter as $val) {
				$pos[$val]=strpos($text,$val);
			}
			//replace False by stringlength+1
			foreach ($delimiter as $val) {
				if ($pos[$val]===False) {
					$pos[$val]=$len+1;
				}		
			}
			$min=min($pos);
			if ($min==$len+1) {
				array_push($arr_text,$text);
				break; //stop while
			}
			else{
				$del=array_search($min, $pos);
				$arr=explode($del,$text,2);
				array_push($arr_text,$arr[0]);
				array_push($arr_del,$del);
				$text=$arr[1];
			}
		}
		return array($arr_text, $arr_del);
	}
  
	/*
	* shortnprint - use only through printTextFlowSimp!!
	* function with recursion!!, dont change anything!!
	* -----------
	* make multiline if necessary
	* vertical position is always top, no height necessary (we don't know how many lines)
	* reference-point is upper(!!) left corner
	*/
	function shortnprint($text,$font,$fontstyle,$fontheight,$x,$y,$width,$poshor,$lpchangetop,$lines){
		$text=trim($text); //necessary, because splittext must trim to work and then substr_replace (some lines further down in this function) would not work correctly because of the use of strlen, which is calculated without the space at beginning
		$this->pdf->SetFont($font,$fontstyle, $fontheight);
		$fh=$fontheight+10;
		$arr0=$this->splittext($text);
		$arr=$arr0[0];
		$arr_del=$arr0[1];
		if (count($arr)<2){
			$this->pdf->fit_textline($text,$x,$y-$lpchangetop-(($fontheight+2)*$lines)+2,$width,$fh,$poshor,"bottom");
		}else{ 
			for ($i=(count($arr));$i>=0;$i--){ //$i=how many words stick together 
				$text2='';
				if ($i==0) {//if first word too long print anyway (will be shrunk in fit_textline)
					$text2=$arr[0].$arr_del[0];
				}else{
					for ($a=0; $a<$i; $a++) {
						$text2 .= $arr[$a].$arr_del[$a];
					}
				}
				$text2=rtrim($text2); //space not important at end of the line
				if ($this->pdf->GetStringWidth($text2)<$width or $i==0){ //TODO: perhaps include here the max_shrink-value
					$this->pdf->fit_textline($text2,$x,$y-$lpchangetop-(($fontheight+2)*$lines)+2,$width,$fh,$poshor,"bottom"); //space between two lines=2pt
					$text=substr_replace($text,'',0,strlen($text2));
					$text=ltrim($text); //may be space on the left (not count as len and not print)
					if (strlen($text)!=0){
						$lines++;
						$lines=$this->shortnprint($text,$font,$fontstyle,$fontheight,$x,$y,$width,$poshor,$lpchangetop,$lines);
					}
					break;
				}
			}
		}
		return $lines;
	}
	
    /**
	 * printTextFlowSimp
	 * -------------
	 * Print a simple textflow if necessary
	 * new lines at 'space' and '-', vertical position is automatically made with line distance=2pt
	 * refence-point is upper!! left corner, must be like this casue we don't know yet how many lines
	 * returns the needed Lines
	 */
	function printTextFlowSimp($textin,$font,$fontstyle,$fontheight,$x,$y,$width,$poshor,$lpchangetop,$lpchangebottom, $lines=1){
		$textin=$this->delformat($textin,$font,$fontstyle,$fontheight);
		$lines=max($this->shortnprint($textin,$font,$fontstyle,$fontheight,$x,$y,$width,$poshor,$lpchangetop,1),$lines);
		$this->lp-=($lpchangetop+$lpchangebottom);
		$this->posx=($x+$width);
		return ($lines);
	}
	
	/**
	 * printTextCell
	 * -------------
	 * Print Cell with (optional) text in it, margin is hardcoded 4 (or 2 where not enough space)
	 * reference point is (stupidly) upper!! left corner
	 * top/right/left/bottom are the linewidths
	 */
	function printTextCell($text,$font,$fontstyle,$fontheight,$x,$y,$width,$height,$position,$lpchangetop,$lpchangebottom,$top=0.2,$right=0.2,$bottom=0.2,$left=0.2){
		//0 is not acceptet as line-width by fpdf
		$this->pdf->SetLineWidth($top);
		if (!$top==0) {$this->pdf->Line($x,$y-$lpchangetop,$x+$width,$y-$lpchangetop); } //line top
		$this->pdf->SetLineWidth($bottom);
		if (!$bottom==0) {$this->pdf->Line($x,$y-$lpchangetop-$height,$x+$width,$y-$height-$lpchangetop); } //line bottom
		$this->pdf->SetLineWidth($left);
		if (!$left==0) {$this->pdf->Line($x,$y-$lpchangetop,$x,$y-$height-$lpchangetop); } //line left
		$this->pdf->SetLineWidth($right);
		if (!$right==0) {$this->pdf->Line($x+$width,$y-$lpchangetop,$x+$width,$y-$height-$lpchangetop); } //line right
		
		//if enough space space between border and text is 4pt, if not: 2pt only
		if ($this->pdf->GetStringWidth($text)>$width-8){
			$this->printTextLineBox($text,$font,$fontstyle,$fontheight,$x+4,$y-$height-$lpchangetop,$width-8,$height,$position,0,0);
		}else{
			$this->printTextLineBox($text,$font,$fontstyle,$fontheight,$x+2,$y-$height-$lpchangetop,$width-4,$height,$position,0,0);
		}
		$this->lp-=($lpchangetop+$lpchangebottom);
		$this->posx=$x+$width;
	}
  	
	function setHeaderAndFooter(){
		global $cfgApplicationName, $cfgApplicationVersion;
		global $strPage, $strOf;
		
		// get organiser
		$res = mysql_query("SELECT Organisator FROM meeting WHERE xMeeting = ".$_COOKIE['meeting_id']);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			$row = mysql_fetch_array($res);
			$this->orga = $row[0];
		}
		mysql_free_result($res);
		
		// get settings for pagelayout
		$res = mysql_query("SELECT * FROM layout WHERE xMeeting = ".$_COOKIE['meeting_id']);
		if(mysql_errno() > 0){
			AA_printErrorMsg(mysql_errno().": ".mysql_error());
		}else{
			if(mysql_num_rows($res) == 0){		// no page layout defined, standard definitions
				
				$this->stdHeaderLeft = $_COOKIE['meeting'];
				$this->stdHeaderRight = date('d.m.Y H:i');
				$this->stdFooterLeft = "created by ". $cfgApplicationName." $cfgApplicationVersion";
				$this->stdFooterCenter = $this->orga;
				$this->stdFooterRight = $strPage." {pg} " .strtolower($strOf)." {pgn}";
                $this->picHeaderCenter = "/layout/athletica-logo.png";
                $this->picHeaderBig = "";
				$this->picFooterBig = "";
				
			}else{					// layout defined
				
				$row = mysql_fetch_assoc($res);
                $this->defineHeaderAndFooter($this->stdHeaderBig, $this->picHeaderBig, 6, "", $row['BildT']);
				$this->defineHeaderAndFooter($this->stdHeaderLeft, $this->picHeaderLeft, $row['TypTL'], $row['TextTL'], $row['BildTL']);
				$this->defineHeaderAndFooter($this->stdHeaderCenter, $this->picHeaderCenter, $row['TypTC'], $row['TextTC'], $row['BildTC']);
				$this->defineHeaderAndFooter($this->stdHeaderRight, $this->picHeaderRight, $row['TypTR'], $row['TextTR'], $row['BildTR']);
                $this->defineHeaderAndFooter($this->stdFooterBig, $this->picFooterBig, 6, "", $row['BildB']);
				$this->defineHeaderAndFooter($this->stdFooterLeft, $this->picFooterLeft, $row['TypBL'], $row['TextBL'], $row['BildBL']);
				$this->defineHeaderAndFooter($this->stdFooterCenter, $this->picFooterCenter, $row['TypBC'], $row['TextBC'], $row['BildBC']);
				$this->defineHeaderAndFooter($this->stdFooterRight, $this->picFooterRight, $row['TypBR'], $row['TextBR'], $row['BildBR']);
				
			}
            if($this->picFooterBig == "") {
                $this->footerheight = 36;
            } else {
                $footer_size = getimagesize($_SERVER['DOCUMENT_ROOT'].'/athletica'.$this->picFooterBig);
                $this->footerheight = $footer_size[1]*(585/$footer_size[0]) + 10;    
            }
		}
	}
	
	/**
	* getColWidth
	* -------------
	* calculate the column_width
	* !! Font must be set before calling this function !! (in order to get the correct string-width)
	* args: total-width of all columns and one argument for each column
	* arg = array(HeaderText, Minimum Width, Weight for fillup (0 when no changeO), additional space (in pt))
	* returns array with widths of cols
	*/
	function getColWidth($total_width) {
		$weight = array();
		$width_min = array();
		$width = array();
		foreach (func_get_args() as $arr) {
			if (is_array($arr)) {
				array_push($weight, $arr[2]);
				if ($this->pdf->GetStringWidth($arr[0])+$arr[3]>$arr[1]) {
					array_push($width_min, $this->pdf->GetStringWidth($arr[0])+$arr[3]);
				} else {
					array_push($width_min, $arr[1]);
				}
			}
		}
		$minwidth = array_sum($width_min);
		$weight_sum = array_sum($weight);
		$diff = $total_width-$minwidth;
		for ($i=0; $i<count($width_min); $i++) {
			$x=$width_min[$i]+$diff*$weight[$i]/$weight_sum;
			array_push($width, $x);
		}
		return $width;
	}
    
    function GetMultiCellHeight($w, $h, $txt, $border=null, $align='J') {
        // Calculate MultiCell with automatic or explicit line breaks height
        // $border is un-used, but I kept it in the parameters to keep the call
        //   to this function consistent with MultiCell()
        $cw = &$this->pdf->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->pdf->rMargin-$this->x;
        $wmax = ($w-2*$this->pdf->cMargin)*1000/$this->pdf->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $height = 0;
        while($i<$nb)
        {
            // Get next character
            $c = $s[$i];
            if($c=="\n")
            {
                // Explicit line break
                if($this->pdf->ws>0)
                {
                    $this->pdf->ws = 0;
                    $this->pdf->_out('0 Tw');
                }
                //Increase Height
                $height += $h;
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                continue;
            }
            if($c==' ')
            {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l += $cw[$c];
            if($l>$wmax)
            {
                // Automatic line break
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                    if($this->pdf->ws>0)
                    {
                        $this->pdf->ws = 0;
                        $this->pdf->_out('0 Tw');
                    }
                    //Increase Height
                    $height += $h;
                }
                else
                {
                    if($align=='J')
                    {
                        $this->pdf->ws = ($ns>1) ? ($wmax-$ls)/1000*$this->pdf->FontSize/($ns-1) : 0;
                        $this->pdf->_out(sprintf('%.3F Tw',$this->pdf->ws*$this->pdf->k));
                    }
                    //Increase Height
                    $height += $h;
                    $i = $sep+1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
            }
            else
                $i++;
        }
        // Last chunk
        if($this->pdf->ws>0)
        {
            $this->pdf->ws = 0;
            $this->pdf->_out('0 Tw');
        }
        //Increase Height
        $height += $h;
        return $height;
    }
	
	function defineHeaderAndFooter(&$txt, &$pic, $type, $text, $picture){
		global $cfgApplicationName, $cfgApplicationVersion;
		global $strPage, $strOf;
		
		switch($type){
			case 0:
				$txt = $strPage." {pg} " .strtolower($strOf)." {pgn}";
				break;
			case 1:
				$txt = $_COOKIE['meeting'];
				break;
			case 2:
				$txt = $this->orga;
				break;
			case 3:
				$txt = date('d.m.Y H:i');
				break;
			case 4:
				$txt = "created by ".$cfgApplicationName." $cfgApplicationVersion with pdf";
				break;
			case 5:
				$txt = $text;
				break;
			case 6:
				$txt = "";
				break;
		}
		if(!empty($picture)){
			$pic = "/layout/$picture";//
		}
	}
	
	function printImage($pfad,$x,$y,$width,$height,$position) {
		if ($pfad!='')
		{	//Picture should fill space if its bigger than the box
			$pos=explode(" ",$position); //[horizontal, vertical]
			$y=$this->pageheight-$y-$height; //FPDF starts on top with 0
			$info = getimagesize($_SERVER['DOCUMENT_ROOT'].'/athletica'.$pfad); //returns array [width, height, ...]
			$picwidth=$info[0];
			$picheight=$info[1];
			$dw=$picwidth/$width;
			$dh=$picheight/$height;
			if ($dw>1 and $dw>$dh) {
				$picwidth=$width;
				$picheight=$picheight/$dw;
			}
			elseif ($dh>1 and $dh>$dw) {
				$picheight=$height;
				$picwidth=$picwidth/$dh;
			}
			if ($pos[0]=="right") {
				$x+=$width-$picwidth;
			}
			elseif ($pos[0]=="center") {
				$x+=($width-$picwidth)/2;
			}
			if ($pos[1]=="bottom") {
				$y+=$height-$picheight;
			}
			elseif ($pos[1]=="center") {
				$y+=($height-$picheight)/2;
			}
			$this->pdf->Image($_SERVER['DOCUMENT_ROOT'].'/athletica'.$pfad, $x, $y, $picwidth, $picheight);
		}
		$this->posx=$x+$width;
	}
	
	/*
	* printCheckBox
	* -------------
	* prints a checkbox with width=height=$size
	* reference-point is lower left corner
	*/
	function printCheckBox($x,$y,$size,$sign='None') { 
		$this->pdf->SetLineWidth(0.5);
		$this->pdf->Line($x,$y,$x,$y+$size); //left
		$this->pdf->Line($x+$size,$y,$x+$size,$y+$size); //right
		$this->pdf->Line($x,$y+$size,$x+$size,$y+$size); //top
		$this->pdf->Line($x,$y,$x+$size,$y); //bottom
		if ($sign=='Tick') {
			$this->pdf->Line($x+$size/10,$y+$size*4/10,$x+$size*4/10,$y+$size/10);
			$this->pdf->Line($x+$size*9/10,$y+$size*9/10,$x+$size*4/10,$y+$size/10);
		} elseif ($sign=='Cross') {
			$this->pdf->Line($x,$y,$x+$size,$y+$size);
			$this->pdf->Line($x+$size,$y,$x,$y+$size);
		}
		$this->posx=$x+$size;
	}
	
	function printPageHeader(){    
        if($this->printHeader_bool) {
            
            if($this->landscape) {
                $this->lp=($this->pageheight-35);
                
                //$this->printImage($this->picHeaderLeft,11,$this->lp,820,83,'left top');
		        $this->printImage($this->picHeaderLeft,11,$this->lp,197,24,'left top');
		        $this->printImage($this->picHeaderCenter,$this->posx,$this->lp,197,24,'center top');
		        $this->printImage($this->picHeaderRight,$this->posx,$this->lp,197,24,'right top');
		        
		        $this->printTextLineBox($this->stdHeaderLeft,$this->font,"B",10,11,$this->lp,820,24,'left top',0,0);
		        $this->printTextLineBox($this->stdHeaderCenter,$this->font,"B",10,11,$this->lp,820,24,'center top',0,0);
		        $this->printTextLineBox($this->stdHeaderRight,$this->font,"B",10,11,$this->lp,820,24,'right top',0,5);
            } else {
                
                if($this->picHeaderBig != "") {
                    $this->lp=($this->pageheight);
                    $this->printImage($this->picHeaderBig,0,$this->lp,$this->pagewidth,0,'center top');
                    $header_size = getimagesize($_SERVER['DOCUMENT_ROOT'].'/athletica'.$this->picHeaderBig);
                    $this->lp = $this->lp - $header_size[1]*(585/$header_size[0]); 
                } else {
                    $this->lp=($this->pageheight-35);
                    $this->printImage($this->picHeaderLeft,11,$this->lp,191,24,'left top');
                    $this->printImage($this->picHeaderCenter,$this->posx,$this->lp,191,24,'center top');
                    $this->printImage($this->picHeaderRight,$this->posx,$this->lp,191,24,'right top');
                    
                    $this->printTextLineBox($this->stdHeaderLeft,$this->font,"B",10,11,$this->lp,191,24,'left top',0,0);
                    $this->printTextLineBox($this->stdHeaderCenter,$this->font,"B",10,$this->posx,$this->lp,191,24,'center top',0,0);
                    $this->printTextLineBox($this->stdHeaderRight,$this->font,"B",10,$this->posx,$this->lp,191,24,'right top',0,5);
                }
            }
        } else {
            $this->lp=($this->pageheight-20);
        }
	}
	
	function printPageFooter(){
        if($this->printFooter_bool) {
            if($this->landscape) {    
		        $this->printImage($this->picFooterLeft,11,12,820,24,'left bottom');
		        $this->printImage($this->picFooterCenter,11,12,820,24,'center bottom');
		        $this->printImage($this->picFooterRight,11,12,820,24,'right bottom');
		        
		        $this->printTextLineBox($this->stdFooterLeft,$this->font,"B",10,11,12,820,24,'left bottom',0,0);
		        $this->printTextLineBox($this->stdFooterCenter,$this->font,"B",10,11,12,820,24,'center bottom',0,0);
		        $this->printTextLineBox($this->stdFooterRight,$this->font,"B",10,11,12,820,24,'right bottom',0,0);
            } else {
                if($this->picFooterBig != "") {
                    $this->printImage($this->picFooterBig,0,0,$this->pagewidth,0,'center bottom');
                } else {
                    $this->printImage($this->picFooterLeft,11,12,191,24,'left bottom');
                    $this->printImage($this->picFooterCenter,$this->posx,12,191,24,'center bottom');
                    $this->printImage($this->picFooterRight,$this->posx,12,191,24,'right bottom');
                
                    $this->printTextLineBox($this->stdFooterLeft,$this->font,"B",10,11,12,191,24,'left bottom',0,0);
                    $this->printTextLineBox($this->stdFooterCenter,$this->font,"B",10,$this->posx,12,191,24,'center bottom',0,0);
                    $this->printTextLineBox($this->stdFooterRight,$this->font,"B",10,$this->posx,12,191,24,'right bottom',0,0);   
                }
            }
        }
	}
} // end PRINT_Page



/********************************************
 * Print_Definitions: printing meeting definitions
 *******************************************/

class PRINT_Definitions_pdf extends PRINT_Page_pdf
{
	function printLine($disc)
	{
		if($this->lp < $this->footerheight + 12)		// page break check; 36+12=48
		{
			$this->insertPageBreak();
		}
    $this->printTextLineBox($disc,$this->font,"",10,$this->marginLeft,$this->lp,515,30,'left bottom',10,2);
	}

} // end PRINT_Definitions




/********************************************
 * PRINT_RankingList: printing ranking list for single events
 *******************************************/

class PRINT_RankingList_pdf extends PRINT_Page_pdf
{
	var $cat;			// current category
	var $disc;			// current discipline
	var $relay;			// current relay status
	var $round;			// current round
	var $points;		// current point info
	var $wind;			// current wind info
	var $title;			// heat title
	var $heatwind;		// wind per heat
	var $width;			// width of columns


	function printSubTitle($category='', $discipline='', $round='', $info)
	{  
  
		if(!empty($category)) {
			$this->cat = $category;
		}
		if(!empty($discipline)) {
			if(empty($info)){
				$this->disc = $discipline;
			}else{
				$this->disc = $discipline." ($info)";
			}
		}
		if(!empty($round)) {
			$this->round = $round;
		}
		else {
			$this->round = '';
		}
	
		if(empty($category)) { 
			$this->round = $this->round . " " . $GLOBALS['strCont'];         
		}
		// page break check
		if($this->lp < $this->footerheight + 170)		//This+169 (from HeaderLine)=37+169=206
		{ 
			$this->insertPageBreak();       
		}
		$this->lp-=22;
		$this->printTextLineBox($this->cat,$this->font,"B",13,$this->marginLeft,$this->lp,160,30,'left bottom',8,0);
		$this->printTextLineBox($this->disc,$this->font,"B",13,$this->posx,$this->lp,160,30,'left bottom',0,0);
		$this->printTextLineBox($this->round,$this->font,"B",13,$this->posx,$this->lp,160,30,'left bottom',0,5);
	}


	function printInfoLine($info)
	{ 
		if(!empty($info))
		{   
			$this->printTextLineBox($info,$this->font,"I",10,$this->marginLeft,$this->lp,515,30,'left bottom',10,2);
		}
	}


	function printHeaderLine($title, $relay=FALSE, $points=FALSE
		, $wind=FALSE, $heatwind='', $time="", $svm = false, $base_perf = false, $qual_mode = false , $eval, $withStartnr, $teamsm = false)
	{ 
		$this->relay = $relay;
		$this->points = $points;
		$this->wind = $wind;
		$this->heatwind = $heatwind;
		$this->title = $title;
	
        // page break check
		if($this->lp < $this->footerheight + 133)  //Footer+8 Lines (often used)+This=36+8*12+37=169
		{  
			$this->insertPageBreak();
			$this->printSubTitle('','','','');
		}
		$this->lp-=10;
    
		if(!empty($this->title))
		{  
			$this->printTextLineBox($this->title,$this->font,"",11,$this->marginLeft,$this->lp,127,30,'left bottom',11,0);
			if(!empty($this->heatwind)) {
				$text=$GLOBALS['strWind'] . ": " .$this->heatwind;
			}else{
				$text='';
			}
			$this->printTextLineBox($text,$this->font,"",11,$this->posx,$this->lp,171,30,'center bottom',0,0);
			$this->printTextLineBox($time,$this->font,"",11,$this->posx,$this->lp,$this->pagewidth - $this->posx - $this->marginRight,30,'right bottom',0,5);
			$this->lp-=2;
		}
        //elseif ($eval == $cfgEvalType[$strEvalTypeDiscDefault]){
        else {
			if(!empty($time)){
                $this->printTextLineBox($time,$this->font,"",11,$this->marginLeft,$this->lp,$this->pagewidth - $this->marginLeft - $this->marginRight,30,'right bottom',5,5);
            }
        }
    
		$year = '';
		$wyear = 10;
		$ioc = '';
		$wioc = 12;
		if($this->relay == FALSE) {
			$year = $GLOBALS['strYearShort'];
			$wyear = 16;
			$ioc = $GLOBALS['strIocShort'];
			$wioc = 22;
		}

		$points = '';
		$wpoints = 25;
		if($this->points == TRUE) {
			$points = $GLOBALS['strPoints'];
			$wpoints = 35;
		}
		
		$wind = '';
		$wwind = 15; //with 0 the tables-column-width would get too different (looks bad)
		if($this->wind == TRUE) {
			$wind = $GLOBALS['strWind'];
			$wwind = 25;
		}
		$stnr = '';
		$wstnr = 0;
		if ($withStartnr && $this->relay == FALSE) {
			$stnr = $GLOBALS['strStNr'];
			$wstnr = 14;
		}
		if ($svm) { 
			$ssvm=$GLOBALS['strTeam'];
    }elseif($teamsm){
      $ssvm=$GLOBALS['strTeamsm'];
		} else { 
			$ssvm=$GLOBALS['strClub'];
		}
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth($this->pagewidth - $this->marginLeft - $this->marginRight - 30,array($GLOBALS['strRank'],28,0,0),array($stnr,$wstnr,0,0),
		array($GLOBALS['strName'],100,4,0),array($year,$wyear,0,0),array($ssvm,137,2,0),
		array($ioc,$wioc,0,0),array($GLOBALS['strPerformance'],60,0,0),array('',12,0,0),
		array($wind,$wwind,0,0),array($points,$wpoints,0,0),array($GLOBALS['strResultRemarkShort'],30,0,0));
		
		$this->printTextLineBox($GLOBALS['strRank'],$this->font,"B",10,$this->marginLeft,$this->lp,$this->width[0],30,'left bottom',10,0);
		$this->printTextLineBox($stnr,$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($year,$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($ssvm,$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'left bottom',0,0);
		$this->printTextLineBox($ioc,$this->font,"B",10,$this->posx+2,$this->lp,$this->width[5],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strPerformance'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[6],30,'right bottom',0,0);
		$this->printTextLineBox('',$this->font,"B",10,$this->posx+2,$this->lp,$this->width[7],30,'left bottom',0,0);
		$this->printTextLineBox($wind,$this->font,"B",10,$this->posx+2,$this->lp,$this->width[8],30,'right bottom',0,0);
		$this->printTextLineBox($points,$this->font,"B",10,$this->posx+2,$this->lp,$this->width[9],30,'right bottom',0,0);
		$this->printTextLineBox($GLOBALS['strResultRemarkShort'],$this->font,"B",10,$this->posx+7,$this->lp,$this->width[10],30,'left bottom',0,2);
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line($this->marginLeft,$this->lp,$this->pagewidth - $this->marginRight,$this->lp);
		$this->lp-=2;
	}


	function startList() //not needed with pdf, do not delete!
	{   
		return;
	}


	function printLine($rank, $name, $year, $club, $perf, $wind, $points, $qual, $ioc, $sb="", $pb="", $qual_mode=false, $athleteCat='', $remark, $secondResult=false, $withStartnr, $startnr)
	{ 
		$lnqnty=1;
		if (!$secondResult){
			// page break check
			if($this->lp < $this->footerheight + 24)		//Footer + This (2 Lines) = 36+2*12=60
			{   			   			
				$this->insertPageBreak(); 
				$this->printSubTitle('','','','');  			 	
				$this->printHeaderLine('', $this->relay, $this->points, $this->wind, '', '', false, false, false, '', $withStartnr);          
			}
		} else {
			//$this->lp-=12; 
		} 
		$this->printTextLineBox($rank,$this->font,"",10,$this->marginLeft,$this->lp,$this->width[0],30,'right bottom',10,0);
		if ($this->width[1]==0){
			$this->posx+=2;
		}else{
			$this->printTextLineBox($startnr,$this->font,"",8,$this->posx+2,$this->lp,$this->width[1],30,'center bottom',0,0);
		}
		$lnqnty=max($this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],'left',-10,0),$lnqnty);
		$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',10,0);
		$lnqnty=max($this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],'left',-10,0),$lnqnty);
		$this->printTextLineBox($ioc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[5],30,'left bottom',10,0);
		$this->printTextLineBox($perf,$this->font,"",10,$this->posx+2,$this->lp,$this->width[6],30,'right bottom',0,0);
		$this->printTextLineBox($qual,$this->font,"",10,$this->posx+2,$this->lp,$this->width[7],30,'left bottom',0,0);
		$this->printTextLineBox($wind,$this->font,"",10,$this->posx+2,$this->lp,$this->width[8],30,'right bottom',0,0);
		if ($this->width[9]==0) {
			$this->posx+=2;
		} else {
			$this->printTextLineBox($points,$this->font,"",10,$this->posx+2,$this->lp,$this->width[9],30,'right bottom',0,0);
		}
		$this->printTextLineBox($remark,$this->font,"",10,$this->posx+7,$this->lp,$this->width[10],30,'left bottom',0,-8);
		$this->lp-=$lnqnty*12;
	}
	
	function printAthletesLine($text){ 
		$this->printTextLineBox($text,$this->font,"",8,$this->marginLeft+33,$this->lp,473,30,'left bottom',8,2);
	}

} // end PRINT_RankingList


/********************************************
 * PRINT_CombingedRankingList: printing ranking list for combined events
 *******************************************/

class PRINT_CombinedRankingList_pdf extends PRINT_RankingList_pdf
{
	var $width;
	
	function printHeaderLine($time = "")
	{ 
		$add=0;
        if(!empty($time)){ 
           $add = 13;  
        }
		// page break check
		if($this->lp < $add + $this->footerheight + 52)  //Footer+ 1 line + InfoLine + (time) + header  =36+12+16+(13)+24=(13)+88
		{
			$this->insertPageBreak();
		}
		$this->lp-=10;
		if(!empty($time)){
			    $this->printTextLineBox($time,$this->font,"",11,$this->marginLeft,$this->lp,$this->pagewidth - $this->marginLeft - $this->marginRight,30,'right bottom',5,5);
		}
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth($this->pagewidth - $this->marginLeft - $this->marginLeft - 30,array($GLOBALS['strRank'],28,0,0),array($GLOBALS['strName'],200,2,0),
			array($GLOBALS['strYearShort'],16,0,0),array($GLOBALS['strClub'],150,1,0),array($GLOBALS['strIocShort'],25,0,0),
			array($GLOBALS['strPoints'],50,0,0),array($GLOBALS['strResultRemarkShort'],24,0,0));
		
		$this->printTextLineBox($GLOBALS['strRank'],$this->font,"B",10,$this->marginLeft,$this->lp,$this->width[0],30,'left bottom',10,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strYearShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'center bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strIocShort'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[4],30,'center bottom',0,0);
		$this->printTextLineBox($GLOBALS['strPoints'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[5],30,'right bottom',0,0);
		$this->printTextLineBox($GLOBALS['strResultRemarkShort'],$this->font,"B",10,$this->posx+7,$this->lp,$this->width[6],30,'left bottom',0,2);
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line($this->marginLeft,$this->lp,$this->pagewidth - $this->marginRight,$this->lp);
		$this->lp-=2;
	}
  
    function printSubTitle($category='')
    {
        if(!empty($category)) {
            $this->cat = $category;
        }   
		// page break check
        if($this->lp < $this->footerheight + 89)		//all in Header + This=101+24=125
        {        
            $this->insertPageBreak();           
        }
        $this->printTextLineBox($this->cat,$this->font,"B",13,$this->marginLeft,$this->lp,515,30,'left bottom',22,2);
    }
    
   
	function printLine($rank, $name, $year, $club, $points, $ioc, $remark)
	{ 	$lnqnty=1;
		// page break check
		if($this->lp < $this->footerheight + 28)	//Footer + 1 Line + InfoLine = 36 + 12 + 16 = 64
		{   
			$this->insertPageBreak();
			$this->printSubTitle();
			$this->printHeaderLine($this->relay, $this->windinfo);
		}
		$pt = '';
		if(!empty($rank)) {
			$pt = ".";
		}
		$this->printTextLineBox($rank.$pt,$this->font,"",10,$this->marginLeft,$this->lp,$this->width[0],30,'right bottom',10,0);
		$lnqnty=max($this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0),$lnqnty);
		$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],30,'center bottom',10,0);
		$lnqnty=max($this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],'left',-10,0),$lnqnty);
		$this->printTextLineBox($ioc,$this->font,"",10,$this->posx+2,$this->lp,$this->width[4],30,'center bottom',10,0);
		$this->printTextLineBox($points,$this->font,"",10,$this->posx+2,$this->lp,$this->width[5],30,'right bottom',0,0);
		$this->printTextLineBox($remark,$this->font,"",10,$this->posx+7,$this->lp,$this->width[6],30,'left bottom',0,-10);
		
		$this->lp-=$lnqnty*12;
	}


	function printInfo($info)
	{
       $lnqnty = $this->printTextFlowSimp($info,$this->font,"",8,$this->marginLeft + 30,$this->lp,400,'left',2,4);
       $this->lp -= 10*$lnqnty;   
	}

} // end PRINT_CombinedRankingList


/********************************************
 * PRINT_TeamRankingList: printing ranking list for svm team events
 *******************************************/

class PRINT_TeamRankingList_pdf extends PRINT_RankingList_pdf    //TODO
{
	var $width;
	function printHeaderLine($time = "")
	{
		$add=0;
        if(!empty($time)){ 
           $add = 13;  
        }
		// page break check
		if($this->lp < $add + $this->footerheight + 111)  //Footer + 4 lines + 4 InfoLines + (time) + header  =36+4*12+4*12+(13)+15=(13)+147
		{
			$this->insertPageBreak();
		}
		if(!empty($time)){
			$this->printTextLineBox($time,$this->font,"",11,$this->marginLeft,$this->lp,515,30,'right bottom',11,2);
		}
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth($this->pagewidth - $this->marginLeft - $this->marginRight - 30,array($GLOBALS['strRank'],28,0,0),array($GLOBALS['strName'],230,2,0),
			array($GLOBALS['strClub'],186,1,0),array($GLOBALS['strPoints'],65,0,0));
		
		$this->printTextLineBox($GLOBALS['strRank'],$this->font,"B",10,$this->marginLeft,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strPoints'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'right bottom',0,2);
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line($this->marginLeft,$this->lp,$this->pagewidth - $this->marginRight,$this->lp);
		$this->lp-=2;
	}


	function printLine($rank, $name, $club, $points)
	{
		if($this->lp < $this->footerheight + 24)	//Footer + line + infoline = 36 + 12 + 12 = 60
		{
			$this->insertPageBreak();
			$this->printSubTitle();
			$this->printHeaderLine($this->relay, $this->windinfo);
		}
		
		$pt = '';
		if(!empty($rank)) {
			$pt = ".";
		}
		$this->printTextLineBox($rank.$pt,$this->font,"",10,$this->marginLeft,$this->lp,$this->width[0],30,'right bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],'left',0,0,$lnqnty);
		$this->printTextLineBox($points,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'right bottom',10,-8);
		
		$this->lp-=$lnqnty*12;
	}


	function printAthleteLine($name, $year, $points, $country, $club, $rank, $type)
	{
		if($this->lp < $this->footerheight + 24)	//Footer + line + infoline = 36 + 12 + 12 = 60
		{
			$this->insertPageBreak();
			$this->printSubTitle();
			    $this->printHeaderLine($this->relay, $this->windinfo);
		}
		
		$pt = '';
		if(!empty($rank)) {
			$pt = ".";
		}
		if ($type == 'teamP'){
			$this->printTextLineBox($rank.$pt,$this->font,"",10,$this->marginLeft,$this->lp,$this->width[0],30,'right bottom',10,0);
			$lnqnty=$this->printTextFlowSimp($name.", ".$year.", ".$country,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
			$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],'left',0,0,$lnqnty);
			$this->printTextLineBox($points,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'right bottom',10,-10);
		}
		else {
			$lnqnty=$this->printTextFlowSimp($name.", ".$year.", ".$country,$this->font,"",10,42+$this->width[0],$this->lp,$this->width[1],'left',0,0,1);
			$this->printTextLineBox($points,$this->font,"",10,$this->posx+4+$this->width[2],$this->lp,$this->width[3],30,'right bottom',10,-10);
		}
		$this->lp-=$lnqnty*12;

	}


	function printInfo($info)
	{
		$this->printTextLineBox($info,$this->font,"",8,$this->marginLeft + 30,$this->lp,475,30,'left bottom',8,4);  
	}

} // end PRINT_TeamRankingList



/********************************************
 * PRINT_TeamSheet: printing team sheets
 *******************************************/

class PRINT_TeamSheet_pdf extends PRINT_Page_pdf
{
	var $width;
	function printHeader($team, $category, $competitors)
	{
		$this->printTextLineBox($GLOBALS['strTeamRankingTitle'],$this->font,"B",11,$this->marginLeft,$this->lp,515,30,'left bottom',20,2);
		
		$result = mysql_query("
			SELECT m.Ort
				, s.Name
				, DATE_FORMAT(m.DatumBis, '" . $GLOBALS['cfgDBdateFormat'] . "')
			FROM
				meeting AS m
				LEFT JOIN stadion AS s ON (m.xStadion = s.xStadion)
			WHERE 
                m.xMeeting = ". $_COOKIE['meeting_id']);

		if(mysql_errno() > 0) {		// DB error
			AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
		}
		else {
			$row = mysql_fetch_row($result);
			$place = $row[0];
			$stadium = $row[1];
			$date = $row[2];
			mysql_free_result($result);
		}
		
		$this->printTextLineBox($GLOBALS['strPlace'] . ", " . $GLOBALS['strStadium'],$this->font,"",10,$this->marginLeft,$this->lp,140,30,'left bottom',14,0);
		$this->printTextLineBox($place . ", " . $stadium,$this->font,"",10,$this->posx+2,$this->lp,229,30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strDate'],$this->font,"",10,$this->posx+2,$this->lp,80,30,'left bottom',0,0);
		$this->printTextLineBox($date,$this->font,"",10,$this->posx+2,$this->lp,60,30,'left bottom',0,4);
		
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"",10,$this->marginLeft,$this->lp,140,30,'left bottom',10,0);
		$lnqnty = $this->printTextFlowSimp($team,$this->font,"B",10,$this->posx+2,$this->lp,229,'left',-10,10);
		$this->printTextLineBox($GLOBALS['strCategory'],$this->font,"",10,$this->posx+2,$this->lp,80,30,'left bottom',0,0);
		$this->printTextLineBox($category,$this->font,"B",10,$this->posx+2,$this->lp,60,30,'left bottom',0,$lnqnty*12-8);
		
		$this->printTextLineBox($GLOBALS['strCompetition'],$this->font,"",10,$this->marginLeft,$this->lp,140,30,'left bottom',10,0);
		$lnqnty = $this->printTextFlowSimp($competitors,$this->font,"",10,$this->posx+2,$this->lp,373,'left',-10,0);
		$this->lp-=$lnqnty*12+4;
		
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line($this->marginLeft,$this->lp,$this->pagewidth - $this->marginRight,$this->lp);
		$this->lp-=6;
	}


	function printSubHeader($title)
	{	// page break check
		if($this->lp < $this->footerheight + 62)		//Footer + 4 Lines + SubHeader = 36 + 4*12 + 14 = 98
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($title,$this->font,"B",10,$this->marginLeft,$this->lp,515,30,'left bottom',10,2);
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line($this->marginLeft,$this->lp,555,$this->lp);
		$this->lp-=2;
	}



	function printLine($disc, $name, $perf, $wind, $points, $total, $remark)
	{	// page break check
		if($this->lp < $this->footerheight + 24)		//Footer + 2 lines = 36 + 2*12 = 60
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($disc,$this->font,"",10,$this->marginLeft,$this->lp,80,30,'left bottom',10,0);
		$lnqnty = $this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,272,'left',-10,10); //6.1: 285, >6.2: 285-30-2=253, for points
		$this->printTextLineBox($perf,$this->font,"",10,$this->posx+2,$this->lp,65,30,'right bottom',0,0);
		$this->printTextLineBox($wind,$this->font,"",10,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
		$this->printTextLineBox($points,$this->font,"",10,$this->posx+2,$this->lp,30,30,'left bottom',0,0); //new in 6.2, TODO: test
		$this->printTextLineBox($remark,$this->font,"",10,$this->posx+2,$this->lp,30,30,'left bottom',0,0);
		$this->printTextLineBox($total,$this->font,"",10,$this->posx+2,$this->lp,30,30,'right bottom',0,0);
		$this->lp-=$lnqnty*12-10;
	}


	function printLineCombined($name, $year, $points, $country)
	{	// page break check
		if($this->lp < $this->footerheight + 24)		//Footer + 2 lines = 36 + 2*12 = 60
		{
			$this->insertPageBreak();
		}
		$lnqnty = $this->printTextFlowSimp($name,$this->font,"",10,$this->marginLeft,$this->lp,367,'left',0,10);
		$this->printTextLineBox($year,$this->font,"",10,$this->posx+2,$this->lp,65,30,'left bottom',0,0);
		$this->printTextLineBox($country,$this->font,"",10,$this->posx+2,$this->lp,25,30,'left bottom',0,0);
		$this->printTextLineBox($points,$this->font,"",10,$this->posx+34,$this->lp,20,30,'right bottom',0,0);
		$this->lp-=$lnqnty*12-10;
	}


	function printDisciplinesCombined($disciplines)
	{
		$this->printTextLineBox($disciplines,$this->font,"",10,$this->marginLeft+10,$this->lp,505,30,'left bottom',10,2);
	}


	function printRelayAthlete($name)
	{	// page break check
		if($this->lp < $this->footerheight + 10)		//Footer + athleteline = 36 +10=46
		{
			$this->insertPageBreak();
		}
		$this->printTextLineBox($name,$this->font,"",8,$this->marginLeft+90,$this->lp,425,30,'left bottom',8,2);
	}


	function printSubTotal($total)
	{	// page break check
		if($this->lp < $this->footerheight + 22)		//Footer + Subtotal = 36 + 22 = 58
		{
			$this->insertPageBreak();
		}
		$this->lp-=4;
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line(476,$this->lp,$this->pagewidth - $this->marginRight,$this->lp);
		$this->lp-=6;
		$this->printTextLineBox($GLOBALS['strSubTotal'],$this->font,"B",10,476,$this->lp,57,30,'left bottom',10,0);
		$this->printTextLineBox($total,$this->font,"B",10,$this->posx+2,$this->lp,30,30,'right bottom',0,2);
	}


	function printTotal($total)
	{	// page break check
		if($this->lp < $this->footerheight + 32)		//Footer + total  = 36 + 32 = 68
		{
			$this->insertPageBreak();
		}
		$this->lp-=4;
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line(476,$this->lp,$this->pagewidth - $this->marginRight,$this->lp);
		$this->lp-=6;
		$this->printTextLineBox($GLOBALS['strTotal'],$this->font,"B",10,476,$this->lp,57,30,'left bottom',10,0);
		$this->printTextLineBox($total,$this->font,"B",10,$this->posx+2,$this->lp,30,30,'right bottom',0,2);
		$this->lp-=4;
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line(476,$this->lp,$this->pagewidth - $this->marginRight,$this->lp);
		$this->lp-=0.5;
		$this->pdf->Line(476,$this->lp,$this->pagewidth - $this->marginRight,$this->lp);
		$this->lp-=5.5; 
	}


	function printFooter()
	{   // page break check
		if($this->lp < $this->footerheight + 114)		//Footer + SubscriptionFooter (2 lines) + space for signature  = 36 + 2*12 + 90 = 150
		{
			$this->insertPageBreak();
		}
		$this->lp-=4;
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line($this->marginLeft,$this->lp,$this->pagewidth - $this->marginRight,$this->lp);
		$this->lp-=6;
		$lnqnty = $this->printTextFlowSimp($GLOBALS['strTeamRankingDeclaration'],$this->font,"",10,$this->marginLeft,$this->lp,515,'left',0,0);
		$this->lp-=$lnqnty*12;
		$this->printTextLineBox($GLOBALS['strJudges'] . ":",$this->font,"",10,$this->marginLeft,$this->lp,258,30,'left bottom',10,5);
		$this->printTextLineBox($GLOBALS['strTeamLeaders'] . ":",$this->font,"",10,$this->posx,$this->lp,257,30,'right bottom',0,0);
	}
    
    
} // end PRINT_TeamSheet



/********************************************
 * PRINT_TeamSMRankingList: printing ranking list for team sm events
 *******************************************/

class PRINT_TeamSMRankingList_pdf extends PRINT_RankingList_pdf 
{
	var $width;
	function printHeaderLine($time = "")
	{
		$add=0;
        if(!empty($time)){ 
           $add = 13;  
        }
		// page break check
		if($this->lp < $add + $this->footerheight + 111)  //Footer + 4 lines + 4 InfoLines + (time) + header  =36+4*12+4*12+(13)+15=(13)+147
		{
			$this->insertPageBreak();
		}
		if(!empty($time)){
			$this->printTextLineBox($time,$this->font,"",11,$this->marginLeft,$this->lp,515,30,'right bottom',11,2);
		}
		$this->pdf->SetFont($this->font,"B",10); 
		$this->width = $this->getColWidth(509,array($GLOBALS['strRank'],28,0,0),array($GLOBALS['strName'],230,2,0),
			array($GLOBALS['strClub'],186,1,0),array($GLOBALS['strResult'],65,0,0));
		
		$this->printTextLineBox($GLOBALS['strRank'],$this->font,"B",10,$this->marginLeft,$this->lp,$this->width[0],30,'left bottom',11,0);
		$this->printTextLineBox($GLOBALS['strName'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[1],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strClub'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[2],30,'left bottom',0,0);
		$this->printTextLineBox($GLOBALS['strResult'],$this->font,"B",10,$this->posx+2,$this->lp,$this->width[3],30,'right bottom',0,2);
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line($this->marginLeft,$this->lp,555,$this->lp);
		$this->lp-=2;
	}


	function printLine($rank, $name, $club, $perf)
	{	// page break check
		if($this->lp < $this->footerheight + 24)	//Footer + line + infoline = 36 + 12 + 12 = 60
		{
			$this->insertPageBreak();
			$this->printSubTitle();
			$this->printHeaderLine($this->relay, $this->windinfo);
		}
		
		$pt = '';
		if(!empty($rank)) {
			$pt = ".";
		}
		$this->printTextLineBox($rank.$pt,$this->font,"",10,$this->marginLeft,$this->lp,$this->width[0],30,'right bottom',10,0);
		$lnqnty=$this->printTextFlowSimp($name,$this->font,"",10,$this->posx+2,$this->lp,$this->width[1],'left',-10,0,1);
		$lnqnty=$this->printTextFlowSimp($club,$this->font,"",10,$this->posx+2,$this->lp,$this->width[2],'left',0,0,$lnqnty);
		$this->printTextLineBox($perf,$this->font,"",10,$this->posx+2,$this->lp,$this->width[3],30,'right bottom',10,-10);
		
		$this->lp-=$lnqnty*12;
	}


	function printAthleteLine($name, $year, $perf) //seems unused
	{
		if(($this->lpp - $this->linecnt) < 2)		// page break check
		{
			printf("</table>");
			$this->insertPageBreak();
			$this->printSubTitle();
			printf("<table class='rank'>");
			$this->printHeaderLine($this->relay, $this->windinfo);
		}
		
		$this->linecnt++;			// increment line count
?>
	<tr>
		<td class='team_rank' />
		<td class='team_name'><?php echo "$name, $year"; ?></td>
		<td class='team_club'><?php echo $perf; ?></td>
		<td class='team_points' />
	</tr>

<?php
	}


	function printInfo($info)
	{
		$this->printTextLineBox($info,$this->font,"",8,80,$this->lp,475,30,'left bottom',8,4);   
	}

} // end PRINT_TeamSMRankingList



/********************************************
 * PRINT_Timetable: printing meeting timetable
 *******************************************/

class PRINT_Timetable_pdf extends PRINT_Page_pdf   //TODO: but in the way it is made in print_timetable.php it does not make sense to implement
{

	function printHeaderLine($headerline)
	{
		if(($this->lpp - $this->linecnt) < 3)		// page break check
		{
			$this->insertPageBreak();
		}
		$this->linecnt++;	// needs two lines (see style sheet)
?>
	<tr><?php echo $headerline; ?></tr>
<?php
	}


	function printLine($row)
	{
		if(($this->lpp - $this->linecnt) < 1)		// page break check
		{
			$this->insertPageBreak();

		}
		$this->linecnt++;			// increment line count
?>
	<tr><?php echo $row; ?></tr>
<?php
	}

} // end PRINT_Timetable



/***************************************************************
 * PRINT_TimetableComp: printing meeting timetable (competition)
 ***************************************************************/

class PRINT_TimetableComp_pdf extends PRINT_Page_pdf   //TODO
{
	var $dateFormat = '';
	var $width;
	
	function startHeaderComp(){ //not needed with pdf, do not delete!
		return;
	}
	
	function endHeaderComp(){ //not needed with pdf, do not delete!
		return;
	}

	function printHeaderLine($dateFormat='')
	{
		global $strTimetableCompEnrolement, $strCategoryShort, $strDisciplineShort, $strTimetableCompHeatType, $strTimetableCompGrp, 
			   $strTimetableCompManipulationTime, $strTimetableCompTime, $strTimetableCompHeatNum, $strTimetableCompLauf, 
			   $strTimetableCompRemarks;
			   
		$date = ($dateFormat!='') ? $dateFormat : $this->dateFormat;
		// page break check
		if($this->lp < $this->footerheight + 75)		//36+20+55=111
		{
			$this->insertPageBreak();
		}
		$txt=array($strTimetableCompEnrolement,$strCategoryShort,$strDisciplineShort,
		$strTimetableCompHeatType,$strTimetableCompGrp,$strTimetableCompManipulationTime,
		$strTimetableCompTime,$strTimetableCompHeatNum,$strTimetableCompLauf,$strTimetableCompRemarks);
		$txt_l=array();
		$txt_a=array();
		
		foreach ($txt as $x) {  //search longest part in multiline text
			$x=explode('<br/>',$x);
			array_push($txt_a,$x);
			$max=-1;
			$t='';
			foreach ($x as $y) {
				$l=$this->pdf->GetStringWidth($y);
				if ($l>$max) {
					$max=$l; 
					$t=$y; 
				}
			}
			array_push($txt_l,$t);
		}
		$this->width=$this->getColWidth(515,array($txt_l[0],40,0,0), array($txt_l[1],43,0,0),array($txt_l[2],103,1,0),
		array($txt_l[3],36,0,0),array($txt_l[4],28,0,0),array($txt_l[5],40,0,0),array($txt_l[6],40,0,0),
		array($txt_l[7],36,0,0),array($txt_l[8],56,0,0),array($txt_l[9],96,0,2));
	
		$this->pdf->SetLineWidth(1);
		$this->pdf->Line(40,$this->lp+1,555,$this->lp+1);
		$this->printTextFlowSimp(implode(' ',$txt_a[0]),$this->font,'B',11,40,$this->lp,$this->width[0],'left',0,0);
		$this->printTextFlowSimp(implode(' ',$txt_a[1]),$this->font,'B',11,$this->posx,$this->lp,$this->width[1],'left',0,0);
		$this->printTextFlowSimp(implode(' ',$txt_a[2]),$this->font,'B',11,$this->posx,$this->lp,$this->width[2],'left',0,0);
		$this->printTextFlowSimp(implode(' ',$txt_a[3]),$this->font,'B',11,$this->posx,$this->lp,$this->width[3],'left',0,0);
		$this->printTextFlowSimp(implode(' ',$txt_a[4]),$this->font,'B',11,$this->posx,$this->lp,$this->width[4],'left',0,0);
		$this->printTextFlowSimp(implode(' ',$txt_a[5]),$this->font,'B',11,$this->posx,$this->lp,$this->width[5],'left',0,0);
		$this->printTextFlowSimp(implode(' ',$txt_a[6]),$this->font,'B',11,$this->posx,$this->lp,$this->width[6],'left',0,0);
		$this->printTextFlowSimp(implode(' ',$txt_a[7]),$this->font,'B',11,$this->posx,$this->lp,$this->width[7],'left',0,0);
		$this->printTextFlowSimp(implode(' ',$txt_a[8]),$this->font,'B',11,$this->posx,$this->lp,$this->width[8],'left',0,0);
		$this->printTextFlowSimp(implode(' ',$txt_a[9]),$this->font,'B',11,$this->posx,$this->lp,$this->width[9],'left',0,45);
		$this->printTextLineBox($date,$this->font,'B',14,40,$this->lp,515,25,'left center',10,0);
		$this->pdf->Line(40,$this->lp,555,$this->lp);
		$this->dateFormat = $date;
	}


	function startTableComp(){ //not needed with pdf, do not delete!
		return;
	}
	
	function endTableComp(){ //not needed with pdf, do not delete!
		return;
	}
	
	function printLine($col1, $col2, $col3, $col4, $col5, $col6, $col7, $col10)
	{	// page break check
		if($this->lp < $this->footerheight + 20)	//36+20
		{
			$this->insertPageBreak();
			$this->printHeaderLine($this->dateFormat);
		}
		$this->printTextCell($col1,$this->font,'',11,40,$this->lp,$this->width[0],20,'left center',0,0,0,0,0.2,0);
		$this->printTextCell($col2,$this->font,'',11,$this->posx,$this->lp,$this->width[1],20,'left center',0,0,0,0,0.2,0);
		$this->printTextCell($col3,$this->font,'',11,$this->posx,$this->lp,$this->width[2],20,'left center',0,0,0,0,0.2,0);
		$this->printTextCell($col4,$this->font,'',11,$this->posx,$this->lp,$this->width[3],20,'left center',0,0,0,0,0.2,0);
		$this->printTextCell($col5,$this->font,'',11,$this->posx,$this->lp,$this->width[4],20,'left center',0,0,0,0,0.2,0);
		$this->printTextCell($col6,$this->font,'',11,$this->posx,$this->lp,$this->width[5],20,'left center',0,0,0,0,0.2,0);
		$this->printTextCell($col7,$this->font,'',11,$this->posx,$this->lp,$this->width[6],20,'left center',0,0,0,0.2,0.2,0);
		$this->printTextCell('',$this->font,'',11,$this->posx,$this->lp,$this->width[7],20,'left center',0,0,0,1,0.2,0);
		$this->printTextCell('',$this->font,'',11,$this->posx,$this->lp,$this->width[8],20,'left center',0,0,0,0.2,0.2,0);
		$this->printTextCell($col10,$this->font,'',11,$this->posx,$this->lp,$this->width[9],20,'left center',0,20,0,0,0.2,0);
	}

} // end PRINT_TimetableComp


/********************************************
 * PRINT_Statistics:	printing meeting statistics
 *******************************************/

class PRINT_Statistics_pdf extends PRINT_Page_pdf 
{
	var $headerCol1;
	var $headerCol2;
	var $headerCol3;
	var $headerCol4;
	var $headerCol5;
	var $headerCol6;
	var $width;
	var $LineWidth;
	
	function printHeaderLine($col1, $col2, $col3, $col4="", $col5="", $col6="")
	{	// page break check
		if($this->lp < $this->footerheight + 57)		//Header + 76 (from linetax) = 17 + 76 = 93
		{
			$this->insertPageBreak();
		}
		$this->pdf->SetFont("Helvetica","B",10);
		if ($col6=="" and $col5==""){ //set width of columns (manually, cause not like browser dynamical change of width when too big)
			$this->width = $this->getColWidth(515,array($col1,40,2,0),array($col2,80,3,0),array($col3,90,0,0),array($col4,90,0,0));
		}else{
			$this->width = $this->getColWidth(515,array($col1,120,1,0),array($col2,15,0,2),array($col3,15,0,2),
				array($col4,20,0,2),array($col5,20,0,2),array($col6,30,0,2));
		}
		$this->printTextLineBox($col1,$this->font,"B",10,42,$this->lp,$this->width[0]-4,30,'left bottom',14,0);
		$this->printTextLineBox($col2,$this->font,"B",10,$this->posx+4,$this->lp,$this->width[1]-4,30,'left bottom',0,0);
		$this->printTextLineBox($col3,$this->font,"B",10,$this->posx+4,$this->lp,$this->width[2]-4,30,'left bottom',0,0);
		$this->LineWidth = $this->width[0]+$this->width[1]+$this->width[2];
		if(!$col4==""){
			$this->printTextLineBox($col4,$this->font,"B",10,$this->posx+4,$this->lp,$this->width[3]-4,30,'left bottom',0,0);
			$this->LineWidth+=$this->width[3];
		}
		if(!$col5==""){
			$this->printTextLineBox($col5,$this->font,"B",10,$this->posx+4,$this->lp,$this->width[4]-4,30,'left bottom',0,0);
			$this->LineWidth+=$this->width[4];
		}
		if(!$col6==""){
			$this->printTextLineBox($col6,$this->font,"B",10,$this->posx+4,$this->lp,$this->width[5]-4,30,'left bottom',0,0);
			$this->LineWidth+=$this->width[5];
		}
		$this->lp-=2;
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line(40,$this->lp,40+$this->LineWidth,$this->lp);
		$this->lp-=1;
		
		$this->headerCol1 = $col1;
		$this->headerCol2 = $col2;
		$this->headerCol3 = $col3;
		$this->headerCol4 = $col4;
		$this->headerCol5 = $col5;    
		$this->headerCol6 = $col6;
	}

	function printLine($col1, $col2, $col3, $col4="", $col5="")
	{	// page break check
		if($this->lp < $this->footerheight + 28)	//Footer+Line+Total+Line=36+14+14=64
		{
			$this->insertPageBreak();
			$this->printHeaderLine($this->headerCol1, $this->headerCol2
						, $this->headerCol3, $this->headerCol4, $this->headerCol5);
		}
		$this->printTextLineBox($col1,$this->font,"",10,42,$this->lp,$this->width[0]-4,30,'left bottom',12,0);
		$this->printTextLineBox($col2,$this->font,"",10,$this->posx+4,$this->lp,$this->width[1]-4,30,'right bottom',0,0);
		$this->printTextLineBox($col3,$this->font,"",10,$this->posx+4,$this->lp,$this->width[2]-4,30,'right bottom',0,0);
		if(!empty($col4) || $col4 == '0'){
			$this->printTextLineBox($col4,$this->font,"",10,$this->posx+4,$this->lp,$this->width[3]-4,30,'right bottom',0,0);
		}
		if(!empty($col5) || $col5 == '0'){//what is this for?
			$this->printTextLineBox($col5,$this->font,"",10,$this->posx+4,$this->lp,$this->width[3]-4,30,'right bottom',0,0);
		}
		$this->lp-=2;
	}
    
	function printLineTax($col1, $col2, $col3, $col4="", $col5="", $assTax, $span=1)
	{	// page break check
		$lines=1;
		if($this->lp < $this->footerheight + 40)    //Footer + 2 Lines + Total_line=36+2*12+16=76
		{
			$this->insertPageBreak();
			$this->printHeaderLine($this->headerCol1, $this->headerCol2
										, $this->headerCol3, $this->headerCol4, $this->headerCol5 ,  $this->headerCol6);
			}
		if (!empty($assTax) || $assTax == '0'){
			$lines=$this->printTextFlowSimp($col1,$this->font,"",10,52,$this->lp,$this->width[0]-14,'left',0,10);
		}else{
			$lines=$this->printTextFlowSimp($col1,$this->font,"B",10,42,$this->lp,$this->width[0]-4,'left',0,10);
		}
		if ($span == 2){
			$this->printTextLineBox($col2,$this->font,"",10,$this->posx+4,$this->lp,$this->width[1]+$this->width[2]-4,30,'left bottom',0,0);
		}else {
			$this->printTextLineBox($col2,$this->font,"B",10,$this->posx+4,$this->lp,$this->width[1]-4,30,'right bottom',0,0);
			$this->printTextLineBox($col3,$this->font,"B",10,$this->posx+4,$this->lp,$this->width[2]-4,30,'right bottom',0,0);
		}
		if(!empty($col4) || $col4 == '0' || !empty($assTax) || $assTax == '0'){
			$this->printTextLineBox($col4,$this->font,"B",10,$this->posx+4,$this->lp,$this->width[3]-4,30,'right bottom',0,0);
		}
		if (!empty($assTax) || $assTax == '0'){  
			if(!empty($col5) || $col5 == '0' ){
				$this->printTextLineBox($col5,$this->font,"",10,$this->posx+4,$this->lp,$this->width[4]-4,30,'right bottom',0,0);
			}
		} elseif  (!empty($col5) || $col5 == '0' ){
			$this->printTextLineBox($col5,$this->font,"B",10,$this->posx+4,$this->lp,$this->width[4]-4,30,'right bottom',0,0);
		}
		if(!empty($assTax) || $assTax == '0' ){
			if ($span==2){
				$this->printTextLineBox($assTax .".00",$this->font,"",10,$this->posx+4,$this->lp,$this->width[4]+$this->width[5]-4,30,'right bottom',0,0);
			}else{
				$this->printTextLineBox($assTax .".00",$this->font,"",10,$this->posx+4,$this->lp,$this->width[5]-4,30,'right bottom',0,0);
			}
		}
		$this->lp-=$lines*12-10;
  }
    
	function printTotalLine($col1, $col2, $col3, $col4="", $col5="")
	{
		$this->printTextCell($col1,$this->font,"B",10,40,$this->lp,$this->width[0],14,'right center',0,0);
		$this->printTextCell($col2,$this->font,"B",10,$this->posx,$this->lp,$this->width[1],14,'right center',0,0);
		$this->printTextCell($col3,$this->font,"B",10,$this->posx,$this->lp,$this->width[2],14,'right center',0,0);
		
		if(!empty($col4) || $col4 == '0'){
			$this->printTextCell($col4,$this->font,"B",10,$this->posx,$this->lp,$this->width[3],14,'right center',0,0);
		}
		if(!empty($col5) || $col5 == '0'){
			$this->printTextCell($col5,$this->font,"B",10,$this->posx,$this->lp,$this->width[3],14,'right center',0,0);
		}
		$this->lp-=14;
	}
  
  function printTotalLineTax($col1, $col2, $col3, $col4="", $col5="",$span=1)
  {
    $this->printTextCell($col1,$this->font,"B",10,40,$this->lp,$this->width[0],14,'right center',2,0);
		$this->printTextCell($col2,$this->font,"B",10,$this->posx,$this->lp,$this->width[1],14,'right center',0,0);
		$this->printTextCell($col3,$this->font,"B",10,$this->posx,$this->lp,$this->width[2],14,'right center',0,0);
		if(!empty($col4) || $col4 == '0'){
			$this->printTextCell($col4,$this->font,"B",10,$this->posx,$this->lp,$this->width[3],14,'right center',0,0);
		}
		if(!empty($col5) || $col5 == '0'){
			if ($span==1){
				$this->printTextCell($col5.".00",$this->font,"B",10,$this->posx,$this->lp,$this->width[4],14,'right center',0,0);
			}else{
				$this->printTextCell($col5.".00",$this->font,"B",10,$this->posx,$this->lp,$this->width[3]+$this->width[4]+$this->width[5],14,'right center',0,0);
			}
		}
		if ($span==1){
			$this->printTextCell('',$this->font,"B",10,$this->posx,$this->lp,$this->width[5],14,'right center',0,0);
		}
		$this->lp-=14;
	}
} // end PRINT_Statistics

} // end PRINT_RankingList