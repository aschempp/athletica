<?php
/**
* common functions
*
* @package Athletica Technical Client
* @subpackage Common
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

/**
* prints out a box
*
* @param string $message the box' content
* @param string $title the box' title
* @param string $type box' type (success, error, info, warning, ...)
* @param integer $padding_top padding in pixels to be printed before the box
* @param integer $padding_bottom padding in pixels to be appended to the box
* @param boolean $message_is_file include a file instead of printing a message
* @return NULL
*/
function box($message, $title = '', $type = 'success', $padding_top = 0, $padding_bottom = 0, $message_is_file = false){
	global $glb_box_message_id;

	$glb_box_message_id++;

	$title = ($title=='') ? '&nbsp;' : $title;
	$hidden = (strstr($type, '_hidden')!==FALSE) ? ' hidden' : '';
	$type = str_replace('_hidden', '', $type);

	// +++ set the onclick action for filters
	$onclick = '';
	if($type=='filter'){
		$onclick = ' onclick="show_hide_filter('.$glb_box_message_id.');"';
	}
	// --- set the onclick action for filters
	?>
	<div style="padding-top: <?=$padding_top?>px; padding-bottom: <?=$padding_bottom?>px;">
		<div id="box_<?=$type?>">
			<div class="title"<?=$onclick?>>
				<div class="icon">&nbsp;</div>
				<?=$title?>
			</div>
			<div id="box_message_<?=$glb_box_message_id?>" class="message<?=$hidden?>">
				<?php
				if($message_is_file && file_exists($message)){
					include($message);
				} else {
					?>
					<?=$message?>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<?php

	return;
}

function csv_output($content, $filename = NULL){
	$filename = (is_null($filename)) ? 'csv_export_'.date('Y-m-d_H-i-s', time()) : $filename;
	$filename = $filename.((strlen($filename)<=4 || substr($filename, -4)!=='.csv') ? '.csv' : '');

	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Expires: 0');
	header('Content-type: text/csv');
	header('Content-Disposition: attachment; filename="'.$filename.'"');

	echo $content;
	exit();
}

/**
* prepares a string for the use in a csv file
*
* @param string $string the string to be prepared
* @return string the prepared string
*/
function csv_prepare($string){
	$return = $string;

	$return = str_replace('"', '""', $return);
	$return = utf8_decode($return);

	return $return;
}

/**
* formats a datetime according to the formatting rules; translates day and month names
*
* @param string $formatting rules
* @param mixed $time as string or timestamp
* @param string $lang 
* @return string formatted date
*/
function datetime_format($format, $time, $lang = ''){
	global $lg, $lg_translation;

	$return = '';

	if(!is_null($time)){
		if(is_string($time)){
			$time = strtotime($time);
		}

		$return = date($format, $time);
	}

	return $return;
}

/**
* converts a money value from float to integer
*
* @param float $float money value
* @return integer converted money value
*/
function float2int($float){
	return (int)($float * 100);
}

/**
* generates a random string using a default or a user specified pattern
*
* @param length $length the string's target length
* @param string $pattern a string with all allowed characters
* @return string random string
*/
function generate_random_string($length = 8, $pattern = NULL){
	$return = '';

	$pattern = (!is_null($pattern)) ? $pattern : 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

	while(strlen($return)<$length){
		$index = rand(0, (strlen($pattern) - 1));

		$return .= $pattern[$index];
	}

	return $return;
}

/**
* gets the file extension for a filename
*
* @param string $filename the filename
* @param boolean $include_dot specifies if the dot is included in the result
* @return string file extension
*/
function get_extension($filename, $include_dot = FALSE){
	$return = '';

	$strrpos = strrpos($filename, '.');
	$strrpos = ($include_dot) ? $strrpos : ($strrpos + 1);
	$return = substr($filename, $strrpos);

	return $return;
}

/**
* gets the MIME type for a filename
*
* @param string $filename the filename
* @return string MIME type
*/
function get_MIME($filename){
	$return = 'unknown/unknown';

	$extension = get_extension($filename);

	switch($extension){
		case 'ai': $return = 'application/postscript'; break;
		case 'aif': $return = 'audio/x-aiff'; break;
		case 'aifc': $return = 'audio/x-aiff'; break;
		case 'aiff': $return = 'audio/x-aiff'; break;
		case 'asc': $return = 'text/plain'; break;
		case 'au': $return = 'audio/basic'; break;
		case 'avi': $return = 'video/x-msvideo'; break;

		case 'bcpio': $return = 'application/x-bcpio'; break;
		case 'bin': $return = 'application/octet-stream'; break;

		case 'c': $return = 'text/plain'; break;
		case 'cc': $return = 'text/plain'; break;
		case 'ccad': $return = 'application/clariscad'; break;
		case 'cdf': $return = 'application/x-netcdf'; break;
		case 'class': $return = 'application/octet-stream'; break;
		case 'cpio': $return = 'application/x-cpio'; break;
		case 'cpt': $return = 'application/mac-compactpro'; break;
		case 'csh': $return = 'application/x-csh'; break;
		case 'css': $return = 'text/css'; break;

		case 'dcr': $return = 'application/x-director'; break;
		case 'dir': $return = 'application/x-director'; break;
		case 'dms': $return = 'application/octet-stream'; break;
		case 'doc':
		case 'docx':
		case 'dot':
		case 'dotx': $return = 'application/msword'; break;
		case 'drw': $return = 'application/drafting'; break;
		case 'dvi': $return = 'application/x-dvi'; break;
		case 'dwg': $return = 'application/acad'; break;
		case 'dxf': $return = 'application/dxf'; break;
		case 'dxr': $return = 'application/x-director'; break;

		case 'eps': $return = 'application/postscript'; break;
		case 'etx': $return = 'text/x-setext'; break;
		case 'exe': $return = 'application/octet-stream'; break;
		case 'ez': $return = 'application/andrew-inset'; break;

		case 'f': $return = 'text/plain'; break;
		case 'f90': $return = 'text/plain'; break;
		case 'fli': $return = 'video/x-fli'; break;

		case 'gif': $return = 'image/gif'; break;
		case 'gtar': $return = 'application/x-gtar'; break;
		case 'gz': $return = 'application/x-gzip'; break;

		case 'h': $return = 'text/plain'; break;
		case 'hdf': $return = 'application/x-hdf'; break;
		case 'hh': $return = 'text/plain'; break;
		case 'hqx': $return = 'application/mac-binhex40'; break;
		case 'htm': $return = 'text/html'; break;
		case 'html': $return = 'text/html'; break;

		case 'ice': $return = 'x-conference/x-cooltalk'; break;
		case 'ief': $return = 'image/ief'; break;
		case 'iges': $return = 'model/iges'; break;
		case 'igs': $return = 'model/iges'; break;
		case 'ips': $return = 'application/x-ipscript'; break;
		case 'ipx': $return = 'application/x-ipix'; break;

		case 'jpe': $return = 'image/jpeg'; break;
		case 'jpeg': $return = 'image/jpeg'; break;
		case 'jpg': $return = 'image/jpeg'; break;
		case 'js': $return = 'application/x-javascript'; break;

		case 'kar': $return = 'audio/midi'; break;

		case 'latex': $return = 'application/x-latex'; break;
		case 'lha': $return = 'application/octet-stream'; break;
		case 'lsp': $return = 'application/x-lisp'; break;
		case 'lzh': $return = 'application/octet-stream'; break;

		case 'm': $return = 'text/plain'; break;
		case 'man': $return = 'application/x-troff-man'; break;
		case 'me': $return = 'application/x-troff-me'; break;
		case 'mesh': $return = 'model/mesh'; break;
		case 'mid': $return = 'audio/midi'; break;
		case 'midi': $return = 'audio/midi'; break;
		case 'mif': $return = 'application/vnd.mif'; break;
		case 'mime': $return = 'www/mime'; break;
		case 'mov': $return = 'video/quicktime'; break;
		case 'movie': $return = 'video/x-sgi-movie'; break;
		case 'mp2': $return = 'audio/mpeg'; break;
		case 'mp3': $return = 'audio/mpeg'; break;
		case 'mpe': $return = 'video/mpeg'; break;
		case 'mpeg': $return = 'video/mpeg'; break;
		case 'mpg': $return = 'video/mpeg'; break;
		case 'mpga': $return = 'audio/mpeg'; break;
		case 'ms': $return = 'application/x-troff-ms'; break;
		case 'msh': $return = 'model/mesh'; break;

		case 'nc': $return = 'application/x-netcdf'; break;

		case 'oda': $return = 'application/oda'; break;

		case 'pbm': $return = 'image/x-portable-bitmap'; break;
		case 'pdb': $return = 'chemical/x-pdb'; break;
		case 'pdf': $return = 'application/pdf'; break;
		case 'pgm': $return = 'image/x-portable-graymap'; break;
		case 'pgn': $return = 'application/x-chess-pgn'; break;
		case 'png': $return = 'image/png'; break;
		case 'pnm': $return = 'image/x-portable-anymap'; break;
		case 'pot': $return = 'application/mspowerpoint'; break;
		case 'ppm': $return = 'image/x-portable-pixmap'; break;
		case 'pps':
		case 'ppsx':
		case 'ppt':
		case 'pptx':
		case 'ppz':
		case 'ppzx': $return = 'application/mspowerpoint'; break;
		case 'pre': $return = 'application/x-freelance'; break;
		case 'prt': $return = 'application/pro_eng'; break;
		case 'ps': $return = 'application/postscript'; break;

		case 'qt': $return = 'video/quicktime'; break;

		case 'ra': $return = 'audio/x-realaudio'; break;
		case 'ram': $return = 'audio/x-pn-realaudio'; break;
		case 'ras': $return = 'image/cmu-raster'; break;
		case 'rgb': $return = 'image/x-rgb'; break;
		case 'rm': $return = 'audio/x-pn-realaudio'; break;
		case 'roff': $return = 'application/x-troff'; break;
		case 'rpm': $return = 'audio/x-pn-realaudio-plugin'; break;
		case 'rtf': $return = 'text/rtf'; break;
		case 'rtx': $return = 'text/richtext'; break;

		case 'scm': $return = 'application/x-lotusscreencam'; break;
		case 'set': $return = 'application/set'; break;
		case 'sgm': $return = 'text/sgml'; break;
		case 'sgml': $return = 'text/sgml'; break;
		case 'sh': $return = 'application/x-sh'; break;
		case 'shar': $return = 'application/x-shar'; break;
		case 'silo': $return = 'model/mesh'; break;
		case 'sit': $return = 'application/x-stuffit'; break;
		case 'skd': $return = 'application/x-koan'; break;
		case 'skm': $return = 'application/x-koan'; break;
		case 'skp': $return = 'application/x-koan'; break;
		case 'skt': $return = 'application/x-koan'; break;
		case 'smi': $return = 'application/smil'; break;
		case 'smil': $return = 'application/smil'; break;
		case 'snd': $return = 'audio/basic'; break;
		case 'sol': $return = 'application/solids'; break;
		case 'spl': $return = 'application/x-futuresplash'; break;
		case 'src': $return = 'application/x-wais-source'; break;
		case 'step': $return = 'application/STEP'; break;
		case 'stl': $return = 'application/SLA'; break;
		case 'stp': $return = 'application/STEP'; break;
		case 'sv4cpio': $return = 'application/x-sv4cpio'; break;
		case 'sv4crc': $return = 'application/x-sv4crc'; break;
		case 'swf': $return = 'application/x-shockwave-flash'; break;

		case 't': $return = 'application/x-troff'; break;
		case 'tar': $return = 'application/x-tar'; break;
		case 'tcl': $return = 'application/x-tcl'; break;
		case 'tex': $return = 'application/x-tex'; break;
		case 'texi': $return = 'application/x-texinfo'; break;
		case 'texinfo': $return = 'application/x-texinfo'; break;
		case 'tif': $return = 'image/tiff'; break;
		case 'tiff': $return = 'image/tiff'; break;
		case 'tr': $return = 'application/x-troff'; break;
		case 'tsi': $return = 'audio/TSP-audio'; break;
		case 'tsp': $return = 'application/dsptype'; break;
		case 'tsv': $return = 'text/tab-separated-values'; break;
		case 'txt': $return = 'text/plain'; break;

		case 'unv': $return = 'application/i-deas'; break;
		case 'ustar': $return = 'application/x-ustar'; break;

		case 'vcd': $return = 'application/x-cdlink'; break;
		case 'vda': $return = 'application/vda'; break;
		case 'viv': $return = 'video/vnd.vivo'; break;
		case 'vivo': $return = 'video/vnd.vivo'; break;
		case 'vrml': $return = 'model/vrml'; break;

		case 'wav': $return = 'audio/x-wav'; break;
		case 'wrl': $return = 'model/vrml'; break;
		case 'xbm': $return = 'image/x-xbitmap'; break;

		case 'xlc':
		case 'xlcx':
		case 'xll':
		case 'xllx':
		case 'xlm':
		case 'xlmx':
		case 'xls':
		case 'xlsx':
		case 'xlw':
		case 'xlwx': $return = 'application/vnd.ms-excel'; break;
		case 'xml': $return = 'text/xml'; break;
		case 'xpm': $return = 'image/x-xpixmap'; break;
		case 'xwd': $return = 'image/x-xwindowdump'; break;
		case 'xyz': $return = 'chemical/x-pdb'; break;

		case 'zip': $return = 'application/zip'; break;

		default: $return = 'unknown/unknown'; break;
	}

	return $return;
}

/**
* gets the current URI and removes given GET variables
*
* @param string $remove_GET GET variables to be removed, separated by , or |
* @return string URI with or without the GET variables
*/
function get_URI($remove_GET = ''){
	$return = '';

	// replace all pipes (|) by a comma and create an array
	$remove_GET = string_to_array($remove_GET, ',|');

	// only check GET variables if they don't have to be removed all
	if(!in_array('{ALL}', $remove_GET)){
		foreach($_GET as $key => $value){
			// if the actual variable must not be removed, add them to the URI
			if(!in_array($key, $remove_GET)){
				$return .= (($return!='') ? '&' : '?').$key.'='.$value;
			}
		}
	}

	// get the filename and attach the URI
	$filename = substr($_SERVER['PHP_SELF'], (strrpos($_SERVER['PHP_SELF'], '/') + 1));
	$return = $filename.$return;

	return $return;
}

/**
* prepares a string for the use in html (forms, etc.)
*
* @param mixed $object the string or string containing array to be prepared
* @return string the prepared object
*/
function html_prepare($object){
	$return = $object;

	if(is_array($return)){
		foreach($return as $key => $value){
			$return[$key] = html_prepare($value);
		}
	} else {
		$return = htmlspecialchars($return);
		$return = str_replace(array("\r", "\n", "\l"), '', nl2br($return));
	}

	return $return;
}

/**
* converts a money value from integer to float
*
* @param integer $integer money value
* @return int converted money value
*/
function int2float($integer){
	return number_format(($integer / 100), 2, '.', '');
}

/**
* prepares a string for the use in javascript, escaping quotes
*
* @param string $string the string to be prepared
* @param boolean $double_quotes defines if single or double quotes are parsed
* @param boolean $is_html whether the current string is a HTML code (convert new lines to <br />) or JS (convert new lines to JS new lines)
* @return string the prepared string
*/
function javascript_prepare($string, $double_quotes = FALSE, $is_html = FALSE){
	$return = $string;

	$search = ($double_quotes) ? '"' : "'";
	$replace = ($double_quotes) ? '\"' : "\'";

	$return = str_replace($search, $replace, $return);
	$return = str_replace(array("\r", "\n", "\l"), '', nl2br($return));

	if(!$is_html){
		$return = str_replace('<br />', '\n', $return);
	}

	return $return;
}

/**
* forwards the browser to a given file
*
* @param string $location the location to forward to
* @return NULL
*/
function location($location = ''){
	// set index.php as standard location if no location is set
	$location = ($location!='') ? $location : 'index.php';

	header('Location: '.$location);
	exit();

	return;
}

/**
* prints a formatted array
*
* @param mixed $content the array to be printed. Can also be a string or a number
* @return NULL
*/
function print_arr($content){
	echo('<pre>');

	if(is_array($content)){
		print_r($content);
	} elseif(is_string($content)){
		echo($content);
	}
	else{
		var_dump($content);
	}

	echo('</pre>');

	return;
}


/**
* formats a value of bytes
*
* @param integer $bytes
* @param integer $decimals number of decimals
* @param string $entity the entity (KB, MB, GB). NULL = the greatest possible
* @return string formatted byte value
*/
function bytes_format($bytes, $decimals = 0, $entity = NULL){
	global $lg;

	$return = '';
	$entity_string = $lg['ENTITY_BYTES'];

	// +++ check if $bytes is a filename
	if(is_string($bytes)){
		$bytes = (@file_exists($bytes)) ? @filesize($bytes) : 0;
	}
	// +++ check if $bytes is a filename

	if(is_null($entity)){
		$entities = array('', $lg['ENTITY_KB'], $lg['ENTITY_MB'], $lg['ENTITY_GB'], $lg['ENTITY_TB']);

		if($bytes>0){
			for($pow=4; $pow>=0; $pow--){
				$temp_value = ($pow>0) ? ($bytes / pow(1024, $pow)) : $bytes;

				if($temp_value>=1){
					$entity_string = ($pow==0) ? (($bytes==1) ? $lg['ENTITY_BYTE'] : $lg['ENTITY_BYTES']) : $entities[$pow];
					$return = round($temp_value, $decimals);
					break;
				}
			}
		} else {
			$return = $bytes;
		}
	} else {
		$dividend = 1;
		switch($entity){
			case 'T':
			case 'TB':
				$dividend = pow(1024, 4);
				$entity_string = $lg['ENTITY_TB'];
				break;
			case 'G':
			case 'GB':
				$dividend = pow(1024, 3);
				$entity_string = $lg['ENTITY_GB'];
				break;
			case 'M':
			case 'MB':
				$dividend = pow(1024, 2);
				$entity_string = $lg['ENTITY_MB'];
				break;
			case 'K':
			case 'KB':
				$dividend = 1024;
				$entity_string = $lg['ENTITY_KB'];
				break;
			default:
				$dividend = 1;
				$entity_string = ($bytes==1) ? $lg['ENTITY_BYTE'] : $lg['ENTITY_BYTES'];
				break;
		}

		$return = round(($bytes / $dividend), $decimals);
	}

	$return .= ' '.$entity_string;

	return $return;
}


/**
* converts a XByte (KB, MB, GB, ...) value to bytes
*
* @param mixed $Xbyte the value as string or int
* @return integer number of bytes
*/
function Xbyte_to_bytes($Xbyte){
	$return = 0;

	$multiplicator = 0;
	if(strstr($Xbyte, 'K')!==FALSE){
		$multiplicator = 1024;
	} elseif(strstr($Xbyte, 'M')!==FALSE){
		$multiplicator = pow(1024, 2);
	} elseif(strstr($Xbyte, 'G')!==FALSE){
		$multiplicator = pow(1024, 3);
	} elseif(strstr($Xbyte, 'T')!==FALSE){
		$multiplicator = pow(1024, 4);
	}

	$return = (($Xbyte) * $multiplicator);

	return $return;
}

/**
* creates a directory recursively
*
* @param string $path the directory's full path
* @return NULL
*/
function create_directory($path){
	$path = explode('/', $path);

	$path_current = '';
	foreach($path as $part){
		if($part==''){
			continue;
		}

		$path_current .= (($path_current!='') ? '/' : '').$part;

		if(!is_dir($path_current)){
			@mkdir($path_current, 0777);
			@chmod($path_current, 0777);
		}
	}

	return NULL;
}

/**
* removes a directory and all its content recursively
*
* @param string $path the directory's full path
* @return NULL
*/
function remove_directory($path){
	$dir = dir($path);
	while(($entry = $dir->read())!==FALSE){
		if($entry=='.' || $entry=='..'){
			continue;
		}

		if(is_file($path.$entry)){
			@unlink($path.$entry);
		} else {
			remove_directory($path.$entry.'/');
		}
	}
	$dir->close();

	@rmdir($path);

	return NULL;
}

/**
* recursively removes a directory and all its content
*
* @param string $path the directory's full path
* @return NULL
*/
function rrmdir($path){
	$dir = dir($path);
	while(($entry = $dir->read())!==FALSE){
		if($entry=='.' || $entry=='..'){
			continue;
		}

		if(is_file($path.$entry)){
			@unlink($path.$entry);
		} else {
			remove_directory($path.$entry.'/');
		}
	}
	$dir->close();

	@rmdir($path);

	return NULL;
}

/**
* converts a string with multiple values to an array
*
* @param string $string string
* @param string $separators valid separators for this string
* @param boolean $strip_doubles remove double values
* @param boolean $strip_empty remove empty values
* @param array $old_array an existent array to complete
* @param boolean $insert_before insert new elements at the beginning or at the end of the array
* @return array
*/
function string_to_array($string, $separators = ',;|', $strip_doubles = true, $strip_empty = false, $old_array = array(), $insert_before = false){
	$return = $old_array;

	if($string!='' && $separators!=''){
		$separator = $separators{0};

		for($char=0; $char<strlen($separators); $char++){
			$string = str_replace($separators{$char}, $separator, $string);
		}

		$array_tmp = explode($separator, $string);

		foreach($array_tmp as $tmp){
			if(($strip_doubles && in_array($tmp, $return)) || ($strip_empty && trim($tmp)=='')){
				continue;
			}

			$tmp = trim($tmp);

			if($insert_before){
				array_unshift($return, $tmp);
			} else {
				$return[] = $tmp;
			}
		}
	}

	return $return;
}

/**
* saves a value in a serialized cookie
*
* @param string $key
* @param string $value
*/
function set_cookie($key, $value){
	$cookie = (isset($_COOKIE[CFG_COOKIE])) ? unserialize($_COOKIE[CFG_COOKIE]) : array();
	$cookie[$key] = $value;

	setcookie(CFG_COOKIE, serialize($cookie), strtotime('+1 year', time()));

	return;
}

/**
* recursively encodes an array to utf8
* alters the array directly
* @author Davide De Santis
*
* @param array $array
* @return no return value
*/
function utf8_rencode(&$array){
	if(is_array($array)){
		foreach($array as $key => &$value){
			utf8_rencode($value);
		}
	} elseif(gettype($array)=='string'){
		$array = utf8_encode($array);
	}
}

/**
* recursively decodes an array from utf8
* alters the array directly
* @author Davide De Santis
*
* @param array $array
* @return no return value
*/
function utf8_rdecode(&$array){
	if(is_array($array)){
		foreach($array as $key => &$value){
			utf8_rdecode($value);
		}
	} elseif(gettype($array)=='string'){
		$array = utf8_decode($array);
	}
}

/**
* comments for the set of functions addZeros/format_limit_time:
*
* converts into correct format for TIMELIMIT
* @author Stefan Bauer
*
*/
function addZeros($zahl){
	if ($zahl<=9){
		return $zahl * 100;
	} elseif ($zahl<=99){
		return $zahl * 10;
	} else {
		return $zahl;
	}
}

function format_limit_time($limite){
	//für sek.hundertstel < 10 sek
	if (preg_match('/^([0-9])([.:-])(\d{1,3})$/',$limite,$treffer)){
		return '00:00:0'.$treffer[1].'.'. addZeros($treffer[3]);continue;
	}

	//für sek.hundertstel > 10 AND < 60 sek
	if (preg_match('/^([0-5][0-9])([.:-])(\d{1,3})$/',$limite,$treffer)){
		return '00:00:'.$treffer[1].'.'. addZeros($treffer[3]);continue;
	}

	//SONDERFALL: für sek.hundertstel > 60 AND < 70 sek (zeit muss umgerechnet werden)
	if (preg_match('/^(6[0-9])([.:-])(\d{1,3})$/',$limite,$treffer)){
		return '00:01:0'. ($treffer[1]-60).'.'. addZeros($treffer[3]);
	}

	//SONDERFALL für sek.hundertstel >= 70 AND < 100 sek (zeit muss umgerechnet werden)
	if (preg_match('/^([7-9][0-9])([.:-])(\d{1,3})$/',$limite,$treffer)){
		return '00:01:'. ($treffer[1]-60).'.'. addZeros($treffer[3]);
	}

	//für min.sek.hundertstel
	if (preg_match('/^([0-9])([.:-])([0-5][0-9])([.:-])(\d{1,3})$/',$limite,$treffer)){
		return '00:0' . $treffer[1] .':'.$treffer[3].'.'. addZeros($treffer[5]);
	}

	//für minmin.sek.hundertstel
	if (preg_match('/^([0-5][0-9])([.:-])([0-5][0-9])([.:-])(\d{1,3})$/',$limite,$treffer)){
		return '00:' . $treffer[1] .':'.$treffer[3].'.'. addZeros($treffer[5]);
	}

	//für hh.minmin.sek.hundertstel
	//(naja, regex stimmt nicht ganz, 24-29 stunden geht durch... aber das ist nun nicht das wichtigste und schlimmste...)
	if (preg_match('/^([0-2][0-9])([.:-])([0-5][0-9])([.:-])([0-5][0-9])([.:-])(\d{1,3})$/',$limite,$treffer)){
		return  $treffer[1] .':'.$treffer[3].':'. $treffer[5].'.'. addZeros($treffer[7]);
	}

	//für hh.minmin.sek.tausendstel
	//(naja, regex stimmt nicht ganz, 24-29 stunden geht durch... aber das ist nun nicht das wichtigste und schlimmste...)
	if (preg_match('/^([0-2][0-9])([.:-])([0-5][0-9])([.:-])([0-5][0-9])([.:-])(\d{1,3})$/',$limite,$treffer)){
		return  $treffer[1] .':'.$treffer[3].':'. $treffer[5].'.'. addZeros($treffer[7]);
	}

	//no match
	return false;
}

?>