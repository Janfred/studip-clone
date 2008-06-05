<?

if (!$indent) $indent = 0;
$pos = 1;
if (!isset($all_roles)) $all_roles = $roles;
if (is_array($roles)) foreach ($roles as $id => $role) :
?>
<tr>

	<? for($i = 0; $i < $indent - 1; $i++) : ?>
	<td class="blank" width="10" align="right" nowrap><?= ($followers[$i+1]) ? Assets::img('forumstrich') : '' ?></td>
	<? endfor; ?>

	<? if ($indent > 0) : ?>
	<td class="blank" width="10" align="right" nowrap><? 
		if (sizeof($roles) == $pos) :
			echo Assets::img('forumstrich2');
		elseif ($pos < sizeof($roles)) :
			echo Assets::img('forumstrich3');
		endif;
	?></td>
	<? endif; ?>

	<td class="printhead" valign="bottom" colspan="<?= 19-$indent ?>" height="22" nowrap style="padding-left: 3px" width="99%">
		<a name="<?= $id ?>">
		<a class="tree" href="<?= $PHP_SELF ?>?switch=<?= $id ?>&range_id=<?= $range_id ?>&rand=<?= md5(uniqid(rand())) ?>#<?= $id ?>">
			<? if ($open == $id) :
				echo Assets::img('forumgraurunt');	
			else :
				echo '&nbsp;' . Assets::img('forumgrau');	
			endif; ?>
		</a>
			
		<? if ($move) : ?>
		<a href="#"><?= Assets::img('move') ?></a>
		<? endif; ?>
		
		<? if ($sort) : 
			if ($pos > 1) : ?>
		<a href="<?= $GLOBALS['PHP_SELF'] ?>?cmd=moveUp&view=sort&role_id=<?= $id ?>&range_id=<?= $range_id ?>"><?= Assets::img('move_up'); ?></a>
		<? endif; if ($pos < sizeof($roles)) : ?>
		<a href="<?= $GLOBALS['PHP_SELF'] ?>?cmd=moveDown&view=sort&role_id=<?= $id ?>&range_id=<?= $range_id ?>"><?= Assets::img('move_down'); ?></a>
		<? endif;
		endif;
		?>
		
		<a class="tree" href="<?= $PHP_SELF ?>?switch=<?= $id ?>&range_id=<?= $range_id ?>&rand=<?= md5(uniqid(rand())) ?>#<?= $id ?>">			
			<?= $role['role']->getName() ?>
		</a>

	</td>
	<td width="1%" class="printhead" align="right" valign="bottom" nowrap>
		<? if ($role['role']->hasFolder()) :
			echo Assets::img('icon-disc');
		endif; ?>

		&nbsp;
	</td>
</tr>
<?

	// if the current $role has followers, we need to display a straight line later
	$new_followers = $followers;
	$new_followers[$indent] = (sizeof($roles) > $pos);

	// if we have opened an entry, we show edit fields
	if ($open == $id && $range_type != 'sem') :
		echo $this->render_partial('statusgruppen/role_administration.php', 
			array('indent' => $indent, 'followers' => $new_followers,
				'persons' => getPersonsForRole($id), 'role_id' => $id, 'editRole' => ($editRole == $id), 'role' => $role['role'],
				'role_size' => sizeof($roles), 'role_pos' => $pos, 'has_child' => ($role['child']) ? true : false, 'all_roles' => $all_roles)
		);	

	// we are in a seminar and use a different template to display the role-adminstartion
	else:
		echo $this->render_partial('statusgruppen/role_administration_sem.php', 
			array('indent' => $indent, 'followers' => $new_followers,
				'persons' => getPersonsForRole($id), 'role_id' => $id, 'editRole' => ($editRole == $id), 'role' => $role['role'],
				'role_size' => sizeof($roles), 'role_pos' => $pos, 'has_child' => ($role['child']) ? true : false, 'all_roles' => $all_roles)
		);	
	
	endif;

	// if we have childs, we display them with the same template and some indention
	if($role['child']) {
		echo $this->render_partial('statusgruppen/roles.php', 
			array('indent' => $indent + 1, 'roles' => $role['child'], 'followers' => $new_followers, 'all_roles' => $all_roles));
	}

	$pos++;
endforeach;
