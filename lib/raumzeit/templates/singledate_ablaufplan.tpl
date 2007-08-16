<TR>
	<TD width="1%" align="right" valign="center" class="<?=$tpl['class']?>" nowrap>
		<A name="<?=$tpl['sd_id']?>">
		<A href="<?=$PHP_SELF?>?cmd=<?=($issue_open[$tpl['sd_id']]) ? 'close' : 'open'?>&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
			<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/forumgrau<?=($issue_open[$tpl['sd_id']]) ? 'runt' : ''?>.gif" border="0">
		</A>
	</TD>
	<TD width="1%" align="right" valign="top" class="<?=$tpl['class']?>" nowrap>
		<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/termin-icon.gif" border="0" align="abstop">&nbsp;
	</TD>
	<TD width="30%" nowrap class="<?=$tpl['class']?>" valign="bottom">
		<A class="tree" href="<?=$PHP_SELF?>?cmd=<?=($issue_open[$tpl['sd_id']]) ? 'close' : 'open'?>&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
			<FONT size="-1">
				<?=$tpl['date']?>
			</FONT>
		</A>
	</TD>
	<TD width="23%" nowrap class="<?=$tpl['class']?>" valign="bottom">
		<FONT size="-1" color="#000000">
			<?=$tpl['room']?>
		</FONT>
	</TD>
	<td width="40%" nowrap class="<?=$tpl['class']?>" valign="bottom">
		<font size="-1" color="#000000">
			<?=mila($tpl['theme_title'])?>
		</font>
	</td>
	<td width="5%" class="<?=$tpl['class']?>" nowrap valign="bottom">
		<?=$tpl['calendar']?>
	</td>
</TR>
<? if ($issue_open[$tpl['sd_id']] || $openAll) { ?>
<TR>
	<TD colspan="6" class="steel1" align="center">
		<? if (!$openAll) { ?><FORM action="<?=$PHP_SELF?>" method="post"><? } ?>
		<TABLE border="0" cellspacing="0" cellpadding="1" width="99%">
			<TR>
				<TD class="steel1">
					<FONT size="-1">
						<B><?=("Titel:")?></B><BR/>
						<INPUT type="text" name="theme_title<?=$openAll ? '§'.$tpl['sd_id']: ''?>" maxlength="255" size="50" value="<?=$tpl['theme_title']?>" style="width: 98%"><BR/>
						<B><?=_("Beschreibung:")?></B><BR/>
						<TEXTAREA name="theme_description<?=$openAll ? '§'.$tpl['sd_id']: ''?>" rows="5" cols="50" style="width: 98%"><?=$tpl['theme_description']?></TEXTAREA><BR/>
					</FONT>
				</TD>
				<TD class="steel1" valign="top" nowrap>
					<FONT size="-1">
						<B><?=_("Verknüfpungen mit diesem Termin:")?></B>
						<BR/>
						<? if ($tpl['forumEntry']) {
							echo _("Forenthema vorhanden").'<BR/>';
							echo '<INPUT type="hidden" name="forumFolder" value="on">';
						} else { ?>
							<INPUT type="checkbox" name="forumFolder<?=$openAll ? '§'.$tpl['sd_id']: ''?>"> <?=_("Thema im Forum anlegen")?><BR/>
						<? } ?>
						<? if ($tpl['fileEntry']) {
							echo _("Dateiordner vorhanden");
							echo '<INPUT type="hidden" name="fileFolder" value="on">';
						} else { ?>
							<INPUT type="checkbox" name="fileFolder<?=$openAll ? '§'.$tpl['sd_id']: ''?>"<?=$tpl['fileEntry']?>> <?=_("Dateiordner anlegen")?>
						<? } ?>
						<br/>
						<br/>
						<b><?=_("Art des Termins")?>:</b> <?=$tpl['art']?>
					</FONT>
				</TD>
			</TR>
			<TR>
				<TD class="steel1" align="center" colspan="2">
					<? if (!$openAll) { ?>
					<? if ($tpl['issue_id']) { ?>
					<INPUT type="hidden" name="issue_id" value="<?=$tpl['issue_id']?>">
					<? } ?>
					<INPUT type="hidden" name="singledate_id" value="<?=$tpl['sd_id']?>">
					<INPUT type="image" <?=makebutton('uebernehmen', 'src')?> align="absmiddle" name="<?=$tpl['submit_name']?>">
					<A href="<?=$PHP_SELF?>?cmd=close&open_close_id=<?=$tpl['sd_id']?>">
						<IMG <?=makebutton('abbrechen', 'src')?> border="0" align="absmiddle">
					</A>
					<? } ?>
				</TD>
			</TR>
		</TABLE>
		<? if (!$openAll) { ?></FORM> <? } ?>
	</TD>
</TR>
<?
}
unset($tpl);
