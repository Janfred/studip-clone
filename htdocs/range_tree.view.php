<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// range_tree.view.class.php
// Database views used with "range_tree"
// 
// Copyright (c) 2002 André Noack <noack@data-quest.de> 
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
$_views["TREE_KIDS"] = array("pk"=>"item_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT * FROM range_tree WHERE parent_id=? ORDER BY priority");
$_views["TREE_GET_DATA"] = array("pk"=>"item_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT a.*, b.Name AS fak_name, c.Name as inst_name FROM range_tree a LEFT JOIN Fakultaeten b ON (a.studip_object = 'fak' AND a.studip_object_id = b.Fakultaets_id)
LEFT JOIN Institute c ON (a.studip_object = 'inst' AND a.studip_object_id = c.Institut_id) ORDER BY priority");
$_views["TREE_OBJECT_NAME"] = array("pk"=>"","temp_table_type"=>"HEAP",
							"query"=>"SELECT Name FROM ! WHERE ! LIKE ? ");
$_views["TREE_OBJECT_DETAIL"] = array("pk"=>"","temp_table_type"=>"HEAP",
							"query"=>"SELECT * FROM ! WHERE ! LIKE ? ");
$_views["TREE_OBJECT_CAT"] = array("pk"=>"kategorie_id","temp_table_type"=>"MyISAM",
							"query"=>"SELECT * FROM kategorien WHERE range_id LIKE ? ORDER BY priority");
?>
