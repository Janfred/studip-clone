<?

require_once ($ABSOLUTE_PATH_STUDIP."/language.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/config.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/functions.php");
require_once ($ABSOLUTE_PATH_STUDIP."/visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/messaging.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/contact.inc.php");

$msging=new messaging;

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
} else {
	#if (empty($my_messaging_settings["hoover"])) {
		#$my_messaging_settings["hoover"] = "0";
	#}
	if (empty($my_messaging_settings["save_snd"])) {
		$my_messaging_settings["save_snd"] = "1";
	}
	if (empty($my_messaging_settings["openall"])) {
		$my_messaging_settings["openall"] = "2";
	}
	if (empty($my_messaging_settings["timefilter"])) {
		$my_messaging_settings["timefilter"] = "all";
	}
	if (empty($my_messaging_settings["addsignature"])) {
		$my_messaging_settings["addsignature"] = "0";
	}
	if (empty($my_messaging_settings["opennew"])) {
		$my_messaging_settings["opennew"] = "1";
	}
}

if ($do_add_user_x)
	$msging->add_buddy ($add_user);



//Anpassen der Ansicht
function change_messaging_view() {
	global $_fullname_sql,$my_messaging_settings, $PHP_SELF, $perm, $user, $search_exp, $add_user, $add_user_x, $do_add_user_x, $new_search_x, $i_page;
		
	$db=new DB_Seminar;
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
					&nbsp; &nbsp; <b><?=_("Systeminterne Kurznachrichten")?></b>
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
					&nbsp;<font size=-1> <?=_("Alle Nachrichten automatisch nach dem Logout l&ouml;schen")?></font>
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
					<input type="radio" name="opennew" value="1" <? echo CheckChecked("1", $my_messaging_settings["opennew"]) ?>>ja
					<input type="radio" name="opennew" value="2" <? echo CheckChecked("2", $my_messaging_settings["opennew"]) ?>>nein
					&nbsp;<?#=_("Neue Nachrichten werden automatisch aufgeklappt")?></font>
					</td>
				</tr>
				
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("immer aufgeklappen:")?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="70%">&nbsp;<font size=-1> 
					<input type="radio" name="openall" value="1" <? echo CheckChecked("1", $my_messaging_settings["openall"]) ?>>ja
					<input type="radio" name="openall" value="2" <? echo CheckChecked("2", $my_messaging_settings["openall"]) ?>>nein
					&nbsp;<?=#_("Alle Nachrichten werden immer automatisch aufgeklappt dargestellt")?></font>					
					</td>
				</tr>

				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="30%">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=_("Gesendete speichern:")?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="70%">&nbsp;<font size=-1> 
					<input type="radio" name="save_snd" value="1" <? echo CheckChecked("1", $my_messaging_settings["save_snd"]) ?>>ja
					<input type="radio" name="save_snd" value="2" <? echo CheckChecked("2", $my_messaging_settings["save_snd"]) ?>>nein
					&nbsp;<?#=_("Gesendete Nachrichten werden im Postausgang gespeichert")?></font>					
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
					<td class="<? echo $cssSw->getClass() ?>" width="70%">&nbsp; 
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
					<td class="<? echo $cssSw->getClass() ?>">&nbsp;<font size=-1> 
					<input type="radio" name="addsignature" value="1" <? echo CheckChecked("1", $my_messaging_settings["addsignature"]) ?>>ja
					<input type="radio" name="addsignature" value="0" <? echo CheckChecked("0", $my_messaging_settings["addsignature"]) ?>>nein
					&nbsp;&nbsp;<?=_("gesendeten Nachrichten eine Signatur anhängen") ?>
					</font>
					<br>&nbsp;
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
					<td class="<? echo $cssSw->getClass() ?>">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?echo _("Buddies verwalten:");?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>"><font size=-1>&nbsp;&nbsp;
					<?
					printf(_("Zum Verwalten Ihrer Buddies besuchen Sie bitte das %sAdressbuch%s."), "<a href=\"contact.php\">", "</a>");
					?>
					</font></td>
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
