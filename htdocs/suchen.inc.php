<?php
/*
suchen.inc.php - Funktion zur Suche im Forensystem, Stud.IP
Copyright (C) 2001 Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

//////////////////////////////////////////////////////////////////////////

function topic_liste_suche($eintrag, $root_id, $open, $name, $author, $create_dt, $root_name, $description, $username, $mehr, $show,$write=0,$modify_dt)
{global $PHP_SELF,$loginfilelast,$SessSemName,$forum,$view,$davor,$check_author,$check_cont,$check_name,$suchbegriff,$mehr,$tmp,$open,$show,$anfang,$auth,$user;
	$meineseite = $PHP_SELF;
	$datumtmp = $loginfilelast[$SessSemName[1]];
	IF ($datumtmp < $modify_dt) $neuer_beitrag = TRUE;  //ist der Beitrag neu?
	 IF (strstr($open,$eintrag)!=TRUE AND $show !=1 AND $show!=$eintrag AND !($neuer_beitrag==TRUE AND $forum["neuauf"]==1 AND $write==0) AND ($davor!=$eintrag)) {
	  // nicht aufgeklappt
	
		$link =	$meineseite."?open=".$eintrag."&mehr=$mehr&view=$view&show=".$eintrag;
		$link = $link."&eintrag=".$eintrag."&mehr=$mehr&suchbegriff=$suchbegriff&check_author=$check_author&check_cont=$check_cont&check_name=$check_name#anker";
		$icon = NTForum("topic",$eintrag,$description,$name,$neuer_beitrag,$root_id);
		IF  (!$auth->is_authenticated() || $user->id == "nobody" || $author=="unbekannt" || $username=="") // Nobody darf nicht auf die about...
			$zusatz = "<font size=2>".htmlReady($author)."&nbsp; ";
		ELSE $zusatz = "<a href=\"about.php?username=".$username."\"><font size=2 color='#333399'>".$author."</font>&nbsp;</a>";
		$zusatz .="&nbsp;".date("d.m.Y - H:i", $create_dt)
			."&nbsp;<a href=\"forum.php?topic_id=".$root_id
			."&open=".$eintrag
			."&all=TRUE"
			."#anker\" class=\"printhead\">".htmlReady(mila($root_name,20))
			."</a>";
		echo "<table width=90% border=0 cellpadding=0 cellspacing=0 align=center><tr>";
		printhead ("100%","0",$link,"close",$neuer_beitrag,$icon,htmlReady(mila($name)),$zusatz);
		echo "</tr></table>";	
		}
	ELSE { 
	
	// aufgeklappt
		
		$anfang = $root_id;
		$edit = NTForum("reply",$eintrag,"0");
		$link =	$meineseite."?mehr=$mehr&view=".$view;
		$link = $link."&mehr=$mehr&suchbegriff=$suchbegriff&check_author=$check_author&check_cont=$check_cont&check_name=$check_name";
		$icon =	"<img src=\"pictures/cont_blatt.gif\">";
		IF  (!$auth->is_authenticated() || $user->id == "nobody" || $author=="unbekannt" || $username=="") // Nobody darf nicht auf die about...
			$zusatz = htmlReady($author)."&nbsp; ";
		ELSE $zusatz = "<a href=\"about.php?username=".$username."\"><font size=2 color='#333399'>".$author."</font>&nbsp;</a>";
		$zusatz .="&nbsp;".date("d.m.Y - H:i", $create_dt)
			."&nbsp;<a class=\"printhead\" href=\"forum.php?topic_id=".$root_id
			."&open=".$eintrag
			."&all=TRUE"
			."#anker\"><font size=2 color='#333399'>".mila($root_name,20)
			."</a>";
		echo "<a name='anker'></a><table width=90% border=0 cellpadding=0 cellspacing=0 align=center><tr>";
		IF ($write!=0 AND $eintrag!=$davor) {
			$form=TRUE;
			echo "<form method=post action=\"forum.php?view=".$view."&open=".$topic_id."&topic_id=".$topic_id."\">";
			if (substr($name,0,3)!="Re:") $name = "Re:".$name;
			$name = "<input type=text style='font-size:8 pt;' name=titel value='".$name."'>";
		} else {
			$name = htmlReady(mila($name));
		}
		printhead ("100%","0",$link,"open",$neuer_beitrag,$icon,$name,$zusatz);
		echo "</tr></table>";	
		echo "<table width=90% border=0 cellpadding=0 cellspacing=0 align=center><tr>";
		IF ($write==-1) {
			$write=$eintrag;
			$nichtneu=TRUE;
			$edit="";
			}
		IF ($write>0) $edit="";	
		IF ($eintrag!=$davor) $description = editarea($write,$description,$nichtneu,"");
		ELSE $description = formatReady($description);
		IF(ereg("\[quote",$description) AND ereg("\[/quote\]",$description) AND !$write)  $description = quotes_decode($description);
		printcontent ("100%",$form,$description,$edit);
		IF ($write!=0 AND $eintrag!=$davor) echo "</form>";
		echo "</tr></table>";	

		}
	RETURN;
}

//////////////////////////////////////////////////////////////////////////


function suchen ($eintrag=0, $mehr=5, $suchbegriff,$check_author,$check_name,$check_cont,$seite )
{ global $SessionSeminar,$SessSemName;


	if( !$mehr) $mehr=5;
	if($check_author=="ON") $search_author="x.author LIKE '%$suchbegriff%'";

	if($check_author=="ON"&&($check_name="ON"))
		$search_name=" OR x.name LIKE '%$suchbegriff%'";
	elseif($check_name=="ON") 
		$search_name="x.name LIKE '%$suchbegriff%'";
	if($check_cont=="ON"){
		if($check_author=="ON")
			$search_cont=" OR x.description LIKE '%$suchbegriff%'";
		if($check_name=="ON")
			$search_cont=" OR x.description LIKE '%$suchbegriff%'";
		else
			$search_cont="x.description LIKE '%$suchbegriff%'";
	}
	
IF ($SessSemName[1] =="")
	{
	parse_window ("error§Sie haben keine Veranstaltung gew&auml;hlt. <br /><font size=-1 color=black>Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher eine Veranstaltung gew&auml;hlt haben.<br /><br /> Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich länger als $AUTH_LIFETIME Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zurück zur Anmeldung zu gelangen. </font>", "§",
				"Keine Veranstaltung gew&auml;hlt", 
				"<a href=\"index.php\"><b>&nbsp;Hier</b></a> geht es wieder zur Anmeldung beziehungsweise Startseite.<br />&nbsp;");
	die;
	}


	if(!isset($suchbegriff)):
	?>
<tr>
	<td class="topic" colspan=2><b>&nbsp;Bitte geben Sie hier Ihren Suchbegriff ein</b></td>
</tr>
<tr>
<td class="blank" width=100%">
<blockquote>
<BR>
<P>
 <center> 
   <table cellpadding=3 cellspacing=1>
	<FORM  NAME="search" METHOD=POST  ACTION="<?echo $seite?>" >
	<tr>
	<td><b>Suchbegriff:</b></td>
	<td><INPUT  TYPE="text"  NAME="suchbegriff"></td>
	</tr>
   	<tr><td><b>Suchen in den Feldern:</b></td><td>&nbsp; </td></tr>
	<tr><td>&nbsp; </td><td><input NAME="check_author" TYPE="checkbox" value="ON" checked> Autor</td></tr>
     	<tr><td>&nbsp; </td><td><input type="CHECKBOX" name="check_name" value="ON" checked> Thema </td></tr>
     	<tr><td>&nbsp; </td><td><input type="CHECKBOX" name="check_cont" value="ON" checked> Inhalt</td></tr> 
	<tr><td colspan=2 align="center"><INPUT  TYPE="submit"  VALUE=" Suche starten "></td>
	</tr>
	</FORM>
   </table>
<td class="blank" align="right" valign="top"><img src="pictures/suche.jpg" border="0"></td>

</td>
</tr>

<?

else:

?>
 
<tr>
	<td class="topic" colspan=2><b>&nbsp;Ergebnis der Suche nach "<? echo $suchbegriff ?>"</></td>
</tr>
<tr><td class="blank" colspan=2 width=100%">&nbsp;</td></tr>
<tr><td class="blank" colspan=2 width=100%" align="center">

<? 
$db = new DB_Seminar;
$db2 = new DB_Seminar;

if(isset($SessSemName[0]) && $SessSemName[0] != "")
	$db->query("SELECT x.topic_id, x.name AS titel, x.author , x.mkdate, y.name AS thema, y.topic_id AS thema_id, x.description, x.Seminar_id, x.user_id FROM px_topics x, px_topics y WHERE x.root_id = y.topic_id AND x.seminar_id =
	'$SessionSeminar' AND($search_author $search_name $search_cont) ORDER BY mkdate DESC ");

$i = 1;
$anzahl = $db->num_rows();
echo "<b>Ihre Suche ergab $anzahl Treffer</b><p>";
if ($anzahl > 0):
?>
<table width="100%" border=0 cellpadding=0 cellspacing=0 class="blank">
	<tr><td colspan=2 class="blank"><br>
	<table border=0 width="90%" cellspacing="0" cellpadding="0" align="center"><tr>
	<th width="100%">
	<?echo "<a href=\"suchen.php?view=".$view."&mehr=".$mehr."&suchbegriff=$suchbegriff&check_author=$check_author&check_cont=$check_cont&check_name=$check_name"
		."&show=1\"><img src='pictures/forumleer.gif' border=0 height='25' align=middle><img src='pictures/forumgraurunt.gif' border=0 alt='Alle aufklappen' align=middle><img src='pictures/forumleer.gif' border=0></a></th>";?>

	</th></tr></table>
<?
if($anzahl<$mehr) $mehr=$anzahl;
$suchbegriff=rawurlencode($suchbegriff);

while($i<=$mehr) {
	$db->next_record();
	$user_id = $db->f("user_id");
	$db2->query("SELECT username FROM auth_user_md5 WHERE user_id='$user_id'");
	$db2->next_record();
	$tmp = $db->f("topic_id");
	topic_liste_suche($db->f("topic_id"),$db->f("thema_id"),$open,$db->f("titel"),$db->f("author"),$db->f("mkdate"),$db->f("thema"),$db->f("description"),$db2->f("username"),$mehr,$show,$write,$db->f("chdate"));
/*	
	$datum = sprintf("%s.%s.%s - %s:%s",substr($db->f("create_dt"),6,2), substr($db->f("create_dt"),4,2), substr($db->f("create_dt"),0,4), substr($db->f("create_dt"),8,2), substr($db->f("create_dt"),10,2));
 	if ($eintrag != $db->f("topic_id")) {
		printf("<tr><td><a href=\"display_topic.php?topic_id=%s\">%s</a></td><td>%s</td><td>%s</td><td><a href=\"display_topic.php?topic_id=%s\">%s</a></td><td><a href=\"suchen.php?eintrag=%s&mehr=$mehr&suchbegriff=%s&check_author=$check_author&check_cont=$check_cont&check_name=$check_name\">%s</a></td></tr>\n",
		$db->f("topic_id"), $db->f("titel"), $db->f("author"), $datum, $db->f("thema_id"), $db->f("thema"), $db->f("topic_id"), $suchbegriff, substr(ereg_replace("<[[:print:]]*>","",$db->f("description")),0,30));
		}
	ELSE {
		printf("<tr><td><a href=\"display_topic.php?topic_id=%s\">%s</a></td><td>%s</td><td>%s</td><td><a href=\"display_topic.php?topic_id=%s\">%s</a></td><td>%s</td></tr>\n",
		$db->f("topic_id"), $db->f("titel"), $db->f("author"), $datum, $db->f("thema_id"), $db->f("thema"), $db->f("description"));
		}
*/
	$i++; 
}
$eintrag = 0;
echo "</td></tr></table>";

