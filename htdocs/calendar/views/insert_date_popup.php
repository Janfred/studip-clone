<?
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
include ($ABSOLUTE_PATH_STUDIP . '/seminar_open.php'); // initialise Stud.IP-Session

$element_switch = (isset($_REQUEST['element_switch']))? $_REQUEST['element_switch']:0;
$c = (isset($_REQUEST['c']))? $_REQUEST['c'] : 0;
$mcount = (isset($_REQUEST['mcount']))? $_REQUEST['mcount'] : 1;
$ss = (isset($_REQUEST['ss']))? sprintf('%02d',$_REQUEST['ss']):'';
$sm = (isset($_REQUEST['sm']))? sprintf('%02d',$_REQUEST['sm']):'';
$es = (isset($_REQUEST['es']))? sprintf('%02d',$_REQUEST['es']):'';
$em = (isset($_REQUEST['em']))? sprintf('%02d',$_REQUEST['em']):'';
$q = ($ss !== '')? "&ss=$ss&sm=$sm&es=$es&em=$em":'';

$zz = array (
	array ('07','45','09','15'),
	array ('09','30','11','00'),
	array ('11','15','12','45'),
	array ('13','30','15','00'),
	array ('15','15','16','45'),
	array ('17','00','18','30'),
	array ('18','45','20','15')
);

$jsarray = "zz = new Array();\n";
for($z = 0; $z < count($zz); $z++) {
	$jsarray .= "zz[$z] = new Array('".$zz[$z][0]."','".$zz[$z][1]."','".$zz[$z][2]."','".$zz[$z][3]."');\n";
}
$jsarray .= "zz[$z] = new Array('$ss','$sm','$es','$em');\n";

switch ($element_switch){
	case 1:  // admin_dates.php Einzeltermin
		$txt_day   = 'tag';
		$txt_month = 'monat';
		$txt_year  = 'jahr';
		$txt_ss = 'stunde';
		$txt_sm = 'minute';
		$txt_es = 'end_stunde';
		$txt_em = 'end_minute';
		$zeiten = true;
		$kalender = true;
		break;
	case 2:  // admin_dates.php alle Termine
		$txt_day   = "tag[$c]";
		$txt_month = "monat[$c]";
		$txt_year  = "jahr[$c]";
		$txt_ss = "stunde[$c]";
		$txt_sm = "minute[$c]";
		$txt_es = "end_stunde[$c]";
		$txt_em = "end_minute[$c]";
		$zeiten = true;
		$kalender = true;
		break;
	case 3:  // admin_metadates.php regelmäßige Veranstaltungstermine
		$txt_ss = "turnus_start_stunde[$c]";
		$txt_sm = "turnus_start_minute[$c]";
		$txt_es = "turnus_end_stunde[$c]";
		$txt_em = "turnus_end_minute[$c]";
		$zeiten = true;
		$kalender = false;
		break;
	case 4:  //admin_seminare_assi.php regelmäßige Veranstaltungen (kein Kalender)
		$txt_ss = "term_turnus_start_stunde[$c]";
		$txt_sm = "term_turnus_start_minute[$c]";
		$txt_es = "term_turnus_end_stunde[$c]";
		$txt_em = "term_turnus_end_minute[$c]";
		$zeiten = true;
		$kalender = false;
		break;
	case 5: // admin_seminare_assi.php unregelmäßige Veranstaltungen
		$txt_day   = "term_tag[$c]";
		$txt_month = "term_monat[$c]";
		$txt_year  = "term_jahr[$c]";
		$txt_ss = "term_start_stunde[$c]";
		$txt_sm = "term_start_minute[$c]";
		$txt_es = "term_end_stunde[$c]";
		$txt_em = "term_end_minute[$c]";
		$zeiten = true;
		$kalender = true;
		break;
	case 6: // admin_seminare_assi.php Vorbesprechung
		$txt_day   = 'vor_tag';
		$txt_month = 'vor_monat';
		$txt_year  = 'vor_jahr';
		$txt_ss = 'vor_stunde';
		$txt_sm = 'vor_minute';
		$txt_es = 'vor_end_stunde';;
		$txt_em = 'vor_end_minute';
		$zeiten = true;
		$kalender = true;
		break;
	case 7:  // admin_metadates.php Startdatum Veranstaltungsbeginn
		$txt_day   = 'tag';
		$txt_month = 'monat';
		$txt_year  = 'jahr';
		$zeiten = false;
		$kalender = true;
		break;
	default: // z.b. calendar.php
		$txt_month = 'start_month';
		$txt_day   = 'start_day';
		$txt_year  = 'start_year';
		$zeiten = false;
		$kalender = true;

}
$title = _('Kalender');
$resize = '';
if ($zeiten && !$kalender) {
	$resize = 'window.resizeTo('.(($auth->auth["xres"] > 650)? 780 : 600).',150);'. "\n";
	$resize .= 'window.moveBy(0,330);'."\n";
}
echo <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>
<title>$title</title>
<link rel="stylesheet" type="text/css" href="{$CANONICAL_RELATIVE_PATH_STUDIP}style.css">
<script type="text/javascript">
<!--
window.setTimeout("window.close()", 120000); // Fenster automatisch wieder schließen :-)
$resize
function insert_time () {
   if (opener && document.forms['TimeForm']) {
     $jsarray
     var c = 999;
     for (i=0; i < document.forms['TimeForm'].elements.timei.length; i++){
     	if (document.forms['TimeForm'].elements.timei[i].checked == true) c = i;
     }
     if(c != 999){
     	opener.document.Formular.elements['$txt_ss'].value = zz[c][0];
     	opener.document.Formular.elements['$txt_sm'].value = zz[c][1];
     	opener.document.Formular.elements['$txt_es'].value = zz[c][2];
     	opener.document.Formular.elements['$txt_em'].value = zz[c][3];
     }
   }
   window.close();
}
function insert_date (m, d, y) {
   if (opener) {
     opener.document.Formular.elements['$txt_month'].value = m;
     opener.document.Formular.elements['$txt_day'].value = (d < 10) ? '0' + d : d;
     opener.document.Formular.elements['$txt_year'].value = y;
   }
   if (document.forms['TimeForm']) {
      insert_time();
   } else {
      window.close(); // bei this.close() stürzt Konqueror ab ...
   }
}
-->
</script>
</head>
<body>
EOT;

