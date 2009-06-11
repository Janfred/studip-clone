<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
	<td class="topic" colspan=1><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/nutzer.gif" border="0" align="texttop"><b>&nbsp;Stud.IP-Messenger (<?=$username?>)</b></td>
	</tr>
	
	<tr>
	<td class="blank" width="50%" valign="top" id="online_list"><br>
	<table width="100%" border=0 cellpadding=1 cellspacing=0 valign="top">
		<tr><td valign="top">
		<table width="100%" border=0 cellpadding=1 cellspacing=0 valign="top">
<?php
$template = $GLOBALS['template_factory']->open('studipim_onlinelist');
$template->set_attribute('GLOBALS', $GLOBALS);
$template->set_attribute('online', $online);
$template->set_attribute('my_messaging_settings', $my_messaging_settings);
$template->set_attribute('new_msg', $new_msg);
$template->set_attribute('old_msg', $old_msg);
$template->set_attribute('new_msgs', $new_msgs);
echo $template->render();
?>
	</td>
	</tr>
	
	<tr>
	<td>
	<table id="readthenews" style="visibility: visible" width="100%" border=0 cellpadding=1 cellspacing=0 valign="top"><tr><td id="readthenews_cell" class="blank" colspan="2" valign="middle"></td></tr>
	</table>
	</td>
	</tr>	
	
	<tr>
	<td>
	<table id="messenger_writer" style="visibility:collapse" width="100%" border=0 cellpadding=1 cellspacing=0 valign="top">
		<tr>
		<td class='blank' colspan='2' valign='middle'>
		<table width="100%" border=0 cellpadding=1 cellspacing=0 valign="top">
			<tr><td class='blank' colspan='2' valign='middle'><font size=-1>
			<?php	print _("Ihre Nachricht an ").'<b><span id="adressat"></span>: </b></font>'; ?>
			</td></tr>

			<form  name='eingabe' action='sms_send.php' method='post'>
			<input type='hidden'  name='msg_rec' value=''>
			<input type='hidden'  name='msg_subject' value=''>

			<tr><td class='blank' colspan='2' valign='middle'>
			<textarea  style="width: 100%" name='nu_msg' rows='4' cols='44' wrap='virtual'></textarea></font><br>
			<font size=-1><a target="_blank" href="show_smiley.php"><?= _("Smileys</a> k&ouml;nnen verwendet werden") ?></font>
			</td></tr>
			<tr><td class='blank' colspan='2' valign='middle' align='center'><font size=-1>&nbsp;
			<a href="Javascript: studipim.send()">
			<img "<?= makeButton("absenden","src") . tooltip(_("Nachricht versenden")) ?> border=0 />
			</a>
			<a href="Javascript: studipim.close_writer(false)">
			<img "<?= makeButton("abbrechen","src") . tooltip(_("Vorgang abbrechen")) ?> border=0 />
			</a>
			<a href="Javascript: studipim.settings()">
			<img src="assets/images/edit_transparent.gif" border=0>
			</a>
			</form>	

			</font></td></tr>
			</td></tr>
		</table>
		</td>
		</tr>

	</table>
	</td>
	</tr>
</table>