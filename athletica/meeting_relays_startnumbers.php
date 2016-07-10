<?php

/**********
 *
 *    meeting_relays_startnumbers.php
 *    --------------------------------
 *    
 */            
  
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_page.lib.php');

require('./lib/common.lib.php');

if(AA_connectToDB() == FALSE)    {                // invalid DB connection
    return;        // abort
}

if(empty($_COOKIE['meeting_id'])) {
    AA_printErrorMsg($GLOBALS['strNoMeetingSelected']);
}  

$max_startnr = 0;  
$nbr = 0;  
$limit = 0;  

?>

<?php

if($_GET['arg'] == 'assign')
{
    if ($_GET['sort']!="del")        // assign startnumbers
    {    
        // sort argument
        $argument="v.Sortierwert, k.Anzeige, d.Anzeige";
        if ($_GET['sort']=="club") {
            $argument="v.Sortierwert, k.Anzeige, d.Anzeige";           
        } 
        elseif ($_GET['sort']=="cat") {
                $argument="k.Anzeige, d.Anzeige, v.Sortierwert";   
        }    
                      
            //
            // Read relays
            //
            
            mysql_query("
                LOCK TABLES                      
                     kategorie AS k READ
                    , disziplin_" . $_COOKIE['language'] . " AS d READ  
                    , verein AS v READ  
                    , wettkampf AS w READ 
                    , start AS s READ   
                    , staffel WRITE
                    , staffel AS st READ   
            ");  
          
            $sql="SELECT
                    st.xStaffel 
                FROM
                    start AS s
                    LEFT JOIN staffel AS st ON (st.xStaffel = s.xStaffel)
                    LEFT JOIN kategorie AS k ON (k.xKategorie = st.xKategorie)
                    LEFT JOIN wettkampf AS w ON (w.xWettkampf = s.xWettkampf)
                    LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d ON (d.xDisziplin = w.xDisziplin)   
                    LEFT JOIN verein AS v ON (v.xVerein = st.xVerein)   
                WHERE 
                    st.xMeeting = " . $_COOKIE['meeting_id'] . "                    
                ORDER BY
                        $argument";
           
            $result = mysql_query($sql); 
          
            if(mysql_errno() > 0)        // DB error
            {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
            }
            else if(mysql_num_rows($result) > 0)  // data found
            {              
              $first = true;  
              
              // Assign startnumbers
              while ($row = mysql_fetch_row($result))
              {     
                    if ($first){                         
                        if (!empty($_GET["of"]) ){
                            $nbr = $_GET["of"];                               
                        }                          
                        else {
                           $nbr = 0;   
                        } 
                                 
                        $limit = $_GET["to"];    
                        $nbr=($nbr==0 && $limit>0)?1:$nbr;  
                        $first = false;
                    }
                    else {
                         if(($limit > 0 && $nbr > $limit) || $limit == 0){
                            $nbr = 0;
                            $limit = 0;
                         }   
                    } 
                
                    mysql_query("UPDATE staffel SET
                                        Startnummer='$nbr'
                                        WHERE xStaffel = $row[0]
                                        ");   
                    $nbr++;     
               
                    if(mysql_errno() > 0) {
                        AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    }  
              }
              mysql_free_result($result);
            }                        // ET DB error
        mysql_query("UNLOCK TABLES");    
    
    }
    else        // delete startnumbers
    {
        mysql_query("LOCK TABLE staffel WRITE");

          mysql_query("UPDATE staffel SET
                                Startnummer = 0
                                WHERE xMeeting=" . $_COOKIE['meeting_id']
                                );

        if(mysql_errno() > 0)
        {
          AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
        }

        mysql_query("UNLOCK TABLES");
    }
}

//
// show dialog 
//

$page = new GUI_Page('meeting_relays_startnumbers');
$page->startPage();
$page->printPageTitle($strAssignStartnumbers);

if($_GET['arg'] == 'assign')    // refresh list
{
    ?>
    <script>
        window.open("meeting_relaylist.php", "list")
    </script>
    <?php
}
?>

<script type="text/javascript">     

function check_rounds(){  
        check = confirm("<?php echo $strStartNrConfirm ?>");
        return check;   
}
         

  

</script>

<form action='meeting_relays_startnumbers.php' method='get' id='startnr'>
<input type='hidden' name='arg' value='assign'>
<table class='dialog'>
<tr>
    <th class='dialog' colspan="4"><?php echo $strSortBy; ?></th>
   
</tr>
<tr>
    <td class='dialog'>
        <input type='radio' name='sort' id='club' value='club' checked='checked' />   
        <?php echo $strClub; ?>
    </td> 
</tr>  

<tr>
    <td class='dialog'>
        <input type='radio' name='sort' id='cat' value='cat' />
        <?php echo $strCategory; ?></input>    
    </td>
</tr>    

<?php

$i = 0;
// get all relays in contest

 $sql="SELECT COUNT(*)   
                FROM
                    start AS s
                    LEFT JOIN staffel AS st ON (st.xStaffel = s.xStaffel)
                    LEFT JOIN kategorie AS k ON (k.xKategorie = st.xKategorie)
                    LEFT JOIN wettkampf AS w ON (w.xWettkampf = s.xWettkampf)
                    LEFT JOIN disziplin_" . $_COOKIE['language'] ." AS d ON (d.xDisziplin = w.xDisziplin)   
                    LEFT JOIN verein AS v ON (v.xVerein = st.xVerein)   
                WHERE 
                    st.xMeeting = " . $_COOKIE['meeting_id'];   
 
$res = mysql_query($sql);
             
if(mysql_errno() > 0){
    AA_printErrorMsg(mysql_errno().": ".mysql_error());
}else{
     if (mysql_num_rows($res) > 0){ 
           $row=mysql_fetch_array($res);     
           $max_startnr = $row[0];     
           
        ?>
       
        <tr>
            <th class='dialog' ></th> 
            <th class='dialog' > <?php echo $strOf ?></th>
            <th class='dialog' ><?php echo $strTo ?></th>
            <th class='dialog' ><?php echo $strMax; ?>  </th> 
        </tr>       
        <tr>
            <th class='dialog' ><?php echo $strRelays; ?></th>            
            <td class='forms'> <input type="text" size="3" value="0" name="of" ></td>
            <td class='forms_right'><input type="text" size="3" value="0" name="to" ></td>   
            <td class='forms_right_grey'><?php echo $max_startnr; ?></td>  
        </tr>
        
        <?php    
    }      
}
?>    
 
<tr>
    <td class='dialog' colspan = '13'>
        <hr>
        <input type='radio' name='sort' value='del'>
            <?php echo $strDeleteStartnumbers; ?></input></td>
</tr>
</table>

<p/>

<table>
<tr>
    <td>
        <button type='submit' onclick="return check_rounds();">
            <?php echo $strAssign; ?>
          </button>
    </td>
</tr>
</table>

</form>
 
</body>
</html>
