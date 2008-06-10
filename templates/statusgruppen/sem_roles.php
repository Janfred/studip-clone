<?

$roles_pos = 1;
if (!isset($all_roles)) $all_roles = $roles;
if (is_array($roles)) foreach ($roles as $id => $role) :
	$topic_class = 'topic';
	if ($edit_role == $id) $topic_class = 'topicwrite';
?>
<a name="<?= $id ?>" ></a>
<table cellspacing="0" cellpadding="0" border="0">
<tr>
	<td class="blank" width="1%" style="padding: 0px 10px 0px 0px">
		<input type="image" name="role_id" value="<?= $id ?>" src="<?= Assets::image_path('move') ?>" title="<?= _("Markierte Personen dieser Gruppe zuordnen") ?>">
	</td>
	<td class="<?= $topic_class ?>" nowrap style="padding-left: 5px" width="85%">
		<?= htmlReady($role['role']->getName()) ?>
	</td>
	<td width="5%" class="<?= $topic_class ?>" align="right" colspan="3" nowrap>
		<? if ($role['role']->hasFolder()) :
			echo Assets::img('icon-disc', array('title' => _("Dateiordner vorhanden")));
		endif; ?>
		<? if ($role['role']->getSelfAssign()) :
			echo Assets::img('nutzer', array('title' => _("Personen können sich dieser Gruppe selbst zuordnen")));
		endif; ?>
		<a href="<?= URLHelper::getLink('?cmd=sortByName&role_id='.  $id) ?>">
			<?= Assets::img('sort') ?>
		</a>
		<a href="<?= URLHelper::getLink('?cmd=editRole&role_id='.  $id) ?>">
			<?= Assets::img('einst') ?>
		</a>
	&nbsp;
	</td>
	<td width="1%" class="blank" nowrap style="padding-left: 5px">
		<a href="<?= URLHelper::getLink('?cmd=deleteRole&role_id='. $id) ?>">
			<?= Assets::img('trash_att', array('title' => _("Gruppe mit Personenzuordnung entfernen"))) ?>
		</a>
	</td>
</tr>
<?
	$cssSw = new CSSClassSwitcher();
	$pos = 0;
	$style = "style=\"background-image: url('". Assets::image_path('forumstrich') ."');"
		." background-position: right;"
		." background-repeat: repeat-y;"
		."\" ";		
	$persons = getPersonsForRole($id);
?>
<!-- Persons assigned to this role -->
<? if (is_array($persons)) foreach ($persons as $person) :
			$cssSw->switchClass();
			$pos ++;
?>
<tr>
	<td class="blank" width="1%" nowrap>
		<?= $pos ?>
	</td>

	<td class="<?= $cssSw->getClass() ?>">
		<? if ($range_type == 'sem') : ?>
		<a href="<?= URLHelper::getLink('about.php?username='. $person['username'] ) ?>">
		<? else: ?>
		<a href="<?= URLHelper::getLink('edit_about.php?view=Karriere&open='. $id .'&username='. $person['username'] .'#'. $id) ?>">
		<? endif; ?>
			<?= htmlReady($person['fullname']) ?>
		</a>
	</td>

	<td class="<?= $cssSw->getClass() ?>">&nbsp;</td>
	<td class="<?= $cssSw->getClass() ?>" width="1%" nowrap>
		<? if ($pos < sizeof($persons)) : ?>
		<a href="<?= URLHelper::getLink('?cmd=move_down&role_id='. $id .'&username='. $person['username']) ?>">
			<input type="image" src="<?= Assets::image_path('move_down') ?>">
		</a>
		<? endif; ?>
	</td>

	<td class="<?= $cssSw->getClass() ?>" width="1%" nowrap style="padding-left: 4px">
		<? if ($pos > 1) : ?>
		<a href="<?= URLHelper::getLink('?cmd=move_up&role_id='. $id .'&username='. $person['username']) ?>">
			<input type="image" src="<?= Assets::image_path('move_up') ?>">
		</a>
		<? endif; ?>
	</td>

	<td class="blank" width="1%" align="center">
		<a href="<?= URLHelper::getLink('?role_id='. $id .'&cmd=removePerson&username='. $person['username'])  ?>">
		<?= Assets::img('trash.gif') ?>
		</a>
	</td>
</tr>
<? endforeach; ?>

<!-- fill up to group size with empty roles -->
<? for ($i = $pos + 1; $i <= $role['role']->getSize(); $i++) : ?>
<tr>
	<td colspan="6">
		<span style="color:red"><?= $i ?></span>
	</td>
</tr>
<? endfor; ?>

<? if (sizeof($roles) > $roles_pos) : ?>
<tr>
	<td colspan="6" class="blank" align="center">
		<br/>
		<a href="<?= URLHelper::getLink('?cmd=swapRoles&role_id='. $id) ?>">
			<?= Assets::img('move_up') ?>
			<?= Assets::img('move_down') ?>
		</a><br/>
		<br/>
	</td>
</tr>
<? endif; ?>

</table>

<? 
$roles_pos++;
endforeach;
