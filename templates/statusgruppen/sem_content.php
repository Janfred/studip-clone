<table cellspacing="0" cellpadding="2" border="0" width="100%">
	<tr>
		<td width="50%" valign="top">
			<?= $this->render_partial('statusgruppen/sem_optionbox.php') ?>
		</td>
		<td width="50%" valign="top">
			<!-- edit options -->
			<?= $this->render_partial('statusgruppen/sem_edit_role.php') ?>
		</td>
	</tr>
</table>
<form action="<?= URLHelper::getLink('') ?>" method="post">
<table cellspacing="0" cellpadding="2" border="0" width="100%">
	<tr>
		<td width="50%" valign="top">
			<!-- the persons who can be added to a role -->
			<?= $this->render_partial('statusgruppen/sem_available_users.php') ?>
		</td>
		<td width="50%" valign="top">
			<!-- the roles -->
			<?= $this->render_partial('statusgruppen/sem_roles') ?>
		</td>	
	</tr>
</table>
</form>

