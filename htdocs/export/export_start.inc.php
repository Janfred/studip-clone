<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// export_run_xslt.inc.php
// Integration of xslt-processor
// 
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

$perm->check("dozent");

require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_config.inc.php");   // Konfigurationsdatei
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_xslt_vars.inc.php");   // XSLT-Variablen
	
	$db=new DB_Seminar;

$export_pagename = _("Datenexport - Startseite");

	$export_info = _("Bitte wählen Sie Datenart und Einrichtung.") . "<br>";

	$export_pagecontent .= "<form method=\"POST\" action=\"" . $PHP_SELF . "\">";

	$export_pagecontent .= "<br><br>";
	$export_pagecontent .= _("Art der auszugebenden Daten: ") .  "<select name=\"ex_type\">";
	$export_pagecontent .= "<option value=\"veranstaltung\">" . _("Veranstaltungsdaten") .  "";
	$export_pagecontent .= "<option value=\"person\">" . _("MitarbeiterInnendaten") .  "";
//	$export_pagecontent .= "<option value=\"forschung\">" . _("Forschungsberichte") .  "";
	$export_pagecontent .= "</select><br><br><br><br>";
	
	$export_pagecontent .= _("Bitte wählen Sie eine Einrichtung: ") .  "<select name=\"range_id\">";
	
	$db->query("SELECT Institut_id, Name FROM Institute ORDER BY Name");
	while ($db->next_record())
	{
		$export_pagecontent .= "<option value=\"" . $db->f("Institut_id") . "\">" . $db->f("Name");
	}
	$export_pagecontent .= "</select><br><br><br>";
	
	$export_pagecontent .= "<input type=\"hidden\" name=\"o_mode\" value=\"choose\">";

	$export_weiter_button = "<center><input type=\"IMAGE\"" . makeButton("weiter", "src") . " name=\"\"></center></form>";
		$infobox = array	(			
		array ("kategorie"  => _("Information:"),
			"eintrag" => array	(	
							array (	"icon" => "pictures/ausruf_small.gif",
									"text"  => sprintf(_("Dies ist das Stud.IP Exportmodul. Mit diesem Modul können Sie Daten in den Formaten %s und XML ausgeben."), implode($output_formats, ", "))
								 )
							)
			)
		);
		{
			$infobox[1]["kategorie"] = _("Aktionen:");
				$infobox[1]["eintrag"][] = array (	"icon" => "pictures/forumgrau.gif" ,
											"text"  => sprintf(_("Wählen Sie die Art der Daten, die Sie exportieren wollen, und die Einrichtung, aus der die Daten gelesen werden sollen. Klicken Sie dann auf 'weiter.'"), $link2, "</a>")
										);
		}
?>