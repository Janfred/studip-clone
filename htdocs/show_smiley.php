<?php
require_once "config.inc.php";
$folder=dir($ABSOLUTE_PATH_STUDIP."/".$SMILE_PATH);
$SMILE_SHORT_R=array_flip($SMILE_SHORT);
?>
<html>
<head>
<title>Alle Smileys</title>
<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<table><tr><td valign="top">
<table><tr>
<th>Bild</th><th>Schreibweise</th><th>Kürzel</th>
</tr>
<?
$zahl=0;
while ($entry=$folder->read()){
	$dot = strrpos($entry,".");
	$l = strlen($entry) - $dot;
	$name = substr($entry,0,$dot);
	$ext = strtolower(substr($entry,$dot+1,$l));
  	if ($dot AND !is_dir($entry) AND $ext=="gif"){
		echo "\n<tr><td class=\"blank\" align=\"center\"><img src=\"$SMILE_PATH/$entry\"></td>";
		echo "\n<td class=\"blank\" align=\"center\">:$name:</td>";
		($SMILE_SHORT_R[$name]) ? print "\n<td class=\"blank\" align=\"center\">$SMILE_SHORT_R[$name]</td>" : print "\n<td>&nbsp;</td>";
		echo "\n</tr>";
       $zahl++;
       if (!($zahl%30)) {
           ?>
           </table></td><td valign="top">
           <table align="center"><tr>
           <th>Bild</th><th>Schreibweise</th><th>Kürzel</th>
           </tr>
           <?
       }

	}
}
?>
</table></td></tr></table></body></html>

