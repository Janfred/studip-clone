<?
//Variable registrieren
//$user->register("my_messaging_settings");

require_once ($ABSOLUTE_PATH_STUDIP."/config.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/functions.php");
require_once ($ABSOLUTE_PATH_STUDIP."/visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/messaging.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/contact.inc.php");

$msging=new messaging;

//vorgenommene Anpassungen der Ansicht in Uservariablen schreiben
if ($messaging_cmd=="change_view_insert") {

	$my_messaging_settings["changed"]=TRUE;
	$my_messaging_settings["show_only_buddys"]=$show_only_buddys;
	$my_messaging_settings["delete_messages_after_logout"]=$delete_messages_after_logout;
	$my_messaging_settings["start_messenger_at_startup"]=$start_messenger_at_startup;
	$my_messaging_settings["active_time"]=$active_time;
	$my_messaging_settings["sms_sig"]=$sms_sig;
	$my_messaging_settings["changed"]="TRUE";
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
		<td class="topic" colspan=2><img src="pictures/meinesem.gif" border="0" align="texttop"><b>&nbsp;Einstellungen f&uuml;r das Stud.IP Messaging anpassen</b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>&nbsp;
			<blockquote>
				Hier k&ouml;nnen Sie sie Einstellungen f&uuml;r das Stud.IP-Messaging &auml;ndern. <br />
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
					&nbsp; &nbsp; <b>Systeminterne Kurznachrichten (SMS)</b>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>automatisches L&ouml;schen:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">&nbsp; 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["delete_messages_after_logout"]) echo " checked"; ?>
					 name="delete_messages_after_logout">
					&nbsp; Gelesene Nachrichten automatisch nach dem Logout l&ouml;schen
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>Signatur:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">&nbsp; 
					<textarea name="sms_sig" rows=3 cols=40><? echo htmlready($my_messaging_settings["sms_sig"]); ?></textarea>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan=2>
					&nbsp; &nbsp; <b>Stud.IP Messenger</b>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>Starten:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">&nbsp; 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["start_messenger_at_startup"]) echo " checked"; ?>
					 name="start_messenger_at_startup">
					&nbsp; Stud.IP-Messenger automatisch nach dem Login starten
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan=2>
					&nbsp; &nbsp; <b>Buddies / Wer ist online</b><a name="buddy_anker"></a>
					</td>
				</tr>
				<?
				if (GetNumberOfBuddies()) {
				?>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>Anzeige:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">&nbsp; 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["show_only_buddys"]) echo " checked"; ?>
					 name="show_only_buddys">
					&nbsp; Nur Buddys in der &Uuml;bersicht der aktiven Benutzer anzeigen
					</td>
				</tr>
				<?
				}
				?>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>Dauer bis inaktiv:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">&nbsp; Benutzer nach&nbsp; 
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
					&nbsp; Minuten nicht mehr anzeigen
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b><?echo _("Buddys verwalten:");?></b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">
					<?
					printf(_("Zum Verwalten Ihrer Buddies besuchen Sie bitte das %s Adressbuch."), "<a href=\"contact.php\">");
					echo "</a>";
					?>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">&nbsp;
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%"><br>&nbsp; 
					<font size=-1><input type="IMAGE" src="pictures/buttons/uebernehmen-button.gif" border=0 value="&Auml;nderungen &uuml;bernehmen"></font>&nbsp; 
					<?
					if ($i_page == "online.php")
						echo "<a href=\"online.php\"><img src=\"pictures/buttons/zurueck2-button.gif\" border=0></a>";
					if ($i_page == "sms.php")
						echo "<a href=\"sms.php\"><img src=\"pictures/buttons/zurueck2-button.gif\" border=0></a>";
					?>
					<input type="HIDDEN" name="view" value="Messaging">
					</td>
				</tr>
			</table>
		</form>	
	<?
	}

?>