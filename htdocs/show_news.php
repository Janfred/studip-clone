<?
/*
show_news.php - Anzeigefunktion fuer News
Copyright (C) 2001 Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/language.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/object.inc.php");


function show_news($range_id, $show_admin=FALSE,$limit="", $open, $width="100%", $last_visited=0) {
global $_fullname_sql,$PHP_SELF,$auth,$QUERY_STRING, $SessSemName;

$db=new DB_Seminar;
$db2=new DB_Seminar;

$aktuell=time();

if ($QUERY_STRING) {
	list ($schnipp,)=explode("show_full",$QUERY_STRING);
	if (substr($schnipp,-1)=='&')
		$schnipp=substr($schnipp, 0, -1);
	if ($schnipp)
		$self=$PHP_SELF."?".$schnipp.'&';
	else $self=$PHP_SELF."?";
} else
	$self=$PHP_SELF."?";

$query="SELECT *, date FROM news_range LEFT JOIN news USING (news_id) WHERE news_range.range_id='$range_id' AND date < $aktuell AND (date+expire) > $aktuell ORDER BY date DESC";
if ($limit)
	$query=$query." LIMIT $limit";
$db->query($query);

if ($SessSemName[1] == $range_id){
	$admin_link = ($SessSemName["class"]=="sem") ? "new_sem=TRUE&view=news_sem" :  "new_inst=TRUE&view=news_inst";
} else if ($range_id == $auth->auth['uid']){
	$admin_link = "range_id=self";
} else if ($range_id == "studip"){
	$admin_link = "range_id=studip";
}

if (!$db->num_rows()) {
	if ($show_admin) {
		echo"\n<table  border=\"0\" bgcolor=\"#FFFFFF\" cellspacing=\"0\" cellpadding=\"2\" align=\"center\" width=\"$width\" >";
		echo"\n<tr><td class=\"topic\" colspan=\"2\" width=\"99%\"><img src=\"./pictures/news2.gif\" border=\"0\"". tooltip(_("Newsticker. Klicken Sie auf die Pfeile (rechts), um neue News in diesen Bereich einzustellen. Klicken Sie auf die Pfeile am linken Rand, um den ganzen Nachrichtentext zu lesen.")) . "align=\"texttop\"><b>&nbsp;" . _("News") . "</b></td>";
		echo"\n<td align = \"right\" class=\"topic\">";
		printf ("&nbsp;<a href=\"admin_news.php?%s&cmd=new_entry\"><img src=\"./pictures/pfeillink.gif\" border=\"0\"" . tooltip(_("News einstellen")) . "></a>&nbsp;", $admin_link);
		echo"\n</td></tr>";
		echo "\n<tr><td class=\"steel1\" colspan=\"3\"><blockquote><br /><font size=\"-1\">" . _("Es sind keine aktuellen News vorhanden. Um neue News zu erstellen, klicken Sie auf die Doppelpfeile.") . "<br />&nbsp; </font></blockquote>";
		echo "\n</td></tr></table>";
		return TRUE;
	} else {
		return FALSE;
	}
} else {     	
	$colspan=2;

	//Ausgabe der Kopfzeile vor erster auszugebener News
	echo"\n<table  border=\"0\" bgcolor=\"#FFFFFF\" cellspacing=\"0\" cellpadding=\"2\" align=\"center\" width=\"$width\" >";
	echo"\n<tr><td class=\"topic\" colspan=\"2\" width=\"99%\"><img src=\"./pictures/news2.gif\" border=\"0\"". tooltip(_("Newsticker. Klicken Sie auf die Pfeile (rechts), um neue News in diesen Bereich einzustellen. Klicken Sie auf die Pfeile am linken Rand, um den ganzen Nachrichtentext zu lesen.")) . "align=\"texttop\"><b>&nbsp;" . _("News") . "</b></td>";
	if ($show_admin) {
		$colspan++;
		echo"\n<td align = \"right\" class=\"topic\">";
		printf ("&nbsp;<a href=\"admin_news.php?%s&modus=admin&cmd=show\"><img src=\"./pictures/pfeillink.gif\" border=\"0\"" . tooltip(_("News bearbeiten")) . "></a>&nbsp;", $admin_link);
		echo"\n</td></tr>";
	}
	echo "\n<tr><td colspan=$colspan>";
     	
	// Ausgabe der Daten
	while ($db->next_record()) {
		$tmp_titel=htmlReady(mila($db->f("topic")));
		$titel='';
		if ($open ==$db->f("news_id")) { 
			$link=$PHP_SELF."?nclose=true";
			$titel=$tmp_titel."<a name='anker'>";
			if ($db->f("user_id") != $auth->auth["uid"])
				object_add_view($db->f("news_id"));  //Counter for news - not my own
		} else {
			$link=$PHP_SELF."?nopen=".$db->f("news_id");
			$titel=$tmp_titel;
		}

		$icon="&nbsp;<img src=\"./pictures/news-icon.gif\" border=0>";
						
		$db2->query("SELECT username, " . $_fullname_sql['full'] ." AS fullname FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE a.user_id='".$db->f("user_id")."'");
		$db2->next_record();
		$link .= "&username=".$db2->f("username");
		$zusatz="<a href=\"about.php?username=".$db2->f("username")."\"><font size=-1 color=\"#333399\">".$db2->f("fullname")."</font></a><font size=-1>&nbsp;".date("d.m.Y",$db->f("date"))." | <font color=\"#005500\">".object_return_views($db->f("news_id"))."<font color=\"black\"> |</font>";			

		if ($link)
			$titel = "<a href=\"$link\" class=\"tree\" >".$titel."</a>";

		$tempnew = ($db->f("date") >= $last_visited);

		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr>";
		if ($open == $db->f("news_id"))
			printhead(0, 0, $link, "open", $tempnew, $icon, $titel, $zusatz, $db->f("date"));
		else
			printhead(0, 0, $link, "close", $tempnew, $icon, $titel, $zusatz, $db->f("date"));
		
		echo "</tr></table>	";

		if ($open==$db->f("news_id")) {
			list ($content,$admin_msg)=explode("<admin_msg>",$db->f("body"));
			$content = formatReady($content);
			if ($admin_msg) 
				$content.="<br><br><i>$admin_msg</i>";
	       		
			if (!$content)
				$content="Keine Beschreibung vorhanden\n";
			else
				$content.="<br>";

			if ($auth->auth["uid"]==$db->f("user_id") || $show_admin) {
				$edit="<a href=\"admin_news.php?cmd=edit&edit_news=".$db->f("news_id")."&$admin_link\">" . makeButton("bearbeiten") . "</a>";
				$edit.="&nbsp;<a href=\"admin_news.php?cmd=kill&kill_news=".$db->f("news_id")."&$admin_link\">" . makeButton("loeschen") . "</a>";
			}
			
			echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr>";
			printcontent(0,0, $content, $edit);
			echo "</tr></table>	";
		       	}
	  	}
	}
	echo "</td></tr></table>";

return TRUE;

}

?>
