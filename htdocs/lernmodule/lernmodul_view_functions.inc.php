<?

function show_user_modules($benutzername)
{
	global $print_open_admin;
	$module_count = 0;
	$mod_array = get_user_modules($benutzername);
	if ($mod_array != false)
	{	
		echo "<b>" . _("Sie haben Zugriff auf folgende Lernmodule:") . "</b><br><br>";
		while ($module_count < sizeof($mod_array))
		{
			$edit_link = link_edit_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$delete_link = link_delete_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);

			$module_info = get_module_info($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$ph_key = $mod_array[$module_count]["id"] . "@" . $mod_array[$module_count]["inst"] . "@" . "user";
			$printlink = $module_info["title"];
			$printimage = "<img src=\"pictures/icon-lern.gif\">";
			$printcontent = $module_info["description"] . "<br><br><center><a href=\"$edit_link\">" . makeButton("bearbeiten", "img") . "</a>&nbsp;<a href=\"$edit_link\">" . makeButton("loeschen", "img") . "</a></center>";
			$mod_author = get_module_author($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			for ($i=0; $i<sizeof($mod_author); $i ++)
				$mod_author[$i] = $mod_author[$i]["fullname"];
			$printdesc = implode($mod_author, ", ");
			?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					if ($print_open_admin[$ph_key] == true)
						printhead ("99%", FALSE, $PHP_SELF . "?do_close=" . $ph_key . "", "open", true, $printimage, $printlink, $printdesc);
					else
						printhead ("99%", FALSE, $PHP_SELF . "?do_open=" . $ph_key . "", "close", true, $printimage, $printlink, $printdesc);
					?>
				</tr>
			</table>
			<? if ($print_open_admin[$ph_key] == true) 
			{ ?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					printcontent("99%", FALSE, $printcontent, "");
					?>
				</tr>
			</table>
			<? }
			$module_count ++;
		}
	}
	else
		echo "<b>" . _("Sie haben keinen Zugriff auf bestehende ILIAS-Lernmodule.") . "</b><br><br>";
}

function show_admin_modules()
{
	global $print_open_admin;
	$module_count = 0;
	$mod_array = get_all_modules();
	if ($mod_array != false)
	{	
		echo "<b>" . _("Auf folgende Lernmodule haben Sie Zugriff:") . "</b><br><br>";
		while ($module_count < sizeof($mod_array))
		{
			$edit_link = link_edit_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$delete_link = link_delete_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);

			$module_info = get_module_info($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$ph_key = $mod_array[$module_count]["id"] . "@" . $mod_array[$module_count]["inst"] . "@" . "admin";
			$printlink = $module_info["title"];
			$printimage = "<img src=\"pictures/icon-lern.gif\">";
			$printcontent = $module_info["description"] . "<br><br><center><a href=\"$edit_link\">" . makeButton("bearbeiten", "img") . "</a>&nbsp;<a href=\"$edit_link\">" . makeButton("loeschen", "img") . "</a></center>";
			$mod_author = get_module_author($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			for ($i=0; $i<sizeof($mod_author); $i ++)
				$mod_author[$i] = $mod_author[$i]["fullname"];
			$printdesc = implode($mod_author, ", ");
			?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					if ($print_open_admin[$ph_key] == true)
						printhead ("99%", FALSE, $PHP_SELF . "?do_close=" . $ph_key . "", "open", true, $printimage, $printlink, $printdesc);
					else
						printhead ("99%", FALSE, $PHP_SELF . "?do_open=" . $ph_key . "", "close", true, $printimage, $printlink, $printdesc);
					?>
				</tr>
			</table>
			<? if ($print_open_admin[$ph_key] == true) 
			{ ?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					printcontent("99%", FALSE, $printcontent, "");
					?>
				</tr>
			</table>
			<? }
			$module_count ++;
		}
	}
	else
		echo "<b>" . _("Es sind keine Lernmodule vorhanden.") . "</b><br><br>";
}

function show_seminar_modules($seminar_id)
{
	global $PHP_SELF, $print_open, $SessSemName;

	$module_count = 0;
	$mod_array = get_seminar_modules($seminar_id);
	if ($mod_array != false)
	{	
		if ($SessSemName["class"]=="inst") 
			$msg = _("Der Einrichtung sind folgende Lernmodule zugeordnet:");
		else	
			$msg = _("Der Veranstaltung sind folgende Lernmodule zugeordnet:");
		echo "<b>" . $msg . "</b><br><br>";

		while ($module_count < sizeof($mod_array))
		{
			$link_del = $PHP_SELF . "?view=edit&seminar_id=" . $seminar_id . "&do_op=clear&op_co_inst=" . $mod_array[$module_count]["inst"] . "&op_co_id=". $mod_array[$module_count]["id"];

			$module_info = get_module_info($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$ph_key = $mod_array[$module_count]["id"] . "@" . $mod_array[$module_count]["inst"] . "@" . "sem";
			$printlink = $module_info["title"];
			$printimage = "<img src=\"pictures/icon-lern.gif\">";
			$printcontent = $module_info["description"] . "<br><br><center><a href=\"$link_del\">" . makeButton("entfernen", "img") . "</a></center>";
			$mod_author = get_module_author($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			for ($i=0; $i<sizeof($mod_author); $i ++)
				$mod_author[$i] = $mod_author[$i]["fullname"];
			$printdesc = implode($mod_author, ", ");
			?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					if ($print_open[$ph_key] == true)
						printhead ("99%", FALSE, $PHP_SELF . "?do_close=" . $ph_key . "&view=edit&seminar_id=$seminar_id", "open", true, $printimage, $printlink, $printdesc);
					else
						printhead ("99%", FALSE, $PHP_SELF . "?do_open=" . $ph_key . "&view=edit&seminar_id=$seminar_id", "close", true, $printimage, $printlink, $printdesc);
					?>
				</tr>
			</table>
			<? if ($print_open[$ph_key] == true) 
			{ ?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					printcontent("99%", FALSE, $printcontent, "");
					?>
				</tr>
			</table>
			<? }
			$module_count ++;
		}
		echo "<br>";
	}
	else
	{
		if ($SessSemName["class"]=="inst") 
			$msg = _("Mit dieser Einrichtung sind keine ILIAS-Lernmodule verknüpft.");
		else	
			$msg = _("Mit dieser Veranstaltung sind keine ILIAS-Lernmodule verknüpft.");
		echo "<b>" . $msg . "</b><br><br>";
	}
}
	
function show_all_modules($seminar_id)
{
	global $PHP_SELF, $print_open, $SessSemName;

	$module_count = 0;
	$hide_mod = get_seminar_modules($seminar_id);
	$mod_array = get_all_modules($hide_mod);
	if ($mod_array != false)
	{	
		echo "<b>" . _("Folgende Lernmodule können eingebunden werden:") . "</b><br><br>";

		while ($module_count < sizeof($mod_array))
		{
			$link_con = $PHP_SELF . "?view=edit&seminar_id=" . $seminar_id . "&do_op=connect&op_co_inst=" . $mod_array[$module_count]["inst"] . "&op_co_id=". $mod_array[$module_count]["id"];

			$module_info = get_module_info($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$ph_key = $mod_array[$module_count]["id"] . "@" . $mod_array[$module_count]["inst"] . "@" . "all";
			$printlink = $module_info["title"];
			$printimage = "<img src=\"pictures/icon-lern.gif\">";
			$printcontent = $module_info["description"] . "<br><br><center><a href=\"$link_con\">" . makeButton("hinzufuegen", "img") . "</a></center>";
			$mod_author = get_module_author($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			for ($i=0; $i<sizeof($mod_author); $i ++)
				$mod_author[$i] = $mod_author[$i]["fullname"];
			$printdesc = implode($mod_author, ", ");
			?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					if ($print_open[$ph_key] == true)
						printhead ("99%", FALSE, $PHP_SELF . "?do_close=" . $ph_key . "&view=edit&seminar_id=$seminar_id", "open", true, $printimage, $printlink, $printdesc);
					else
						printhead ("99%", FALSE, $PHP_SELF . "?do_open=" . $ph_key . "&view=edit&seminar_id=$seminar_id", "close", true, $printimage, $printlink, $printdesc);
					?>
				</tr>
			</table>
			<? if ($print_open[$ph_key] == true) 
			{ ?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					printcontent("99%", FALSE, $printcontent, "");
					?>
				</tr>
			</table>
			<? }
			$module_count ++;
		}
	}
	elseif ($hide_mod != "")
	{	
		if ($SessSemName["class"]=="inst") 
			$msg = _("Alle verf&uuml;gbaren Lernmodule sind der Einrichtung zugeordnet.");
		else	
			$msg = _("Alle verf&uuml;gbaren Lernmodule sind der Veranstaltung zugeordnet.");
		echo "<b>" . $msg . "</b><br><br>";
	}
	else
		echo "<b>" . _("Es sind keine Lernmodule vorhanden.") . "</b><br><br>";
}
	
function show_seminar_modules_links($seminar_id)
{
	global $PHP_SELF, $print_open, $SessSemName;
	$out_str = link_seminar_modules($seminar_id);
 	if ($out_str == false)
 	{
 		return false;
 	}
 	else
 	{
		if (sizeof($out_str)<2)
		{
			if ($SessSemName["class"]=="inst") 
				$msg = _("Diese Einrichtung ist mit einem Lernmodul verbunden:");
			else	
				$msg = _("Diese Veranstaltung ist mit einem Lernmodul verbunden:");
			echo "<br><b>" . $msg . "</b><br /><br /><br />";
		}
		else
		{	
			if ($SessSemName["class"]=="inst") 
				$msg = _("Diese Einrichtung ist mit den folgenden Lernmodulen verbunden:");
			else	
				$msg = _("Diese Veranstaltung ist mit den folgenden Lernmodulen verbunden:");
			echo "<br><b>" . $msg . "</b><br /><br /><br />";
		}
		
			for ($i=0; $i<sizeof($out_str); $i++) 
			{
				$printlink = $out_str[$i]["link"];
				$printimage = $out_str[$i]["image"];
				$printcontent = $out_str[$i]["content"] . "<br>";
				$printdesc = $out_str[$i]["desc"];
				?>
				<table cellspacing="0" cellpadding="0" border="0" width="100%">
					<tr>
						<?
						if ($print_open[$out_str[$i]["key"]] == true)
							printhead ("99%", FALSE, $PHP_SELF . "?do_close=" . $out_str[$i]["key"] . "&view=show&seminar_id=$seminar_id", "open", true, $printimage, $printlink, $printdesc);
						else
							printhead ("99%", FALSE, $PHP_SELF . "?do_open=" . $out_str[$i]["key"] . "&view=show&seminar_id=$seminar_id", "close", true, $printimage, $printlink, $printdesc);
						?>
					</tr>
				</table>
				<? if ($print_open[$out_str[$i]["key"]] == true) 
				{ ?>
				<table cellspacing="0" cellpadding="0" border="0" width="100%">
					<tr>
						<?
						printcontent("99%", FALSE, $printcontent, "");
						?>
					</tr>
				</table>
				<? }
			}
	}
}
?>