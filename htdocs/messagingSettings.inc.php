<?
/*
messagingSettings.php
Copyright (C) 2003 Nils K. Windisch <info@nkwindisch.de>

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

require_once ($ABSOLUTE_PATH_STUDIP."/language.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/config.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/functions.php");
require_once ($ABSOLUTE_PATH_STUDIP."/visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/messaging.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/contact.inc.php");

$db2=new DB_Seminar;
$db3=new DB_Seminar;

## ACTION ##

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
	$my_messaging_settings["hover"] = $hover;
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

if (!empty($new_folder_in) && $new_folder_in_button_x) {
	$my_messaging_settings["folder"]['in'] = array_add_value($new_folder_in, $my_messaging_settings["folder"]['in']);
}
if (!empty($new_folder_out) && $new_folder_out_button_x) {
	$my_messaging_settings["folder"]['out'] = array_add_value($new_folder_out, $my_messaging_settings["folder"]['out']);
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

## FUNCTION ##


function change_messaging_view() {
	global $_fullname_sql,$my_messaging_settings, $PHP_SELF, $perm, $user, $search_exp, $add_user, $add_user_x, $do_add_user_x, $new_search_x, $i_page, $search_exp, $gosearch_x, $show_form;
	$msging=new messaging;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;	
	$cssSw=new cssClassSwitcher;	
	?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">
		<tr>
			<td class="topic" colspan=2><img src="pictures/einst.gif" border="0" align="texttop"><b>&nbsp;<?print _("Einstellungen des Messagings anpassen");?></b></td>
		</tr>
		<tr>
			<td class="blank" colspan=2>&nbsp;
			</td>
		</tr>
		<tr>
			
			<td class="blank" width="100%" colspan="2" align="center">
			<blockquote>
				<font size="-1"><b><?print _("Auf dieser Seite k&ouml;nnen Sie die Eigenschaften des Stud.IP-Messagingsystems an Ihre Bed&uuml;rfnisse anpassen.");?>
			</blockquote>			
			<form action="<?=$PHP_SELF?>?messaging_cmd=change_view_insert" method="post">
			<table width="70%" align="center"cellpadding=8 cellspacing=0 border=0>
				<tr>
					<th width="50%" align=center><?=_("Option")?></th>
					<th align=center><?=_("Auswahl")?></th>
				</tr>
				<tr>
					<td colspan="2" align="center" class="steelgraulight" style="border-bottom:1px dotted black;border-top:1px dotted black;"><font size="-1"><b><?=_("Einstellungen des system-internen Nachrichten-Systems")?></b></font></td>
				</tr>
				<tr  <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1"><?print _("Neue Nachrichten immer aufgeklappt");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" value="1" name="opennew"<? if ($my_messaging_settings["opennew"] != "2") echo " checked"; ?>>
					</td>
				</tr>
			
				<tr  <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1"><?print _("Alle Nachrichten immer aufgeklappt");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" value="1" name="openall"<? if ($my_messaging_settings["openall"] != "2") echo " checked"; ?>>
					</td>
				</tr>

				<tr  <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1"><?print _("Gesendete Nachrichten im Postausgang speichern");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" value="1" name="save_snd"<? if ($my_messaging_settings["save_snd"] != "2") echo " checked"; ?>>
					</td>
				</tr>	

				<tr  <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1"><?print _("Nachrichten hovern");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" value="1" name="hover"<? if ($my_messaging_settings["hover"] == "1") echo " checked"; ?>>
					</td>
				</tr>	

				<tr <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1">
						<?print _("Weiterleitung empfangener Nachrichten");?></font>
					</td> <?
						$query = "SELECT * FROM user_info WHERE user_id='".$user->id."'";
						$db2->query($query);
						while ($db2->next_record()) {
							$smsforward['active'] = $db2->f("smsforward_active");
							$smsforward['copy'] = $db2->f("smsforward_copy");
							$smsforward['rec'] = $db2->f("smsforward_rec");
						} ?>
					<td <?=$cssSw->getFullClass()?>> <?
						if ($smsforward['active'] == "1") { // wenn umleitung aktiv
							if ($smsforward['rec']) { // empfaenger ausgewaehlt
								printf("&nbsp;<font size=\"-1\">"._("%s%s%s ist der Empfänger der weitergeleiteten Nachrichten.")."</font><br>", "<a href=\"about.php?username=".get_username($smsforward['rec'])."\">", get_fullname($smsforward['rec']), "</a>");
							} else { // kein empfaenger ausgewaehlt
								$show_form = "1";
								printf("<font size=\"-1\">"._("Es ist kein Empfänger ausgewählt.")."</font>");	
							}
						} ?>
						<input type="checkbox" value="1" name="smsforward_active" <? if ($smsforward['active'] != "2") { echo " checked "; } ?>>
						<font size="-1">&nbsp;<?=_("Empfangene Nachrichten weiterleiten.")?></font> <?
						if ($smsforward['active'] == "1") { // wenn umleitung aktiv ?>
							<br><input type="checkbox" value="1" name="smsforward_copy" <? if ($smsforward['copy'] != "2") echo " checked"; ?>>
							&nbsp;<font size="-1"><?=("Kopie im persönlichen Posteingang speichern.")?></font>
							<input type="hidden" name="smsforward_copy_orig" value="<?=$smsforward['copy']?>"><br><?
							if ($search_exp != "" && $gosearch_x) { // auswahl
								$db->query("SELECT username, ".$_fullname_sql['full_rev']." AS fullname, perms FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE (username LIKE '%$search_exp%' OR Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%') ORDER BY Nachname ASC"); 
								if (!$db->num_rows()) { // wenn keine treffer
									echo "&nbsp;<input type=\"image\" name=\"reset_freesearch\" src=\"./pictures/rewind.gif\" border=\"0\" value=\""._("Suche zur&uuml;cksetzen")."\" ".tooltip(_("setzt die Suche zurück")).">";
									echo "<font size=\"-1\">&nbsp;"._("keine Treffer")."</font>";
								} else { // treffer auswählen
									echo "<input type=\"image\" name=\"add_smsforward_rec\" ".tooltip(_("als Empfänger weitergeleiteter Nachrichten eintragen"))." value=\""._("als Empfänger auswählen")."\" src=\"./pictures/vote_answer_correct.gif\" border=\"0\">&nbsp;&nbsp;";
									echo "<select size=\"1\" width=\"100\" name=\"smsforward_rec\">";
									while ($db->next_record()) {
										if (get_username($user->id) != $db->f("username")) {
											echo "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))." (".$db->f("username").") - ".$db->f("perms")."</option>";
										}							
									} ?>
									</select>
									<input type="image" name="reset_serach" src="./pictures/rewind.gif" border="0" value="<?=_("Suche zur&uuml;cksetzen")?>" <?=tooltip(_("setzt die Suche zurück"))?>> <?
								}
							} else if ($show_form == "1" || !empty($show_form_x)) { // suchinput ?>
								<input type="text" name="search_exp" size="30" value="">
								<input type="image" name="gosearch" src="./pictures/suche2.gif" border="0"><?
							} else {?>
								<input type="image" name="show_form" value="1" src="./pictures/suche2.gif" border="0" <?=tooltip(_("Suche nach Empfänger anzeigen."))?>>
								&nbsp;<font size="-1"><?=_("Neuen Empfänger suchen.")?></font> <?
							}
						} ?>
					</td>
				</tr>	
				<tr <? $cssSw->switchClass() ?>>
					<td align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size=-1><?echo _("Zeitfilter der Anzeige in Postein- bzw. ausgang");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<font size=-1> &nbsp;<select name="timefilter"> <?
						printf("<option value=\"%s\" %s>%s</option>", "new", CheckSelected($my_messaging_settings["timefilter"], "new"), _("neue Nachrichten"));
						printf("<option value=\"%s\" %s>%s</option>", "all", CheckSelected($my_messaging_settings["timefilter"], "all"), _("alle Nachrichten"));
						printf("<option value=\"%s\" %s>%s</option>", "24h", CheckSelected($my_messaging_settings["timefilter"], "24h"), _("letzte 24 Stunden"));
						printf("<option value=\"%s\" %s>%s</option>", "7d", CheckSelected($my_messaging_settings["timefilter"], "7d"), _("letzte 7 Tage"));
						printf("<option value=\"%s\" %s>%s</option>", "30d", CheckSelected($my_messaging_settings["timefilter"], "30d"), _("letzte 30 Tage"));
						printf("<option value=\"%s\" %s>%s</option>", "older", CheckSelected($my_messaging_settings["timefilter"], "older"), _("&auml;lter als 30 Tage")); ?>
						</select>
					</td>
				</tr>	
<!-- 
				<tr <? #$cssSw->switchClass() ?>>
					<td align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size=-1><?echo _("Persönliche Ordner");?></font>
					</td>
					<td <?=#$cssSw->getFullClass()?> align="left"> <font size=-1> <?
						printf("&nbsp;<b>%s:</b><br>", _("Posteingang"));
						printf("&nbsp;<input type=\"text\" name=\"new_folder_in[]\" value=\"\" size=\"25\" maxlength=\"255\">&nbsp;<input type=\"image\" name=\"new_folder_in_button\" %s  src=\"./pictures/cont_folder_add.gif\" border=\"0\">&nbsp;%s<br>", tooltip(_("Erstellt einen neuen Ordner im Posteingang.")), _("Neuen Ordner anlegen"));
						if (!empty($my_messaging_settings["folder"]['in'])) {
							printf("&nbsp;<select name=\"delete_folder_in\" style=\"width:175px\">");
							for($x="0";$x<sizeof($my_messaging_settings["folder"]['in']);$x++) {
								printf("<option>".$my_messaging_settings["folder"]['in'][$x]."</option>");
							}
							printf("</select>&nbsp;<input type=\"image\" name=\"delete_folder_in_button\" %s src=\"./pictures/trash.gif\" border=\"0\">&nbsp;&nbsp;%s<br>", tooltip(_("Entfernt den Ordner. Nachrichten aus diesem Ordner werden in \"Unzugeordnet\" verschoben.")), _("Ordner entfernen"));
						}
						printf("&nbsp;<b>%s:</b><br>", _("Postausgang"));
						printf("&nbsp;<input type=\"text\" name=\"new_folder_out[]\" value=\"\" size=\"25\" maxlength=\"255\">&nbsp;<input type=\"image\" name=\"new_folder_out_button\" %s src=\"./pictures/cont_folder_add.gif\" border=\"0\">&nbsp;%s<br>", tooltip(_("Erstellt einen neuen Ordner im Postausgang.")), _("Neuen Ordner anlegen"));
						if (!empty($my_messaging_settings["folder"]['out'])) {
							printf("&nbsp;<select name=\"delete_folder_out\" style=\"width:175px\">");
							for($x="0";$x<sizeof($my_messaging_settings["folder"]['out']);$x++) {
								printf("<option>".$my_messaging_settings["folder"]['out'][$x]."</option>");
							}
							printf("</select>&nbsp;<input type=\"image\" name=\"delete_folder_out_button\" %s src=\"./pictures/trash.gif\" border=\"0\">&nbsp;&nbsp;%s<br>", tooltip(_("Entfernt den Ordner. Nachrichten aus diesem Ordner werden in \"Unzugeordnet\" verschoben.")), _("Ordner entfernen"));
						} ?>
						</font>
					</td>
				</tr>
 -->
				<tr <? $cssSw->switchClass() ?>>
					<td align="right" class="blank">
						<font size=-1><?echo _("Signatur gesendeten Nachrichten anhängen");?></font>
					</td>
					<td align="left" <?=$cssSw->getFullClass()?>>
						<font size=-1><input type="checkbox" value="1" name="addsignature"<? if ($my_messaging_settings["addsignature"] != "2") echo " checked"; ?>>&nbsp;<?=_("Signatur anhängen")?></font> <br>
						&nbsp;<textarea name="sms_sig" rows=3 cols=30><? echo htmlready($my_messaging_settings["sms_sig"]); ?></textarea>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td colspan="2" align="center" class="steelgraulight" style="border-bottom:1px dotted black;border-top:1px dotted black;"><font size="-1"><b><?=_("Stud.IP-Messenger")?></b></font></td>
				</tr <? $cssSw->switchClass() ?>>
					<td align="right" class="blank">
						<font size=-1><?=_("Stud.IP-Messenger automatisch nach dem Login starten")?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" name="start_messenger_at_startup" <? if ($my_messaging_settings["start_messenger_at_startup"]) echo " checked"; ?> >
					</td>
				<tr <? $cssSw->switchClass() ?>>
					<td colspan="2" align="center" class="steelgraulight" style="border-bottom:1px dotted black;border-top:1px dotted black;"><font size="-1"><b><?=_("Buddies/ Wer ist online?")?></b></font></td>
				</tr>
				<? if (GetNumberOfBuddies()) { ?>                      
				<tr <? $cssSw->switchClass() ?>>
					<td align="right" class="blank">
						<font size=-1><?=_("Nur Buddies in der &Uuml;bersicht der aktiven Benutzer anzeigen")?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" name="show_only_buddys"<? if ($my_messaging_settings["show_only_buddys"]) echo " checked"; ?> >
					</td>
				</tr>
				<? } ?>
				<tr <? $cssSw->switchClass() ?>>
					<td align="right" class="blank">
						<font size=-1><?=_("Dauer bis inaktiv:")?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<select name="active_time"> <? 
						for ($i=0; $i<=15; $i=$i+5) {
							if ($i) {
								if ($my_messaging_settings["active_time"] == $i) {
									echo "<option selected>$i</option>";
								} else {
									echo "<option>$i</option>"; 
								}
							}
						} ?>
                        			</select>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td  <?=$cssSw->getFullClass()?> colspan=2 align="middle">
						<input type="HIDDEN" name="view" value="Messaging">
						<font size=-1><input type="IMAGE" <?=makeButton("uebernehmen", "src") ?> border=0 value="<?_("Änderungen übernehmen")?>"></font>&nbsp;	
					</td>
				</tr>
				</form>	
			</table>
			<br />
			<br />
			</td>
		</tr>
	</table> 
<?
} 
?>