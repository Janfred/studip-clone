<?

//page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
//$perm->check("dozent");

//include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

//$handle=opendir("" . $TMP_PATH . "");
$dirstr = "" . $TMP_PATH;
if (!($handle=opendir( $dirstr )))
	echo "Das Verzeichnis existiert nicht!";
else
{
	echo "Directory handle: $handle<br>";
	echo "Files:<br>";

	while (($file = readdir($handle))!==false) 
	{
		if (filemtime($dirstr . "/" . $file) < (time() - 60*60 * 24) AND ($file != ".") AND ($file != "..") )
		{
			echo "<font color=\"FF0000#\">" . date("h:i d. m. y", filemtime($dirstr . "/" . $file)) . " $file</font><br>";
			unlink($dirstr . "/" . $file);
		}
		else
			echo date("h:i d. m. y", filemtime($dirstr . "/" . $file)) . " $file<br>";
	}

	closedir($handle); 
}
//page_close();
?>
</body>
</html>