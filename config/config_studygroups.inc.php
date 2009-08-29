<?
/**
* config_studygroups.inc.php
*
* Configuration file for studip. In this file you can change the options of many
* Stud.IP Settings. Please note: to setup the system, set the basic settings in the
* local.inc of the phpLib package first.
*
* @access		public
* @package		studip_core
* @modulegroup	library
* @module		config.inc.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Stud.IP Kernfunktionen
// Copyright (C) 2009 Tobias Thelen <tobias.thelen@uni-osnabrueck.de>
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

#$STUDYGROUPS_ENABLE=TRUE;

// Veranstaltungen, die von Autoren angelegt werden können
$SEM_TYPE[99]=array("name"=>_("Studentische Arbeitsgruppe"), "class"=>99);

$SEM_CLASS[99]=array("name"=>_("Studentische Arbeitsgruppen"),
					"studygroup_mode"=>TRUE,
					"compact_mode"=>FALSE,
					"workgroup_mode"=>FALSE,
					"only_inst_user"=>FALSE,
					"turnus_default"=>1,
					"default_read_level"=>1,
					"default_write_level" =>1,
					"bereiche"=>FALSE,
					"show_browse"=>FALSE,
					"topic_create_autor"=>TRUE,
					"write_access_nobody"=>FALSE,
					"visible"=>TRUE,
					"course_creation_forbidden" => TRUE, 
					"forum"=>TRUE,
					"documents"=>TRUE,
					"schedule"=>FALSE,
					"participants"=>TRUE,
					"literature"=>FALSE,
					"chat"=>TRUE,
					"wiki"=>TRUE,
					"description"=> "Studentische Arbeitsgruppen",
					"create_description"=> "Studentische Arbeitsgruppen");

