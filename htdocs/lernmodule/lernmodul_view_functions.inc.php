<?

function show_user_modules($benutzername)
{
	global $cssSw;
	$module_count = 0;
	$mod_array = get_user_modules($benutzername);
	if ($mod_array != false)
	{	
		echo "<b>" . _("Auf folgende Lernmodule haben Sie Zugriff:") . "</b><br><br>";
		?> 
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr align="center" valign="top">
				<th width="80%" align="left"><b><? echo _("Name"); ?></b></th>
				<th width="15%"><b><? echo _("Bearbeiten"); ?></b></th>
				<th width="5%"><b>X</b></th>
			</tr>		
		<?
		while ($module_count < sizeof($mod_array))
		{
			$edit_link = link_edit_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$delete_link = link_delete_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$cssSw->switchClass();
			?><tr><td class="<? echo $cssSw->getClass(); ?>"><? echo "<b>" . $mod_array[$module_count]["title"] . "</b> - " . $mod_array[$module_count]["description"]; ?>
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $edit_link;?>"><img src='pictures/icon-posting.gif' border=0  alt="<? echo _("Bearbeiten") ?>" title="<? echo _("Bearbeiten") ?>" ></a>&nbsp; 
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $delete_link;?>"><img src='pictures/trash.gif' border=0 alt="<? echo _("Löschen") ?>" title="<? echo _("Löschen") ?>"></a>
			</td></tr><?
			$module_count ++;
		}
		?></table><?
	}
	else
		echo "<b>" . _("Sie haben keinen Zugriff auf bestehende ILIAS-Lernmodule.") . "</b><br><br>";
}

function show_seminar_modules($seminar_id)
{
	global $cssSw;
	$module_count = 0;
	$mod_array = get_seminar_modules($seminar_id);
	if ($mod_array != false)
	{	
		echo "<b>" . _("Folgende Lernmodule sind der Veranstaltung zugeordnet:") . "</b><br><br>";
		?>
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr align="center" valign="top">
				<th width="80%" align="left"><b><? echo _("Name");?></b></th>
				<th width="20%"><b><? echo _("Entfernen");?></b></th>
			</tr>		
		<?
		while ($module_count < sizeof($mod_array))
		{
			$cssSw->switchClass();
			$module_info = get_module_info($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$link_del = $PHP_SELF . "?seminar_id=" . $seminar_id . "&do_op=clear&op_co_inst=" . $mod_array[$module_count]["inst"] . "&op_co_id=". $mod_array[$module_count]["id"];
			echo "<tr><td class=\"" . $cssSw->getClass() . "\"><b>" . $module_info["title"] . "</b> - " . $module_info["description"] . "</td><td align=\"center\" class=\"" . $cssSw->getClass() . "\">" .
			"<a href=\"" . $link_del . "\"><img src='pictures/trash.gif' border=0 alt=\"" .  _("Verknüpfung aufheben") . "\" title=\"" . _("Verknüpfung aufheben") . "\"></a></td></tr>";
			$module_count ++;
		}
		echo "</table><br>";
	}
	else
		echo "<b>" . _("Mit dieser Veranstaltung sind bisher keine ILIAS-Lernmodule verknüpft.") . "</b><br><br>";
}
	
function show_all_modules($seminar_id)
{
	global $cssSw;
	$module_count = 0;
	$mod_array = get_all_modules();
	if ($mod_array != false)
	{	
		echo "<b>" . _("Folgende Lernmodule können eingebunden werden:") . "</b><br><br>";
		?> 
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr align="center" valign="top">
				<th width="80%" align="left"><b><? echo _("Name"); ?></b></th>
				<th width="20%"><b><? echo _("Hinzufügen");?></b></th>
			</tr>		
		<?
		while ($module_count < sizeof($mod_array))
		{
			$cssSw->switchClass();
			$link_con = $PHP_SELF . "?seminar_id=" . $seminar_id . "&do_op=connect&op_co_inst=" . $mod_array[$module_count]["inst"] . "&op_co_id=". $mod_array[$module_count]["id"];
			?><tr><td class="<? echo $cssSw->getClass(); ?>"><? echo "<b>" . $mod_array[$module_count]["title"] . "</b> - " . $mod_array[$module_count]["description"]; ?>
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $link_con;?>"><img src='pictures/icon-posting.gif' border=0  alt="<? echo _("Hinzufügen") ?>" title="<? echo _("Hinzufügen") ?>" ></a>&nbsp; 
			</td></tr><?
			$module_count ++;
		}
	}
	else
		echo "<b>" . _("Es sind keine Lernmodule vorhanden.") . "</b><br><br>";
}
	
function show_all_modules_admin()
{
	global $cssSw;
	$module_count = 0;
	$mod_array = get_all_modules();
	if ($mod_array != false)
	{	
		echo "<b>" . _("Auf folgende Lernmodule haben Sie Zugriff:") . "</b><br><br>";
		?> 
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr align="center" valign="top">
				<th width="80%" align="left"><b><? echo _("Name"); ?></b></th>
				<th width="15%"><b><? echo _("Bearbeiten"); ?></b></th>
				<th width="5%"><b>X</b></th>
			</tr>		
		<?
		while ($module_count < sizeof($mod_array))
		{
			$edit_link = link_edit_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$delete_link = link_delete_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$cssSw->switchClass();
			?><tr><td class="<? echo $cssSw->getClass(); ?>"><? echo "<b>" . $mod_array[$module_count]["title"] . "</b> - " . $mod_array[$module_count]["description"]; ?>
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $edit_link; ?>"><img src='pictures/icon-posting.gif' border=0  alt="<? echo _("Bearbeiten") ?>" title="<? echo _("Bearbeiten") ?>" ></a>&nbsp; 
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $delete_link; ?>"><img src='pictures/trash.gif' border=0 alt="<? echo _("Löschen") ?>" title="<? echo _("Löschen") ?>"></a>
			</td></tr><?
			$module_count ++;
		}
		?></table><?
	}
	else
		echo "<b>" . _("Es sind keine Lernmodule vorhanden.") . "</b><br><br>";
}

function show_seminar_modules_links($seminar_id)
{
	link_seminar_modules($seminar_id);
}
?>