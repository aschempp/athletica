<?php

require('./lib/cl_gui_page.lib.php');
require('./lib/cl_gui_menulist.lib.php');
require('./lib/cl_gui_dropdown.lib.php');

require('./lib/common.lib.php');
require('./lib/cl_performance.lib.php');
require('./lib/meeting.lib.php');

if(AA_connectToDB() == FALSE)	// invalid DB connection
{
    return;		// abort
}

if(AA_checkMeetingID() == FALSE) {		// no meeting selected
    return;		// abort
}

if(isset($_POST["submit"])) {
    if ($_FILES['file']['error'] > 0) {
        AA_printErrorMsg(sprintf('Fehler %s beim Import', $_FILES['file']['error']));
    } else {
        require_once 'lib/PHPExcel.php';

        $kategorieMap = array();
        $res = mysql_query("SELECT xKategorie, Geschlecht, Alterslimite FROM kategorie WHERE aktiv=1 AND Kurzname LIKE 'J%'");
        if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno().": ".mysql_error());
            return;
        }
        while($row = mysql_fetch_row($res)) {
            $kategorieMap[$row[1]][$row[2]] = $row[0];
        }

        $vereinMap = array();
        $res = mysql_query("SELECT xVerein, Name, Sortierwert FROM verein");
        if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno().": ".mysql_error());
            return;
        }
        while($row = mysql_fetch_row($res)) {
            $vereinMap[$row[1]] = $row[0];
            $vereinMap[$row[2]] = $row[0];
        }

        $wettkampfMap = array();
        $res = mysql_query("SELECT xWettkampf, xKategorie FROM wettkampf");
        if(mysql_errno() > 0){
            AA_printErrorMsg(mysql_errno().": ".mysql_error());
            return;
        }
        while($row = mysql_fetch_row($res)) {
            $wettkampfMap[$row[1]] = $row[0];
        }

        $athleten = array();

        $data = PHPExcel_IOFactory::createReader('Excel5')
            ->load($_FILES['file']['tmp_name'])
            ->getSheet(0)
            ->toArray()
        ;

        // Remove header
        unset($data[0]);

        foreach ($data as $row) {
            $alter       = date('Y') - $row[2];
            $meetingId   = (int) $_COOKIE['meeting_id'];
            $kategorieId = $kategorieMap[$row[3]][$alter];
            $wettkampfId = $wettkampfMap[$kategorieId];

            if (!$kategorieId) {
                AA_printErrorMsg(sprintf('Keine Kategorie für Jahrgang %s (Altersgrenze %s)', $row[2], $alter));
                continue;
            }

            if (!$wettkampfId) {
                AA_printErrorMsg(sprintf('Disziplin für Jahrgang %s fehlt', $row[2]));
                continue;
            }

            if (!isset($vereinMap[$row[4]])) {
                mysql_query("INSERT INTO verein SET Name='{$row[4]}', Sortierwert='{$row[4]}'");
                $vereinMap[$row[4]] = mysql_insert_id();
            }

            $res = mysql_query("SELECT xAthlet FROM athlet WHERE Name='{$row[1]}' AND Vorname='{$row[0]}' AND Jahrgang='{$row[2]}' AND Geschlecht='{$row[3]}'");

            if (mysql_num_rows($res) > 0) {
                $athletId = mysql_fetch_row($res);
                $athletId = $athletId[0];
            } else {
                mysql_query("
                    INSERT INTO athlet 
                    SET
                        Name = '{$row[1]}',
                        Vorname = '{$row[0]}',
                        Jahrgang = '{$row[2]}',
                        Geschlecht = '{$row[3]}',
                        Lizenztyp = 3,
                        xVerein = {$vereinMap[$row[4]]}
                ");

                if (mysql_errno() > 0) {
                    AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                    continue;
                }

                $athletId = mysql_insert_id();
            }

            // Check for duplicates
            if ($athleten[$athletId]) {
                AA_printErrorMsg(sprintf('Athlet "%s %s" ist mehrfach im Excel-Dokument vorhanden!', $row[0], $row[1]));
            } else {
                $athleten[$athletId] = true;
            }

            mysql_query("
                INSERT INTO anmeldung 
                SET
                    xAthlet = $athletId,
                    Startnummer = (IFNULL((SELECT MAX(Startnummer) FROM (SELECT * FROM anmeldung) AS a), 0) + 1),
                    xMeeting = $meetingId,
                    xKategorie = $kategorieId
            ");

            if (mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                continue;
            }

            $anmeldungId = mysql_insert_id();

            mysql_query("INSERT INTO start SET xWettkampf=$wettkampfId, xAnmeldung=$anmeldungId");

            if (mysql_errno() > 0) {
                AA_printErrorMsg(mysql_errno() . ": " . mysql_error());
                continue;
            }
        }
    }
}


$page = new GUI_Page('meeting_entries_import',false,'stylesheet_small-fonts.css');
$page->startPage();
?>

<form name="layout" method="POST" action="meeting_entries_import.php" enctype="multipart/form-data">
<table class='dialog'>
<tr>
<th class='dialog'>Anmeldungen importieren</th>
</tr>
<tr>
    <td class='forms'>
        <input type="file" name="file">
    </td>
</tr>
</table>

<br>
<input type="submit" name="submit" value="Import starten">
</form>

<?php
$page->endPage();