require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . '/calendar_visual.inc.php');

$atime =  (isset($_REQUEST['atime']) && $_REQUEST['atime'])? $_REQUEST['atime']: time();

$js['function'] = 'insert_date';

// mehr als einen Monat anzeigen?
if ($mcount > 3) {
	if ($mcount > 12) $mcount = 12;
	if ($mcount % 2 == 1) $mcount++; // nur gerade Werte erlaubt
	$mcounth = $mcount / 2;
	$atimex = getdate($atime);
	$i = 0;
	echo '<table class="blank" border=0 align="center"><tr valign=top>', "\n";
	while ($kalender && ($i < $mcount)) {
		if (($i % $mcounth == 0) && $i > 0) echo '</tr><tr valign=top>', "\n";
		echo '<td class="blank">';
		echo includeMonth(mktime(0,0,0,$atimex['mon'] + $i++,$atimex['mday'],$atimex['year']), 'javascript:void(0);//', 'NONAV', $js);
		echo '</td>';
	}
	if (!$kalender) echo '<td class="blank" colspan="',$mcounth,'">&nbsp;</td>';
	echo '</tr>', "\n";
	// time row
	if ($zeiten) {
		echo '<tr><td class="blank" colspan="',$mcounth,'" align=center><form name="TimeForm" action="javascript:void(0);">';
		echo '<table class="tabdaterow" cellspacing="0" cellpadding="0" align="center"><tr>', "\n";
		$sel = 0;
		for($z = 0; $z < count($zz); $z++) {
			if ($zz[$z][0] == $ss && $zz[$z][1] == $sm && $zz[$z][2] == $es && $zz[$z][3] == $em ){
				$selx = 'tddaterowpx';
				$check = ' checked';
				$sel = 1;
			} else {
				$selx = 'tddaterowp';
				$check = '';
			}
			$txtzeit =  $zz[$z][0].':'.$zz[$z][1].'&nbsp;-&nbsp;'.$zz[$z][2].':'.$zz[$z][3];
			$txtzeitc =  $zz[$z][0].':'.$zz[$z][1].' - '.$zz[$z][2].':'.$zz[$z][3] .' ' . _('Uhr') . ' ' . _('eintragen');
			echo '<td class="', $selx, '" ', tooltip($txtzeitc), '><input type="radio" name="timei" value="',$z,'"',$check,'>', $txtzeit, '</td>', "\n";
		}
		if ($sel == 0 && $ss != '' && $sm != '' && $es != '' && $em != '') {
			$txtzeit =  $ss.':'.$sm.'&nbsp;-&nbsp;'.$es.':'.$em;
			$txtzeitc =  _('zurücksetzen auf') .' '. $ss.':'.$sm.' - '.$es.':'.$em .' ' . _('Uhr');
			echo '<td class="tddaterowpx" ', tooltip($txtzeitc), '><input type="radio" name="timei" value="',$z,'" checked>', $txtzeit, '&nbsp;</td>', "\n";
		}
		echo '</tr></table></form>';
		echo '</td></tr>', "\n";
	}

	// navigation arrows

	echo '<tr>';
	$zeiten_buttons = '<a href="javascript:insert_time();">'. makeButton('uebernehmen', 'img') . '</a> &nbsp; <a href="javascript:window.close();">' . makeButton("abbrechen", "img").'</a>';
	if ($kalender) {
		echo '<td class="blank">&nbsp;<a href="',$PHP_SELF,'?atime=',mktime(0,0,0,$atimex['mon'] - $mcount,$atimex['mday'],$atimex['year']),'&mcount=',$mcount,'&element_switch=',$element_switch,'&c=',$c,$q,'"><img border="0" src="',$CANONICAL_RELATIVE_PATH_STUDIP,'pictures/calendar_previous_double_small.gif"', tooltip($mcount . ' ' . _('Monate zurück')),' border="0"></a>';
		echo '&nbsp;<a href="',$PHP_SELF,'?atime=',mktime(0,0,0,$atimex['mon'] - $mcounth,$atimex['mday'],$atimex['year']),'&mcount=',$mcount,'&element_switch=',$element_switch,'&c=',$c,$q,'"><img border="0" src="',$CANONICAL_RELATIVE_PATH_STUDIP,'pictures/calendar_previous_small.gif"', tooltip($mcounth .' ' . _('Monate zurück')),' border="0"></a></td>', "\n";
		if ($mcounth - 2 > 0) {
			echo '<td class="blank" colspan="' , ($mcounth - 2) , '" align=center>';
			if ($zeiten) echo $zeiten_buttons;
			echo '&nbsp;</td>';
		}
		echo '<td class="blank" align="right"><a href="',$PHP_SELF,'?atime=',mktime(0,0,0,$atimex['mon'] + $mcounth,$atimex['mday'],$atimex['year']),'&mcount=',$mcount,'&element_switch=',$element_switch,'&c=',$c,$q,'"><img border="0" src="',$CANONICAL_RELATIVE_PATH_STUDIP,'pictures/calendar_next_small.gif"', tooltip($mcounth . ' ' . _('Monate vor')),' border="0"></a>&nbsp;', "\n";
		echo '<a href="',$PHP_SELF,'?atime=',mktime(0,0,0,$atimex['mon'] + $mcount,$atimex['mday'],$atimex['year']),'&mcount=',$mcount,'&element_switch=',$element_switch,'&c=',$c,$q,'"><img border="0" src="',$CANONICAL_RELATIVE_PATH_STUDIP,'pictures/calendar_next_double_small.gif"', tooltip($mcount .' ' . _('Monate vor')),' border="0"></a>&nbsp;</td>';
	} elseif ($zeiten) {
		echo '<td class="blank" colspan="',$mcounth,'" align="center">', $zeiten_buttons, "</td>\n";
	}
	echo '</tr></table>', "\n";
} else {
	echo includeMonth($atime, $PHP_SELF . '?element_switch='.$element_switch.'&c='.$c.'&atime=', 'NOKW', $js);
}
echo "</body>\n</html>";

page_close();
?>
