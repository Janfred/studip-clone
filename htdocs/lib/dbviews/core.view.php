<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// core.view.class.php
// Database views used within Stud.IP core
// 
// Copyright (c) 2003 André Noack <noack@data-quest.de> 
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
// $Id$


$GLOBALS["_views"]["AUTH_USER_UNAME"] = array("pk"=>"item_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT * FROM auth_user_md5 WHERE username=? ");
$GLOBALS["_views"]["AUTH_USER_UID"] = array("pk"=>"item_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT * FROM auth_user_md5 WHERE user_id=? ");
							
$GLOBALS["_views"]["GENERIC_UPDATE"] = array("query" => "UPDATE § SET §=? WHERE user_id=?");
$GLOBALS["_views"]["AUTH_USER_INSERT"] = array("query" => "INSERT INTO auth_user_md5 (user_id, username, perms, password, Vorname, Nachname, Email,auth_plugin) VALUES (?,?,?,?,?,?,?,?)");
$GLOBALS["_views"]["USER_INFO_INSERT"] = array("query" => "INSERT INTO user_info (user_id, mkdate, chdate, preferred_language) VALUES (?,?,?,?)");
?>
