<?

$pos = 1;
if (!isset($all_roles)) $all_roles = $roles;
if (is_array($roles)) foreach ($roles as $id => $role) :
?>
<a name="<?= $id ?>" ></a>
<table cellspacing="0" cellpadding="0" border="0">
<tr>
	<td class="blank" width="1%" style="padding: 0px 10px 0px 0px">
		<input type="image" name="role_id" value="<?= $id ?>" src="<?= Assets::image_path('move') ?>" title="<?= _("Markierte Personen dieser Gruppe zuordnen") ?>">
	</td>
	<td class="topic" nowrap style="padding-left: 5px" width="85%">
		<?= $role['role']->getName() ?>
	</td>
	<td width="5%" class="topic" align="right" nowrap>
		<? if ($role['role']->hasFolder()) :
			echo Assets::img('icon-disc', array('title' => _("Dateiordner vorhanden")));
		endif; ?>
		<? if ($role['role']->getSelfAssign()) :
			echo Assets::img('nutzer', array('title' => _("Personen können sich dieser Gruppe selbst zuordnen")));
		endif; ?>
		<a href="<?= $GLOBALS['PHP_SELF'] ?>?cmd=sortByName&role_id=<?= $role_id ?>&range_id=<?= $range_id ?>">
			<?= Assets::img('sort') ?>
		</a>
		&nbsp;
	</td>
	<td width="1%" nowrap>
		&nbsp;<?= Assets::img('trash_att', array('title' => _("Gruppe mit Personenzuordnung entfernen"))) ?>
	</td>
</tr>
<?
	$cssSw = new CSSClassSwitcher();
	$pos = 0;
	$style = "style=\"background-image: url('". Assets::image_path('forumstrich') ."');"
		." background-position: right;"
		." background-repeat: repeat-y;"
		."\" ";		
		
	if ($seminar_persons) :
		$width = '33%';
		$indirect = true;
	else :
		$width = '50%';
		$indirect = false;
	endif;
?>
<tr>
	<td class="blank" width="100%" colspan="4">
		<center>
			<form action="<?= $GLOBALS['PHP_SELF'] ?>" method="post" style="display: inline">
				<input type="hidden" name="cmd" value="sort_person">
				<input type="hidden" name="role_id" value="<?= $role_id ?>">
				<table cellspacing="0" cellpadding="0" border="0" width="95%">
					<!-- Persons assigned to this role -->
					<? if (is_array($persons)) foreach ($persons as $person) :
								$cssSw->switchClass();
								$pos ++;
					?>
					<tr>
						<td class="<?= $cssSw->getClass() ?>" width="1%" nowrap>
							<input name="sort_person[]" value="<?= $person['username'] ?>" type="radio">
						</td>

						<td class="<?= $cssSw->getClass() ?>" width="1%" nowrap>
							<input 
								src="<?= Assets::image_path('antwortnew') ?>" 
								name="do_person_sort[<?= $person['username'] ?>]" type="image">
						</td>

						<td class="<?= $cssSw->getClass() ?>" width="1%" nowrap style="padding-left: 6px">
							<? if ($pos < sizeof($persons)) : ?>
							<a href="<?= $GLOBALS['PHP_SELF']?>?cmd=move_down&role_id=<?= $role_id ?>&username=<?= $person['username'] ?>&range_id=<?= $range_id ?>">
								<input type="image" src="<?= Assets::image_path('move_down') ?>">
							</a>
							<? endif; ?>
						</td>

						<td class="<?= $cssSw->getClass() ?>" width="1%" nowrap style="padding-left: 4px">
							<? if ($pos > 1) : ?>
							<a href="<?= $GLOBALS['PHP_SELF'] ?>?cmd=move_up&role_id=<?= $role_id ?>&username=<?= $person['username'] ?>&range_id=<?= $range_id ?>">
								<input type="image" src="<?= Assets::image_path('move_up') ?>">
							</a>
							<? endif; ?>
						</td>

						<td class="<?= $cssSw->getClass() ?>" width="1%" nowrap>
							&nbsp;&nbsp;<?= $pos ?>&nbsp;
						</td>

						<td class="<?= $cssSw->getClass() ?>">
							<? if ($range_type == 'sem') : ?>
							<a href="about.php?username=<?= $person['username'] ?>">
							<? else: ?>
							<a href="edit_about.php?view=Karriere&open=<?= $role_id ?>&username=<?= $person['username'] ?>#<?= $role_id ?>">
							<? endif; ?>
							 	<?= $person['fullname'] ?>
							</a>
						</td>

						<td class="<?= $cssSw->getClass() ?>" width="1%" colspan="2" align="right">
							<a href="<?= $GLOBALS['PHP_SELF'] ?>?role_id=<?= $role_id ?>&cmd=removePerson&username=<?= $person['username'] ?>&range_id=<?= $range_id ?>">
							<?= Assets::img('trash.gif') ?>
							</a>
						</td>
					</tr>
					<? endforeach; ?>
				</table>
			</form>
			<br/>
		</center>
	</td>
</tr>
</table>
<?
endforeach;
