<?
function print_freie($username) {	
	
	global $view,$PHP_SELF,$auth;
	$db=new DB_Seminar;
	$cssSw=new cssClassSwitcher;

	$cssSw->switchClass();
	
	$db->query("SELECT * FROM kategorien LEFT JOIN auth_user_md5 ON(range_id=user_id) WHERE username='$username' ORDER BY chdate DESC");

        echo "<tr><td align=\"left\" valign=\"top\" class=\"blank\"><blockquote><br>Hier können Sie beliebige eigene Kategorien anlegen. Diese Kategorien erscheinen auf Ihrer pers&ouml;nlichen Homepage.<br>Wenn Sie die Option \"f&uuml;r andere unsichtbar\" verwenden, k&ouml;nnen Sie Memos anlegen, die nur f&uuml;r Sie selbst auf der Homepage sichtbar werden - andere Nutzer k&ouml;nnen die Daten nicht einsehen.";
  	echo "<br><br></td></tr>\n<tr><td class=blank><table width=100% class=blank border=0 cellpadding=0 cellspacing=0>";
     	echo "<form action=\"$PHP_SELF?freie=update_freie&username=$username&view=$view\" method=\"POST\" name=\"edit_freie\">";
	if (!$db->affected_rows())
		echo "<tr><td class=\"".$cssSw->getClass()."\"><font size=-1><b><blockquote>Es existieren zur Zeit keine eigenen Kategorien.</b></font></blockquote></td></tr>\n";
	echo "<tr><td class=\"".$cssSw->getClass()."\"><blockquote>Kategorie&nbsp; <a href='$PHP_SELF?freie=create_freie&view=$view&username=$username'><img src='pictures/buttons/neuanlegen-button.gif' border=0 align=absmiddle></a></blockquote></td></tr>";
	$count = 0;
	$hidden_count = 0;
	while ($db->next_record() ){
		
		IF ((($auth->auth["perm"]=="root") OR ($auth->auth["perm"]=="admin")) AND $db->f("hidden")=='1' AND $username!=$auth->auth["uname"]) {
			$hidden_count++;
			}
		ELSE {
			$cssSw->switchClass();
			$id = $db->f("kategorie_id");
			echo "<tr><td class=\"".$cssSw->getClass()."\">";
			if ($count)
				echo "<br />";
			echo "<input type=\"hidden\" name=\"freie_id[]\" value=\"".$db->f("kategorie_id")."\">\n";
			echo "<blockquote><input type='text' name='freie_name[]' value='".htmlReady($db->f("name"))."' size=40>";
			echo "&nbsp; &nbsp; &nbsp; <input type=checkbox name='freie_secret[$count]' value='1'";
			IF ($db->f("hidden")=='1') 
				echo " checked";
			echo ">f&uuml;r andere unsichtbar<br />&nbsp; </td></tr>";
			// Breite für textarea
			$cols = $auth->auth["jscript"]?ceil($auth->auth["xres"]/13):50;
			echo "<tr><td class=\"".$cssSw->getClass()."\"><blockquote><textarea  name='freie_content[]' style=\"width: 80%\" cols=\"$cols\" rows=7 wrap=virtual>".htmlReady($db->f("content"))."</textarea><br /><br />";
			echo "<a href='$PHP_SELF?freie=delete_freie&freie_id=$id&view=$view&username=$username'><img src='pictures/buttons/loeschen-button.gif' border=0></a>";
			echo "&nbsp;<input type='IMAGE' name='update' border=0 src='pictures/buttons/uebernehmen-button.gif' value='ver&auml;ndern'><br />&nbsp; </td></tr>";
			$count++;
			}
		}
	if ($hidden_count)
		if ($hidden_count > 1)
			echo "<tr><td class=\"".$cssSw->getClass()."\"><font size=-1><b><blockquote>Es existiereren zus&auml;tzlich $hidden_count Kategorien, die Sie nicht einsehen und bearbeiten k&ouml;nnen.</b></font></blockquote></td></tr>\n";
		else
			echo "<tr><tdclass=\"".$cssSw->getClass()."\" ><font size=-1><b><blockquote>Es existiert zus&auml;tzlich eine Kategorie, die Sie nicht einsehen und bearbeiten k&ouml;nnen.</b></font></blockquote></td></tr>\n";
	 echo "</form></td></tr></table></td></tr>";
}

function create_freie()
{ global $auth,$use,$username,$PHP_SELF;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$tmp = $username;
	$now = time();
	$kategorie_id=md5(uniqid($hash_secret));
	$db->query ("SELECT user_id , username FROM auth_user_md5 WHERE username = '$tmp'");
	WHILE ($db->next_record())
		$user_id = $db->f("user_id");
	$db2->query("INSERT INTO kategorien (kategorie_id,name, content, mkdate, chdate, range_id) VALUES ('$kategorie_id','neue Kategorie','Inhalt der Kategorie','$now','$now','$user_id')");
	IF  ($db2->affected_rows() == 0){
		parse_msg ("info§Anlegen fehlgeschlagen");
		die;
		}
}

function delete_freie($kategorie_id)
{ global $auth,$user,$PHP_SELF,$username;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
//	$tmp = $auth->auth["uname"];
	$tmp = $username;
	$db->query ("SELECT * FROM kategorien LEFT JOIN auth_user_md5 ON(range_id=user_id) WHERE username = '$tmp' and kategorie_id='$kategorie_id'");
		IF (!$db->next_record()) { //hier wollte jemand schummeln
				parse_msg ("info§Netter Versuch, vielleicht beim n&auml;chsten Mal!");
			die;
			}
		ELSE {
			$db2->query("DELETE FROM kategorien WHERE kategorie_id='$kategorie_id'");
			IF  ($db2->affected_rows() == 1) {
				parse_msg ("msg§Kategorie gel&ouml;scht!");
				}
			}
}

function update_freie()
{ global $auth,$user,$freie_id,$freie_name,$freie_content,$freie_secret,$PHP_SELF;
	$max = sizeof($freie_id);
	FOR ($i=0;$i<$max;$i++) {
		$now = time();
		$db=new DB_Seminar;
		$name = $freie_name[$i];
		$content = $freie_content[$i];
		$secret=$freie_secret[$i];
		$id = $freie_id[$i];
		$db->query("UPDATE kategorien SET name='$name', content='$content', hidden='$secret', chdate='$now' WHERE kategorie_id='$id'");
		}
	parse_msg ("msg§Kategorien ge&auml;ndert!");
}

//////////////////////////////////////////////////////////////////////////
?>