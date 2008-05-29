<?php
# Lifter002: TODO
/**
* lit_import.inc.php
*
* Routinen zum Importieren von XML-Daten aus EndNote
*
*
* @author               Jan Kulmann <jankul@tzi.de>
* @version              $Id$
*/

// +---------------------------------------------------------------------------+
// This file is NOT part of Stud.IP
// admin_foto_contest.php
// Copyright (C) 2005 Jan Kulmann <jankul@tzi.de>
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

require_once ("lib/classes/lit_import_plugins/StudipLitImportPluginAbstract.class.php");

function do_lit_import() {
	global $_msg, $cmd, $xmlfile, $xmlfile_size, $xmlfile_name, $username, $_range_id, $PHP_SELF, $plugin_name;
	if ($cmd) {
		if ($cmd=="import_lit_list" && $xmlfile) {
			StudipLitImportPluginAbstract::use_lit_import_plugins($xmlfile, $xmlfile_size, $xmlfile_name, $plugin_name, $_range_id);
			//header("Location: $PHP_SELF?_range_id=$_range_id&username=$username&_msg=".urlencode($_msg));
			//wozu dieses???
		}
	}
}

function print_lit_import_dlg() {
	global $PHP_SELF, $username, $_range_id, $plugin_name, $LIT_IMPORT_PLUGINS;

	if (!$plugin_name) $plugin_name = "EndNote";

	$plugin = array();
	
	if ($plugin_name)
		foreach ($LIT_IMPORT_PLUGINS as $p) {
			if ($p["name"] == $plugin_name) {
				$plugin = $p;
				break;
			}
		}
			

	echo "<blockquote>\n";
        echo "<form enctype=\"multipart/form-data\" action=\"$PHP_SELF?_range_id=$_range_id&username=$username\" method=\"POST\">\n";
	echo "  <INPUT TYPE=\"hidden\" NAME=\"cmd\" VALUE=\"import_lit_list\">\n";
	echo "  <TABLE BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"0\">\n";
	echo "    <TR>\n";
	echo "      <TD COLSPAN=\"2\"><FONT SIZE=\"\"><B>"._("Literaturlisten importieren:")."</B></FONT></TD>\n";
	echo "    </TR>\n";
	echo "    <TR><TD COLSPAN=\"2\">&nbsp;</TD></TR>\n";

	echo "    <TR>\n";
	echo "      <TD COLSPAN=\"2\"><FONT SIZE=\"\">"._("Bitte w&auml;hlen Sie eine Literaturverwaltung aus:");
	echo "        <SELECT NAME=\"plugin_name\" SIZE=\"1\" onChange=\"this.form.cmd='';this.form.submit();\">\n";
	foreach ($LIT_IMPORT_PLUGINS as $p) {
		echo "          <OPTION VALUE=\"".$p["name"]."\" ".($p["name"]==$plugin_name ? "SELECTED" : "").">".$p["visual_name"]."\n";
	}
	echo "        </SELECT>\n";
	echo "      </FONT></TD>\n";
	echo "    </TR>\n";

	if ($plugin_name) {
		echo "    <TR><TD COLSPAN=\"2\">&nbsp;</TD></TR>\n";
		echo "    <TR>\n";
		echo "      <TD COLSPAN=\"2\"><FONT SIZE=\"\">".(strlen($plugin["description"])>0 ? "<IMG SRC=\"".$GLOBALS['ASSETS_URL']."images/ausruf_small3.gif\">" : "").formatReady($plugin["description"])."</FONT></TD>\n";
		echo "    </TR>\n";
		echo "    <TR><TD COLSPAN=\"2\">&nbsp;</TD></TR>\n";
		echo "    <TR>\n";
		echo "      <TD COLSPAN=\"2\"><FONT SIZE=\"\">"._("1. W&auml;hlen Sie mit <B>Duchsuchen</B> eine Datei von Ihrer Festplatte aus.")."</FONT></TD>\n";
		echo "    </TR>\n";
		echo "    <TR><TD COLSPAN=\"2\">&nbsp;</TD></TR>\n";
		echo "    <TR>\n";
        	echo "      <TD COLSPAN=\"2\"><input name=\"xmlfile\" type=\"file\" style=\"width:250px\" accept=\"text/xml\" maxlength=\"8000000\"></TD>\n";
		echo "    </TR>\n";
		echo "    <TR><TD COLSPAN=\"2\">&nbsp;</TD></TR>\n";
		echo "    <TR>\n";
		echo "      <TD COLSPAN=\"2\"><FONT SIZE=\"\">"._("2. Klicken Sie auf <B>absenden</B>, um die Datei hochzuladen.")."</FONT></TD>\n";
		echo "    </TR>\n";
		echo "    <TR><TD COLSPAN=\"2\">&nbsp;</TD></TR>\n";
		echo "    <TR>\n";
        	echo "      <TD COLSPAN=\"2\"><input type=\"IMAGE\" " . makeButton("absenden", "src") . " border=0 value=\"" . _("absenden") . "\"></TD>\n";
		echo "    </TR>\n";
	}
	echo "  </TABLE>\n";
	echo "</FORM>\n";
        echo "</blockquote>\n";
}
?>
