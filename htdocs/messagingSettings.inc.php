<?

require_once ($ABSOLUTE_PATH_STUDIP."/language.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/config.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/functions.php");
require_once ($ABSOLUTE_PATH_STUDIP."/visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/messaging.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/contact.inc.php");

$msging=new messaging;
$db2=new DB_Seminar;
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


if (!empty($new_folder_in) && $new_folder_in_button_x) {
	$my_messaging_settings["folder"]['in'] = array_add_value($new_folder_in, $my_messaging_settings["folder"]['in']);
}
if (!empty($new_folder_out) && $new_folder_out_button_x) {
	$my_messaging_settings["folder"]['out'] = array_add_value($new_folder_out, $my_messaging_settings["folder"]['out']);
}
if ($delete_folder_out && $delete_folder_out_button_x) {
	$query = "UPDATE message_user SET folder='' WHERE folder='".$delete_folder_out."' AND snd_rec='snd'";
	$db2->query($query);
	$my_messaging_settings["folder"]['out'] = array_delete_value($my_messaging_settings["folder"]['out'], $delete_folder_out);
}
if (!empty($delete_folder_in) && $delete_folder_in_button_x) {
	$query = "UPDATE message_user SET folder='' WHERE folder='".$delete_folder_in."' AND user_id='".$user->id."' AND snd_rec='rec'";
	$db2->query($query);	
	$my_messaging_settings["folder"]['in'] = array_delete_value($my_messaging_settings["folder"]['in'], $delete_folder_in);
}
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
	global $_fullname_sql,$my_messaging_settings, $PHP_SELF, $perm, $user, $search_exp, $add_user, $add_user_x, $do_add_user_x, $new_search_x, $i_page, $search_exp, $gosearch_x, $show_form;
		
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
<!-- 
				<tr <? #$cssSw->switchClass() ?>>
					<td class="<? #echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("automatisches L&ouml;schen:")?>
					</td>
					<td class="<? #echo $cssSw->getClass() ?>" width="70%">&nbsp; 
					<? #echo ".".$my_messaging_settings["delete_messages_after_logout"].".";	 ?>
					<input type="CHECKBOX" 
					<? #if ($my_messaging_settings["delete_messages_after_logout"]) echo " checked"; ?>
					 name="delete_messages_after_logout">
					&nbsp;&nbsp;<font size=-1> <?=_("Alle Nachrichten automatisch nach dem Logout l&ouml;schen")?></font>
					</td>
				</tr>
 
				<tr <? #$cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("automatisches L&ouml;schen:")?>
					</td>
					<td class="<? echo #$cssSw->getClass() ?>" width="70%">&nbsp;<font size=-1>
					<?
					if ($my_messaging_settings["delete_messages_after_logout"] == "on") {
						$my_messaging_settings["delete_messages_after_logout"] = "all";
					} else if (!$my_messaging_settings["delete_messages_after_logout"]) {
						$my_messaging_settings["delete_messages_after_logout"] = "no";
					}
					echo $my_messaging_settings["delete_messages_after_logout"];
					?>
					
					&nbsp;<select name="delete_messages_after_logout">
					<?  
					#printf("<option value=\"%s\" %s>%s</option>", "all", CheckSelected($my_messaging_settings["delete_messages_after_logout"], "all"), _("alle"));
					#printf("<option value=\"%s\" %s>%s</option>", "7d", CheckSelected($my_messaging_settings["delete_messages_after_logout"], "7d"), _("älter als 7 Tage"));
					#printf("<option value=\"%s\" %s>%s</option>", "30d", CheckSelected($my_messaging_settings["delete_messages_after_logout"], "30d"), _("älter als 30 Tage"));
					#printf("<option value=\"%s\" %s>%s</option>", "no", CheckSelected($my_messaging_settings["delete_messages_after_logout"], "no"), _("keine"));
					?>
					</select>
					</font>
					</td>
				</tr>
 
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
					<?=_("Neue Aufgeklappen:")?>
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
					<?=_("immer Aufgeklappen:")?>
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

				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Weiterleitung:")?>
					</td> <?
					$query = "SELECT * FROM user_info WHERE user_id='".$user->id."'";
					$db2->query($query);
					while ($db2->next_record()) {
						$smsforward['active'] = $db2->f("smsforward_active");
						$smsforward['copy'] = $db2->f("smsforward_copy");
						$smsforward['rec'] = $db2->f("smsforward_rec");
					} ?>
					<td class="<? echo $cssSw->getClass() ?>" width="70%"><font size=-1> <?
					if ($smsforward['active'] == "1") { // wenn umleitung aktiv
						if ($smsforward['rec']) { // empfaenger ausgewaehlt
							printf("&nbsp;&nbsp;"._("%s%s%s ist der Empfänger der weitergeleiteten Nachrichten.")."<br>", "<a href=\"about.php?username=".get_username($smsforward['rec'])."\">", get_fullname($smsforward['rec']), "</a>");
						} else { // kein empfaenger ausgewaehlt
							$show_form = "1";
							printf(_("Es ist kein Empfänger ausgewählt."));	
						}
					}

					echo "&nbsp;&nbsp;<input type=\"CHECKBOX\"";
					if ($smsforward['active'] != "2") { 
						echo " checked "; 
					}
					echo "value=\"1\" name=\"smsforward_active\">&nbsp;&nbsp;"._("Empfangene Nachrichten weitergeleiten.");
					
					if ($smsforward['active'] == "1") { // wenn umleitung aktiv
						
						?><br>&nbsp;&nbsp;<input type="CHECKBOX" 
						<? if ($smsforward['copy'] != "2") echo " checked"; ?>
						value="1" name="smsforward_copy">&nbsp;&nbsp;<?=("Kopie im persönlichen Posteingang speichern.")?>
						<input type="hidden" name="smsforward_copy_orig" value="<?=$smsforward['copy']?>"><br><?

						if ($search_exp != "" && $gosearch_x) { // auswahl
							$db->query("SELECT username, ".$_fullname_sql['full_rev']." AS fullname, perms FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE (username LIKE '%$search_exp%' OR Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%') ORDER BY Nachname ASC"); 
							if (!$db->num_rows()) { // wenn keine treffer
								echo "&nbsp;<input type=\"image\" name=\"reset_freesearch\" src=\"./pictures/rewind.gif\" border=\"0\" value=\""._("Suche zur&uuml;cksetzen")."\" ".tooltip(_("setzt die Suche zurück")).">";
								echo "<font size=\"-1\">&nbsp;"._("keine Treffer")."</font>";
							} else { // treffer auswählen
								echo "&nbsp;&nbsp;<input type=\"image\" name=\"add_smsforward_rec\" ".tooltip(_("als Empfänger weitergeleiteter Nachrichten eintragen"))." value=\""._("als Empfänger auswählen")."\" src=\"./pictures/vote_answer_correct.gif\" border=\"0\">&nbsp;&nbsp;";
								echo "<select size=\"1\" width=\"100\" name=\"smsforward_rec\">";
								while ($db->next_record()) {
									if (get_username($user->id) != $db->f("username")) {
										echo "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))." (".$db->f("username").") - ".$db->f("perms")."</option>";
									}							
								}
								echo "</select>";
								echo "<input type=\"image\" name=\"reset_serach\" src=\"./pictures/rewind.gif\" border=\"0\" value=\""._("Suche zur&uuml;cksetzen")."\" ".tooltip(_("setzt die Suche zurück")).">";
							}
						} else if ($show_form == "1" || !empty($show_form_x)) { // suchinput
							echo "&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"search_exp\" size=\"30\" value=\"\">";
							echo "<input type=\"image\" name=\"gosearch\" src=\"./pictures/suche2.gif\" border=\"0\">";
						} else {
							#echo "&nbsp;&nbsp;";
							echo "&nbsp;&nbsp;<input type=\"image\" name=\"show_form\" value=\"1\" src=\"./pictures/suche2.gif\" border=\"0\" ".tooltip(_("Suche nach Empfänger anzeigen.")).">";
							echo "&nbsp;&nbsp;"._("Neuen Empfänger suchen.");
						}
					}
					?>
					</font>
					</td>
				</tr>

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
					<td class="<? echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Eigene Ordner:")?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="70%"><font size=-1>
					<?
					printf("&nbsp;&nbsp;<b>%s:</b><br>", _("Posteingang"));
					printf("&nbsp;&nbsp;<input type=\"text\" name=\"new_folder_in[]\" value=\"\" size=\"25\" maxlength=\"255\">&nbsp;<input type=\"image\" name=\"new_folder_in_button\" %s  src=\"./pictures/cont_folder_add.gif\" border=\"0\">&nbsp;%s<br>", tooltip(_("Erstellt einen neuen Ordner im Posteingang.")), _("Neuen Ordner anlegen"));
					if (!empty($my_messaging_settings["folder"]['in'])) {
						printf("&nbsp;&nbsp;<select name=\"delete_folder_in\" style=\"width:175px\">");
						for($x="0";$x<sizeof($my_messaging_settings["folder"]['in']);$x++) {
							printf("<option>".$my_messaging_settings["folder"]['in'][$x]."</option>");
						}
						printf("</select>&nbsp;<input type=\"image\" name=\"delete_folder_in_button\" %s src=\"./pictures/trash.gif\" border=\"0\">&nbsp;&nbsp;%s<br>", tooltip(_("Entfernt den Ordner. Nachrichten aus diesem Ordner werden in \"Unzugeordnet\" verschoben.")), _("Ordner entfernen"));
					}
					printf("&nbsp;&nbsp;<b>%s:</b><br>", _("Postausgang"));
					printf("&nbsp;&nbsp;<input type=\"text\" name=\"new_folder_out[]\" value=\"\" size=\"25\" maxlength=\"255\">&nbsp;<input type=\"image\" name=\"new_folder_out_button\" %s src=\"./pictures/cont_folder_add.gif\" border=\"0\">&nbsp;%s<br>", tooltip(_("Erstellt einen neuen Ordner im Postausgang.")), _("Neuen Ordner anlegen"));
					if (!empty($my_messaging_settings["folder"]['out'])) {
						printf("&nbsp;&nbsp;<select name=\"delete_folder_out\" style=\"width:175px\">");
						for($x="0";$x<sizeof($my_messaging_settings["folder"]['out']);$x++) {
							printf("<option>".$my_messaging_settings["folder"]['out'][$x]."</option>");
						}
						printf("</select>&nbsp;<input type=\"image\" name=\"delete_folder_out_button\" %s src=\"./pictures/trash.gif\" border=\"0\">&nbsp;&nbsp;%s<br>", tooltip(_("Entfernt den Ordner. Nachrichten aus diesem Ordner werden in \"Unzugeordnet\" verschoben.")), _("Ordner entfernen"));
					}
					?>
					</font>
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
