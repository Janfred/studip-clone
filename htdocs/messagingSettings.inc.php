<?

require_once ($ABSOLUTE_PATH_STUDIP."/language.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/config.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/functions.php");
require_once ($ABSOLUTE_PATH_STUDIP."/visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/messaging.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/contact.inc.php");

$msging=new messaging;
$db3=new DB_Seminar;

//vorgenommene Anpassungen der Ansicht in Uservariablen schreiben
if ($messaging_cmd=="change_view_insert") {
	$my_messaging_settings["changed"] = TRUE;
	$my_messaging_settings["show_only_buddys"] = $show_only_buddys;
	$my_messaging_settings["delete_messages_after_logout"] = $delete_messages_after_logout;
	$my_messaging_settings["start_messenger_at_startup"] = $start_messenger_at_startup;
	$my_messaging_settings["active_time"] = $active_time;
	$my_messaging_settings["sms_sig"] = $sms_sig;
	$my_messaging_settings["timefilter"] = $timefilter;
	$my_messaging_settings["openall"] = $openall;
	$my_messaging_settings["opennew"] = $opennew;
	#$my_messaging_settings["hoover"] = $hoover;
	$my_messaging_settings["addsignature"] = $addsignature;
	$my_messaging_settings["changed"] = "TRUE";
	$my_messaging_settings["save_snd"] = $save_snd;
	$sms_data["time"] = $my_messaging_settings["timefilter"];

	if (!$smsforward_active) {
		$smsforward_active = "2";
	}
	if (!$smsforward_copy && ($smsforward_copy_orig == "1" || $smsforward_copy_orig == "2")) {
		$smsforward_copy = "2";
	}
	$query = "UPDATE user_info SET smsforward_active='".$smsforward_active."', smsforward_copy='".$smsforward_copy."'  WHERE user_id='".$user->id."'";
	$db3->query($query);
	
	$nosearch = "1";

}

#if (empty($my_messaging_settings["hoover"])) {
	#$my_messaging_settings["hoover"] = "0";
#}
if (empty($my_messaging_settings["save_snd"])) {
	$my_messaging_settings["save_snd"] = "2";
}
if (empty($my_messaging_settings["openall"])) {
	$my_messaging_settings["openall"] = "2";
}
if (empty($my_messaging_settings["timefilter"])) {
	$my_messaging_settings["timefilter"] = "all";
}
if (empty($my_messaging_settings["addsignature"])) {
	$my_messaging_settings["addsignature"] = "2";
}
if (empty($my_messaging_settings["opennew"])) {
	$my_messaging_settings["opennew"] = "2";
}

if ($add_smsforward_rec_x) { // update forward_receiver
	$query = "UPDATE user_info SET smsforward_rec='".get_userid($smsforward_rec)."' WHERE user_id='".$user->id."'";
	$db3->query($query);
	$nosearch = "1";
}

if ($do_add_user_x)
	$msging->add_buddy ($add_user);

