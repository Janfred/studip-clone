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
require_once ("functions.php");
require_once ("visual.inc.php");


function show_news($range_id, $show_admin=FALSE,$limit="", $open, $width="100%", $last_visited=0)
{
global $PHP_SELF,$auth,$QUERY_STRING;

$db=new DB_Seminar;
$db2=new DB_Seminar;

$aktuell=time();

if ($QUERY_STRING)
	{
     list ($schnipp,)=explode("show_full",$QUERY_STRING);
     if (substr($schnipp,-1)=='&') $schnipp=substr($schnipp, 0, -1);
     if ($schnipp) $self=$PHP_SELF."?".$schnipp.'&';
     else $self=$PHP_SELF."?";
     }
else $self=$PHP_SELF."?";
$query="SELECT * FROM news_range LEFT JOIN news USING (news_id) WHERE news_range.range_id='$range_id' AND date < $aktuell AND (date+expire) > $aktuell ORDER BY date DESC";
if ($limit) $query=$query." LIMIT $limit";
$db->query($query);

if (!$db->num_rows())
	{
		 if ($show_admin)
		 	 {
     			echo"\n<table  border='0' bgcolor='#FFFFFF' cellspacing='0' cellpadding='2' align=\"center\" width='$width' >";
     			echo"\n<tr><td class='topic' colspan='2' width='99%'><img src='./pictures/news2.gif' border='0' alt='Newsticker. Klicken Sie rechts auf die Pfeile, um neue News in diesen Bereich zu stellen. Klicken Sie auf die roten Pfeile, um den ganzen Nachrichtentext zu lesen.' align='texttop'><b>&nbsp;News</b></td>";
     			echo"\n<td align = 'right' class='topic'>";
     			echo"&nbsp;<a href='admin_news.php?new_sem=TRUE&cmd=new_entry'><img src='./pictures/pfeillink.gif' border='0' alt='News einstellen'></a>&nbsp;";
     			echo"\n</td></tr>";
			echo "\n<tr><td class='steel1' colspan=3><blockquote><br /><font size=-1>Es sind keine aktuellen News vorhanden. Um neue News zu erstellen, klicken sie auf die Doppelpfeile.</font></blockquote>";
     			echo "\n</td></tr></table>";
     			return TRUE;
			 }
		 else {
		 	return FALSE;
		 	}
	}
else
	{     	
     	$colspan=2;
     	$k=0;
     	
	// Ausgabe der Daten
	while ($db->next_record())
 	{
	  	if(!$k) {
		  	//Ausgabe der Kopfzeile vor erster auszugebener News
		     	echo"\n<table  border='0' bgcolor='#FFFFFF' cellspacing='0' cellpadding='0' align=\"center\" width='$width' >";
			echo"\n<tr><td class='topic' colspan='2' width='99%'><img src='./pictures/news2.gif' border='0' alt='Newsticker. Klicken Sie rechts auf die Pfeile, um neue News in diesen Bereich zu stellen. Klicken Sie auf die roten Pfeile, um den ganzen Nachrichtentext zu lesen.' align='texttop'><b>&nbsp;News</b></td>";
			if ($show_admin) {
				$colspan++;
				echo"\n<td align = 'right' class='topic'>";
				echo"&nbsp;<a href='admin_news.php?new_sem=TRUE&modus=admin&cmd=show&range_id=$range_id'><img src='./pictures/pfeillink.gif' border='0' alt='News bearbeiten'></a>&nbsp;";
			     	echo"\n</td></tr>";
				}
			echo "</table>";
			}
		
		$k++;
		
		$tmp_titel=htmlReady(mila($db->f("topic")));
		$titel='';
	  	if ($open ==$db->f("news_id")) { 
			$link=$PHP_SELF."?nclose=true";
			$titel=$tmp_titel."<a name='anker'>";
			}
	  	else {
			$link=$PHP_SELF."?nopen=".$db->f("news_id");
			$titel=$tmp_titel;
			}

		$icon="&nbsp;<img src=\"./pictures/news-icon.gif\" border=0>";
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width='$width'><tr>";
						
	    $db2->query("SELECT username, Vorname, Nachname FROM auth_user_md5 WHERE user_id='".$db->f("user_id")."'");
	  	$db2->next_record();
		$link .= "&username=".$db2->f("username");
		$zusatz="<a href=\"about.php?username=".$db2->f("username")."\"><font size=-1 color=\"#333399\">".$db2->f("Vorname")." ".$db2->f("Nachname")."</font></a><font size=-1>&nbsp;".date("d.m.Y",$db->f("date"))."</font>";			

			
		$tempnew = ($db->f("date") >= $last_visited);
		if ($open == $db->f("news_id"))
			printhead(0, 0, $link, "open", $tempnew, $icon, $titel, $zusatz);
		else
			printhead(0, 0, $link, "close", $tempnew, $icon, $titel, $zusatz);
		
		echo "</tr></table>	";

	  	if ($open==$db->f("news_id"))
	  		{
	  		list ($content,$admin_msg)=explode("<admin_msg>",$db->f("body"));
	       		$content = formatReady($content);
	       		if ($admin_msg) 
	       			$content.="<br><br><i>$admin_msg</i>";
	       		
	       		if (!$content)
	       			$content="Keine Beschreibung vorhanden\n";
	       		else
	       			$content.="<br>";

		       	if ($auth->auth["uid"]==$db->f("user_id"))
		       		{
			    	$edit="<a href=\"admin_news.php?cmd=edit&edit_news=".$db->f("news_id")."\"><img src=\"pictures/buttons/bearbeiten-button.gif\" border=0></a>";
		    		$edit.="&nbsp;<a href=\"admin_news.php?cmd=kill&kill_news=".$db->f("news_id")."\"><img src=\"pictures/buttons/loeschen-button.gif\" border=0></a>";
		  	  	}
			
			echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width='$width'><tr>";
			printcontent(0,0, $content, $edit);
			echo "</tr></table>	";
		       	}
		echo "</td></tr></table></td></tr></table>";
	  	}
	  }


return TRUE;

}

?>