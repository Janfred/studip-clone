<?
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
echo "<html>\n";
echo "<head>\n<title>Monat</title>\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$CANONICAL_RELATIVE_PATH_STUDIP}style.css\">\n";
echo "</head>\n";
echo "<body>\n";

echo "<script type=\"text/javascript\">\n";
echo "<!--\n";
echo "function insert_date (m, d, y) {\n";
echo "   opener.document.Formular.elements['start_month'].value = m;\n";
echo "   opener.document.Formular.elements['start_day'].value = (d < 10) ? '0' + d : d;\n";
echo "   opener.document.Formular.elements['start_year'].value = y;\n";
echo "   this.close();";
echo "}\n";
echo "-->\n";
echo "</script>\n";


require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/calendar_visual.inc.php");

if (!$atime)
	$atime = time();

$js['function'] = 'insert_date';

echo includeMonth($atime, $PHP_SELF . '?atime=', 'NOKW', $js);

echo "</body>\n</html>";

page_close();
?>
