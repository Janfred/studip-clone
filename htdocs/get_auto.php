<?
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("autor");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

if (!$HTTP_POST_VARS["pass"])
   {
    ?>
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html><head><title><?=_("Autologin Datei erzeugen")?></title>
		<meta name="copyright" content="Stud.IP-Crew (crew@studip.de)">
    <script type="text/javascript">
    function doSubmit(){
	    if (document.forms[0].pass.value!="") document.forms[0].submit();
	    else document.forms[0].pass.focus();
    }
    </script></head>
    <body style="background-image: url('pictures/steel1.jpg');font-family: Arial, Helvetica, sans-serif;">
    <?
    echo "<div align=\"center\"><form action=\"$PHP_SELF\" method=\"post\" >";
    printf(_("Bitte Passwort eingeben für User: <b>%s</b>"), $auth->auth["uname"]);
		echo "<br><br>";
    echo "<input type=\"password\" size=\"15\" name=\"pass\"><br><br><a href=\"javascript:doSubmit();\"><img " . makeButton("herunterladen", "src") . " border=\"0\" " . tooltip(_("Die heruntergeladene Datei bitte mit der Endung .html speichern!")) . "></a>";
    echo "&nbsp;&nbsp;<a href=\"javascript:window.close()\"><img " . makeButton("abbrechen", "src") . " border=\"0\" " . tooltip(_("Fenster schließen")) . "></a></form></div></body></html>";
    ?><script type="text/javascript">document.forms[0].pass.focus();</script><?
    page_close();
    die;
    }
ob_start();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title><?=_("Autologin")?></title>
	<meta name="copyright" content="Stud.IP-Crew (crew@studip.de)">
<script src="http://<? echo $HTTP_SERVER_VARS["HTTP_HOST"].$CANONICAL_RELATIVE_PATH_STUDIP;?>get_key.php" type="text/javascript">
</script>
<script type="text/javascript">

function convert(x, n, m, d)
   {
      if (x == 0) return "00";
      var r = "";
      while (x != 0)
      {
         r = d.charAt((x & m)) + r;
         x = x >>> n;
      }
      return (r.length%2) ? "0" + r : r;
   }
   
function toHexString(x){
	return convert(x, 4, 15, "0123456789abcdef");
	}
	
function one_time_pad(text,key)
{
var geheim=""
 for(var i = 0; i < text.length; i++)
         {
         k=((text.charCodeAt(i))+(key.charCodeAt(i)))%256;
         geheim=geheim + toHexString(k);
         }
 return(geheim);
}

/* Hier gehts los... */
var password = "<?=$HTTP_POST_VARS["pass"];?>";
var username = "<?=$auth->auth["uname"];?>";
if (auto_key)
   {
    var response = one_time_pad(password,auto_key);
    var autourl="http://<? echo $HTTP_SERVER_VARS["HTTP_HOST"].$CANONICAL_RELATIVE_PATH_STUDIP;?>index.php?again=yes&auto_user=" + username + "&auto_response=" + response + "&auto_id=" + auto_id + "&resolution=" + screen.width+"x"+screen.height;
    location.href=autourl;
    }
//-->
</script>
</head>
<body>
</body>
</html>
<?
header("Content-Type: application/x-autologin");
header("Content-Disposition: inline; filename=\"autologin_".$auth->auth["uname"].".rename_to_html\"");
header("Pragma: no-cache");
header("Expires: 0");
header("cache-control: no-cache");

ob_end_flush();
?>
