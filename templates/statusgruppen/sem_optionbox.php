<!-- display the old box for special statusgroup-options -->
<form action="<?= URLHelper::getLink('') ?>" method="post">
	<table cellpadding="2" cellspacing="2" border="0" style="border:1px solid;margin:10px">
		<tr>
			<td width="300">
				<font size="-1"><?=_("Selbsteintrag in allen Gruppen eingeschaltet")?>
				</td>
			<td>
				<input type="checkbox" name="toggle_selfassign_all" value="1" <?=($self_assign_all ? 'checked' : '')?>>
			</td>
			<td rowspan="2">
				&nbsp;
				<?=makeButton('uebernehmen2','input',_("Einstellungen zum Selbsteintrag ändern"),'change_self_assign')?>
				&nbsp;
			</td>
		</tr>
		<tr>
			<td width="300"><font size="-1">
				<?= _("Selbsteintrag nur in einer Gruppe erlauben") ?>
			</td>
			<td>
				<input type="checkbox" name="toggle_selfassign_exclusive" value="1" <?=($self_assign_exclusive ? 'checked' : '')?>>
			</td>
		</tr>
	</table>
	<input type="hidden" name="cmd" value="changeOptions">
</form>
