<?
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
include ($ABSOLUTE_PATH_STUDIP . '/seminar_open.php'); // initialise Stud.IP-Session

if (!isset($element_switch)) $element_switch = 0;
if (!isset($c)) $c = 0;

switch ($element_switch){
	case 1:  // admin_dates.php Einzeltermin
		$txt_day   = 'tag';
		$txt_month = 'monat';
		$txt_year  = 'jahr';
		break;
	case 2:  // admin_dates.php alle Termine
		$txt_day   = "tag[$c]";
		$txt_month = "monat[$c]";
		$txt_year  = "jahr[$c]";
		break;
	case 4:  //admin_seminare_assi.php regelm‰ﬂige Veranstaltungen (kein Kalender)
		break;
	case 5: // admin_seminare_assi.php unregelm‰ﬂige Veranstaltungen
		$txt_day   = "term_tag[$c]";
		$txt_month = "term_monat[$c]";
		$txt_year  = "term_jahr[$c]";
		break;
	case 6: // admin_seminare_assi.php Vorbesprechung
		$txt_day   = 'vor_tag';
		$txt_month = 'vor_monat';
		$txt_year  = 'vor_jahr';
		break;
	default:
		$txt_month = 'start_month';
		$txt_day   = 'start_day';
		$txt_year  = 'start_year';
}
$title = _('Kalender');

echo <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>
<title>$title</title>
<link rel="stylesheet" type="text/css" href="{$CANONICAL_RELATIVE_PATH_STUDIP}style.css">
<script type="text/javascript">
<!--
window.setTimeout("window.close()", 120000); // Fenster automatisch wieder schlieﬂen
function insert_date (m, d, y) {
   if (opener) {
     opener.document.Formular.elements['$txt_month'].value = m;
     opener.document.Formular.elements['$txt_day'].value = (d < 10) ? '0' + d : d;
     opener.document.Formular.elements['$txt_year'].value = y;
   }
   window.close(); // bei this.close() st¸rzt Konqueror ab ...
}
-->
</script>
</head>
<body>
EOT;

require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . '/calendar_visual.inc.php');

if (!isset($atime) || !$atime)
	$atime = time();

$js['function'] = 'insert_date';

// mehr als einen Monat anzeigen?
if (isset($mcount) && $mcount > 3) {
	if ($mcount > 12) $mcount = 12;
	if ($mcount % 2 == 1) $mcount++; // nur gerade Werte erlaubt
	$mcounth = $mcount / 2;
	$atimex = getdate($atime);
	$i = 0;
	echo '<table class="blank" border=0><tr valign=top>', "\n";
	while ($i < $mcount) {
		if (($i % $mcounth == 0) && $i > 0) echo '</tr><tr valign=top>', "\n";
		echo '<td class="blank">';
		echo includeMonth(mktime(0,0,0,$atimex['mon'] + $i++,$atimex['mday'],$atimex['year']), 'javascript:void(0);//', 'NONAV', $js);
		echo '</td>';
	}
	echo '</tr>', "\n";
	// navigation arrows
	echo '<tr><td class="blank">&nbsp;<a href="',$PHP_SELF,'?atime=',mktime(0,0,0,$atimex['mon'] - $mcount,$atimex['mday'],$atimex['year']),'&mcount=',$mcount,'&element_switch=',$element_switch,'&c=',$c,'"><img border="0" src="',$CANONICAL_RELATIVE_PATH_STUDIP,'pictures/calendar_previous_double_small.gif"', tooltip($mcount . ' ' . _('Monate zur¸ck')),' border="0"></a>';
	echo '&nbsp;<a href="',$PHP_SELF,'?atime=',mktime(0,0,0,$atimex['mon'] - $mcounth,$atimex['mday'],$atimex['year']),'&mcount=',$mcount,'&element_switch=',$element_switch,'&c=',$c,'"><img border="0" src="',$CANONICAL_RELATIVE_PATH_STUDIP,'pictures/calendar_previous_small.gif"', tooltip($mcounth .' ' . _('Monate zur¸ck')),' border="0"></a></td>', "\n";
	if ($mcounth - 2 > 0) echo '<td class="blank" colspan="' , ($mcounth - 2) , '">&nbsp;</td>';
	echo '<td class="blank" align="right"><a href="',$PHP_SELF,'?atime=',mktime(0,0,0,$atimex['mon'] + $mcounth,$atimex['mday'],$atimex['year']),'&mcount=',$mcount,'&element_switch=',$element_switch,'&c=',$c,'"><img border="0" src="',$CANONICAL_RELATIVE_PATH_STUDIP,'pictures/calendar_next_small.gif"', tooltip($mcounth . ' ' . _('Monate vor')),' border="0"></a>&nbsp;', "\n";
	echo '<a href="',$PHP_SELF,'?atime=',mktime(0,0,0,$atimex['mon'] + $mcount,$atimex['mday'],$atimex['year']),'&mcount=',$mcount,'&element_switch=',$element_switch,'&c=',$c,'"><img border="0" src="',$CANONICAL_RELATIVE_PATH_STUDIP,'pictures/calendar_next_double_small.gif"', tooltip($mcount .' ' . _('Monate vor')),' border="0"></a>&nbsp;</td>', "\n";
	echo '</tr></table>', "\n";
} else {
	echo includeMonth($atime, $PHP_SELF . '?element_switch=',$element_switch,'&c=',$c,'&atime=', 'NOKW', $js);
}
echo "</body>\n</html>";

page_close();
?>
