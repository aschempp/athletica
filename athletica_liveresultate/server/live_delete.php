<?php   
/**********
 *
 *    live_delete.php
 *    ----------------
 *    
 */ 
 
  $path = dirname($_SERVER['SCRIPT_FILENAME']);   
  $upload_dir = dirname($_SERVER['SCRIPT_FILENAME']); 
           
  //delete files      
  $dir = opendir($path); 
 
  while($file = readdir($dir)) {  
        if(is_file($path."/".$file)){   
            list($name, $type) = split("[.]",$file);
            if ($type == 'php' && $name != 'index' && $name != 'live_delete'){       
                unlink($upload_dir ."/" . $file);  
            }  
        }  
  }  
    
?>
