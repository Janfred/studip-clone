<?
/*
archiv.php - Suchmaske fuer das Archiv
Copyright (C) 2001 Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

include "seminar_open.php";

?>
<html>
<head>
<title>Stud.IP</title>

<?
// Druckversion? ansonsten mit CSS
IF (!isset($druck)) ECHO"<link rel='stylesheet' href='style.css' type='text/css'>";
?>
</head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
<body bgcolor=white>
<?php 

require_once "msg.inc.php";
require_once "config.inc.php";
require_once "visual.inc.php";
require_once "functions.php";

$db=new DB_Seminar;
$db2=new DB_Seminar;
$cssSw=new cssClassSwitcher;
$sess->register("archiv_data");

//Daten des Suchformulars uebernehmen oder loeschen
if ($suche) {
	$archiv_data='';
	$archiv_data["all"]=$all;
	$archiv_data["name"]=$name;
	$archiv_data["sem"]=$sem;
	$archiv_data["inst"]=$inst;
	$archiv_data["desc"]=$desc;
	$archiv_data["doz"]=$doz;
	$archiv_data["pers"]=$pers;
	$archiv_data["perform_search"]=TRUE;
	}
elseif ((!$open) && (!$delete_id) && (!$show_grants) && (!$hide_grants) && (!$delete_user) && (!$add_user) && (!$new_search) && (!$close) && (!$dump_id) && (!$sortby) && (!$back))
	$archiv_data["perform_search"]=FALSE;

//Anzeige der Zugriffsberechtigten Personen ein/ausschalten
if ($show_grants) {
	$archiv_data["edit_grants"]=TRUE;
	}
if ($hide_grants) {
	$archiv_data["edit_grants"]=FALSE;
	}

if ($open) {
	$archiv_data["open"]=$open;
	}

if (($close) || ($suche)){
	$archiv_data["open"]=FALSE;
	}
	
if ($sortby)
	$archiv_data["sortby"]=$sortby;

$u_id = $user->id;	

//Sicherheitsabfrage
if ($delete_id) {
   	$db->query("SELECT name FROM archiv WHERE seminar_id= '$delete_id'");
	$db->next_record();
	$msg="info§Wollen Sie die Veranstaltung <b>".htmlReady($db->f("name"))."</b> wirklich l&ouml;schen? S&auml;mtliche Daten und die mit der Veranstaltung archivierte Dateisammlung werden unwiederuflich gel&ouml;scht! <br />";
	$msg.="<a href=\"".$PHP_SELF."?delete_really=TRUE&delete_id=$delete_id\"><img src=\"pictures/buttons/ja2-button.gif\" border=0 /></a>&nbsp; \n";
	$msg.="<a href=\"".$PHP_SELF."?back=TRUE\"><img src=\"pictures/buttons/nein-button.gif\" border=0 /></a>\n";
	
}

//Loeschen aus dem Archiv
if (($delete_id) && ($delete_really)){
	if ($perm->have_perm("admin") && !$perm->have_perm("root")) // root darf sowieso ueberall dran
		{
	   	$db2->query("SELECT archiv.seminar_id, archiv_file_id FROM archiv LEFT OUTER JOIN user_inst ON (heimat_inst_id = institut_id) WHERE user_inst.user_id = '$u_id' AND user_inst.inst_perms = 'admin' AND archiv.seminar_id= '$delete_id'");
		if ($db2->affected_rows() >0) {
		$db->query("DELETE FROM archiv WHERE seminar_id = '$delete_id'");
			if ($db->affected_rows())
				$msg="msg§Die Veranstaltung wurde aus dem Archiv gel&ouml;scht§";
			$db2->next_record();
			if ($db2->f("archiv_file_id")) {
				if (unlink ($ARCHIV_PATH."/".$db2->f("archiv_file_id")))
					$msg.="msg§Das Zip-Archiv der Veranstaltung wurde aus dem Archiv gel&ouml;scht.§";
				else
					$msg.="error§Das Zip-Archiv der Veranstaltung konnte nicht aus dem Archiv gel&ouml;scht werden.§";
				}
			}
		else
			$msg="error§Netter Versuch";
		}
	elseif ($perm->have_perm("root")) {
	   	$db2->query("SELECT archiv_file_id FROM archiv WHERE seminar_id='$delete_id'");
		$db->query("DELETE FROM archiv WHERE seminar_id = '$delete_id'");
		if ($db->affected_rows())
			$msg="msg§Die Veranstaltung wurde aus dem Archiv gel&ouml;scht§";
		$db2->next_record();
		if ($db2->f("archiv_file_id")) {
			if (unlink ($ARCHIV_PATH."/".$db2->f("archiv_file_id")))
				$msg.="msg§Das Zip-Archiv der Veranstaltung wurde aus dem Archiv gel&ouml;scht.§";
			else
				$msg.="error§Das Zip-Archiv der Veranstaltung konnte nicht aus dem Archiv gel&ouml;scht werden.§";
			}
		}
	}

//Loeschen von Archiv-Usern
if ($delete_user) {
	if ($perm->have_perm("root")) //root darf alles
		$do=TRUE;
	else { //vielleicht sein eigenes Seminar??
	   	$db2->query("SELECT seminar_id FROM archiv_user WHERE user_id = '$u_id' AND status = 'dozent' AND seminar_id= '$d_sem_id'");
		if ($db2->affected_rows()) 
			$do=TRUE;
		else { //dann aber vielleicht der Admin??
		   	$db2->query("SELECT archiv.seminar_id FROM archiv LEFT OUTER JOIN user_inst ON (heimat_inst_id = institut_id) WHERE user_inst.user_id = '$u_id' AND user_inst.inst_perms = 'admin' AND archiv.seminar_id= '$d_sem_id'");
			if ($db2->affected_rows()) 
			$do=TRUE;
			}
		}
		
		if ($do) {
		$db->query("DELETE FROM archiv_user WHERE seminar_id = '$d_sem_id' AND user_id='$delete_user'");
		if ($db->affected_rows())
			$msg="msg§Zugriffsberechtigung entfernt§";
		}
		else
			$msg="error§Netter Versuch";
	}
	
//Eintragen von Archiv_Usern
if ($do_add_user) {
	if ($perm->have_perm("root")) //root darf alles
		$do=TRUE;
	else { //vielleicht sein eigenes Seminar??
	   	$db2->query("SELECT seminar_id FROM archiv_user WHERE user_id = '$u_id' AND status = 'dozent' AND seminar_id= '$a_sem_id'");
		if ($db2->affected_rows()) 
			$do=TRUE;
		else { //dann aber vielleicht der Admin??
		   	$db2->query("SELECT archiv.seminar_id FROM archiv LEFT OUTER JOIN user_inst ON (heimat_inst_id = institut_id) WHERE user_inst.user_id = '$u_id' AND user_inst.inst_perms = 'admin' AND archiv.seminar_id= '$a_sem_id'");
			if ($db2->affected_rows()) 
			$do=TRUE;
			}
		}
		
		if ($do) {
		$db->query("INSERT INTO archiv_user SET seminar_id = '$a_sem_id', user_id='$add_user', status=\"autor\"");
		if ($db->affected_rows())
			$msg="msg§Zugriffsberechtigung erteilt§";
		}
		else
			$msg="error§Netter Versuch";
	$add_user=FALSE;
	}


// wollen wir den dump?

IF (!empty($dump_id))
{

       	IF ($perm->have_perm("root")) $query = "SELECT dump FROM archiv WHERE archiv.seminar_id = '$dump_id'"; 
	ELSEIF ($perm->have_perm("admin")) $query = "SELECT dump FROM archiv LEFT JOIN user_inst ON(heimat_inst_id = institut_id) WHERE user_inst.user_id = '$u_id' AND user_inst.inst_perms = 'admin' AND archiv.seminar_id = '$dump_id'";
        ELSE $query = "SELECT dump FROM archiv LEFT JOIN archiv_user USING(seminar_id) WHERE archiv.seminar_id = '$dump_id' AND user_id = '$u_id'";
	$db->query ($query);
       	IF ($db->next_record()) 
       		{
       		IF (!isset($druck)) ECHO "<div align=center> <a href='$PHP_SELF?dump_id=".$dump_id."&druck=1' target=_self><b>Druckversion</b></a><br><br>";
       		ECHO $db->f('dump');
       		}
	ELSE ECHO "netter Versuch, vieleicht beim n&auml;chsten Mal";
}

// oder vielleicht den Forendump?

ELSEIF (!empty($forum_dump_id))
{

       	IF ($perm->have_perm("root")) $query = "SELECT forumdump FROM archiv WHERE archiv.seminar_id = '$forum_dump_id'"; 
	ELSEIF ($perm->have_perm("admin")) $query = "SELECT forumdump FROM archiv LEFT JOIN user_inst ON(heimat_inst_id = institut_id) WHERE user_inst.user_id = '$u_id' AND user_inst.inst_perms = 'admin' AND archiv.seminar_id = '$forum_dump_id'";
        ELSE $query = "SELECT forumdump FROM archiv LEFT JOIN archiv_user USING(seminar_id) WHERE archiv.seminar_id = '$forum_dump_id' AND user_id = '$u_id'";
	$db->query ($query);
       	IF ($db->next_record()) 
       		{
       		IF (!isset($druck)) ECHO "<div align=center> <a href='$PHP_SELF?forum_dump_id=".$forum_dump_id."&druck=1' target=_self><b>Druckversion</b></a><br><br>";
       		ECHO $db->f('forumdump');
       		}
	ELSE ECHO "netter Versuch, vieleicht beim n&auml;chsten Mal";
}

ELSE
{	

// dann eben den Rest...

include "header.php";   //hier wird der "Kopf" nachgeladen 
?>
<table width="100%" border=0 cellpadding=0 cellspacing=0 border=0>
	<tr>
		<td class="topic" colspan=2><img valign="top" src="pictures/suchen.gif" border="0" align="texttop"><b>&nbsp;Suche im Archiv</>
		</td>
	</tr>
	<?
	if ($msg) { ?>
	<tr>
		<td class="blank" colspan=2>&nbsp;
		<? parse_msg($msg); ?>
		</td>
	</tr>
	<? } ?>
	<tr>
		<td class="blank" width="60%" align="left">
			<blockquote>
			<br />
				<p>
				<form  name="search" method="post" action="<?echo $PHP_SELF?>" >
					<table border=0 cellspacing=0 cellpadding=2>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" colspan=2>
							<b><font size=-1>Bitte geben Sie hier Ihre Suchkriterien ein:</font></b><br /><font size=-1>Wenn Sie keinen Suchbegriff angeben, werden alle Veranstaltungen angezeigt.</font>
							</td>
						</tr>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%">
								<font size=-1>Name der Veranstaltung:</font>
							</td>
							<td class="<? echo $cssSw->getClass() ?>" width="90%">
								<input  type="text"  size=30 maxlength=255 name="name" value="<? echo $archiv_data["name"] ?>">
							</td>
						</tr>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%">
								<font size=-1>DozentIn der Veranstaltung:</font>
							</td>
							<td  class="<? echo $cssSw->getClass() ?>" width="90%">
								<input  type="text"  size=30 maxlength=255 name="doz" value="<? echo $archiv_data["doz"] ?>">
							</td>
						</tr>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>"  width="10%">
								<font size=-1>Semester </font>
							</td>
							<td class="<? echo $cssSw->getClass() ?>"  width="90%">
								<font size=-1>
								<select name="sem">
								<option selected value=0>alle</option>
								<?
								$db->query("SELECT DISTINCT semester FROM archiv");
								while ($db->next_record()) 
									if  ($db->f("semester"))
										if ($db->f("semester") == $archiv_data["sem"])
											echo "<option selected value=\"", $db->f("semester"), "\">", $db->f("semester"), "</option>";
										else
											echo "<option value=\"", $db->f("semester"), "\">", $db->f("semester"), "</option>";											
								?>
								</select>
								</font>
							</td>
						</tr>						
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%">
								<font size=-1>Heimat-Einrichtung </font>
							</td>
							<td class="<? echo $cssSw->getClass() ?>"  width="90%">
								<font size=-1>
								<select name="inst">
								<option selected value=0>alle</option>
								<?
								$db->query("SELECT DISTINCT heimat_inst_id, Institute.Name FROM archiv LEFT JOIN Institute ON (Institut_id=heimat_inst_id)  ORDER BY Name");
								while ($db->next_record()) 
									{
									if  (($db->f("Name")) && ($db->f("Name")) !="- - -")
										if ($db->f("heimat_inst_id") == $archiv_data["inst"])
											echo "<option selected value=", $db->f("heimat_inst_id"), ">", my_substr($db->f("Name"),0, 40), "</option>";
										else
											echo "<option value=", $db->f("heimat_inst_id"), ">", my_substr($db->f("Name"),0, 40), "</option>";
										
									}
								?>
								</select>
								</font>
							</td>
						</tr>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%">
								<font size=-1>Beschreibung:</font>
							</td>
							<td class="<? echo $cssSw->getClass() ?>" width="90%">
								<input  type="text"  size=30 maxlength=255 name="desc" value="<? echo $archiv_data["desc"] ?>">
							</td>
						</tr>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%">
								<font size=-1>Suche &uuml;ber <b>alle</b> Felder:</font>
							</td>
							<td class="<? echo $cssSw->getClass() ?>" width="90%">
								<input  type="text"  size=30 maxlength=255 name="all" value="<? echo $archiv_data["all"] ?>">
							</td>
						</tr>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%">
								&nbsp; 
							</td>
							<td class="<? echo $cssSw->getClass() ?>" width="90%">
								<input  type="checkbox" name="pers" <? if ($archiv_data["pers"]) echo "checked" ?>>
								<font size=-1>Nur Veranstaltungen anzeigen, an denen ich teilgenommen habe</font>
							</td>
						</tr>
					   	<tr <? $cssSw->switchClass() ?>>
					   		<td class="<? echo $cssSw->getClass() ?>" width="10%">
					   			&nbsp; 
					   		</td>
					   		<td class="<? echo $cssSw->getClass() ?>" width="90%">
					   			<center>
					   				<input type="IMAGE" border=0 src="pictures/buttons/suchestarten-button.gif" value="Suche starten">
					   			</center
					   		</td>
						</tr>
					</table>
					<br />
					<input type="HIDDEN" name="suche" value="yes">
				</form>
			</blockquote>
		</td>
		<td class="blank" align = right valign=top><img src="pictures/archiv.jpg" border="0">
		</td>
	</tr>

<? 

// wollen wir was Suchen?

IF ($archiv_data["perform_search"]) {
	if (!$archiv_data["sortby"])
		$archiv_data["sortby"]="Name";
	if ($archiv_data["pers"])
		$query ="SELECT archiv.seminar_id, name, untertitel,  beschreibung, start_time, semester, fach, bereich, heimat_inst_id, fakultaet_id, institute, dozenten, fakultaet, archiv_file_id, forumdump FROM archiv LEFT JOIN archiv_user USING (seminar_id) WHERE user_id = '".$user->id."' AND ";
	else
		$query ="SELECT seminar_id, name, untertitel,  beschreibung, start_time, semester, fach, bereich, heimat_inst_id, fakultaet_id, institute, dozenten, fakultaet, archiv_file_id, forumdump FROM archiv WHERE ";
	if ($archiv_data["all"]) {
		$query .= "name LIKE '%".$archiv_data["all"]."%'";
		$query .= " OR untertitel LIKE '%".$archiv_data["all"]."%'";
		$query .= " OR beschreibung LIKE '%".$archiv_data["all"]."%'";
		$query .= " OR start_time LIKE '%".$archiv_data["all"]."%'";
		$query .= " OR semester LIKE '%".$archiv_data["all"]."%'";
		$query .= " OR fach LIKE '%".$archiv_data["all"]."%'";
		$query .= " OR bereich LIKE '%".$archiv_data["all"]."%'";
		$query .= " OR institute LIKE '%".$archiv_data["all"]."%'";
		$query .= " OR dozenten LIKE '%".$archiv_data["all"]."%'";
		$query .= " OR fakultaet LIKE '%".$archiv_data["all"]."%'";
		}
	else {
		if ($archiv_data["name"])
			$query .= "name LIKE '%".$archiv_data["name"]."%'";
		else
			$query .= "name LIKE '%%'";
		if ($archiv_data["desc"])
			$query .= " AND beschreibung LIKE '%".$archiv_data["desc"]."%'";
		else
			$query .= " AND beschreibung LIKE '%%'";		
		if ($archiv_data["sem"])
			$query .= " AND semester LIKE '%".$archiv_data["sem"]."%'";
		else
			$query .= " AND semester LIKE '%%'";
		if ($archiv_data["inst"])
			$query .= " AND heimat_inst_id LIKE '%".$archiv_data["inst"]."%'";
		else
			$query .= " AND heimat_inst_id LIKE '%%'";
		if ($archiv_data["doz"])
			$query .= " AND dozenten LIKE '%".$archiv_data["doz"]."%'";
		else
			$query .= " AND dozenten LIKE '%%'";		
		}
	$query .= " ORDER BY ".$archiv_data["sortby"];

	$db->query($query);
	IF (!$db->affected_rows() == 0) {
		$hits = $db->affected_rows();
		
		?>
	<tr>
		<td class="blank" colspan=2>
		<?
		
		echo "<blockquote><b><font size=-1>Es wurden $hits Veranstaltungen gefunden.</font></b></blockquote>";

	
	//alle Seminare, in denen ich Admin bin...
	$treffer=''	;
	IF ($perm->have_perm("admin") && !$perm->have_perm("root")) // root darf sowieso ueberall dran
		{
	   	$db2->query("SELECT archiv.seminar_id FROM archiv LEFT OUTER JOIN user_inst ON (heimat_inst_id = institut_id) WHERE user_inst.user_id = '$u_id' AND user_inst.inst_perms = 'admin'");
		WHILE ($db2->next_record()) 
			{
			$treffer[$db2->f("seminar_id")]=array("seminar_id"=>$db2->f("seminar_id"), "status" =>"admin");
			}
		}

	//und alle anderen  
	ELSE
	{
   	$db2->query("SELECT seminar_id FROM archiv_user WHERE user_id = '$u_id'");
	WHILE ($db2->next_record()) 
		{
		$treffer[$db2->f("seminar_id")]=array("seminar_id"=>$db2->f("seminar_id"), "status" =>$db2->f("status"));
		}
	}
	
   	ECHO "<br /><br /><TABLE class=\"blank\"  WIDTH=99% align=center cellspacing=0 border=0>";
   	ECHO "<tr height=28><td  width=\"1%\" class=\"steel\"><img src=\"pictures/blank.gif\" width=1 height=20>&nbsp; </td><td  width=\"29%\" class=\"steel\" align=center valign=bottom><b><a href=\"$PHP_SELF?sortby=Name\">Name</a></b></td><td  width=20% class=\"steel\" align=center valign=bottom><b><a href=\"$PHP_SELF?sortby=dozenten\">Dozent</a></b></td><td  width=20% class=\"steel\" align=center valign=bottom><b><a href=\"$PHP_SELF?sortby=institute\">Institut</a></b></td><td  width=20% class=\"steel\" align=center valign=bottom><b><a href=\"$PHP_SELF?sortby=semester\">Semester</a></b></td><td  width=10% class=\"steel\" colspan=3 align=center valign=bottom><b>Aktion</b></td></tr>";

	$c=0;
       	WHILE ($db->next_record()) 
	    	{
 		$file_name="Dateisammlung ".substr($db->f("name"),0,200).".zip";
	 	$view = 0;
		if ($archiv_data["open"]) {
	 	  	if ($archiv_data["open"] ==$db->f('seminar_id'))
 		  		$class="steelgraulight";
 		  	else
 		  		$class="steel1";
 		  	}
 	  	else {
	 	  	if ($c % 2)
  				$class="steelgraulight";
			else
				$class="steel1"; 
			$c++;
			}

      		ECHO "<tr><td class=\"$class\" WIDTH=\"1%\">&nbsp;";
      		
      		// schon aufgeklappt?
		IF ($archiv_data["open"]==$db->f('seminar_id'))  
			ECHO"<a name='anker'></a><a href='$PHP_SELF?close=yes'>&nbsp;<img src='pictures/forumgraurunt.gif' alt='Zuklappen' border='0'></a></td><td class=\"$class\" width=\"29%\"><font size=-1><b>".htmlReady($db->f("name"))."</b></font></td>";
      		ELSE 
      			ECHO"<a href='$PHP_SELF?open=",$db->f('seminar_id'),"#anker'><img src='pictures/forumgrau.gif' alt='Aufklappen' border='0'></a></td><td class=\"$class\" width=\"29%\"><font size=-1>".htmlReady($db->f("name"))."</font></td>";

    		ECHO "<td align=center class=\"$class\" WIDTH=25%>&nbsp;<font size=-1>".$db->f("dozenten")."</font></td>";
 		ECHO "<td align=center class=\"$class\" WIDTH=25%>&nbsp;<font size=-1>".$db->f("institute")."</font></td>";
 		ECHO "<td align=center class=\"$class\" WIDTH=11%>&nbsp;<font size=-1>".$db->f("semester")."</font></td>";
		
      		IF ($perm->have_perm("root")) $view = 1;
      		ELSEIF ($treffer[$db->f("seminar_id")])
      			$view = 1;
      		IF ($view == 1)
      			{
	      		 ECHO "<td class=\"$class\" width=\"3%\">&nbsp;<a href='$PHP_SELF?dump_id=".$db->f('seminar_id')."' target=_blank><img src='pictures/i.gif' alt='Komplettansicht' border='0'></a></td><td class=\"$class\" width=\"3%\">&nbsp;";
	      		 IF (!$db->f('archiv_file_id')=='') ECHO "<a href=\"sendfile.php?type=1&file_id=".$db->f('archiv_file_id')."&file_name=".rawurlencode($file_name)."\"><img src='pictures/files.gif' alt='Dateisammlung' border='0'></a>";
	      		 echo "</td><td class=\"$class\" width=\"3%\">&nbsp;";
	      		 if ($perm->have_perm("admin"))
	      		 	ECHO "<a href='$PHP_SELF?delete_id=".$db->f('seminar_id')."'>&nbsp;<img border=0 src=\"./pictures/trash.gif\" alt=\"Diese Veranstaltung aus dem Archiv entfernen\"></a>";
	      		 echo "</td>";
	      		 }	
      		ELSE ECHO "<td class=\"$class\" width=\"3%\">&nbsp;</td><td class=\"$class\" width=\"3%\">&nbsp;</td><td class=\"$class\" width=\"3%\">&nbsp;</td>";
      		
      		IF ($archiv_data["open"]==$db->f('seminar_id'))
      			{
	      		ECHO "</tr><tr><td class=\"steelgraulight\" colspan=8><blockquote>";
	      		IF (!$db->f('untertitel')=='') ECHO "<li><font size=-1><b>Untertitel: </b>".htmlReady($db->f('untertitel'))."</font></li>";
	      		IF (!$db->f('beschreibung')=='') ECHO "<li><font size=-1><b>Beschreibung: </b>".htmlReady($db->f('beschreibung'))."</font></li>";
   		 	IF (!$db->f('fakultaet')=='') ECHO "<li><font size=-1><b>Fakult&auml;t: </b>".htmlReady($db->f('fakultaet'))."</font></li>";
    		      	IF (!$db->f('fach')=='') ECHO "<li><font size=-1><b>Fach: </b>".htmlReady($db->f('fach'))."</font></li>";
    		 	IF (!$db->f('bereich')=='') ECHO "<li><font size=-1><b>Bereich: </b>".htmlReady($db->f('bereich'))."</font></li>";

		// doppelt haelt besser: noch mal die Extras

   		 	IF ($view == 1) 
   		 		{
   		 		ECHO "<br><br><li><a href='$PHP_SELF?dump_id=".$db->f('seminar_id')."' target=_blank><font size=-1>&Uuml;bersicht der Veranstaltungsinhalte</font></a></li>";
   		 		IF (!$db->f('forumdump')=='') ECHO "<li><font size=-1><a href='$PHP_SELF?forum_dump_id=".$db->f('seminar_id')."' target=_blank>Beitr&auml;ge des Forums</a></font></li>";
   		 		IF (!$db->f('archiv_file_id')=='') ECHO "<li><font size=-1><a href=\"sendfile.php?type=1&file_id=".$db->f('archiv_file_id')."&file_name=".rawurlencode($file_name)."\">Download der Dateisammlung</a></font></li>";
		      		if ($perm->have_perm("admin"))
	      		 		ECHO "<li><a href='$PHP_SELF?delete_id=".$db->f('seminar_id')."'><font size=-1>Diese Veranstaltung unwiderruflich aus dem Archiv entfernen</font></a></li>";
				if ($treffer[$db2->f("seminar_id")]["status"] != "autor") {
					if (!$archiv_data["edit_grants"])
						echo "<li><font size=-1><a href=\"$PHP_SELF?show_grants=yes#anker\">Zugriffsberechtigungen einblenden</a></font></li>";	      		 		
					else
						echo "<li><font size=-1><a href=\"$PHP_SELF?hide_grants=yes#anker\">Zugriffsberechtigungen ausblenden</a></font></li>";	      		 		
					}
   		 		}
   		 	ELSE ECHO "<br><br><li><font size=-1>Die Veranstaltungsinhalte, Beitr&auml;ge im Forum und das Dateiarchiv sind nicht zug&auml;ngig, da Sie an dieser Veranstaltung nicht teilgenommen haben.</font></li>";

       		 	if ($archiv_data["edit_grants"]) {
				echo "<br /><br /><hr><b><font size=-1>Folgende Benutzer haben Zugriff auf die Daten der Veranstaltung (&Uuml;bersicht, Beitr&auml;ge und Dateiarchiv):</font></b><br /><br />";
				$db2->query("SELECT Nachname, Vorname, archiv_user.status, username, archiv_user.user_id FROM archiv_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id = '".$db->f("seminar_id")."' ORDER BY Nachname");
				while ($db2->next_record()) {
					echo "<font size=-1>".$db2->f("Nachname"), ", ", $db2->f("Vorname"), " (Status: ", $db2->f("status"), ")</font>";
					if ($db2->f("status") != "dozent")
						echo "<a href='$PHP_SELF?delete_user=".$db2->f("user_id")."&d_sem_id=".$db->f("seminar_id"),"#anker'><font size=-1>&nbsp;Zugriffsberechtigung entfernen</font> <img border=0 src=\"./pictures/trash.gif\" alt=\"Diesem Benutzer die Zugriffsberechtigung entziehen\"></a>";
					echo "<br />";	
					}		
				if (($add_user) && (!$new_search)){
					$db2->query("SELECT Vorname, Nachname, username, user_id FROM auth_user_md5 WHERE Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%' OR username LIKE '%$search_exp%' ORDER BY Nachname");
					if ($db2->affected_rows()) {
						echo "<form action=\"$PHP_SELF#anker\">";
						echo "<hr><b><font size=-1>Benutzer Berechtigung erteilen: </font></b><br /><br />";
						echo "<b><font size=-1>Es wurden ", $db2->affected_rows(), " Benutzer gefunden </font></b><br />";
						echo "<font size=-1>Bitte w&auml;hlen Sie den Benutzer aus der Liste aus:</font>&nbsp;<br /><font size=-1><select name=\"add_user\">";
						while ($db2->next_record()) {
							echo "<option value=",$db2->f("user_id"),">",$db2->f("Nachname"),", ".$db2->f("Vorname"), ", (",$db2->f("username"),") </option>";
						}
						echo "</select></font>";
						echo "<br /><font size=-1><input type=\"SUBMIT\"  name=\"do_add_user\" value=\"Diesen Benutzer hinzuf&uuml;gen\" /></font>";
						echo "&nbsp;<font size=-1><input type=\"SUBMIT\"  name=\"new_search\" value=\"Neue Suche\" /></font>";
						echo "<input type=\"HIDDEN\"  name=\"a_sem_id\" value=\"",$db->f("seminar_id"), "\" />";
						echo "</form>";
						}
					}
				if ((($add_user) && (!$db2->affected_rows())) || (!$add_user) || ($new_search)) {
					echo "<form action=\"$PHP_SELF#anker\">";
					echo "<hr><b><font size=-1>Benutzer Berechtigung erteilen: </font></b><br />";
					if (($add_user) && (!$db2->affected_rows)  && (!§new_search))
						echo "<br /><b><font size=-1>Es wurde kein Benutzer zu dem eingegebenem Suchbegriff gefunden!</font></b><br />";
					echo "<font size=-1>Bitte Namen, Vornamen oder Usernamen eingeben:</font>&nbsp; ";
					echo "<br /><input type=\"TEXT\" size=20 maxlength=255 name=\"search_exp\" />";
					echo "&nbsp;<font size=-1><br /><input type=\"SUBMIT\"  name=\"add_user\" value=\"Suche starten\" /></font>";	
					echo "</form>";						
					}
       		 		}

      		 	ECHO "</blockquote></td>";
	      		}
      		ECHO "</tr>";
    		}
    	ECHO "</table><br><br>";
    	}
   ELSE
 	{
	echo "<tr><td class=\"blank\" colspan=2><blockquote><font size=-1><b>Es wurde keine Veranstaltung gefunden.</b></font></blockquote>";
  	}
   }

?>
</td></tr>
</table>
<?
}

  // Save data back to database.
  page_close()
 ?>
</body>
</html>