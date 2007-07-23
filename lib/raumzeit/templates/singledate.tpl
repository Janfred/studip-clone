<? if (!$tpl['deleted']) { ?>
<TR>
	<TD width="7%" align="right" valign="top" class="<?=$tpl['class']?>" nowrap height="25">
		<A name="<?=$tpl['sd_id']?>">
		<? if (!$_LOCKED) { ?>
		<A href="<?=$PHP_SELF?>?cmd=<?=($sd_open[$tpl['sd_id']]) ? 'close' : 'open'?>&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
			<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/forumgrau<?=($sd_open[$tpl['sd_id']]) ? 'runt' : ''?>.gif" border="0" align="abstop" <?=tooltip(_("Termin zum Bearbeiten öffnen"))?>>
		</A>
		<INPUT type="checkbox" name="singledate[]" value="<?=$tpl['sd_id']?>" <?=$tpl['checked']?>>
		<? } ?>
	</TD>
	<TD width="43%" nowrap class="<?=$tpl['class']?>">
		<? if (!$_LOCKED) { ?>
		<A class="tree" href="<?=$PHP_SELF?>?cmd=<?=($sd_open[$tpl['sd_id']]) ? 'close' : 'open'?>&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
		<? } ?>
			<FONT size="-1" color="#000000">
				<?=$tpl['date']?>
			</FONT>
		<? if (!$_LOCKED) { ?>
		</A>
		<? } ?>
	</TD>
	<TD width="30%" nowrap class="<?=$tpl['class']?>">
		<FONT size="-1" color="#000000">
			<?=$tpl['room']?>
		</FONT>
		<? if ($tpl['ausruf']) { ?>
			<A href="javascript:;" onClick="alert('<?=$tpl['ausruf']?>')">
				<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/ausrufezeichen_rot.gif" alt="<?=$tpl['ausruf']?>" border="0" align="absmiddle">
			</A>
		<? } ?>
	</TD>
	<TD width="20%" nowrap class="<?=$tpl['class']?>" align="right">
		<? if (!$_LOCKED) { ?>
		<A href="<?=$PHP_SELF?>?cmd=<?=($sd_open[$tpl['sd_id']]) ? 'close' : 'open'?>&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
			<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/edit_transparent.gif" border="0" align="absmiddle" <?=tooltip(_("Termin bearbeiten"))?>>
		</A>
		<A href="<?=$PHP_SELF?>?cmd=delete_singledate&sd_id=<?=$tpl['sd_id']?>&cycle_id=<?=$tpl['cycle_id']?>">
			<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/trash.gif" border="0" align="absmiddle" <?=tooltip(_("Termin löschen"))?>>
		</A>
		<? } ?>
	</TD>
</TR>
<? } else { ?>
<TR>
	<TD width="7%" align="right" valign="top" class="<?=$tpl['class']?>" nowrap>
		<? if (!$_LOCKED) { ?>
			<? if ($GLOBALS['perm']->have_perm('admin')) : ?>
				<A href="<?=$PHP_SELF?>?cmd=<?=($sd_open[$tpl['sd_id']]) ? 'close' : 'open'?>&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
					<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/forumgrau<?=($sd_open[$tpl['sd_id']]) ? 'runt' : ''?>.gif" border="0" align="abstop">
				</A>
			<? else : ?>
				<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/forumgrau2.gif" border="0" align="abstop">
			<? endif; ?>
			<INPUT type="checkbox" name="singledate[]" value="<?=$tpl['sd_id']?>" <?=$tpl['checked']?>>
		<? } ?>
	</TD>
	<TD width="43%" nowrap class="<?=$tpl['class']?>">
		<FONT size="-1" color="#666666">
			<?=$tpl['date']?>
		</FONT>
	</TD>
	<TD width="30%" nowrap class="<?=$tpl['class']?>">
		<? if ($tpl['comment']) : ?>
		<font size="-1">
			<i><?=_("Kommentar")?>: <?=$tpl['comment']?></i>
		</font>
		<? else : ?>
		<font size="-1" color="#666666">
			<?=$tpl['room']?>
		</font>
		<? endif; ?>
	</TD>
	<TD width="20%" nowrap class="<?=$tpl['class']?>" align="right">
		<? if (!$_LOCKED) { ?>
		<A href="<?=$PHP_SELF?>?cmd=undelete_singledate&sd_id=<?=$tpl['sd_id']?>&cycle_id=<?=($tpl['cycle_id']) ? $tpl['cycle_id'] : '' ?>">
			<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/untrash.gif" border="0" align="absmiddle" <?=tooltip(_("Termin wiederherstellen"))?>>
		</A>
		<? } ?>
	</TD>
</TR>
<? }
	unset($tpl)
?>