//Anpassen der Ansicht
function change_messaging_view() {
	global $_fullname_sql,$my_messaging_settings, $PHP_SELF, $perm, $user, $search_exp, $add_user, $add_user_x, $do_add_user_x, $new_search_x, $i_page, $search_exp, $gosearch_x;
		
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$cssSw=new cssClassSwitcher;	
	?>
	<table width ="100%" cellspacing=0 cellpadding=0 border=0>
	<tr>
		<td class="topic" colspan=2><img src="pictures/meinesem.gif" border="0" align="texttop"><b>&nbsp;<?=_("Einstellungen f&uuml;r das Stud.IP-Messaging anpassen")?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>&nbsp;
			<blockquote>
				<?=_("Hier k&ouml;nnen Sie Ihre Einstellungen f&uuml;r das Stud.IP-Messaging &auml;ndern.")?> <br />
			<br>
			</blockquote>
		</td>
	</tr>	
	<tr>
		<td class="blank" colspan=2>
			<form method="POST" action="<? echo $PHP_SELF ?>?messaging_cmd=change_view_insert">
			<table width ="99%" align="center" cellspacing=0 cellpadding=2 border=0>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan=2>
					&nbsp;&nbsp;<b><?=_("Systeminterne Kurznachrichten")?></b>
					</td>
				</tr>

				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("automatisches L&ouml;schen:")?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="70%">&nbsp; 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["delete_messages_after_logout"]) echo " checked"; ?>
					 name="delete_messages_after_logout">
					&nbsp;&nbsp;<font size=-1> <?=_("Alle Nachrichten automatisch nach dem Logout l&ouml;schen")?></font>
					</td>
				</tr>

				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Weiterleitung:")?>
					</td>
					<?
					$query = "SELECT * FROM user_info WHERE user_id='".$user->id."'";
					$db2->query($query);
					while ($db2->next_record()) {
						$smsforward['active'] = $db2->f("smsforward_active");
						$smsforward['copy'] = $db2->f("smsforward_copy");
						$smsforward['rec'] = $db2->f("smsforward_rec");
					}
					?>
					<td class="<? echo $cssSw->getClass() ?>" width="70%"><font size=-1>&nbsp; 
					
					<input type="CHECKBOX" 
					<? if ($smsforward['active'] != "2") echo " checked"; ?>
					 value="1" name="smsforward_active">	&nbsp;&nbsp;<?=("Empfangene Nachrichten werden an einen beliebigen Stud.IP-Nutzer weitergeleitet")?>&nbsp;
					
					<?
					if ($smsforward['active'] == "1") {
						
						?><br>&nbsp;&nbsp;<input type="CHECKBOX" 
						<? if ($smsforward['copy'] != "2") echo " checked"; ?>
						value="1" name="smsforward_copy">&nbsp;&nbsp;&nbsp;<?=("eine Kopie im persönliche Posteingang speichern")?><br>&nbsp;&nbsp;
						<input type="hidden" name="smsforward_copy_orig" value="<?=$smsforward['copy']?>"><?

						if ($search_exp != "" && $gosearch_x) {
							$query = "SELECT username, ".$_fullname_sql['full_rev']." AS fullname, perms FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE (username LIKE '%$search_exp%' OR Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%') ORDER BY Nachname ASC";
							$db->query($query); //
							if (!$db->num_rows()) {
								echo "&nbsp;<input type=\"image\" name=\"reset_freesearch\" src=\"./pictures/rewind.gif\" border=\"0\" value=\""._("Suche zur&uuml;cksetzen")."\" ".tooltip(_("setzt die Suche zurück")).">";
								echo "&nbsp;<font size=\"-1\">"._("keine Treffer")."</font>";
							} else {
								echo "<input type=\"image\" name=\"add_smsforward_rec\" ".tooltip(_("als Empfänger weitergeleiteter Nachrichten eintragen"))." value=\""._("als Empfänger auswählen")."\" src=\"./pictures/vote_answer_correct.gif\" border=\"0\">&nbsp;";
								echo "<select size=\"1\" width=\"100\" name=\"smsforward_rec\">";
								while ($db->next_record()) {
									if (get_username($user->id) != $db->f("username")) {
										echo "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))." (".$db->f("username").") - ".$db->f("perms")."</option>";
									}							
								}
								echo "</select>";
								echo "<input type=\"image\" name=\"reset_serach\" src=\"./pictures/rewind.gif\" border=\"0\" value=\""._("Suche zur&uuml;cksetzen")."\" ".tooltip(_("setzt die Suche zurück")).">";
							}
						} else {
							echo "<input type=\"text\" name=\"search_exp\" size=\"40\" value=\"";
							if ($smsforward['rec'] != "0") {
								echo get_username($smsforward['rec']);
							}
							echo "\">";
							echo "<input type=\"image\" name=\"gosearch\" src=\"./pictures/suchen.gif\" border=\"0\">";
						}
					}
					?>
					</font>
					</td>
				</tr>

<!-- 
				<tr <? #$cssSw->switchClass() ?>>
					<td class="<? #echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Nachrichten hoovern")?>
					</td>
					<td class="<? #echo $cssSw->getClass() ?>" width="70%">&nbsp;<font size=-1> 
					<input type="radio" name="hoover" value="1" <? #echo CheckChecked("1", $my_messaging_settings["hoover"]) ?>>ja
					<input type="radio" name="hoover" value="0" <? #echo CheckChecked("0", $my_messaging_settings["hoover"]) ?>>nein</font>
					</td>
				</tr>
 -->				
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Neue aufgeklappen:")?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="70%">&nbsp;<font size=-1> 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["opennew"] != "2") echo " checked"; ?>
					 value="1" name="opennew">
					&nbsp;<?#=_("Neue Nachrichten werden automatisch aufgeklappt")?></font>
					</td>
				</tr>
				
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("immer aufgeklappen:")?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="70%">&nbsp;<font size=-1> 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["openall"] != "2") echo " checked"; ?>
					 value="1" name="openall">
					&nbsp;<?=#_("Alle Nachrichten werden immer automatisch aufgeklappt dargestellt")?></font>					
					</td>
				</tr>

				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Gesendete speichern:")?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="70%">&nbsp;<font size=-1> 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["save_snd"] != "2") echo " checked"; ?>
					 value="1" name="save_snd">
					&nbsp;&nbsp;<?#=_("Gesendete Nachrichten werden im Postausgang gespeichert")?></font>					
					</td>
				</tr>

