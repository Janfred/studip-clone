<?
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("autor");
if (!$HTTP_POST_VARS["pass"])
   {
    ?>
    <html><head><title>Autlogin Datei erzeugen</title>
    <script type="text/javascript">
    function doSubmit(){
	    if (document.forms[0].pass.value!="") document.forms[0].submit();
	    else document.forms[0].pass.focus();
    }
    </script></head>
    <body style="background-image: url('pictures/steel1.jpg');font-family: Arial, Helvetica, sans-serif;">
    <?
    echo "<div align=\"center\"><form action=\"$PHP_SELF\" method=\"post\" >";
    echo "Bitte Passwort eingeben für User: <b>".$auth->auth["uname"]."</b><br><br>";
    echo "<input type=\"password\" size=\"15\" name=\"pass\"><br><br><a href=\"javascript:doSubmit();\"><img alt=\"Die heruntergeladene Datei bitte mit Endung .html speichern!\" src=\"pictures/buttons/herunterladen-button.gif\" border=\"0\"></a>";
    echo "&nbsp;&nbsp;<a href=\"javascript:window.close()\"><img alt=\"Fenster schließen\" src=\"pictures/buttons/abbrechen-button.gif\" border=\"0\"></a></form></div></body></html>";
    ?><script type="text/javascript">document.forms[0].pass.focus();</script><?
    page_close();
    die;
    }
ob_start();

?>
<html>
<head>
       <title>Autologin</title>

<script src="http://<? echo $HTTP_SERVER_VARS["HTTP_HOST"].$CANONICAL_RELATIVE_PATH_STUDIP;?>get_key.php" type="text/javascript">
</script>
<script type="text/javascript">

function convert(x, n, m, d)
   {
      if (x == 0) return "0";
      var r = "";
      while (x != 0)
      {
         r = d.charAt((x & m)) + r;
         x = x >>> n;
      }
      return r;
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
ob_end_flush();
?>
