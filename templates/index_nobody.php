<!-- Startseite (nicht eingeloggt) -->
<table class="blank" width="800"  border="0" cellpadding="0" cellspacing="0" align="center" valign="top">
	<tr>
		<td colspan="3" class="topic">
			&nbsp;<b><?=$GLOBALS['UNI_NAME']?></b>
			<img src="<?=$GLOBALS['ASSETS_URL']?>images/blank.gif" height="16" width="5" border="0">
		</td>
	</tr>
	<tr>
		<td height="270" valign="top" colspan="3" background="<?=$GLOBALS['ASSETS_URL']?>images/startseite.jpg">
			<img src="<?=$GLOBALS['ASSETS_URL']?>images/blank.gif" height="30" width="5" border="0">
			<br>
			<table style="margin-left:70px; margin-top:10px;" cellspacing="2" cellpadding="0" border="0">
				<tr>
					<td width="270">
					<a class="index" href="index.php?again=yes">
					<font size="4"><b><?=_("Login")?></b></font>
					<font color="#555555" size="1"><br><?=_("f&uuml;r registrierte NutzerInnen")?></font>
					</a>
					&nbsp;
					</td>
				</tr>
			</table>
			<?if($sso_activated){?>
			<table style="margin-left:70px; margin-top:10px;" cellspacing="2" cellpadding="0" border="0">
				<tr>
					<td width="270">
					<a class="index" href="index.php?again=yes&sso=true">
					<font size="4"><b><?=_("Login")?></b></font>
					<font color="#555555" size="1"><br><?=_("f&uuml;r Single Sign On mit CAS")?></font>
					</a>
					&nbsp;
					</td>
				</tr>
			</table>
			<?}?>
			<?if($self_registration_activated){?>
			<table style="margin-left:70px; margin-top:10px;" cellspacing="2" cellpadding="0" border="0">
				<tr>
					<td width="270">
					<a class="index" href="register1.php">
					<font size="4"><b><?=_("Registrieren")?></b></font>
					<font color="#555555" size="1"><br><?=_("um NutzerIn zu werden")?></font>
					</a>
					&nbsp;
					</td>
				</tr>
			</table>
			<?}?>
			<?if($free_access_activated){?>
			<table style="margin-left:70px; margin-top:10px;" cellspacing="2" cellpadding="0" border="0">
				<tr>
					<td width="270">
					<a class="index" href="freie.php">
					<font size="4"><b><?=_("Freier Zugang")?></b></font>
					<font color="#555555" size="1"><br><?=_("ohne Registrierung")?></font>
					</a>
					&nbsp;
					</td>
				</tr>
			</table>
			<?}?>
			<table style="margin-left:70px; margin-top:10px;" cellspacing="2" cellpadding="0" border="0">
				<tr>
					<td width="270">
					<a class="index" href="<?=$help_url?>" target="_blank">
					<font size="4"><b><?=_("Hilfe")?></b></font>
					<font color="#555555" size="1"><br><?=_("zu Bedienung und Funktionsumfang")?></font>
					</a>
					&nbsp;
					</td>
				</tr>
			</table>
			<br>
		</td>
	</tr>
	<?if($GLOBALS['UNI_LOGIN_ADD']){?>
	<tr>
		<td colspan="3" bgcolor="#FFFFFF">
			<blockquote>
			<font size="-1">
			&nbsp;<br>
			<?=$GLOBALS['UNI_LOGIN_ADD']?>
			</font>
			</blockquote>
		</td>
	</tr>
	<?}?>
	<tr>
		<td class="blank" align="left" valign="middle">
		<img src="<?=$GLOBALS['ASSETS_URL']?>images/blank.gif" height="45" width="48" border="0">
		</td>
		<td class="blank" valign="middle" align="left">
			<a href="http://www.studip.de">
			<img src="<?=$GLOBALS['ASSETS_URL']?>images/logoklein.gif" border="0"  <?=tooltip(_("Zur Portalseite"))?> >
			</a>
		</td>
		<td class="blank" align="right" nowrap valign="middle">
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td class="steel1">
					<font size="2" color="#555555">&nbsp; <?=_("Aktive Veranstaltungen")?>:</font>
					</td>
					<td class="steel1" align="right">
					<font size="2" color="#555555">&nbsp; <?=$num_active_courses?>&nbsp;</font>
					</td>
					<td class="blank">&nbsp; &nbsp; </td>
				</tr>
				<tr>
					<td class="steel1">
					<font size="2" color="#555555">&nbsp; <?=_("Registrierte NutzerInnen")?>:</font>
					</td>
					<td class="steel1" align="right">
					<font size="2" color="#555555">&nbsp; <?=$num_registered_users?>&nbsp; </font>
					</td>
					<td class="blank">&nbsp; &nbsp; </td>
				</tr>
				<tr>
					<td class="steel1">
					<font size="2" color="#555555">&nbsp; <?=_("Davon online")?>:</font>
					</td>
					<td class="steel1" align="right">
					<font size="2" color="#555555">&nbsp; <?=$num_online_users?>&nbsp; </font>
					</td>
					<td class="blank">&nbsp; &nbsp; </td>
				</tr>
				<tr>
					<td height="30" class="blank" valign="middle">
					<?foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $temp_language_key => $temp_language) {?>
						&nbsp;
						<a href="index.php?set_language=<?=$temp_language_key?>">
						<img src="<?=$GLOBALS['ASSETS_URL']?>images/languages/<?=$temp_language['picture']?>" border="0" <?=tooltip($temp_language['name'])?>>
						</a>
					<?}?>
					</td>
					<td align="right" valign="top" class="blank">
					<a href="impressum.php?view=statistik">
					<font size="2" color="#888888"><?=_("mehr")?>...</font>
					</a>
					</td>
					<td class="blank">
					&nbsp; &nbsp;
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="3" align="center" height="30">
		&nbsp;
		</td>
	</tr>
</table>
