<?
# Lifter002: TODO
/**
* Presentation of a set of learning-modules.
*
* This file contains several functions to show a set of learning-modules.
*
* @author		Arne Schroeder <schroeder@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup		elearning_modules
* @module		lernmodul_view_functions
* @package		ELearning
*/

/**
* Shows array of learning-modules
*
* Shows a set of learning-modules specified by $mod_array using the printhead/printcontent functions of Stud.IP.
*
* @access	public
* @param		array		$mod_array	Array of learning-module-data
* @return		boolean	returns false if array is empty
*/
function show_these_modules($mod_array)
{
	global $PHP_SELF, $print_open_search, $SessSemName, $search_key;
 	if ($mod_array == false)
 	{
		echo "<br><b>" . _("Es wurden keine Lernmodule zu diesem Suchbegriff gefunden.") . "</b><br /><br /><br />";
 		return false;
 	}
 	else
 	{
		if (sizeof($mod_array)<2)
			echo "<br><b>" . _("Es wurde ein Lernmodul gefunden:") . "</b><br /><br /><br />";
		else
			echo "<br><b>" . sprintf(_("%s Lernmodule wurden gefunden:"), sizeof($mod_array)) . "</b><br /><br /><br />";

		for ($i=0; $i<sizeof($mod_array); $i++)
		{
			$out_str = get_module_linkdata($mod_array[$i]);

			if ($print_open_search[$out_str["key"]] == true)
				$do_str = "do_close";
			else
				$do_str = "do_open";
			$printlink = "<a href=\"".$PHP_SELF . "?$do_str=" . $out_str["key"] . "&view=show&search_key=$search_key\" class=\"tree\">" . $out_str["link"] . "</a>";
			$printimage = $out_str["image"];
			$printcontent = $out_str["content"] . $out_str["button"];
			$printdesc = $out_str["desc"];

				?>
				<table cellspacing="0" cellpadding="0" border="0" width="100%">
					<tr>
						<?
						if ($print_open_search[$out_str["key"]] == true)
							printhead ("99%", FALSE, $PHP_SELF . "?do_close=" . $out_str["key"] . "&view=show&search_key=$search_key", "open", true, $printimage, $printlink, $printdesc);
						else
							printhead ("99%", FALSE, $PHP_SELF . "?do_open=" . $out_str["key"] . "&view=show&search_key=$search_key", "close", true, $printimage, $printlink, $printdesc);
						?>
					</tr>
				</table>
				<? if ($print_open_search[$out_str["key"]] == true)
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
	return true;
}

/**
* Shows array of learning-modules that belong to the given username
*
* Shows a set of learning-modules that belong to the given username using the printhead/printcontent functions of Stud.IP.
*
* @access	public
* @param		string	$benutzername	Username
*/
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
			$module_info = get_module_info($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$edit_link = link_edit_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
//			$delete_link = link_delete_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$delete_link = $PHP_SELF . "?delete=now&del_inst=".$mod_array[$module_count]["inst"]."&del_id=".$mod_array[$module_count]["id"]."&del_title=".$module_info["title"];

			$ph_key = $mod_array[$module_count]["id"] . "@" . $mod_array[$module_count]["inst"] . "@" . "user";
			if ($print_open_admin[$ph_key] == true)
				$do_str = "do_close";
			else
				$do_str = "do_open";
			$printlink = "<a href=\"".$PHP_SELF . "?$do_str=" . $ph_key . "&view=edit&seminar_id=$seminar_id\" class=\"tree\">" . $module_info["title"] . "</a>";
			$printimage = "<img src=\"".$GLOBALS['ASSETS_URL']."images/icon-lern.gif\">";
			$printcontent = $module_info["description"] . "<br><br><center><a href=\"$edit_link\" target=\"_blank\">" . makeButton("bearbeiten", "img") . "</a>&nbsp;<a href=\"$delete_link\" target=\"_blank\">" . makeButton("loeschen", "img") . "</a></center>";
			$mod_author = get_module_author($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$mod_desc = "";
			for ($i=0; $i<sizeof($mod_author); $i ++)
				if (get_studip_user($mod_author[$i]["id"]) == false)
					$mod_desc[$i] = $mod_author[$i]["fullname"];
				else
					$mod_desc[$i] = "<a href=\"about.php?username=" . get_studip_user($mod_author[$i]["id"]). "\">" . $mod_author[$i]["fullname"] . "</a>";
			$printdesc = implode($mod_desc, ", ");
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
		echo "<b>" . _("Sie haben bisher keine ILIAS-Lernmodule angelegt.") . "</b><br><br>";
}

/**
* Shows array of learning-modules with administration buttons
*
* Shows all learning-modules for the administraion screen using the printhead/printcontent functions of Stud.IP.
* Depending on if the user has acces to the shown modules there are also administration options to the learning-modules.
*
* @access	public
*/
function show_admin_modules()
{
	global $print_open_admin;
	$module_count = 0;
	$mod_array = get_all_modules();
	if ($mod_array != false)
	{
		echo "<b>" . _("Sie haben Zugriff auf folgende Lernmodule:") . "</b><br><br>";
		while ($module_count < sizeof($mod_array))
		{
			$module_info = get_module_info($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$edit_link = link_edit_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
//			$delete_link = link_delete_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$delete_link = $PHP_SELF . "?delete=now&del_inst=".$mod_array[$module_count]["inst"]."&del_id=".$mod_array[$module_count]["id"]."&del_title=".$module_info["title"];

			$ph_key = $mod_array[$module_count]["id"] . "@" . $mod_array[$module_count]["inst"] . "@" . "admin";
			if ($print_open_admin[$ph_key] == true)
				$do_str = "do_close";
			else
				$do_str = "do_open";
			$printlink = "<a href=\"".$PHP_SELF . "?$do_str=" . $ph_key . "&view=edit&seminar_id=$seminar_id\" class=\"tree\">" . $module_info["title"] . "</a>";
			$printimage = "<img src=\"".$GLOBALS['ASSETS_URL']."images/icon-lern.gif\">";
			$printcontent = $module_info["description"] . "<br><br><center><a href=\"$edit_link\" target=\"_blank\">" . makeButton("bearbeiten", "img") . "</a>&nbsp;".
				"<a href=\"$delete_link\" target=\"_blank\">" . makeButton("loeschen", "img") . "</a></center>";
			$mod_author = get_module_author($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$mod_desc = "";
			for ($i=0; $i<sizeof($mod_author); $i ++)
				if (get_studip_user($mod_author[$i]["id"]) == false)
					$mod_desc[$i] = $mod_author[$i]["fullname"];
				else
					$mod_desc[$i] = "<a href=\"about.php?username=" . get_studip_user($mod_author[$i]["id"]). "\">" . $mod_author[$i]["fullname"] . "</a>";
			$printdesc = implode($mod_desc, ", ");
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

/**
* Shows learning-modules that belong to a lecture / institute
*
* Shows a set of learning-modules belonging to $seminar_id using the printhead/printcontent functions of Stud.IP.
* $status defines if the modules are all to be shown (status=0) or only those modules that are rated official (1) or those rated inofficial (2).
*
* @access	public
* @param		string	$seminar_id	id of the lecture / institute
* @param		integer	$status	all learning-modules or officials or inofficials?
* @return		boolean	returns false if array is empty
*/
function show_seminar_modules($seminar_id, $status = 0)
{
	global $PHP_SELF, $print_open, $SessSemName, $perm;

	$module_count = 0;
	$mod_array = get_seminar_modules($seminar_id);
	if ($mod_array != false)
	{
		if ($status == 1)
		{
			if ($SessSemName["class"]=="inst")
				$msg = _("Offizielle Lernmodule dieser Einrichtung:");
			else
				$msg = _("Offizielle Lernmodule dieser Veranstaltung:");
		}
		elseif ($status == 2)
		{
			if ($SessSemName["class"]=="inst")
				$msg = _("Inoffizielle Lernmodule zu dieser Einrichtung:");
			else
				$msg = _("Inoffizielle Lernmodule zu dieser Veranstaltung:");
		}

		while ($module_count < sizeof($mod_array))
		{
			if (($mod_array[$module_count]["status"] == $status) OR ($status == 0))
			{
				if (!isset($module_info))
					echo "<b>" . $msg . "</b><br><br>";

				$link_del = $PHP_SELF . "?view=edit&seminar_id=" . $seminar_id . "&do_op=clear&op_co_inst=" . $mod_array[$module_count]["inst"] . "&op_co_id=". $mod_array[$module_count]["id"] . "&op_status=" . $mod_array[$module_count]["status"];
				if ($mod_array[$module_count]["status"] == 1)
					$op_status = 2;
				elseif ($mod_array[$module_count]["status"] == 2)
					$op_status = 1;
				if ($perm->have_studip_perm("tutor", $seminar_id))
					$link_change = $PHP_SELF . "?view=edit&seminar_id=" . $seminar_id . "&do_op=change&op_co_inst=" . $mod_array[$module_count]["inst"] . "&op_co_id=". $mod_array[$module_count]["id"] . "&op_status=". $op_status;

				$module_info = get_module_info($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
				$ph_key = $mod_array[$module_count]["id"] . "@" . $mod_array[$module_count]["inst"] . "@" . "sem";
				if ($print_open[$ph_key] == true)
					$do_str = "do_close";
				else
					$do_str = "do_open";
				$printlink = "<a href=\"".$PHP_SELF . "?$do_str=" . $ph_key . "&view=edit&seminar_id=$seminar_id\" class=\"tree\">" . $module_info["title"] . "</a>";
				$printimage = "<img src=\"".$GLOBALS['ASSETS_URL']."images/icon-lern.gif\">";
				$printcontent = $module_info["description"] . "<br><br><center><a href=\"$link_change\">" . makeButton("verschieben", "img") . "</a>&nbsp;<a href=\"$link_del\">" . makeButton("entfernen", "img") . "</a></center>";
				$mod_author = get_module_author($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
				$mod_desc = "";
				for ($i=0; $i<sizeof($mod_author); $i ++)
					if (get_studip_user($mod_author[$i]["id"]) == false)
						$mod_desc[$i] = $mod_author[$i]["fullname"];
					else
						$mod_desc[$i] = "<a href=\"about.php?username=" . get_studip_user($mod_author[$i]["id"]). "\">" . $mod_author[$i]["fullname"] . "</a>";
				$printdesc = implode($mod_desc, ", ");
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
			}
			$module_count ++;
		}
	}

	if (!isset($module_info))
		return false;

	echo "<br>";
	return true;
}

/**
* Shows all learning-modules
*
* Shows all learning modules. This function is used to connect a specific learning-module to a lecture.
*
* @access	public
* @param		string	$seminar_id	id of the lecture
*/
function show_all_modules($seminar_id)
{
	global $PHP_SELF, $print_open, $SessSemName, $perm;

	$module_count = 0;
	$hide_mod = get_seminar_modules($seminar_id);
	$mod_array = get_all_modules($hide_mod);
	if ($mod_array != false)
	{
		echo "<b>" . _("Folgende Lernmodule können eingebunden werden:") . "</b><br><br>";

		while ($module_count < sizeof($mod_array))
		{
			$op_status = 2;
			if ($perm->have_studip_perm("tutor", $seminar_id))
				$op_status = 1;
			$link_con = $PHP_SELF . "?view=edit&seminar_id=" . $seminar_id . "&do_op=connect&op_co_inst=" . $mod_array[$module_count]["inst"] . "&op_co_id=". $mod_array[$module_count]["id"] . "&op_status=". $op_status;

			$module_info = get_module_info($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$ph_key = $mod_array[$module_count]["id"] . "@" . $mod_array[$module_count]["inst"] . "@" . "all";
			if ($print_open[$ph_key] == true)
				$do_str = "do_close";
			else
				$do_str = "do_open";
			$printlink = "<a href=\"".$PHP_SELF . "?$do_str=" . $ph_key . "&view=edit&seminar_id=$seminar_id\" class=\"tree\">" . $module_info["title"] . "</a>";
			$printimage = "<img src=\"".$GLOBALS['ASSETS_URL']."images/icon-lern.gif\">";
			$printcontent = $module_info["description"] . "<br><br><center><a href=\"$link_con\">" . makeButton("hinzufuegen", "img") . "</a></center>";
			$mod_author = get_module_author($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$mod_desc = "";
			for ($i=0; $i<sizeof($mod_author); $i ++)
				if (get_studip_user($mod_author[$i]["id"]) == false)
					$mod_desc[$i] = $mod_author[$i]["fullname"];
				else
					$mod_desc[$i] = "<a href=\"about.php?username=" . get_studip_user($mod_author[$i]["id"]). "\">" . $mod_author[$i]["fullname"] . "</a>";
			$printdesc = implode($mod_desc, ", ");
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

/**
* Shows linked learning-modules that belong to a lecture / institute
*
* Shows a set of learning-modules belonging to $seminar_id using the printhead/printcontent functions of Stud.IP.
*
* @access	public
* @param		string	$seminar_id	id of the lecture / institute
* @return		boolean	returns false if no learning-modules were found
*/
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
			echo "<br><b>" . $msg . "</b><br /><br />";
		}

		for ($status=1; $status<=2; $status++)
		{
			unset($printlink);
			if ($status == 1)
				$msg = _("Offizielle Lernmodule:");
			else
				$msg = _("Inoffizielle Lernmodule:");
			for ($i=0; $i<sizeof($out_str); $i++)
				if ($out_str[$i]["status"] == $status)
				{
					if (!isset($printlink))
						echo "<br><b>" . $msg . "</b><br /><br />";

					if ($print_open[$out_str[$i]["key"]] == true)
						$do_str = "do_close";
					else
						$do_str = "do_open";
					$printlink = "<a href=\"".$PHP_SELF . "?$do_str=" . $out_str[$i]["key"] . "&view=show&seminar_id=$seminar_id\" class=\"tree\">" . $out_str[$i]["link"] . "</a>";
					$printimage = $out_str[$i]["image"];
					$printcontent = $out_str[$i]["content"] . $out_str[$i]["button"];
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
}
?>