echo "<table width='90%' border=0 cellpadding=0 cellspacing=0 align='center'><tr><td class='blank'><img src='pictures/forumleer.gif' border=0 height='4'></td></tr><tr><td class=\"steelgraudunkel\" align=\"center\">";
if($mehr>5) printf ("<a href=\"suchen.php?mehr=%s&suchbegriff=%s&check_author=%s&check_cont=%s&check_name=%s\"><img src='pictures/forumleer.gif' border=0 height='25' align=middle><img src='pictures/forumgraurauf.gif' alt='zeig mir die Neuesten' border=0 align=middle><img src='pictures/forumleer.gif' border=0 height='25' align=middle></a>",
5,$suchbegriff,$check_author,$check_cont,$check_name);
if($mehr<=5)printf ("<a href=\"suchen.php?mehr=%s&suchbegriff=%s&check_author=%s&check_cont=%s&check_name=%s\"><img src='pictures/forumleer.gif' border=0 height='25' align=middle><img src='pictures/forumgraurunt.gif' alt='zeig mit alle Trefer' border=0 align=middle><img src='pictures/forumleer.gif' border=0 height='25' align=middle></a>",
$anzahl,$suchbegriff,$check_author,$check_cont,$check_name);
echo "</td></tr><tr><td class=\"blank\">&nbsp;<br><br></td></tr></table>";

echo "<a href='suchen.php'>Neue Suche</a>";


endif;  // nur eine Ergebnistabelle anzeigen, wenn es auch ein Ergebnis gibt


endif;  // Ende des Ergebniszeiges

//echo "<br>&nbsp;</td></tr>";
}

?>