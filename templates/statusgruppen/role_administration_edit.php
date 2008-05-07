<?
	$cssSw = new cssClassSwitcher();
	$num = 0;
	$group_data = $role->getData();
?>
	<tr>
		<td colspan="5" class="printcontent">
			<form action="<?= $GLOBALS['PHP_SELF'] ?>#<?= $role->getId() ?>" method="post">
			<table cellspacing="0" cellpadding="1" border="0" width="100%">
				<tr>
					<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<font size="-1">
							<?= _("Gruppenname") ?>:
						</font>
					</td>
					<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<font size="-1">
							<input type="text" name="new_name" value="<?=htmlReady($group_data['name'])?>">
					</font>
					</td>
				</tr>
				<? $cssSw->switchClass() ?>
				
				<? if ($range_type != 'sem') : ?>
				<tr>
					<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<font size="-1">
							<?= _("Übergeordnete Gruppe") ?>:
						</font>
					</td>
					<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<font size="-1">
							<select name="vather">
								<option value="nochange"> -- <?= _("Keine Änderung") ?> -- </option>
								<option value="root"> -- <?= _("Hauptebene") ?> -- </option> 
						 		<? Statusgruppe::displayOptionsForRoles($all_roles); ?>
							</select>
						</font>
					</td>
				</tr>
				<? $cssSw->switchClass() ?>
				<? endif; ?>
				
				<tr>
					<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<font size="-1">
							<?= _("Gruppengröße") ?>:
							&nbsp;<img style="{cursor:pointer; vertical-align:bottom;}" src="<?=$GLOBALS['ASSETS_URL']?>images/info.gif" <?=tooltip(_("Mit dem Feld 'Gruppengröße' haben Sie die Möglichkeit, die Sollstärke für eine Gruppe festzulegen. Dieser Wert wird nur für die Anzeige benutzt - es können auch mehr Personen eingetragen werden."), TRUE, TRUE)?>>
						</font>
					</td>
					<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<input type="text" name="new_size" value="<?=$group_data['size']?>"><br/>
					</td>
				</tr>
				<? if ($range_type == 'sem') : ?>
				<? $cssSw->switchClass() ?>
				<tr>
					<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<font size="-1">
							<?=_("Selbsteintrag") ?>:
						</font>
					</td>
					<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<input type="checkbox" name="new_selfassign" value="1" <?=$group_data['selfassign']? 'checked="checked"' : ''?>>
						<input type="hidden" name="vather" value="nochange">
					</td>
				</tr>

				<? $cssSw->switchClass() ?>
				<tr>
						<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<font size="-1">
							<?=_("Gruppenordner:") ?>:
						</font>
					</td>
					<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<? if ($group_data['folder']) : ?>
						<input type="checkbox" name="groupfolder" value="1">
						<? else:
							echo _("vorhanden");
						endif; ?>
					</td>
				</tr>
				<? endif; ?>
								
				<? if ($range_type != 'sem' && is_array($group_data['datafields'])) foreach ($group_data['datafields'] as $field) : ?>
				<? $cssSw->switchClass() ?>
				<tr>
					<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<?=$field['invalid']?'<font color="red" size="-1"><b>':'<font size="-1">'?>
						<?=$field['name']?>
						<?=$field['invalid']?'</b></font>':'</font>'?>
					</td>
					<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
						<font size="-1">
							<?=$field['html']?>
						</font>
					</td>
				</tr>
				<input type="HIDDEN" name="datafield_id[]" value="<?= $field['datafield_id'] ?>">
				<input type="HIDDEN" name="datafield_type[]" value="<?= $field['datafield_type'] ?>">
				<input type="HIDDEN" name="datafield_sec_range_id[]" value="<?= $role->getId() ?>">
				<? endforeach; ?>
				<tr>
					<td class="steel1" align="right" colspan="2">
						<br/>
						&nbsp;
						<a href="<?= $GLOBALS['PHP_SELF'] ?>?range_id=<?= $range_id ?>#<?= $role->getId() ?>">
							<?= makebutton('zurueck') ?>
						</a>
						<input type="image" <?= makebutton('speichern', 'src') ?> align="absbottom">
					</td>
				</tr>
			</table>
			<input type="hidden" name="view" value="editRole">
			<input type="hidden" name="cmd" value="editRole">
			<input type="hidden" name="role_id" value="<?= $role->getId() ?>">
			<input type="hidden" name="range_id" value="<?= $range_id ?>">
			</form>
		</td>
	</tr>
