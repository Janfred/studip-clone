<?
$cssSw = new CSSClassSwitcher();
$style = "style=\"background-image: url('". Assets::image_path('forumstrich') ."');"
	." background-position: right;"
	." background-repeat: repeat-y;"
	."\" ";
?>
<tr>
	<td <?= ($followers) ? $style: ''?> width="1%">&nbsp;</td>
	<td width="99%" class="printcontent">
		<center>
		<br/>
		<a href="<?= $GLOBALS['PHP_SELF'] ?>?view=Karriere&username=<?= $username ?>&cmd=removeFromGroup&role_id=<?= $role_id ?>&studipticket=<?= get_ticket() ?>">
			<?= makebutton('loeschen') ?>
		</a>
		<? if ($GLOBALS['perm']->have_studip_perm('admin', $inst_id)) : ?>
			&nbsp;&nbsp;&nbsp;
			<a href="admin_statusgruppe.php?admin_inst_id=<?=$inst_id?>&open=<?=$role_id?>#<?= $role_id ?>">
			 <?= makebutton('zurfunktion'); ?>
			</a>			
		<? endif; ?>
		<br/><br/>
		<table cellspacing="0" cellpadding="0" border="0" class="blank" width="90%">
		<form action="<?=$PHP_SELF?>#<?= $role_id ?>" method="POST">
			<input type="hidden" name="cmd" value="special_edit">
			<input type="hidden" name="role_id" value="<?= $role_id ?>">
			<input type="hidden" name="studipticket" value="<?=get_ticket()?>">
			<input type="hidden" name="username" value="<?=$username?>">
			<input type="hidden" name="view" value=<?=$view?>>		
	<?
		
		// Rollendaten anzeigen
		//if ($sgroup = GetSingleStatusgruppe($role_id, $userID)) {			
			//$groupOptions = getOptionsOfStGroups($userID);
			?>
			<tr>
				<td align="left" colspan="4" class="topic">
					&nbsp;<b><?= _("Daten für diese Funktion") ?></b>
				</td>
				<td class="blank">&nbsp;</td>
				<td class="topic">
					<b><?=_("Standarddaten")?></b>
				</td>
			<?
			echo "<input type=\"hidden\" name=\"group_id[]\" value=\"$role_id\">";
			echo "</td></tr>\n";
			$cssSw->resetClass();
			$default_entries = DataFieldEntry::getDataFieldEntries(array($user_id, $inst_id));
			$entries = DataFieldEntry::getDataFieldEntries(array($user_id, $role_id));
			foreach ($entries as $id=>$entry) {
				$cssSw->switchClass();
				?>
				<tr>
					<td class="<?=$cssSw->getClass()?>" align="left"></td>
					<td class="<?=$cssSw->getClass()?>" align="left">
						<?=$entry->getName();?>
					</td>
					<td colspan="1" class="<?=$cssSw->getClass()?>">&nbsp;
				<?
				global $auth;
				if ($entry->structure->editAllowed($auth->auth['perm']) && ($entry->getValue() != 'default_value')) {
					echo $entry->getHTML('datafield_content[]', $entry->structure->getID());
					echo '<input type="HIDDEN" name="datafield_id[]" value="'.$entry->structure->getID().'">';
					echo '<input type="HIDDEN" name="datafield_type[]" value="'.$entry->getType().'">';
					echo '<input type="HIDDEN" name="datafield_sec_range_id[]" value="'.$role_id.'">';
					echo '</td>';

					// Set-Default Checkbox
					echo '<td class="'.$cssSw->getClass().'" align="right">';
					echo '<a href="'.$PHP_SELF.'?cmd=set_default&username='.$username.'&view='.$view.'&subview='.$subview.'&role_id='.$role_id.'&chgdef_entry_id='.$id.'&cor_inst_id='.$inst_id.'&sec_range_id='.$role_id.'&subview_id='.$subview_id.'&studipticket='.get_ticket().'" ';
					echo tooltip(_("Diese Daten von den Standarddaten übernehmen")). '>';
					echo '<img src="'.$GLOBALS['ASSETS_URL'].'/images/off_small_blank_transparent.gif" border="0">';
					echo '</a>';
				} else {
					if ($entry->getValue() == 'default_value') {
						echo $default_entries[$id]->getDisplayValue();
						echo '</td>';

						// UnSet-Default Checkbox
						echo '<td class="'.$cssSw->getClass().'" align="right">';
						if ($entry->structure->editAllowed($auth->auth['perm'])) {
							echo '<a href="'.$PHP_SELF.'?cmd=unset_default&username='.$username.'&view='.$view.'&subview='.$subview.'&role_id='.$role_id.'&chgdef_entry_id='.$id.'&cor_inst_id='.$inst_id.'&sec_range_id='.$role_id.'&subview_id='.$subview_id.'&studipticket='.get_ticket().'" ';
							echo tooltip(_("Diese Daten NICHT von den Standarddaten übernehmen")). '>';
							echo '<img src="'.$GLOBALS['ASSETS_URL'].'/images/on_small_transparent.gif" border="0">';
							echo '</a>';
						}
					} else {
						echo $entry->getDisplayValue();
						echo '<td class="'.$cssSw->getClass().'" align="right">';
					}
				}
				echo '</td>';
				echo '<td class="blank">&nbsp;</td>';
				echo '<td width ="30%" class="'.$cssSw->getClass().'"><font size="-1">'.$default_entries[$id]->getDisplayValue().'</font></td>';
				echo '</tr>';
			}
		//}
			$cssSw->switchClass();
		?>
			<tr>
				<td colspan="4" class="<?= $cssSw->getClass() ?>" align="right">
					<font size="-1">
						<?= _("Standarddaten übernehmen:") ?>
						<a href="<?=$PHP_SELF?>?view=Karriere&username=<?=$username?>&inst_id=<?=$inst_id?>&cmd=makeAllSpecial&role_id=<?=$role_id?>&studipticket=<?=get_ticket()?>">
							<?= _("keine") ?>
						</a>
						&nbsp;/&nbsp;
						<a href="<?=$PHP_SELF?>?view=Karriere&username=<?=$username?>&inst_id=<?=$inst_id?>&cmd=makeAllDefault&role_id=<?=$role_id?>&studipticket=<?=get_ticket()?>">
							<?=_("alle") ?>
						</a>
						</font>
					</td>
					<td class="blank">&nbsp;</td>
					<td class="<?= $cssSw->getClass() ?>" align="center">
						<a href="<?= $PHP_SELF ?>?view=Karriere&open=<?= $inst_id ?>&username=<?= $username ?>#<?= $inst_id ?>">
						<?=_("ändern")?>
						</a>					
					</td>
				</tr>
			</table>
		</form>
		<br/>
		<input type="image" <?=makeButton('speichern', 'src')?> value="<?=_("Änderungen speichern")?>" align="absbottom">
		<br/>
		<br/>
		</center>
	</td>
	<td class="printcontent"></td>
</tr>