<?php
/*
suchen.php - Suche im Forensystem, Stud.IP
Copyright (C) 2001 Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
?>
<DIV ID="overDiv" STYLE="position:absolute; visibility:hidden; z-index:1000;"></DIV>
<SCRIPT LANGUAGE="JavaScript" SRC="overlib.js"></SCRIPT>

<?

require_once "$ABSOLUTE_PATH_STUDIP/forum.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/functions.php";
require_once "$ABSOLUTE_PATH_STUDIP/visual.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/suchen.inc.php";

checkObject();

include "$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php";

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<?
suchen($eintrag,$mehr,$suchbegriff,$check_author,$check_name,$check_cont);
  // Save data back to database.
  page_close()
 ?>
 <tr><td class="blank" colspan=2 width="100%">&nbsp;</td></tr>
</table>
</body>
</html>