<!-- 
				<tr <? #$cssSw->switchClass() ?>>
					<td class="<? #echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Vorschau")?>
					</td>
					<td class="<? #echo $cssSw->getClass() ?>" width="70%">&nbsp;<font size=-1> 
					<input type="radio" name="preview" value="1" <? #echo CheckChecked("1", $my_messaging_settings["preview"]) ?>>ja
					<input type="radio" name="preview" value="0" <? #echo CheckChecked("0", $my_messaging_settings["preview"]) ?>>nein
					&nbsp;<?=_("Zeige Vorschau bei Schreiben von Nachrichten")?></font>
					</td>
				</tr>
 -->
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Zeitfilter der Anzeige")?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="70%">&nbsp;&nbsp; 
					<select name="timefilter">
					<?  
					printf("<option value=\"%s\" %s>%s</option>", "new", CheckSelected($my_messaging_settings["timefilter"], "new"), _("neue Nachrichten"));
					printf("<option value=\"%s\" %s>%s</option>", "all", CheckSelected($my_messaging_settings["timefilter"], "all"), _("alle Nachrichten"));
					printf("<option value=\"%s\" %s>%s</option>", "24h", CheckSelected($my_messaging_settings["timefilter"], "24h"), _("letzte 24 Stunden"));
					printf("<option value=\"%s\" %s>%s</option>", "7d", CheckSelected($my_messaging_settings["timefilter"], "7d"), _("letzte 7 Tage"));
					printf("<option value=\"%s\" %s>%s</option>", "30d", CheckSelected($my_messaging_settings["timefilter"], "30d"), _("letzte 30 Tage"));
					printf("<option value=\"%s\" %s>%s</option>", "older", CheckSelected($my_messaging_settings["timefilter"], "older"), _("&auml;lter als 30 Tage"));
					?>
					</select>
					&nbsp;<font size=-1> <?=_("Voreingestellter Anzeige-Filter der anzeigten Nachrichten.")?></font>
					</td>
				</tr>

				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Signatur:")?>
					<td class="<? echo $cssSw->getClass() ?>"><font size=-1>&nbsp; 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["addsignature"] != "2") echo " checked"; ?>
					 value="1" name="addsignature">&nbsp;<?=_("Signatur anhängen.")?>
					</font>
					<br>&nbsp;&nbsp;
					<textarea name="sms_sig" rows=5 cols=40><? echo htmlready($my_messaging_settings["sms_sig"]); ?></textarea>
					</td>
				</tr>

				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan=2>
					&nbsp; &nbsp; <b><?=_("Stud.IP-Messenger")?></b>
					</td>
				</tr>

				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Starten:")?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>">&nbsp; 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["start_messenger_at_startup"]) echo " checked"; ?>
					 name="start_messenger_at_startup">
					&nbsp; <font size=-1><?=_("Stud.IP-Messenger automatisch nach dem Login starten")?></font>
					</td>
				</tr>

				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan=2>
					&nbsp; &nbsp; <b><?=_("Buddies / Wer ist online")?></b><a name="buddy_anker"></a>
					</td>
				</tr>
				<?
				if (GetNumberOfBuddies()) {
				?>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Anzeige:")?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>">&nbsp; 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["show_only_buddys"]) echo " checked"; ?>
					 name="show_only_buddys">
					&nbsp; <font size=-1><?=_("Nur Buddies in der &Uuml;bersicht der aktiven Benutzer anzeigen")?></font>
					</td>
				</tr>
				<?
				}
				?>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Dauer bis inaktiv:")?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>">&nbsp;&nbsp; 
					<select name="active_time">
					<? 
					for ($i=0; $i<=15; $i=$i+5) {
					if ($i)
						if ($my_messaging_settings["active_time"] == $i) 
							echo "<option selected>$i</option>";
						else
							echo "<option>$i</option>"; 
							}
					?>
					</select>
					&nbsp; <font size=-1><?=_("Anzahl der Minuten, nach denen ein(e) Nutzer(in) nicht mehr angezeigt wird")?></font>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">&nbsp;
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%"><br>&nbsp; 
					<font size=-1><input type="IMAGE" <?=makeButton("uebernehmen", "src")?> border=0 align="absmiddle" value="<?=_("&Auml;nderungen &uuml;bernehmen")?>"></font>&nbsp; 
					<?
					echo "<a href=\"$i_page\">" . makeButton("zurueck2", "img") . "</a>";
					?>
					<input type="HIDDEN" name="view" value="Messaging">
					</td>
				</tr>
			</table>
		</form>	
	<?
	}

?>
