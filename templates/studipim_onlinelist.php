<?php
$c=0;
?>
	<br><table width="100%" border=0 cellpadding=1 cellspacing=0 valign="top">
		<tr><td valign="top">
		<table width="100%" border=0 cellpadding=1 cellspacing=0 valign="top">
<?php
		
		if (is_array($online)) {
			foreach($online as $tmp_uname => $detail){
				if ($detail['is_buddy']){
					if (!$c){
						echo "<tr><td class=\"blank\" colspan=2 align=\"left\" ><font size=-1><b>" . _("Buddies:") . "</b></td></tr>";
					}
					echo "<tr><td class='blank' width='90%' align='left'><font size=-1><a " . tooltip(sprintf(_("letztes Lebenszeichen: %s"),date("i:s",$detail['last_action'])),false) . " href=\"javascript: studipim.coming_home('about.php?username=$tmp_uname');\">"
					. Avatar::getAvatar($detail['user_id'])->getImageTag(Avatar::SMALL)."&nbsp;".htmlReady($detail['name'])."</a></font></td>\n";
					echo "<td  class='blank' width='10%' align='middle'><font size=-1><a href=\"Javascript: studipim.write_to('$tmp_uname', '".$detail['name']."', '"._("Ohne Betreff")."', '')\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" ".tooltip(_("Nachricht an User verschicken"))." border=\"0\" width=\"24\" height=\"21\"></a></font></td></tr>";
					$c++;
				}
			}
		} else {
			echo "<tr><td class='blank' colspan='2' align='left' ><font size=-1>" . _("Kein Nutzer ist online.") . "</font>";
		}
		if (!$my_messaging_settings["show_only_buddys"]) {
			if ((sizeof($online)-$c) == 1) {
				echo "<tr><td class=\"blank\" colspan=2 align=\"left\"><font size=-1>" . _("Es ist ein anderer Nutzer online.");
				printf ("&nbsp;<a href=\"javascript:studipim.coming_home('online.php')\"><font size=-1>" . _("Wer?") . "</font></a>");
			}
			elseif((sizeof($online)-$c) > 1) {
				printf ("<tr><td class=\"blank\" colspan=2 align=\"left\"><font size=-1>" . _("Es sind %s andere Nutzer online.") , sizeof($online)-$c);
				printf ("&nbsp;<a href=\"javascript:studipim.coming_home('online.php')\"><font size=-1>" . _("Wer?") . "</font></a>");
			}
		}
?>
			</td></tr>
		</table>
		</td>
		<td class="blank" width="50%" valign="top" id="incoming"><br><font size=-1>
<?php
		if ($old_msg)
			printf(_("%s alte Nachricht(en)&nbsp;%s[lesen]%s"),$old_msg,"<a href=\"javascript: studipim.coming_home('sms_box.php?sms_inout=in')\">","</a><br>");
		elseif (!$new_msg)
			print (_("Keine Nachrichten") . "<br>");
		else
			print (_("Keine alten Nachrichten") . "<br>");
	
		if ($new_msg) {
			printf ("<br /><b>"._("%s neue Nachrichten:") . "</b><br />", $new_msg);
			foreach ($new_msgs as $val)
					print "<br />".$val;
		}
?>
		</font><br>&nbsp
		</td></tr>
	</table>
