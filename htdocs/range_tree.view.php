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
							"query"=>"SELECT item_id FROM range_tree WHERE parent_id=? ORDER BY priority");
$_views["TREE_GET_DATA"] = array("pk"=>"item_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT a.*, b.Name AS fak_name, c.Name as inst_name, c.fakultaets_id FROM range_tree a LEFT JOIN Fakultaeten b ON (a.studip_object = 'fak' AND a.studip_object_id = b.Fakultaets_id)
LEFT JOIN Institute c ON (a.studip_object = 'inst' AND a.studip_object_id = c.Institut_id) ORDER BY priority");
$_views["TREE_OBJECT_NAME"] = array("pk"=>"","temp_table_type"=>"HEAP",
							"query"=>"SELECT Name FROM ! WHERE ! LIKE ? ");
$_views["TREE_OBJECT_DETAIL"] = array("pk"=>"","temp_table_type"=>"HEAP",
							"query"=>"SELECT * FROM ! WHERE ! LIKE ? ");
$_views["TREE_OBJECT_CAT"] = array("pk"=>"kategorie_id","temp_table_type"=>"MyISAM",
							"query"=>"SELECT * FROM kategorien WHERE range_id LIKE ? ORDER BY priority");
$_views["TREE_INST_STATUS"] = array("pk"=>"","temp_table_type"=>"HEAP",
							"query"=>"SELECT Institut_id FROM user_inst WHERE Institut_id IN(&) AND user_id=? AND inst_perms='admin'");
$_views["TREE_FAK_STATUS"] = array("pk"=>"","temp_table_type"=>"HEAP",
							"query"=>"SELECT a.Fakultaets_id,Institut_id FROM fakultaet_user a LEFT JOIN Institute USING(Fakultaets_id) WHERE a.Fakultaets_id IN(&) AND user_id=? AND status='admin'");

$_views["TREE_UPD_PRIO"] = array("query" => "UPDATE range_tree SET priority=! WHERE item_id=?");
$_views["TREE_INS_ITEM"] = array("query" => "INSERT INTO range_tree (item_id,parent_id,name,priority,studip_object,studip_object_id) VALUES (?,?,?,!,?,?)");
$_views["TREE_UPD_ITEM"] = array("query" => "UPDATE range_tree SET name=?, studip_object=?, studip_object_id=? WHERE item_id=?");
$_views["TREE_MOVE_ITEM"] = array("query" => "UPDATE range_tree SET parent_id=?, priority=! WHERE item_id=?");
$_views["TREE_DEL_ITEM"] = array("query" => "DELETE FROM range_tree WHERE item_id IN (&)");

$_views["TREE_SEARCH_INST"] = array("query" => "SELECT Name,Institut_id FROM Institute WHERE Name LIKE '%!%'");
$_views["TREE_SEARCH_FAK"] = array("query" => "SELECT Name,Fakultaets_id FROM Fakultaeten WHERE Name LIKE '%!%'");

$_views["CAT_UPD_PRIO"] = array("query" => "UPDATE kategorien SET priority=!,chdate=UNIX_TIMESTAMP() WHERE kategorie_id=?");
$_views["CAT_UPD_CONTENT"] = array("query" => "UPDATE kategorien SET name=?, content=?, chdate=UNIX_TIMESTAMP() WHERE kategorie_id=?");
$_views["CAT_INS_ALL"] = array("query" => "INSERT INTO kategorien (kategorie_id,range_id,name,content,priority,mkdate,chdate)VALUES (?,?,?,?,!,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");
$_views["CAT_DEL"] = array("query" => "DELETE FROM kategorien WHERE kategorie_id IN (&)");
$_views["CAT_DEL_RANGE"] = array("query" => "DELETE FROM kategorien WHERE range_id IN (&)");
?>
